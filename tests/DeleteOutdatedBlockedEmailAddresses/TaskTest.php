<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\DeleteOutdatedBlockedEmailAddresses;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns\Task;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;

class TaskTest extends TestCase
{
    protected const BLOCK_EMAIL_DURATION_IN_DAYS = 30;

    /** @var PendingOptInRepositoryInterface|MockObject */
    protected $repository;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(PendingOptInRepositoryInterface::class);
        $this->task = new Task($this->repository, self::BLOCK_EMAIL_DURATION_IN_DAYS);
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
    public function sets_threshold_date_from_now_if_called_without_one(): void
    {
        $expected = new \DateTimeImmutable('-'.self::BLOCK_EMAIL_DURATION_IN_DAYS.' hour');
        $allowedDeltaInSeconds = 10;

        $this->repository
            ->expects($this->once())
            ->method('removeOutdated')
            ->with($this->callback(
                function (\DateTimeImmutable $calculdatedThresholdDate) use ($expected, $allowedDeltaInSeconds) {
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
