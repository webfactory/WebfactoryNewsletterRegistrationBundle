<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class RecipientTest extends TestCase
{
    #[Test]
    public function uuid_is_added_if_omitted()
    {
        $this->assertNotEmpty(
            (new Recipient(null, new EmailAddress('webfactory@example.com', null)))->getUuid()
        );
    }

    #[Test]
    public function optInDate_is_added_if_omitted()
    {
        $this->assertEqualsWithDelta(
            new DateTimeImmutable(),
            (new Recipient('uuid', new EmailAddress('webfactory@example.com', null)))->getOptInDate(),
            1
        );
    }

    #[Test]
    public function static_construction_with_categories()
    {
        $categoriesForPendingOptIn = [new Category(1, 'category 1'), new Category(2, 'category 2')];
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'), $categoriesForPendingOptIn);

        $recipient = Recipient::fromPendingOptIn($pendingOptIn);

        $this->assertEquals('uuid', $recipient->getUuid());
        $this->assertEquals('webfactory@example.com', (string) $recipient->getEmailAddress());
        $this->assertEquals($categoriesForPendingOptIn, $recipient->getCategories());
    }

    #[DoesNotPerformAssertions]
    #[Test]
    public function static_construction_without_categories()
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        Recipient::fromPendingOptIn($pendingOptIn);
    }
}
