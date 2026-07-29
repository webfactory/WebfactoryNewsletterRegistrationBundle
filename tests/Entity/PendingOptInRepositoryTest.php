<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\PendingOptInFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PendingOptInRepositoryTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    /** @var PendingOptInRepositoryInterface */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->repository = self::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(PendingOptIn::class);
    }

    /**
     * @test
     */
    public function findByEmailAddress_returns_PendingOptIn_if_it_exists(): void
    {
        $emailAddress = new EmailAddress('webfactory@example.com', 'secret');
        $proxy = PendingOptInFactory::createOne(['emailAddress' => $emailAddress]);

        $result = $this->repository->findByEmailAddress($emailAddress);

        $this->assertNotEmpty($result);
        $this->assertEquals($proxy->getUuid(), $result->getUuid());
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
        PendingOptInFactory::createOne(['registrationDate' => new DateTimeImmutable('2000-01-01')]);

        $numberOfDeletedOnes = $this->repository->removeOutdated(new DateTimeImmutable());

        $this->assertEquals(1, $numberOfDeletedOnes);
        $this->assertCount(0, $this->repository->findAll());
    }

    /**
     * @test
     */
    public function removeOutdated_does_not_remove_current_ones(): void
    {
        PendingOptInFactory::createOne(['registrationDate' => new DateTimeImmutable('-1h')]);

        $numberOfDeletedOnes = $this->repository->removeOutdated(new DateTimeImmutable('-72h'));

        $this->assertEquals(0, $numberOfDeletedOnes);
        $this->assertCount(1, $this->repository->findAll());
    }
}
