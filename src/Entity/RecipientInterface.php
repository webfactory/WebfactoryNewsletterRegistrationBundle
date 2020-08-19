<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientInterface
{
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

    public static function fromPendingOptIn(PendingOptInInterface $pendingOptIn, string $emailAddress): self;
}
