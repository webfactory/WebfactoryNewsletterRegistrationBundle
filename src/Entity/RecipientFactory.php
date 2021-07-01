<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use ReflectionMethod;
use Webfactory\NewsletterRegistrationBundle\Exception\RecipientClassCouldNotBeDeterminedException;

/**
 * Tries to determine the app's RecipientInterface implementation to call it's static construction method.
 */
class RecipientFactory implements RecipientFactoryInterface
{
    use DetermineAppsSubclassTrait;

    public function fromPendingOptIn(PendingOptInInterface $pendingOptIn): RecipientInterface
    {
        $appsRecipientClass = $this->getAppsSubclassOf(
            RecipientInterface::class,
            new RecipientClassCouldNotBeDeterminedException()
        );
        $reflectionMethod = new ReflectionMethod($appsRecipientClass, 'fromPendingOptIn');

        return $reflectionMethod->invoke(null, $pendingOptIn);
    }
}
