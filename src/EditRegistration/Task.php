<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class Task implements TaskInterface
{
    /** @var RecipientRepositoryInterface */
    protected $recipientRepo;

    /** @var FlashBagInterface */
    protected $flashBag;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        RecipientRepositoryInterface $recipientRepo,
        FlashBagInterface $flashBag,
        TranslatorInterface $translator
    ) {
        $this->recipientRepo = $recipientRepo;
        $this->flashBag = $flashBag;
        $this->translator = $translator;
    }

    public function editRegistration(RecipientInterface $recipient): void
    {
        $this->recipientRepo->save($recipient);

        $messageKey = \count($recipient->getNewsletters()) > 0
            ? 'edit.registration.updated'
            : 'edit.registration.updated.no.newsletters.chosen';
        $this->flashBag->add(
            'success',
            $this->translator->trans($messageKey, [], 'webfactory-newsletter-registration')
        );
    }
}
