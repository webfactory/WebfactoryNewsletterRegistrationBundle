<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedBlockedEmailAddresses;

use DateInterval;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHashRepositoryInterface;

class Task implements TaskInterface
{
    /** @var BlockedEmailAddressHashRepositoryInterface */
    protected $repository;

    /** @var int */
    protected $blockEmailAddressDurationInDays;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        BlockedEmailAddressHashRepositoryInterface $pendingOptInRepository,
        int $blockEmailAddressDurationInDays,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $pendingOptInRepository;
        $this->blockEmailAddressDurationInDays = $blockEmailAddressDurationInDays;
        $this->logger = $logger ?: new NullLogger();
    }

    public function deleteOutdatedBlockedEmailAddresses(?DateTimeImmutable $now = null): void
    {
        $this->logger->info('Starting '.static::class);

        $now = $now ?: new DateTimeImmutable();
        $thresholdDate = $now->sub(new DateInterval('P'.$this->blockEmailAddressDurationInDays.'D'));
        $numberOfDeletedOutdatedBlockedEmailAddresses = $this->repository->removeOutdated($thresholdDate);
        $this->logger->info(
            'Deleted [numberOfDeletedOutdatedBlockedEmailAddresses] outdated BlockedEmailAddressHashes',
            ['numberOfDeletedOutdatedBlockedEmailAddresses' => $numberOfDeletedOutdatedBlockedEmailAddresses]
        );

        $this->logger->info('Finished '.static::class);
    }
}
