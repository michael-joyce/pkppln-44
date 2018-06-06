<?php

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
     * @var FilePaths
     */
    private $filePaths;
    
    /**
     * @var BagReader
     */
    private $bagReader;

    /**
     * Build the validator.
     *
     * @param FilePaths $fp
     */
    public function __construct(FilePaths $fp) {
        $this->filePaths = $fp;
        $this->bagReader = new BagReader();
    }
    
    /**
     * Overridet the bag reader.
     */
    public function setBagReader(BagReader $bagReader) {
        $this->bagReader = $bagReader;
    }

    /**
     * {@inheritdoc}
     */
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
