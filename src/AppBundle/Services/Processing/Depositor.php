<?php

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\SwordClient;

/**
 * Send a fully processed deposit to LOCKSSOMatic.
 *
 * @see SwordClient
 */
class Depositor {

    /**
     * @var SwordClient
     */
    private $client;
    
    /**
     * @var string
     */
    private $heldVersions;

    /**
     * Build the service.
     *
     * @param SwordClient $client
     * @param string $heldVersions
     */
    public function __construct(SwordClient $client, $heldVersions) {
        $this->client = $client;
        $this->heldVersions = $heldVersions;
    }

    /**
     * Process one deposit.
     *
     * @param Deposit deposit
     *
     * @return bool|null|string
     */
    public function processDeposit(Deposit $deposit) {
        if ($this->heldVersions && version_compare($deposit->getJournalVersion(), $this->heldVersions, ">=")) {
            return "hold";
        }
        return $this->client->createDeposit($deposit);
    }

}
