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
use AppBundle\Services\DepositBuilder;
use AppBundle\Services\JournalBuilder;
use AppBundle\Utilities\Namespaces;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * An implementation of the SWORD v2 protocol.
 *
 * @Route("/api/sword/2.0")
 */
class SwordController extends Controller {

    use LoggerAwareTrait;

    /**
     * Black and white list service.
     *
     * @var BlackWhiteList
     */
    private $blackwhitelist;

    /**
     * Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Build the controller.
     *
     * @param BlackWhiteList $blackwhitelist
     * @param EntityManagerInterface $em
     */
    public function __construct(BlackWhiteList $blackwhitelist, EntityManagerInterface $em) {
        $this->blackwhitelist = $blackwhitelist;
        $this->em = $em;
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
     * @param string $key
     * @param string $required
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
    private function checkAccess($uuid) {
        if ($this->blackwhitelist->isWhitelisted($uuid)) {
            return true;
        }
        if ($this->blackwhitelist->isBlacklisted($uuid)) {
            return false;
        }

        return $this->getParameter('pln.accepting');
    }

    /**
     * Figure out which message to return for the network status widget in OJS.
     *
     * @param Journal $journal
     *
     * @return string
     */
    private function getNetworkMessage(Journal $journal) {
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
     *
     * @return SimpleXMLElement
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
     * Return a SWORD service document for a journal.
     *
     * Requires On-Behalf-Of and Journal-Url HTTP headers.
     *
     * @param Request $request
     * @param JournalBuilder $builder
     *
     * @return array
     *
     * @Method("GET")
     * @Template()
     * @Route("/sd-iri.{_format}",
     *  name="sword_service_document",
     *  defaults={"_format": "xml"},
     *  requirements={"_format": "xml"}
     * )
     */
    public function serviceDocumentAction(Request $request, JournalBuilder $builder) {
        $obh = strtoupper($this->fetchHeader($request, 'On-Behalf-Of'));
        $journalUrl = $this->fetchHeader($request, 'Journal-Url');
        $accepting = $this->checkAccess($obh);
        $this->logger->notice("{$request->getClientIp()} - service document - {$obh} - {$journalUrl} - accepting: " . ($accepting ? 'yes' : 'no'));
        if (!$obh) {
            throw new BadRequestHttpException("Missing On-Behalf-Of header.", null, 400);
        }
        if (!$journalUrl) {
            throw new BadRequestHttpException("Missing Journal-Url header.", null, 400);
        }

        $journal = $builder->fromRequest($obh, $journalUrl);
        if (!$journal->getTermsAccepted()) {
            $this->accepting = false;
        }
        $this->em->flush();
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
     * @param Request $request
     * @param Journal $journal
     * @param JournalBuilder $journalBuilder
     * @param DepositBuilder $depositBuilder
     *
     * @return Response
     *
     * @Route("/col-iri/{uuid}", name="sword_create_deposit", requirements={
     *      "uuid": ".{36}",
     * })
     * @ParamConverter("journal", options={"mapping": {"uuid"="uuid"}})
     * @Method("POST")
     */
    public function createDepositAction(Request $request, Journal $journal, JournalBuilder $journalBuilder, DepositBuilder $depositBuilder) {
        $accepting = $this->checkAccess($journal->getUuid());
        if (!$journal->getTermsAccepted()) {
            $this->accepting = false;
        }
        $this->logger->notice("{$request->getClientIp()} - create deposit - {$journal->getUuid()} - accepting: " . ($accepting ? 'yes' : 'no'));

        if (!$accepting) {
            throw new BadRequestHttpException('Not authorized to create deposits.', null, 400);
        }

        $xml = $this->getXml($request);
        // Update the journal metadata.
        $journalBuilder->fromXml($xml, $journal->getUuid());
        $deposit = $depositBuilder->fromXml($journal, $xml);
        $this->em->flush();

        /* @var Response */
        $response = $this->statementAction($request, $journal, $deposit);
        $response->headers->set('Location', $this->generateUrl('sword_statement', array(
        'journal_uuid' => $journal->getUuid(),
        'deposit_uuid' => $deposit->getDepositUuid(),
        ), UrlGeneratorInterface::ABSOLUTE_URL));
        $response->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

    /**
     * Check that status of a deposit by fetching the sword statemt.
     *
     * @param Request $request
     * @param Journal $journal
     * @param Deposit $deposit
     *
     * @return Response
     *
     * @Route("/cont-iri/{journal_uuid}/{deposit_uuid}/state", name="sword_statement", requirements={
     *      "journal_uuid": ".{36}",
     *      "deposit_uuid": ".{36}"
     * })
     * @ParamConverter("journal", options={"mapping": {"journal_uuid"="uuid"}})
     * @ParamConverter("deposit", options={"mapping": {"deposit_uuid"="depositUuid"}})
     * @Method("GET")
     */
    public function statementAction(Request $request, Journal $journal, Deposit $deposit) {
        $accepting = $this->checkAccess($journal->getUuid());
        $this->logger->notice("{$request->getClientIp()} - statement - {$journal->getUuid()} - {$deposit->getDepositUuid()} - accepting: " . ($accepting ? 'yes' : 'no'));
        if (!$accepting && !$this->isGranted('ROLE_USER')) {
            throw new BadRequestHttpException('Not authorized to request statements.', null, 400);
        }
        if ($journal !== $deposit->getJournal()) {
            throw new BadRequestHttpException('Deposit does not belong to journal.', null, 400);
        }
        $journal->setContacted(new DateTime());
        $journal->setStatus('healthy');
        $this->em->flush();
        $response = $this->render('AppBundle:sword:statement.xml.twig', array(
        'deposit' => $deposit,
        ));
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    /**
     * Edit a deposit with an HTTP PUT.
     *
     * @param Request $request
     * @param Journal $journal
     * @param Deposit $deposit
     * @param DepositBuilder $builder
     *
     * @return Response
     *
     * @Route("/cont-iri/{journal_uuid}/{deposit_uuid}/edit", name="sword_edit", requirements={
     *      "journal_uuid": ".{36}",
     *      "deposit_uuid": ".{36}"
     * })
     * @ParamConverter("journal", options={"mapping": {"journal_uuid"="uuid"}})
     * @ParamConverter("deposit", options={"mapping": {"deposit_uuid"="depositUuid"}})
     * @Method("PUT")
     */
    public function editAction(Request $request, Journal $journal, Deposit $deposit, DepositBuilder $builder) {
        $accepting = $this->checkAccess($journal->getUuid());
        $this->logger->notice("{$request->getClientIp()} - edit deposit - {$journal->getUuid()} - {$deposit->getDepositUuid()} - accepting: " . ($accepting ? 'yes' : 'no'));
        if (!$accepting) {
            throw new BadRequestHttpException('Not authorized to create deposits.', null, 400);
        }
        if ($journal !== $deposit->getJournal()) {
            throw new BadRequestHttpException('Deposit does not belong to journal.', null, 400);
        }
        $xml = $this->getXml($request);
        $newDeposit = $builder->fromXml($journal, $xml);
        $newDeposit->setAction('edit');
        $this->em->flush();

        /* @var Response */
        $response = $this->statementAction($request, $journal, $deposit);
        $response->headers->set('Location', $this->generateUrl('sword_statement', array(
        'journal_uuid' => $journal->getUuid(),
        'deposit_uuid' => $deposit->getDepositUuid(),
        ), UrlGeneratorInterface::ABSOLUTE_URL));
        $response->setStatusCode(Response::HTTP_CREATED);

        return $response;
    }

}
