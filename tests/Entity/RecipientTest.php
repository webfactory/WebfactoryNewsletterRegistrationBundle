<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Form\RegisterType;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class RecipientTest extends TestCase
{
    /**
     * @test
     */
    public function uuid_is_added_if_omitted()
    {
        $this->assertNotEmpty(
            (new Recipient(null, 'webfactory@example.com'))->getUuid()
        );
    }

    /**
     * @test
     */
    public function email_address_gets_normalized()
    {
        $this->assertEquals(
            'webfactory@example.com',
            (new Recipient('uuid', 'WEBFACTORY@EXAMPLE.COM'))->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function registrationDate_is_added_if_omitted()
    {
        $this->assertEqualsWithDelta(
            new \DateTime(),
            (new Recipient('uuid', 'webfactory@example.com'))->getRegistrationDate(),
            1
        );
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function static_construction_with_newsletters()
    {
        Recipient::fromFormData([
            RegisterType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.org',
        ]);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function static_construction_without_newsletters()
    {
        Recipient::fromFormData([
            RegisterType::ELEMENT_EMAIL_ADDRESS => 'webfactory@example.org',
            RegisterType::ELEMENT_NEWSLETTERS => [
                new Newsletter(null, 'First Newsletter'),
                new Newsletter(null, 'Second Newsletter'),
            ],
        ]);
    }
}
