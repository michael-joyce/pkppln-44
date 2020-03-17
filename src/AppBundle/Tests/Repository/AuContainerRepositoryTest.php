<?php

namespace AppBundle\Entity;

use AppBundle\DataFixtures\ORM\LoadAuContainer;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class AuContainerRepositoryTest extends BaseTestCase {
	
	/**
	 * @var AuContainer
	 */
	protected $repository;

	public function setUp() : void {
		parent::setUp();
		$this->repository = $this->em->getRepository('AppBundle:AuContainer');
	}

	public function testGetOpenContainer() {
		$c = $this->repository->getOpenContainer();
		$this->assertInstanceOf('AppBundle\Entity\AuContainer', $c);
		$this->assertEquals(true, $c->isOpen());
		$this->assertEquals(2, $c->getId());
	}

	public function getFixtures() {
		return array(
			LoadAuContainer::class,
		);
	}
}
