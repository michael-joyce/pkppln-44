<?php

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
    const PERMISSION_STMT = 'LOCKSS system has permission to collect, preserve, and serve this Archival Unit.';

    /**
     * Home page action.
     *
     * @return Response
     *
     * @Route("/", name="homepage")
     */
    public function indexAction(EntityManagerInterface $em) {
        $user = $this->getUser();

        if(!$user || !$user->hasRole('ROLE_USER')) {
            return $this->render('default/index_anon.html.twig');
        }

        $journalRepo = $em->getRepository('AppBundle:Journal');
        $depositRepo = $em->getRepository('AppBundle:Deposit');
        return $this->render('default/index_user.html.twig', array(
                'journals_new' => $journalRepo->findNew(),
                'journal_summary' => $journalRepo->statusSummary(),
                'deposits_new' => $depositRepo->findNew(),
                'states' => $depositRepo->stateSummary(),
        ));
    }

    /**
     *
     * @param EntityManagerInterface $em
     * @param string $state
     *
     * @Route("/browse/{state}", name="deposit_browse")
     * @Template()
     */
    public function depositStatusAction(Request $request, EntityManagerInterface $em, $state) {
        $repo = $em->getRepository(Deposit::class);
        $qb = $repo->createQueryBuilder('d');
        $qb->where('d.state = :state');
        $qb->setParameter('state', $state);
        $qb->orderBy('d.id');
        $paginator = $this->get('knp_paginator');
        $deposits = $paginator->paginate($qb->getQuery(), $request->query->getInt('page', 1), 25);
        $states = $repo->stateSummary();
        return array(
            'deposits' => $deposits,
            'states' => $states,
        );
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

}
