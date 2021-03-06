<?php

namespace Webfactory\NewsletterRegistrationBundle\Entity;

use DateInterval;
use DateTimeImmutable;

interface PendingOptInInterface
{
    public static function fromRegistrationFormData(array $formData): ?self;

    public function getUuid(): string;

    public function getEmailAddress(): EmailAddress;

    public function setEmailAddressIfItMatchesStoredHash(EmailAddress $email): void;

    /**
     * @return NewsletterInterface[]
     */
    public function getNewsletters(): array;

    public function getRegistrationDate(): DateTimeImmutable;

    public function isOutdated(DateTimeImmutable $threshold): bool;

    public function isAllowedToReceiveAnotherOptInEmail(
        DateInterval $minimalIntervalBetweenOptInEmailsInHours,
        ?DateTimeImmutable $now = null
    ): bool;
}
