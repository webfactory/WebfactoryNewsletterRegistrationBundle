<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface DeleteRegistrationInterface
{
    public function deleteRegistration(RecipientInterface $recipient): void;
}
