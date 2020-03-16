<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AuContainer;
use AppBundle\Entity\Blacklist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * LoadBlacklist form.
 */
class LoadAuContainer extends Fixture {

    /**
     * {@inheritdoc}
     */
    public function load(\Doctrine\Persistence\ObjectManager $manager) {
        $auContainer = new AuContainer();
        $manager->persist($auContainer);
        $manager->flush();
        $this->setReference('aucontainer', $auContainer);
    }
}
