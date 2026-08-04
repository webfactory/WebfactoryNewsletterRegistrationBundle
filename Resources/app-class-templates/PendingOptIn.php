<?php

namespace AppBundle\Newsletter\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PendingOptInRepository::class)]
#[ORM\Table(name: 'wfd_newsletterPendingOptIn')]
#[ORM\UniqueConstraint(name: 'emailAddressHash_unique', columns: ['emailAddressHash'])]
#[ORM\UniqueConstraint(name: 'uuid_unique', columns: ['uuid'])]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride(
        name: 'categories',
        joinTable: new ORM\JoinTable(name: 'wfd_newsletterPendingOptIn_category'),
        joinColumns: [new ORM\JoinColumn(referencedColumnName: 'uuid', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')],
    ),
])]
class PendingOptIn extends \Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn
{
}
