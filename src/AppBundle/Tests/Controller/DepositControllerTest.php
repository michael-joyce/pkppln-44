<?php

namespace AppBundle\Tests\Controller;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class DepositControllerTest extends BaseTestCase {

    protected function getFixtures() {
        return [
            LoadUser::class,
            LoadDeposit::class,
            LoadJournal::class,
        ];
    }

    public function testAnonIndex() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/journal/1/deposit/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testUserIndex() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/journal/1/deposit/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminIndex() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/journal/1/deposit/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonShow() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/journal/1/deposit/1');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testUserShow() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/journal/1/deposit/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminShow() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/journal/1/deposit/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonSearch() {
        $client = $this->makeClient();
        $formCrawler = $client->request('GET', '/journal/2/deposit/search');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserSearch() {
        $client = $this->makeClient(LoadUser::USER);
        $formCrawler = $client->request('GET', '/journal/2/deposit/search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => 'A584',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("92ED9A27-A584-4487-A3F9-997379FBA182")')->count());
    }

    public function testAdminSearch() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/journal/2/deposit/search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => 'A584',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("92ED9A27-A584-4487-A3F9-997379FBA182")')->count());
    }

}
