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
 * Description of BagReader
 */
class BagReader {

    /**
     * 
     */
    public function __construct() {
        $this->fs = new Filesystem();
    }

    /**
     * Set the file system client.
     *
     * @param Filesystem $fs
     */
    public function setFilesystem(Filesystem $fs) {
        $this->fs = $fs;
    }
    
    /**
     * @param string $path
     * 
     * @return BagIt
     * 
     * @throws Exception
     */
    public function readBag($path) {
        if (!$this->fs->exists($path)) {
            throw new Exception("Bag {$path} does not exist");
        }

        $bag = new BagIt($path);
        return $bag;
    }

}
