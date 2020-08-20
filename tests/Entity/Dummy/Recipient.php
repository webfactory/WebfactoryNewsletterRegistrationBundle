<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\RecipientRepository")
 */
class Recipient extends \Webfactory\NewsletterRegistrationBundle\Entity\Recipient
{
    /**
     * @ORM\ManyToMany(targetEntity="Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter")
     * @ORM\JoinTable(
     *     joinColumns={@ORM\JoinColumn(referencedColumnName="uuid", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     *
     * @var Collection of Newsletter
     */
    protected $newsletters;
}
