<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Deposit;
use AppBundle\Services\DepositBuilder;
use AppBundle\Utilities\Namespaces;
use DateTime;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of DepositBuilderTest
 */
class DepositBuilderTest extends BaseTestCase {

    private $deposit;
    
    protected function setUp() {
        parent::setUp();
        $builder = $this->container->get(DepositBuilder::class);
        $this->deposit = $builder->fromXml($this->getReference('journal.1'), $this->getXml());
    }
    
    public function getFixtures() {
        return array(
            LoadJournal::class,
            LoadDeposit::class,
        );
    }

    public function testInstance() {
        $this->assertInstanceOf(DepositBuilder::class, $this->container->get(DepositBuilder::class));
    }
    
    public function testBuildInstance() {
        $this->assertInstanceOf(Deposit::class, $this->deposit);
    }
        
    public function testReceived() {
        $this->assertInstanceOf(DateTime::class, $this->deposit->getReceived());        
    }
    
    public function testProcessingLog() {
        $this->assertStringEndsWith("Deposit received.\n\n", $this->deposit->getProcessingLog());        
    }
    
    /**
     * @dataProvider depositData
     */
    public function testNewDeposit($expected, $method) {
        $this->assertEquals($expected, $this->deposit->$method());
    }
    
    public function depositData() {
        return [
            ['2.4.8', 'getJournalVersion'],
            [['publishingMode' => 'Open'], 'getLicense'],
            ['', 'getFileType'],
            ['00FD6D96-0155-43A4-97F7-2C6EE8EBFF09', 'getDepositUuid'],
            ['add', 'getAction'],
            [44, 'getVolume'],
            [4, 'getIssue'],
            [new DateTime('2015-07-14'), 'getPubDate'],
            ['sha-1', 'getChecksumType'],
            ['25B0BD51BB05C145672617FCED484C9E71EC553B', 'getChecksumValue'],
            ['http://example.com//00FD6D96-0155-43A4-97F7-2C6EE8EBFF09', 'getUrl'],
            [3613, 'getSize'],
            ['depositedByJournal', 'getState'],
            [[], 'getErrorLog'],
            [null, 'getPlnState'],
            [null, 'getPackagePath'],
            [null, 'getPackageChecksumType'],
            [null, 'getPackageChecksumValue'],
            [null, 'getDepositDate'],
            [null, 'getDepositReceipt'],
            [0, 'getHarvestAttempts'],
        ];
    }
    
    private function getXml() {
        $data = <<<'ENDXML'
<?xml version="1.0" encoding="utf-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" 
       xmlns:dcterms="http://purl.org/dc/terms/" 
       xmlns:pkp="http://pkp.sfu.ca/SWORD">
	<email>user@example.com</email>
	<title>Intl J Test</title>
	<pkp:journal_url>http://example.com/ijt</pkp:journal_url>
	<pkp:publisherName>Publisher institution</pkp:publisherName>
	<pkp:publisherUrl>http://publisher.example.com</pkp:publisherUrl>
	<pkp:issn>0000-0000</pkp:issn>
	<id>urn:uuid:00FD6D96-0155-43A4-97F7-2C6EE8EBFF09</id>
	<updated>1996-12-31T16:00:00Z</updated>
	<pkp:content size="3613" volume="44" issue="4" pubdate="2015-07-14" 
            checksumType="SHA-1" checksumValue="25b0bd51bb05c145672617fced484c9e71ec553b">
            http://example.com//00FD6D96-0155-43A4-97F7-2C6EE8EBFF09
        </pkp:content>
	<pkp:license>
            <pkp:publishingMode mode="0">Open</pkp:publishingMode>
	</pkp:license>
</entry>   
ENDXML;
        $xml = simplexml_load_string($data);
        Namespaces::registerNamespaces($xml);
        return $xml;
    }
            
}
