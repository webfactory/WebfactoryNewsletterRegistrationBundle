<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class DeleteRegistration implements DeleteRegistrationInterface
{
    /** @var RecipientRepositoryInterface */
    protected $recipientRepo;

    /** @var FlashBagInterface */
    protected $flashBag;

    public function __construct(
        RecipientRepositoryInterface $recipientRepo,
        FlashBagInterface $flashBag
    ) {
        $this->recipientRepo = $recipientRepo;
        $this->flashBag = $flashBag;
    }

    public function deleteRegistration(RecipientInterface $recipient): void
    {
        $this->recipientRepo->remove($recipient);

        $this->flashBag->add(
            'success',
            'You are unsubscribed from all newsletters and your registration data has been deleted.'
        );
    }
}
