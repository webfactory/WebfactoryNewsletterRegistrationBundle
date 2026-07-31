<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;

class NewsletterTest extends TestCase
{
    #[DoesNotPerformAssertions]
    #[Test]
    public function can_be_constructed()
    {
        new Newsletter(null, 'newsletter name');
    }
}
