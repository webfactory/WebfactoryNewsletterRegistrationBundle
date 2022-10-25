<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;

class NewsletterTest extends TestCase
{
    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function can_be_constructed()
    {
        new Newsletter(null, 'newsletter name');
    }
}
