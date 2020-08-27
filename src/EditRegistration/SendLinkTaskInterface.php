<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface SendLinkTaskInterface
{
    public function sendEditRegistrationLink(RecipientInterface $recipient): void;
}
