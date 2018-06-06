<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\VirusScanner;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Scan a deposit for viruses.
 */
class ScanCommand extends AbstractProcessingCmd {

    /**
     * Virus scanning service.
     *
     * @var VirusScanner
     */
    private $scanner;

    /**
     * Build the command.
     *
     * @param EntityManagerInterface $em
     * @param VirusScanner $scanner
     */
    public function __construct(EntityManagerInterface $em, VirusScanner $scanner) {
        parent::__construct($em);
        $this->scanner = $scanner;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:scan');
        $this->setDescription('Scan deposit packages for viruses.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function processDeposit(Deposit $deposit) {
        return $this->scanner->processDeposit($deposit);
    }

    /**
     * {@inheritdoc}
     */
    public function errorState() {
        return 'virus-error';
    }

    /**
     * {@inheritdoc}
     */
    public function failureLogMessage() {
        return 'Virus check failed.';
    }

    /**
     * {@inheritdoc}
     */
    public function nextState() {
        return 'virus-checked';
    }

    /**
     * {@inheritdoc}
     */
    public function processingState() {
        return 'xml-validated';
    }

    /**
     * {@inheritdoc}
     */
    public function successLogMessage() {
        return 'Virus check passed. No infections found.';
    }

}
