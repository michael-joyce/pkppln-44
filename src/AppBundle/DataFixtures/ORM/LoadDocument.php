<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Document;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadDocument form.
 */
class LoadDocument extends Fixture {

    /**
     * {@inheritDoc}.
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Document();
            $fixture->setTitle('Title ' . $i);
            $fixture->setPath('doc/' . $i);
            $fixture->setSummary('Summary ' . $i);
            $fixture->setContent('Content ' . $i);

            $em->persist($fixture);
            $this->setReference('document.' . $i, $fixture);
        }

        $em->flush();
    }

}
