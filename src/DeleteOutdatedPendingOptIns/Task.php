<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns;

use DateInterval;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;

class Task implements TaskInterface
{
    protected PendingOptInRepositoryInterface $repository;
    protected int $timeLimitForOptInInHours;
    protected LoggerInterface $logger;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepository,
        int $timeLimitForOptInInHours,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $pendingOptInRepository;
        $this->timeLimitForOptInInHours = $timeLimitForOptInInHours;
        $this->logger = $logger ?: new NullLogger();
    }

    public function deleteOutdatedPendingOptIns(?DateTimeImmutable $now = null): void
    {
        $this->logger->info('Starting '.static::class);

        $now = $now ?: new DateTimeImmutable();
        $thresholdDate = $now->sub(new DateInterval('PT'.$this->timeLimitForOptInInHours.'H'));
        $numberOfDeletedOutdatedPendingOptIns = $this->repository->removeOutdated($thresholdDate);
        $this->logger->info(
            'Deleted [numberOfDeletedOutdatedPendingOptIns] outdated PendingOpIns',
            ['numberOfDeletedOutdatedPendingOptIns' => $numberOfDeletedOutdatedPendingOptIns]
        );

        $this->logger->info('Finished '.static::class);
    }
}
