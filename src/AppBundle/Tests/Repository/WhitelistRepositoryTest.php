<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadWhitelist;
use AppBundle\Entity\Whitelist;
use AppBundle\Repository\WhitelistRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of WhitelistRepositoryTest
 */
class WhitelistRepositoryTest extends BaseTestCase {

    /**
     * @return WhitelistRepository
     */
    private $repo;

    protected function getFixtures() {
        return array(
            LoadWhitelist::class,
        );
    }

    protected function setUp() {
        parent::setUp();
        $this->repo = $this->em->getRepository(Whitelist::class);
    }

    public function testSearchQuery() {
        $query = $this->repo->searchQuery('960CD4D9');
        $result = $query->execute();
        $this->assertEquals(1, count($result));
    }

}
