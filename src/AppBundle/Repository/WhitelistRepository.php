<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Whitelist repository for custom doctrine queries.
 */
class WhitelistRepository extends EntityRepository {

    /**
     * Build a query to search for whitelist entries.
     *
     * @param string $q
     * @return Query
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('b');
        $qb->where('CONCAT(b.uuid, \' \', b.comment) LIKE :q');
        $qb->setParameter('q', '%' . $q . '%');
        $query = $qb->getQuery();
        return $query;
    }

}
