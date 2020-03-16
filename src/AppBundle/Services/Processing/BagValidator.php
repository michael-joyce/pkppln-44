<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\FilePaths;
use AppBundle\Utilities\BagReader;
use Exception;

/**
 * Validate a bag, according to the bagit spec.
 */
class BagValidator {
    /**
     * File path service.
     *
     * @var FilePaths
     */
    private $filePaths;

    /**
     * Bag reader service.
     *
     * @var BagReader
     */
    private $bagReader;

    /**
     * Build the validator.
     */
    public function __construct(FilePaths $fp) {
        $this->filePaths = $fp;
        $this->bagReader = new BagReader();
    }

    /**
     * Overridet the bag reader.
     */
    public function setBagReader(BagReader $bagReader) : void {
        $this->bagReader = $bagReader;
    }

    public function processDeposit(Deposit $deposit) {
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);
        $bag = $this->bagReader->readBag($harvestedPath);

        $errors = $bag->validate();

        if (count($errors) > 0) {
            foreach ($errors as $error) {
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
