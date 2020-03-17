<?php

namespace AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAuContainer;
use AppBundle\DataFixtures\ORM\LoadBlacklist;
use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Utility\AbstractTestCase;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuContainerControllerTest extends BaseTestCase {
    protected function getFixtures() {
        return [
            LoadUser::class,
            LoadBlacklist::class,
            LoadJournal::class,
            LoadDeposit::class,
            LoadAuContainer::class,
        ];
    }

    public function setUp() : void {
        parent::setUp();
    }

    public function testIndex() {
        $client = $this->makeClient(LoadUser::USER);
        $client->request('GET', '/aucontainer/');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Displaying 3 records of 3 total.', $response->getContent());
        $this->assertStringContainsString('2 (0 deposits/0kb)', $client->getResponse()->getContent());
    }
}
