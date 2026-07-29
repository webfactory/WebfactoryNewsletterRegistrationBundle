<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;
use Zenstruck\Foundry\ModelFactory;

final class NewsletterFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'rank' => 0,
            'visible' => true,
        ];
    }

    protected function initialize(): self
    {
        return $this->instantiateWith(function (array $attributes): Newsletter {
            return new Newsletter(null, $attributes['name'], $attributes['rank'], $attributes['visible']);
        });
    }

    protected static function getClass(): string
    {
        return Newsletter::class;
    }
}
