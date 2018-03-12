<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Utilities;

use Exception;
use SimpleXMLElement;

/**
 * Wrapper around some XPath functions.
 */
class Xpath {
    
    /**
     * Get a single XML value as a string.
     *
     * @param SimpleXMLElement $xml
     *   XML document with the data.
     * @param string $xpath
     *   XPath query to evaluate.
     * @param string $default
     *   Default value to return if no value is found.
     *
     * @return string
     *   The result is cast to string and returned.
     *
     * @throws Exception
     *   If there are more than one result.
     */
    public static function getXmlValue(SimpleXMLElement $xml, $xpath, $default = null) {
        $data = $xml->xpath($xpath);
        if (count($data) === 1) {
            return trim((string) $data[0]);
        }
        if (count($data) === 0) {
            return $default;
        }
        throw new Exception("Too many elements for '{$xpath}'");
    }
    
    /**
     * Query an XML document.
     *
     * @param SimpleXMLElement $xml
     *   XML document with the data.
     * @param string $xpath
     *   XPath query to evaluate.
     *
     * @return array
     *   An array of SimpleXMLElement objects or false.
     */
    public static function query(SimpleXMLElement $xml, $xpath) {
        return $xml->xpath($xpath);
    }

}
