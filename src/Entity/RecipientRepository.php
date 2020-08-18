<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RecipientRepository extends EntityRepository implements RecipientRepositoryInterface
{
    public function isEmailAddressAlreadyRegistered(string $emailAddress): bool
    {
        $recipient = $this->createQueryBuilder('recipient')
            ->select()
            ->where('recipient.emailAddress = :emailAddress')
            ->setParameter('emailAddress', $emailAddress)
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $recipient;
    }
}