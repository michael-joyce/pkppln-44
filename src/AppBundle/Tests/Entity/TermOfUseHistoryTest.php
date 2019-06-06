<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\TermOfUseHistory;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of TermOfUseHistoryTest
 */
class TermOfUseHistoryTest extends BaseTestCase {
    
    private $history;
    
    protected function setup() : void {
        parent::setUp();
        $this->history = new TermOfUseHistory();
    }
    
    public function testToString() {
        $this->history->setAction('update');
        $this->assertEquals('update', (string)$this->history);
    }
    
    public function testGetUser() {
        $this->history->setUser('Yoda');
        $this->assertEquals('Yoda', $this->history->getUser());
    }
    
}
