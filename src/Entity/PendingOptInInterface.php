<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface PendingOptInInterface
{
    public static function fromRegistrationFormData(array $formData): self;

    public function getUuid(): string;

    public function getEmailAddress(): string;

    public function matchesEmailAddress(EmailAddress $email): bool;

    /**
     * @return NewsletterInterface[]
     */
    public function getNewsletters(): array;

    public function getRegistrationDate(): \DateTime;
}
