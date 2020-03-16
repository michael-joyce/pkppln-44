<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Controller;

use AppBundle\DataFixtures\ORM\LoadJournal;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class JournalControllerTest extends BaseTestCase {
    protected function getFixtures() {
        return [
            LoadUser::class,
            LoadJournal::class,
        ];
    }

    public function testAnonIndex() : void {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/journal/');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function testUserIndex() : void {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/journal/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminIndex() : void {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/journal/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonShow() : void {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/journal/1');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function testUserShow() : void {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/journal/1');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminShow() : void {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/journal/1');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonSearch() : void {
        $client = $this->makeClient();
        $formCrawler = $client->request('GET', '/journal/search');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserSearch() : void {
        $client = $this->makeClient(LoadUser::USER);
        $formCrawler = $client->request('GET', '/journal/search');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '5D69',
        ]);
        $client->submit($form);
        $this->assertSame(200, $client->getresponse()->getStatusCode());
        $this->assertSame(1, $client->getCrawler()->filter('td:contains("CBF45637-5D69-44C3-AEC0-A906CBC3E27B")')->count());
    }

    public function testAdminSearch() : void {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/journal/search');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '5D69',
        ]);
        $client->submit($form);
        $this->assertSame(200, $client->getresponse()->getStatusCode());
        $this->assertSame(1, $client->getCrawler()->filter('td:contains("CBF45637-5D69-44C3-AEC0-A906CBC3E27B")')->count());
    }

    public function testAnonPing() : void {
        $client = $this->makeClient();
        $client->request('GET', '/journal/1/ping');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserPing() : void {
        $client = $this->makeClient(LoadUser::USER);
        $client->request('GET', '/journal/1/ping');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminPing() : void {
        $client = $this->makeClient(LoadUser::ADMIN);
        $client->request('GET', '/journal/1/ping');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
