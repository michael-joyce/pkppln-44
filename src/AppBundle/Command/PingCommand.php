<?php

namespace AppBundle\Command;

use AppBundle\Entity\Journal;
use AppBundle\Services\Ping;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Ping the journals to make sure they're up and running.
 */
class PingCommand extends ContainerAwareCommand {

    /**
     * Fully configured ping service.
     *
     * @var Ping
     */
    private $ping;

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
     * @param Ping $ping
     */
    public function __construct(EntityManagerInterface $em, Ping $ping) {
        parent::__construct();
        $this->ping = $ping;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName('pln:ping-whitelist');
        $this->setDescription('Find journals running a sufficiently new version of OJS and whitelist them.');
        $this->addArgument('minVersion', InputArgument::OPTIONAL, 'Minimum version required to whitelist.');
        $this->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Do not update the whitelist - report only.');
        $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Ping all journals, including whitelisted/blacklisted.');
    }

    /**
     * Find the journals that need to be binged.
     *
     * @param bool $all
     */
    public function findJournals($all) {
        $repo = $this->em->getRepository(Journal::class);
        if ($all) {
            return $repo->findAll();
        }
        return $repo->getJournalsToPing();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $all = $input->getOption('all');
        $journals = $this->findJournals($all);
        foreach ($journals as $journal) {
            $output->writeln($journal->getUuid());
            $result = $this->ping->ping($journal);
            $this->em->flush();
        }
    }

}
