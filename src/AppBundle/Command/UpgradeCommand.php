<?php

namespace AppBundle\Command;

use AppBundle\Entity\Blacklist;
use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Entity\TermOfUse;
use AppBundle\Entity\TermOfUseHistory;
use AppBundle\Entity\Whitelist;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends ContainerAwareCommand {

    /**
     * @var Connection
     */
    private $source;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $idMapping;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param Connection $oldEm
     * @param EntityManagerInterface $em
     */
    public function __construct(Connection $oldEm, EntityManagerInterface $em) {
        parent::__construct();
        $this->source = $oldEm;
        $this->em = $em;
        $this->idMapping = array();
        $this->force = false;
    }

    protected function setIdMap($class, $old, $new) {
        $this->idMapping[$class][$old] = $new;
    }

    protected function getIdMap($class, $old, $default = null) {
        if (isset($this->idMapping[$class][$old])) {
            return $this->idMapping[$class][$old];
        }
        return $default;
    }

    /**
     *
     */
    public function configure() {
        $this->setName('lom:upgrade');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Actually make the database changes.');
    }

    /**
     * @param string $table
     * @param callable $callback
     */
    public function upgradeTable($table, $callback) {
        $countQuery = $this->source->query("SELECT count(*) c FROM {$table}");
        $countQuery->execute();
        $countRow = $countQuery->fetch();
        print "upgrading {$countRow['c']} entities in {$table}.\n";

        $query = $this->source->query("SELECT * FROM {$table}");
        $n = 0;
        $query->execute();
        print "$n\r";
        while ($row = $query->fetch()) {
            $entity = $callback($row);
            if ($entity) {
                $this->em->persist($entity);
                $this->em->flush($entity);
                $this->setIdMap(get_class($entity), $row['id'], $entity->getId());
                $this->em->detach($entity);
            }
            $n++;
            print "$n\r";
        }
        print "\n";
    }

    public function upgradeWhitelist() {
        $callback = function($row) {
            $entry = new Whitelist();
            $entry->setComment($row['comment']);
            $entry->setUuid($row['uuid']);
            $entry->setCreated(new DateTime($row['created']));
            return $entry;
        };
        $this->upgradeTable('whitelist', $callback);
    }

    public function upgradeBlacklist() {
        $callback = function($row) {
            $entry = new Blacklist();
            $entry->setComment($row['comment']);
            $entry->setUuid($row['uuid']);
            $entry->setCreated(new DateTime($row['created']));
            return $entry;
        };
        $this->upgradeTable('blacklist', $callback);
    }

    public function upgradeUsers() {
        $callback = function($row) {
            $entry = new User();
            $entry->setUsername($row['username']);
            $entry->setEmail($row['username']);
            $entry->setEnabled(true);
            $entry->setSalt($row['salt']);
            $entry->setPassword($row['password']);
            $entry->setLastLogin(new DateTime($row['last_login']));
            $entry->setRoles(unserialize($row['roles']));
            $entry->setFullname($row['fullname']);
            $entry->setInstitution($row['institution']);
            return $entry;
        };
        $this->upgradeTable('appuser', $callback);
    }

    public function upgradeTerms() {
        $callback = function($row) {
            $term = new TermOfUse();
            $term->setWeight($row['weight']);
            $term->setKeyCode($row['key_code']);
            $term->setContent($row['content']);
            $term->setCreated(new DateTime($row['created']));
            $term->setUpdated(new DateTime($row['updated']));
            return $term;
        };
        $this->upgradeTable('term_of_use', $callback);
    }

    public function upgradeTermHistory() {
        $callback = function($row) {
            $history = new TermOfUseHistory();
            $termId = $this->getIdMap(TermOfUse::class, $row['term_id'], $row['term_id']);
            $history->setTermId($termId);
            $history->setAction($row['action']);
            $history->setUser($row['user']);
            $history->setChangeSet($row['change_set']);
            $history->setCreated(new DateTime($row['created']));
            $history->setUpdated(new DateTime($row['created']));
            return $history;
        };
        $this->upgradeTable('term_of_use_history', $callback);
    }

    public function upgradeJournals() {
        $callback = function($row) {
            $journal = new Journal();
            $journal->setUuid($row['uuid']);
            $journal->setContacted(new DateTime($row['contacted']));
            if ($row['notified']) {
                $journal->setNotified(new DateTime($row['notified']));
            }
            $journal->setTitle($row['title']);
            if ($row['issn'] !== 'unknown') {
                $journal->setIssn($row['issn']);
            }
            $journal->setUrl($row['url']);
            $journal->setStatus($row['status']);
            if ($row['email'] !== 'unknown@unknown.com') {
                $journal->setEmail($row['email']);
            }
            if ($row['publisher_name']) {
                $journal->setPublisherName($row['publisher_name']);
            }
            if ($row['publisher_url']) {
                $journal->setPublisherUrl($row['publisher_url']);
            }
            if ($row['ojs_version']) {
                $journal->setOjsVersion($row['ojs_version']);
            }
            $journal->setTermsAccepted($row['terms_accepted'] == 1);

            return $journal;
        };
        $this->upgradeTable('journal', $callback);
    }

    public function upgradeDeposits() {
        $callback = function($row) {
            $deposit = new Deposit();

            $journalId = $this->getIdMap(Journal::class, $row['journal_id']);
            $journal = $this->em->find(Journal::class, $journalId);
            if (!$journal) {
                throw new Exception("Journal {$row['journal_id']} not found.");
            }
            $deposit->setJournal($journal);
            if ($row['file_type']) {
                $deposit->setFileType($row['file_type']);
            }
            $deposit->setDepositUuid($row['deposit_uuid']);
            $deposit->setReceived(new DateTime($row['received']));
            $deposit->setAction($row['action']);
            $deposit->setVolume($row['volume']);
            $deposit->setIssue($row['issue']);
            $deposit->setPubDate(new DateTime($row['pub_date']));
            $deposit->setChecksumType($row['checksum_type']);
            $deposit->setChecksumValue($row['checksum_value']);
            $deposit->setUrl($row['url']);
            $deposit->setSize($row['size']);
            $deposit->setState($row['state']);
            $deposit->setPlnState($row['pln_state']);
            $deposit->setPackageSize($row['package_size']);
            $deposit->setPackageChecksumType($row['package_checksum_type']);
            $deposit->setPackageChecksumValue($row['package_checksum_value']);
            if($row['deposit_date']) {
                $deposit->setDepositDate(new DateTime($row['deposit_date']));
            }
            if( ! preg_match('|^http://pkp-pln|', $row['deposit_receipt'])) {
                $deposit->setDepositReceipt($row['deposit_receipt']);
            }
            $deposit->setProcessingLog($row['processing_log']);
            $deposit->setLicense(unserialize($row['license']));
            $deposit->setErrorLog(unserialize($row['error_log']));
            $deposit->setHarvestAttempts($row['harvest_attempts']);
            $deposit->setJournalVersion($row['journal_version']);
            return $deposit;
        };
        $this->upgradeTable('deposit', $callback);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        if( ! $input->getOption('force')) {
            $output->writeln("Will not run without --force.");
            exit;
        }
        $this->upgradeWhitelist();
        $this->upgradeBlacklist();
        $this->upgradeUsers();
        $this->upgradeTerms();
        $this->upgradeTermHistory();
        $this->upgradeJournals();
        $this->upgradeDeposits();
    }
}
