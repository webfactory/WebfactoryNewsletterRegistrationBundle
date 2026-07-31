<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class RecipientFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array
    {
        return [
            'uuid' => self::faker()->uuid(),
            'emailAddress' => new EmailAddress(self::faker()->email(), null),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): Recipient {
            return new Recipient($attributes['uuid'], $attributes['emailAddress']);
        });
    }

    public static function class(): string
    {
        return Recipient::class;
    }
}
