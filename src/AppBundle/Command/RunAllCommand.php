<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run all the processing commands in order.
 */
class RunAllCommand extends ContainerAwareCommand {

    /**
     * List of commands to run, in order.
     */
    const COMMAND_LIST = array(
            'pln:harvest',
            'pln:validate:payload',
            'pln:validate:bag',
            'pln:validate:xml',
            'pln:scan',
            'pln:reserialize',
            'pln:deposit',
            // 'pln:status',.
    );

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:run-all');
        $this->setDescription('Run all processing commands.');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the processing state to be updated');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Only process $limit deposits.');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        foreach (self::COMMAND_LIST as $cmd) {
            $output->writeln("Running {$cmd}");
            $command = $this->getApplication()->find($cmd);
            $command->run($input, $output);
        }
    }

}
