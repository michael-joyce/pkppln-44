<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Journal;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Custom doctrine queries for deposits.
 */
class DepositRepository extends EntityRepository {

    /**
     * Create a search query and return it.
     *
     * The query isn't executed here.
     *
     * @param string $q
     * @param Journal $journal
     *
     * @return Query
     */
    public function searchQuery($q, Journal $journal = null) {
        $qb = $this->createQueryBuilder('d');
        $qb->where('CONCAT(d.depositUuid, d.url) LIKE :q');
        $qb->setParameter('q', '%' . $q . '%');
        if ($journal) {
            $qb->andWhere('d.journal = :journal');
            $qb->setParameter('journal', $journal);
        }
        $query = $qb->getQuery();
        return $query;
    }

}
