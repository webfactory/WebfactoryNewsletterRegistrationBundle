<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Task;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Task\ConfirmRegistration;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class ConfirmRegistrationTest extends TestCase
{
    protected const SECRET = 'secret';

    /** @var RecipientFactoryInterface|MockObject */
    protected $recipientFactory;

    /** @var RecipientRepositoryInterface|MockObject */
    protected $recipientRepo;

    /** @var PendingOptInRepositoryInterface|MockObject */
    protected $pendingOptInRepo;

    /** @var FlashBagInterface|MockObject */
    protected $flashBag;

    /** @var ConfirmRegistration */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->recipientFactory = $this->createMock(RecipientFactoryInterface::class);
        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->pendingOptInRepo = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->task = new ConfirmRegistration(
            self::SECRET,
            $this->recipientFactory,
            $this->recipientRepo,
            $this->pendingOptInRepo,
            $this->flashBag
        );
    }

    /**
     * @test
     */
    public function throws_exception_if_email_address_does_not_match_hash()
    {
        $pendingOptIn = new PendingOptIn('uuid', 'webfactory@example.com', self::SECRET);
        $this->expectException(EmailAddressDoesNotMatchHashOfPendingOptInException::class);

        $this->task->confirmRegistration($pendingOptIn, 'other@example.com');
    }

    /**
     * @test
     */
    public function saves_recipient()
    {
        $this->recipientRepo->expects($this->once())->method('save');
        $pendingOptIn = new PendingOptIn('uuid', 'webfactory@example.com', self::SECRET);

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }

    /**
     * @test
     */
    public function removes_pending_opt_in()
    {
        $pendingOptIn = new PendingOptIn('uuid', 'webfactory@example.com', self::SECRET);
        $this->pendingOptInRepo->expects($this->once())->method('remove')->with($pendingOptIn);

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }

    /**
     * @test
     */
    public function writes_success_flash()
    {
        $pendingOptIn = new PendingOptIn('uuid', 'webfactory@example.com', self::SECRET);
        $this->flashBag->expects($this->once())->method('add');

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }
}
