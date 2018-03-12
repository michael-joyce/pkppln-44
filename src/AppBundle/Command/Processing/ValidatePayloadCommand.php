<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\PayloadValidator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * PlnValidatePayloadCommand command.
 */
class ValidatePayloadCommand extends AbstractProcessingCmd {

    /**
     * @var PayloadValidator
     */
    private $payloadValidator;

    /**
     *
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
     *
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
