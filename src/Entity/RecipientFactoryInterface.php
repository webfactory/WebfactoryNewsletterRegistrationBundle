<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientFactoryInterface
{
    public function fromPendingOptIn(
        PendingOptInInterface $pendingOptIn,
        EmailAddress $emailAddress
    ): RecipientInterface;
}
