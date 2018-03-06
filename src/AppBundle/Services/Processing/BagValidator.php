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

require_once 'vendor/scholarslab/bagit/lib/bagit.php';

use AppBundle\Entity\Deposit;
use AppBundle\Services\FilePaths;
use BagIt;
use Exception;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Validate a bag, according to the bagit spec.
 */
class BagValidator {

    /**
     * @var FilePaths
     */
    private $filePaths;

    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * 
     * @param FilePaths $fp
     * @param Filesystem $fs
     */
    public function __construct(FilePaths $fp, Filesystem $fs) {
        $this->filePaths = $fp;
        $this->fs = $fs;
    }

    /**
     * {@inheritdoc}
     */
    public function processDeposit(Deposit $deposit) {
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);

        if (!$this->fs->exists($harvestedPath)) {
            throw new Exception("Deposit file {$harvestedPath} does not exist");
        }

        $bag = new BagIt($harvestedPath);
        $bag->validate();

        if (count($bag->getBagErrors()) > 0) {
            foreach ($bag->getBagErrors() as $error) {
                $deposit->addErrorLog("Bagit validation error for {$error[0]} - {$error[1]}");
            }
            throw new Exception("BagIt validation failed for {$deposit->getDepositUuid()}");
        }
        $journalVersion = $bag->getBagInfoData('PKP-PLN-OJS-Version');
        if ($journalVersion && $journalVersion !== $deposit->getJournalVersion()) {
            $deposit->addErrorLog("Bag journal version tag {$journalVersion} does not match deposit journal version {$deposit->getJournalVersion()}");
        }

        return true;
    }

}
