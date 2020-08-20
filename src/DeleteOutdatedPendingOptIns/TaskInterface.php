<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns;

interface TaskInterface
{
    public function deleteOutdatedPendingOptIns(?\DateTime $now = null): void;
}
