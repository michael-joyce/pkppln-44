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
use AppBundle\Services\FilePaths;
use AppBundle\Services\SwordClient;
use AppBundle\Utilities\ServiceDocument;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use org\bovigo\vfs\vfsStream;
use SimpleXMLElement;

/**
 * Description of PingTest
 */
class SwordClientTest extends BaseTestCase {

    /**
     * @var SwordClient
     */
    private $client;

    protected function setUp() {
        parent::setUp();
        $this->client = $this->container->get(SwordClient::class);
    }

    protected function getFixtures() {
        return array(
            LoadDeposit::class,
            LoadJournal::class,
        );
    }

    public function testSanity() {
        $this->assertInstanceOf(SwordClient::class, $this->client);
    }

    public function testServiceDocument() {
        $mock = new MockHandler([
            new Response(200, [], $this->serviceDocumentData())
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $sd = $this->client->serviceDocument();
        $this->assertInstanceOf(ServiceDocument::class, $sd);

        $this->assertEquals(1, count($container));
        $transaction = $container[0];
        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals(
            ['9AE14D70-B799-473C-8072-983310ECB0E1'], $transaction['request']->getHeader('On-Behalf-Of')
        );
    }

    /**
     * @expectedException Exception
     */
    public function testServiceDocumentException() {
        $mock = new MockHandler([
            new Response(400, [])
        ]);
        $stack = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $sd = $this->client->serviceDocument();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage FAILURE WILL ROBINSON
     */
    public function testServiceDocumentGenericException() {
        $mock = new MockHandler([
            new Exception("FAILURE WILL ROBINSON"),
        ]);
        $stack = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $sd = $this->client->serviceDocument();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage NO NO
     */
    public function testServiceDocumentExceptionResponse() {
        $mock = new MockHandler([
            new Response(400, [], "NO NO")
        ]);
        $stack = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $sd = $this->client->serviceDocument();
    }

    public function testCreateDeposit() {
        $mock = new MockHandler([
            new Response(200, [], $this->serviceDocumentData()),
            new Response(201, ['Location' => 'http://example.com'], $this->createDepositResponse())
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $deposit = $this->getReference('deposit.1');
        $result = $this->client->createDeposit($deposit);
        $this->assertTrue($result);

        $this->assertEquals(2, count($container));
        $request = $container[1]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals(
            'http://localhost/lom2/web/app_dev.php/api/sword/2.0/col-iri/29125DE2-E622-416C-93EB-E887B2A3126C', (string) $request->getUri()
        );

        $this->assertEquals('http://example.com', $deposit->getDepositReceipt());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage NOT AUTHORIZED
     */
    public function testCreateDepositException() {
        $mock = new MockHandler([
            new Response(200, [], $this->serviceDocumentData()),
            new Response(401, [], "NOT AUTHORIZED")
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $deposit = $this->getReference('deposit.1');
        $result = $this->client->createDeposit($deposit);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage NO FUN FOR YOU
     */
    public function testCreateDepositGenericException() {
        $mock = new MockHandler([
            new Response(200, [], $this->serviceDocumentData()),
            new Exception("NO FUN FOR YOU")
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $deposit = $this->getReference('deposit.1');
        $result = $this->client->createDeposit($deposit);
        $this->assertContains("NO FUN FOR YOU", $deposit->getErrorLog("\n"));
    }

    public function testGetDepositReceiptNull() {
        $mock = new MockHandler([
            new Response(200, [], $this->receiptData())
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $deposit = $this->getReference('deposit.1');
        $deposit->setDepositReceipt(null);
        $result = $this->client->receipt($deposit);
        $this->assertNull($result);
        $this->assertEquals(0, count($container));
    }

    public function testGetDepositReceipt() {
        $mock = new MockHandler([
            new Response(200, [], $this->receiptData())
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $deposit = $this->getReference('deposit.1');
        $result = $this->client->receipt($deposit);
        $this->assertInstanceOf(SimpleXMLElement::class, $result);

        $this->assertEquals(1, count($container));
        $request = $container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('http://example.com/receipt/1', (string) $request->getUri());
    }

    public function testGetDepositStatement() {
        $mock = new MockHandler([
            new Response(200, [], $this->receiptData()),
            new Response(200, [], $this->statementData()),
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $deposit = $this->getReference('deposit.1');
        $result = $this->client->statement($deposit);
        $this->assertInstanceOf(SimpleXMLElement::class, $result);

        $this->assertEquals(2, count($container));
        $request = $container[1]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('http://path/to/statement', (string) $request->getUri());
    }

    public function testFetch() {
        $root = vfsStream::setup();

        $fp = $this->createMock(FilePaths::class);
        $fp->method('getRestoreFile')->willReturn('vfs://root/path.zip');
        $this->client->setFilePaths($fp);

        $mock = new MockHandler([
            new Response(200, [], $this->receiptData()),
            new Response(200, [], $this->statementData()),
            new Response(200, [], 'some random content.'),
        ]);
        $container = [];
        $history = Middleware::history($container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $guzzle = new Client(['handler' => $stack]);
        $this->client->setClient($guzzle);
        $deposit = $this->getReference('deposit.1');
        $result = $this->client->fetch($deposit);
        $this->assertEquals('vfs://root/path.zip', $result);

        $this->assertEquals(3, count($container));
        $request = $container[2]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('http://path/to/deposit', (string) $request->getUri());
    }

    private function serviceDocumentData() {
        $xml = <<<'ENDXML'
<?xml version="1.0" ?>
<service xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:sword="http://purl.org/net/sword/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:lom="http://lockssomatic.info/SWORD2"
    xmlns="http://www.w3.org/2007/app">
    <sword:version>2.0</sword:version>
    <!-- sword:maxUploadSize is the maximum file size in content element, measured in kB (1,000 bytes). -->
    <sword:maxUploadSize>10000</sword:maxUploadSize>
    <lom:uploadChecksumType>SHA1</lom:uploadChecksumType>
    <workspace>
        <atom:title>LOCKSSOMatic</atom:title>
        <collection href="http://localhost/lom2/web/app_dev.php/api/sword/2.0/col-iri/29125DE2-E622-416C-93EB-E887B2A3126C">
            <lom:pluginIdentifier id="com.example.text"/>
            <atom:title>Test Provider 1</atom:title>
            <accept>application/atom+xml;type=entry</accept>
            <sword:mediation>true</sword:mediation>
        </collection>
    </workspace>
</service>
ENDXML;
        return $xml;
    }

    private function createDepositResponse() {
        $xml = <<<'ENDXML'
<entry xmlns="http://www.w3.org/2005/Atom"
       xmlns:sword="http://purl.org/net/sword/">
    <sword:treatment>Content URLs deposited to Network Test, collection Test Provider 1.</sword:treatment>
    <content src="http://localhost/lom2/web/app_dev.php/api/sword/2.0/cont-iri/29125DE2-E622-416C-93EB-E887B2A3126C/066D5E90-03F7-469E-A231-C67FB8D6109F/state"/>
    <!-- Col-IRI. -->
    <link rel="edit-media" href="http://localhost/lom2/web/app_dev.php/api/sword/2.0/col-iri/29125DE2-E622-416C-93EB-E887B2A3126C" />
    <!-- SE-IRI (can be same as Edit-IRI) -->
    <link rel="http://purl.org/net/sword/terms/add" href="http://localhost/lom2/web/app_dev.php/api/sword/2.0/cont-iri/29125DE2-E622-416C-93EB-E887B2A3126C/066D5E90-03F7-469E-A231-C67FB8D6109F/edit" />
    <!-- Edit-IRI -->
    <link rel="edit" href="http://localhost/lom2/web/app_dev.php/api/sword/2.0/cont-iri/29125DE2-E622-416C-93EB-E887B2A3126C/066D5E90-03F7-469E-A231-C67FB8D6109F/edit" />
    <!-- In LOCKSS-O-Matic, the State-IRI will be the EM-IRI/Cont-IRI with the string '/state' appended. -->
    <link rel="http://purl.org/net/sword/terms/statement" type="application/atom+xml;type=feed"
          href="http://localhost/lom2/web/app_dev.php/api/sword/2.0/cont-iri/29125DE2-E622-416C-93EB-E887B2A3126C/066D5E90-03F7-469E-A231-C67FB8D6109F/state" />
</entry>
ENDXML;
        return $xml;
    }

    private function receiptData() {
        $xml = <<<'ENDXML'
<entry xmlns="http://www.w3.org/2005/Atom"
       xmlns:sword="http://purl.org/net/sword/">

    <sword:treatment>Content URLs deposited to TestPLN.</sword:treatment>
    <content src="http://path/to/statement"/>
    <link rel="edit-media" href="http://path/to/collection" />
    <link rel="http://purl.org/net/sword/terms/add" href="http://path/to/receipt" />
    <link rel="edit" href="http://path/to/receipt" />
    <link rel="http://purl.org/net/sword/terms/statement" type="application/atom+xml;type=feed" href="http://path/to/statement" />
</entry>
ENDXML;
        return $xml;
    }

    private function statementData() {
        $xml = <<<'ENDXML'
<atom:feed xmlns:sword="http://purl.org/net/sword/terms/"
           xmlns:atom="http://www.w3.org/2005/Atom"
           xmlns:lom="http://lockssomatic.info/SWORD2">
    <atom:category scheme="http://purl.org/net/sword/terms/state"
                   term="inProgress"
                   label="State">
        The deposit has been sent to LOCKSS.
    </atom:category>
    <atom:entry>
        <atom:category scheme="http://purl.org/net/sword/terms"
                       term="http://purl.org/net/sword/terms/originalDeposit"
                       label="Original Deposit"/>
        <sword:depositedOn>Tuesday</sword:depositedOn>
        <lom:agreement>0</lom:agreement>
        <sword:originalDeposit href="http://path/to/deposit" />
    </atom:entry>
</atom:feed>
ENDXML;
        return $xml;
    }

}
