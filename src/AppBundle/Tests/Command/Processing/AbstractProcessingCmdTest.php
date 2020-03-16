<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Command\Processing;

use AppBundle\Command\Processing\AbstractProcessingCmd;
use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Deposit;
use Doctrine\ORM\EntityManagerInterface;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Symfony\Component\Console\Output\OutputInterface;

class DummyCommand extends AbstractProcessingCmd {
    private $return;

    public function __construct(EntityManagerInterface $em, $return) {
        parent::__construct($em);
        $this->return = $return;
    }

    protected function processDeposit(Deposit $deposit) {
        return $this->return;
    }

    public function errorState() {
        return 'dummy-error';
    }

    public function failureLogMessage() {
        return 'dummy log message';
    }

    public function nextState() {
        return 'next-state';
    }

    public function processingState() {
        return 'dummy-state';
    }

    public function successLogMessage() {
        return 'success';
    }
}

/**
 * Description of AbstractProcessingCmdTest.
 *
 * @author michael
 */
class AbstractProcessingCmdTest extends BaseTestCase {
    /**
     * @var OutputInterface
     */
    private $output;

    public function getFixtures() {
        return [
            LoadJournal::class,
            LoadDeposit::class,
        ];
    }

    public function testSuccessfulRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, true);
        $cmd->runDeposit($deposit, $this->output);
        $this->assertSame('next-state', $deposit->getState());
        $this->assertStringEndsWith('success', trim($deposit->getProcessingLog()));
    }

    public function testFailureRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, false);
        $cmd->runDeposit($deposit, $this->output);
        $this->assertSame('dummy-error', $deposit->getState());
        $this->assertStringEndsWith('dummy log message', trim($deposit->getProcessingLog()));
    }

    public function testUncertainRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, null);
        $cmd->runDeposit($deposit, $this->output);
        $this->assertSame('dummy-state', $deposit->getState());
        $this->assertSame('', trim($deposit->getProcessingLog()));
    }

    public function testCustomRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, 'held');
        $cmd->runDeposit($deposit, $this->output);
        $this->assertSame('held', $deposit->getState());
        $this->assertStringEndsWith('Holding deposit.', trim($deposit->getProcessingLog()));
    }

    public function testSuccessfulDryRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, true);
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertSame('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }

    public function testFailureDryRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, false);
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertSame('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }

    public function testUncertainDryRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, null);
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertSame('dummy-state', $deposit->getState());
        $this->assertSame('', trim($deposit->getProcessingLog()));
    }

    public function testCustomDryRun() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, 'held');
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertSame('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }

    public function testGetDeposits() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $this->em->flush();

        $cmd = new DummyCommand($this->em, 'held');
        $deposits = $cmd->getDeposits();
        $this->assertSame(1, count($deposits));
        $this->assertSame($deposit, $deposits[0]);
    }

    public function testGetDepositsRetry() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-error');
        $this->em->flush();

        $cmd = new DummyCommand($this->em, 'held');
        $deposits = $cmd->getDeposits(true);
        $this->assertSame(1, count($deposits));
        $this->assertSame($deposit, $deposits[0]);
    }

    public function testGetDepositsId() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $this->em->flush();

        $cmd = new DummyCommand($this->em, 'held');
        $deposits = $cmd->getDeposits(false, [1]);
        $this->assertSame(1, count($deposits));
        $this->assertSame($deposit, $deposits[0]);
    }

    public function testGetDepositsRetryId() : void {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-error');
        $this->em->flush();

        $cmd = new DummyCommand($this->em, 'held');
        $deposits = $cmd->getDeposits(true, [1]);
        $this->assertSame(1, count($deposits));
        $this->assertSame($deposit, $deposits[0]);
    }

    public function testGetDepositsLimit() : void {
        foreach ($this->em->getRepository(Deposit::class)->findAll() as $deposit) {
            $deposit->setState('dummy-state');
        }
        $this->em->flush();

        $cmd = new DummyCommand($this->em, 'held');
        $deposits = $cmd->getDeposits(false, [], 2);
        $this->assertSame(2, count($deposits));
    }

    protected function setUp() : void {
        parent::setUp();
        $this->output = $this->createMock(OutputInterface::class);
        $this->output->method('writeln')->willReturn(null);
    }
}
