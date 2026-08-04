<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'wfd_newsletterCategory')]
class Category extends \Webfactory\NewsletterRegistrationBundle\Entity\Category
{
}
