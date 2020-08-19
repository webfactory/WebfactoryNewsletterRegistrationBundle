<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class EditRegistration implements EditRegistrationInterface
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

    public function editRegistration(RecipientInterface $recipient): void
    {
        $this->recipientRepo->save($recipient);

        if (\count($recipient->getNewsletters()) > 0) {
            $message = 'Your newsletter registration was updated.';
        } else {
            $message = 'All your newsletter subscriptions have been deleted, but your registration data (like your '
                .'email address) is still saved in our database. If you would like to delete that data too, please '
                .'delete your registration with the button below.';
        }
        $this->flashBag->add('success', $message);
    }
}
