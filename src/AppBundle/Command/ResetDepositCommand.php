<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
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
    const BATCH_SIZE = 100;

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
     *   Dependency injected database interface.
     */
    public function __construct(EntityManagerInterface $em) {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function configure() {
        $this->setName('pln:reset');
        $this->setDescription('Reset the processing status on one or more deposits.');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Update all deposits. Use with caution.');
        $this->addArgument('state', InputArgument::REQUIRED, 'One or more deposit database IDs to process');
        $this->addArgument('deposit-id', InputArgument::IS_ARRAY, 'One or more deposit database IDs to process');
    }

    /**
     * Create an iterator for the deposits.
     *
     * @param int[] $ids
     *   Optional list of deposit database ids.
     *
     * @return IterableResult|Deposit[]
     *   Iterator for all the deposits to reset.
     */
    public function getDepositIterator(array $ids = null) {
        $qb = $this->em->createQueryBuilder();
        $qb->select('d')->from('AppBundle:Deposit', 'd');
        if ($ids && count($ids)) {
            $qb->andWhere('d.depositUuid IN :ids');
            $qb->setParameter('ids', $ids);
        }
        return $qb->getQuery()->iterate();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $ids = $input->getArgument('deposit-id');
        if (count($ids) === 0 && !$input->getOption('all')) {
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
            $deposit->addToProcessingLog('Deposit state reset to ' . $state);
            if (($i % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
        $this->em->flush();
        $this->em->clear();
    }

}
