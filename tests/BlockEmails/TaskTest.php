<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\BlockEmails;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\BlockEmails\Task;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactory;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class TaskTest extends TestCase
{
    protected const BLOCK_DURATION_IN_DAYS = 30;

    /** @var EmailAddressFactoryInterface|MockObject */
    protected $emailAddressFactory;

    /** @var BlockedEmailAddressHashRepositoryInterface|MockObject */
    protected $blockedEmailHashesRepository;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailAddressFactory = new EmailAddressFactory('secret');
        $this->blockedEmailHashesRepository = $this->createMock(BlockedEmailAddressHashRepositoryInterface::class);
        $this->pendingOptInRepository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->task = new Task(
            self::BLOCK_DURATION_IN_DAYS,
            $this->emailAddressFactory,
            $this->blockedEmailHashesRepository,
            $this->pendingOptInRepository
        );
    }

    /**
     * @test
     */
    public function throws_exception_if_email_address_does_not_match_hash()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->expectException(EmailAddressDoesNotMatchHashOfPendingOptInException::class);

        $this->task->blockEmailsFor($pendingOptIn, 'other@example.com');
    }

    /**
     * @test
     */
    public function saves_block()
    {
        $this->blockedEmailHashesRepository->expects($this->once())->method('save');
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));

        $this->task->blockEmailsFor($pendingOptIn, 'webfactory@example.com');
    }

    /**
     * @test
     */
    public function replaces_older_block_if_it_exists()
    {
        $emailAddress = $this->emailAddressFactory->fromString('webfactory@example.com');
        $pendingOptIn = new PendingOptIn('uuid', $emailAddress);
        $olderBlock = BlockedEmailAddressHash::fromEmailAddress($emailAddress);

        $this->blockedEmailHashesRepository
            ->method('findByEmailAddress')
            ->with($emailAddress)
            ->willReturn($olderBlock);

        $this->blockedEmailHashesRepository
            ->expects($this->once())
            ->method('remove')
            ->with($olderBlock);

        $this->blockedEmailHashesRepository
            ->expects($this->once())
            ->method('save');

        $this->task->blockEmailsFor($pendingOptIn, 'webfactory@example.com');
    }

    /**
     * @test
     */
    public function removes_PendingOpIn()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->pendingOptInRepository->expects($this->once())->method('remove')->with($pendingOptIn);

        $this->task->blockEmailsFor($pendingOptIn, 'webfactory@example.com');
    }
}
