<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Resources;

use AppBundle\Newsletter\Entity\Newsletter as NewsletterTemplate;
use AppBundle\Newsletter\Entity\NewsletterRepository as NewsletterRepositoryTemplate;
use AppBundle\Newsletter\Entity\PendingOptIn as PendingOptInTemplate;
use AppBundle\Newsletter\Entity\PendingOptInRepository as PendingOptInRepositoryTemplate;
use AppBundle\Newsletter\Entity\Recipient as RecipientTemplate;
use AppBundle\Newsletter\Entity\RecipientRepository as RecipientRepositoryTemplate;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\Newsletter as AbstractNewsletter;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterRepository as AbstractNewsletterRepository;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptIn as AbstractPendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepository as AbstractPendingOptInRepository;
use Webfactory\NewsletterRegistrationBundle\Entity\Recipient as AbstractRecipient;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepository as AbstractRecipientRepository;

class AppClassTemplatesTest extends TestCase
{
    /** @test */
    public function newsletter_template_extends_abstract_newsletter(): void
    {
        self::assertTrue(is_subclass_of(NewsletterTemplate::class, AbstractNewsletter::class));
    }

    /** @test */
    public function recipient_template_extends_abstract_recipient(): void
    {
        self::assertTrue(is_subclass_of(RecipientTemplate::class, AbstractRecipient::class));
    }

    /** @test */
    public function pending_opt_in_template_extends_abstract_pending_opt_in(): void
    {
        self::assertTrue(is_subclass_of(PendingOptInTemplate::class, AbstractPendingOptIn::class));
    }

    /** @test */
    public function newsletter_repository_template_extends_abstract_newsletter_repository(): void
    {
        self::assertTrue(is_subclass_of(NewsletterRepositoryTemplate::class, AbstractNewsletterRepository::class));
    }

    /** @test */
    public function recipient_repository_template_extends_abstract_recipient_repository(): void
    {
        self::assertTrue(is_subclass_of(RecipientRepositoryTemplate::class, AbstractRecipientRepository::class));
    }

    /** @test */
    public function pending_opt_in_repository_template_extends_abstract_pending_opt_in_repository(): void
    {
        self::assertTrue(is_subclass_of(PendingOptInRepositoryTemplate::class, AbstractPendingOptInRepository::class));
    }
}
