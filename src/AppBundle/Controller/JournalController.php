<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Journal;
use AppBundle\Form\JournalType;

/**
 * Journal controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/journal")
 */
class JournalController extends Controller {

    /**
     * Lists all Journal entities.
     *
     * @param Request $request
     *   Dependency injected HTTP request object.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/", name="journal_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Journal::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $journals = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'journals' => $journals,
        );
    }

    /**
     * Search for Journal entities.
     *
     * To make this work, add a method like this one to the
     * AppBundle:Journal repository. Replace the fieldName with
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
     * @Route("/search", name="journal_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Journal');
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $journals = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $journals = array();
        }

        return array(
            'journals' => $journals,
            'q' => $q,
        );
    }

    /**
     * Finds and displays a Journal entity.
     *
     * @param Journal $journal
     *   The Journal to show.
     *
     * @return array
     *   Array data for the template processor.
     *
     * @Route("/{id}", name="journal_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Journal $journal) {

        return array(
            'journal' => $journal,
        );
    }

}
