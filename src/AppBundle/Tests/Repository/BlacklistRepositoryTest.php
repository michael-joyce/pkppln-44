<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadBlacklist;
use AppBundle\Entity\Blacklist;
use AppBundle\Repository\BlacklistRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of BlacklistRepositoryTest.
 */
class BlacklistRepositoryTest extends BaseTestCase {
    /**
     * @return BlacklistRepository
     */
    private $repo;

    protected function getFixtures() {
        return [
            LoadBlacklist::class,
        ];
    }

    public function testSearchQuery() : void {
        $query = $this->repo->searchQuery('B156FACD');
        $result = $query->execute();
        $this->assertSame(1, count($result));
    }

    protected function setup() : void {
        parent::setUp();
        $this->repo = $this->em->getRepository(Blacklist::class);
    }
}
