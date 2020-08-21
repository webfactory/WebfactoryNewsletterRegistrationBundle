<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;

class Task implements TaskInterface
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

    /** @var int */
    protected $timeLimitForOpInInHours;

    /** @var int */
    protected $blockEmailsDurationInDays;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepo,
        MailerInterface $swiftMailer,
        string $senderEmailAddress,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        int $timeLimitForOpInInHours,
        int $blockEmailsDurationInDays
    ) {
        $this->pendingOptInRepo = $pendingOptInRepo;
        $this->mailer = $swiftMailer;
        $this->senderEmailAddress = $senderEmailAddress;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->timeLimitForOpInInHours = $timeLimitForOpInInHours;
        $this->blockEmailsDurationInDays = $blockEmailsDurationInDays;
    }

    public function startRegistration(PendingOptInInterface $pendingOptIn): Email
    {
        $olderPendingOptIn = $this->pendingOptInRepo->findByEmailAddress($pendingOptIn->getEmailAddress());
        if ($olderPendingOptIn) {
            $this->pendingOptInRepo->remove($olderPendingOptIn);
        }

        $this->pendingOptInRepo->save($pendingOptIn);

        return $this->sendOptInEmail($pendingOptIn);
    }

    protected function sendOptInEmail(PendingOptInInterface $pendingOptIn): Email
    {
        $email = (new Email())
            ->from($this->senderEmailAddress)
            ->to((string) $pendingOptIn->getEmailAddress())
            ->subject($this->renderSubject($pendingOptIn))
            ->text($this->renderBody($pendingOptIn));

        $this->mailer->send($email);

        return $email;
    }

    protected function renderSubject(PendingOptInInterface $pendingOptIn): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/StartRegistration/opt-in-email-subject.txt.twig',
                ['pendingOptIn' => $pendingOptIn]
            )
        );
    }

    protected function renderBody(PendingOptInInterface $pendingOptIn): string
    {
        return trim(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/StartRegistration/opt-in-email-body.txt.twig',
                [
                    'pendingOptIn' => $pendingOptIn,
                    'urlForConfirmation' => $this->urlGenerator->generate(
                        'newsletter-registration-confirm',
                        ['emailAddress' => (string) $pendingOptIn->getEmailAddress(), 'uuid' => $pendingOptIn->getUuid()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'dateUrlForConfirmationIsValidUntil' => new \DateTimeImmutable('+'.$this->timeLimitForOpInInHours.' hour'),
                    'urlForBlockingEmails' => $this->urlGenerator->generate(
                        'newsletter-registration-block-emails',
                        [
                            'uuid' => $pendingOptIn->getUuid(),
                            'emailAddress' => (string) $pendingOptIn->getEmailAddress(),
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'blockEmailsDurationInDays' => $this->blockEmailsDurationInDays,
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
