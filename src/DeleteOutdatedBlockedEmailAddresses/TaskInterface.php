<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedBlockedEmailAddresses;

use DateTimeImmutable;

interface TaskInterface
{
    public function deleteOutdatedBlockedEmailAddresses(?DateTimeImmutable $now = null): void;
}
