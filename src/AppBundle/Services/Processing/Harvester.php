<?php

/*
 * Copyright (C) 2015-2016 Michael Joyce <ubermichael@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Services\Processing;

use AppBundle\Entity\Deposit;
use AppBundle\Services\FilePaths;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Harvest a deposit from a journal. Attempts to check file sizes via HTTP HEAD
 * before downloading, and checks that there will be sufficient disk space.
 */
class Harvester {

    /**
     * Configuration for the harvester client.
     */
    const CONF = array(
        'allow_redirects' => true,
        'headers' => array(
            'User-Agent' => 'PkpPlnBot 1.0; http://pkp.sfu.ca',
        ),
        'decode_content' => false,
    );

    /**
     * File sizes reported via HTTP HEAD must this close to to the file size
     * as reported in the deposit. Threshold = 0.02 is 2%.
     */
    const FILE_SIZE_THRESHOLD = 0.08;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var int
     */
    private $maxAttempts;

    /**
     * @var FilePaths
     */
    private $filePaths;

    /**
     *
     */
    public function __construct($maxHarvestAttempts, FilePaths $filePaths) {
        $this->maxAttempts = $maxHarvestAttempts;
        $this->filePaths = $filePaths;
        $this->fs = new FileSystem();
        $this->client = new Client();
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
     * Write a deposit's data to the filesystem at $path. Returns true on
     * success and false on failure.
     *
     * @param string $path
     * @param Response $response
     *
     * @return bool
     */
    public function writeDeposit($path, ResponseInterface $response) {
        $body = $response->getBody();
        if (!$body) {
            throw new Exception('Response body was empty.');
        }
        if ($this->fs->exists($path)) {
            $this->fs->remove($path);
        }
        // 64k chunks.
        while ($bytes = $body->read(64 * 1024)) {
            $this->fs->appendToFile($path, $bytes);
        }
        return true;
    }

    /**
     * Fetch a deposit URL with Guzzle. Returns the data on success or false
     * on failure.
     *
     * @param string $url
     *
     * @return Response
     *
     * @throws Exception
     */
    public function fetchDeposit($url) {
        $response = $this->client->get($url, self::CONF);
        if ($response->getStatusCode() !== 200) {
            throw new Exception("Harvest download error - {$url} - HTTP {$response->getHttpStatus()} - {$url} - {$response->getError()}");
        }
        return $response;
    }

    /**
     * Send an HTTP HEAD request to get the deposit's host to get an estimate
     * of the download size.
     *
     * @param mixed $deposit
     *
     * @throws Exception
     */
    public function checkSize(Deposit $deposit) {
        $response = $this->client->head($deposit->getUrl(), self::CONF);
        if ($response->getStatusCode() != 200 || !$response->hasHeader('Content-Length')) {
            throw new Exception("HTTP HEAD request cannot check file size: HTTP {$response->getStatusCode()} - {$response->getReasonPhrase()} - {$deposit->getUrl()}");
        }
        $values = $response->getHeader('Content-Length');
        $reported = (int) $values[0];
        if ($reported === 0) {
            throw new Exception("HTTP HEAD response does not include file size: HTTP {$response->getStatusCode()} - {$response->getReasonPhrase()} - {$deposit->getUrl()}");
        }
        $expected = $deposit->getSize() * 1000;
        $difference = abs($reported - $expected) / (($reported + $expected) / 2.0);
        if ($difference > self::FILE_SIZE_THRESHOLD) {
            throw new Exception("Expected file size {$expected} is not close to reported size {$reported}");
        }
    }

    /**
     * Process one deposit. Fetch the data and write it to the file system.
     * Updates the deposit status.
     *
     * @param Deposit $deposit
     *
     * @return bool
     */
    public function processDeposit(Deposit $deposit) {
        if ($deposit->getHarvestAttempts() > $this->maxAttempts) {
            $deposit->setState('harvest-error');
            return false;
        }
        try {
            $deposit->setHarvestAttempts($deposit->getHarvestAttempts() + 1);
            $this->checkSize($deposit);
            $response = $this->fetchDeposit($deposit->getUrl(), $deposit->getSize());
            $deposit->setFileType($response->getHeaderLine('Content-Type'));
            $filePath = $this->filePaths->getHarvestFile($deposit);
            return $this->writeDeposit($filePath, $response);
        } catch (Exception $e) {
            $deposit->addToProcessingLog($e->getMessage());
            return null;
        }
    }

}
