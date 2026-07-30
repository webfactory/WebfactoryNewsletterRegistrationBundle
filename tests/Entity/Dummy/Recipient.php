<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
class Recipient extends \Webfactory\NewsletterRegistrationBundle\Entity\Recipient
{
    /**
     * @var Collection of Newsletter
     */
    #[ORM\ManyToMany(targetEntity: Newsletter::class)]
    #[ORM\JoinTable(
        joinColumns: [new ORM\JoinColumn(referencedColumnName: 'uuid', onDelete: 'CASCADE')],
        inverseJoinColumns: [new ORM\JoinColumn(onDelete: 'CASCADE')]
    )]
    protected Collection $newsletters;
}
