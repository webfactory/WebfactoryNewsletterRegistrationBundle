<?php

namespace Webfactory\NewsletterRegistrationBundle\Task;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;

class ConfirmRegistration implements ConfirmRegistrationInterface
{
    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    /** @var RecipientFactoryInterface */
    protected $recipientFactory;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepo;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepo;

    /** @var FlashBagInterface */
    protected $flashBag;

    public function __construct(
        EmailAddressFactoryInterface $emailAddressFactory,
        RecipientFactoryInterface $recipientFactory,
        RecipientRepositoryInterface $recipientRepo,
        PendingOptInRepositoryInterface $pendingOptInRepo,
        FlashBagInterface $flashBag
    ) {
        $this->emailAddressFactory = $emailAddressFactory;
        $this->recipientFactory = $recipientFactory;
        $this->recipientRepo = $recipientRepo;
        $this->pendingOptInRepo = $pendingOptInRepo;
        $this->flashBag = $flashBag;
    }

    public function confirmRegistration(
        PendingOptInInterface $pendingOptIn,
        string $emailAddressStringString
    ): RecipientInterface {
        $emailAddress = $this->emailAddressFactory->fromString($emailAddressStringString);
        if (false === $pendingOptIn->matchesEmailAddress($emailAddress)) {
            throw new EmailAddressDoesNotMatchHashOfPendingOptInException($emailAddress, $pendingOptIn);
        }

        $recipient = $this->recipientFactory->fromPendingOptIn($pendingOptIn, $emailAddress);
        $this->recipientRepo->save($recipient);
        $this->pendingOptInRepo->remove($pendingOptIn);

        $this->flashBag->add('success', 'Your newsletter registration is now active.');

        return $recipient;
    }
}
