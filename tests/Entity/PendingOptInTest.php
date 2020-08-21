<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class PendingOptInTest extends TestCase
{
    /**
     * @test
     */
    public function uuid_is_added_if_omitted(): void
    {
        $this->assertNotEmpty(
            (new PendingOptIn(null, new EmailAddress('webfactory@example.com', 'secret')))->getUuid()
        );
    }

    /**
     * @test
     */
    public function registrationDate_is_added_if_omitted(): void
    {
        $this->assertEqualsWithDelta(
            new \DateTime(),
            (new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret')))->getRegistrationDate(),
            1
        );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function static_construction_with_newsletters(): void
    {
        PendingOptIn::fromRegistrationFormData(
            [
                StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.org', 'secret'),
                StartRegistrationType::ELEMENT_NEWSLETTERS => [
                    new Newsletter(null, 'First Newsletter'),
                    new Newsletter(null, 'Second Newsletter'),
                ],
            ]
        );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function static_construction_without_newsletters(): void
    {
        PendingOptIn::fromRegistrationFormData(
            [
                StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.org', 'secret'),
            ]
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_true_if_matches(): void
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $this->assertTrue(
            $pendingOptIn->matchesEmailAddress(new EmailAddress('webfactory@example.com', 'secret'))
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_false_if_email_address_does_not_match(): void
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $this->assertFalse(
            $pendingOptIn->matchesEmailAddress(new EmailAddress('other@example.com', 'secret'))
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_false_if_secret_does_not_match(): void
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $this->assertFalse(
            $pendingOptIn->matchesEmailAddress(new EmailAddress('webfactory@example.com', 'other-secret'))
        );
    }

    /**
     * @test
     */
    public function isOutdated_returns_true_if_outdated(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new \DateTime('2000-01-01')
        );

        $this->assertTrue(
            $pendingOptIn->isOutdated(new \DateTime())
        );
    }

    /**
     * @test
     */
    public function isOutdated_returns_false_if_not_outdated(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new \DateTime()
        );

        $this->assertFalse(
            $pendingOptIn->isOutdated(new \DateTime('2000-01-01'))
        );
    }

    /**
     * @test
     */
    public function isAllowedToReceiveAnotherOptInEmail_returns_true_if_enough_time_passed_since_registration(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new \DateTime('2000-01-01')
        );

        $this->assertTrue(
            $pendingOptIn->isAllowedToReceiveAnotherOptInEmail(new \DateInterval('PT1H'), new \DateTime())
        );
    }

    /**
     * @test
     */
    public function isAllowedToReceiveAnotherOptInEmail_returns_false_if_too_little_time_passed_since_registration(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new \DateTime()
        );

        $this->assertFalse(
            $pendingOptIn->isAllowedToReceiveAnotherOptInEmail(new \DateInterval('PT1H'), new \DateTime())
        );
    }
}
