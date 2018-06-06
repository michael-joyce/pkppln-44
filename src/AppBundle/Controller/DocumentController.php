<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Document;
use AppBundle\Form\DocumentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Document controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/document")
 */
class DocumentController extends Controller {

    /**
     * Lists all Document entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="document_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
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
     * Creates a new Document entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Document.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/new", name="document_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request) {
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
     *
     * @return array
     *
     * @Route("/{id}", name="document_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Document $document) {

        return array(
            'document' => $document,
        );
    }

    /**
     * Displays a form to edit an existing Document entity.
     *
     * @param Request $request
     * @param Document $document
     *
     * @return array|RedirectResponse
     *   Array data for the template processor or a redirect to the Document.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/edit", name="document_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Document $document) {
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
     * @param Request $request
     * @param Document $document
     *
     * @return array|RedirectResponse
     *   A redirect to the document_index.
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/delete", name="document_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Document $document) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($document);
        $em->flush();
        $this->addFlash('success', 'The document was deleted.');

        return $this->redirectToRoute('document_index');
    }

}
