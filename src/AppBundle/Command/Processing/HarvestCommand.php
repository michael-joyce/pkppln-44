<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\Harvester;
use Doctrine\ORM\EntityManagerInterface;

/**
 * PlnHarvestCommand command.
 */
class HarvestCommand extends AbstractProcessingCmd {

    /**
     * @var Harvester
     */
    private $harvester;

    /**
     * @param Harvester $harvester
     */
    public function __construct(EntityManagerInterface $em, Harvester $harvester) {
        parent::__construct($em);
        $this->harvester = $harvester;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:harvest');
        $this->setDescription('Harvest OJS deposits.');
        parent::configure();
    }

    /**
     *
     */
    protected function processDeposit(Deposit $deposit) {
        return $this->harvester->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'harvested';
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'harvest-error';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'depositedByJournal';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'Deposit harvest failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'Deposit harvest succeeded.';
    }

}
