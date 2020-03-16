<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Document;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadDocument form.
 */
class LoadDocument extends Fixture {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
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
