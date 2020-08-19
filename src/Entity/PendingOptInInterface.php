<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface PendingOptInInterface
{
    public static function fromRegistrationFormData(array $formData, string $secret): self;

    public function getUuid(): string;

    public function getEmailAddress(): string;

    public function emailAddressMatchesHash(string $email, string $secret): bool;

    /**
     * @return NewsletterInterface[]
     */
    public function getNewsletters(): array;
}
