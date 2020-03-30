<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Services;

use DOMDocument;

/**
 * Simple wrapper around around DOMDocument->validate().
 */
class DtdValidator {
    /**
     * List of errors during validation.
     *
     * @var array
     */
    private $errors;

    /**
     * Construct a validator.
     */
    public function __construct() {
        $this->errors = [];
    }

    /**
     * Callback for a validation or parsing error.
     *
     * @param string $n
     * @param string $message
     * @param string $file
     * @param string $line
     * @param string $context
     */
    public function validationError($n, $message, $file, $line, $context) : void {
        $lxml = libxml_get_last_error();
        $error = [
            'message' => $message,
            'file' => $file,
            'line' => $line,
        ];

        if ($lxml) {
            $error = [
                'message' => $lxml->message,
                'file' => $lxml->file,
                'line' => $lxml->line,
            ];
        }
        $this->errors[] = $error;
    }

    /**
     * Validate a DOM document.
     *
     * @param bool $clearErrors
     */
    public function validate(DOMDocument $dom, $clearErrors = true) : void {
        if ($clearErrors) {
            $this->clearErrors();
        }
        if (null === $dom->doctype) {
            return;
        }
        $oldHandler = set_error_handler([$this, 'validationError']);
        $dom->validate();
        set_error_handler($oldHandler);
    }

    public function schemaValidate(DOMDocument $dom, $path, $clearErrors = true) : void {
        if ($clearErrors) {
            $this->clearErrors();
        }
        $xsd = $path . '/native.xsd';
        $oldHandler = set_error_handler([$this, 'validationError']);
        $dom->schemaValidate($xsd);
        set_error_handler($oldHandler);

    }

    /**
     * Return true if the document had errors.
     *
     * @return bool
     */
    public function hasErrors() {
        return count($this->errors) > 0;
    }

    /**
     * Count the errors in validation.
     *
     * @return int
     */
    public function countErrors() {
        return count($this->errors);
    }

    /**
     * Get a list of the errors.
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Clear out the errors and start fresh.
     */
    public function clearErrors() : void {
        $this->errors = [];
    }
}
