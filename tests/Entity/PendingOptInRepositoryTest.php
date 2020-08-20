<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class PendingOptInRepositoryTest extends TestCase
{
    /** @var ORMInfrastructure */
    private $infrastructure;

    /** @var PendingOptInRepositoryInterface */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(PendingOptIn::class);
        $this->repository = $this->infrastructure->getRepository(PendingOptIn::class);
    }

    /**
     * @test
     */
    public function isEmailAddressAlreadyRegistered_returns_true_if_already_registered(): void
    {
        $alreadyRegisteredEmailAddress = new EmailAddress('webfactory@example.com', 'secret');
        $this->infrastructure->import(new PendingOptIn('uuid-1', $alreadyRegisteredEmailAddress));

        $this->assertTrue($this->repository->isEmailAddressAlreadyRegistered($alreadyRegisteredEmailAddress));
    }

    /**
     * @test
     */
    public function isEmailAddressAlreadyRegistered_returns_false_if_not_already_registered(): void
    {
        $this->assertFalse(
            $this->repository->isEmailAddressAlreadyRegistered(new EmailAddress('webfactory@example.com', 'secret'))
        );
    }

    /**
     * @test
     */
    public function removeOutdated_removes_outdated_ones(): void
    {
        $this->infrastructure->import(
            new PendingOptIn(
                'uuid-1',
                new EmailAddress('webfactory@example.com', 'secret'),
                [],
                new \DateTime('2000-01-01')
            )
        );

        $numberOfDeletedOnes = $this->repository->removeOutdated(new \DateTime());

        $this->assertEquals(1, $numberOfDeletedOnes);
        $this->assertCount(0, $this->repository->findAll());
    }

    /**
     * @test
     */
    public function removeOutdated_does_not_remove_current_ones(): void
    {
        $this->infrastructure->import(
            new PendingOptIn(
                'uuid-1',
                new EmailAddress('webfactory@example.com', 'secret'),
                [],
                new \DateTime('-1h')
            )
        );

        $numberOfDeletedOnes = $this->repository->removeOutdated(new \DateTime('-72h'));

        $this->assertEquals(0, $numberOfDeletedOnes);
        $this->assertCount(1, $this->repository->findAll());
    }
}
