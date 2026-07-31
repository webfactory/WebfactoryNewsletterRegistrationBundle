<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class NewsletterFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'rank' => 0,
            'visible' => true,
        ];
    }

    protected function initialize(): static
    {
        return $this->instantiateWith(function (array $attributes): Newsletter {
            return new Newsletter(null, $attributes['name'], $attributes['rank'], $attributes['visible']);
        });
    }

    public static function class(): string
    {
        return Newsletter::class;
    }
}
