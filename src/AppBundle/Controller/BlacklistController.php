<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Blacklist;
use AppBundle\Form\BlacklistType;

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
     *   Dependency injected HTTP request object.
     *
     * @return array
     *   Array data for the template processor.
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
     * To make this work, add a method like this one to the
     * AppBundle:Blacklist repository. Replace the fieldName with
     * something appropriate, and adjust the generated search.html.twig
     * template.
     *
     * <code><pre>
     *    public function searchQuery($q) {
     *        $qb = $this->createQueryBuilder('e');
     *        // Simple search against a field
     *        $qb->where("e.fieldName like '%$q%'");
     *        // Full text matching with Beberlei's Doctrine Extensions for MySQL
     *        // https://github.com/beberlei/DoctrineExtensions/
     *        // $qb->addSelect("MATCH_AGAINST (e.name, :q 'IN BOOLEAN MODE') as score");
     *        // $qb->add('where', "MATCH_AGAINST (e.name, :q 'IN BOOLEAN MODE') > 0.5");
     *        return $qb->getQuery();
     *    }
     * </pre></code>
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @Route("/search", name="blacklist_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Blacklist');
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $blacklists = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $blacklists = array();
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
     *   Dependency injected HTTP request object.
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
     *   The Blacklist to show.
     *
     * @return array
     *   Array data for the template processor.
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
     *   Dependency injected HTTP request object.
     * @param Blacklist $blacklist
     *   The Blacklist to edit.
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
     *   Dependency injected HTTP request object.
     * @param Blacklist $blacklist
     *   The Blacklist to delete.
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
