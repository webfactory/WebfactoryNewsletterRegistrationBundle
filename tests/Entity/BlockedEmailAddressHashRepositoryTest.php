<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;

class BlockedEmailAddressHashRepositoryTest extends TestCase
{
    /** @var ORMInfrastructure */
    private $infrastructure;

    /** @var BlockedEmailAddressHashRepositoryInterface */
    private $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->infrastructure = ORMInfrastructure::createWithDependenciesFor(BlockedEmailAddressHash::class);
        $this->repository = $this->infrastructure->getRepository(BlockedEmailAddressHash::class);
    }

    /**
     * @test
     */
    public function findByEmailAddress_returns_BlockedEmailAddressHash_if_it_exists(): void
    {
        $emailAddress = new EmailAddress('webfactory@example.com', 'secret');
        $blockedEmailAddressHashFixture = BlockedEmailAddressHash::fromEmailAddress($emailAddress);

        $this->infrastructure->import($blockedEmailAddressHashFixture);

        $result = $this->repository->findByEmailAddress($emailAddress);
        $this->assertNotEmpty($result);
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
}
