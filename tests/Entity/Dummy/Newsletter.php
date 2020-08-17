<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\NewsletterRepository")
 */
class Newsletter extends \Webfactory\NewsletterRegistrationBundle\Entity\Newsletter
{
}
