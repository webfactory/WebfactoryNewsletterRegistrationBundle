<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\Mime\Email;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

interface StartRegistrationInterface
{
    public function startRegistration(PendingOptInInterface $pendingOptIn): Email;
}
