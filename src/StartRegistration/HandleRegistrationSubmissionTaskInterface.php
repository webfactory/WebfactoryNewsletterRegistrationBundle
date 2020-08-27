<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\HttpFoundation\Response;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

interface HandleRegistrationSubmissionTaskInterface
{
    public function handleRegistrationSubmission(PendingOptInInterface $pendingOptIn): Response;
}
