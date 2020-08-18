<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Symfony\Component\Form\FormInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\RecipientClassCouldNotBeDeterminedException;

/**
 * Tries to determine the app's RecipientInterface implementation to call it's static construction method.
 */
class RecipientFactory implements RecipientFactoryInterface
{
    public function fromRegistrationForm(FormInterface $form): RecipientInterface
    {
        $appsRecipientClass = $this->getAppsSubclassOf(RecipientInterface::class);
        $reflectionMethod = new \ReflectionMethod($appsRecipientClass, 'fromFormData');

        return $reflectionMethod->invoke(null, $form->getData());
    }

    protected function getAppsSubclassOf(string $parentClass): ?string
    {
        foreach (get_declared_classes() as $class) {
            if (
                is_subclass_of($class, $parentClass)
                && 0 !== strpos($class, 'Webfactory\NewsletterRegistrationBundle')
            ) {
                return $class;
            }
        }

        throw new RecipientClassCouldNotBeDeterminedException();
    }
}
