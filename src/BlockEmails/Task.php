<?php

namespace Webfactory\NewsletterRegistrationBundle\BlockEmails;

use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddressFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInInterface;
use Webfactory\NewsletterRegistrationBundle\Exception\EmailAddressDoesNotMatchHashOfPendingOptInException;

class Task implements TaskInterface
{
    /** @var int */
    protected $blockDurationInDays;

    /** @var EmailAddressFactoryInterface */
    protected $emailAddressFactory;

    /** @var BlockedEmailAddressHashRepositoryInterface */
    protected $blockedEmailHashesRepository;

    public function __construct(
        int $blockDurationInDays,
        EmailAddressFactoryInterface $emailAddressFactory,
        BlockedEmailAddressHashRepositoryInterface $blockedEmailHashesRepository
    ) {
        $this->blockDurationInDays = $blockDurationInDays;
        $this->emailAddressFactory = $emailAddressFactory;
        $this->blockedEmailHashesRepository = $blockedEmailHashesRepository;
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
        if (false === $pendingOptIn->matchesEmailAddress($emailAddress)) {
            throw new EmailAddressDoesNotMatchHashOfPendingOptInException($emailAddress, $pendingOptIn);
        }

        $block = $this->blockedEmailHashesRepository->findByEmailAddress($emailAddress);
        if ($block) {
            $this->blockedEmailHashesRepository->remove($block);
        }

        $this->blockedEmailHashesRepository->save(BlockedEmailAddressHash::fromEmailAddress($emailAddress));
    }

    public function getBlockDurationInDays(): int
    {
        return $this->blockDurationInDays;
    }
}
