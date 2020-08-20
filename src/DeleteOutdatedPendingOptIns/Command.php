<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends \Symfony\Component\Console\Command\Command
{
    /** @var TaskInterface */
    protected $task;

    public function __construct(TaskInterface $task)
    {
        parent::__construct('newsletter-registration:delete-outdated-pending-opt-ins');

        $this->task = $task;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->task->deleteOutdatedPendingOptIns();
    }
}
