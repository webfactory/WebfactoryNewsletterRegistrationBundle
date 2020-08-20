<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface EmailAddressFactoryInterface
{
    public function fromString(string $emailAddressString): EmailAddress;
}
