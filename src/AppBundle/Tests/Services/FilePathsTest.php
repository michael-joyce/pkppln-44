<?php

namespace AppBundle\Tests\Services;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Services\FilePaths;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FilePathsTest extends TestCase {

    /**
     * @dataProvider testRootDirData
     */
    public function testRootDir($expected, $rootDir, $projectDir) {
        $fp = new FilePaths($rootDir, $projectDir);
        $this->assertEquals($expected, $fp->getRootPath());
    }

    public function testRootDirData() {
        return [
            ['', '', ''],
            ['/path/to/data', '/path/to/data', '/path/to/project'],
            ['/path/to/project/data', 'data', '/path/to/project'],
        ];
    }

    public function testGetRestoreDir() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $this->assertEquals('/data/restore/ABC123', $fp->getRestoreDir($journal));
    }

    public function testGetRestoreFile() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $deposit = new Deposit();
        $deposit->setJournal($journal);
        $deposit->setDepositUuid('def456');
        $this->assertEquals('/data/restore/ABC123/DEF456.zip', $fp->getRestoreFile($deposit));
    }

    public function testGetHarvestDir() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $this->assertEquals('/data/harvest/ABC123', $fp->getHarvestDir($journal));
    }

    public function testGetHarvestFile() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $deposit = new Deposit();
        $deposit->setJournal($journal);
        $deposit->setDepositUuid('def456');
        $this->assertEquals('/data/harvest/ABC123/DEF456.zip', $fp->getHarvestFile($deposit));
    }

    public function testGetProcessingDir() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $this->assertEquals('/data/processing/ABC123', $fp->getProcessingDir($journal));
    }

    public function testGetProcessingBagPath() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $deposit = new Deposit();
        $deposit->setJournal($journal);
        $deposit->setDepositUuid('def456');
        $this->assertEquals('/data/processing/ABC123/DEF456', $fp->getProcessingBagPath($deposit));
    }

    public function testGetStagingDir() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $this->assertEquals('/data/staged/ABC123', $fp->getStagingDir($journal));
    }

    public function testGetStagingBagPath() {
        $mock = $this->createMock(Filesystem::class);
        $mock->method('exists')->willReturn(true);
        $fp = new FilePaths('/data', '/path/', $mock);
        $journal = new Journal();
        $journal->setUuid('abc123');
        $deposit = new Deposit();
        $deposit->setJournal($journal);
        $deposit->setDepositUuid('def456');
        $this->assertEquals('/data/staged/ABC123/DEF456.zip', $fp->getStagingBagPath($deposit));
    }

    public function testGetOnixPath() {
        $fp = new FilePaths('/data', '/path/');
        $this->assertEquals('/data/onix.xml', $fp->getOnixPath());
    }

}
