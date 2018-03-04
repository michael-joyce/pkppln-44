<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Blacklist;
use AppBundle\Entity\Whitelist;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of BlackWhiteList.
 */
class BlackWhiteList {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @param ObjectRepository $repo
     * @param string $uuid
     */
    private function getEntry(ObjectRepository $repo, $uuid) {
        return $repo->findOneBy(['uuid' => strtoupper($uuid)]) !== null;
    }

    /**
     * Return true if the uuid is whitelisted.
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function isWhitelisted($uuid) {
        $repo = $this->em->getRepository(Whitelist::class);
        return $this->getEntry($repo, $uuid);
    }

    /**
     * Return true if the uuid is blacklisted.
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function isBlacklisted($uuid) {
        $repo = $this->em->getRepository(Blacklist::class);
        return $this->getEntry($repo, $uuid);
    }
    
    /**
     *
     */
    public function isListed($uuid) {
        return $this->isWhitelisted($uuid) || $this->isBlacklisted($uuid);
    }

}
