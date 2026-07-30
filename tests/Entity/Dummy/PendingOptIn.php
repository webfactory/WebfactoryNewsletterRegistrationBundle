<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PendingOptInRepository::class)]
class PendingOptIn extends \Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn
{
    #[ORM\ManyToMany(targetEntity: Newsletter::class)]
    #[ORM\JoinTable(
        joinColumns: [new ORM\JoinColumn(referencedColumnName: 'uuid', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')]
    )]
    protected $newsletters;
}
