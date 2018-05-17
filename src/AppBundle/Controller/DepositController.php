<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Deposit controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/journal/{journalId}/deposit")
 * @ParamConverter("journal", options={"id"="journalId"})
 */
class DepositController extends Controller {

    /**
     * Lists all Deposit entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Journal $journal
     *   Injected journal parameter from the URL.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/", name="deposit_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request, Journal $journal) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Deposit::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $deposits = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'deposits' => $deposits,
            'journal' => $journal,
        );
    }

    /**
     * Search for Deposit entities.
     *
     * This action lives in the default controller because the deposit
     * controller works with deposits from a single journal. This
     * search works across all deposits.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Journal $journal
     *   Injected journal parameter from the URL.
     *
     * @Route("/search", name="deposit_search")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     * @Template()
     */
    public function searchAction(Request $request, Journal $journal) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Deposit::class);
        $q = $request->query->get('q');
        $paginator = $this->get('knp_paginator');
        if ($q) {
            $query = $repo->searchQuery($q, $journal);
            $deposits = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $deposits = $paginator->paginate(array(), $request->query->getInt('page', 1), 25);
        }

        return array(
            'journal' => $journal,
            'deposits' => $deposits,
            'q' => $q,
        );
    }
    
    /**
     * Finds and displays a Deposit entity.
     *
     * @param Journal $journal
     *   Injected journal parameter from the URL.
     * @param Deposit $deposit
     *   The Deposit to show.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/{id}", name="deposit_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Journal $journal, Deposit $deposit) {

        return array(
            'journal' => $journal,
            'deposit' => $deposit,
        );
    }

}
