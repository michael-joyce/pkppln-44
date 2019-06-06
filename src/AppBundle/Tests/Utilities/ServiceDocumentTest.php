<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Utilities;

use AppBundle\Utilities\ServiceDocument;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Description of PingResultTest
 */
class ServiceDocumentTest extends TestCase {

    /**
     * @var ServiceDocument
     */
    private $sd;

    protected function setup() : void {
        parent::setUp();
        $this->sd = new ServiceDocument($this->getXml());
    }

    public function testInstance() {
        $this->assertInstanceOf(ServiceDocument::class, $this->sd);
    }

    /**
     * @dataProvider getXpathValueData
     */
    public function testGetXpathValue($expected, $query) {
        $value = $this->sd->getXpathValue($query);
        $this->assertEquals($expected, $value);
    }

    public function getXpathValueData() {
        return [
            [2.0, '/app:service/sword:version'],
            [null, '/foo/bar'],
        ];
    }

    public function testGetXpathValueException() {
        $this->expectException(Exception::class);
        $this->sd->getXpathValue('/app:service/node()');
    }

    /**
     * @dataProvider valueData
     */
    public function testValue($expected, $method) {
        $this->assertEquals($expected, $this->sd->$method());
    }

    public function valueData() {
        return array(
            [10000, 'getMaxUpload'],
            ['SHA1 MD5', 'getUploadChecksum'],
            ['http://example.com/path/to/sd', 'getCollectionUri'],
        );
    }

    public function testToString() {
        $string = (string)$this->sd;
        $this->assertStringContainsStringIgnoringCase('LOCKSSOMatic', $string);
    }

    private function getXml() {
        $data = <<<'ENDXML'
<service xmlns:dcterms="http://purl.org/dc/terms/"
         xmlns:sword="http://purl.org/net/sword/"
         xmlns:atom="http://www.w3.org/2005/Atom"
         xmlns:lom="http://lockssomatic.info/SWORD2"
         xmlns="http://www.w3.org/2007/app">
    <sword:version>2.0</sword:version>
    <sword:maxUploadSize>10000</sword:maxUploadSize>
    <lom:uploadChecksumType>SHA1 MD5</lom:uploadChecksumType>
    <workspace>
        <atom:title>LOCKSSOMatic</atom:title>
        <collection href="http://example.com/path/to/sd">
            <lom:pluginIdentifier id="com.example.text"/>
            <atom:title>Site Title</atom:title>
            <accept>application/atom+xml;type=entry</accept>
            <sword:mediation>true</sword:mediation>
        </collection>
    </workspace>
</service>
ENDXML;
        return $data;
    }

}
