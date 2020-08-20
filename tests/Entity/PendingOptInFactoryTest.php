<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInFactory;
use Webfactory\NewsletterRegistrationBundle\Form\StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;

class PendingOptInFactoryTest extends TestCase
{
    /** @var PendingOptInFactory */
    protected $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new PendingOptInFactory();
    }

    /**
     * @test
     */
    public function fromRegistrationFormData_without_newsletter_choices(): void
    {
        // The PendingOptInFactory searches for a PendingOptInInterface implementation outside the
        // Webfactory\NewsletterRegistrationBundle namespace, so let's declare an anonymous one:
        new class(null, new EmailAddress('webfactory@example.com', 'secret')) extends PendingOptIn {
        };

        $pendingOptIn = $this->factory->fromRegistrationFormData([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.com', 'secret'),
        ]);

        $this->assertEquals('webfactory@example.com', $pendingOptIn->getEmailAddress());
    }

    /**
     * @test
     */
    public function fromRegistrationFormData_with_newsletter_choices(): void
    {
        // The PendingOptInFactory searches for a PendingOptInInterface implementation outside the
        // Webfactory\NewsletterRegistrationBundle namespace, so let's declare an anonymous one:
        new class(null, new EmailAddress('webfactory@example.com', 'secret')) extends PendingOptIn {
        };

        $newslettersForPendingOptIn = [new Newsletter(1, 'newsletter 1')];
        $pendingOptIn = $this->factory->fromRegistrationFormData([
            StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.com', 'secret'),
            StartRegistrationType::ELEMENT_NEWSLETTERS => $newslettersForPendingOptIn,
        ]);

        $this->assertEquals('webfactory@example.com', $pendingOptIn->getEmailAddress());
        $this->assertEquals($newslettersForPendingOptIn, $pendingOptIn->getNewsletters());
    }
}
