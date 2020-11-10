<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\StartRegistration;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\SendLinkTask;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;

class SendLinkTaskTest extends TestCase
{
    protected const SENDER = 'sender@example.com';

    /** @var MailerInterface|MockObject */
    protected $mailer;

    /** @var Environment|MockObject */
    protected $twig;

    /** @var UrlGeneratorInterface|MockObject */
    protected $urlGenerator;

    /** @var SendLinkTask */
    private $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->task = new SendLinkTask($this->mailer, self::SENDER, $this->twig, $this->urlGenerator);
    }

    /**
     * @test
     */
    public function sends_edit_registration_email()
    {
        $recipient = new Recipient(null, new EmailAddress('receiver@example.com', null));

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
                        && $email->getTo()[0]->getAddress() === (string) $recipient->getEmailAddress()
                        && $email->getTextBody() === $renderResult
                        && $email->getTextBody() === $renderResult;
                }
            ));

        $this->task->sendEditRegistrationLink($recipient);
    }
}
