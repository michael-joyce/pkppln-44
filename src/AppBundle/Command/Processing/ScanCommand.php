<?php

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\Processing\VirusScanner;
use Doctrine\ORM\EntityManagerInterface;

/**
 * PlnScanCommand command.
 */
class ScanCommand extends AbstractProcessingCmd {

    /**
     * @var VirusScanner
     */
    private $scanner;

    public function __construct(EntityManagerInterface $em, VirusScanner $scanner) {
        parent::__construct($em);
        $this->scanner = $scanner;
    }

    /**
     * Configure the command.
     */
    protected function configure() {
        $this->setName('pln:scan');
        $this->setDescription('Scan deposit packages for viruses.');
        parent::configure();
    }

    protected function processDeposit(Deposit $deposit) {
        $this->scanner->processDeposit($deposit);
    }

    public function errorState() {
        return 'virus-error';
    }

    public function failureLogMessage() {
        return 'Virus check failed.';
    }

    public function nextState() {
        return 'virus-checked';
    }

    public function processingState() {
        return 'xml-validated';
    }

    public function successLogMessage() {
        return 'Virus check passed. No infections found.';
    }

}
