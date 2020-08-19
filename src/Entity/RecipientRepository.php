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

    public function save(RecipientInterface $pendingOptIn): void
    {
        $this->getEntityManager()->persist($pendingOptIn);
        $this->getEntityManager()->flush();
    }

    public function remove(RecipientInterface $recipient): void
    {
        $this->getEntityManager()->remove($recipient);
        $this->getEntityManager()->flush();
    }

    public function findByUuid(string $uuid): ?RecipientInterface
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
