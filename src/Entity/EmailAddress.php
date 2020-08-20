<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressCanNotBeHashedWithoutSecretException;

/**
 * Value object.
 */
class EmailAddress
{
    /** @var string */
    protected $emailAddress;

    /** @var string|null */
    protected $secret;

    public function __construct(string $emailAddressString, ?string $secret)
    {
        $this->emailAddress = $this->normalize($emailAddressString);
        $this->secret = $secret;
    }

    protected function normalize(string $string): string
    {
        return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function __toString(): string
    {
        return $this->emailAddress;
    }

    public function getHash(): string
    {
        if (null === $this->secret) {
            throw new EmailAddressCanNotBeHashedWithoutSecretException($this);
        }

        return md5($this->secret.$this->emailAddress);
    }
}
