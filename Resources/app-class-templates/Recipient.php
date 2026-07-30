<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\Table(
    name: 'wfd_newsletterRecipient',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'email_unique', columns: ['email']),
        new ORM\UniqueConstraint(name: 'uuid_unique', columns: ['uuid']),
    ]
)]
class Recipient extends \Webfactory\NewsletterRegistrationBundle\Entity\Recipient
{
}
