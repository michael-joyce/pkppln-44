<?php


namespace AppBundle\Services;

use DOMDocument;

abstract class AbstractValidator {

    /**
     * @var array
     */
    protected $errors;

    /**
     * Construct a validator.
     */
    public function __construct()
    {
        $this->errors = array();
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
    public function validationError($n, $message, $file, $line, $context)
    {
        $lxml = libxml_get_last_error();

        if ($lxml) {
            $this->errors[] = array(
                'message' => $lxml->message,
                'file' => $lxml->file,
                'line' => $lxml->line,
            );
        } else {
            $this->errors[] = array(
                'message' => $message,
                'file' => $file,
                'line' => $line,
            );
        }
    }

    abstract public function validate(DOMDocument $dom, $path, $clearErrors = true);

    /**
     * Return true if the document had errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Count the errors in validation.
     *
     * @return int
     */
    public function countErrors()
    {
        return count($this->errors);
    }

    /**
     * Get a list of the errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Clear out the errors and start fresh.
     */
    public function clearErrors()
    {
        $this->errors = array();
    }

}
