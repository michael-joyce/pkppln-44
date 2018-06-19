<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Blacklist;
use AppBundle\Entity\Whitelist;
use Doctrine\ORM\EntityRepository;

/**
 * Custom journal queries for doctrine.
 */
class JournalRepository extends EntityRepository {

    /**
     * Get a list of journals that need to be pinged.
     *
     * @return Collection|Journal[]
     *   List of journals.
     */
    public function getJournalsToPing() {

        $blacklist = $this->getEntityManager()->getRepository(Blacklist::class)
            ->createQueryBuilder('bl')
            ->select('bl.uuid');

        $whitelist = $this->getEntityManager()->getRepository(Whitelist::class)
            ->createQueryBuilder('wl')
            ->select('wl.uuid');

        $qb = $this->createQueryBuilder('j');
        $qb->andWhere('j.status != :status');
        $qb->setParameter('status', 'ping-error');
        $qb->andWhere($qb->expr()->notIn('j.uuid', $blacklist->getDQL()));
        $qb->andWhere($qb->expr()->notIn('j.uuid', $whitelist->getDQL()));
        return $qb->getQuery()->execute();
    }

    /**
     * @param string $q
     * @return Query
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('j');
        $qb->where('CONCAT(j.uuid, j.title, j.issn, j.url, j.email, j.publisherName, j.publisherUrl) LIKE :q');
        $qb->setParameter('q', '%' . $q . '%');
        $query = $qb->getQuery();
        return $query;
    }

    /**
     * Summarize the journal statuses, counting them by status.
     *
     * @return array
     */
    public function statusSummary() {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e.status, count(e) as ct')
            ->groupBy('e.status')
            ->orderBy('e.status');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find journals that haven't contacted the PLN in $days.
     *
     * @param int $days
     *
     * @return Collection|Journal[]
     */
    public function findSilent($days) {
        $dt = new DateTime("-{$days} day");

        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.contacted < :dt');
        $qb->setParameter('dt', $dt);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find journals that have gone silent and that notifications have been sent
     * for, but they have not been updated yet.
     *
     * @param int $days
     *
     * @return Collection|Journal[]
     */
    public function findOverdue($days) {
        $dt = new DateTime("-{$days} day");
        $qb = $this->createQueryBuilder('e');
        $qb->Where('e.notified < :dt');
        $qb->setParameter('dt', $dt);

        return $qb->getQUery()->getResult();
    }

    /**
     * @todo This method should be called findRecent(). It does not find
     * journals with status=new
     *
     * @param type $limit
     *
     * @return Collection|Journal[]
     */
    public function findNew($limit = 5) {
        $qb = $this->createQueryBuilder('e');
        $qb->orderBy('e.id', 'DESC');
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

}
