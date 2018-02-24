<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Whitelist
 *
 * @ORM\Table(name="whitelist", indexes={
 *  @ORM\Index(columns={"uuid"}, flags={"fulltext"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WhitelistRepository")
 */
class Whitelist extends AbstractEntity
{
    /**
     * Journal UUID, as generated by the PLN plugin. 
     * 
     * This cannot be part of a relationship - a journal may be listed before 
     * we have a record of it.
     *
     * @var string
     * @Assert\Uuid(strict=false)
     * @ORM\Column(type="string", length=36, nullable=false)
     */
    private $uuid;

    /**
     * Short message describing why the journal was listed.
     *
     * @var type
     * @ORM\Column(type="text")
     */
    private $comment;
    
    public function __toString() {
        return $this->uuid;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Whitelist
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Whitelist
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
