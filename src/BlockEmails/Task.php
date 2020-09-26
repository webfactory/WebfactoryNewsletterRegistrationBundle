<?php

namespace Webfactory\NewsletterRegistrationBundle\BlockEmails;

use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;

class Task implements TaskInterface
{
    /** @var int */
    protected $blockDurationInDays;

    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    /** @var BlockedEmailAddressHashRepositoryInterface */
    protected $blockedEmailHashesRepository;

    /** @var PendingOptInRepositoryInterface */
    protected $pendingOptInRepository;

    public function __construct(
        int $blockDurationInDays,
        EmailAddressFactoryInterface $emailAddressFactory,
        BlockedEmailAddressHashRepositoryInterface $blockedEmailHashesRepository,
        PendingOptInRepositoryInterface $pendingOptInRepository
    ) {
        $this->blockDurationInDays = $blockDurationInDays;
        $this->emailAddressFactory = $emailAddressFactory;
        $this->blockedEmailHashesRepository = $blockedEmailHashesRepository;
        $this->pendingOptInRepository = $pendingOptInRepository;
    }

    /**
     * @param PendingOptInInterface $pendingOptIn
     * @param string                $emailAddressString
     *
     * @throws EmailAddressDoesNotMatchHashOfPendingOptInException
     */
    public function blockEmailsFor(PendingOptInInterface $pendingOptIn, string $emailAddressString): void
    {
        $emailAddress = $this->emailAddressFactory->fromString($emailAddressString);
        $pendingOptIn->setEmailAddressIfItMatchesStoredHash($emailAddress);

        // renew an older block for the block duration
        $block = $this->blockedEmailHashesRepository->findByEmailAddress($pendingOptIn->getEmailAddress());
        if ($block) {
            $this->blockedEmailHashesRepository->remove($block);
        }

        $this->blockedEmailHashesRepository->save(BlockedEmailAddressHash::fromEmailAddress($emailAddress));
        $this->pendingOptInRepository->remove($pendingOptIn);
    }

    public function getBlockDurationInDays(): int
    {
        return $this->blockDurationInDays;
    }

    public function getBlockedEmailAddressHash(string $emailAddress): ?BlockedEmailAddressHashInterface
    {
        return $this->blockedEmailHashesRepository->findByEmailAddress(
            $this->emailAddressFactory->fromString($emailAddress)
        );
    }
}
