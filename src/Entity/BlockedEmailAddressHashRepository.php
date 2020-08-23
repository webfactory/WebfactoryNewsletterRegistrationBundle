<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BlockedEmailAddressHashRepository extends EntityRepository implements BlockedEmailAddressHashRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?BlockedEmailAddressHashInterface
    {
        return $this->findOneBy(['hash' => $emailAddress->getHash()]);
    }

    public function save(BlockedEmailAddressHashInterface $blockedEmailAddressHash): void
    {
        $this->getEntityManager()->persist($blockedEmailAddressHash);
        $this->getEntityManager()->flush();
    }

    public function remove(BlockedEmailAddressHashInterface $blockedEmailAddressHash): void
    {
        $this->getEntityManager()->remove($blockedEmailAddressHash);
        $this->getEntityManager()->flush();
    }

    public function removeOutdated(\DateTimeImmutable $thresholdDate): int
    {
        return $this->createQueryBuilder('blockedEmailAddressHash')
            ->delete()
            ->where('blockedEmailAddressHash.blockDate < :thresholdDate')
            ->setParameter('thresholdDate', $thresholdDate)
            ->getQuery()
            ->execute();
    }
}
