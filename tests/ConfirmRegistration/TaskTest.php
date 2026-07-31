<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\ConfirmRegistration;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\ConfirmRegistration\Task;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactory;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInIsOutdatedException;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class TaskTest extends TestCase
{
    protected const TIME_LIMIT_FOR_OPT_IN_IN_HOURS = 1;

    protected EmailAddressFactoryInterface $emailAddressFactory;
    protected RecipientFactoryInterface&MockObject $recipientFactory;
    protected RecipientRepositoryInterface&MockObject $recipientRepo;
    protected PendingOptInRepositoryInterface&MockObject $pendingOptInRepo;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailAddressFactory = new EmailAddressFactory('secret');
        $this->recipientFactory = $this->createMock(RecipientFactoryInterface::class);
        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->pendingOptInRepo = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->task = new Task(
            $this->pendingOptInRepo,
            self::TIME_LIMIT_FOR_OPT_IN_IN_HOURS,
            $this->emailAddressFactory,
            $this->recipientFactory,
            $this->recipientRepo
        );
    }

    #[Test]
    public function throws_exception_if_PendingOptIn_is_outdated()
    {
        $pendingOptIn = new PendingOptIn(
            'uuid',
            $this->emailAddressFactory->fromString('webfactory@example.com'),
            [],
            new DateTimeImmutable('2000-01-01')
        );
        $this->expectException(PendingOptInIsOutdatedException::class);

        $this->task->confirmRegistration($pendingOptIn, 'other@example.com');
    }

    #[Test]
    public function throws_exception_if_email_address_does_not_match_hash()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->expectException(EmailAddressDoesNotMatchHashOfPendingOptInException::class);

        $this->task->confirmRegistration($pendingOptIn, 'other@example.com');
    }

    #[Test]
    public function saves_recipient()
    {
        $this->recipientRepo->expects($this->once())->method('save');
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }

    #[Test]
    public function removes_pending_opt_in()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->pendingOptInRepo->expects($this->once())->method('remove')->with($pendingOptIn);

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }
<<<<<<< HEAD

    #[Test]
    public function writes_success_flash()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->flashBag->expects($this->once())->method('add');

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }
=======
>>>>>>> 69d36a6 (Refactoring: Move flash messages from Task services to Controller actions)
}
