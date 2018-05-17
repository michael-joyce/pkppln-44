<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Deposit;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadDeposit form.
 */
class LoadDeposit extends Fixture implements DependentFixtureInterface {

    const UUIDS = array(
        'F93A8108-B705-4763-A592-B718B00BD4EA',
        '4ECC5D8B-ECC9-435C-A072-6DCF198ACD6D',
        '92ED9A27-A584-4487-A3F9-997379FBA182',
        '978EA2B4-01DB-4F37-BD74-871DDBE71BF5',
    );

    /**
     * {@inheritDoc}.
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Deposit();
            $fixture->setJournalVersion('2.4.8.' . $i);
            $fixture->setLicense(['Creative Commons']);
            $fixture->setFileType('application/zip');
            $fixture->setDepositUuid(self::UUIDS[$i]);
            $fixture->setReceived(new DateTime("2018-{$i}-{$i}T12:00:00"));
            $fixture->setAction('add');
            $fixture->setVolume(1);
            $fixture->setIssue($i + 1);
            $fixture->setPubDate(new DateTime("2016-{$i}-{$i}T12:00:00"));
            $fixture->setChecksumType('sha1');
            $fixture->setChecksumValue(sha1(self::UUIDS[$i]));
            $fixture->setUrl("http://example.com/path/to/{$i}.zip");
            $fixture->setSize(1000 + $i * 1000);
            $fixture->setState('depositedByJournal');
            $fixture->setErrorLog(array());
            $fixture->setDepositReceipt("http://example.com/receipt/{$i}");
            $fixture->setProcessingLog('');
            $fixture->setJournal($this->getReference('journal.1'));

            $em->persist($fixture);
            $this->setReference('deposit.' . $i, $fixture);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            LoadJournal::class,
        ];
    }

}
