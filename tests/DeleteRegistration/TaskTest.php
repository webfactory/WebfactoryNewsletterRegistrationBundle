<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\DeleteRegistration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\DeleteRegistration\Task;
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
    public function removes_recipient()
    {
        $recipient = new Recipient('uuid', new EmailAddress('webfactory@example.com', null));
        $this->recipientRepo->expects($this->once())->method('remove')->with($recipient);

        $this->task->deleteRegistration($recipient);
    }
}
