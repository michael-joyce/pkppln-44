<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Entity\TermOfUse;
use AppBundle\Services\BlackWhiteList;
use AppBundle\Utilities\Namespaces;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * Figure out which message to return for the network status widget in OJS.
     *
     * @param Journal $journal
     *
     * @return string
     */
    private function getNetworkMessage(Journal $journal)
    {
        if ($journal->getOjsVersion() === null) {
            return $this->getParameter('pln.network_default');
        }
        if (version_compare($journal->getOjsVersion(), $this->getParameter('pln.min_ojs_version'), '>=')) {
            return $this->getParameter('pln.network_accepting');
        }

        return $this->getParameter('pln.network_oldojs');
    }    
    
    /**
     * Get the XML from an HTTP request.
     *
     * @param Request $request
     *   Depedency injected http request.
     *
     * @return SimpleXMLElement
     *   Parsed XML.
     *
     * @throws BadRequestHttpException
     */
    private function getXml(Request $request) {
        $content = $request->getContent();
        if (!$content || !is_string($content)) {
            throw new BadRequestHttpException("Expected request body. Found none.", null, Response::HTTP_BAD_REQUEST);
        }
        try {
            $xml = simplexml_load_string($content);
            Namespaces::registerNamespaces($xml);
            return $xml;
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Cannot parse request XML.", $e, Response::HTTP_BAD_REQUEST);
        }
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
        $termsRepo = $this->getDoctrine()->getRepository(TermOfUse::class);
        return array(
            'onBehalfOf' => $obh,
            'accepting' => $accepting ? 'Yes' : 'No',
            'maxUpload' => $this->getParameter('pln.max_upload'),
            'checksumType' => $this->getParameter('pln.checksum_type'),
            'message' => $this->getNetworkMessage($journal),
            'journal' => $journal,
            'terms' => $termsRepo->getTerms(),
            'termsUpdated' => $termsRepo->getLastUpdated(),
        );
    }

    /**
     * Create a deposit.
     *
     * @Route("/col-iri/{uuid}", name="sword_create_deposit", requirements={
     *      "journal_uuid": ".{36}",
     * })
     * @ParamConverter("journal", options={"uuid"="uuid"})
     * @Method("POST")
     *
     * @param Request $request
     * @param Journal $journal
     *
     * @return Response
     */
    public function createDepositAction(Request $request, Journal $journal)
    {
        $accepting = $this->checkAccess($journal->getUuid());
        $acceptingLog = 'not accepting';
        if ($accepting) {
            $acceptingLog = 'accepting';
        }

        if (!$accepting) {
            throw new BadRequestHttpException('Not authorized to create deposits.', 400);
        }

        try {
            $xml = $this->getXml($request);
            $journal->setStatus('healthy');
            $deposit = $this->get('depositbuilder')->fromXml($journal, $xml);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e, 400);
        }

        /* @var Response */
        $response = $this->statementAction($request, $journal->getUuid(), $deposit->getDepositUuid());
        $response->headers->set(
            'Location',
            $deposit->getDepositReceipt(),
            true
        );
        $response->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

    /**
     * Check that status of a deposit by fetching the sword statemt.
     *
     * @Route("/cont-iri/{journal_uuid}/{deposit_uuid}/state", name="sword_statement", requirements={
     *      "journal_uuid": ".{36}",
     *      "deposit_uuid": ".{36}"
     * })
     * @ParamConverter("journal", options={"uuid"="journal_uuid"})
     * @ParamConverter("deposit", options={"deposit_uuid"="deposit_uuid"})
     * @Method("GET")
     *
     * @param Request $request
     * @param Journal $journal
     * @param Deposit $deposit
     *
     * @return Response
     */
    public function statementAction(Request $request, Journal $journal, Deposit $deposit) {
        
    }
    
    /**
     * Edit a deposit with an HTTP PUT.
     *
     * @Route("/cont-iri/{journal_uuid}/{deposit_uuid}/edit", name="sword_edit", requirements={
     *      "journal_uuid": ".{36}",
     *      "deposit_uuid": ".{36}"
     * })
     * @ParamConverter("journal", options={"uuid"="journal_uuid"})
     * @ParamConverter("deposit", options={"deposit_uuid"="deposit_uuid"})
     * @Method("GET")
     *
     * @param Request $request
     * @param Journal $journal
     * @param Deposit $deposit
     *
     * @return Response
     */
    public function editAction(Request $request, Journal $journal, Deposit $deposit) {
        
    }
    
    /**
     *
     * @Route("/original/{journal_uuid}/{deposit_uuid}", name="sword_original_deposit", requirements={
     *      "journal_uuid": ".{36}",
     *      "deposit_uuid": ".{36}"
     * })
     * @ParamConverter("journal", options={"uuid"="journal_uuid"})
     * @ParamConverter("deposit", options={"deposit_uuid"="deposit_uuid"})
     * @Method("GET")
     *
     * @param Request $request
     * @param Journal $journal
     * @param Deposit $deposit
     *
     * @return BinaryFileResponse
     */
    public function originalDepositAction(Request $request, Journal $journal, Deposit $deposit) {
        
    }
    
}
