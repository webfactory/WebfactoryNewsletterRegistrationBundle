<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Task;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Task\StartRegistration;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;

class StartRegistrationTest extends TestCase
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

    /** @var StartRegistration */
    private $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pendingOptInRepo = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->task = new StartRegistration(
            $this->pendingOptInRepo,
            $this->mailer,
            self::SENDER,
            $this->twig,
            $this->urlGenerator
        );
    }

    /**
     * @test
     */
    public function saves_PendingOptIn()
    {
        $pendingOptIn = new PendingOptIn(null, 'receiver@example.com', 'secret');

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
        $pendingOptIn = new PendingOptIn(null, 'receiver@example.com', 'secret');

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
                        && $email->getTo()[0]->getAddress() === $pendingOptIn->getEmailAddress()
                        && $email->getTextBody() === $renderResult
                        && $email->getTextBody() === $renderResult;
                }
            ));

        $this->task->startRegistration($pendingOptIn);
    }
}
