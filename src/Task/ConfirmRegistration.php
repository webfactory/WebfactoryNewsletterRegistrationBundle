<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;

class ConfirmRegistration implements ConfirmRegistrationInterface
{
    /** @var string */
    protected $secret;

    /** @var RecipientFactoryInterface */
    protected $recipientFactory;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepo;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepo;

    /** @var FlashBagInterface */
    protected $flashBag;

    public function __construct(
        string $secret,
        RecipientFactoryInterface $recipientFactory,
        RecipientRepositoryInterface $recipientRepo,
        PendingOptInRepositoryInterface $pendingOptInRepo,
        FlashBagInterface $flashBag
    ) {
        $this->secret = $secret;
        $this->recipientFactory = $recipientFactory;
        $this->recipientRepo = $recipientRepo;
        $this->pendingOptInRepo = $pendingOptInRepo;
        $this->flashBag = $flashBag;
    }

    public function confirmRegistration(PendingOptInInterface $pendingOptIn, string $emailAddress): RecipientInterface
    {
        if (false === $pendingOptIn->emailAddressMatchesHash($emailAddress, $this->secret)) {
            throw new EmailAddressDoesNotMatchHashOfPendingOptInException($emailAddress, $pendingOptIn);
        }

        $recipient = $this->recipientFactory->fromPendingOptIn($pendingOptIn, $emailAddress);
        $this->recipientRepo->save($recipient);
        $this->pendingOptInRepo->remove($pendingOptIn);

        $this->flashBag->add('success', 'Your newsletter registration is now active.');

        return $recipient;
    }
}
