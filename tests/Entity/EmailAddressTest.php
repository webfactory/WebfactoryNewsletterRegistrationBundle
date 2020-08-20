<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressCanNotBeHashedWithoutSecretException;

class EmailAddressTest extends TestCase
{
    /**
     * @test
     */
    public function email_address_gets_normalized()
    {
        $this->assertEquals(
            'webfactory@example.com',
            (new EmailAddress('WEBFACTORY@EXAMPLE.COM', 'secret'))->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function email_address_gets_hashed()
    {
        $this->assertNotEmpty(
            (new EmailAddress('WEBFACTORY@EXAMPLE.COM', 'secret'))->getHash()
        );
    }

    /**
     * @test
     */
    public function throws_exception_when_trying_to_hash_without_secret()
    {
        $this->expectException(EmailAddressCanNotBeHashedWithoutSecretException::class);
        (new EmailAddress('WEBFACTORY@EXAMPLE.COM', null))->getHash();
    }
}
