<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\PayloadValidator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Validate the payload checksum.
 */
class ValidatePayloadCommand extends AbstractProcessingCmd {

    /**
     * Payload validator service.
     *
     * @var PayloadValidator
     */
    private $payloadValidator;

    /**
     * Build the command.
     *
     * @param EntityManagerInterface $em
     * @param PayloadValidator $payloadValidator
     */
    public function __construct(EntityManagerInterface $em, PayloadValidator $payloadValidator) {
        parent::__construct($em);
        $this->payloadValidator = $payloadValidator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:validate:payload');
        $this->setDescription('Validate PLN deposit packages.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        return $this->payloadValidator->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'payload-validated';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'harvested';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'Payload checksum validation failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'Payload checksum validation succeeded.';
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'payload-error';
    }

}
