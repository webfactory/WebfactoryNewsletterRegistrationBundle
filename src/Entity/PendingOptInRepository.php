<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PendingOptInRepository extends EntityRepository implements PendingOptInRepositoryInterface
{
    public function isEmailAddressHashAlreadyRegistered(string $emailAddressHash): bool
    {
        $recipient = $this->createQueryBuilder('recipient')
            ->select()
            ->where('recipient.emailAddressHash = :emailAddressHash')
            ->setParameter('emailAddressHash', $emailAddressHash)
            ->getQuery()
            ->getOneOrNullResult();

        return null !== $recipient;
    }

    public function save(PendingOptInInterface $pendingOptIn): void
    {
        $this->getEntityManager()->persist($pendingOptIn);
        $this->getEntityManager()->flush();
    }
}
