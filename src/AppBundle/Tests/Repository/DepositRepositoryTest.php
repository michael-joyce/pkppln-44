<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Deposit;
use AppBundle\Repository\DepositRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of DepositRepositoryTest.
 */
class DepositRepositoryTest extends BaseTestCase {
    /**
     * @return DepositRepository
     */
    private $repo;

    protected function getFixtures() {
        return [
            LoadDeposit::class,
            LoadJournal::class,
        ];
    }

    public function testSearchQueryUuid() : void {
        $result = $this->repo->searchQuery('A584');
        $this->assertSame(1, count($result->execute()));
    }

    public function testSearchQueryUrl() : void {
        $result = $this->repo->searchQuery('1.zip');
        $this->assertSame(1, count($result->execute()));
    }

    public function testSearchQueryUuidWithJournal() : void {
        $result = $this->repo->searchQuery('A584', $this->getReference('journal.1'));
        $this->assertSame(1, count($result->execute()));
    }

    public function testSearchQueryUrlWithJorunal() : void {
        $result = $this->repo->searchQuery('1.zip', $this->getReference('journal.1'));
        $this->assertSame(1, count($result->execute()));
    }

    public function testSearchQueryUuidWithOtherJournal() : void {
        $result = $this->repo->searchQuery('A584', $this->getReference('journal.2'));
        $this->assertSame(0, count($result->execute()));
    }

    public function testSearchQueryUrlWithOtherJorunal() : void {
        $result = $this->repo->searchQuery('1.zip', $this->getReference('journal.2'));
        $this->assertSame(0, count($result->execute()));
    }

    protected function setup() : void {
        parent::setUp();
        $this->repo = $this->em->getRepository(Deposit::class);
    }
}
