<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RecipientRepository extends EntityRepository implements RecipientRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?RecipientInterface
    {
        return $this->createQueryBuilder('recipient')
            ->select()
            ->where('recipient.emailAddress = :emailAddress')
            ->setParameter('emailAddress', $emailAddress->getEmailAddress())
            ->getQuery()
            ->getOneOrNullResult();
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
