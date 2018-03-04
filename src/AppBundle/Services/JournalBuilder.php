<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Services;

use AppBundle\Entity\Journal;
use AppBundle\Utilities\Xpath;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;

/**
 * Description of JournalBuilder.
 */
class JournalBuilder {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     *
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * Build and persist a journal from XML.
     *
     * Does not flush the journal to the database.
     *
     * @param SimpleXMLElement $xml
     * @param string $uuid
     *
     * @return Journal
     */
    public function fromXml(SimpleXMLElement $xml, $uuid) {
        $journal = $this->em->getRepository('AppBundle:Journal')->findOneBy(array(
        'uuid' => strtoupper($uuid),
        ));
        if ($journal === null) {
            $journal = new Journal();
        }
        $journal->setUuid($uuid);
        $journal->setTitle(Xpath::getXmlValue($xml, '//atom:title'));
        // &amp; -> &.
        $journal->setUrl(html_entity_decode(Xpath::getXmlValue($xml, '//pkp:journal_url')));
        $journal->setEmail(Xpath::getXmlValue($xml, '//atom:email'));
        $journal->setIssn(Xpath::getXmlValue($xml, '//pkp:issn'));
        $journal->setPublisherName(Xpath::getXmlValue($xml, '//pkp:publisherName'));
        // &amp; -> &.
        $journal->setPublisherUrl(html_entity_decode(Xpath::getXmlValue($xml, '//pkp:publisherUrl')));
        $journal->setContacted(new \DateTime());
        $this->em->persist($journal);

        return $journal;
    }
    
    /**
     * The journal with UUID $uuid has contacted the PLN.
     *
     * @param string $uuid
     * @param string $url
     *
     * @return Journal
     */
    public function fromRequest($uuid, $url) {
        $journal = $this->em->getRepository('AppBundle:Journal')->findOneBy(array(
        'uuid' => strtoupper($uuid),
        ));
        if ($journal === null) {
            $journal = new Journal();
            $journal->setUuid($uuid);
            $journal->setTitle('unknown');
            $journal->setIssn('unknown');
            $journal->setStatus('new');
            $journal->setEmail('unknown@unknown.com');
            $this->em->persist($journal);
        }
        $journal->setUrl($url);
        $journal->setContacted(new \DateTime());
        if ($journal->getStatus() !== 'new') {
            $journal->setStatus('healthy');
        }
        return $journal;
    }

}
