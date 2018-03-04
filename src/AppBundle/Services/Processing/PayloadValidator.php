<?php

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\FilePaths;
use Exception;

/**
 * Validate the size and checksum of a downloaded deposit.
 */
class PayloadValidator {

    /**
     * @var FilePaths
     */
    private $fp;

    /**
     * @param FilePaths $fp
     */
    public function __construct(FilePaths $fp) {
        $this->fp = $fp;
    }

    public function setFilePaths(FilePaths $filePaths) {
        $this->fp = $filePaths;
    }
    
    public function hashFile($algorithm, $filepath) {
        $handle = fopen($filepath, "r");
        $context = null;
        switch (strtolower($algorithm)) {
            case 'sha-1':
            case 'sha1':
                $context = hash_init('sha1');
                break;
            case 'md5':
                $context = hash_init('md5');
                break;
            default:
                throw new Exception("Unknown hash algorithm {$algorithm}");
        }
        while (($data = fread($handle, 64 * 1024))) {
            hash_update($context, $data);
        }
        $hash = hash_final($context);
        fclose($handle);
        return strtoupper($hash);
    }

    public function processDeposit(Deposit $deposit) {
        try {
            $depositPath = $this->fp->getHarvestFile($deposit);
            $checksumValue = $this->hashFile($deposit->getChecksumType(), $depositPath);
            if ($checksumValue !== $deposit->getChecksumValue()) {
                throw new Exception("Deposit checksum does not match. Expected {$deposit->getChecksumValue()} != Actual {$checksumValue}");
            }
            return true;
        } catch (Exception $e) {
            $deposit->addToProcessingLog($e->getMessage());
            return false;
        }
    }

}
