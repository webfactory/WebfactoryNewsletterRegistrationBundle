<?php

namespace Webfactory\NewsletterRegistrationBundle\EditRegistration;

use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;

class Task implements TaskInterface
{
    protected RecipientRepositoryInterface $recipientRepo;

    public function __construct(RecipientRepositoryInterface $recipientRepo)
    {
        $this->recipientRepo = $recipientRepo;
    }

    public function editRegistration(RecipientInterface $recipient): void
    {
        $this->recipientRepo->save($recipient);
    }
}
