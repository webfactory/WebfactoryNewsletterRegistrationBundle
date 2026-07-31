<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use App\PendingOptIn as AppPendingOptIn;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactory;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;

class PendingOptInFactoryTest extends TestCase
{
    protected PendingOptInFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new PendingOptInFactory();
    }

    #[Test]
    public function fromRegistrationFormData_without_newsletter_choices(): void
    {
        // The PendingOptInFactory uses get_declared_classes() to find a PendingOptInInterface
        // implementation outside the Webfactory\NewsletterRegistrationBundle namespace —
        // the class must be loaded before the factory is called:
        class_exists(AppPendingOptIn::class);

        $pendingOptIn = $this->factory->fromRegistrationFormData([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.com', 'secret'),
        ]);

        $this->assertEquals('webfactory@example.com', (string) $pendingOptIn->getEmailAddress());
    }

    #[Test]
    public function fromRegistrationFormData_with_newsletter_choices(): void
    {
        // The PendingOptInFactory uses get_declared_classes() to find a PendingOptInInterface
        // implementation outside the Webfactory\NewsletterRegistrationBundle namespace —
        // the class must be loaded before the factory is called:
        class_exists(AppPendingOptIn::class);

        $newslettersForPendingOptIn = [new Newsletter(1, 'newsletter 1')];
        $pendingOptIn = $this->factory->fromRegistrationFormData([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.com', 'secret'),
            StartRegistrationType::ELEMENT_NEWSLETTERS => $newslettersForPendingOptIn,
        ]);

        $this->assertEquals('webfactory@example.com', (string) $pendingOptIn->getEmailAddress());
        $this->assertEquals($newslettersForPendingOptIn, $pendingOptIn->getNewsletters());
    }
}
