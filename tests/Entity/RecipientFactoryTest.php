<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use App\Recipient as AppRecipient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactory;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class RecipientFactoryTest extends TestCase
{
    #[Test]
    public function fromPendingOptIn()
    {
        // The RecipientFactory uses get_declared_classes() to find a RecipientInterface
        // implementation outside the Webfactory\NewsletterRegistrationBundle namespace —
        // the class must be loaded before the factory is called:
        class_exists(AppRecipient::class);

        $categoriesForPendingOptIn = [new Category(1, 'category 1')];
        $recipient = (new RecipientFactory())->fromPendingOptIn(
            new PendingOptIn('uuid', new EmailAddress('webfactory@example.com', 'secret'), $categoriesForPendingOptIn)
        );

        $this->assertEquals('uuid', $recipient->getUuid());
        $this->assertEquals('webfactory@example.com', (string) $recipient->getEmailAddress());
        $this->assertEquals($categoriesForPendingOptIn, $recipient->getCategories());
    }
}
