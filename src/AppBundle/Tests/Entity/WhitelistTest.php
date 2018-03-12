<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Whitelist;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of WhitelistTest
 */
class WhitelistTest extends BaseTestCase {

    private $whitelist;
    
    protected function setUp() {
        parent::setUp();
        $this->whitelist = new Whitelist();
    }
    
    public function testInstance() {
        $this->assertInstanceOf(Whitelist::class, $this->whitelist);
    }
    
    public function testSetUuid() {
        $this->whitelist->setUuid('abc123');
        $this->assertEquals('ABC123', $this->whitelist->getUuid());
    }
    
    public function testToString() {
        $this->whitelist->setUuid('abc123');
        $this->assertEquals('ABC123', (string)$this->whitelist);
    }
    
}
