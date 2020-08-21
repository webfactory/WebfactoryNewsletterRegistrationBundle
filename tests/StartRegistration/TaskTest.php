<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\StartRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\StartRegistration\Task;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class TaskTest extends TestCase
{
    protected const SENDER = 'sender@example.com';

    /** @var PendingOptInRepositoryInterface|MockObject */
    protected $pendingOptInRepo;

    /** @var MailerInterface|MockObject */
    protected $mailer;

    /** @var Environment|MockObject */
    protected $twig;

    /** @var UrlGeneratorInterface|MockObject */
    protected $urlGenerator;

    /** @var Task */
    private $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pendingOptInRepo = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->task = new Task(
            $this->pendingOptInRepo,
            $this->mailer,
            self::SENDER,
            $this->twig,
            $this->urlGenerator,
            1,
            30
        );
    }

    /**
     * @test
     */
    public function saves_PendingOptIn()
    {
        $pendingOptIn = new PendingOptIn(null, new EmailAddress('receiver@example.com', 'secret'));

        $this->pendingOptInRepo
            ->expects($this->once())
            ->method('save')
            ->with($pendingOptIn);

        $this->task->startRegistration($pendingOptIn);
    }

    /**
     * @test
     */
    public function removes_outdated_PendingOptIn_if_it_exists_and_saves_new_one()
    {
        $outdatedPendingOptIn = new PendingOptIn(null, new EmailAddress('webfactory@example.com', 'secret'));
        $this->pendingOptInRepo
            ->method('findByEmailAddress')
            ->willReturn($outdatedPendingOptIn);
        $this->pendingOptInRepo
            ->expects($this->once())
            ->method('remove')
            ->with($outdatedPendingOptIn);

        $pendingOptIn = new PendingOptIn(null, new EmailAddress('receiver@example.com', 'secret'));

        $this->pendingOptInRepo
            ->expects($this->once())
            ->method('save')
            ->with($pendingOptIn);

        $this->task->startRegistration($pendingOptIn);
    }

    /**
     * @test
     */
    public function sends_opt_in_email()
    {
        $pendingOptIn = new PendingOptIn(null, new EmailAddress('receiver@example.com', 'secret'));

        $renderResult = 'render-result';
        $this->twig
            ->method('render')
            ->willReturn($renderResult);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                function (Email $email) use ($pendingOptIn, $renderResult) {
                    return self::SENDER === $email->getFrom()[0]->getAddress()
                        && $email->getTo()[0]->getAddress() === (string) $pendingOptIn->getEmailAddress()
                        && $email->getTextBody() === $renderResult
                        && $email->getTextBody() === $renderResult;
                }
            ));

        $this->task->startRegistration($pendingOptIn);
    }
}
