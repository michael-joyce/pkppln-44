<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Custom blacklist queries for doctrine.
 */
class BlacklistRepository extends EntityRepository {
    /**
     * Build a query to search for blacklist entries.
     *
     * @param string $q
     *
     * @return Query
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('b');
        $qb->where('CONCAT(b.uuid, \' \', b.comment) LIKE :q');
        $qb->setParameter('q', '%' . $q . '%');

        return $qb->getQuery();
    }
}
