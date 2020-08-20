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
    public function uuid_is_added_if_omitted()
    {
        $this->assertNotEmpty(
            (new PendingOptIn(null, new EmailAddress('webfactory@example.com', 'secret')))->getUuid()
        );
    }

    /**
     * @test
     */
    public function registrationDate_is_added_if_omitted()
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
    public function static_construction_with_newsletters()
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
    public function static_construction_without_newsletters()
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
    public function emailAddressMatchesHash_returns_true_if_matches()
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $this->assertTrue(
            $pendingOptIn->matchesEmailAddress(new EmailAddress('webfactory@example.com', 'secret'))
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_false_if_email_address_does_not_match()
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $this->assertFalse(
            $pendingOptIn->matchesEmailAddress(new EmailAddress('other@example.com', 'secret'))
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_false_if_secret_does_not_match()
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $this->assertFalse(
            $pendingOptIn->matchesEmailAddress(new EmailAddress('webfactory@example.com', 'other-secret'))
        );
    }
}
