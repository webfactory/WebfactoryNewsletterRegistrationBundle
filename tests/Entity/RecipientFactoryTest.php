<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\Recipient;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactory;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class RecipientFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function fromPendingOptIn()
    {
        // The RecipientFactory searches for a RecipientInterface implementation outside the
        // Webfactory\NewsletterRegistrationBundle namespace, so let's declare an anonymous one:
        new class(null, new EmailAddress('webfactory@example.com', null)) extends Recipient {
        };

        $newslettersForPendingOptIn = [new Newsletter(1, 'newsletter 1')];
        $recipient = (new RecipientFactory())->fromPendingOptIn(
            new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'), $newslettersForPendingOptIn)
        );

        $this->assertEquals('uuid', $recipient->getUuid());
        $this->assertEquals('webfactory@example.com', (string) $recipient->getEmailAddress());
        $this->assertEquals($newslettersForPendingOptIn, $recipient->getNewsletters());
    }
}
