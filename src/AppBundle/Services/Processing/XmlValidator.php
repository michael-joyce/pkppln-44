<?php

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\DtdValidator;
use AppBundle\Services\FilePaths;
use BagIt;
use DOMDocument;
use Exception;

/**
 * Validate the OJS XML export.
 */
class XmlValidator {
    /**
     * The PKP Public Identifier for OJS export XML.
     */
    const PKP_PUBLIC_ID = '-//PKP//OJS Articles and Issues XML//EN';

    const BLOCKSIZE = 64 * 1023;
    
    /**
     * @var FilePaths
     */
    private $filePaths;
    
    /**
     * @var DtdValidator
     */
    private $validator;
    
    public function __construct(FilePaths $filePaths, DtdValidator $validator) {
        $this->filePaths = $filePaths;
        $this->validator = $validator;
    }
    
    /**
     * Filter out any invalid UTF-8 data in $from and write the result to $to.
     * 
     * @param string $from
     * @param string $to
     * @return int 
     *   The number of invalid bytes filtered out.
     */
    public function filter($from, $to) {
        $fromHandle = fopen($from, 'rb');
        $toHandle = fopen($to, 'wb');
        $changes = 0;
        while($buffer = fread($fromHandle, self::BLOCKSIZE)) {
            $filtered = iconv('UTF-8', 'UTF-8//IGNORE', $buffer);
            $changes += (strlen($buffer) - strlen($filtered));
            fwrite($toHandle);
        }
        return $changes;
    }
    
    /**
     * Load the XML document into a DOM and return it. Errors are appended to
     * the $report parameter.
     *
     * For reasons beyond anyone's apparent control, the export may contain
     * invalid UTF-8 characters. If the file cannot be parsed as XML, the
     * function will attempt to filter out invalid UTF-8 characters and then
     * try to load the XML again.
     *
     * Other errors in the XML, beyond the bad UTF-8, will not be tolerated.
     *
     * @return DOMDocument
     *
     * @param string $filename
     * @param string $report
     */
    public function loadXml($filename, &$report) {
        $dom = new DOMDocument();
        try {
            $dom->load($filename, LIBXML_COMPACT | LIBXML_PARSEHUGE);
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'Input is not proper UTF-8') === false) {
                throw $ex;
            }
            // The XML files can be arbitrarily large, so stream them, filter
            // the stream, and write to disk. The result may not fit in memory.
            $filteredFilename = "{$filename}-filtered.xml";
            $changes = $this->filter($filename, $filteredFilename);
            $report .= basename($filename) . " contains {$changes} invalid UTF-8 characters, which have been removed.";
            $report .= basename($filteredFilename) . " will be validated.\n";
            $dom->load($filteredFilename, LIBXML_COMPACT | LIBXML_PARSEHUGE);
        }
        return $dom;
    }
    
    public function reportErrors($errors, &$report) {
        foreach($errors as $error) {
            $report .= "On line {$error['line']}: {$error['message']}\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processDeposit(Deposit $deposit) {
        $extractedPath = $this->filePaths->getHarvestFile($deposit);
        $bag = new BagIt($extractedPath);
        $report = '';
        
        foreach ($bag->getBagContents() as $filename) {
            if (substr($filename, -4) !== '.xml') {
                continue;
            }
            $dom = $this->loadXml($filename, $report);
            $this->validator->validate($dom, $report);
            $this->reportErrors($this->validator->getErrors(), $report);
        }
        if(trim($report)) {
            $deposit->addToProcessingLog($report);
            return false;
        }
        return true;
    }

}
