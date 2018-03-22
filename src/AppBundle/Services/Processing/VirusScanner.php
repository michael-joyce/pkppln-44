<?php

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\FilePaths;
use AppBundle\Utilities\XmlParser;
use DOMElement;
use DOMXPath;
use PharData;
use RecursiveIteratorIterator;
use Socket\Raw\Factory;
use Symfony\Component\Filesystem\Filesystem;
use Xenolope\Quahog\Client;

/**
 * Virus scanning service, via ClamAV.
 */
class VirusScanner {
    
    /**
     * File path service.
     *
     * @var FilePaths
     */
    private $filePaths;

    /**
     * Path to the ClamAV socket.
     *
     * @var string
     */
    private $socketPath;

    /**
     * Socket factory, for use with the Quahog ClamAV interface.
     *
     * @var Factory
     */
    private $factory;
    
    /**
     * Filesystem client.
     *
     * @var Filesystem
     */
    private $fs;

    /**
     * Construct the virus scanner.
     *
     * @param string $socketPath
     *   Path to the clamd socket.
     * @param FilePaths $filePaths
     *   FilePath service.
     */
    public function __construct($socketPath, FilePaths $filePaths) {
        $this->filePaths = $filePaths;
        $this->socketPath = $socketPath;
        $this->factory = new Factory();
        $this->fs = new Filesystem();
    }

    /**
     * Set the socket factory.
     *
     * @param Factory $factory
     *   Override the default socket factory.
     */
    public function setFactory(Factory $factory) {
        $this->factory = $factory;
    }

    /**
     * Get the Quahog client.
     *
     * The client can't be instantiated in the constructor. If the socket path
     * isn't configured or if the socket isn't set up yet the entire app will
     * fail. Symfony tries it instantiate all services for each request, and if
     * one constructor throws an exception everything gets cranky.
     *
     * @return Client
     *   Fully configured client.
     */
    public function getClient() {
        $socket = $this->factory->createClient('unix://' . $this->socketPath);
        $client = new Client($socket, 30, PHP_NORMAL_READ);
        $client->startSession();
        return $client;
    }

    /**
     * Scan an embedded file.
     *
     * @param DOMElement $embed
     *   DOM Element with the embedded content.
     * @param DOMXpath $xp
     *   XPath context with pointing to the embedded element.
     * @param Client $client
     *   Configured virus scanning client.
     *
     * @return array
     *   Scan details.
     */
    public function scanEmbed(DOMElement $embed, DOMXpath $xp, Client $client) {
        $length = $xp->evaluate('string-length(./text())', $embed);
        // Xpath starts at 1.
        $offset = 1;
        $handle = fopen('php://temp', 'w+');
        while ($offset < $length) {
            $end = $offset + $this->bufferSize;
            $chunk = $xp->evaluate("substring(./text(), {$offset}, {$this->bufferSize})", $embed);
            $data = base64_decode($chunk);
            fwrite($handle, $data);
            $offset = $end;
        }
        rewind($handle);
        return $client->scanResourceStream($handle);
    }
    
    /**
     * Scan an XML file and it's embedded content.
     *
     * @param string $pathname
     *   Path to the file to scan.
     * @param Client $client
     *   Configured virus client.
     * @param XmlParser $parser
     *   Parser to get the XML out of $pathname.
     *
     * @return array
     *   Virus scanning details.
     */
    public function scanXmlFile($pathname, Client $client, XmlParser $parser = null) {
        if (!$parser) {
            $parser = new XmlParser();
        }
        $dom = $parser->fromFile($pathname);
        $xp = new DOMXPath($dom);
        $results = array();
        foreach ($xp->query('//embed') as $embed) {
            $filename = $embed->attributes->getNamedItem('filename')->nodeValue;
            $r = $this->scanEmbed($embed, $xp, $client);
            if ($r['status'] === 'OK') {
                $results[] = $filename . ' OK';
            } else {
                $results[] = $filename . ' ' . $r['status'] . ': ' . $r['reason'];
            }
        }
        return $results;
    }
    
    /**
     * Find all the embedded files in the XML and scan them.
     *
     * @param PharData $phar
     *   Parsed bag data.
     * @param Client $client
     *   Virus scannign client.
     *
     * @return array
     *   Scan details.
     */
    public function scanEmbededFiles(PharData $phar, Client $client) {
        $results = array();
        $parser = new XmlParser();
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            if (substr($file->getFilename(), -4) !== '.xml') {
                continue;
            }
            $results = array_merge($this->scanXmlFile($file->getPathname(), $client, $parser), $results);
        }
        
        return $results;
    }
    
    /**
     * Scan an archive.
     *
     * @param PharData $phar
     *   Parsed compressed bag.
     * @param Client $client
     *   Virus scanning client.
     *
     * @return array
     *   Scan details.
     */
    public function scanArchiveFiles(PharData $phar, Client $client) {
        $results = array();
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            $fh = fopen($file->getPathname(), 'rb');
            $r = $client->scanResourceStream($fh);
            if ($r['status'] === 'OK') {
                $results[] = "{$file->getFileName()} OK";
            } else {
                $results[] = "{$file->getFileName()} {$r['status']}: {$r['reason']}";
            }
        }
        
        return $results;
    }

    /**
     * Process one deposit.
     *
     * @param Deposit $deposit
     *   Deposit to scan and parse.
     * @param Client $client
     *   Optional virus client.
     *
     * @return bool
     *   True if the scan succeeded, regardless of viruses present in the deposit.
     */
    public function processDeposit(Deposit $deposit, Client $client = null) {
        if ($client === null) {
            $client = $this->getClient();
        }
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);
        $basename = basename($harvestedPath);
        $phar = new PharData($harvestedPath);
        
        $baseResult = array();
        $r = $client->scanFile($harvestedPath);
        if ($r['status'] === 'OK') {
            $baseResult[] = "{$basename} OK";
        } else {
            $baseResult[] = "{$basename} {$r['status']}: {$r['reason']}";
        }
        $archiveResult = $this->scanArchiveFiles($phar, $client);
        $embeddedResult = $this->scanEmbededFiles($phar, $client);
        $deposit->addToProcessingLog(implode("\n", array_merge(
            $baseResult,
            $archiveResult,
            $embeddedResult
        )));
        return true;
    }

}
