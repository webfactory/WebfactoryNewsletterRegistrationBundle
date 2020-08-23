<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface BlockedEmailAddressHashRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?BlockedEmailAddressHashInterface;

    public function remove(BlockedEmailAddressHashInterface $blockedEmailAddressHash): void;

    public function save(BlockedEmailAddressHashInterface $blockedEmailAddressHash): void;

    /**
     * @param \DateTimeImmutable $thresholdDate
     *
     * @return int Number of deleted BlockedEmailAddressHashes
     */
    public function removeOutdated(\DateTimeImmutable $thresholdDate): int;
}
