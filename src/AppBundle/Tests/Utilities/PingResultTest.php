<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Utilities;

use AppBundle\Utilities\PingResult;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Description of PingResultTest
 */
class PingResultTest extends TestCase {

    /**
     * @var PingResult
     */
    private $result;
    
    protected function setUp() {
        parent::setUp();
        $mock = $this->createMock(ResponseInterface::class);
        $mock->method('getBody')->willReturn($this->getXml());
        $mock->method('getStatusCode')->willReturn(200);
        $mock->method('getHeader')->willReturn('Validated');
        $this->result = new PingResult($mock);
    }
    
    public function testInstance() {
        $this->assertInstanceOf(PingResult::class, $this->result);        
    }
    
    public function testHttpStatus() {
        $this->assertEquals(200, $this->result->getHttpStatus());
    }
    
    public function testError() {
        $this->assertFalse($this->result->hasError());
        $this->assertNull($this->result->getError());
    }
    
    public function testGetBody() {
        $this->assertContains('1.2.0.0', $this->result->getBody());
        $this->assertContains('1.2.0.0', $this->result->getBody(true));
        $this->assertContains('1.2.0.0', $this->result->getBody(false));
    }
    
    public function testXml() {
        $this->assertTrue($this->result->hasXml());
        $this->assertInstanceOf(\SimpleXMLElement::class, $this->result->getXml());
    }
    
    public function testGetHeader() {
        $this->assertEquals('Validated', $this->result->getHeader('foo'));
    }
    
    public function testGetOjsRelease() {
        $this->assertEquals('2.4.8.1', $this->result->getOjsRelease());
    }
    
    public function testGetPluginReleaseVersion() {
        $this->assertEquals('1.2.0.0', $this->result->getPluginReleaseVersion());
    }
    
    public function testPluginReleaseDate() {
        $this->assertEquals('2015-07-13', $this->result->getPluginReleaseDate());
    }
    
    public function testPluginCurrent() {
        $this->assertEquals(1, $this->result->isPluginCurrent());
    }
    
    public function testTermsAccepted() {
        $this->assertEquals('yes', $this->result->areTermsAccepted());
    }
    
    public function testJournalTitle() {
        $this->assertEquals('Publication of Soft Cheeses', $this->result->getJournalTitle());
    }
    
    public function testArticleCount() {
        $this->assertEquals(12, $this->result->getArticleCount());
    }
    
    public function testArticleTitles() {
        $expected = [
            ['date' => '2017-12-26 13:56:20', 'title' => 'Brie'],
            ['date' => '2017-12-26 13:56:20', 'title' => 'Coulommiers'],
        ];
        $this->assertEquals($expected, $this->result->getArticleTitles());
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
