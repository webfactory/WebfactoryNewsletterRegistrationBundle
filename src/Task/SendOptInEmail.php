<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class SendOptInEmail implements SendOptInEmailInterface
{
    /** @var RecipientRepositoryInterface */
    protected $recipientRepo;

    /** @var MailerInterface */
    protected $mailer;

    /** @var string */
    protected $senderEmailAddress;

    /** @var Environment */
    protected $twig;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    public function __construct(
        RecipientRepositoryInterface $recipientRepo,
        MailerInterface $swiftMailer,
        string $senderEmailAddress,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->recipientRepo = $recipientRepo;
        $this->mailer = $swiftMailer;
        $this->senderEmailAddress = $senderEmailAddress;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function sendOptInEmail(RecipientInterface $recipient): Email
    {
        $email = (new Email())
            ->from($this->senderEmailAddress)
            ->to($recipient->getEmailAddress())
            ->subject($this->renderSubject($recipient))
            ->text($this->renderBody($recipient));

        $this->mailer->send($email);

        return $email;
    }

    protected function renderSubject(RecipientInterface $recipient): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/Register/opt-in-email-subject.txt.twig',
                ['recipient' => $recipient]
            )
        );
    }

    protected function renderBody(RecipientInterface $recipient): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/Register/opt-in-email-body.txt.twig',
                [
                    'recipient' => $recipient,
                    'urlForActivation' => $this->urlGenerator->generate('newsletter-registration-activate'),
                    'urlForEditing' => $this->urlGenerator->generate('newsletter-registration-edit'),
                ]
            )
        );
    }
}
