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
     * @return array
     *
     * @Route("/", name="deposit_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request, Journal $journal) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Deposit::class, 'e')->where('e.journal = :journal')->orderBy('e.id', 'ASC')->setParameter('journal', $journal);
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $deposits = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'deposits' => $deposits,
            'journal' => $journal,
        ];
    }

    /**
     * Search for Deposit entities.
     *
     * This action lives in the default controller because the deposit
     * controller works with deposits from a single journal. This
     * search works across all deposits.
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
            $deposits = $paginator->paginate([], $request->query->getInt('page', 1), 25);
        }

        return [
            'journal' => $journal,
            'deposits' => $deposits,
            'q' => $q,
        ];
    }

    /**
     * Finds and displays a Deposit entity.
     *
     * @return array
     *
     * @Route("/{id}", name="deposit_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Journal $journal, Deposit $deposit) {
        return [
            'journal' => $journal,
            'deposit' => $deposit,
        ];
    }
}
