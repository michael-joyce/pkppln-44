<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * TermOfUse.
 *
 * @ORM\Table(name="term_of_use")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TermOfUseRepository")
 */
class TermOfUse extends AbstractEntity {
    /**
     * The "weight" of the term. Heavier terms are sorted lower.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $weight;

    /**
     * A term key code, something unique to all versions and translations
     * of a term.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $keyCode;

    /**
     * The content of the term, in the language in $langCode.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;
    
    /**
     *
     */
    public function __toString() {
    }

    /**
     * Set weight.
     *
     * @param int $weight
     *
     * @return TermOfUse
     */
    public function setWeight($weight) {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight.
     *
     * @return int
     */
    public function getWeight() {
        return $this->weight;
    }

    /**
     * Set keyCode.
     *
     * @param string $keyCode
     *
     * @return TermOfUse
     */
    public function setKeyCode($keyCode) {
        $this->keyCode = $keyCode;

        return $this;
    }

    /**
     * Get keyCode.
     *
     * @return string
     */
    public function getKeyCode() {
        return $this->keyCode;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return TermOfUse
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

}
