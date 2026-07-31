<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use DateTimeImmutable;
use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class PendingOptInFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array
    {
        return [
            'uuid' => self::faker()->uuid(),
            'emailAddress' => new EmailAddress(self::faker()->email(), 'secret'),
            'registrationDate' => new DateTimeImmutable(),
        ];
    }

    protected function initialize(): static
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

    public static function class(): string
    {
        return PendingOptIn::class;
    }
}
