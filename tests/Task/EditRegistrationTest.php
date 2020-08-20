<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Task;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Task\EditRegistration;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class EditRegistrationTest extends TestCase
{
    /** @var RecipientRepositoryInterface|MockObject */
    protected $recipientRepo;

    /** @var FlashBagInterface|MockObject */
    protected $flashBag;

    /** @var EditRegistration */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->task = new EditRegistration($this->recipientRepo, $this->flashBag);
    }

    /**
     * @test
     */
    public function saves_recipient()
    {
        $recipient = new Recipient('uuid', new EmailAddress('webfactory@example.com', null));
        $this->recipientRepo->expects($this->once())->method('save')->with($recipient);

        $this->task->editRegistration($recipient);
    }

    /**
     * @test
     */
    public function writes_success_flash()
    {
        $recipient = new Recipient('uuid', new EmailAddress('webfactory@example.com', null));
        $this->flashBag->expects($this->once())->method('add');

        $this->task->editRegistration($recipient);
    }
}
