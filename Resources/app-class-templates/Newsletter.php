<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
#[ORM\Table(name: 'wfd_newsletterNewsletter')]
class Newsletter extends \Webfactory\NewsletterRegistrationBundle\Entity\Newsletter
{
}
