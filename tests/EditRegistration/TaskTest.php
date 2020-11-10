<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\EditRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\Task;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class TaskTest extends TestCase
{
    /** @var RecipientRepositoryInterface|MockObject */
    protected $recipientRepo;

    /** @var FlashBagInterface|MockObject */
    protected $flashBag;

    /** @var Task */
    protected $task;

    /** @var TranslatorInterface|MockObject */
    protected $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->task = new Task($this->recipientRepo, $this->flashBag, $this->translator);
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
