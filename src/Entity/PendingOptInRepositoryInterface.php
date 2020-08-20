<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface PendingOptInRepositoryInterface
{
    public function isEmailAddressAlreadyRegistered(EmailAddress $emailAddress): bool;

    public function save(PendingOptInInterface $pendingOptIn): void;

    public function remove(PendingOptInInterface $pendingOptIn): void;

    public function findByUuid(string $uuid): ?PendingOptInInterface;
}
