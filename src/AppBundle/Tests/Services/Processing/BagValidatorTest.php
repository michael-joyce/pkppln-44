<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\DataFixtures\ORM\LoadDeposit;
use AppBundle\DataFixtures\ORM\LoadJournal;
use AppBundle\Services\Processing\BagValidator;
use AppBundle\Utilities\BagReader;
use whikloj\BagItTools\Bag;;
use Exception;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of PayloadValidatorTest.
 */
class BagValidatorTest extends BaseTestCase {
    /**
     * @var BagValidator
     */
    private $validator;

    protected function getFixtures() {
        return [
            LoadJournal::class,
            LoadDeposit::class,
        ];
    }

    public function testInstance() : void {
        $this->assertInstanceOf(BagValidator::class, $this->validator);
    }

    public function testValidate() : void {
        $deposit = $this->getReference('deposit.1');

        $bag = $this->createMock(Bag::class);
        $bag->method('validate')->willReturn([]);
        $bag->method('getBagInfoData')->willReturn($deposit->getJournalVersion());
        $reader = $this->createMock(BagReader::class);
        $reader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($reader);

        $this->validator->processDeposit($deposit);
        $this->assertEmpty($deposit->getErrorLog());
    }

    public function testValidateVersionMismatch() : void {
        $deposit = $this->getReference('deposit.1');

        $bag = $this->createMock(Bag::class);
        $bag->method('validate')->willReturn([]);
        $bag->method('getBagInfoData')->willReturn('2.0.0.0');
        $reader = $this->createMock(BagReader::class);
        $reader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($reader);

        $this->validator->processDeposit($deposit);
        $this->assertSame(1, count($deposit->getErrorLog()));
        $this->assertStringStartsWith('Bag journal version tag', $deposit->getErrorLog()[0]);
    }

    public function testValidateFail() : void {
        $this->expectException(Exception::class);
        $deposit = $this->getReference('deposit.1');

        $bag = $this->createMock(Bag::class);
        $bag->method('validate')->willReturn([['foo', 'error message']]);
        $bag->method('getBagInfoData')->willReturn('2.0.0.0');
        $reader = $this->createMock(BagReader::class);
        $reader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($reader);

        $this->validator->processDeposit($deposit);
    }

    protected function setup() : void {
        parent::setUp();
        $this->validator = $this->container->get(BagValidator::class);
    }
}
