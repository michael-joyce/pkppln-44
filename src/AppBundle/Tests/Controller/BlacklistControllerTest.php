<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Blacklist;
use AppBundle\DataFixtures\ORM\LoadBlacklist;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class BlacklistControllerTest extends BaseTestCase {

    protected function getFixtures() {
        return [
            LoadUser::class,
            LoadBlacklist::class
        ];
    }

    public function testAnonIndex() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/blacklist/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    public function testUserIndex() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/blacklist/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    public function testAdminIndex() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/blacklist/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('New')->count());
    }

    public function testAnonShow() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/blacklist/1');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
        $this->assertEquals(0, $crawler->selectLink('Delete')->count());
    }

    public function testUserShow() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/blacklist/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
        $this->assertEquals(0, $crawler->selectLink('Delete')->count());
    }

    public function testAdminShow() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/blacklist/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('Edit')->count());
        $this->assertEquals(1, $crawler->selectLink('Delete')->count());
    }

    public function testAnonSearch() {
        $client = $this->makeClient();
        $formCrawler = $client->request('GET', '/blacklist/search');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }
    
    public function testUserSearch() {
        $client = $this->makeClient(LoadUser::USER);
        $formCrawler = $client->request('GET', '/blacklist/search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '4EED',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("AC54ED1A-9795-4EED-94FD-D80CB62E0C84")')->count());
    }
    
    public function testAdminSearch() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/blacklist/search');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '4EED',
        ]);
        $client->submit($form);
        $this->assertEquals(200, $client->getresponse()->getStatusCode());
        $this->assertEquals(1, $client->getCrawler()->filter('td:contains("AC54ED1A-9795-4EED-94FD-D80CB62E0C84")')->count());
    }
    
    public function testAnonEdit() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/blacklist/1/edit');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testUserEdit() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/blacklist/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/blacklist/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Update')->form([
            'blacklist[uuid]' => '77E72F60-67B0-43AE-95FF-14F16BBF4B30',
            'blacklist[comment]' => 'Testing.'
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/blacklist/1'));
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("77E72F60-67B0-43AE-95FF-14F16BBF4B30")')->count());
    }

    public function testAnonNew() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/blacklist/new');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testUserNew() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/blacklist/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminNew() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/blacklist/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Create')->form([
            'blacklist[uuid]' => '77E72F60-67B0-43AE-95FF-14F16BBF4B30',
            'blacklist[comment]' => 'Testing.'
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("77E72F60-67B0-43AE-95FF-14F16BBF4B30")')->count());
    }

    public function testAnonDelete() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/blacklist/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testUserDelete() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/blacklist/1/delete');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminDelete() {
        $preCount = count($this->em->getRepository(Blacklist::class)->findAll());
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/blacklist/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->em->clear();
        $postCount = count($this->em->getRepository(Blacklist::class)->findAll());
        $this->assertEquals($preCount - 1, $postCount);
    }

}
