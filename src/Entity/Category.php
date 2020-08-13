<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity()
 * @ ORM\Table("wfd_newsletterCategory")
 */
abstract class Category implements CategoryInterface
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

    public function __construct(string $name, $visible = true)
    {
        $this->name = $name;
        $this->visible = $visible;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
