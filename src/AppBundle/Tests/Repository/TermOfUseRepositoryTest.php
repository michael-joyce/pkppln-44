<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadTermOfUse;
use AppBundle\Entity\TermOfUse;
use AppBundle\Repository\TermOfUseRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of JournalRepositoryTest
 */
class TermOfUseRepositoryTest extends BaseTestCase {

    /**
     * @return TermOfUseRepository
     */
    private $repo;

    protected function getFixtures() {
        return array(
            LoadTermOfUse::class,
        );
    }

    protected function setup() : void {
        parent::setUp();
        $this->repo = $this->em->getRepository(TermOfUse::class);
    }
    
    public function testGetTerms() {
        $terms = $this->repo->getTerms();
        $this->assertEquals([4,3,2,1], array_map(function($term){return $term->getId();}, $terms));
    }
    
    public function testGetLastUpdated() {
        $this->assertNotNull($this->repo->getLastUpdated());
    }

}
