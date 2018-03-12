<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Deposit;
use AppBundle\Repository\DepositRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of DepositRepositoryTest
 */
class DepositRepositoryTest extends BaseTestCase {

    /**
     * @return DepositRepository
     */
    private $repo;

    protected function getFixtures() {
        return array(
            LoadDeposit::class,
            LoadJournal::class,
        );
    }

    protected function setUp() {
        parent::setUp();
        $this->repo = $this->em->getRepository(Deposit::class);
    }

    public function testSearchQueryUuid() {
        $result = $this->repo->searchQuery('A584');
        $this->assertEquals(1, count($result->execute()));
    }
    
    public function testSearchQueryUrl() {
        $result = $this->repo->searchQuery('1.zip');
        $this->assertEquals(1, count($result->execute()));
    }
    
    public function testSearchQueryUuidWithJournal() {
        $result = $this->repo->searchQuery('A584', $this->getReference('journal.1'));
        $this->assertEquals(1, count($result->execute()));
    }
    
    public function testSearchQueryUrlWithJorunal() {
        $result = $this->repo->searchQuery('1.zip', $this->getReference('journal.1'));
        $this->assertEquals(1, count($result->execute()));
    }
    
    public function testSearchQueryUuidWithOtherJournal() {
        $result = $this->repo->searchQuery('A584', $this->getReference('journal.2'));
        $this->assertEquals(0, count($result->execute()));
    }
    
    public function testSearchQueryUrlWithOtherJorunal() {
        $result = $this->repo->searchQuery('1.zip', $this->getReference('journal.2'));
        $this->assertEquals(0, count($result->execute()));
    }
    
}
