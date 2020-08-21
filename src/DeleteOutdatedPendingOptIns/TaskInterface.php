<?php

namespace Webfactory\NewsletterRegistrationBundle\DeleteOutdatedPendingOptIns;

interface TaskInterface
{
    public function deleteOutdatedPendingOptIns(?\DateTimeImmutable $now = null): void;
}
