<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Deposit;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of DepositTest
 */
class DepositTest extends BaseTestCase {

    private $deposit;
    
    protected function setUp() {
        parent::setUp();
        $this->deposit = new Deposit();
    }
    
    public function testInstance() {
        $this->assertInstanceOf(Deposit::class, $this->deposit);
    }
    
    public function testSetUuid() {
        $this->deposit->setDepositUuid('abc123');
        $this->assertEquals('ABC123', $this->deposit->getDepositUuid());
    }
    
    public function testToString() {
        $this->deposit->setDepositUuid('abc123');
        $this->assertEquals('ABC123', (string)$this->deposit);
    }
    
    public function testSetChecksumType() {
        $this->deposit->setChecksumType('ABC123');
        $this->assertEquals('abc123', $this->deposit->getChecksumType());
    }
    
    public function testSetChecksumValue() {
        $this->deposit->setChecksumValue('abc123');
        $this->assertEquals('ABC123', $this->deposit->getChecksumValue());
    }
    
    public function testAddErrorLog() {
        $this->deposit->addErrorLog("foo");
        $this->assertEquals(['foo'], $this->deposit->getErrorLog());
    }
    
}
