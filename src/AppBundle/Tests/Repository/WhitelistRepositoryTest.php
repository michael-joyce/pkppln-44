<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Repository;

use AppBundle\DataFixtures\ORM\LoadWhitelist;
use AppBundle\Entity\Whitelist;
use AppBundle\Repository\WhitelistRepository;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of WhitelistRepositoryTest.
 */
class WhitelistRepositoryTest extends BaseTestCase {
    /**
     * @return WhitelistRepository
     */
    private $repo;

    protected function getFixtures() {
        return [
            LoadWhitelist::class,
        ];
    }

    public function testSearchQuery() : void {
        $query = $this->repo->searchQuery('960CD4D9');
        $result = $query->execute();
        $this->assertSame(1, count($result));
    }

    protected function setup() : void {
        parent::setUp();
        $this->repo = $this->em->getRepository(Whitelist::class);
    }
}
