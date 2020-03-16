<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Services\FilePaths;
use Psr\Log\LoggerAwareTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Lockss Controller for handling all interaction with the LOCKSS network.
 */
class LockssController extends Controller {
    use LoggerAwareTrait;

    /**
     * The LOCKSS permision statement.
     */
    public const PERMISSION_STMT = 'LOCKSS system has permission to collect, preserve, and serve this Archival Unit.';

    /**
     * Fetch a processed and packaged deposit.
     *
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     *
     * @return BinaryFileResponse
     *
     *
     * @Route("/fetch/{journalUuid}/{depositUuid}.zip", name="fetch")
     * @Method("GET")
     * @ParamConverter("journal", class="AppBundle:Journal", options={"mapping": {"journalUuid"="uuid"}})
     * @ParamConverter("deposit", class="AppBundle:Deposit", options={"mapping": {"depositUuid"="depositUuid"}})
     */
    public function fetchAction(Request $request, Journal $journal, Deposit $deposit, FilePaths $fp) {
        $this->logger->notice("{$request->getClientIp()} - fetch - {$journal->getUuid()} - {$deposit->getDepositUuid()}");
        if ($deposit->getJournal() !== $journal) {
            throw new BadRequestHttpException("The requested Journal ID does not match the deposit's journal ID.");
        }
        $fs = new Filesystem();
        $path = $fp->getStagingBagPath($deposit);
        if ( ! $fs->exists($path)) {
            throw new NotFoundHttpException('Deposit not found.');
        }

        return new BinaryFileResponse($path);
    }

    /**
     * Return the permission statement for LOCKSS.
     *
     * @return Response
     *
     * @Route("/permission", name="lockss_permission")
     * @Method("GET")
     */
    public function permissionAction(Request $request) {
        $this->logger->notice("{$request->getClientIp()} - permission");

        return new Response(self::PERMISSION_STMT, Response::HTTP_OK, [
            'content-type' => 'text/plain',
        ]);
    }
}
