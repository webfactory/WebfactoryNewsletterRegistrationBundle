<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteRegistration;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class Task implements TaskInterface
{
    protected RecipientRepositoryInterface $recipientRepo;

    public function __construct(RecipientRepositoryInterface $recipientRepo)
    {
        $this->recipientRepo = $recipientRepo;
    }

    public function deleteRegistration(RecipientInterface $recipient): void
    {
        $this->recipientRepo->remove($recipient);
    }
}
