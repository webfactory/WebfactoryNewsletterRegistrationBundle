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
     *
     * @var Collection of Newsletter
     */
    protected $newsletters;
}
