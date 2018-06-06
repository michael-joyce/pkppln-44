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
     * @param string $xpath
     * @param string $default
     *
     * @return string
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
     * @param string $xpath
     *
     * @return array
     */
    public static function query(SimpleXMLElement $xml, $xpath) {
        return $xml->xpath($xpath);
    }

}
