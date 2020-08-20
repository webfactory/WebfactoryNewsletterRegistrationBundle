<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\Mime\Email;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

interface TaskInterface
{
    public function startRegistration(PendingOptInInterface $pendingOptIn): Email;
}
