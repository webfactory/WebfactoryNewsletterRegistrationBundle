<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;

class Task implements TaskInterface
{
    /** @var PendingOptInRepositoryInterface */
    protected $repository;

    /** @var int */
    protected $timeLimitForOptInInHours;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        PendingOptInRepositoryInterface $pendingOptInRepository,
        int $timeLimitForOptInInHours,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $pendingOptInRepository;
        $this->timeLimitForOptInInHours = $timeLimitForOptInInHours;
        $this->logger = $logger ?: new NullLogger();
    }

    public function deleteOutdatedPendingOptIns(?\DateTime $now = null): void
    {
        $this->logger->info('Starting '.static::class);

        $now = $now ?: new \DateTime();
        $tresholdDate = $now->sub(new \DateInterval('PT'.$this->timeLimitForOptInInHours.'H'));
        $numberOfDeletedOutdatedPendingOptIns = $this->repository->removeOutdated($tresholdDate);
        $this->logger->info(
            'Deleted [numberOfDeletedOutdatedPendingOptIns] outdated PendingOpIns',
            ['numberOfDeletedOutdatedPendingOptIns' => $numberOfDeletedOutdatedPendingOptIns]
        );

        $this->logger->info('Finished '.static::class);
    }
}
