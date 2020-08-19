<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;

class StartRegistration implements StartRegistrationInterface
{
    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepo;

    /** @var MailerInterface */
    protected $mailer;

    /** @var string */
    protected $senderEmailAddress;

    /** @var Environment */
    protected $twig;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepo,
        MailerInterface $swiftMailer,
        string $senderEmailAddress,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->pendingOptInRepo = $pendingOptInRepo;
        $this->mailer = $swiftMailer;
        $this->senderEmailAddress = $senderEmailAddress;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    public function startRegistration(PendingOptInInterface $pendingOptIn): Email
    {
        $this->pendingOptInRepo->save($pendingOptIn);

        return $this->sendOptInEmail($pendingOptIn);
    }

    protected function sendOptInEmail(PendingOptInInterface $pendingOptIn): Email
    {
        $email = (new Email())
            ->from($this->senderEmailAddress)
            ->to($pendingOptIn->getEmailAddress())
            ->subject($this->renderSubject($pendingOptIn))
            ->text($this->renderBody($pendingOptIn));

        $this->mailer->send($email);

        return $email;
    }

    protected function renderSubject(PendingOptInInterface $pendingOptIn): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/Register/opt-in-email-subject.txt.twig',
                ['pendingOptIn' => $pendingOptIn]
            )
        );
    }

    protected function renderBody(PendingOptInInterface $pendingOptIn): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/Register/opt-in-email-body.txt.twig',
                [
                    'pendingOptIn' => $pendingOptIn,
                    'urlForConfirmation' => $this->urlGenerator->generate(
                        'newsletter-registration-confirm',
                        ['emailAddress' => $pendingOptIn->getEmailAddress(), 'uuid' => $pendingOptIn->getUuid()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'urlForEditing' => $this->urlGenerator->generate(
                        'newsletter-registration-edit',
                        ['uuid' => $pendingOptIn->getUuid()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ]
            )
        );
    }
}
