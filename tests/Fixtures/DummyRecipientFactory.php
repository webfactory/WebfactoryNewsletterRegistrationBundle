<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Fixtures;

use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

/**
 * Creates Dummy\Recipient instances for the functional test application.
 *
 * The bundle's default RecipientFactory uses DetermineAppsSubclassHelper to find a
 * RecipientInterface implementation outside the bundle namespace. In the test kernel
 * that implementation does not exist, so this explicit factory is wired instead.
 */
class DummyRecipientFactory implements RecipientFactoryInterface
{
    public function fromPendingOptIn(PendingOptInInterface $pendingOptIn): RecipientInterface
    {
        return Recipient::fromPendingOptIn($pendingOptIn);
    }
}
