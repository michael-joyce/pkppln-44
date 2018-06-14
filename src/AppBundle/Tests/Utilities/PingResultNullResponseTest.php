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
class PingResultNullResponseTest extends TestCase {

    /**
     * @var PingResult
     */
    private $result;

    protected function setUp() {
        parent::setUp();
        $this->result = new PingResult();
    }

    public function testInstance() {
        $this->assertInstanceOf(PingResult::class, $this->result);
    }

    public function testHttpStatus() {
        $this->assertEquals(500, $this->result->getHttpStatus());
    }

    public function testGetBody() {
        $this->assertEquals('', $this->result->getBody());
    }

    public function testHasXml() {
        $this->assertFalse($this->result->hasXml());
        $this->assertNull($this->result->getXml());
    }

    public function testGetHeader() {
        $this->assertEquals('', $this->result->getHeader('foo'));
    }

    public function testGetOjsRelease() {
        $this->assertEquals('', $this->result->getOjsRelease());
    }

    public function testGetPluginReleaseVersion() {
        $this->assertEquals('', $this->result->getPluginReleaseVersion());
    }

    public function testPluginReleaseDate() {
        $this->assertEquals('', $this->result->getPluginReleaseDate());
    }

    public function testPluginCurrent() {
        $this->assertEquals('', $this->result->isPluginCurrent());
    }

    public function testTermsAccepted() {
        $this->assertEquals('', $this->result->areTermsAccepted());
    }

    public function testJournalTitle() {
        $this->assertEquals('', $this->result->getJournalTitle());
    }

    public function testArticleCount() {
        $this->assertEquals('', $this->result->getArticleCount());
    }

    public function testArticleTitles() {
        $expected = [];
        $this->assertEquals($expected, $this->result->getArticleTitles());
    }

}
