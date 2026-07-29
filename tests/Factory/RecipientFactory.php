<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use Webfactory\NewsletterRegistrationBundle\Entity\EmailAddress;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;
use Zenstruck\Foundry\ModelFactory;

final class RecipientFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'uuid' => self::faker()->uuid(),
            'emailAddress' => new EmailAddress(self::faker()->email(), null),
        ];
    }

    protected function initialize(): self
    {
        return $this->instantiateWith(function (array $attributes): Recipient {
            return new Recipient($attributes['uuid'], $attributes['emailAddress']);
        });
    }

    protected static function getClass(): string
    {
        return Recipient::class;
    }
}
