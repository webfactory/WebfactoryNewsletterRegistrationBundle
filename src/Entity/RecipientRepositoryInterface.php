<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientRepositoryInterface
{
    public function isEmailAddressAlreadyRegistered(string $emailAddress): bool;

    public function save(RecipientInterface $pendingOptIn): void;
}
