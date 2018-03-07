<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Utilities;

use DOMDocument;
use Exception;

/**
 * Description of XmlParser
 */
class XmlParser {
    
    const LIBXML_OPTS = LIBXML_COMPACT | LIBXML_PARSEHUGE;
    
    private $errors;
    
    public function __construct() {
        $this->errors = array();
    }
    
    public function hasErrors() {
        return count($this->errors) > 0;
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
    public function fromFile($filename) {
        $dom = new DOMDocument();
        try {
            $dom->load($filename, self::LIBXML_OPTS);
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'Input is not proper UTF-8') === false) {
                throw $ex;
            }
            $filteredFilename = tempnam(sys_get_temp_dir(), 'pkppln-');
            $changes = $this->filter($filename, $filteredFilename);
            $this->errors[] = basename($filename) . " contains {$changes} invalid UTF-8 characters, which have been removed.";
            $dom->load($filteredFilename, self::LIBXML_OPTS);
        }
        return $dom;
    }
    
}
