<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\Depositor;
use Doctrine\ORM\EntityManagerInterface;

/**
 * PlnDepositCommand command.
 */
class DepositCommand extends AbstractProcessingCmd {

    /**
     * @var Depositor
     */
    private $depositor;
    
    public function __construct(EntityManagerInterface $em, Depositor $depositor) {
        parent::__construct($em);
        $this->depositor = $depositor;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pln:deposit');
        $this->setDescription('Send deposits to LockssOMatic.');
        parent::configure();
    }


    protected function processDeposit(Deposit $deposit) {
        $this->depositor->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function nextState()
    {
        return 'deposited';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState()
    {
        return 'reserialized';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage()
    {
        return 'Deposit to Lockssomatic failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage()
    {
        return 'Deposit to Lockssomatic succeeded.';
    }

    /**
     * {@inheritdoc}
     */
    public function errorState()
    {
        return 'deposit-error';
    }
}
