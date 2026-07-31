<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use DateTimeImmutable;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class BlockedEmailAddressHashFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'hash' => self::faker()->sha1(),
            'blockDate' => new DateTimeImmutable(),
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): BlockedEmailAddressHash {
            return new BlockedEmailAddressHash($attributes['hash'], $attributes['blockDate']);
        });
    }

    public static function class(): string
    {
        return BlockedEmailAddressHash::class;
    }
}
