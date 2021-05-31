<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Services\Processing;

use App\DataFixtures\DepositFixtures;
use App\Entity\Deposit;
use App\Services\Processing\XmlValidator;
use App\Utilities\BagReader;
use App\Utilities\XmlParser;
use DOMDocument;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use whikloj\BagItTools\Bag;

/**
 * Description of PayloadValidatorTest.
 */
class XmlValidatorTest extends ControllerBaseCase
{
    /**
     * @var XmlValidator
     */
    private $validator;

    protected function fixtures() : array {
        return [
            DepositFixtures::class,
        ];
    }

    public function testInstance() : void {
        $this->assertInstanceOf(XmlValidator::class, $this->validator);
    }

    public function testReportErrors() : void {
        $errors = [
            ['line' => 1, 'message' => 'bad things happend.'],
            ['line' => 3, 'message' => 'good things happend.'],
        ];

        $report = '';
        $this->validator->reportErrors($errors, $report);
        $this->assertStringContainsStringIgnoringCase('On line 1: bad things happend.', $report);
        $this->assertStringContainsStringIgnoringCase('On line 3: good things happend.', $report);
    }

    public function testProcessDepositDtdValid() {
        /** @var Deposit $deposit */
        $deposit = $this->getReference('deposit.1');

        $bag = $this->createMock(Bag::class);
        $bag->method('getBagRoot')->willReturn('foo');
        $bagReader = $this->createMock(BagReader::class);
        $bagReader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($bagReader);

        $xmlParser = $this->createMock(XmlParser::class);
        $xmlParser->method('fromFile')->willReturn($this->domWithDtdValid());
        $this->validator->setXmlParser($xmlParser);

        $this->validator->processDeposit($deposit);
        $this->assertEquals('', $deposit->getProcessingLog());
    }

    public function testProcessDepositDtdInvalid() {
        /** @var Deposit $deposit */
        $deposit = $this->getReference('deposit.1');

        $bag = $this->createMock(Bag::class);
        $bag->method('getBagRoot')->willReturn('foo');
        $bagReader = $this->createMock(BagReader::class);
        $bagReader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($bagReader);

        $xmlParser = $this->createMock(XmlParser::class);
        $xmlParser->method('fromFile')->willReturn($this->domWithDtdInvalid());
        $this->validator->setXmlParser($xmlParser);

        $this->validator->processDeposit($deposit);
        $this->assertStringContainsString('Element item does not carry attribute type', $deposit->getProcessingLog());
    }

    public function testProcessDepositSchemaValid() {
        /** @var Deposit $deposit */
        $deposit = $this->getReference('deposit.1');

        $bag = $this->createMock(Bag::class);
        $bag->method('getBagRoot')->willReturn(dirname(__FILE__, 3));
        $bagReader = $this->createMock(BagReader::class);
        $bagReader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($bagReader);

        $xmlParser = $this->createMock(XmlParser::class);
        $xmlParser->method('fromFile')->willReturn($this->domWithSchemaValid());
        $this->validator->setXmlParser($xmlParser);

        $this->validator->processDeposit($deposit);
        $this->assertEquals('', $deposit->getProcessingLog());
    }

    public function testProcessDepositSchemaInvalid() {
        /** @var Deposit $deposit */
        $deposit = $this->getReference('deposit.1');

        $bag = $this->createMock(Bag::class);
        $bag->method('getBagRoot')->willReturn(dirname(__FILE__, 3));
        $bagReader = $this->createMock(BagReader::class);
        $bagReader->method('readBag')->willReturn($bag);
        $this->validator->setBagReader($bagReader);

        $xmlParser = $this->createMock(XmlParser::class);
        $xmlParser->method('fromFile')->willReturn($this->domWithSchemaInvalid());
        $this->validator->setXmlParser($xmlParser);

        $this->validator->processDeposit($deposit);
        $this->assertStringContainsString('This element is not expected.', $deposit->getProcessingLog());
    }

    protected function setup() : void {
        parent::setUp();
        $this->validator = self::$container->get(XmlValidator::class);
    }

    protected function domWithDtdValid() {
        $dom = new DOMDocument();
        $xml = <<<'ENDSTR'
<?xml version="1.0" standalone="yes"?>
<!DOCTYPE root [
<!ELEMENT root (item)+ >
<!ELEMENT item EMPTY >
<!ATTLIST item type CDATA #REQUIRED>
]>
<root>
	<item type="foo"/>
	<item type="bar"/>
</root>
ENDSTR;
        $dom->loadXML($xml);
        return $dom;
    }

    protected function domWithDtdInvalid() {
        $dom = new DOMDocument();
        $xml = <<<'ENDSTR'
<?xml version="1.0" standalone="yes"?>
<!DOCTYPE root [
<!ELEMENT root (item)+ >
<!ELEMENT item EMPTY >
<!ATTLIST item type CDATA #REQUIRED>
]>
<root>
	<item />
	<item type="bar"/>
</root>
ENDSTR;
        $dom->loadXML($xml);
        return $dom;
    }

    private function domWithSchemaValid() {
        $dom = new DOMDocument();
        $xml = <<<'ENDSTR'
<root xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation='native.xsd'>
 <item>String 1</item>
 <item>String 2</item>
 <item>String 3</item>
</root>
ENDSTR;
        $dom->loadXML($xml);
        return $dom;
    }

    private function domWithSchemaInvalid() {
        $dom = new DOMDocument();
        $xml = <<<'ENDSTR'
<root xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation='native.xsd'>
      <items>
         <item>String 1</item>
         <item>String 2</item>
         <item>String 3</item>
     </items>
</root>
ENDSTR;
        $dom->loadXML($xml);
        return $dom;
    }



}
