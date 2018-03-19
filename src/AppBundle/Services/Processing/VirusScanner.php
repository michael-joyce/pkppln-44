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
    
    const DEFAULT_BUFFER_SIZE = 64 * 1024;

    /**
     * @var FilePaths
     */
    private $filePaths;

    /**
     * @var string
     */
    private $socketPath;

    /**
     * @var Factory
     */
    private $factory;
    
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var int
     */
    private $bufferSize;

    /**
     * Construct the virus scanner.
     * 
     * @param string $socketPath
     * @param FilePaths $filePaths
     */
    public function __construct($socketPath, FilePaths $filePaths) {
        $this->filePaths = $filePaths;
        $this->socketPath = $socketPath;
        $this->factory = new Factory();
        $this->fs = new Filesystem();
        $this->bufferSize = self::DEFAULT_BUFFER_SIZE;
    }

    /**
     * Set the socket factory.
     */
    public function setFactory(Factory $factory) {
        $this->factory = $factory;
    }

    /**
     * @return Client
     */
    public function getClient() {
        $socket = $this->factory->createClient('unix://' . $this->socketPath);
        $client = new Client($socket);
        $client->startSession();
        return $client;
    }

    public function setBufferSize($size) {
        $this->size = $size;
    }
    
    /**
     * Scan an embedded file.
     * 
     * @param DOMElement $embed
     * @param DOMXpath $xp
     * @param Client $client
     * @return string
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
    
    public function scanXmlFile($pathname, Client $client, XmlParser $parser = null) {
        if( ! $parser) {
            $parser = new XmlParser();
        }
        $dom = $parser->fromFile($pathname);
        $xp = new DOMXPath($dom);
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
     * @param Client $client
     * @return string
     */
    public function scanEmbededFiles(PharData $phar, Client $client) {
        $results = array();
        $parser = new XmlParser();
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            if (substr($file->getFilename(), -4) !== '.xml') {
                continue;
            }
            $results[] = $this->scanXmlFile($file->getPathname, $client, $parser);
        }
        
        return $results;
    }
    
    /**
     * Scan an archive.
     * 
     * @param PharData $phar
     * @param Client $client
     * @return string
     */
    public function scanArchiveFiles(PharData $phar, Client $client) {
        $results = array();
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            $fh = fopen($file->getPathname(), 'rb');
            $r = $client->scanResourceStream($fh);
            if ($r['status'] === 'OK') {
                $results[] = $file->getFileName() . ' ' . 'OK';
            } else {
                $results[$file->getFileName()] = $file->getFileName() . ' ' . $r['status'] . ': ' . $r['reason'];
            }
        }
        
        return $results;
    }

    /**
     * Process one deposit.
     * 
     * @param Deposit $deposit
     * @param Client $client
     * @return boolean
     */
    public function processDeposit(Deposit $deposit, Client $client = null) {
        if($client === null) {
            $client = $this->getClient();
        }
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);
        $phar = new PharData($harvestedPath);
        
        $baseResult = array();
        $r = $client->scanFile($harvestedPath);
        if ($r['status'] === 'OK') {
            $baseResult[] = basename($harvestedPath) . ' OK';
        } else {
            $baseResult[] = basename($harvestedPath) . ' ' . $r['status'] . ': ' . $r['reason'];
        }
        $archiveResult = $this->scanArchiveFiles($phar, $client);
        $embeddedResult = $this->scanEmbededFiles($phar, $client);
        $deposit->addToProcessingLog(implode("\n", array_merge($baseResult, $archiveResult, $embeddedResult)));
        return true;
    }

}
