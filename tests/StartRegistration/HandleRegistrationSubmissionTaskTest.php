<?php

namespace Tests\Webfactory\NewsletterRegistrationBundle\StartRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\HandleRegistrationSubmissionTask;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\SendLinkTaskInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\TaskInterface as StartRegistrationTaskInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class HandleRegistrationSubmissionTaskTest extends TestCase
{
    /** @var RecipientRepositoryInterface */
    private $recipientRepository;

    /** @var Environment */
    private $twig;

    /** @var StartRegistrationTaskInterface|MockObject */
    private $startRegistrationTask;

    /** @var SendLinkTaskInterface|MockObject */
    private $sendLinkTask;

    /** @var HandleRegistrationSubmissionTask */
    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipientRepository = $this->createMock(RecipientRepositoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->startRegistrationTask = $this->createMock(StartRegistrationTaskInterface::class);
        $this->sendLinkTask = $this->createMock(SendLinkTaskInterface::class);
        $this->task = new HandleRegistrationSubmissionTask(
            $this->recipientRepository,
            $this->twig,
            $this->startRegistrationTask,
            $this->sendLinkTask
        );
    }

    /**
     * @test
     */
    public function runs_SendLinkTask_if_user_is_already_registered()
    {
        $pendingOptIn = new PendingOptIn(null, new EmailAddress('webfactory@example.org', 'secret'));
        $recipientFixture = Recipient::fromPendingOptIn($pendingOptIn);

        $this->recipientRepository
            ->expects($this->once())
            ->method('findByEmailAddress')
            ->willReturn($recipientFixture);

        $this->startRegistrationTask
            ->expects($this->never())
            ->method('startRegistration');

        $this->sendLinkTask
            ->expects($this->once())
            ->method('sendEditRegistrationLink')
            ->with($recipientFixture);

        $this->task->handleRegistrationSubmission($pendingOptIn);
    }

    /**
     * @test
     */
    public function runs_StartRegistrationTask_if_user_is_not_yet_registered()
    {
        $pendingOptIn = new PendingOptIn(null, new EmailAddress('webfactory@example.org', 'secret'));

        $this->recipientRepository
            ->expects($this->once())
            ->method('findByEmailAddress')
            ->willReturn(null);

        $this->sendLinkTask
            ->expects($this->never())
            ->method('sendEditRegistrationLink');

        $this->startRegistrationTask
            ->expects($this->once())
            ->method('startRegistration')
            ->with($pendingOptIn);

        $this->task->handleRegistrationSubmission($pendingOptIn);
    }
}
