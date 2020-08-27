<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class RecipientRepositoryTest extends TestCase
{
    /** @var ORMInfrastructure */
    private $infrastructure;

    /** @var RecipientRepositoryInterface */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(Recipient::class);
        $this->repository = $this->infrastructure->getRepository(Recipient::class);
    }

    /**
     * @test
     */
    public function isEmailAddressAlreadyRegistered_returns_true_if_already_registered()
    {
        $recipientFixture = new Recipient('uuid-1', new EmailAddress('webfactory@example.com', null));
        $this->infrastructure->import($recipientFixture);

        $retrievedRecipient = $this->repository->findByEmailAddress($recipientFixture->getEmailAddress());
        $this->assertNotNull($retrievedRecipient);
        $this->assertEquals($recipientFixture->getUuid(), $retrievedRecipient->getUuid());
    }

    /**
     * @test
     */
    public function isEmailAddressAlreadyRegistered_returns_null_if_not_already_registered()
    {
        $this->assertNull(
            $this->repository->findByEmailAddress(new EmailAddress('webfactory@example.com', null))
        );
    }
}
