<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Controller;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class DefaultControllerTest extends BaseTestCase {
    protected function getFixtures() {
        return [
            LoadDeposit::class,
            LoadUser::class,
        ];
    }

    public function testAnonIndex() : void {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUserIndex() : void {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminIndex() : void {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAnonDepositDepositSearch() : void {
        $client = $this->makeClient();
        $formCrawler = $client->request('GET', '/deposit_search');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testUserDepositSearch() : void {
        $client = $this->makeClient(LoadUser::USER);
        $formCrawler = $client->request('GET', '/deposit_search');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '4F37',
        ]);
        $client->submit($form);
        $this->assertSame(200, $client->getresponse()->getStatusCode());
        $this->assertSame(1, $client->getCrawler()->filter('td:contains("978EA2B4-01DB-4F37-BD74-871DDBE71BF5")')->count());
    }

    public function testAdminDepositSearch() : void {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/deposit_search');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $formCrawler->selectButton('Search')->form([
            'q' => '4F37',
        ]);
        $client->submit($form);
        $this->assertSame(200, $client->getresponse()->getStatusCode());
        $this->assertSame(1, $client->getCrawler()->filter('td:contains("978EA2B4-01DB-4F37-BD74-871DDBE71BF5")')->count());
    }

    public function testFetchActionJournalMismatch() : void {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/fetch/44428B12-CDC4-453E-8157-319004CD8CE6/F93A8108-B705-4763-A592-B718B00BD4EA.zip');
        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Journal ID does not match', $client->getResponse()->getContent());
    }

    public function testFetchActionDeposit404() : void {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/fetch/04F2C06E-35B8-43C1-B60C-1934271B0B7E/F93A8108-B705-4763-A592-B718B00BD4EA.zip');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('Deposit not found.', $client->getResponse()->getContent());
    }

    public function testPermissionAction() : void {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/permission');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsStringIgnoringCase('LOCKSS system has permission', $client->getResponse()->getContent());
    }
}
