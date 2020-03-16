<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

/**
 * AuContainerRepository makes it easy to find AuContainers.
 */
class AuContainerRepository extends EntityRepository {
    /**
     * Find the open container with the lowest database ID. There should only
     * ever be one open container, but finding the one with lowest database ID
     * guarantees it.
     *
     * @return AuContainer[]|Collection
     */
    public function getOpenContainer() {
        return $this->findOneBy(
            ['open' => true],
            ['id' => 'ASC']
        );
    }
}
