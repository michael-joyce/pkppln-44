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
     * @var FilePaths
     */
    private $filePaths;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string 
     */
    private $socketPath;

    /**
     * @var Factory
     */
    private $factory;

    public function __construct($socketPath, FilePaths $filePaths) {
        $this->filePaths = $filePaths;
        $this->socketPath = $socketPath;
        $this->fs = new FileSystem();
        $this->factory = new Factory();
    }

    public function setFactory(Factory $factory) {
        $this->factory = $factory;
    }

    protected function getClient() {
        $socket = $this->factory->createClient('unix://' . $this->socketPath);
        $client = new Client($socket);
        $client->startSession();
        return $client;
    }
    
    public function scanEmbed(DOMElement $embed, DOMXpath $xp, Client $client) {
        $fh = tmpfile();
        $chunkSize = 1024*64; // 64kb chunks
        $length = $xp->evaluate('string-length(./text())', $embed);
        $offset = 1; //xpath starts at 1
        while($offset < $length) {
            $end = $offset + $chunkSize;
            $chunk = $xp->evaluate("substring(./text(), {$offset}, {$chunkSize})", $embed);
            fwrite($fh, base64_decode($chunk));
            $offset = $end;
        }
        return $client->scanResourceStream($fh);
    }
    
    public function scanEmbededFiles(PharData $phar, Client $client) {
        $results = array();
        foreach(new RecursiveIteratorIterator($phar) as $file) {
            if(substr($file->getFilename(), -4) !== '.xml') {
                continue;
            }
            $parser = new XmlParser();
            $dom = $parser->fromFile($file->getPathname());
            $xp = new DOMXPath($dom);
            foreach($xp->query('//embed') as $embed) {
                $filename = $embed->attributes->getNamedItem('filename')->nodeValue;
                $r = $this->scanEmbed($embed, $xp, $client);
                if($r['status'] === 'OK') {
                    $results[$filename] = 'OK';
                } else {
                    $results[$filename] = $r['status'] . ': ' . $r['reason'];
                }
            }            
        }
        
        return $results;
    }
    
    public function scanArchiveFiles(PharData $phar, Client $client) {
        $results = array();
        foreach(new RecursiveIteratorIterator($phar) as $file) {
            $fh = fopen($file->getPathname(), 'rb');
            $r = $client->scanResourceStream($fh);
            if($r['status'] === 'OK') {
                $results[$file->getFileName()] = 'OK';
            } else {
                $results[$file->getFileName()] = $r['status'] . ': ' . $r['reason'];
            }
        }
        
        return $results;
    }

    public function processDeposit(Deposit $deposit) {
        $client = $this->getClient();
        $harvestedPath = $this->filePaths->getHarvestFile($deposit);
        $phar = new PharData($harvestedPath);
        
        $baseResult = array();        
        $r = $client->scanFile($harvestedPath);
        if($r['status'] === 'OK') {
            $baseResult[basename($harvestedPath)] = 'OK';
        } else {
            $baseResult[basename($harvestedPath)] = $r['status'] . ': ' . $r['reason'];
        }
        $archiveResult = $this->scanArchiveFiles($phar, $client);
        $embeddedResult = $this->scanEmbededFiles($phar, $client);
        dump(array_merge($baseResult, $archiveResult, $embeddedResult));
        return null;
    }

}
