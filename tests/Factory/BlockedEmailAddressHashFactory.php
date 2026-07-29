<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use DateTimeImmutable;
use Webfactory\NewsletterRegistrationBundle\Entity\BlockedEmailAddressHash;
use Zenstruck\Foundry\ModelFactory;

final class BlockedEmailAddressHashFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'hash' => self::faker()->sha1(),
            'blockDate' => new DateTimeImmutable(),
        ];
    }

    protected function initialize(): self
    {
        return $this->instantiateWith(function (array $attributes): BlockedEmailAddressHash {
            return new BlockedEmailAddressHash($attributes['hash'], $attributes['blockDate']);
        });
    }

    protected static function getClass(): string
    {
        return BlockedEmailAddressHash::class;
    }
}
