<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Calculate file paths.
 */
class FilePaths {

    /**
     * Base directory where the files are stored.
     *
     * @var string
     */
    private $root;

    /**
     * Symfony filesystem object.
     *
     * @var FileSystem
     */
    private $fs;

    /**
     * Build the service.
     */
    public function __construct($root, $projectDir, FileSystem $fs = null) {
        if ($root && $root[0] !== '/') {
            $this->root = $projectDir . '/' . $root;
        } else {
            $this->root = $root;
        }
        if ($fs) {
            $this->fs = $fs;
        } else {
            $this->fs = new Filesystem();
        }
    }

    /**
     * Get the root file system path.
     *
     * @return string
     */
    public function getRootPath() {
        return $this->root;
    }

    /**
     *
     */
    public function getRestoreDir(Journal $journal) {
        $path = implode('/', array(
            $this->getRootPath(),
            'restore',
            $journal->getUuid(),
        ));
        if (!$this->fs->exists($path)) {
            $this->fs->mkdir($path);
        }

        return $path;
    }
    
    /**
     *
     */
    public function getRestoreFile(Deposit $deposit) {
        $path = implode('/', array(
            $this->getRestoreDir($deposit->getJournal()),
            $deposit->getDepositUuid() . '.zip',
        ));
        return $path;
    }

    /**
     * Get the harvest directory.
     *
     * @param Journal $journal
     *
     * @return string
     */
    public function getHarvestDir(Journal $journal) {
        $path = implode('/', array(
        $this->getRootPath(),
        'harvest',
        $journal->getUuid(),
        ));
        if (!$this->fs->exists($path)) {
            $this->fs->mkdir($path);
        }

        return $path;
    }

    /**
     * Get the path to a harvested deposit.
     *
     * @param Deposit $deposit
     *
     * @return mixed
     */
    public function getHarvestFile(Deposit $deposit) {
        $path = implode('/', array(
        $this->getHarvestDir($deposit->getJournal()),
        $deposit->getDepositUuid() . '.zip',
        ));
        return $path;
    }

    /**
     * Get the processing directory.
     *
     * @param Journal $journal
     *
     * @return string
     */
    public function getProcessingDir(Journal $journal) {
        $path = implode('/', array(
        $this->getRootPath(),
        'processing',
        $journal->getUuid(),
        ));
        if (!$this->fs->exists($path)) {
            $this->fs->mkdir($path);
        }
        return $path;
    }

    /**
     * Get the path to a deposit bag being processed.
     *
     * @param Deposit $deposit
     *
     * @return mixed
     */
    public function getProcessingBagPath(Deposit $deposit) {
        $path = implode('/', array(
        $this->getProcessingDir($deposit->getJournal()),
        $deposit->getDepositUuid(),
        ));
        if (!$this->fs->exists($path)) {
            $this->fs->mkdir($path);
        }
        return $path;
    }

    /**
     * Get the staging directory for processed deposits.
     *
     * @param Journal $journal
     *
     * @return string
     */
    public function getStagingDir(Journal $journal) {
        $path = implode('/', array(
        $this->getRootPath(),
        'staged',
        $journal->getUuid(),
        ));
        if (!$this->fs->exists($path)) {
            $this->fs->mkdir($path);
        }

        return $path;
    }

    /**
     * Get the path to a processed, staged, bag.
     *
     * @param Deposit $deposit
     *
     * @return mixed
     */
    public function getStagingBagPath(Deposit $deposit) {
        $path = $this->getStagingDir($deposit->getJournal());

        return $path . '/' . $deposit->getDepositUuid() . '.zip';
    }

    /**
     * Get the path to the onix feed file.
     *
     * @return string
     */
    public function getOnixPath() {
        return $this->root . '/onix.xml';
    }

}
