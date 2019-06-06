<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Journal;
use DateTime;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of JournalTest
 */
class JournalTest extends BaseTestCase {

    private $journal;
    
    protected function setup() : void {
        parent::setUp();
        $this->journal = new Journal();
    }
    
    public function testInstance() {
        $this->assertInstanceOf(Journal::class, $this->journal);
    }
    
    public function testSetUuid() {
        $this->journal->setUuid('abc123');
        $this->assertEquals('ABC123', $this->journal->getUuid());
    }
    
    public function testSetNotified() {
        $this->journal->setNotified(new DateTime());
        $this->assertInstanceOf(DateTime::class, $this->journal->getNotified());
    }
    
}
