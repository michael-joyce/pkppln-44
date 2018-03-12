<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\XmlValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PlnValidateXmlCommand command.
 */
class ValidateXmlCommand extends AbstractProcessingCmd {

    /**
     * @var XmlValidator
     */
    private $validator;

    /**
     *
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
     *
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
