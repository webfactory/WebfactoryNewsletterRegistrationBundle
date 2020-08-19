<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use Symfony\Component\Form\FormInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInClassCouldNotBeDeterminedException;

/**
 * Tries to determine the app's PendingOptInInterface implementation to call it's static construction method.
 */
class PendingOptInFactory implements PendingOptInFactoryInterface
{
    use DetermineAppsSubclassTrait;

    /** @var string */
    protected $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function fromRegistrationForm(FormInterface $form): PendingOptInInterface
    {
        $appsPendingOptInClass = $this->getAppsSubclassOf(
            PendingOptInInterface::class,
            new PendingOptInClassCouldNotBeDeterminedException()
        );
        $reflectionMethod = new \ReflectionMethod($appsPendingOptInClass, 'fromRegistrationFormData');

        return $reflectionMethod->invoke(null, $form->getData(), $this->secret);
    }
}
