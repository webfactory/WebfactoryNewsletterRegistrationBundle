<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\AppBundle\Newsletter\Entity\NewsletterRepository")
 * @ORM\Table("wfd_newsletterNewsletter")
 */
class Newsletter extends \Webfactory\NewsletterRegistrationBundle\Entity\Newsletter
{
}
