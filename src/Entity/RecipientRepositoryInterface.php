<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientRepositoryInterface
{
    public function findByEmailAddress(EmailAddress $emailAddress): ?RecipientInterface;

    public function save(RecipientInterface $pendingOptIn): void;

    public function remove(RecipientInterface $recipient): void;

    public function findByUuid(string $uuid): ?RecipientInterface;
}
