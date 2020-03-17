<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AuContainer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Load a deposit for testing.
 */
class LoadAuContainer extends Fixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
		$c1 = new AuContainer();
		$c1->setOpen(false);
        $this->setReference('aucontainer', $c1);
		$manager->persist($c1);

		$c2 = new AuContainer();
		$manager->persist($c2);
		$c3 = new AuContainer();
		$manager->persist($c3);
		$manager->flush();
    }

}
