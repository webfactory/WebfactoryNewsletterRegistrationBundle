<?php

namespace Webfactory\NewsletterRegistrationBundle\Exception;

use Throwable;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

class RecipientClassCouldNotBeDeterminedException extends WebfactoryNewsletterRegistrationException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct(
            'We were unable to determine your '.RecipientInterface::class.' implementation. Consider replacing'
            .' the '.RecipientFactoryInterface::class.' service with your own implementation.',
            $code,
            $previous
        );
    }
}
