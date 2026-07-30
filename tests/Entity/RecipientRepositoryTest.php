<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;
use Webfactory\NewsletterRegistrationBundle\Tests\Factory\RecipientFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RecipientRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private RecipientRepositoryInterface $repository;

    /** @see \PHPUnit_Framework_TestCase::setUp() */
    protected function setUp(): void
    {
        $this->repository = self::getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(Recipient::class);
    }

    /**
     * @test
     */
    public function isEmailAddressAlreadyRegistered_returns_true_if_already_registered()
    {
        $emailAddress = new EmailAddress('webfactory@example.com', null);
        $registeredRecipient = RecipientFactory::createOne(['emailAddress' => $emailAddress]);

        $retrievedRecipient = $this->repository->findByEmailAddress($emailAddress);

        $this->assertNotNull($retrievedRecipient);
        $this->assertEquals($registeredRecipient->getUuid(), $retrievedRecipient->getUuid());
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
