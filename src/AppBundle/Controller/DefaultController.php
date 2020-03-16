<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Deposit;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default controller.
 */
class DefaultController extends Controller {
    /**
     * The LOCKSS permision statement.
     */
    public const PERMISSION_STMT = 'LOCKSS system has permission to collect, preserve, and serve this Archival Unit.';

    /**
     * Home page action.
     *
     * @return Response
     *
     * @Route("/", name="homepage")
     */
    public function indexAction(EntityManagerInterface $em) {
        $user = $this->getUser();

        if ( ! $user || ! $user->hasRole('ROLE_USER')) {
            return $this->render('default/index_anon.html.twig');
        }

        $journalRepo = $em->getRepository('AppBundle:Journal');
        $depositRepo = $em->getRepository('AppBundle:Deposit');

        return $this->render('default/index_user.html.twig', [
            'journals_new' => $journalRepo->findNew(),
            'journal_summary' => $journalRepo->statusSummary(),
            'deposits_new' => $depositRepo->findNew(),
            'states' => $depositRepo->stateSummary(),
        ]);
    }

    /**
     * Browse deposits across all jouurnals by state.
     *
     * @param string $state
     *
     * @Route("/browse/{state}", name="deposit_browse")
     * @Template()
     */
    public function browseAction(Request $request, EntityManagerInterface $em, $state) {
        $repo = $em->getRepository(Deposit::class);
        $qb = $repo->createQueryBuilder('d');
        $qb->where('d.state = :state');
        $qb->setParameter('state', $state);
        $qb->orderBy('d.id');
        $paginator = $this->get('knp_paginator');
        $deposits = $paginator->paginate($qb->getQuery(), $request->query->getInt('page', 1), 25);
        $states = $repo->stateSummary();

        return [
            'deposits' => $deposits,
            'states' => $states,
        ];
    }

    /**
     * Search for Deposit entities.
     *
     * This action lives in the default controller because the
     * deposit controller works with deposits from a single
     * journal. This search works across all deposits.
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
            $deposits = $paginator->paginate([], $request->query->getInt('page', 1), 25);
        }

        return [
            'deposits' => $deposits,
            'q' => $q,
        ];
    }
}
