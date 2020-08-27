<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientInterface
{
    public static function fromPendingOptIn(PendingOptInInterface $pendingOptIn): self;

    public function getUuid(): string;

    public function getEmailAddress(): EmailAddress;

    /**
     * @return NewsletterInterface[]
     */
    public function getNewsletters(): array;

    /**
     * @param NewsletterInterface[] $newsletters
     */
    public function setNewsletters(array $newsletters): void;
}
