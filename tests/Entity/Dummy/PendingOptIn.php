<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: '\Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptInRepository')]
class PendingOptIn extends \Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn
{
    #[ORM\ManyToMany(targetEntity: Newsletter::class)]
    #[ORM\JoinTable(
        joinColumns: [new ORM\JoinColumn(referencedColumnName: 'uuid', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')]
    )]
    protected $newsletters;
}
