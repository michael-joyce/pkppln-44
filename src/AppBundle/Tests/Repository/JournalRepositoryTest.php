<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Blacklist;
use AppBundle\Entity\Journal;
use AppBundle\Entity\Whitelist;
use AppBundle\Repository\JournalRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of JournalRepositoryTest
 */
class JournalRepositoryTest extends BaseTestCase {

    /**
     * @return JournalRepository
     */
    private $repo;

    protected function getFixtures() {
        return array(
            LoadJournal::class,
        );
    }

    protected function setUp() {
        parent::setUp();
        $this->repo = $this->em->getRepository(Journal::class);
    }

    public function testGetJournalsToPingNoListed() {
        $this->assertEquals(4, count($this->repo->getJournalsToPing()));
    }

    public function testGetJournalsToPingListed() {
        $whitelist = new Whitelist();
        $whitelist->setUuid(LoadJournal::UUIDS[0]);
        $whitelist->setComment('Test');
        $this->em->persist($whitelist);

        $blacklist = new Blacklist();
        $blacklist->setUuid(LoadJournal::UUIDS[1]);
        $blacklist->setComment('Test');
        $this->em->persist($blacklist);

        $this->em->flush();

        $this->assertEquals(2, count($this->repo->getJournalsToPing()));
    }

    public function testGetJournalsToPingPingErrors() {
        $journal = $this->em->find(Journal::class, 1);
        $journal->setStatus('ping-error');        
        $this->em->flush();

        $this->assertEquals(3, count($this->repo->getJournalsToPing()));
    }

}