<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Deposit;
use AppBundle\Utilities\ServiceDocument;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\EngineInterface;
use function GuzzleHttp\Psr7\str;

/**
 * Description of SwordClient.
 */
class SwordClient {

    /**
     * Configuration for the harvester client.
     */
    const CONF = array(
        'allow_redirects' => false,
        'headers' => array(
            'User-Agent' => 'PkpPlnBot 1.0; http://pkp.sfu.ca',
        ),
        'decode_content' => false,
    );

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var FilePaths
     */
    private $fp;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var Client
     */
    private $client;
    private $serviceUri;
    private $saveXml;
    private $uuid;

    /**
     *
     */
    public function __construct($serviceUri, $uuid, $saveXml, FilePaths $filePaths, EngineInterface $templating) {
        $this->serviceUri = $serviceUri;
        $this->uuid = $uuid;
        $this->saveXml = $saveXml;
        $this->fp = $filePaths;
        $this->templating = $templating;
        $this->fs = new Filesystem();
        $this->client = new Client(self::CONF);
    }

    /**
     * Set the HTTP client, usually based on Guzzle.
     *
     * @param Client $client
     */
    public function setClient(Client $client) {
        $this->client = $client;
    }

    /**
     * Set the file system client.
     *
     * @param Filesystem $fs
     */
    public function setFilesystem(Filesystem $fs) {
        $this->fs = $fs;
    }

    /**
     *
     */
    public function setServiceUri($serviceUri) {
        $this->serviceUri = $serviceUri;
    }

    /**
     *
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    /**
     *
     */
    public function serviceDocument() {
        try {
            $response = $this->client->get($this->serviceUri, array(
            'headers' => array('On-Behalf-Of' => $this->uuid),
            ));
            return new ServiceDocument($response->getBody());
        } catch (RequestException $e) {
            $message = str($e->getRequest());
            if ($e->hasResponse()) {
                $message .= "\n\n" . str($e->getResponse());
            }
            throw new Exception($message);
        }
    }

    /**
     *
     */
    public function createDeposit(Deposit $deposit) {
        $sd = $this->serviceDocument();
        $xml = $this->templating->render('AppBundle:sword:deposit.xml.twig', array(
            'deposit' => $deposit,
        ));
        if ($this->saveXml) {
            $path = $this->fp->getStagingBagPath($deposit) . '.xml';
            $this->fs->dumpFile($path, $xml);
        }
        try {
            $response = $this->client->request('POST', $sd->getCollectionUri(), array(
            'body' => $xml,
            ));
        } catch (RequestException $e) {
            $message = str($e->getRequest());
            if ($e->hasResponse()) {
                $message .= "\n\n" . str($e->getResponse());
            }
            $deposit->addErrorLog($message);
            throw new Exception($message);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $deposit->addErrorLog($message);
            throw new Exception($message);
        }
        $deposit->setDepositReceipt($response->getHeader('Location'));
        $deposit->setDepositDate(new DateTime());
        return true;
    }

}
