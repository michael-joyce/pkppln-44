<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Services\Processing\Harvester;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Description of HarvesterTest
 */
class HarvesterTest extends BaseTestCase {

    private $harvester;
    
    protected function setUp() {
        parent::setUp();
        $this->harvester = $this->container->get(Harvester::class);
    }
    
    public function testInstance() {
        $this->assertInstanceOf(Harvester::class, $this->harvester);
    }
    
    public function testWriteDeposit() {
        $body = $this->createMock(StreamInterface::class);
        $body->method('read')->will($this->onConsecutiveCalls('abc', 'def', ''));        
        $response = $this->createMock(Response::class);
        $response->method('getBody')->willReturn($body);
        $fs = $this->createMock(Filesystem::class);
        
        $output = '';
        $fs->method('appendToFile')->will($this->returnCallback(function($path, $bytes) use(&$output) {
            $output .= $bytes;
            return null;
        }));
        $this->harvester->setFilesystem($fs);
        $this->harvester->writeDeposit('', $response);
        $this->assertEquals('abcdef', $output);
    }
    
    /**
     * @expectedException Exception
     */
    public function testWriteDepositNoBody() {
        $response = $this->createMock(Response::class);
        $response->method('getBody')->willReturn(null);
        $this->harvester->writeDeposit('', $response);
    }
    
    public function testFetchDeposit() {
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        
        $response = $this->harvester->fetchDeposit('http://example.com');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    /**
     * @expectedException Exception
     */
    public function testDepositException() {
        $mock = new MockHandler([
            new Response(404),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        $this->harvester->fetchDeposit('http://example.com');
        $this->fail('No exception thrown.');
    }
    
    public function testDepositRedirect() {
        $mock = new MockHandler([
            new Response(302, ['Location' => 'http://example.com/path']),
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        $response = $this->harvester->fetchDeposit('http://example.com');
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testCheckSize() {
        $deposit = new Deposit();
        $deposit->setSize(1);
        $deposit->setUrl('http://example.com/deposit');
        
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 1024]),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        $this->harvester->checkSize($deposit);
        $this->assertNotContains('Expected file size', $deposit->getErrorLog());
    }
    
    /**
     * @expectedException Exception
     */
    public function testCheckSizeBadResponse() {
        $deposit = new Deposit();
        $deposit->setSize(1);
        $deposit->setUrl('http://example.com/deposit');
        
        $mock = new MockHandler([
            new Response(500),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        $this->harvester->checkSize($deposit);
    }
    
    /**
     * @expectedException Exception
     */
    public function testCheckSizeContentLengthMissing() {
        $deposit = new Deposit();
        $deposit->setSize(1);
        $deposit->setUrl('http://example.com/deposit');
        
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        $this->harvester->checkSize($deposit);
    }
    
    
    /**
     * @expectedException Exception
     */
    public function testCheckSizeContentLengthZero() {
        $deposit = new Deposit();
        $deposit->setSize(100);
        $deposit->setUrl('http://example.com/deposit');
        
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0]),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        $this->harvester->checkSize($deposit);
    }
    
    /**
     * @expectedException Exception
     */
    public function testCheckSizeContentLengthMismatch() {
        $deposit = new Deposit();
        $deposit->setSize(100);
        $deposit->setUrl('http://example.com/deposit');
        
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 10240]),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        $this->harvester->checkSize($deposit);
    }
    
    public function testProcessDeposit() {
        $fs = $this->createMock(Filesystem::class);
        $fs->method('appendToFile')->willReturn(null);
        $this->harvester->setFilesystem($fs);
        
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 1000]), # head request
            new Response(200, ['Content-Length' => 1000], 'abcdef'), # get request
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        
        $deposit = new Deposit();
        $deposit->setUrl('http://example.com/path');
        $deposit->setSize(1);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $deposit->setJournal($journal);
        $result = $this->harvester->processDeposit($deposit);
        $this->assertEquals('', $deposit->getProcessingLog());
        $this->assertTrue($result);
    }
    
    public function testProcessDepositFailue() {
        $fs = $this->createMock(Filesystem::class);
        $fs->method('appendToFile')->willReturn(null);
        $this->harvester->setFilesystem($fs);
        
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 1000]), # head request
            new Response(200, ['Content-Length' => 1000], 'abcdef'), # get request
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->harvester->setClient($client);
        
        $deposit = new Deposit();
        $deposit->setUrl('http://example.com/path');
        $deposit->setSize(1000);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $deposit->setJournal($journal);
        $result = $this->harvester->processDeposit($deposit);
        $this->assertContains('Expected file size', $deposit->getProcessingLog());
        $this->assertNull($result);
    }
    
    public function testProcessDepositTooManyFails() {
        $deposit = new Deposit();
        $deposit->setHarvestAttempts(13);
        $result = $this->harvester->processDeposit($deposit);
        $this->assertEquals('harvest-error', $deposit->getState());
        $this->assertFalse($result);
    }
    
    
}
