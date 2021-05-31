<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Services;

use DOMDocument;

/**
 * Simple wrapper around around DOMDocument->validate().
 */
class SchemaValidator extends AbstractValidator
{
    /**
     * Validate a DOM document.
     *
     * @param bool $clearErrors
     * @param mixed $path
     */
    public function validate(DOMDocument $dom, $path, $clearErrors = true) : void {
        if ($clearErrors) {
            $this->clearErrors();
        }
        $xsd = $path . '/native.xsd';

        // Enable user error handling
        $oldErrors = libxml_use_internal_errors(true);
        if($dom->schemaValidate($xsd)) {
            return;
        }
        $errors = libxml_get_errors();
        libxml_clear_errors();
        foreach($errors as $error) {
            $this->validationError($error->code, $error->message, $error->file, $error->line, '');
        }
        libxml_use_internal_errors($oldErrors);
    }
}
