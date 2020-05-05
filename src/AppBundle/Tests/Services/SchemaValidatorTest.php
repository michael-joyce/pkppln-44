<?php

namespace AppBundle\Tests\Services;

use AppBundle\Services\SchemaValidator;
use DOMDocument;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class SchemaValidatorTest extends BaseTestCase {

	/**
	 * @var SchemaValidator
	 */
	protected $validator;

	protected function setUp() : void {
		parent::setUp();
		$this->validator = $this->container->get(SchemaValidator::class);
		$this->validator->clearErrors();
	}

	public function testInstance() {
		$this->assertInstanceOf(SchemaValidator::class, $this->validator);
	}

	public function testValidate() {
		$dom = new DOMDocument();
		$dom->loadXML($this->getValidXml());
		$path = dirname(__FILE__, 2) . '/data';
		$this->validator->validate($dom, $path, true);
		$this->assertEquals(0, $this->validator->countErrors());
	}

	public function testValidateWithErrors() {
		$dom = new DOMDocument();
		$dom->loadXML($this->getinvalidXml());
        $path = dirname(__FILE__, 2) . '/data';
        $this->validator->validate($dom, $path, true);
		$this->assertEquals(1, $this->validator->countErrors());
	}

	private function getValidXml() {
		$str = <<<ENDSTR
<root xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation='testSchema.xsd'>
 <item>String 1</item>
 <item>String 2</item>
 <item>String 3</item>
</root>
ENDSTR;
		return $str;
	}

	private function getInvalidXml() {
		$str = <<<ENDSTR
<root xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation='testSchema.xsd'>
      <items>
         <item>String 1</item>
         <item>String 2</item>
         <item>String 3</item>
     </items>
</root>
ENDSTR;
		return $str;
	}
}
