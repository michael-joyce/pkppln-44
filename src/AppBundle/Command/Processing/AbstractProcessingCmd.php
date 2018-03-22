<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Command\Processing;

use AppBundle\Entity\Deposit;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Parent class for all processing commands.
 */
abstract class AbstractProcessingCmd extends ContainerAwareCommand {

    /**
     * Database interface.
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Build the command.
     *
     * @param EntityManagerInterface $em
     *   Dependency injected entity manager.
     */
    public function __construct(EntityManagerInterface $em) {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->addOption('retry', 'r', InputOption::VALUE_NONE, 'Retry failed deposits');
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Do not update processing status');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Only process $limit deposits.');
        $this->addArgument('deposit-id', InputArgument::IS_ARRAY, 'One or more deposit database IDs to process');
    }

    /**
     * Preprocess the list of deposits.
     *
     * @param Deposit[] $deposits
     *   List of deposits to process.
     */
    protected function preprocessDeposits(array $deposits = array()) {
        // Do nothing by default.
    }

    /**
     * Process one deposit return true on success and false on failure.
     *
     * @param Deposit $deposit
     *   Single deposit to process.
     *
     * @return string|bool|null
     *   True for success, false for fail, null for unknown, and string for other.
     */
    abstract protected function processDeposit(Deposit $deposit);

    /**
     * Deposits in this state will be processed by the commands.
     */
    abstract public function processingState();

    /**
     * Successfully processed deposits will be given this state.
     */
    abstract public function nextState();

    /**
     * Deposits which generate errors will be given this state.
     */
    abstract public function errorState();

    /**
     * Successfully processed deposits will be given this log message.
     */
    abstract public function successLogMessage();

    /**
     * Failed deposits will be given this log message.
     */
    abstract public function failureLogMessage();

    /**
     * Code to run before executing the command.
     */
    protected function preExecute() {
        // Do nothing, let subclasses override if needed.
    }

    /**
     * Get a list of deposits to process.
     *
     * @param bool $retry
     *   Retry failed deposits.
     * @param int[] $depositIds
     *   Zero or more deposit Ids to filter.
     * @param int $limit
     *   Maximum number of deposits to return.
     *
     * @return Deposit[]
     *   List of deposits for processing.
     */
    public function getDeposits($retry = false, array $depositIds = array(), $limit = null) {
        $repo = $this->em->getRepository(Deposit::class);
        $state = $this->processingState();
        if ($retry) {
            $state = $this->errorState();
        }
        $query = array('state' => $state);
        if (count($depositIds) > 0) {
            $query['id'] = $depositIds;
        }
        $orderBy = array(
        'id' => 'ASC',
        );
        return $repo->findBy($query, $orderBy, $limit);
    }

    /**
     * Run and process one deposit.
     *
     * @param Deposit $deposit
     *   Deposit to process.
     * @param OutputInterface $output
     *   Output for writing messages.
     * @param bool $dryRun
     *   If true, then this is a dry run and
     *   results are not flushed to the database.
     */
    public function runDeposit(Deposit $deposit, OutputInterface $output, $dryRun = false) {
        try {
            $result = $this->processDeposit($deposit);
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
            $deposit->setState($this->errorState());
            $deposit->addToProcessingLog($this->failureLogMessage());
            $deposit->addErrorLog(get_class($e) . $e->getMessage());
            $this->em->flush($deposit);
            return;
        }

        if ($dryRun) {
            return;
        }

        if (is_string($result)) {
            $deposit->setState($result);
            $deposit->addToProcessingLog("Holding deposit.");
        } elseif ($result === true) {
            $deposit->setState($this->nextState());
            $deposit->addToProcessingLog($this->successLogMessage());
        } elseif ($result === false) {
            $deposit->setState($this->errorState());
            $deposit->addToProcessingLog($this->failureLogMessage());
        } elseif ($result === null) {
            // dunno, do nothing I guess.
        }
        $this->em->flush($deposit);
    }

    /**
     * {@inheritdoc}
     */
    final protected function execute(InputInterface $input, OutputInterface $output) {
        $this->preExecute();
        $deposits = $this->getDeposits(
            $input->getOption('retry'),
            $input->getArgument('deposit-id'),
            $input->getOption('limit')
        );

        $this->preprocessDeposits($deposits);
        foreach ($deposits as $deposit) {
            $this->runDeposit($deposit, $output, $input->getOption('dry-run'));
        }
    }

}
