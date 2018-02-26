<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Services\DepositBuilder;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use SimpleXMLElement;

/**
 * Description of DepositBuilderTest
 */
class DepositBuilderTest extends BaseTestCase {
    
    /**
     * @var DepositBuilder
     */
    private $builder;
    
    protected function setUp() {
        parent::setUp();
        $this->builder = $this->container->get(DepositBuilder::class);
    }
    
    public function getFixtures() {
        return array(
            LoadJournal::class,
            LoadDeposit::class,
        );
    }


    public function testInstance() {
        $this->assertInstanceOf(DepositBuilder::class, $this->builder);
    }
    
    public function testBuild() {
        $this->builder->fromXml($this->getReference('journal.1'), $xml);
    }
    
    /**
     * @return SimpleXMLElement
     */
    private function getXml() {
        $data = <<<'ENDXML'
ENDXML;
        return simplexml_load_string($data);
    }
            
}
