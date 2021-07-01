<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use DateTimeImmutable;

interface BlockedEmailAddressHashRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?BlockedEmailAddressHashInterface;

    public function remove(BlockedEmailAddressHashInterface $blockedEmailAddressHash): void;

    public function save(BlockedEmailAddressHashInterface $blockedEmailAddressHash): void;

    /**
     * @return int Number of deleted BlockedEmailAddressHashes
     */
    public function removeOutdated(DateTimeImmutable $thresholdDate): int;
}
