<?php

/*
 * Copyright (C) 2015-2016 Michael Joyce <ubermichael@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\SwordClient;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Check the status of deposits in LOCKSSOMatic.
 *
 * @see SwordClient
 */
class StatusChecker {

    /**
     * Sword client to communicate with LOCKSS.
     *
     * @var SwordClient
     */
    private $client;

    /**
     * If true, completed deposits will be removed from disk.
     *
     * @var bool
     */
    private $cleanup;

    /**
     * Construct the status checker.
     *
     * @param SwordClient $client
     * @param bool $cleanup
     */
    public function __construct(SwordClient $client, $cleanup) {
        $this->cleanup = $cleanup;
        $this->client = $client;
    }

    /**
     * Remove a directory and its contents recursively.
     *
     * Use with caution.
     */
    private function delTree($path) {
        $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $fileIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($fileIterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($path);
    }

    /**
     * Process one deposit.
     *
     * Updates the deposit status, and may remove the processing files if
     * LOCKSSOatic reports agreement.
     *
     * @param Deposit $deposit
     *
     * @return bool|null
     */
    protected function processDeposit(Deposit $deposit) {
        $this->logger->notice("Checking deposit {$deposit->getDepositUuid()}");
        $statement = $this->client->statement($deposit);
        $status = (string) $statement->xpath('//atom:category[@scheme="http://purl.org/net/sword/terms/state"]/@term')[0];
        $this->logger->notice('Deposit is ' . $status);
        $deposit->setPlnState($status);
        if ($status === 'agreement' && $this->cleanup) {
            $this->logger->notice("Deposit complete. Removing processing files for deposit {$deposit->getId()}.");
            unlink($this->filePaths->getHarvestFile($deposit));
            $this->deltree($this->filePaths->getProcessingBagPath($deposit));
            unlink($this->filePaths->getStagingBagPath($deposit));
        }

        if ($status === 'agreement') {
            return true;
        }
        return null;
    }

}
