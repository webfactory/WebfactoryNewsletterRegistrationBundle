<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use DateTimeImmutable;
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
    public function findByEmailAddress_returns_PendingOptIn_if_it_exists(): void
    {
        $alreadyRegisteredEmailAddress = new EmailAddress('webfactory@example.com', 'secret');
        $pendingOptInFixture = new PendingOptIn('uuid-1', $alreadyRegisteredEmailAddress);
        $this->infrastructure->import($pendingOptInFixture);

        $result = $this->repository->findByEmailAddress($alreadyRegisteredEmailAddress);
        $this->assertNotEmpty($result);
        $this->assertEquals($pendingOptInFixture->getUuid(), $result->getUuid());
    }

    /**
     * @test
     */
    public function findByEmailAddress_returns_null_if_no_matching_PendingOptIn_exists(): void
    {
        $this->assertNull(
            $this->repository->findByEmailAddress(new EmailAddress('not-registered@example.com', 'secret'))
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
                new DateTimeImmutable('2000-01-01')
            )
        );

        $numberOfDeletedOnes = $this->repository->removeOutdated(new DateTimeImmutable());

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
                new DateTimeImmutable('-1h')
            )
        );

        $numberOfDeletedOnes = $this->repository->removeOutdated(new DateTimeImmutable('-72h'));

        $this->assertEquals(0, $numberOfDeletedOnes);
        $this->assertCount(1, $this->repository->findAll());
    }
}
