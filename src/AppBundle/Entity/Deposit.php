<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Deposit.
 *
 * @ORM\Table(name="deposit", indexes={
 * @ORM\Index(columns={"deposit_uuid", "url"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DepositRepository")
 */
class Deposit extends AbstractEntity {

    /**
     * Default OJS version.
     *
     * The journal version was added to the PKP PLN plugin in OJS version 3. If
     * a deposit doesn't have a version attribute, then assume it is OJS 2.4.8.
     */
    const DEFAULT_JOURNAL_VERSION = '2.4.8';

    /**
     * The journal that sent this deposit.
     *
     * @var Journal
     *
     * @ORM\ManyToOne(targetEntity="Journal", inversedBy="deposits")
     * @ORM\JoinColumn(name="journal_id", referencedColumnName="id")
     */
    private $journal;

    /**
     * The version of OJS that made the deposit and created the export file.
     *
     * The default is 2.4.8. If annotations made use of class constants, it would use
     * self::DEFAULT_JOURNAL_VERSION.
     *
     * @var string
     * @ORM\Column(type="string", length=15, nullable=false, options={"default": "2.4.8"})
     */
    private $journalVersion;

    /**
     * Serialized list of licensing terms as reported in the ATOM deposit.
     *
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $license;

    /**
     * Mime type from the deposit.
     *
     * Bagit doesn't understand compressed files that don't have a file
     * extension. So set the file type, and build file names from that.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true);
     */
    private $fileType;

    /**
     * Deposit UUID, as generated by the PLN plugin in OJS.
     *
     * @var string
     *
     * @Assert\Uuid
     * @ORM\Column(type="string", length=36, nullable=false, unique=true)
     */
    private $depositUuid;

    /**
     * When the deposit was received.
     *
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $received;

    /**
     * The deposit action (add, edit).
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $action;

    /**
     * The issue volume number.
     *
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $volume;

    /**
     * The issue number for the deposit.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $issue;

    /**
     * Publication date of the deposit content.
     *
     * @var DateTime
     * @ORM\Column(type="date")
     */
    private $pubDate;

    /**
     * The checksum type for the deposit (SHA1, MD5).
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $checksumType;

    /**
     * The checksum value, in hex.
     *
     * @var string
     * @Assert\Regex("/^[0-9a-f]+$/");
     * @ORM\Column(type="string")
     */
    private $checksumValue;

    /**
     * The source URL for the deposit. This may be a very large string.
     *
     * @var string
     *
     * @Assert\Url
     * @ORM\Column(type="string", length=2048)
     */
    private $url;

    /**
     * Size of the deposit, in bytes.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * Current processing state.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $state;

    /**
     * List of errors that occured while processing.
     *
     * @var array
     * @ORM\Column(type="array", nullable=false)
     */
    private $errorLog;

    /**
     * State of the deposit in LOCKSSOMatic.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $plnState;

    /**
     * Size of the processed package file, ready for deposit to LOCKSS.
     *
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packageSize;

    /**
     * Processed package checksum type.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $packageChecksumType;

    /**
     * Checksum for the processed package file.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $packageChecksumValue;

    /**
     * Date the deposit was sent to LOCKSSOmatic or the PLN.
     *
     * @var DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $depositDate;

    /**
     * URL for the deposit receipt in LOCKSSOMatic.
     *
     * @var string
     * @Assert\Url
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $depositReceipt;

    /**
     * Processing log for this deposit.
     *
     * @var string
     * @ORM\Column(type="text")
     */
    private $processingLog;

    /**
     * Number of times the the server has attempted to harvest the deposit.
     * @var int
     * @ORM\Column(type="integer")
     */
    private $harvestAttempts;

    /**
     * Construct a deposit.
     */
    public function __construct() {
        parent::__construct();
        $this->license = array();
        $this->received = new DateTime();
        $this->processingLog = '';
        $this->state = 'depositedByJournal';
        $this->errorLog = array();
        $this->harvestAttempts = 0;
        $this->journalVersion = self::DEFAULT_JOURNAL_VERSION;
    }

    /**
     * Return the deposit UUID.
     *
     * @return string
     */
    public function __toString() {
        return $this->getDepositUuid();
    }

    /**
     * Set journalVersion.
     *
     * @param string $journalVersion
     *
     * @return Deposit
     */
    public function setJournalVersion($journalVersion) {
        $this->journalVersion = $journalVersion;

        return $this;
    }

    /**
     * Get journalVersion.
     *
     * @return string
     */
    public function getJournalVersion() {
        return $this->journalVersion;
    }

    /**
     * Set license.
     *
     * @param array $license
     *
     * @return Deposit
     */
    public function setLicense(array $license) {
        $this->license = $license;

        return $this;
    }

    /**
     * Add a bit of licensing information to a deposit.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return Deposit
     */
    public function addLicense($key, $value) {
        if (trim($value)) {
            $this->license[$key] = trim($value);
        }
        return $this;
    }

    /**
     * Get license.
     *
     * @return array
     */
    public function getLicense() {
        return $this->license;
    }

    /**
     * Set fileType.
     *
     * @param string $fileType
     *
     * @return Deposit
     */
    public function setFileType($fileType) {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get fileType.
     *
     * @return string
     */
    public function getFileType() {
        return $this->fileType;
    }

    /**
     * Set depositUuid.
     *
     * UUIDs are stored and returned in upper case letters.
     *
     * @param string $depositUuid
     *
     * @return Deposit
     */
    public function setDepositUuid($depositUuid) {
        $this->depositUuid = strtoupper($depositUuid);

        return $this;
    }

    /**
     * Get depositUuid.
     *
     * @return string
     */
    public function getDepositUuid() {
        return $this->depositUuid;
    }

    /**
     * Set received.
     *
     * @param DateTime $received
     *
     * @return Deposit
     */
    public function setReceived(DateTime $received) {
        $this->received = $received;

        return $this;
    }

    /**
     * Get received.
     *
     * @return DateTime
     */
    public function getReceived() {
        return $this->received;
    }

    /**
     * Set action.
     *
     * @param string $action
     *
     * @return Deposit
     */
    public function setAction($action) {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set volume.
     *
     * @param int $volume
     *
     * @return Deposit
     */
    public function setVolume($volume) {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume.
     *
     * @return int
     */
    public function getVolume() {
        return $this->volume;
    }

    /**
     * Set issue.
     *
     * @param int $issue
     *
     * @return Deposit
     */
    public function setIssue($issue) {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Get issue.
     *
     * @return int
     */
    public function getIssue() {
        return $this->issue;
    }

    /**
     * Set pubDate.
     *
     * @param DateTime $pubDate
     *
     * @return Deposit
     */
    public function setPubDate(DateTime $pubDate) {
        $this->pubDate = $pubDate;

        return $this;
    }

    /**
     * Get pubDate.
     *
     * @return DateTime
     */
    public function getPubDate() {
        return $this->pubDate;
    }

    /**
     * Set checksumType.
     *
     * @param string $checksumType
     *
     * @return Deposit
     */
    public function setChecksumType($checksumType) {
        $this->checksumType = strtolower($checksumType);

        return $this;
    }

    /**
     * Get checksumType.
     *
     * @return string
     */
    public function getChecksumType() {
        return $this->checksumType;
    }

    /**
     * Set checksumValue.
     *
     * @param string $checksumValue
     *
     * @return Deposit
     */
    public function setChecksumValue($checksumValue) {
        $this->checksumValue = strtoupper($checksumValue);

        return $this;
    }

    /**
     * Get checksumValue.
     *
     * @return string
     */
    public function getChecksumValue() {
        return $this->checksumValue;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Deposit
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set size.
     *
     * @param int $size
     *
     * @return Deposit
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Set state.
     *
     * @param string $state
     *
     * @return Deposit
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Set errorLog.
     *
     * @param array $errorLog
     *
     * @return Deposit
     */
    public function setErrorLog(array $errorLog) {
        $this->errorLog = $errorLog;

        return $this;
    }

    /**
     * Get errorLog.
     *
     * @return array|string
     *   Errors encountered during processing, either as a string or list.
     */
    public function getErrorLog($delim = null) {
        if ($delim) {
            return implode($delim, $this->errorLog);
        }
        return $this->errorLog;
    }

    /**
     * Add a message to the error log.
     *
     * @param string $error
     *
     * @return Deposit
     */
    public function addErrorLog($error) {
        $this->errorLog[] = $error;
        return $this;
    }

    /**
     * Set plnState.
     *
     * @param string $plnState
     *
     * @return Deposit
     */
    public function setPlnState($plnState) {
        $this->plnState = $plnState;

        return $this;
    }

    /**
     * Get plnState.
     *
     * @return string
     */
    public function getPlnState() {
        return $this->plnState;
    }

    /**
     * Set packageSize.
     *
     * @param int $packageSize
     *
     * @return Deposit
     */
    public function setPackageSize($packageSize) {
        $this->packageSize = $packageSize;

        return $this;
    }

    /**
     * Get packageSize.
     *
     * @return int
     */
    public function getPackageSize() {
        return $this->packageSize;
    }

    /**
     * Set packageChecksumType.
     *
     * @param string $packageChecksumType
     *
     * @return Deposit
     */
    public function setPackageChecksumType($packageChecksumType) {
        $this->packageChecksumType = strtolower($packageChecksumType);

        return $this;
    }

    /**
     * Get packageChecksumType.
     *
     * @return string
     */
    public function getPackageChecksumType() {
        return $this->packageChecksumType;
    }

    /**
     * Set packageChecksumValue.
     *
     * @param string $packageChecksumValue
     *
     * @return Deposit
     */
    public function setPackageChecksumValue($packageChecksumValue) {
        $this->packageChecksumValue = strtoupper($packageChecksumValue);

        return $this;
    }

    /**
     * Get packageChecksumValue.
     *
     * @return string
     */
    public function getPackageChecksumValue() {
        return $this->packageChecksumValue;
    }

    /**
     * Set depositDate.
     *
     * @param DateTime $depositDate
     *
     * @return Deposit
     */
    public function setDepositDate(DateTime $depositDate) {
        $this->depositDate = $depositDate;

        return $this;
    }

    /**
     * Get depositDate.
     *
     * @return DateTime
     */
    public function getDepositDate() {
        return $this->depositDate;
    }

    /**
     * Set depositReceipt.
     *
     * @param string $depositReceipt
     *
     * @return Deposit
     */
    public function setDepositReceipt($depositReceipt) {
        $this->depositReceipt = $depositReceipt;

        return $this;
    }

    /**
     * Get depositReceipt.
     *
     * @return string
     */
    public function getDepositReceipt() {
        return $this->depositReceipt;
    }

    /**
     * Set processingLog.
     *
     * @param string $processingLog
     *
     * @return Deposit
     */
    public function setProcessingLog($processingLog) {
        $this->processingLog = $processingLog;

        return $this;
    }

    /**
     * Get processingLog.
     *
     * @return string
     */
    public function getProcessingLog() {
        return $this->processingLog;
    }

    /**
     * Append to the processing history.
     *
     * @param string $content
     */
    public function addToProcessingLog($content) {
        $date = date(DateTime::ATOM);
        $this->processingLog .= "{$date}\n{$content}\n\n";
    }

    /**
     * Set harvestAttempts.
     *
     * @param int $harvestAttempts
     *
     * @return Deposit
     */
    public function setHarvestAttempts($harvestAttempts) {
        $this->harvestAttempts = $harvestAttempts;

        return $this;
    }

    /**
     * Get harvestAttempts.
     *
     * @return int
     */
    public function getHarvestAttempts() {
        return $this->harvestAttempts;
    }

    /**
     * Set journal.
     *
     * @param Journal $journal
     *
     * @return Deposit
     */
    public function setJournal(Journal $journal = null) {
        $this->journal = $journal;

        return $this;
    }

    /**
     * Get journal.
     *
     * @return Journal
     */
    public function getJournal() {
        return $this->journal;
    }

}
