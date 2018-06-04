<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Whitelist;
use AppBundle\Form\WhitelistType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array
     *   Array data for the template processor.
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

        return array(
            'whitelists' => $whitelists,
        );
    }

    /**
     * Search for Whitelist entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
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
            $whitelists = $paginator->paginate(array(), $request->query->getInt('page', 1), 25);
        }

        return array(
            'whitelists' => $whitelists,
            'q' => $q,
        );
    }

    /**
     * Creates a new Whitelist entity.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Whitelist.
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
            return $this->redirectToRoute('whitelist_show', array('id' => $whitelist->getId()));
        }

        return array(
            'whitelist' => $whitelist,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Whitelist entity.
     *
     * @param Whitelist $whitelist
     *   The Whitelist to show.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/{id}", name="whitelist_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Whitelist $whitelist) {

        return array(
            'whitelist' => $whitelist,
        );
    }

    /**
     * Displays a form to edit an existing Whitelist entity.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Whitelist $whitelist
     *   The Whitelist to edit.
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Whitelist.
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
            return $this->redirectToRoute('whitelist_show', array('id' => $whitelist->getId()));
        }

        return array(
            'whitelist' => $whitelist,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Whitelist entity.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Whitelist $whitelist
     *   The Whitelist to delete.
     *
     * @return array|RedirectResponse
     *   A redirect to the whitelist_index.
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
