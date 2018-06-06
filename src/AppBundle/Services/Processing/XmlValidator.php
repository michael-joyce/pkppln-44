<?php

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\DtdValidator;
use AppBundle\Services\FilePaths;
use AppBundle\Utilities\BagReader;
use AppBundle\Utilities\XmlParser;
use DOMDocument;
use Exception;

/**
 * Validate the OJS XML export.
 *
 * @todo Rewrite this to use XmlParser.
 */
class XmlValidator {

    /**
     * The PKP Public Identifier for OJS export XML.
     */
    const PKP_PUBLIC_ID = '-//PKP//OJS Articles and Issues XML//EN';

    /**
     * Block size for reading very large files.
     */
    const BLOCKSIZE = 64 * 1023;

    /**
     * Calculate file path locations.
     *
     * @var FilePaths
     */
    private $filePaths;

    /**
     * Validator service.
     *
     * @var DtdValidator
     */
    private $validator;

    /**
     * Parser for XML files.
     *
     * @var XmlParser
     */
    private $xmlParser;

    /**
     * Bag Reader.
     *
     * @var BagReader
     */
    private $bagReader;

    /**
     * Build the validator.
     *
     * @param FilePaths $filePaths
     * @param DtdValidator $validator
     */
    public function __construct(FilePaths $filePaths, DtdValidator $validator) {
        $this->filePaths = $filePaths;
        $this->validator = $validator;
        $this->xmlParser = new XmlParser();
        $this->bagReader = new BagReader();
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
     * Override the default Xml Parser.
     *
     * @param XmlParser $xmlParser
     */
    public function setXmlParser(XmlParser $xmlParser) {
        $this->xmlParser = $xmlParser;
    }

    /**
     * Add any errors to the report.
     *
     * @param array $errors
     * @param string $report
     */
    public function reportErrors(array $errors, &$report) {
        foreach ($errors as $error) {
            $report .= "On line {$error['line']}: {$error['message']}\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processDeposit(Deposit $deposit) {
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);
        $bag = $bag = $this->bagReader->readBag($harvestedPath);
        $report = '';

        foreach ($bag->getBagContents() as $filename) {
            if (substr($filename, -4) !== '.xml') {
                continue;
            }
            $dom = $this->xmlParser->fromFile($filename);
            $this->validator->validate($dom, $report);
            $this->reportErrors($this->validator->getErrors(), $report);
        }
        if (trim($report)) {
            $deposit->addToProcessingLog($report);
            return false;
        }
        return true;
    }

}
