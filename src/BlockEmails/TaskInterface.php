<?php

namespace Webfactory\NewsletterRegistrationBundle\BlockEmails;

use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

interface TaskInterface
{
    public function blockEmailsFor(PendingOptInInterface $pendingOptIn, string $emailAddress);

    public function getBlockDurationInDays(): int;
}
