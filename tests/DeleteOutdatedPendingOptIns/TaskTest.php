<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\DeleteOutdatedPendingOptIns;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns\Task;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;

class TaskTest extends TestCase
{
    protected const TIME_LIMIT_FOR_OPT_IN_IN_HOURS = 1;

    /** @var PendingOptInRepositoryInterface|MockObject */
    protected $repository;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->task = new Task($this->repository, self::TIME_LIMIT_FOR_OPT_IN_IN_HOURS);
    }

    public function deleteOutdatedPendingOptIns(?\DateTime $now = null): void
    {
        $now = $now ?: new \DateTime();
        $tresholdDate = $now->sub(new \DateInterval('PT'.$this->timeLimitForOptInInHours.'H'));
        $numberOfDeletedOutdatedPendingOptIns = $this->repository->removeOutdated($tresholdDate);
        $this->logger->info(
            'Deleted [numberOfDeletedOutdatedPendingOptIns] outdated PendingOpIns',
            ['numberOfDeletedOutdatedPendingOptIns' => $numberOfDeletedOutdatedPendingOptIns]
        );
    }

    /**
     * @test
     */
    public function delegates_to_repository(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('removeOutdated');

        $this->task->deleteOutdatedPendingOptIns();
    }

    /**
     * @test
     */
    public function sets_treshold_date_from_now_if_called_without_one(): void
    {
        $expected = new \DateTime('-'.self::TIME_LIMIT_FOR_OPT_IN_IN_HOURS.' hour');
        $allowedDeltaInSeconds = 10;

        $this->repository
            ->expects($this->once())
            ->method('removeOutdated')
            ->with($this->callback(
                function (\DateTime $calculdatedThresholdDate) use ($expected, $allowedDeltaInSeconds) {
                    $delta = $calculdatedThresholdDate->diff($expected, true);

                    return 0 === $delta->y
                        && 0 === $delta->m
                        && 0 === $delta->d
                        && 0 === $delta->h
                        && 0 === $delta->i
                        && $delta->s < $allowedDeltaInSeconds;
                }
            ));

        $this->task->deleteOutdatedPendingOptIns();
    }
}
