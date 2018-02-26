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
    private $baseDir;

    /**
     * Symfony filesystem object.
     *
     * @var FileSystem
     */
    private $fs;

    /**
     * Build the service.
     */
    public function __construct($dataDir, $projectDir) {
        if ($dataDir[0] === '/') {
            $this->baseDir = $dataDir;
        } else {
            $this->baseDir = $projectDir . '/' . $dataDir;
        }
        $this->fs = new Filesystem();
    }

    public function getBaseDir() {
        return $this->baseDir;
    }

    /**
     * Get an absolute path to a processing directory for the journal.
     *
     * @param string  $dirname
     * @param Journal $journal
     *
     * @return string
     */
    protected function absolutePath($dirname, Journal $journal = null) {
        $path = $this->baseDir . '/' . $dirname;
        if (substr($dirname, -1) !== '/') {
            $path .= '/';
        }
        if (!$this->fs->exists($path)) {
            $this->fs->mkdir($path);
        }
        if ($journal !== null) {
            return $path . $journal->getUuid();
        }

        return realpath($path);
    }

    public function getRestoreDir(Journal $journal) {
        $path = $this->absolutePath('restore', $journal);
        if (!$this->fs->exists($path)) {
            $this->logger->notice("Creating directory {$path}");
            $this->fs->mkdir($path);
        }

        return $path;
    }

    /**
     * Get the harvest directory.
     *
     * @see AppKernel#getRootDir
     *
     * @param Journal $journal
     *
     * @return string
     */
    final public function getHarvestDir(Journal $journal = null) {
        $path = $this->absolutePath('received', $journal);
        if (!$this->fs->exists($path)) {
            $this->logger->notice("Creating directory {$path}");
            $this->fs->mkdir($path);
        }

        return $path;
    }

    /**
     * Get the path to a harvested deposit.
     *
     * @param Deposit $deposit
     *
     * @return type
     */
    final public function getHarvestFile(Deposit $deposit) {
        $path = $this->getHarvestDir($deposit->getJournal());

        return $path . '/' . $deposit->getFileName();
    }

    /**
     * Get the processing directory.
     *
     * @param Journal $journal
     *
     * @return string
     */
    final public function getProcessingDir(Journal $journal) {
        $path = $this->absolutePath('processing', $journal);
        if (!$this->fs->exists($path)) {
            $this->logger->notice("Creating directory {$path}");
            $this->fs->mkdir($path);
        }

        return $path;
    }

    /**
     * Get the path to a deposit bag being processed.
     *
     * @param Deposit $deposit
     *
     * @return type
     */
    public function getProcessingBagPath(Deposit $deposit) {
        $path = $this->getProcessingDir($deposit->getJournal());

        return $path . '/' . $deposit->getDepositUuid();
    }

    /**
     * Get the staging directory for processed deposits.
     *
     * @param Journal $journal
     *
     * @return string
     */
    final public function getStagingDir(Journal $journal) {
        $path = $this->absolutePath('staged', $journal);
        if (!$this->fs->exists($path)) {
            $this->logger->notice("Creating directory {$path}");
            $this->fs->mkdir($path);
        }

        return $path;
    }

    /**
     * Get the path to a processed, staged, bag.
     *
     * @param Deposit $deposit
     *
     * @return type
     */
    final public function getStagingBagPath(Deposit $deposit) {
        $path = $this->getStagingDir($deposit->getJournal());

        return $path . '/' . $deposit->getDepositUuid() . '.zip';
    }

    /**
     * Get the path to the onix feed file.
     *
     * @param string $_format
     *
     * @return string
     */
    public function getOnixPath($_format = 'xml') {
        return $this->baseDir . '/onix.' . $_format;
    }

}
