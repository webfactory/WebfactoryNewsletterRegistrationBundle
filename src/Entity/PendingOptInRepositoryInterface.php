<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use DateTimeImmutable;

interface PendingOptInRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?PendingOptInInterface;

    public function save(PendingOptInInterface $pendingOptIn): void;

    public function remove(PendingOptInInterface $pendingOptIn): void;

    public function findByUuid(string $uuid): ?PendingOptInInterface;

    /**
     * @return int Number of deleted PendingOptIns
     */
    public function removeOutdated(DateTimeImmutable $thresholdDate): int;
}
