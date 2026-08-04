<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PendingOptInRepository::class)]
#[ORM\Table(name: 'wfd_newsletterPendingOptIn')]
#[ORM\UniqueConstraint(name: 'email_address_hash_unique', columns: ['email_address_hash'])]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(
        name: 'categories',
        joinTable: new ORM\JoinTable(name: 'wfd_newsletterPendingOptIn_category'),
        joinColumns: [new ORM\JoinColumn(referencedColumnName: 'uuid', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'category_id', onDelete: 'CASCADE')],
    ),
])]
class PendingOptIn extends \Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn
{
}
