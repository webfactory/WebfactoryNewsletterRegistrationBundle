<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedBlockedEmailAddresses;

interface TaskInterface
{
    public function deleteOutdatedBlockedEmailAddresses(?\DateTimeImmutable $now = null): void;
}
