<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\TermOfUse;
use AppBundle\Entity\TermOfUseHistory;
use AppBundle\Form\TermOfUseType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * TermOfUse controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/termofuse")
 */
class TermOfUseController extends Controller {
    /**
     * Lists all TermOfUse entities.
     *
     * @return array
     *
     * @Route("/", name="termofuse_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(TermOfUse::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $termOfUses = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'termOfUses' => $termOfUses,
        ];
    }

    /**
     * Creates a new TermOfUse entity.
     *
     * @return array|RedirectResponse
     *                                Array data for the template processor or a redirect to the TermOfUse.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/new", name="termofuse_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request) {
        $termOfUse = new TermOfUse();
        $form = $this->createForm(TermOfUseType::class, $termOfUse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($termOfUse);
            $em->flush();

            $this->addFlash('success', 'The new termOfUse was created.');

            return $this->redirectToRoute('termofuse_show', ['id' => $termOfUse->getId()]);
        }

        return [
            'termOfUse' => $termOfUse,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a TermOfUse entity.
     *
     * @return array
     *
     * @Route("/{id}", name="termofuse_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(EntityManagerInterface $em, TermOfUse $termOfUse) {
        // This can't just be $termOfUse->getHistory() or something because there
        // is no foreign key relationship - the history is preserved when a term is deleted.
        $repo = $em->getRepository(TermOfUseHistory::class);
        $history = $repo->findBy(['termId' => $termOfUse->getId()], ['id' => 'ASC']);

        return [
            'termOfUse' => $termOfUse,
            'history' => $history,
        ];
    }

    /**
     * Displays a form to edit an existing TermOfUse entity.
     *
     * @return array|RedirectResponse
     *                                Array data for the template processor or a redirect to the TermOfUse.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/edit", name="termofuse_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, TermOfUse $termOfUse) {
        $editForm = $this->createForm(TermOfUseType::class, $termOfUse);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The termOfUse has been updated.');

            return $this->redirectToRoute('termofuse_show', ['id' => $termOfUse->getId()]);
        }

        return [
            'termOfUse' => $termOfUse,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a TermOfUse entity.
     *
     * @return array|RedirectResponse
     *                                A redirect to the termofuse_index.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/delete", name="termofuse_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, TermOfUse $termOfUse) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($termOfUse);
        $em->flush();
        $this->addFlash('success', 'The termOfUse was deleted.');

        return $this->redirectToRoute('termofuse_index');
    }
}
