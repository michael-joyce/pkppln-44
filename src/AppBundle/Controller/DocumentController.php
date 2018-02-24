<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Document;
use AppBundle\Form\DocumentType;

/**
 * Document controller.
 *
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("/document")
 */
class DocumentController extends Controller
{
    /**
     * Lists all Document entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array
     *   Array data for the template processor.
     * 
     * @Route("/", name="document_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Document::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $documents = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'documents' => $documents,
        );
    }
    /**
     * Search for Document entities.
     *
     * To make this work, add a method like this one to the 
     * AppBundle:Document repository. Replace the fieldName with
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
     * @Route("/search", name="document_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
	$repo = $em->getRepository('AppBundle:Document');
	$q = $request->query->get('q');
	if($q) {
	    $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $documents = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
	} else {
            $documents = array();
	}

        return array(
            'documents' => $documents,
            'q' => $q,
        );
    }

    /**
     * Creates a new Document entity.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Document.
     * 
     * @Route("/new", name="document_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($document);
            $em->flush();

            $this->addFlash('success', 'The new document was created.');
            return $this->redirectToRoute('document_show', array('id' => $document->getId()));
        }

        return array(
            'document' => $document,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Document entity.
     *
     * @param Document $document
     *   The Document to show.
     *
     * @return array
     *   Array data for the template processor.
     *      
     * @Route("/{id}", name="document_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Document $document)
    {

        return array(
            'document' => $document,
        );
    }

    /**
     * Displays a form to edit an existing Document entity.
     *
     * 
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Document $document
     *   The Document to edit.
     * 
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Document.
     * 
     * @Route("/{id}/edit", name="document_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Document $document)
    {
        $editForm = $this->createForm(DocumentType::class, $document);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The document has been updated.');
            return $this->redirectToRoute('document_show', array('id' => $document->getId()));
        }

        return array(
            'document' => $document,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Document entity.
     *
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     * @param Document $document
     *   The Document to delete.
     * 
     * @return array|RedirectResponse
     *   A redirect to the document_index.
     * 
     * @Route("/{id}/delete", name="document_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Document $document)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($document);
        $em->flush();
        $this->addFlash('success', 'The document was deleted.');

        return $this->redirectToRoute('document_index');
    }
}
