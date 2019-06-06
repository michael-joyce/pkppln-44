<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Services\FilePaths;
use AppBundle\Services\Processing\PayloadValidator;
use Exception;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Description of PayloadValidatorTest
 */
class PayloadValidatorTest extends BaseTestCase {
    
    /**
     * @var PayloadValidator
     */
    private $validator;
    
    /**
     * @var vfsStreamDirectory
     */
    private $root;
    
    protected function setup() : void {
        parent::setUp();        
        $this->validator = $this->container->get(PayloadValidator::class);       
        $this->root = vfsStream::setup();
    }
    
    public function testInstance() {
        $this->assertInstanceOf(PayloadValidator::class, $this->validator);
    }
    
    /**
     * @dataProvider hashFileData
     */
    public function testHashFile($alg, $name, $data) {
        $file = vfsStream::newFile('deposit.zip')->withContent($data)->at($this->root);
        $this->assertEquals(strtoupper(hash($alg, $data)), $this->validator->hashFile($name, $file->url()));
    }
    
    public function hashFileData() {
        return array(
            ['sha1', 'sha-1', 'some data.'],
            ['sha1', 'sha1', 'some data.'],
            ['sha1', 'SHA1', 'some data.'],
            ['md5', 'md5', 'some data.'],
            ['md5', 'MD5', 'some data.'],
        );
    }
    
    public function testHashFileException() {
        $this->expectException(Exception::class);
        $file = vfsStream::newFile('deposit.zip')->withContent('some data.')->at($this->root);
        $this->validator->hashFile('cheese', $file->url());
    }
    
    public function testProcessDeposit() {        
        $file = vfsStream::newFile('deposit.zip')->withContent('some data.')->at($this->root);
        
        $filePaths = $this->createMock(FilePaths::class);
        $filePaths->method('getHarvestFile')->willReturn($file->url());
        $this->validator->setFilePaths($filePaths);
        
        $journal = new Journal();
        $journal->setUuid('abc123');
        
        $deposit = new Deposit();
        $deposit->setJournal($journal);
        $deposit->setChecksumType('sha1');
        $deposit->setChecksumValue(hash('sha1', 'some data.'));
        
        $result = $this->validator->processDeposit($deposit);
        $this->assertTrue($result);
        $this->assertEquals('', $deposit->getProcessingLog());
    }
    
    public function testProcessDepositChecksumMismatch() {        
        $file = vfsStream::newFile('deposit.zip')->withContent('some data.')->at($this->root);
        
        $filePaths = $this->createMock(FilePaths::class);
        $filePaths->method('getHarvestFile')->willReturn($file->url());
        $this->validator->setFilePaths($filePaths);
        
        $journal = new Journal();
        $journal->setUuid('abc123');
        
        $deposit = new Deposit();
        $deposit->setJournal($journal);
        $deposit->setChecksumType('sha1');
        $deposit->setChecksumValue(hash('sha1', 'some other different data.'));
        
        $result = $this->validator->processDeposit($deposit);
        $this->assertFalse($result);
        $this->assertStringContainsStringIgnoringCase('Deposit checksum does not match', $deposit->getProcessingLog());
    }
    
    public function testProcessDepositChecksumUnknown() {        
        $file = vfsStream::newFile('deposit.zip')->withContent('some data.')->at($this->root);
        
        $filePaths = $this->createMock(FilePaths::class);
        $filePaths->method('getHarvestFile')->willReturn($file->url());
        $this->validator->setFilePaths($filePaths);
        
        $journal = new Journal();
        $journal->setUuid('abc123');
        
        $deposit = new Deposit();
        $deposit->setJournal($journal);
        $deposit->setChecksumType('cheese');
        $deposit->setChecksumValue(hash('sha1', 'some other different data.'));
        
        $result = $this->validator->processDeposit($deposit);
        $this->assertFalse($result);
        $this->assertStringContainsStringIgnoringCase('Unknown hash algorithm cheese', $deposit->getProcessingLog());
    }
}
