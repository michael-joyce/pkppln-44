<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\XmlValidator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Validate XML in a deposit.
 */
class ValidateXmlCommand extends AbstractProcessingCmd {

    /**
     * XML validator service.
     *
     * @var XmlValidator
     */
    private $validator;

    /**
     * Build the command.
     *
     * @param EntityManagerInterface $em
     *   Dependency injected entity manager.
     * @param XmlValidator $validator
     *   Dependency injected validator service.
     */
    public function __construct(EntityManagerInterface $em, XmlValidator $validator) {
        parent::__construct($em);
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:validate:xml');
        $this->setDescription('Validate OJS XML export files.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        return $this->validator->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'xml-validated';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'bag-validated';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'XML Validation failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'XML validation succeeded.';
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'xml-error';
    }

}
