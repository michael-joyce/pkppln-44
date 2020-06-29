<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAuContainer;
use AppBundle\DataFixtures\ORM\LoadBlacklist;
use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
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

    public function testIndex() : void {
        $client = $this->makeClient(LoadUser::USER);
        $client->request('GET', '/aucontainer/');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('Displaying 3 records of 3 total.', $response->getContent());
        $this->assertStringContainsString('2 (0 deposits/0kb)', $client->getResponse()->getContent());
    }

    public function setUp() : void {
        parent::setUp();
    }
}
