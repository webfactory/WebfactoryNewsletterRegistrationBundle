<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns;

use DateTimeImmutable;

interface TaskInterface
{
    public function deleteOutdatedPendingOptIns(?DateTimeImmutable $now = null): void;
}
