<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Form\StartRegistrationType;
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
            (new PendingOptIn(null, 'webfactory@example.com','secret'))->getUuid()
        );
    }

    /**
     * @test
     */
    public function email_address_gets_normalized()
    {
        $this->assertEquals(
            'webfactory@example.com',
            (new PendingOptIn('uuid', 'WEBFACTORY@EXAMPLE.COM', 'secret'))->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function registrationDate_is_added_if_omitted()
    {
        $this->assertEqualsWithDelta(
            new \DateTime(),
            (new PendingOptIn('uuid', 'webfactory@example.com', 'secret'))->getRegistrationDate(),
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
                StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.org',
                StartRegistrationType::ELEMENT_NEWSLETTERS => [
                    new Newsletter(null, 'First Newsletter'),
                    new Newsletter(null, 'Second Newsletter'),
                ],
            ],
            'secret'
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
                StartRegistrationType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.org',
            ],
            'secret'
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_true_if_matches()
    {
        $pendingOptIn = new PendingOptIn('uuid', 'webfactory@example.com', 'secret');

        $this->assertTrue(
            $pendingOptIn->emailAddressMatchesHash('webfactory@example.com', 'secret')
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_false_if_email_address_does_not_match()
    {
        $pendingOptIn = new PendingOptIn('uuid', 'webfactory@example.com', 'secret');

        $this->assertFalse(
            $pendingOptIn->emailAddressMatchesHash('other@example.com', 'secret')
        );
    }

    /**
     * @test
     */
    public function emailAddressMatchesHash_returns_false_if_secret_does_not_match()
    {
        $pendingOptIn = new PendingOptIn('uuid', 'webfactory@example.com', 'secret');

        $this->assertFalse(
            $pendingOptIn->emailAddressMatchesHash('webfactory@example.com', 'other-secret')
        );
    }
}
