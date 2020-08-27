<?php

namespace Webfactory\NewsletterRegistrationBundle\StartRegistration;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Webfactory\NewsletterRegistrationBundle\EditRegistration\SendLinkTaskInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class HandleRegistrationSubmissionTask implements HandleRegistrationSubmissionTaskInterface
{
    /** @var RecipientRepositoryInterface */
    private $recipientRepository;

    /** @var Environment */
    private $twig;

    /** @var TaskInterface */
    private $startRegistrationTask;

    /** @var SendLinkTaskInterface */
    private $sendLinkTask;

    public function __construct(
        RecipientRepositoryInterface $recipientRepository,
        Environment $twig,
        TaskInterface $startRegistrationTask,
        SendLinkTaskInterface $sendLinkTask
    ) {
        $this->recipientRepository = $recipientRepository;
        $this->twig = $twig;
        $this->startRegistrationTask = $startRegistrationTask;
        $this->sendLinkTask = $sendLinkTask;
    }

    public function handleRegistrationSubmission(PendingOptInInterface $pendingOptIn): Response
    {
        $recipient = $this->recipientRepository->findByEmailAddress($pendingOptIn->getEmailAddress());
        if ($recipient) {
            $this->sendLinkTask->sendEditRegistrationLink($recipient);

            return new Response(
                $this->twig->render(
                    '@WebfactoryNewsletterRegistration/EditRegistration/link-email-sent.html.twig',
                    [
                        'pendingOptIn' => $pendingOptIn,
                    ]
                )
            );
        }

        $optInEmail = $this->startRegistrationTask->startRegistration($pendingOptIn);

        // Usually, we would send a redirect here to prevent double posts. But we want to provide personal data
        // to the upcoming view - personal data that we do not want to save before the user confirmed their
        // registration. Hence, the downsides of double posts are dealt with in the form itself.
        return new Response(
            $this->twig->render(
                '@WebfactoryNewsletterRegistration/StartRegistration/opt-in-email-sent.html.twig',
                [
                    'pendingOptIn' => $pendingOptIn,
                    'optInEmail' => $optInEmail,
                ]
            )
        );
    }
}
