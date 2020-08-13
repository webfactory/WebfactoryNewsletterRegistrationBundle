<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

interface RecipientInterface
{
    public function getUuid(): string;

    public function getEmailAddress(): string;
}
