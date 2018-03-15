<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\Services\Processing\VirusScanner;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Socket\Raw\Factory;
use Socket\Raw\Socket;
use Xenolope\Quahog\Client;

/**
 * Description of PayloadValidatorTest
 */
class VirusScannerTest extends BaseTestCase {
    
    /**
     * @var VirusScanner
     */
    private $scanner;
        
    protected function setUp() {
        parent::setUp();        
        $this->scanner = $this->container->get(VirusScanner::class);       
    }
    
    public function testInstance() {
        $this->assertInstanceOf(VirusScanner::class, $this->scanner);
    }
    
    public function testGetClient() {
        $factory = $this->createMock(Factory::class);
        $factory->method('createClient')->willReturn(new Socket(null));
        $this->scanner->setFactory($factory);
        $client = $this->scanner->getClient();
        $this->assertInstanceOf(Client::class, $client);
    }
    
    public function testScanEmbed() {
        
    }
    
}