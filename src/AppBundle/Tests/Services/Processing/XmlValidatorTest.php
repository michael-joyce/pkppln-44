<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Tests\Services\Processing;

use AppBundle\Services\Processing\XmlValidator;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of PayloadValidatorTest.
 */
class XmlValidatorTest extends BaseTestCase {
    /**
     * @var XmlValidator
     */
    private $validator;

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

    protected function setup() : void {
        parent::setUp();
        $this->validator = $this->container->get(XmlValidator::class);
    }
}
