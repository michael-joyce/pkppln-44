<?php

namespace AppBundle\Command\Processing;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PlnStatusCommand command.
 */
class StatusCommand extends ContainerAwareCommand {

    /**
     * Configure the command.
     */
    protected function configure() {
        $this->setName('pln:status');
        $this->setDescription('...');
        $this->addArgument('argument', InputArgument::OPTIONAL, 'Argument description');
        $this->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *   Command input, as defined in the configure() method.
     * @param OutputInterface $output
     *   Output destination.
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // ...
        }

        $output->writeln('Command result.');
    }

}
