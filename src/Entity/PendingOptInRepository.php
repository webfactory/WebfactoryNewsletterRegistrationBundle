<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PendingOptInRepository extends EntityRepository implements PendingOptInRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?PendingOptInInterface
    {
        return $this->createQueryBuilder('recipient')
            ->select()
            ->where('recipient.emailAddressHash = :emailAddressHash')
            ->setParameter('emailAddressHash', $emailAddress->getHash())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(PendingOptInInterface $pendingOptIn): void
    {
        $this->getEntityManager()->persist($pendingOptIn);
        $this->getEntityManager()->flush();
    }

    public function remove(PendingOptInInterface $pendingOptIn): void
    {
        $this->getEntityManager()->remove($pendingOptIn);
        $this->getEntityManager()->flush();
    }

    public function findByUuid(string $uuid): ?PendingOptInInterface
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function removeOutdated(\DateTimeImmutable $thresholdDate): int
    {
        return $this->createQueryBuilder('pendingOptIn')
            ->delete()
            ->where('pendingOptIn.registrationDate < :thresholdDate')
            ->setParameter('thresholdDate', $thresholdDate)
            ->getQuery()
            ->execute();
    }
}
