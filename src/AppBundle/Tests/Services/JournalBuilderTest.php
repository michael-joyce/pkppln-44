<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services;

use AppBundle\Entity\Journal;
use AppBundle\Services\JournalBuilder;
use AppBundle\Utilities\Namespaces;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of JournalBuilderTest
 */
class JournalBuilderTest extends BaseTestCase {
    
    /**
     * @var Journal
     */
    private $journal;
    
    protected function setUp() {
        parent::setUp();
        $builder = $this->container->get(JournalBuilder::class);
        $xml = $this->getXml();
        $this->journal = $builder->fromXml($xml, 'B99FE131-48B5-440A-A552-4F1BF2BFDE82');
    }
    
    public function testInstance() {
        $this->assertInstanceOf(JournalBuilder::class,  $this->container->get(JournalBuilder::class));
    }
    
    public function testResultInstance() {
        $this->assertInstanceOf(Journal::class, $this->journal);
    }
    
    /**
     * @dataProvider journalData
     */
    public function testJournal($expected, $method) {
        $this->assertEquals($expected, $this->journal->$method());
    }
    
    public function journalData() {
        return [
            ['B99FE131-48B5-440A-A552-4F1BF2BFDE82', 'getUuid'],
            [null, 'getContacted'],
            [null, 'getOjsVersion'],
            [null, 'getNotified'],
            ['Intl J Test', 'getTitle'],
            ['0000-0000', 'getIssn'],
            ['http://example.com/ijt', 'getUrl'],
            ['healthy', 'getStatus'],
            [null, 'getTermsAccepted'],
            ['user@example.com', 'getEmail'],
            ['Publisher institution', 'getPublisherName'],
            ['http://publisher.example.com', 'getPublisherUrl'],
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
            http://ojs.dv/index.php/ijt/pln/deposits/00FD6D96-0155-43A4-97F7-2C6EE8EBFF09
        </pkp:content>
	<pkp:license>
            <pkp:openAccessPolicy>Yes.</pkp:openAccessPolicy>
            <pkp:licenseURL>http://creativecommons.org/licenses/by-nc-sa/4.0</pkp:licenseURL>
            <pkp:publishingMode mode="0">Open</pkp:publishingMode>
            <pkp:copyrightNotice>This is a copyright notice.</pkp:copyrightNotice>
            <pkp:copyrightBasis>article</pkp:copyrightBasis>
            <pkp:copyrightHolder>author</pkp:copyrightHolder>
	</pkp:license>
</entry>   
ENDXML;
        $xml = simplexml_load_string($data);
        Namespaces::registerNamespaces($xml);
        return $xml;
    }
    
}