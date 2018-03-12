<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Custom blacklist queries for doctrine.
 */
class BlacklistRepository extends EntityRepository {
    
    /**
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
