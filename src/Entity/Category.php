<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class Category implements CategoryInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    protected ?int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    protected bool $visible;

    /** Used for sorting amongst other Categories. */
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    protected int $rank;

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
