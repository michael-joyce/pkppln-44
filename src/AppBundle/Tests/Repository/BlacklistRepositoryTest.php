<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadBlacklist;
use AppBundle\Entity\Blacklist;
use AppBundle\Repository\BlacklistRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of BlacklistRepositoryTest
 */
class BlacklistRepositoryTest extends BaseTestCase {

    /**
     * @return BlacklistRepository
     */
    private $repo;

    protected function getFixtures() {
        return array(
            LoadBlacklist::class,
        );
    }

    protected function setup() : void {
        parent::setUp();
        $this->repo = $this->em->getRepository(Blacklist::class);
    }

    public function testSearchQuery() {
        $query = $this->repo->searchQuery('B156FACD');
        $result = $query->execute();
        $this->assertEquals(1, count($result));
    }

}
