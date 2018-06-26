<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\SwordClient;
use Doctrine\ORM\EntityManagerInterface;

/**
 * PlnStatusCommand command.
 */
class StatusCommand extends AbstractProcessingCmd {

    /**
     * {@inheritdoc}
     *
     * @param EntityManagerInterface $em
     * @param SwordClient $client
     */
    public function __construct(EntityManagerInterface $em, SwordClient $client) {
        parent::__construct($em);
        $this->client = $client;
    }

    /**
     * Configure the command.
     */
    protected function configure() {
        $this->setName('pln:status');
        $this->setDescription('Check status of deposits.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        $statusXml = $this->client->statement($deposit);
        $term = (string) $statusXml->xpath('//atom:category[@label="State"]/@term')[0];
        $deposit->setPlnState($term);
        if ($term === 'agreement') {
            return true;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'deposited';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'Status check with LOCKSSOMatic failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'complete';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'deposited';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'Status check with LOCKSSOMatic succeeded.';
    }

}
