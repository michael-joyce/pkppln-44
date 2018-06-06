<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Services\FilePaths;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Default controller.
 */
class DefaultController extends Controller {

    /**
     * The LOCKSS permision statement.
     */
    const PERMISSION_STMT = 'LOCKSS system has permission to collect, preserve, and serve this Archival Unit.';

    /**
     * Home page action.
     *
     * @return Response
     *
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
        // Replace this example code with whatever you need.
        return $this->render('default/index.html.twig', [
                    'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * Search for Deposit entities.
     *
     * This action lives in the default controller because the
     * deposit controller works with deposits from a single
     * journal. This search works across all deposits.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/deposit_search", name="all_deposit_search")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     */
    public function depositSearchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Deposit::class);
        $q = $request->query->get('q');
        $paginator = $this->get('knp_paginator');
        if ($q) {
            $query = $repo->searchQuery($q);
            $deposits = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $deposits = $paginator->paginate(array(), $request->query->getInt('page', 1), 25);
        }

        return array(
            'deposits' => $deposits,
            'q' => $q,
        );
    }

    /**
     * Fetch a processed and packaged deposit.
     *
     * @param Request $request
     * @param Journal $journal
     * @param Deposit $deposit
     * @param FilePaths $fp
     *
     * @return BinaryFileResponse
     *
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     *
     * @Route("/fetch/{journalUuid}/{depositUuid}.zip", name="fetch")
     * @ParamConverter("journal", class="AppBundle:Journal", options={"mapping": {"journalUuid"="uuid"}})
     * @ParamConverter("deposit", class="AppBundle:Deposit", options={"mapping": {"depositUuid"="depositUuid"}})
     */
    public function fetchAction(Request $request, Journal $journal, Deposit $deposit, FilePaths $fp) {
        if ($deposit->getJournal() !== $journal) {
            throw new BadRequestHttpException("The requested Journal ID does not match the deposit's journal ID.");
        }
        $fs = new Filesystem();
        $path = $fp->getStagingBagPath($deposit);
        if (!$fs->exists($path)) {
            throw new NotFoundHttpException("Deposit not found.");
        }
        return new BinaryFileResponse($path);
    }

    /**
     * Return the permission statement for LOCKSS.
     *
     * @return Response
     *
     * @Route("/permission", name="lockss_permission")
     */
    public function permissionAction() {
        return new Response(self::PERMISSION_STMT, Response::HTTP_OK, array(
            'content-type' => 'text/plain',
        ));
    }

}
