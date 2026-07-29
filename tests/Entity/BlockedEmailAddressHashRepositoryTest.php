<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\BlockedEmailAddressHashFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class BlockedEmailAddressHashRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    /** @var BlockedEmailAddressHashRepositoryInterface */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->repository = self::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(BlockedEmailAddressHash::class);
    }

    /**
     * @test
     */
    public function findByEmailAddress_returns_BlockedEmailAddressHash_if_it_exists(): void
    {
        $emailAddress = new EmailAddress('webfactory@example.com', 'secret');
        BlockedEmailAddressHashFactory::createOne(['hash' => $emailAddress->getHash()]);

        $this->assertNotEmpty($this->repository->findByEmailAddress($emailAddress));
    }

    /**
     * @test
     */
    public function findByEmailAddress_returns_null_if_no_matching_BlockedEmailAddressHash_exists(): void
    {
        $this->assertNull(
            $this->repository->findByEmailAddress(new EmailAddress('webfactory@example.com', 'secret'))
        );
    }

    /**
     * @test
     */
    public function removeOutdated_removes_outdated_ones(): void
    {
        BlockedEmailAddressHashFactory::createOne(['blockDate' => new DateTimeImmutable('2000-01-01')]);

        $numberOfDeletedOnes = $this->repository->removeOutdated(new DateTimeImmutable());

        $this->assertEquals(1, $numberOfDeletedOnes);
        $this->assertCount(0, $this->repository->findAll());
    }

    /**
     * @test
     */
    public function removeOutdated_does_not_remove_current_ones(): void
    {
        BlockedEmailAddressHashFactory::createOne(['blockDate' => new DateTimeImmutable('-1d')]);

        $numberOfDeletedOnes = $this->repository->removeOutdated(new DateTimeImmutable('-30d'));

        $this->assertEquals(0, $numberOfDeletedOnes);
        $this->assertCount(1, $this->repository->findAll());
    }
}
