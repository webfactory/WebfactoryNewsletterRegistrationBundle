<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientInterface
{
    public static function fromPendingOptIn(PendingOptInInterface $pendingOptIn): self;

    public function getUuid(): string;

    public function getEmailAddress(): EmailAddress;

    /**
     * @return CategoryInterface[]
     */
    public function getCategories(): array;

    /**
     * @param CategoryInterface[] $categories
     */
    public function setCategories(array $categories): void;
}
