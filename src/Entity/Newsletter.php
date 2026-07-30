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
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: false)]
    protected $name;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    protected $visible;

    /**
     * @var int
     *
     * Used for sorting amongst other Newsletters.
     */
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
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
