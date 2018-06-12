<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Deposit;
use AppBundle\Utilities\Namespaces;
use AppBundle\Utilities\ServiceDocument;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use SimpleXMLElement;
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

    /**
     * URL for the service document.
     *
     * @var string
     */
    private $serviceUri;

    /**
     * If true, save the deposit XML at /path/to/deposit.zip.xml.
     *
     * @var bool
     */
    private $saveXml;

    /**
     * Staging server UUID.
     *
     * @var string
     */
    private $uuid;

    /**
     * Construct the sword client.
     *
     * @param string $serviceUri
     * @param string $uuid
     * @param bool $saveXml
     * @param FilePaths $filePaths
     * @param EngineInterface $templating
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
     * Set or override the HTTP client, usually based on Guzzle.
     *
     * @param Client $client
     */
    public function setClient(Client $client) {
        $this->client = $client;
    }

    /**
     * Set or override  the file system client.
     *
     * @param Filesystem $fs
     */
    public function setFilesystem(Filesystem $fs) {
        $this->fs = $fs;
    }

    /**
     * Set or override the service document URI.
     *
     * @param string $serviceUri
     */
    public function setServiceUri($serviceUri) {
        $this->serviceUri = $serviceUri;
    }

    /**
     * Set or override the UUID.
     *
     * @param string $uuid
     */
    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param mixed $xml
     * @param Deposit $deposit
     * @param array $options
     *
     * @return Response
     *
     * @throws Exception
     */
    public function request($method, $url, array $headers = [], $xml = null, Deposit $deposit = null, array $options = []) {
        try {
            $request = new Request($method, $url, $headers, $xml);
            $response = $this->client->send($request, $options);
            return $response;
        } catch (RequestException $e) {
            $message = str($e->getRequest());
            if ($e->hasResponse()) {
                $message .= "\n\n" . str($e->getResponse());
            }
            if($deposit) {
                $deposit->addErrorLog($message);
            }
            throw new Exception($message);
        } catch (Exception $e) {
            $message = $e->getMessage();
            if($deposit) {
                $deposit->addErrorLog($message);
            }
            throw new Exception($message);
        }
    }

    /**
     * Fetch the service document.
     *
     * @return ServiceDocument
     *
     * @throws Exception
     */
    public function serviceDocument() {
        $response = $this->request('GET', $this->serviceUri, array(
            'On-Behalf-Of' => $this->uuid,
        ));
        return new ServiceDocument($response->getBody());
    }

    /**
     * Create a deposit in LOCKSSOMatic.
     *
     * @param Deposit $deposit
     *
     * @return bool
     *
     * @throws Exception
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
        $response = $this->request('POST', $sd->getCollectionUri(), array(), $xml, $deposit);
        $locationHeader = $response->getHeader('Location');
        if (count($locationHeader) > 0) {
            $deposit->setDepositReceipt($locationHeader[0]);
        }
        $deposit->setDepositDate(new DateTime());
        return true;
    }

    public function receipt(Deposit $deposit) {
        if( ! $deposit->getDepositReceipt()) {
            return null;
        }
        $response = $this->request('GET', $deposit->getDepositReceipt(), array(), null, $deposit);
        $xml = new SimpleXMLElement($response->getBody());
        Namespaces::registerNamespaces($xml);
        return $xml;
    }

    public function statement(Deposit $deposit) {
        $receiptXml = $this->receipt($deposit);
        $statementUrl = (string)$receiptXml->xpath('atom:link[@rel="http://purl.org/net/sword/terms/statement"]/@href')[0];
        $response = $this->request('GET', $statementUrl, array(), null, $deposit);
        $statementXml = new SimpleXMLElement($response->getBody());
        Namespaces::registerNamespaces($statementXml);
        return $statementXml;
    }

    public function fetch(Deposit $deposit){
        $statement = $this->statement($deposit);
        $original = $statement->xpath('//sword:originalDeposit/@href')[0];
        $filepath = $this->fp->getRestoreFile($deposit);

        $this->request('GET', $original, array(), null, $deposit, array(
            'allow_redirects' => false,
            'decode_content' => false,
            'save_to' => $filepath,
        ));
        return $filepath;
    }

}
