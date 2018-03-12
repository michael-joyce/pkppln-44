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
 * Wrapper around some XML parsing.
 */
class XmlParser {

    /**
     * Options passed to LibXML.
     */
    const LIBXML_OPTS = LIBXML_COMPACT | LIBXML_PARSEHUGE;
    
    /**
     * Block size for streaming.
     */
    const BLOCKSIZE = 64 * 1024;

    /**
     * List of errors in parsing.
     *
     * @var array
     */
    private $errors;

    /**
     * Build the parser.
     */
    public function __construct() {
        $this->errors = array();
    }

    /**
     * Check if the parse generated errors.
     *
     * @return bool
     *   True if the parse generated errors.
     */
    public function hasErrors() {
        return count($this->errors) > 0;
    }

    /**
     * Filter out any invalid UTF-8 data in $from and write the result to $to.
     *
     * @param string $from
     *   Path to the source file.
     * @param string $to
     *   Path to the destination file.
     *
     * @return int
     *   The number of invalid bytes filtered out.
     */
    public function filter($from, $to) {
        $fromHandle = fopen($from, 'rb');
        $toHandle = fopen($to, 'wb');
        $changes = 0;
        while ($buffer = fread($fromHandle, self::BLOCKSIZE)) {
            $filtered = iconv('UTF-8', 'UTF-8//IGNORE', $buffer);
            $changes += (strlen($buffer) - strlen($filtered));
            fwrite($toHandle, $filtered);
        }
        return $changes;
    }

    /**
     * Load the XML document into a DOM and return it.
     *
     * Errors are appended to the $report parameter.
     *
     * For reasons beyond anyone's apparent control, the export may contain
     * invalid UTF-8 characters. If the file cannot be parsed as XML, the
     * function will attempt to filter out invalid UTF-8 characters and then
     * try to load the XML again.
     *
     * Other errors in the XML, beyond the bad UTF-8, will not be tolerated.
     *
     * @param string $filename
     *   Path to the source file.
     *
     * @return DOMDocument
     *   Parsed XML document in a DOM.
     */
    public function fromFile($filename) {
        $dom = new DOMDocument("1.0", "UTF-8");
        libxml_use_internal_errors(true);
        $originalResult = $dom->load($filename, self::LIBXML_OPTS);
        if ($originalResult === true) {
            return $dom;
        }
        $error = libxml_get_last_error();
        if (strpos($error->message, 'Input is not proper UTF-8') === false) {
            throw new Exception("{$error->message} at {$error->file}:{$error->line}:{$error->column}.");
        }
        $filteredFilename = tempnam(sys_get_temp_dir(), 'pkppln-');
        $changes = $this->filter($filename, $filteredFilename);
        $this->errors[] = basename($filename) . " contains {$changes} invalid "
        . "UTF-8 characters, which have been removed.";
        $filteredResult = $dom->load($filteredFilename, self::LIBXML_OPTS);
        if ($filteredResult === true) {
            return $dom;
        }
        $filteredError = libxml_get_last_error();
        throw new Exception("Filtered XML cannot be parsed. {$filteredError->message} at "
        . "{$filteredError->file}:{$filteredError->line}:{$filteredError->column}.");
    }

}
