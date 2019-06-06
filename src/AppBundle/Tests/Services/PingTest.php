<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services;

use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Whitelist;
use AppBundle\Services\BlackWhiteList;
use AppBundle\Services\Ping;
use AppBundle\Utilities\PingResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of PingTest
 */
class PingTest extends BaseTestCase {

    /**
     * @var Ping
     */
    private $ping;

    /**
     *
     * @var BlackWhiteList
     */
    private $list;

    protected function setup() : void {
        parent::setUp();
        $this->ping = $this->container->get(Ping::class);
        $this->list = $this->container->get(BlackWhiteList::class);
    }

    protected function getFixtures() {
        return array(
            LoadJournal::class,
        );
    }

    public function testInstance() {
        $this->assertInstanceOf(Ping::class, $this->ping);
    }

    public function testProcessFail() {
        $result = $this->createMock(PingResult::class);
        $result->method('getHttpstatus')->willReturn(404);
        $journal = $this->getReference('journal.1');
        $this->ping->process($journal, $result);
        $this->assertEquals('ping-error', $journal->getStatus());
    }

    public function testProcessMissingRelease() {
        $result = $this->createMock(PingResult::class);
        $result->method('getHttpstatus')->willReturn(200);
        $result->method('getOjsRelease')->willReturn(false);
        $journal = $this->getReference('journal.1');
        $this->ping->process($journal, $result);
        $this->assertEquals('ping-error', $journal->getStatus());
    }

    public function testProcessOldVersion() {
        $result = $this->createMock(PingResult::class);
        $result->method('getHttpstatus')->willReturn(200);
        $result->method('getOjsRelease')->willReturn('2.4.0');
        $result->method('getJournalTitle')->willReturn('Yes Minister');
        $result->method('areTermsAccepted')->willReturn('Yes');

        $journal = $this->getReference('journal.1');
        $this->ping->process($journal, $result);
        $this->em->flush();
        $this->assertEquals('healthy', $journal->getStatus());
        $this->assertFalse($this->list->isListed($journal->getUuid()));
    }

    public function testProcessListed() {
        $result = $this->createMock(PingResult::class);
        $result->method('getHttpstatus')->willReturn(200);
        $result->method('getOjsRelease')->willReturn('2.4.9');
        $result->method('getJournalTitle')->willReturn('Yes Minister');
        $result->method('areTermsAccepted')->willReturn('Yes');

        $journal = $this->getReference('journal.1');
        $whitelist = new Whitelist();
        $whitelist->setUuid($journal->getUuid());
        $whitelist->setComment("testing.");
        $this->em->persist($whitelist);
        $this->em->flush();

        $this->ping->process($journal, $result);
        $this->em->flush();
        $this->assertEquals('healthy', $journal->getStatus());
    }

    public function testProcessSuccess() {
        $result = $this->createMock(PingResult::class);
        $result->method('getHttpstatus')->willReturn(200);
        $result->method('getOjsRelease')->willReturn('2.4.9');
        $result->method('getJournalTitle')->willReturn('Yes Minister');
        $result->method('areTermsAccepted')->willReturn('Yes');

        $journal = $this->getReference('journal.1');
        $this->ping->process($journal, $result);
        $this->em->flush();
        $this->assertEquals('healthy', $journal->getStatus());
        $this->assertTrue($this->list->isListed($journal->getUuid()));
    }

    public function testPingFail() {
        $mock = new MockHandler([
            new RequestException("Bad mojo.", new Request('GET', 'http://example.com')),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->ping->setClient($client);
        $journal = $this->getReference('journal.1');
        $this->ping->ping($journal);
        $this->assertEquals('ping-error', $journal->getStatus());
    }

    public function testPingSuccess() {
        $mock = new MockHandler([
            new Response(200, [], $this->getXml()),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->ping->setClient($client);
        $journal = $this->getReference('journal.1');
        $this->ping->ping($journal);
        $this->em->flush();
        $this->assertEquals('healthy', $journal->getStatus());
        $this->assertTrue($this->list->isListed($journal->getUuid()));
    }

    private function getXml() {
        $data = <<<'ENDXML'
<?xml version="1.0" ?> 
<plnplugin>
  <ojsInfo>
    <release>2.4.8.1</release>
  </ojsInfo>
  <pluginInfo>
    <release>1.2.0.0</release>
    <releaseDate>2015-07-13</releaseDate>
    <current>1</current>
    <prerequisites>
      <phpVersion>5.6.11-1ubuntu3.4</phpVersion>
      <curlVersion>7.43.0</curlVersion>
      <zipInstalled>yes</zipInstalled>
      <tarInstalled>yes</tarInstalled>
      <acron>yes</acron>
      <tasks>no</tasks>
    </prerequisites>
    <terms termsAccepted="yes">
      <term key="foo" updated="2015-11-30 18:34:43+00:00" accepted="2018-02-17T04:00:15+00:00">
        This is a term.
      </term>
    </terms>
  </pluginInfo>
  <journalInfo>
    <title>Publication of Soft Cheeses</title>
    <articles count="12">
      <article pubDate="2017-12-26 13:56:20">
        Brie
      </article>
      <article pubDate="2017-12-26 13:56:20">
        Coulommiers
      </article>
    </articles>
  </journalInfo>
</plnplugin>
ENDXML;
        return $data;
    }

}
