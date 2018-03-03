<?php

namespace AppBundle\Command\Processing;

use AppBundle\Services\Processing\Harvester;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PlnHarvestCommand command.
 */
class HarvestCommand extends ContainerAwareCommand
{
    /**
     * @var Harvester
     */
    private $harvester;

    /**
     * @param \AppBundle\Command\Processing\Harvester $harvester
     */    
    public function __construct(Harvester $harvester) {
        parent::__construct();
        $this->harvester = $harvester;
    }
    
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('pln:harvest')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *   Command input, as defined in the configure() method.
     * @param OutputInterface $output
     *   Output destination.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // ...
        }

        $output->writeln('Command result.');
    }

}
