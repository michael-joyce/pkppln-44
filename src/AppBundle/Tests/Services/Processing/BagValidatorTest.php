<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Services\Processing\BagValidator;
use AppBundle\Utilities\BagReader;
use BagIt;
use Exception;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of PayloadValidatorTest
 */
class BagValidatorTest extends BaseTestCase {

    /**
     * @var BagValidator
     */
    private $validator;
    
    protected function getFixtures() {
        return array(
            LoadJournal::class,
            LoadDeposit::class,
        );
    }
    
    protected function setUp() {
        parent::setUp(); 
        $this->validator = $this->container->get(BagValidator::class);
    }
    
    public function testInstance() {
        $this->assertInstanceOf(BagValidator::class, $this->validator);
    }
    
    public function testValidate() {
        $deposit = $this->getReference('deposit.1');
        
        $bag = $this->createMock(BagIt::class);
        $bag->method('validate')->willReturn(array());
        $bag->method('getBagInfoData')->willReturn($deposit->getJournalVersion());
        $reader = $this->createMock(BagReader::class);
        $reader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($reader);
        
        $this->validator->processDeposit($deposit);
        $this->assertEmpty($deposit->getErrorLog());
    }
    
    public function testValidateVersionMismatch() {
        $deposit = $this->getReference('deposit.1');
        
        $bag = $this->createMock(BagIt::class);
        $bag->method('validate')->willReturn(array());
        $bag->method('getBagInfoData')->willReturn('2.0.0.0');
        $reader = $this->createMock(BagReader::class);
        $reader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($reader);
        
        $this->validator->processDeposit($deposit);
        $this->assertEquals(1, count($deposit->getErrorLog()));
        $this->assertStringStartsWith('Bag journal version tag', $deposit->getErrorLog()[0]);
    }
    
    /**
     * @expectedException Exception
     */
    public function testValidateFail() {
        $deposit = $this->getReference('deposit.1');
        
        $bag = $this->createMock(BagIt::class);
        $bag->method('validate')->willReturn([['foo', 'error message']]);
        $bag->method('getBagInfoData')->willReturn('2.0.0.0');
        $reader = $this->createMock(BagReader::class);
        $reader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($reader);
        
        $this->validator->processDeposit($deposit);
    }
}
