<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Utilities;

use BagIt;
use Exception;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Wrapper around BagIt.
 */
class BagReader {

    /**
     * Build the reader.
     */
    public function __construct() {
        $this->fs = new Filesystem();
    }

    /**
     * Override the default Filesystem component.
     *
     * @param Filesystem $fs
     */
    public function setFilesystem(Filesystem $fs) {
        $this->fs = $fs;
    }

    /**
     * Read a bag from the file system.
     *
     * @param string $path
     *
     * @return BagIt
     *
     * @throws Exception
     *   Exception thrown if the bag doesn't exist.
     */
    public function readBag($path) {
        if (!$this->fs->exists($path)) {
            throw new Exception("Bag {$path} does not exist");
        }

        $bag = new BagIt($path);
        return $bag;
    }

}
