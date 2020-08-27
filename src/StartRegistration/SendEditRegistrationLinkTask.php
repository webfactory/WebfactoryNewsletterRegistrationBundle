<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;

class SendEditRegistrationLinkTask implements SendEditRegistrationLinkTaskInterface
{
    /** @var MailerInterface */
    protected $mailer;

    /** @var string */
    protected $senderEmailAddress;

    /** @var Environment */
    protected $twig;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    public function __construct(
        MailerInterface $mailer,
        string $senderEmailAddress,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $mailer;
        $this->senderEmailAddress = $senderEmailAddress;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function sendEditRegistrationLink(RecipientInterface $recipient): void
    {
        $email = (new Email())
            ->from($this->senderEmailAddress)
            ->to((string) $recipient->getEmailAddress())
            ->subject($this->renderSubject($recipient))
            ->text($this->renderBody($recipient));

        $this->mailer->send($email);
    }

    protected function renderSubject(RecipientInterface $recipient): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/StartRegistration/edit-registration-email-subject.txt.twig',
                ['recipient' => $recipient]
            )
        );
    }

    protected function renderBody(RecipientInterface $recipient): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/StartRegistration/edit-registration-email-body.txt.twig',
                [
                    'recipient' => $recipient,
                    'urlForEditing' => $this->urlGenerator->generate(
                        'newsletter-registration-edit',
                        ['uuid' => $recipient->getUuid()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ]
            )
        );
    }
}
