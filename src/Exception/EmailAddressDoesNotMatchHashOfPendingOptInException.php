<?php

namespace Webfactory\NewsletterRegistrationBundle\Exception;

use Throwable;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

class EmailAddressDoesNotMatchHashOfPendingOptInException extends WebfactoryNewsletterRegistrationException
{
    public function __construct(
        EmailAddress $emailAddress,
        PendingOptInInterface $pendingOptIn,
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            'The email address "'.$emailAddress->getEmailAddress().'" does not correspond to the hash of the '
            .'PendingOptIn with uuid "'.$pendingOptIn->getUuid().'"',
            $code,
            $previous
        );
    }
}
