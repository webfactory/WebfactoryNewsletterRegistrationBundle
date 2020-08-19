<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface PendingOptInRepositoryInterface
{
    public function isEmailAddressHashAlreadyRegistered(string $emailAddressHash): bool;

    public function save(PendingOptInInterface $pendingOptIn): void;

    public function remove(PendingOptInInterface $pendingOptIn): void;
}
