<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface PendingOptInRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?PendingOptInInterface;

    public function save(PendingOptInInterface $pendingOptIn): void;

    public function remove(PendingOptInInterface $pendingOptIn): void;

    public function findByUuid(string $uuid): ?PendingOptInInterface;

    /**
     * @param \DateTime $tresholdDate
     *
     * @return int Number of deleted PendingOptIns
     */
    public function removeOutdated(\DateTime $tresholdDate): int;
}
