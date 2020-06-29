<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Entity;

use AppBundle\DataFixtures\ORM\LoadAuContainer;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class AuContainerRepositoryTest extends BaseTestCase {
    /**
     * @var AuContainer
     */
    protected $repository;

    public function testGetOpenContainer() : void {
        $c = $this->repository->getOpenContainer();
        $this->assertInstanceOf('AppBundle\Entity\AuContainer', $c);
        $this->assertSame(true, $c->isOpen());
        $this->assertSame(2, $c->getId());
    }

    public function getFixtures() {
        return [
            LoadAuContainer::class,
        ];
    }

    public function setUp() : void {
        parent::setUp();
        $this->repository = $this->em->getRepository('AppBundle:AuContainer');
    }
}
