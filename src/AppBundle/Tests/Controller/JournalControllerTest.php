<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Journal;
use AppBundle\DataFixtures\ORM\LoadJournal;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class JournalControllerTest extends BaseTestCase {

    protected function getFixtures() {
        return [
            LoadUser::class,
            LoadJournal::class
        ];
    }

    public function testAnonIndex() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/journal/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testUserIndex() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/journal/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminIndex() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/journal/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonShow() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/journal/1');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testUserShow() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/journal/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminShow() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/journal/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonSearch() {
        $client = $this->makeClient();
        $formCrawler = $client->request('GET', '/journal/search');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserSearch() {
        $client = $this->makeClient(LoadUser::USER);
        $formCrawler = $client->request('GET', '/journal/search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '5D69',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("CBF45637-5D69-44C3-AEC0-A906CBC3E27B")')->count());
    }

    public function testAdminSearch() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/journal/search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '5D69',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("CBF45637-5D69-44C3-AEC0-A906CBC3E27B")')->count());
    }

    public function testAnonPing() {
        $client = $this->makeClient();
        $client->request('GET', '/journal/1/ping');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserPing() {
        $client = $this->makeClient(LoadUser::USER);
        $client->request('GET', '/journal/1/ping');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminPing() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $client->request('GET', '/journal/1/ping');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}
