<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PendingOptInRepository::class)]
class PendingOptIn extends \Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn
{
    /**
     * @var Collection<int, Newsletter>
     */
    #[ORM\ManyToMany(targetEntity: Newsletter::class)]
    #[ORM\JoinTable(
        joinColumns: [new ORM\JoinColumn(referencedColumnName: 'uuid', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')]
    )]
    protected Collection $newsletters;
}
