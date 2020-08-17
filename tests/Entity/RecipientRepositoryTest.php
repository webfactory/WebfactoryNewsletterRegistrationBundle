<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
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
        $alreadyRegisteredEmailAddress = 'webfactory@example.com';
        $this->infrastructure->import(new Recipient('uuid-1', $alreadyRegisteredEmailAddress));

        $this->assertTrue($this->repository->isEmailAddressAlreadyRegistered($alreadyRegisteredEmailAddress));
    }

    /**
     * @test
     */
    public function isEmailAddressAlreadyRegistered_returns_false_if_not_already_registered()
    {
        $this->assertFalse($this->repository->isEmailAddressAlreadyRegistered('webfactory@example.com'));
    }
}
