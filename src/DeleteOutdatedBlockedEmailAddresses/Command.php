<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedBlockedEmailAddresses;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends \Symfony\Component\Console\Command\Command
{
    protected TaskInterface $task;

    public function __construct(TaskInterface $task)
    {
        parent::__construct('newsletter-registration:delete-outdated-blocked-email-addresses');

        $this->task = $task;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->task->deleteOutdatedBlockedEmailAddresses();

        return self::SUCCESS;
    }
}
