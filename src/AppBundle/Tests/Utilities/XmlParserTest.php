<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Utilities;

use AppBundle\Utilities\XmlParser;
use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * Description of XpathTest
 */
class XmlParserTest extends TestCase {

    /**
     * @var XmlParser
     */
    private $parser;

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp() {
        parent::setUp();
        $this->parser = new XmlParser();
        $this->root = vfsStream::setup();
    }

    /**
     * @dataProvider badUtf8Data
     */
    public function testFilter($expected, $data) {
        $sourceFile = vfsStream::newFile('bad.xml')->withContent($data)->at($this->root);
        $destFile = vfsStream::newFile('filtered.xml')->at($this->root);
        $this->assertEquals($expected, $this->parser->filter($sourceFile->url(), $destFile->url()));
    }
    
    /**
     * @dataProvider badUtf8Data
     */
    public function testFromFile($removed, $data) {
        $sourceFile = vfsStream::newFile('bad.xml')
                ->withContent("<a>{$data}</a>")
                ->at($this->root);
        $dom = $this->parser->fromFile($sourceFile->url());
        $this->assertNotNull($dom);
        if($removed) {
            $this->assertTrue($this->parser->hasErrors());
        } else {
            $this->assertFalse($this->parser->hasErrors());
        }
    }
    
    /**
     * @expectedException Exception
     */
    public function testInvalidXml() {
        $sourceFile = vfsStream::newFile('bad.xml')
                ->withContent("<a>chicanery</b>")
                ->at($this->root);
        $dom = $this->parser->fromFile($sourceFile->url());
        $this->fail();
    }
    
    /**
     * @expectedException Exception
     */
    public function testInvalidXmlAndUtf8() {
        $sourceFile = vfsStream::newFile('bad.xml')
                ->withContent("<a>chic\xc3\x28nery</b>")
                ->at($this->root);
        $dom = $this->parser->fromFile($sourceFile->url());
        $this->fail();
    }
    
    public function badUtf8Data() {
        return [
            [0, "Valid ASCII a"],
            [0, "Valid 2 Octet Sequence \xc3\xb1"],
            [1, "Invalid 2 Octet Sequence \xc3\x28"],
            [2, "Invalid Sequence Identifier \xa0\xa1"],
            [0, "Valid 3 Octet Sequence \xe2\x82\xa1"],
            [2, "Invalid 3 Octet Sequence (in 2nd Octet) \xe2\x28\xa1"],
            [2, "Invalid 3 Octet Sequence (in 3rd Octet) \xe2\x82\x28"],
            [0, "Valid 4 Octet Sequence \xf0\x90\x8c\xbc"],
            [3, "Invalid 4 Octet Sequence (in 2nd Octet) \xf0\x28\x8c\xbc"],
            [3, "Invalid 4 Octet Sequence (in 3rd Octet) \xf0\x90\x28\xbc"],
            [2, "Invalid 4 Octet Sequence (in 4th Octet) \xf0\x28\x8c\x28"],
        ];
    }

//    'Valid ASCII' => "a",
//    'Valid 2 Octet Sequence' => "\xc3\xb1",
//    'Invalid 2 Octet Sequence' => "\xc3\x28",
//    'Invalid Sequence Identifier' => "\xa0\xa1",
//    'Valid 3 Octet Sequence' => "\xe2\x82\xa1",
//    'Invalid 3 Octet Sequence (in 2nd Octet)' => "\xe2\x28\xa1",
//    'Invalid 3 Octet Sequence (in 3rd Octet)' => "\xe2\x82\x28",
//    'Valid 4 Octet Sequence' => "\xf0\x90\x8c\xbc",
//    'Invalid 4 Octet Sequence (in 2nd Octet)' => "\xf0\x28\x8c\xbc",
//    'Invalid 4 Octet Sequence (in 3rd Octet)' => "\xf0\x90\x28\xbc",
//    'Invalid 4 Octet Sequence (in 4th Octet)' => "\xf0\x28\x8c\x28",
//    'Valid 5 Octet Sequence (but not Unicode!)' => "\xf8\xa1\xa1\xa1\xa1",
//    'Valid 6 Octet Sequence (but not Unicode!)' => "\xfc\xa1\xa1\xa1\xa1\xa1",
}
