<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadTermOfUse;
use AppBundle\Entity\TermOfUse;
use AppBundle\Repository\TermOfUseRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of JournalRepositoryTest.
 */
class TermOfUseRepositoryTest extends BaseTestCase {
    /**
     * @return TermOfUseRepository
     */
    private $repo;

    protected function getFixtures() {
        return [
            LoadTermOfUse::class,
        ];
    }

    public function testGetTerms() : void {
        $terms = $this->repo->getTerms();
        $this->assertSame([4, 3, 2, 1], array_map(function ($term) {return $term->getId(); }, $terms));
    }

    public function testGetLastUpdated() : void {
        $this->assertNotNull($this->repo->getLastUpdated());
    }

    protected function setup() : void {
        parent::setUp();
        $this->repo = $this->em->getRepository(TermOfUse::class);
    }
}
