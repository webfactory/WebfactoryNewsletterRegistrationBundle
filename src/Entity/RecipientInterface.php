<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientInterface
{
    public static function fromPendingOptIn(PendingOptInInterface $pendingOptIn, EmailAddress $emailAddress): self;

    public function getUuid(): string;

    public function getEmailAddress(): string;

    /**
     * @return NewsletterInterface[]
     */
    public function getNewsletters(): array;

    /**
     * @param NewsletterInterface[] $newsletters
     */
    public function setNewsletters(array $newsletters): void;
}
