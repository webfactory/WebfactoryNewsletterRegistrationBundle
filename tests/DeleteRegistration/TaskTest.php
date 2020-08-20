<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\DeleteRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\DeleteRegistration\Task;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class TaskTest extends TestCase
{
    /** @var RecipientRepositoryInterface|MockObject */
    protected $recipientRepo;

    /** @var FlashBagInterface|MockObject */
    protected $flashBag;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->task = new Task($this->recipientRepo, $this->flashBag);
    }

    /**
     * @test
     */
    public function removes_recipient()
    {
        $recipient = new Recipient('uuid', new EmailAddress('webfactory@example.com', null));
        $this->recipientRepo->expects($this->once())->method('remove')->with($recipient);

        $this->task->deleteRegistration($recipient);
    }

    /**
     * @test
     */
    public function writes_success_flash()
    {
        $recipient = new Recipient('uuid', new EmailAddress('webfactory@example.com', null));
        $this->flashBag->expects($this->once())->method('add');

        $this->task->deleteRegistration($recipient);
    }
}
