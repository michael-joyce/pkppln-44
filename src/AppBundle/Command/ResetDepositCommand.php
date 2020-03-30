<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reset the processing status on one or more deposits.
 */
class ResetDepositCommand extends ContainerAwareCommand {
    /**
     * Number of deposits to process in one batch.
     */
    public const BATCH_SIZE = 100;

    /**
     * Database interface.
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Build the command.
     */
    public function __construct(EntityManagerInterface $em) {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void {
        $ids = $input->getArgument('deposit-id');
        $clear = $input->getOption('clear');
        if (0 === count($ids) && ! $input->getOption('all')) {
            $output->writeln('Either --all or one or more deposit UUIDs are required.');

            return;
        }
        $state = $input->getArgument('state');
        $iterator = $this->getDepositIterator($ids);
        $i = 0;
        foreach ($iterator as $row) {
            $i++;
            $deposit = $row[0];
            $deposit->setState($state);
            if ($clear) {
                $deposit->setErrorLog([]);
                $deposit->setProcessingLog('');
            }
            $deposit->addToProcessingLog('Deposit state reset to ' . $state);
            if (0 === ($i % self::BATCH_SIZE)) {
                $this->em->flush();
                $this->em->clear();
            }
        }
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function configure() : void {
        $this->setName('pln:reset');
        $this->setDescription('Reset the processing status on one or more deposits.');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Update all deposits. Use with caution.');
        $this->addOption('clear', null, InputOption::VALUE_NONE, 'Clear the error and processing log for the deposits Use with caution.');
        $this->addArgument('state', InputArgument::REQUIRED, 'One or more deposit database IDs to process');
        $this->addArgument('deposit-id', InputArgument::IS_ARRAY, 'One or more deposit database IDs to process');
    }

    /**
     * Create an iterator for the deposits.
     *
     * @param int[] $ids
     *                   Optional list of deposit database ids.
     *
     * @return Deposit[]|IterableResult
     *                                  Iterator for all the deposits to reset.
     */
    public function getDepositIterator(array $ids = null) {
        $qb = $this->em->createQueryBuilder();
        $qb->select('d')->from('AppBundle:Deposit', 'd');
        if ($ids && count($ids)) {
            $qb->andWhere('d.depositUuid IN (:ids)');
            $qb->setParameter('ids', $ids);
        }

        return $qb->getQuery()->iterate();
    }
}
