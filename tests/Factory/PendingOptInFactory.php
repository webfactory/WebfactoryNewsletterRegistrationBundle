<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use DateTimeImmutable;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;
use Zenstruck\Foundry\ModelFactory;

final class PendingOptInFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'uuid' => self::faker()->uuid(),
            'emailAddress' => new EmailAddress(self::faker()->email(), 'secret'),
            'registrationDate' => new DateTimeImmutable(),
        ];
    }

    protected function initialize(): self
    {
        return $this->instantiateWith(function (array $attributes): PendingOptIn {
            return new PendingOptIn(
                $attributes['uuid'],
                $attributes['emailAddress'],
                [],
                $attributes['registrationDate']
            );
        });
    }

    protected static function getClass(): string
    {
        return PendingOptIn::class;
    }
}
