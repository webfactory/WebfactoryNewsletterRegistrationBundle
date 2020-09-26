<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface BlockedEmailAddressHashInterface
{
    public static function fromEmailAddress(EmailAddress $emailAddress, \DateTimeImmutable $blockDate = null): self;

    public function getBlockedUntilDate(\DateInterval $blockDuration): \DateTimeImmutable;
}
