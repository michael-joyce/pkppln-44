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
class DtdValidator extends AbstractValidator
{
    /**
     * Validate a DOM document.
     *
     * @param null $path
     * @param bool $clearErrors
     */
    public function validate(DOMDocument $dom, $path = null, $clearErrors = true) : void {
        if ($clearErrors) {
            $this->clearErrors();
        }
        if (null === $dom->doctype) {
            return;
        }
        $oldErrors = libxml_use_internal_errors(true);
        if($dom->validate()) {
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
