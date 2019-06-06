<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Blacklist;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of BlacklistTest
 */
class BlacklistTest extends BaseTestCase {

    private $blacklist;
    
    protected function setup() : void {
        parent::setUp();
        $this->blacklist = new Blacklist();
    }
    
    public function testInstance() {
        $this->assertInstanceOf(Blacklist::class, $this->blacklist);
    }
    
    public function testSetUuid() {
        $this->blacklist->setUuid('abc123');
        $this->assertEquals('ABC123', $this->blacklist->getUuid());
    }
    
    public function testToString() {
        $this->blacklist->setUuid('abc123');
        $this->assertEquals('ABC123', (string)$this->blacklist);
    }
    
}
