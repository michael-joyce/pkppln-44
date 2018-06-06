<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\Depositor;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Send pending deposits to LOCKSS.
 */
class DepositCommand extends AbstractProcessingCmd {

    /**
     * Depositor service.
     *
     * @var Depositor
     */
    private $depositor;

    /**
     * {@inheritdoc}
     *
     * @param EntityManagerInterface $em
     *   Dependency-injected database interface.
     * @param Depositor $depositor
     *   Dependency-injected depositor service.
     */
    public function __construct(EntityManagerInterface $em, Depositor $depositor) {
        parent::__construct($em);
        $this->depositor = $depositor;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:deposit');
        $this->setDescription('Send deposits to LockssOMatic.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        print $deposit->getDepositUuid();
        return $this->depositor->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'deposited';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'reserialized';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'Deposit to Lockssomatic failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'Deposit to Lockssomatic succeeded.';
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'deposit-error';
    }

}
