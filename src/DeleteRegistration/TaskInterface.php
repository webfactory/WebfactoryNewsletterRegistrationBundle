<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteRegistration;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface TaskInterface
{
    public function deleteRegistration(RecipientInterface $recipient): void;
}
