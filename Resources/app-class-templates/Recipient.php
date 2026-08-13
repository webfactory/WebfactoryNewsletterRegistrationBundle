<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\Table(name: 'wfd_newsletterRecipient')]
#[ORM\UniqueConstraint(name: 'email_unique', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'uuid_unique', columns: ['uuid'])]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(
        name: 'categories',
        joinTable: new ORM\JoinTable(name: 'wfd_newsletterSubscription'),
        joinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'category_id', onDelete: 'CASCADE')],
    ),
])]
class Recipient extends \Webfactory\NewsletterRegistrationBundle\Entity\Recipient
{
}
