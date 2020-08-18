<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Symfony\Component\Form\FormInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInClassCouldNotBeDeterminedException;

/**
 * Tries to determine the app's PendingOptInInterface implementation to call it's static construction method.
 */
class PendingOptInFactory implements PendingOptInFactoryInterface
{
    /** @var string */
    protected $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function fromRegistrationForm(FormInterface $form): PendingOptInInterface
    {
        $appsPendingOptInClass = $this->getAppsSubclassOf(PendingOptInInterface::class);
        $reflectionMethod = new \ReflectionMethod($appsPendingOptInClass, 'fromRegistrationFormData');

        return $reflectionMethod->invoke(null, $form->getData(), $this->secret);
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

        throw new PendingOptInClassCouldNotBeDeterminedException();
    }
}
