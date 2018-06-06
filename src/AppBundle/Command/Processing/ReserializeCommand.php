<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\BagReserializer;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Reserialize the bags and add some metadata.
 */
class ReserializeCommand extends AbstractProcessingCmd {


    /**
     * Bag reserializer service.
     *
     * @var BagReserializer
     */
    private $bagReserializer;

    /**
     * Build the command.
     *
     * @param EntityManagerInterface $em
     * @param BagReserializer $bagReserializer
     */
    public function __construct(EntityManagerInterface $em, BagReserializer $bagReserializer) {
        parent::__construct($em);
        $this->bagReserializer = $bagReserializer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:reserialize');
        $this->setDescription('Reserialize the deposit bag.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        return $this->bagReserializer->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'Bag Reserialize failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'reserialized';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'virus-checked';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'Bag Reserialize succeeded.';
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'reserialize-error';
    }

}
