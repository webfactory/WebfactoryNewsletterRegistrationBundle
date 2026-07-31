<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class Task implements TaskInterface
{
    protected RecipientRepositoryInterface $recipientRepo;
    protected RequestStack $requestStack;
    protected TranslatorInterface $translator;

    public function __construct(
        RecipientRepositoryInterface $recipientRepo,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->recipientRepo = $recipientRepo;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function editRegistration(RecipientInterface $recipient): void
    {
        $this->recipientRepo->save($recipient);

        $messageKey = \count($recipient->getNewsletters()) > 0
            ? 'edit.registration.updated'
            : 'edit.registration.updated.no.newsletters.chosen';
        $session = $this->requestStack->getSession();
        \assert($session instanceof FlashBagAwareSessionInterface);
        $session->getFlashBag()->add(
            'success',
            $this->translator->trans($messageKey, [], 'webfactory-newsletter-registration')
        );
    }
}
