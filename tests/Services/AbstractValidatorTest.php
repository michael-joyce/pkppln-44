<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Services;

use App\DataFixtures\BlacklistFixtures;
use App\DataFixtures\WhitelistFixtures;
use App\Services\AbstractValidator;
use App\Services\BlackWhiteList;
use DOMDocument;
use Nines\UtilBundle\Tests\ControllerBaseCase;

class AbstractValidatorTest extends ControllerBaseCase
{
    /**
     * @var AbstractValidator
     */
    protected $validator;

    public function testInstance() : void {
        $this->assertInstanceOf(AbstractValidator::class, $this->validator);
    }

    public function testValidationError() {
        $this->assertFalse($this->validator->hasErrors());
        $this->validator->validationError("a", "error", "file", "line", "");
        $this->assertTrue($this->validator->hasErrors());
        $this->assertEquals(1, $this->validator->countErrors());
        $this->assertEquals([[
            'message' => 'error',
            'file' => 'file',
            'line' => 'line'
        ]], $this->validator->getErrors());
    }

    public function testLibXmlValidationError() {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom = $dom->loadXml("<a><a>");

        $this->assertFalse($this->validator->hasErrors());
        $this->validator->validationError("a", "error", "file", "line", "");

        $this->assertTrue($this->validator->hasErrors());
        $this->assertEquals(1, $this->validator->countErrors());
        $this->assertEquals([[
            'message' => "Premature end of data in tag a line 1\n",
            'file' => '',
            'line' => '1'
        ]], $this->validator->getErrors());
    }

    protected function setup() : void {
        parent::setUp();
        $this->validator = new class() extends AbstractValidator{
            public function validate(DOMDocument $dom, $path, $clearErrors = true) {
                // stub function.
            }
        };
    }
}
