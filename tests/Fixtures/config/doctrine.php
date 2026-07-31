<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Webfactory\NewsletterRegistrationBundle\Entity\NewsletterInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Newsletter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'driver' => 'pdo_sqlite',
            'path' => '%kernel.cache_dir%/test.db',
        ],
        'orm' => [
            'resolve_target_entities' => [
                NewsletterInterface::class => Newsletter::class,
            ],
            'mappings' => [
                'BundleEntities' => [
                    'is_bundle' => false,
                    'type' => 'attribute',
                    'dir' => '%kernel.project_dir%/src/Entity',
                    'prefix' => 'Webfactory\\NewsletterRegistrationBundle\\Entity',
                    'alias' => 'Bundle',
                ],
                'TestEntities' => [
                    'is_bundle' => false,
                    'type' => 'attribute',
                    'dir' => '%kernel.project_dir%/tests/Entity/Dummy',
                    'prefix' => 'Webfactory\\NewsletterRegistrationBundle\\Tests\\Entity\\Dummy',
                    'alias' => 'Test',
                ],
            ],
        ],
    ]);
};
