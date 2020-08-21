<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface BlockedEmailAddressHashInterface
{
    public static function fromEmailAddress(EmailAddress $emailAddress, \DateTimeImmutable $blockDate = null): self;
}
