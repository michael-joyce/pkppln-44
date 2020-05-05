<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\DtdValidator;
use AppBundle\Services\Processing\XmlValidator;
use AppBundle\Services\SchemaValidator;
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
    private $dtdValidator;

    /**
     * @var SchemaValidator
     */
    private $schemaValidator;

    /**
     * Build the command.
     *
     * @param EntityManagerInterface $em
     * @param DtdValidator $dtdValidator
     * @param SchemaValidator $schemaValidator
     */
    public function __construct(EntityManagerInterface $em, DtdValidator $dtdValidator, SchemaValidator $schemaValidator) {
        parent::__construct($em);
        $this->dtdValidator = $dtdValidator;
        $this->schemaValidator = $schemaValidator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() : void {
        $this->setName('pln:validate:xml');
        $this->setDescription('Validate OJS XML export files.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        return $this->dtdValidator->processDeposit($deposit);
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
