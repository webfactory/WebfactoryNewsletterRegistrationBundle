<?php

namespace Webfactory\NewsletterRegistrationBundle\Exception;

use Throwable;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;

class PendingOptInIsOutdatedException extends WebfactoryNewsletterRegistrationException
{
    public function __construct(PendingOptInInterface $pendingOptIn, $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            'The PendingOptIn with uuid '.$pendingOptIn->getUuid().' is outdated and can no longer be '
            .'confirmed. You can increase the webfactory.newsletter_registration.time_limit_for_opt_in_in_hours '
            .'parameter or delete outdated PendingOptIns with bin/console '
            .'newsletter-registration:delete-outdated-pending-opt-ins .',
            $code,
            $previous
        );
    }
}
