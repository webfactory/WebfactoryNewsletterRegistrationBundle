<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

class EmailAddressFactory implements EmailAddressFactoryInterface
{
    protected string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function fromString(string $emailAddressString): EmailAddress
    {
        return new EmailAddress($emailAddressString, $this->secret);
    }
}
