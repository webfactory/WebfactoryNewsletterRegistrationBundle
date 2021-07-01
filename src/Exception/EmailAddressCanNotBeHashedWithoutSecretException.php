<?php

namespace Webfactory\NewsletterRegistrationBundle\Exception;

use Throwable;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;

class EmailAddressCanNotBeHashedWithoutSecretException extends WebfactoryNewsletterRegistrationException
{
    public function __construct(EmailAddress $emailAddress, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            'The email address "'.$emailAddress->getEmailAddress().'" could not be hashed as this email '
            .'address object has been constructed without providing a secret.',
            $code,
            $previous
        );
    }
}
