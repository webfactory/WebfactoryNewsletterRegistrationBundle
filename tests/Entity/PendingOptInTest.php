<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Type as StartRegistrationType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class PendingOptInTest extends TestCase
{
    #[Test]
    public function uuid_is_added_if_omitted(): void
    {
        $this->assertNotEmpty(
            (new PendingOptIn(null, new EmailAddress('webfactory@example.com', 'secret')))->getUuid()
        );
    }

    #[Test]
    public function registrationDate_is_added_if_omitted(): void
    {
        $this->assertEqualsWithDelta(
            new DateTimeImmutable(),
            (new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret')))->getRegistrationDate(),
            1
        );
    }

    #[DoesNotPerformAssertions]
    #[Test]
    public function static_construction_with_categories(): void
    {
        PendingOptIn::fromRegistrationFormData(
            [
                StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.org', 'secret'),
                StartRegistrationType::ELEMENT_CATEGORIES => [
                    new Category(null, 'First Category'),
                    new Category(null, 'Second Category'),
                ],
            ]
        );
    }

    #[DoesNotPerformAssertions]
    #[Test]
    public function static_construction_without_categories(): void
    {
        PendingOptIn::fromRegistrationFormData(
            [
                StartRegistrationType::ELEMENT_EMAIL_ADDRESS => new EmailAddress('webfactory@example.org', 'secret'),
            ]
        );
    }

    #[Test]
    public function static_construction_without_email_address_returns_NULL(): void
    {
        $this->assertNull(
            PendingOptIn::fromRegistrationFormData([
                StartRegistrationType::ELEMENT_EMAIL_ADDRESS => null,
            ])
        );
    }

    #[Test]
    public function setEmailAddressIfItMatchesStoredHash_sets_EmailAddress_if_it_matches_stored_Hash(): void
    {
        $emailAddressFixture = new EmailAddress('webfactory@example.com', 'secret');
        $pendingOptIn = new PendingOptIn('uuid', $emailAddressFixture);

        $pendingOptIn->setEmailAddressIfItMatchesStoredHash($emailAddressFixture);

        $this->assertEquals($emailAddressFixture, $pendingOptIn->getEmailAddress());
    }

    #[Test]
    public function setEmailAddressIfItMatchesStoredHash_throws_Exception_if_email_address_does_not_match(): void
    {
        $this->expectException(EmailAddressDoesNotMatchHashOfPendingOptInException::class);

        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $pendingOptIn->setEmailAddressIfItMatchesStoredHash(new EmailAddress('other@example.com', 'secret'));
    }

    #[Test]
    public function setEmailAddressIfItMatchesStoredHash_throws_Exception_if_secret_does_not_match(): void
    {
        $this->expectException(EmailAddressDoesNotMatchHashOfPendingOptInException::class);

        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        $pendingOptIn->setEmailAddressIfItMatchesStoredHash(new EmailAddress('webfactory@example.com', 'other-secret'));
    }

    #[Test]
    public function isOutdated_returns_true_if_outdated(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new DateTimeImmutable('2000-01-01')
        );

        $this->assertTrue(
            $pendingOptIn->isOutdated(new DateTimeImmutable())
        );
    }

    #[Test]
    public function isOutdated_returns_false_if_not_outdated(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new DateTimeImmutable()
        );

        $this->assertFalse(
            $pendingOptIn->isOutdated(new DateTimeImmutable('2000-01-01'))
        );
    }

    #[Test]
    public function isAllowedToReceiveAnotherOptInEmail_returns_true_if_enough_time_passed_since_registration(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new DateTimeImmutable('2000-01-01')
        );

        $this->assertTrue(
            $pendingOptIn->isAllowedToReceiveAnotherOptInEmail(new DateInterval('PT1H'), new DateTimeImmutable())
        );
    }

    #[Test]
    public function isAllowedToReceiveAnotherOptInEmail_returns_false_if_too_little_time_passed_since_registration(): void
    {
        $pendingOptIn = new PendingOptIn(
            null,
            new EmailAddress('webfactory@example.com', 'secret'),
            [],
            new DateTimeImmutable()
        );

        $this->assertFalse(
            $pendingOptIn->isAllowedToReceiveAnotherOptInEmail(new DateInterval('PT1H'), new DateTimeImmutable())
        );
    }
}
