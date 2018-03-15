<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Utilities;

use AppBundle\Utilities\BagReader;
use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * Description of XpathTest
 */
class BagReaderTest extends TestCase {

    /**
     * @var vfsStreamDirectory
     */
    private $root;
    
    /**
     * @var BagReader
     */
    private $reader;
    
    protected function setUp() {
        parent::setUp();        
        $this->root = vfsStream::setup();
        $this->reader = new BagReader();
    }
    
    /**
     * @expectedException Exception
     */
    public function testReadBagException() {
        $this->reader->readBag($this->root->url() . '/doesnotexist');
    }

}
