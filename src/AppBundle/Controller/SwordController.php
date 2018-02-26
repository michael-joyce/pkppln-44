<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Journal;
use AppBundle\Services\BlackWhiteList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of SwordController
 * @Route("/api/sword/2.0")
 */
class SwordController extends Controller {
    
    /**
     * @var BlackWhiteList
     */
    private $blackwhitelist;
    
    public function __construct(BlackWhiteList $blackwhitelist) {
        $this->blackwhitelist = $blackwhitelist;
    }
    
    /**
     * Fetch an HTTP header.
     *
     * Checks the HTTP headers for $key and X-$key variant. If the app
     * is in the dev environment, will also check the query parameters for
     * $key.
     *
     * If $required is true and the header is not present BadRequestException
     * will be thrown.
     *
     * @param Request $request
     *   Request which should contain the header.
     * @param string $key
     *   Name of the header.
     * @param string $required
     *   If true, an exception will be thrown if the header is missing.
     *
     * @return string|null
     *   The value of the header or null if that's OK.
     *
     * @throws BadRequestException
     */
    private function fetchHeader(Request $request, $key, $required = false) {
        if ($request->headers->has($key)) {
            return $request->headers->get($key);
        }
        if ($this->getParameter('kernel.environment') === 'dev' && $request->query->has($key)) {
            return $request->query->get($key);
        }
        if ($required) {
            throw new BadRequestHttpException("HTTP header {$key} is required.", null, Response::HTTP_BAD_REQUEST);
        }
        return null;
    }
    
    /**
     * Check if a journal's uuid is whitelised or blacklisted. 
     * 
     * The rules are:
     *
     * If the journal uuid is whitelisted, return true
     * If the journal uuid is blacklisted, return false
     * Return the pln_accepting parameter from parameters.yml
     *
     * @param string $uuid
     *
     * @return bool
     */
    private function checkAccess($uuid)
    {
        if ($this->blackwhitelist->isWhitelisted($uuid)) {
            return true;
        }
        if ($this->blackwhitelist->isBlacklisted($uuid)) {
            return false;
        }

        return $this->getParameter('pln.accepting');
    }

    /**
     * The journal with UUID $uuid has contacted the PLN. 
     * 
     * Add a record for the journal if there isn't one, otherwise update the 
     * timestamp. The new journal record will be persisted to the database but
     * not flushed.
     *
     * @param string $uuid
     * @param string $url
     *
     * @return Journal
     */
    private function journalContact($uuid, $url)
    {
        $em = $this->getDoctrine()->getManager();
        $journal = $em->getRepository(Journal::class)->findOneBy(array(
            'uuid' => strtoupper($uuid),
        ));
        if ($journal !== null) {
            $journal->setTimestamp();
            if ($journal->getUrl() !== $url) {
                $journal->setUrl($url);
            }
        } else {
            $journal = new Journal();
            $journal->setUuid($uuid);
            $journal->setUrl($url);
            $journal->setTimestamp();
            $journal->setTitle('unknown');
            $journal->setIssn('unknown');
            $journal->setStatus('new');
            $journal->setEmail('unknown@unknown.com');
            $em->persist($journal);
        }
        if ($journal->getStatus() !== 'new') {
            $journal->setStatus('healthy');
        }
        return $journal;
    }
    
    /**
     * Return a SWORD service document for a journal. Requires On-Behalf-Of
     * and Journal-Url HTTP headers.
     *
     * @Route("/sd-iri.{_format}",
     *  name="sword_service_document",
     *  defaults={"_format": "xml"},
     *  requirements={"_format": "xml"}
     * )
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return array
     * @Template
     */
    public function serviceDocumentAction(Request $request)
    {
        $obh = strtoupper($this->fetchHeader($request, 'On-Behalf-Of'));
        $journalUrl = $this->fetchHeader($request, 'Journal-Url');

        $accepting = $this->checkAccess($obh);
        $acceptingLog = 'not accepting';
        if ($accepting) {
            $acceptingLog = 'accepting';
        }
        if (!$obh) {
            throw new BadRequestHttpException("Missing On-Behalf-Of header.", null, 400);
        }
        if (!$journalUrl) {
            throw new BadRequestHttpException("Missing Journal-Url header.", null, 400);
        }

        $journal = $this->journalContact($obh, $journalUrl);

        return array(
            'onBehalfOf' => $obh,
            'accepting' => $accepting ? 'Yes' : 'No',
            'message' => $this->getNetworkMessage($journal),
            'colIri' => $this->generateUrl(
                'create_deposit',
                array('journal_uuid' => $obh),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'terms' => $this->getTermsOfUse(),
            'termsUpdated' => $this->getDoctrine()->getManager()->getRepository('AppBundle:TermOfUse')->getLastUpdated(),
        );
    }

}
