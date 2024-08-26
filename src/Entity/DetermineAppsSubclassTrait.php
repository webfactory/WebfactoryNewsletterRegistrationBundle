<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Exception;

trait DetermineAppsSubclassTrait
{
    protected function getAppsSubclassOf(string $parentClass, Exception $exceptionToThrowIfNotDeterminable): ?string
    {
        foreach (get_declared_classes() as $class) {
            if (
                is_subclass_of($class, $parentClass)
                && !str_starts_with($class, 'Webfactory\NewsletterRegistrationBundle')
            ) {
                return $class;
            }
        }

        throw $exceptionToThrowIfNotDeterminable;
    }
}
