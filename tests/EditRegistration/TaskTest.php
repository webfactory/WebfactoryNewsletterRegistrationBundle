<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\EditRegistration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\Task;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class TaskTest extends TestCase
{
    protected RecipientRepositoryInterface&MockObject $recipientRepo;
    protected Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->task = new Task($this->recipientRepo);
    }

    #[Test]
    public function saves_recipient()
    {
        $recipient = new Recipient('uuid', new EmailAddress('webfactory@example.com', null));
        $this->recipientRepo->expects($this->once())->method('save')->with($recipient);

        $this->task->editRegistration($recipient);
    }
<<<<<<< HEAD

    #[Test]
    public function writes_success_flash()
    {
        $recipient = new Recipient('uuid', new EmailAddress('webfactory@example.com', null));
        $this->flashBag->expects($this->once())->method('add');

        $this->task->editRegistration($recipient);
    }
=======
>>>>>>> 69d36a6 (Refactoring: Move flash messages from Task services to Controller actions)
}
