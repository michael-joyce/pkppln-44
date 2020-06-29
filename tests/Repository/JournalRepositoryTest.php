<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Blacklist;
use AppBundle\Entity\Journal;
use AppBundle\Entity\Whitelist;
use AppBundle\Repository\JournalRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of JournalRepositoryTest.
 */
class JournalRepositoryTest extends BaseTestCase {
    /**
     * @return JournalRepository
     */
    private $repo;

    protected function getFixtures() {
        return [
            LoadJournal::class,
        ];
    }

    public function testGetJournalsToPingNoListed() : void {
        $this->assertSame(4, count($this->repo->getJournalsToPing()));
    }

    public function testGetJournalsToPingListed() : void {
        $whitelist = new Whitelist();
        $whitelist->setUuid(LoadJournal::UUIDS[0]);
        $whitelist->setComment('Test');
        $this->em->persist($whitelist);

        $blacklist = new Blacklist();
        $blacklist->setUuid(LoadJournal::UUIDS[1]);
        $blacklist->setComment('Test');
        $this->em->persist($blacklist);

        $this->em->flush();

        $this->assertSame(2, count($this->repo->getJournalsToPing()));
    }

    public function testGetJournalsToPingPingErrors() : void {
        $journal = $this->em->find(Journal::class, 1);
        $journal->setStatus('ping-error');
        $this->em->flush();

        $this->assertSame(3, count($this->repo->getJournalsToPing()));
    }

    /**
     * @dataProvider searchQueryData
     */
    public function testSearchQuery() : void {
        $query = $this->repo->searchQuery('CDC4');
        $result = $query->execute();
        $this->assertSame(1, count($result));
    }

    public function searchQueryData() {
        return [
            [1, 'CDC4'],
            [1, 'Title 1'],
            [1, '1234-1234'],
            [4, 'example.com'],
            [4, 'email@'],
            [4, 'PublisherName'],
            [1, 'publisher/1'],
        ];
    }

    protected function setup() : void {
        parent::setUp();
        $this->repo = $this->em->getRepository(Journal::class);
    }
}
