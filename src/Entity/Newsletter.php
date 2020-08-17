<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity(repositoryClass="\Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepositoryInterface")
 * @ ORM\Table("wfd_newsletterNewsletter")
 */
abstract class Newsletter implements NewsletterInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     *
     * @var bool
     */
    protected $visible;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default": 0})
     *
     * @var int
     *
     * Used for sorting amongst other Newsletters.
     */
    protected $rank;

    public function __construct(?int $id, string $name, int $rank = 0, $visible = true)
    {
        $this->id = $id;
        $this->name = $name;
        $this->rank = $rank;
        $this->visible = $visible;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
