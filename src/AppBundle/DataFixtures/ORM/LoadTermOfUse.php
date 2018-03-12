<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\TermOfUse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadTermOfUse form.
 */
class LoadTermOfUse extends Fixture {

    /**
     * {@inheritDoc}.
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new TermOfUse();
            $fixture->setWeight(4 - $i);
            $fixture->setKeyCode('term-' . $i);
            $fixture->setContent('Content ' . $i);

            $em->persist($fixture);
            $this->setReference('termofuse.' . $i, $fixture);
        }

        $em->flush();
    }

}
