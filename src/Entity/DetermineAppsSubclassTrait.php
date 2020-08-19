<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

trait DetermineAppsSubclassTrait
{
    protected function getAppsSubclassOf(string $parentClass, \Exception $exceptionToThrowIfNotDeterminable): ?string
    {
        foreach (get_declared_classes() as $class) {
            if (
                is_subclass_of($class, $parentClass)
                && 0 !== strpos($class, 'Webfactory\NewsletterRegistrationBundle')
            ) {
                return $class;
            }
        }

        throw $exceptionToThrowIfNotDeterminable;
    }
}
