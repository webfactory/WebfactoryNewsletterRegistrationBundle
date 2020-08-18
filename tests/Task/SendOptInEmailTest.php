<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Task;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Task\SendOptInEmail;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class SendOptInEmailTest extends TestCase
{
    protected const SENDER = 'sender@example.com';

    /** @var RecipientRepositoryInterface|MockObject */
    protected $recipientRepo;

    /** @var MailerInterface|MockObject */
    protected $mailer;

    /** @var Environment|MockObject */
    protected $twig;

    /** @var UrlGeneratorInterface|MockObject */
    protected $urlGenerator;

    /** @var SendOptInEmail */
    private $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recipientRepo = $this->createMock(RecipientRepositoryInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->task = new SendOptInEmail(
            $this->recipientRepo,
            $this->mailer,
            self::SENDER,
            $this->twig,
            $this->urlGenerator
        );
    }

    /**
     * @test
     */
    public function sends_opt_in_email()
    {
        $recipient = new Recipient(null, 'receiver@example.com');

        $renderResult = 'render-result';
        $this->twig
            ->method('render')
            ->willReturn($renderResult);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                function (Email $email) use ($recipient, $renderResult) {
                    return self::SENDER === $email->getFrom()[0]->getAddress()
                        && $email->getTo()[0]->getAddress() === $recipient->getEmailAddress()
                        && $email->getTextBody() === $renderResult
                        && $email->getTextBody() === $renderResult;
                }
            ));

        $this->task->sendOptInEmail($recipient);
    }
}
