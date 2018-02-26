<?php

namespace AppBundle\Tests\Services;

use AppBundle\Services\DtdValidator;
use DOMDocument;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class DtdValidatorTest extends BaseTestCase {

    /**
     * @var DtdValidator
     */
    protected $validator;

    public function setUp() {
        parent::setUp();
        $this->validator = $this->container->get(DtdValidator::class);
        $this->validator->clearErrors();
    }

    public function testInstance() {
        $this->assertInstanceOf(DtdValidator::class, $this->validator);
    }

    public function testValidateNoDtd() {
        $dom = new DOMDocument();
        $dom->loadXML('<root />');
        $this->validator->validate($dom);
        $this->assertEquals(0, $this->validator->countErrors());
    }

    public function testValidate() {
        $dom = new DOMDocument();
        $dom->loadXML($this->getValidXml());
        $this->validator->validate($dom, true);
        $this->assertEquals(0, $this->validator->countErrors());
    }

    public function testValidateWithErrors() {
        $dom = new DOMDocument();
        $dom->loadXML($this->getinvalidXml());
        $this->validator->validate($dom, true);
        $this->assertEquals(1, $this->validator->countErrors());
    }

    private function getValidXml() {
        $str = <<<ENDSTR
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
        return $str;
    }

    private function getInvalidXml() {
        $str = <<<ENDSTR
<?xml version="1.0" standalone="yes"?>
<!DOCTYPE root [
<!ELEMENT root (item)+ >
<!ELEMENT item EMPTY >
<!ATTLIST item type CDATA #REQUIRED>
]>
<root>
	<item/>
	<item type="bar"/>
</root>
ENDSTR;
        return $str;
    }

}
