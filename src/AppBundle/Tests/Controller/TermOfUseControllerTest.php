<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\TermOfUse;
use AppBundle\DataFixtures\ORM\LoadTermOfUse;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class TermOfUseControllerTest extends BaseTestCase
{

    protected function getFixtures() {
        return [
            LoadUser::class,
            LoadTermOfUse::class
        ];
    }

    public function testAnonIndex() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/termofuse/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    public function testUserIndex() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/termofuse/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    public function testAdminIndex() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/termofuse/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('New')->count());
    }

    public function testAnonShow() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/termofuse/1');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
        $this->assertEquals(0, $crawler->selectLink('Delete')->count());
    }

    public function testUserShow() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/termofuse/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
        $this->assertEquals(0, $crawler->selectLink('Delete')->count());
    }

    public function testAdminShow() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/termofuse/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('Edit')->count());
        $this->assertEquals(1, $crawler->selectLink('Delete')->count());
    }
    public function testAnonEdit() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/termofuse/1/edit');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testUserEdit() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/termofuse/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/termofuse/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Update')->form([
            'term_of_use[weight]' => '1',
            'term_of_use[keyCode]' => 'test.newcode',
            'term_of_use[content]' => 'This is a test term.',
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/termofuse/1'));
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // there should be two test.newcode table cells, one for the term and
        // one for the history.
        $this->assertEquals(2, $responseCrawler->filter('td:contains("test.newcode")')->count());
    }

    public function testAnonNew() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/termofuse/new');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testUserNew() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/termofuse/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminNew() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/termofuse/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Create')->form([
            'term_of_use[weight]' => '1',
            'term_of_use[keyCode]' => 'test.code',
            'term_of_use[content]' => 'This is a test term.',
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // there should be two test.code table cells, one for the term and
        // one for the history.
        $this->assertEquals(2, $responseCrawler->filter('td:contains("test.code")')->count());
    }

    public function testAnonDelete() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/termofuse/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testUserDelete() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/termofuse/1/delete');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminDelete() {
        $preCount = count($this->em->getRepository(TermOfUse::class)->findAll());
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/termofuse/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->em->clear();
        $postCount = count($this->em->getRepository(TermOfUse::class)->findAll());
        $this->assertEquals($preCount - 1, $postCount);
    }

}
