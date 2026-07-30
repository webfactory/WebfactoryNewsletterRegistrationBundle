<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PendingOptInRepository::class)]
#[ORM\UniqueConstraint(name: 'emailAddressHash_unique', columns: ['emailAddressHash'])]
#[ORM\UniqueConstraint(name: 'uuid_unique', columns: ['uuid'])]
class PendingOptIn extends \Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn
{
}
