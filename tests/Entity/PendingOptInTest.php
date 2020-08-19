<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class PendingOptInTest extends TestCase
{
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
