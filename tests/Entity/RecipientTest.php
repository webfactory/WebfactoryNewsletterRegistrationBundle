<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class RecipientTest extends TestCase
{
    /**
     * @test
     */
    public function uuid_is_added_if_omitted()
    {
        $this->assertNotEmpty(
            (new Recipient(null, new EmailAddress('webfactory@example.com', null)))->getUuid()
        );
    }

    /**
     * @test
     */
    public function optInDate_is_added_if_omitted()
    {
        $this->assertEqualsWithDelta(
            new DateTimeImmutable(),
            (new Recipient('uuid', new EmailAddress('webfactory@example.com', null)))->getOptInDate(),
            1
        );
    }

    /**
     * @test
     */
    public function static_construction_with_newsletters()
    {
        $newslettersForPendingOptIn = [new Newsletter(1, 'newsletter 1'), new Newsletter(2, 'newsletter 2')];
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'), $newslettersForPendingOptIn);

        $recipient = Recipient::fromPendingOptIn($pendingOptIn);

        $this->assertEquals('uuid', $recipient->getUuid());
        $this->assertEquals('webfactory@example.com', (string) $recipient->getEmailAddress());
        $this->assertEquals($newslettersForPendingOptIn, $recipient->getNewsletters());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function static_construction_without_newsletters()
    {
        $pendingOptIn = new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'));

        Recipient::fromPendingOptIn($pendingOptIn);
    }
}
