<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\Services\Processing\VirusScanner;
use AppBundle\Utilities\XmlParser;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Socket\Raw\Factory;
use Socket\Raw\Socket;
use Xenolope\Quahog\Client;

/**
 * This test makes use of the EICAR test signature found here:
 * http://www.eicar.org/86-0-Intended-use.html
 */
class VirusScannerTest extends BaseTestCase {
    
    /**
     * @var VirusScanner
     */
    private $scanner;
        
    protected function setUp() {
        parent::setUp();        
        $this->scanner = $this->container->get(VirusScanner::class);
        $this->socketPath = $this->container->getParameter('pln.clamd_socket');
    }
    
    public function testInstance() {
        $this->assertInstanceOf(VirusScanner::class, $this->scanner);
    }
    
    public function testGetClient() {
        $factory = $this->createMock(Factory::class);
        $factory->method('createClient')->willReturn(new Socket(null));
        $this->scanner->setFactory($factory);
        $client = $this->scanner->getClient();
        $this->assertInstanceOf(Client::class, $client);
    }
    
    public function testScanEmbed() {
        $embed = new DOMElement('unused');
        $xp = $this->createMock(DOMXPath::class);
        $xp->method('evaluate')->will($this->onConsecutiveCalls(10, base64_encode("We're fine. We're all fine here, now, thank you. How are you?")));
        $client = $this->createMock(Client::class);
        $client->method('scanResourceStream')->willReturn([
            'filename' => 'stream',
            'reason' => null,
            'status' => Client::RESULT_OK,
        ]);
        $result = $this->scanner->scanEmbed($embed, $xp, $client);
        $this->assertEquals([
            'filename' => 'stream',
            'reason' => null,
            'status' => Client::RESULT_OK,
        ], $result);
    }
    
    public function testScanEmbedEicar() {
        $embed = new DOMElement('unused');
        $xp = $this->createMock(DOMXPath::class);
        $xp->method('evaluate')->will($this->onConsecutiveCalls(10, base64_encode('X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*')));
        $client = $this->createMock(Client::class);
        $client->method('scanResourceStream')->willReturn([
            'filename' => 'stream',
            'reason' => 'EICAR',
            'status' => Client::RESULT_FOUND,
        ]);
        $result = $this->scanner->scanEmbed($embed, $xp, $client);
        $this->assertEquals([
            'filename' => 'stream',
            'reason' => 'EICAR',
            'status' => Client::RESULT_FOUND,
        ], $result);
    }
    
    /**
     * @group virusscanner
     */
    public function testLiveScanEmbed() {
        $embed = new DOMElement('unused');
        $xp = $this->createMock(DOMXPath::class);
        $xp->method('evaluate')->will($this->onConsecutiveCalls(10, base64_encode("We're fine. We're all fine here, now, thank you. How are you?")));
        
        $result = $this->scanner->scanEmbed($embed, $xp, $this->scanner->getClient());
        $this->assertEquals([
            'id' => '1',
            'filename' => 'stream',
            'reason' => null,
            'status' => Client::RESULT_OK,
        ], $result);
    }
    
    /**
     * @group virusscanner
     */
    public function testLiveScanEmbedEicar() {
        $embed = new DOMElement('unused');
        $xp = $this->createMock(DOMXPath::class);
        $xp->method('evaluate')->will($this->onConsecutiveCalls(10, base64_encode('X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*')));
        $result = $this->scanner->scanEmbed($embed, $xp, $this->scanner->getClient());
        $this->assertEquals([
            'id' => '1',
            'filename' => 'stream',
            'reason' => 'Eicar-Test-Signature',
            'status' => Client::RESULT_FOUND,
        ], $result);
    }
    
    public function testScanCleanXmlFile() {
        $dom = new DOMDocument();
        $dom->loadXML($this->getCleanXml());
        $parser = $this->createMock(XmlParser::class);
        $parser->method('fromFile')->willReturn($dom);
        $client = $this->createMock(Client::class);
        $client->method('scanResourceStream')->willReturn([
            'filename' => 'stream',
            'reason' => null,
            'status' => Client::RESULT_OK,
        ]);
        $result = $this->scanner->scanXmlFile('foo', $client, $parser);
        $this->assertEquals(['file1 OK', 'file2 OK'], $result);
    }

    public function testScanDirtyXmlFile() {
        $dom = new DOMDocument();
        $dom->loadXML($this->getDirtyXml());
        $parser = $this->createMock(XmlParser::class);
        $parser->method('fromFile')->willReturn($dom);
        $client = $this->createMock(Client::class);
        $client->method('scanResourceStream')->will($this->onConsecutiveCalls([
            'filename' => 'stream',
            'reason' => null,
            'status' => Client::RESULT_OK,
        ], [
            'filename' => 'stream',
            'reason' => 'Eicar',
            'status' => Client::RESULT_FOUND,
        ]));
        $result = $this->scanner->scanXmlFile('foo', $client, $parser);
        $this->assertEquals(['file1 OK', 'file2 FOUND: Eicar'], $result);
    }

    /**
     * @group virusscanner
     */
    public function testLiveScanCleanXmlFile() {
        $dom = new DOMDocument();
        $dom->loadXML($this->getCleanXml());
        $parser = $this->createMock(XmlParser::class);
        $parser->method('fromFile')->willReturn($dom);
        $client = $this->scanner->getClient();
        $result = $this->scanner->scanXmlFile('foo', $client, $parser);
        $this->assertEquals(['file1 OK', 'file2 OK'], $result);
    }

    /**
     * @group virusscanner
     */
    public function testLiveScanDirtyXmlFile() {
        $dom = new DOMDocument();
        $dom->loadXML($this->getDirtyXml());
        $parser = $this->createMock(XmlParser::class);
        $parser->method('fromFile')->willReturn($dom);
        $client = $this->scanner->getClient();
        $result = $this->scanner->scanXmlFile('foo', $client, $parser);
        $this->assertEquals(['file1 OK', 'file2 FOUND: Eicar-Test-Signature'], $result);
    }

    public function getCleanXml() {
        $xml = <<<'ENDXML'
<root>
    <!-- All good. -->
    <embed filename='file1'>QWxsIGdvb2QuCg==</embed>
    <!-- Ooh, an EICAR test signature -->
    <embed filename='file2'>Y2hlZXNlIGlzIHRoZSBiZXN0Cg==</embed>
</root>                
ENDXML;
        return $xml;
    }
    
    public function getDirtyXml() {
        $xml = <<<'ENDXML'
<root>
    <!-- All good. -->
    <embed filename='file1'>QWxsIGdvb2QuCg==</embed>
    <!-- Ooh, an EICAR test signature -->
    <embed filename='file2'>WDVPIVAlQEFQWzRcUFpYNTQoUF4pN0NDKTd9JEVJQ0FSLVNUQU5EQVJELUFOVElWSVJVUy1URVNULUZJTEUhJEgrSCo=</embed>
</root>                
ENDXML;
        return $xml;
    }
    
}