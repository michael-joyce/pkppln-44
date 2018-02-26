<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Journal;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

/**
 * Description of Ping
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
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Client
     */
    private $client;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        $this->client = new Client();
    }

    public function setClient(Client $client) {
        $this->client = $client;
    }

    public function ping(Journal $journal) {
        try {
            $response = $this->client->get($journal->getGatewayUrl(), self::CONF);
            
        } catch (Exception $ex) {
            
        }
    }

}
