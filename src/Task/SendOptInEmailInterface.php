<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\Mime\Email;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

interface SendOptInEmailInterface
{
    public function sendOptInEmail(RecipientInterface $recipient): Email;
}
