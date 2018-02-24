<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\TermOfUse;
use AppBundle\Form\TermOfUseType;

/**
 * TermOfUse controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/termofuse")
 */
class TermOfUseController extends Controller
{
    /**
     * Lists all TermOfUse entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array
     *   Array data for the template processor.
     * 
     * @Route("/", name="termofuse_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(TermOfUse::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $termOfUses = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'termOfUses' => $termOfUses,
        );
    }
    /**
     * Search for TermOfUse entities.
     *
     * To make this work, add a method like this one to the 
     * AppBundle:TermOfUse repository. Replace the fieldName with
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
     * @Route("/search", name="termofuse_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
	$repo = $em->getRepository('AppBundle:TermOfUse');
	$q = $request->query->get('q');
	if($q) {
	    $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $termOfUses = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
	} else {
            $termOfUses = array();
	}

        return array(
            'termOfUses' => $termOfUses,
            'q' => $q,
        );
    }

    /**
     * Creates a new TermOfUse entity.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the TermOfUse.
     * 
     * @Route("/new", name="termofuse_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        if( ! $this->isGranted('ROLE_CONTENT_ADMIN')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $termOfUse = new TermOfUse();
        $form = $this->createForm(TermOfUseType::class, $termOfUse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($termOfUse);
            $em->flush();

            $this->addFlash('success', 'The new termOfUse was created.');
            return $this->redirectToRoute('termofuse_show', array('id' => $termOfUse->getId()));
        }

        return array(
            'termOfUse' => $termOfUse,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a TermOfUse entity.
     *
     * @param TermOfUse $termOfUse
     *   The TermOfUse to show.
     *
     * @return array
     *   Array data for the template processor.
     *      
     * @Route("/{id}", name="termofuse_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(TermOfUse $termOfUse)
    {

        return array(
            'termOfUse' => $termOfUse,
        );
    }

    /**
     * Displays a form to edit an existing TermOfUse entity.
     *
     * 
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param TermOfUse $termOfUse
     *   The TermOfUse to edit.
     * 
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the TermOfUse.
     * 
     * @Route("/{id}/edit", name="termofuse_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, TermOfUse $termOfUse)
    {
        if( ! $this->isGranted('ROLE_CONTENT_ADMIN')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $editForm = $this->createForm(TermOfUseType::class, $termOfUse);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The termOfUse has been updated.');
            return $this->redirectToRoute('termofuse_show', array('id' => $termOfUse->getId()));
        }

        return array(
            'termOfUse' => $termOfUse,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a TermOfUse entity.
     *
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param TermOfUse $termOfUse
     *   The TermOfUse to delete.
     * 
     * @return array|RedirectResponse
     *   A redirect to the termofuse_index.
     * 
     * @Route("/{id}/delete", name="termofuse_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, TermOfUse $termOfUse)
    {
        if( ! $this->isGranted('ROLE_CONTENT_ADMIN')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($termOfUse);
        $em->flush();
        $this->addFlash('success', 'The termOfUse was deleted.');

        return $this->redirectToRoute('termofuse_index');
    }
}
