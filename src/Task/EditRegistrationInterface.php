<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface EditRegistrationInterface
{
    public function editRegistration(RecipientInterface $recipient): void;
}
