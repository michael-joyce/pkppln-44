<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Whitelist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadWhitelist form.
 */
class LoadWhitelist extends Fixture {

    const UUIDS = array(
        '57079858-1631-4608-98C1-E1A449DF46DD',
        'E8F084C6-F932-43C0-8B77-B6E8BA9EDF6F',
        '960CD4D9-C4DD-4E47-96ED-532306DE7DBD',
        '930FAF91-7E65-4A61-A589-8D220B686F84',
    );
    
    /**
     * {@inheritDoc}.
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Whitelist();
            $fixture->setUuid(self::UUIDS[$i]);
            $fixture->setComment('Comment ' . $i);

            $em->persist($fixture);
            $this->setReference('whitelist.' . $i, $fixture);
        }

        $em->flush();
    }

}
