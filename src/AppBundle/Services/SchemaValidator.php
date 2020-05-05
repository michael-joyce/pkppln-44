<?php

/*
 * Copyright (C) 2015-2016 Michael Joyce <ubermichael@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Services;

use DOMDocument;

/**
 * Simple wrapper around around DOMDocument->validate().
 */
class SchemaValidator extends AbstractValidator
{

    /**
     * Validate a DOM document.
     *
     * @param DOMDocument $dom
     * @param bool        $clearErrors
     */
    public function validate(DOMDocument $dom, $path, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }
        $xsd = $path . '/native.xsd';
        $oldHandler = set_error_handler([$this, 'validationError']);
        $dom->schemaValidate($xsd);
        set_error_handler($oldHandler);
    }

}
