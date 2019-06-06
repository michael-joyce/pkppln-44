<?php

namespace AppBundle\Tests\Controller\SwordController;

use AppBundle\DataFixtures\ORM\LoadBlacklist;
use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\DataFixtures\ORM\LoadTermOfUse;
use AppBundle\DataFixtures\ORM\LoadWhitelist;
use AppBundle\Utilities\Namespaces;
use Exception;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use SimpleXMLElement;
use Symfony\Component\BrowserKit\Client;

abstract class AbstractSwordTestCase extends BaseTestCase {

    /**
     * @var Client
     */
    protected $testClient;

    public function setUp() : void  {
        parent::setUp();
        $this->testClient = static::createClient();
    }

    public function getFixtures() {
        return array(
            LoadJournal::class,
            LoadDeposit::class,
            LoadTermOfUse::class,
            LoadWhitelist::class,
            LoadBlacklist::class,
        );
    }

    /**
     * @return SimpleXMLElement
     * @param Client $client
     */
    protected function getXml() {
        $xml = new SimpleXMLElement($this->testClient->getResponse()->getContent());
        Namespaces::registerNamespaces($xml);
        return $xml;
    }

    /**
     * Get a single XML value as a string.
     *
     * @param SimpleXMLElement $xml
     * @param type $xpath
     * @return string
     * @throws Exception
     */
    public function getXmlValue(SimpleXMLElement $xml, $xpath) {
        $data = $xml->xpath($xpath);
        if (count($data) === 1) {
            return trim((string) $data[0]);
        }
        if (count($data) === 0) {
            return null;
        }
        throw new Exception("Too many elements for '{$xpath}'");
    }

}
