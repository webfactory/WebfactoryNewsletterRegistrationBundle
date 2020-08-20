<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface TaskInterface
{
    public function editRegistration(RecipientInterface $recipient): void;
}
