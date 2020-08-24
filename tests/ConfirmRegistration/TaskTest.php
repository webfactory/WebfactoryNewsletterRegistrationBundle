<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\ConfirmRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
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

    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    /** @var RecipientFactoryInterface|MockObject */
    protected $recipientFactory;

    /** @var RecipientRepositoryInterface|MockObject */
    protected $recipientRepo;

    /** @var PendingOptInRepositoryInterface|MockObject */
    protected $pendingOptInRepo;

    /** @var FlashBagInterface|MockObject */
    protected $flashBag;

    /** @var TranslatorInterface|MockObject */
    protected $translator;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailAddressFactory = new EmailAddressFactory('secret');
        $this->recipientFactory = $this->createMock(RecipientFactoryInterface::class);
        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->pendingOptInRepo = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->task = new Task(
            $this->pendingOptInRepo,
            self::TIME_LIMIT_FOR_OPT_IN_IN_HOURS,
            $this->emailAddressFactory,
            $this->recipientFactory,
            $this->recipientRepo,
            $this->flashBag,
            $this->translator
        );
    }

    /**
     * @test
     */
    public function throws_exception_if_PendingOptIn_is_outdated()
    {
        $pendingOptIn = new PendingOptIn(
            'uuid',
            $this->emailAddressFactory->fromString('webfactory@example.com'),
            [],
            new \DateTimeImmutable('2000-01-01')
        );
        $this->expectException(PendingOptInIsOutdatedException::class);

        $this->task->confirmRegistration($pendingOptIn, 'other@example.com');
    }

    /**
     * @test
     */
    public function throws_exception_if_email_address_does_not_match_hash()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->expectException(EmailAddressDoesNotMatchHashOfPendingOptInException::class);

        $this->task->confirmRegistration($pendingOptIn, 'other@example.com');
    }

    /**
     * @test
     */
    public function saves_recipient()
    {
        $this->recipientRepo->expects($this->once())->method('save');
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }

    /**
     * @test
     */
    public function removes_pending_opt_in()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->pendingOptInRepo->expects($this->once())->method('remove')->with($pendingOptIn);

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }

    /**
     * @test
     */
    public function writes_success_flash()
    {
        $pendingOptIn = new PendingOptIn('uuid', $this->emailAddressFactory->fromString('webfactory@example.com'));
        $this->flashBag->expects($this->once())->method('add');

        $this->task->confirmRegistration($pendingOptIn, 'webfactory@example.com');
    }
}
