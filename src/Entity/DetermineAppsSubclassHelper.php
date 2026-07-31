<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Exception;
use ReflectionClass;

class DetermineAppsSubclassHelper
{
    public static function getAppsSubclassOf(string $parentClass, Exception $exceptionToThrowIfNotDeterminable): ?string
    {
        foreach (get_declared_classes() as $class) {
            if (
                is_subclass_of($class, $parentClass)
                && 0 !== strpos($class, 'Webfactory\NewsletterRegistrationBundle')
                && is_file((string) (new ReflectionClass($class))->getFileName())
            ) {
                return $class;
            }
        }

        throw $exceptionToThrowIfNotDeterminable;
    }
}
