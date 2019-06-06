<?php

namespace AppBundle\Tests\Services;

use AppBundle\DataFixtures\ORM\LoadBlacklist;
use AppBundle\DataFixtures\ORM\LoadWhitelist;
use AppBundle\Services\BlackWhiteList;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class BlackWhiteListTest extends BaseTestCase {

    /**
     * @var BlackWhiteList
     */
    protected $list;

    public function setup() : void {
        parent::setUp();
        $this->list = $this->container->get(BlackWhiteList::class);
    }

    public function getFixtures() {
        return [
            LoadBlacklist::class,
            LoadWhitelist::class,
        ];
    }

    public function testInstance() {
        $this->assertInstanceOf(BlackWhiteList::class, $this->list);
    }

    public function testIsWhitelisted() {
        $this->assertTrue($this->list->isWhitelisted(LoadWhitelist::UUIDS[0]));
        $this->assertTrue($this->list->isWhitelisted(strtolower(LoadWhitelist::UUIDS[0])));
        
        $this->assertFalse($this->list->isWhitelisted(LoadBlacklist::UUIDS[0]));
        $this->assertFalse($this->list->isWhitelisted(strtolower(LoadBlacklist::UUIDS[0])));
    }

    public function testIsBlacklisted() {
        $this->assertTrue($this->list->isBlacklisted(LoadBlacklist::UUIDS[0]));
        $this->assertTrue($this->list->isBlacklisted(strtolower(LoadBlacklist::UUIDS[0])));
        
        $this->assertFalse($this->list->isBlacklisted(LoadWhitelist::UUIDS[0]));
        $this->assertFalse($this->list->isBlacklisted(strtolower(LoadWhitelist::UUIDS[0])));        
    }

}
