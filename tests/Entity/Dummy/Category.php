<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category extends \Webfactory\NewsletterRegistrationBundle\Entity\Category
{
}
