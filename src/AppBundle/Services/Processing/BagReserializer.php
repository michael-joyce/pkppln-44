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
use AppBundle\Services\FilePaths;
use AppBundle\Utilities\BagReader;
use BagIt;

/**
 * Take a processed bag and reserialize it.
 */
class BagReserializer {

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
     * Construct the reserializer service.
     *
     * @param FilePaths $fp
     * @param BagReader $bagReader
     */
    public function __construct(FilePaths $fp, BagReader $bagReader) {
        $this->bagReader = $bagReader;
        $this->filePaths = $fp;
    }

    /**
     * Override the default bag reader.
     *
     * @param BagReader $bagReader
     */
    public function setBagReader(BagReader $bagReader) {
        $this->bagReader = $bagReader;
    }

    /**
     * Add the metadata from the database to the bag-info.txt file.
     *
     * @param BagIt $bag
     * @param Deposit $deposit
     */
    protected function addMetadata(BagIt $bag, Deposit $deposit) {
        // @todo this is very very bad. Once BagItPHP is updated it should be $bag->clearAllBagInfo();
        $bag->bagInfoData = array();
        $bag->setBagInfoData('External-Identifier', $deposit->getDepositUuid());
        $bag->setBagInfoData('PKP-PLN-Deposit-UUID', $deposit->getDepositUuid());
        $bag->setBagInfoData('PKP-PLN-Deposit-Received', $deposit->getReceived()->format('c'));
        $bag->setBagInfoData('PKP-PLN-Deposit-Volume', $deposit->getVolume());
        $bag->setBagInfoData('PKP-PLN-Deposit-Issue', $deposit->getIssue());
        $bag->setBagInfoData('PKP-PLN-Deposit-PubDate', $deposit->getPubDate()->format('c'));

        $journal = $deposit->getJournal();
        $bag->setBagInfoData('PKP-PLN-Journal-UUID', $journal->getUuid());
        $bag->setBagInfoData('PKP-PLN-Journal-Title', $journal->getTitle());
        $bag->setBagInfoData('PKP-PLN-Journal-ISSN', $journal->getIssn());
        $bag->setBagInfoData('PKP-PLN-Journal-URL', $journal->getUrl());
        $bag->setBagInfoData('PKP-PLN-Journal-Email', $journal->getEmail());
        $bag->setBagInfoData('PKP-PLN-Publisher-Name', $journal->getPublisherName());
        $bag->setBagInfoData('PKP-PLN-Publisher-URL', $journal->getPublisherUrl());

        foreach ($deposit->getLicense() as $key => $value) {
            $bag->setBagInfoData('PKP-PLN-' . $key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processDeposit(Deposit $deposit) {
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);
        $bag = $this->bagReader->readBag($harvestedPath);
        $bag->createFile($deposit->getProcessingLog(), 'data/processing-log.txt');
        $bag->createFile($deposit->getErrorLog("\n\n"), 'data/error-log.txt');
        $this->addMetadata($bag, $deposit);
        $bag->update();

        $path = $this->filePaths->getStagingBagPath($deposit);
        if (file_exists($path)) {
            unlink($path);
        }

        $bag->package($path, 'zip');
        // Bytes to kb.
        $deposit->setPackageSize(ceil(filesize($path) / 1000));
        $deposit->setPackageChecksumType('sha1');
        $deposit->setPackageChecksumValue(hash_file('sha1', $path));

        return true;
    }

}
