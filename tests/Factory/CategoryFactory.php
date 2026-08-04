<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Factory;

use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

final class CategoryFactory extends PersistentObjectFactory
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
        return $this->instantiateWith(function (array $attributes): Category {
            return new Category(null, $attributes['name'], $attributes['rank'], $attributes['visible']);
        });
    }

    public static function class(): string
    {
        return Category::class;
    }
}
