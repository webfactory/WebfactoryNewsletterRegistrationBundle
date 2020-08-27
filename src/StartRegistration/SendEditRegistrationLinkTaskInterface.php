<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface SendEditRegistrationLinkTaskInterface
{
    public function sendEditRegistrationLink(RecipientInterface $recipient): void;
}
