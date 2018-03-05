<?php

namespace AppBundle\Tests\Command\Processing;

use AppBundle\Command\Processing\AbstractProcessingCmd;
use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Entity\Deposit;
use Doctrine\ORM\EntityManagerInterface;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

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
 * Description of AbstractProcessingCmdTest
 *
 * @author michael
 */
class AbstractProcessingCmdTest extends BaseTestCase {

    public function getFixtures() {
        return array(
            LoadJournal::class,
            LoadDeposit::class,
        );
    }
    
    public function testSuccessfulRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, true);
        $cmd->runDeposit($deposit);
        $this->assertEquals('next-state', $deposit->getState());
        $this->assertStringEndsWith('success', trim($deposit->getProcessingLog()));
    }
    
    public function testFailureRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, false);
        $cmd->runDeposit($deposit);
        $this->assertEquals('dummy-error', $deposit->getState());
        $this->assertStringEndsWith('dummy log message', trim($deposit->getProcessingLog()));
    }
    
    public function testUncertainRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, null);
        $cmd->runDeposit($deposit);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertEquals('', trim($deposit->getProcessingLog()));
    }
    
    public function testCustomRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, "held");
        $cmd->runDeposit($deposit);
        $this->assertEquals('held', $deposit->getState());
        $this->assertStringEndsWith('Holding deposit.', trim($deposit->getProcessingLog()));
    }
    
    public function testSuccessfulDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, true);
        $cmd->runDeposit($deposit, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }
    
    public function testFailureDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, false);
        $cmd->runDeposit($deposit, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }
    
    public function testUncertainDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, null);
        $cmd->runDeposit($deposit, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertEquals('', trim($deposit->getProcessingLog()));
    }
    
    public function testCustomDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, "held");
        $cmd->runDeposit($deposit, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }
}