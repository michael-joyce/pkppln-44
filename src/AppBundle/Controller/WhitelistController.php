<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Journal;
use AppBundle\Entity\Whitelist;
use AppBundle\Form\WhitelistType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Whitelist controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/whitelist")
 */
class WhitelistController extends Controller {
    /**
     * Lists all Whitelist entities.
     *
     * @return array
     *
     * @Route("/", name="whitelist_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Whitelist::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $whitelists = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'whitelists' => $whitelists,
        ];
    }

    /**
     * Search for Whitelist entities.
     *
     * @Route("/search", name="whitelist_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Whitelist::class);
        $q = $request->query->get('q');
        $paginator = $this->get('knp_paginator');
        if ($q) {
            $query = $repo->searchQuery($q);
            $whitelists = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $whitelists = $paginator->paginate([], $request->query->getInt('page', 1), 25);
        }

        return [
            'whitelists' => $whitelists,
            'q' => $q,
        ];
    }

    /**
     * Creates a new Whitelist entity.
     *
     * @return array|RedirectResponse
     *                                Array data for the template processor or a redirect to the Whitelist.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/new", name="whitelist_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request) {
        $whitelist = new Whitelist();
        $form = $this->createForm(WhitelistType::class, $whitelist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($whitelist);
            $em->flush();

            $this->addFlash('success', 'The new whitelist was created.');

            return $this->redirectToRoute('whitelist_show', ['id' => $whitelist->getId()]);
        }

        return [
            'whitelist' => $whitelist,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Whitelist entity.
     *
     * @return array
     *
     * @Route("/{id}", name="whitelist_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(EntityManagerInterface $em, Whitelist $whitelist) {
        $repo = $em->getRepository(Journal::class);
        $journal = $repo->findOneBy(['uuid' => $whitelist->getUuid()]);

        return [
            'whitelist' => $whitelist,
            'journal' => $journal,
        ];
    }

    /**
     * Displays a form to edit an existing Whitelist entity.
     *
     * @return array|RedirectResponse
     *                                Array data for the template processor or a redirect to the Whitelist.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/edit", name="whitelist_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Whitelist $whitelist) {
        $editForm = $this->createForm(WhitelistType::class, $whitelist);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The whitelist has been updated.');

            return $this->redirectToRoute('whitelist_show', ['id' => $whitelist->getId()]);
        }

        return [
            'whitelist' => $whitelist,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a Whitelist entity.
     *
     * @return array|RedirectResponse
     *                                A redirect to the whitelist_index.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/delete", name="whitelist_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Whitelist $whitelist) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($whitelist);
        $em->flush();
        $this->addFlash('success', 'The whitelist was deleted.');

        return $this->redirectToRoute('whitelist_index');
    }
}
