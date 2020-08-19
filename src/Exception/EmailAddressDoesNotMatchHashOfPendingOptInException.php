<?php

namespace Webfactory\NewsletterRegistrationBundle\Exception;

use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

class EmailAddressDoesNotMatchHashOfPendingOptInException extends WebfactoryNewsletterRegistrationException
{
    public function __construct(string $emailAddress, PendingOptInInterface $pendingOptIn, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            'The email address "'.$emailAddress.'" does not correspond to the hash of the PendingOptIn with '
            .'uuid "'.$pendingOptIn->getUuid().'"',
            $code,
            $previous
        );
    }
}
