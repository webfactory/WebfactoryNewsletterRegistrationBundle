<?php

namespace Webfactory\NewsletterRegistrationBundle\ConfirmRegistration;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;
use Webfactory\NewsletterRegistrationBundle\Exception\PendingOptInIsOutdatedException;

class Task implements TaskInterface
{
    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepo;

    /** @var int */
    protected $timeLimitForOptInInHours;

    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    /** @var RecipientFactoryInterface */
    protected $recipientFactory;

    /** @var RecipientRepositoryInterface */
    protected $recipientRepo;

    /** @var FlashBagInterface */
    protected $flashBag;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepo,
        int $timeLimitForOptInInHours,
        EmailAddressFactoryInterface $emailAddressFactory,
        RecipientFactoryInterface $recipientFactory,
        RecipientRepositoryInterface $recipientRepo,
        FlashBagInterface $flashBag
    ) {
        $this->pendingOptInRepo = $pendingOptInRepo;
        $this->timeLimitForOptInInHours = $timeLimitForOptInInHours;
        $this->emailAddressFactory = $emailAddressFactory;
        $this->recipientFactory = $recipientFactory;
        $this->recipientRepo = $recipientRepo;
        $this->flashBag = $flashBag;
    }

    /**
     * @param PendingOptInInterface $pendingOptIn
     * @param string                $emailAddressStringString
     *
     * @return RecipientInterface
     *
     * @throws EmailAddressDoesNotMatchHashOfPendingOptInException
     * @throws PendingOptInIsOutdatedException
     */
    public function confirmRegistration(
        PendingOptInInterface $pendingOptIn,
        string $emailAddressStringString
    ): RecipientInterface {
        $thresholdDate = new \DateTime('-'.$this->timeLimitForOptInInHours.' hour');
        if ($pendingOptIn->isOutdated($thresholdDate)) {
            throw new PendingOptInIsOutdatedException($pendingOptIn);
        }

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
