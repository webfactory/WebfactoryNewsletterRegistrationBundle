<?php

namespace Webfactory\NewsletterRegistrationBundle\ConfirmRegistration;

use DateTimeImmutable;
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
    protected PendingOptInRepositoryInterface $pendingOptInRepo;
    protected int $timeLimitForOptInInHours;
    protected EmailAddressFactoryInterface $emailAddressFactory;
    protected RecipientFactoryInterface $recipientFactory;
    protected RecipientRepositoryInterface $recipientRepo;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepo,
        int $timeLimitForOptInInHours,
        EmailAddressFactoryInterface $emailAddressFactory,
        RecipientFactoryInterface $recipientFactory,
        RecipientRepositoryInterface $recipientRepo
    ) {
        $this->pendingOptInRepo = $pendingOptInRepo;
        $this->timeLimitForOptInInHours = $timeLimitForOptInInHours;
        $this->emailAddressFactory = $emailAddressFactory;
        $this->recipientFactory = $recipientFactory;
        $this->recipientRepo = $recipientRepo;
    }

    /**
     * @throws EmailAddressDoesNotMatchHashOfPendingOptInException
     * @throws PendingOptInIsOutdatedException
     */
    public function confirmRegistration(
        PendingOptInInterface $pendingOptIn,
        string $emailAddressString
    ): RecipientInterface {
        $thresholdDate = new DateTimeImmutable('-'.$this->timeLimitForOptInInHours.' hour');
        if ($pendingOptIn->isOutdated($thresholdDate)) {
            throw new PendingOptInIsOutdatedException($pendingOptIn);
        }

        $emailAddress = $this->emailAddressFactory->fromString($emailAddressString);
        $pendingOptIn->setEmailAddressIfItMatchesStoredHash($emailAddress);

        $recipient = $this->recipientFactory->fromPendingOptIn($pendingOptIn);
        $this->recipientRepo->save($recipient);
        $this->pendingOptInRepo->remove($pendingOptIn);

        return $recipient;
    }

    public function getTimeLimitForOptInInHours(): int
    {
        return $this->timeLimitForOptInInHours;
    }
}
