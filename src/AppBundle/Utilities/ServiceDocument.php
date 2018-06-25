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
 * Wrapper around a SWORD service document.
 */
class ServiceDocument {

    /**
     * XML from the document.
     *
     * @var SimpleXMLElement
     */
    private $xml;

    /**
     * Construct the object.
     *
     * @param string $data
     */
    public function __construct($data) {
        $this->xml = new SimpleXMLElement($data);
        Namespaces::registerNamespaces($this->xml);
    }

    /**
     * Get a single value from the document based on the XPath query $xpath.
     *
     * @param string $xpath
     *
     * @return null|string
     * 
     * @throws Exception if the query results in multiple values.
     */
    public function getXpathValue($xpath) {
        $result = $this->xml->xpath($xpath);
        if (count($result) === 0) {
            return null;
        }
        if (count($result) > 1) {
            throw new Exception("Too many values returned by xpath query.");
        }
        return (string) $result[0];
    }

    /**
     * Get the maximum upload size.
     *
     * @return string
     */
    public function getMaxUpload() {
        return $this->getXpathValue('sword:maxUploadSize');
    }

    /**
     * Get the upload checksum type.
     *
     * @return string
     */
    public function getUploadChecksum() {
        return $this->getXpathValue('lom:uploadChecksumType');
    }

    /**
     * Get the collection URI from the service document.
     *
     * @return string
     */
    public function getCollectionUri() {
        return $this->getXpathValue('.//app:collection/@href');
    }

    /**
     * Return the XML for the document.
     *
     * @return string
     */
    public function __toString() {
        return $this->xml->asXML();
    }

}
