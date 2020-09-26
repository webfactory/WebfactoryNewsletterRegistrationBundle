<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface PendingOptInFactoryInterface
{
    public function fromRegistrationFormData(array $formData): ?PendingOptInInterface;
}
