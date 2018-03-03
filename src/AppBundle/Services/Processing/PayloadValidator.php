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
use Exception;

/**
 * Validate the size and checksum of a downloaded deposit.
 */
class PayloadValidator {

    protected function hashFile($algorithm, $filepath) {
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
                throw new \Exception("Unknown hash algorithm {$algorithm}");
        }
        while (($data = fread($handle, 64 * 1024))) {
            hash_update($context, $data);
        }
        $hash = hash_final($context);
        fclose($handle);
        return strtoupper($hash);
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {

        try {
            $depositPath = $this->filePaths->getHarvestFile($deposit);
            $checksumValue = $this->hashFile($deposit->getChecksumType(), $depositPath);
            if ($checksumValue !== $deposit->getChecksumValue()) {
                throw new Exception("Deposit checksum does not match. Expected {$deposit->getChecksumValue()} != Actual {$checksumValue}");
            }
            return true;
        } catch (Exception $e) {
            $deposit->addToProcessingLog($e->getMessage());
            return false;
        }
        return true;
    }

}
