<?php

namespace AppBundle\Services\Processing;

require_once 'vendor/scholarslab/bagit/lib/bagit.php';

use AppBundle\Entity\Deposit;
use AppBundle\Services\FilePaths;
use Socket\Raw\Factory;
use Symfony\Component\Filesystem\Filesystem;
use Xenolope\Quahog\Client;

/**
 *
 */
class VirusScanner {

    /**
     * @var FilePaths
     */
    private $filePaths;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Client
     */
    private $client;
    
    public function __construct($socketPath, FilePaths $filePaths) {        
        $this->filePaths = $filePaths;
        $this->fs = new FileSystem();
        $factory = new Factory();
        $socket = $factory->createClient('unix://' . $socketPath);
        $this->client = new Client($socket);
    }
    
    public function setClient(Client $client) {
        $this->client = $client;
    }

    protected function processDeposit(Deposit $deposit) {
        echo "scanning {$deposit->getDepositUuid()}";
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);
        $result = $this->client->scanFile($harvestedPath);
        dump($result);
        return null;
    }
}
