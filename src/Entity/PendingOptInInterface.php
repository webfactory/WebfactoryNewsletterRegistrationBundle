<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface PendingOptInInterface
{
    public static function fromRegistrationFormData(array $formData, string $secret): self;

    public function getUuid(): string;

    public function getEmailAddress(): string;
}
