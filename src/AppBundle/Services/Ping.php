<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Journal;
use AppBundle\Entity\Whitelist;
use AppBundle\Utilities\PingResult;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;

/**
 * Description of Ping.
 */
class Ping {

    const CONF = array(
        'allow_redirects' => true,
        'headers' => array(
            'User-Agent' => 'PkpPlnBot 1.0; http://pkp.sfu.ca',
            'Accept' => 'application/xml,text/xml,*/*;q=0.1',
        ),
    );
    
    /**
     * @var string
     */
    private $minOjsVersion;

    /**
     * @var EntityManagerInterface
     */
    private $em;
    
    /**
     * @var BlackWhiteList
     */
    private $list;

    /**
     * @var Client
     */
    private $client;

    /**
     *
     */
    public function __construct($minOjsVersion, EntityManagerInterface $em, BlackWhiteList $list) {
        $this->minOjsVersion = $minOjsVersion;
        $this->em = $em;
        $this->list = $list;
        $this->client = new Client();
    }

    /**
     *
     */
    public function setClient(Client $client) {
        $this->client = $client;
    }

    /**
     *
     */
    public function process(Journal $journal, PingResult $result) {
        if ($result->getHttpStatus() !== 200) {
            $journal->setStatus('ping-error');
            return;
        }
        if (!$result->getOjsRelease()) {
            $journal->setStatus('ping-error');
            return;
        }
        $journal->setContacted(new DateTime());
        $journal->setTitle($result->getJournalTitle());
        $journal->setOjsVersion($result->getOjsRelease());
        $journal->setTermsAccepted($result->areTermsAccepted() === 'yes');
        $journal->setStatus('healthy');
        if (version_compare($result->getOjsRelease(), $this->minOjsVersion, '<')) {
            return;
        }
        if ($this->list->isListed($journal->getUuid())) {
            return;
        }
        $whitelist = new Whitelist();
        $whitelist->setUuid($journal->getUuid());
        $whitelist->setComment("{$journal->getUrl()} added by ping.");
        $this->em->persist($whitelist);
    }
    
    /**
     *
     * @param Journal $journal
     * @return PingResult
     */
    public function ping(Journal $journal) {
        try {
            $response = $this->client->get($journal->getGatewayUrl(), self::CONF);
            $result = new PingResult($response);
            $this->process($journal, $result);
            return $result;
        } catch (Exception $e) {
            $journal->setStatus('ping-error');
        }
    }

}
