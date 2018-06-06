<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Blacklist;
use AppBundle\Form\BlacklistType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Blacklist controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/blacklist")
 */
class BlacklistController extends Controller {

    /**
     * Lists all Blacklist entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="blacklist_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Blacklist::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $blacklists = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'blacklists' => $blacklists,
        );
    }

    /**
     * Search for Blacklist entities.
     *
     * @param Request $request
     *
     * @Route("/search", name="blacklist_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Blacklist::class);
        $q = $request->query->get('q');
        $paginator = $this->get('knp_paginator');
        if ($q) {
            $query = $repo->searchQuery($q);
            $blacklists = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $blacklists = $paginator->paginate(array(), $request->query->getInt('page', 1), 25);
        }

        return array(
            'blacklists' => $blacklists,
            'q' => $q,
        );
    }

    /**
     * Creates a new Blacklist entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Blacklist.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/new", name="blacklist_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request) {
        $blacklist = new Blacklist();
        $form = $this->createForm(BlacklistType::class, $blacklist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($blacklist);
            $em->flush();

            $this->addFlash('success', 'The new blacklist was created.');
            return $this->redirectToRoute('blacklist_show', array('id' => $blacklist->getId()));
        }

        return array(
            'blacklist' => $blacklist,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Blacklist entity.
     *
     * @param Blacklist $blacklist
     *
     * @return array
     *
     * @Route("/{id}", name="blacklist_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Blacklist $blacklist) {

        return array(
            'blacklist' => $blacklist,
        );
    }

    /**
     * Displays a form to edit an existing Blacklist entity.
     *
     * @param Request $request
     * @param Blacklist $blacklist
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Blacklist.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/edit", name="blacklist_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Blacklist $blacklist) {
        $editForm = $this->createForm(BlacklistType::class, $blacklist);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The blacklist has been updated.');
            return $this->redirectToRoute('blacklist_show', array('id' => $blacklist->getId()));
        }

        return array(
            'blacklist' => $blacklist,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Blacklist entity.
     *
     * @param Request $request
     * @param Blacklist $blacklist
     *
     * @return array|RedirectResponse
     *   A redirect to the blacklist_index.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/delete", name="blacklist_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Blacklist $blacklist) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($blacklist);
        $em->flush();
        $this->addFlash('success', 'The blacklist was deleted.');

        return $this->redirectToRoute('blacklist_index');
    }

}
