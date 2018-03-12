<?php

namespace AppBundle\Tests\Controller;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class DefaultControllerTest extends BaseTestCase {

    protected function getFixtures() {
        return array(
            LoadDeposit::class,
            LoadUser::class,
        );
    }
    
    public function testAnonIndex() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUserIndex() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminIndex() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonDepositDepositSearch() {
        $client = $this->makeClient();
        $formCrawler = $client->request('GET', '/deposit_search');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserDepositSearch() {
        $client = $this->makeClient(LoadUser::USER);
        $formCrawler = $client->request('GET', '/deposit_search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '4F37',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("978EA2B4-01DB-4F37-BD74-871DDBE71BF5")')->count());
    }

    public function testAdminDepositSearch() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/deposit_search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '4F37',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("978EA2B4-01DB-4F37-BD74-871DDBE71BF5")')->count());
    }

}
