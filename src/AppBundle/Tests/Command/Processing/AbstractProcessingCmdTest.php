<?php

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
 * Description of AbstractProcessingCmdTest
 *
 * @author michael
 */
class AbstractProcessingCmdTest extends BaseTestCase {

    /**
     * @var OutputInterface
     */
    private $output;
    
    public function getFixtures() {
        return array(
            LoadJournal::class,
            LoadDeposit::class,
        );
    }
    
    protected function setUp() : void {
        parent::setUp();
        $this->output = $this->createMock(OutputInterface::class);
        $this->output->method('writeln')->willReturn(null);
    }
    
    public function testSuccessfulRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, true);
        $cmd->runDeposit($deposit, $this->output);
        $this->assertEquals('next-state', $deposit->getState());
        $this->assertStringEndsWith('success', trim($deposit->getProcessingLog()));
    }
    
    public function testFailureRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, false);
        $cmd->runDeposit($deposit, $this->output);
        $this->assertEquals('dummy-error', $deposit->getState());
        $this->assertStringEndsWith('dummy log message', trim($deposit->getProcessingLog()));
    }
    
    public function testUncertainRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, null);
        $cmd->runDeposit($deposit, $this->output);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertEquals('', trim($deposit->getProcessingLog()));
    }
    
    public function testCustomRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, "held");
        $cmd->runDeposit($deposit, $this->output);
        $this->assertEquals('held', $deposit->getState());
        $this->assertStringEndsWith('Holding deposit.', trim($deposit->getProcessingLog()));
    }
    
    public function testSuccessfulDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, true);
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }
    
    public function testFailureDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, false);
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }
    
    public function testUncertainDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, null);
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertEquals('', trim($deposit->getProcessingLog()));
    }
    
    public function testCustomDryRun() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $cmd = new DummyCommand($this->em, "held");
        $cmd->runDeposit($deposit, $this->output, true);
        $this->assertEquals('dummy-state', $deposit->getState());
        $this->assertStringEndsWith('', trim($deposit->getProcessingLog()));
    }
    
    public function testGetDeposits() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $this->em->flush();
        
        $cmd = new DummyCommand($this->em, "held");
        $deposits = $cmd->getDeposits();
        $this->assertEquals(1, count($deposits));
        $this->assertEquals($deposit, $deposits[0]);
    }
    
    public function testGetDepositsRetry() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-error');
        $this->em->flush();
        
        $cmd = new DummyCommand($this->em, "held");
        $deposits = $cmd->getDeposits(true);
        $this->assertEquals(1, count($deposits));
        $this->assertEquals($deposit, $deposits[0]);
    }
    
    public function testGetDepositsId() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-state');
        $this->em->flush();
        
        $cmd = new DummyCommand($this->em, "held");
        $deposits = $cmd->getDeposits(false, [1]);
        $this->assertEquals(1, count($deposits));
        $this->assertEquals($deposit, $deposits[0]);
    }
    
    public function testGetDepositsRetryId() {
        $deposit = $this->em->find(Deposit::class, 1);
        $deposit->setState('dummy-error');
        $this->em->flush();
        
        $cmd = new DummyCommand($this->em, "held");
        $deposits = $cmd->getDeposits(true, [1]);
        $this->assertEquals(1, count($deposits));
        $this->assertEquals($deposit, $deposits[0]);
    }
    
    public function testGetDepositsLimit() {
        foreach($this->em->getRepository(Deposit::class)->findAll() as $deposit) {
            $deposit->setState('dummy-state');
        }
        $this->em->flush();
        
        $cmd = new DummyCommand($this->em, "held");
        $deposits = $cmd->getDeposits(false, array(), 2);
        $this->assertEquals(2, count($deposits));
    }
}
