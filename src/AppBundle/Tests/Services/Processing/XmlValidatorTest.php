<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\Services\Processing\XmlValidator;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of PayloadValidatorTest
 */
class XmlValidatorTest extends BaseTestCase {

    /**
     * @var XmlValidator
     */
    private $validator;

    protected function setup() : void {
        parent::setUp();
        $this->validator = $this->container->get(XmlValidator::class);
    }

    public function testInstance() {
        $this->assertInstanceOf(XmlValidator::class, $this->validator);
    }

    public function testReportErrors() {
        $errors = [
            ['line' => 1, 'message' => 'bad things happend.'],
            ['line' => 3, 'message' => 'good things happend.'],
        ];

        $report = '';
        $this->validator->reportErrors($errors, $report);
        $this->assertStringContainsStringIgnoringCase('On line 1: bad things happend.', $report);
        $this->assertStringContainsStringIgnoringCase('On line 3: good things happend.', $report);
    }

}
