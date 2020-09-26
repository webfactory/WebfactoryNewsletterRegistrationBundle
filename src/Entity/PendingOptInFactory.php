<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInClassCouldNotBeDeterminedException;

/**
 * Tries to determine the app's PendingOptInInterface implementation to call it's static construction method.
 */
class PendingOptInFactory implements PendingOptInFactoryInterface
{
    use DetermineAppsSubclassTrait;

    public function fromRegistrationFormData(array $formData): ?PendingOptInInterface
    {
        $appsPendingOptInClass = $this->getAppsSubclassOf(
            PendingOptInInterface::class,
            new PendingOptInClassCouldNotBeDeterminedException()
        );
        $reflectionMethod = new \ReflectionMethod($appsPendingOptInClass, 'fromRegistrationFormData');

        return $reflectionMethod->invoke(null, $formData);
    }
}
