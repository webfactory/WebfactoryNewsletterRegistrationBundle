<?php

namespace Webfactory\NewsletterRegistrationBundle\ConfirmRegistration;

use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface TaskInterface
{
    public function confirmRegistration(
        PendingOptInInterface $pendingOptIn,
        string $emailAddressString
    ): RecipientInterface;

    public function getTimeLimitForOptInInHours(): int;
}
