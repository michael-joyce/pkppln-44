<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Utilities;

use SimpleXMLElement;

/**
 * Description of ServiceDocument.
 */
class ServiceDocument {

    /**
     * @var SimpleXMLElement
     */
    private $xml;

    /**
     *
     */
    public function __construct($data) {
        $this->xml = new SimpleXMLElement($data);
        Namespaces::registerNamespaces($this->xml);
    }

    /**
     *
     */
    private function getXpathValue($xpath) {
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
     *
     */
    public function getMaxUpload() {
        return $this->getXpathValue('sword:maxUploadSize');
    }

    /**
     *
     */
    public function getUploadChecksum() {
        return $this->getXpathValue('lom:uploadChecksumType');
    }

    /**
     *
     */
    public function getCollectionUri() {
        return $this->getXpathValue('.//app:collection/@href');
    }

    /**
     *
     */
    public function __toString() {
        return $this->xml->asXML();
    }

}
