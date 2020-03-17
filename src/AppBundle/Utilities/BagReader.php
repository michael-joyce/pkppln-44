<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Utilities;

use whikloj\BagItTools\Bag;;
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
     * Read a bag from the file system.
     *
     * @param string $path
     *
     * @throws Exception
     *                   Exception thrown if the bag doesn't exist.
     *
     * @return Bag;
     */
    public function readBag($path) {
        if ( ! $this->fs->exists($path)) {
            throw new Exception("Bag {$path} does not exist");
        }

        //This call isn't testable without a real bag due to limitations
        // in the Bag; library.
        return Bag::create($path);
    }
}
