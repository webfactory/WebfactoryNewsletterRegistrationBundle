<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface ConfirmRegistrationInterface
{
    public function confirmRegistration(PendingOptInInterface $pendingOptIn, string $emailAddress): RecipientInterface;
}
