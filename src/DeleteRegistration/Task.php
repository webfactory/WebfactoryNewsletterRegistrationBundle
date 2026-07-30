<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteRegistration;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class Task implements TaskInterface
{
    protected RecipientRepositoryInterface $recipientRepo;
    protected FlashBagInterface $flashBag;
    protected TranslatorInterface $translator;

    public function __construct(
        RecipientRepositoryInterface $recipientRepo,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->recipientRepo = $recipientRepo;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function deleteRegistration(RecipientInterface $recipient): void
    {
        $this->recipientRepo->remove($recipient);

        $this->flashBag->add(
            'success',
            $this->translator->trans('delete.registration.success', [], 'webfactory-newsletter-registration')
        );
    }
}
