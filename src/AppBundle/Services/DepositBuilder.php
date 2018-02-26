<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Deposit;
use AppBundle\Entity\Journal;
use AppBundle\Utilities\Xpath;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of DepositBuilder
 */
class DepositBuilder {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $generator) {
        $this->em = $em;
        $this->generator = $generator;
    }

    /**
     * @param string $uuid
     * @return Deposit
     */
    protected function findDeposit($uuid) {
        $deposit = $this->em->getRepository(Deposit::class)->findOneBy(array(
            'depositUuid' => strtoupper($uuid),
        ));
        $action = 'edit';
        if (!$deposit) {
            $action = 'add';
            $deposit = new Deposit();
            $deposit->setDepositUuid($uuid);
        }
        if ($action === 'add') {
            $deposit->addToProcessingLog('Deposit received.');
        } else {
            $deposit->addToProcessingLog('Deposit edited or reset by journal manager.');
        }
        $deposit->setAction($action);
        return $deposit;
    }

    /**
     * Build a deposit from XML.
     *
     * @param Journal $journal
     * @param SimpleXMLElement $xml
     *
     * @return Deposit
     */
    public function fromXml(Journal $journal, SimpleXMLElement $xml) {
        $id = Xpath::getXmlValue($xml, '//atom:id');
        $deposit = $this->findDeposit(substr($id, 9, 36));
        $deposit->setState('depositedByJournal');
        $deposit->setChecksumType(Xpath::getXmlValue($xml, 'pkp:content/@checksumType'));
        $deposit->setChecksumValue(Xpath::getXmlValue($xml, 'pkp:content/@checksumValue'));
        $deposit->setFileType('');
        $deposit->setIssue(Xpath::getXmlValue($xml, 'pkp:content/@issue'));
        $deposit->setVolume(Xpath::getXmlValue($xml, 'pkp:content/@volume'));
        $deposit->setPubDate(new DateTime(Xpath::getXmlValue($xml, 'pkp:content/@pubdate')));
        $deposit->setJournal($journal);
        $deposit->setSize(Xpath::getXmlValue($xml, 'pkp:content/@size'));
        $deposit->setUrl(html_entity_decode(Xpath::getXmlValue($xml, 'pkp:content')));

        $deposit->setJournalVersion(Xpath::getXmlValue($xml, 'pkp:content/@ojsVersion', Deposit::DEFAULT_JOURNAL_VERSION));
        foreach ($xml->xpath('//pkp:license/node()') as $node) {
            $deposit->addLicense($node->getName(), (string) $node);
        }
        $this->em->persist($deposit);
        return $deposit;
    }

}
