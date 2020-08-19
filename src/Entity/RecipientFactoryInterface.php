<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientFactoryInterface
{
    public function fromPendingOptIn(PendingOptInInterface $pendingOptIn, string $emailAddress): RecipientInterface;
}
