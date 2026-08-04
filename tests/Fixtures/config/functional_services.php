<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Webfactory\NewsletterRegistrationBundle\Entity\CategoryRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\PendingOptInRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientFactoryInterface;
use Webfactory\NewsletterRegistrationBundle\Entity\RecipientRepositoryInterface;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Category;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\PendingOptIn;
use Webfactory\NewsletterRegistrationBundle\Tests\Entity\Dummy\Recipient;
use Webfactory\NewsletterRegistrationBundle\Tests\Fixtures\DummyRecipientFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('webfactory.newsletter_registration.secret', 'test-secret')
        ->set('webfactory.newsletter_registration.email_sender_address', 'newsletter@example.com');

    $containerConfigurator->extension('framework', [
        'router' => [
            'resource' => '%kernel.project_dir%/tests/Fixtures/config/routes.php',
            'utf8' => true,
        ],
        'session' => ['storage_factory_id' => 'session.storage.factory.mock_file'],
        'mailer' => ['dsn' => 'null://null'],
    ]);

    $services = $containerConfigurator->services();

    $services->set(RecipientFactoryInterface::class, DummyRecipientFactory::class);

    $services->set(PendingOptInRepositoryInterface::class)
        ->factory([service('doctrine.orm.entity_manager'), 'getRepository'])
        ->args([PendingOptIn::class]);

    $services->set(RecipientRepositoryInterface::class)
        ->factory([service('doctrine.orm.entity_manager'), 'getRepository'])
        ->args([Recipient::class]);

    $services->set(CategoryRepositoryInterface::class)
        ->factory([service('doctrine.orm.entity_manager'), 'getRepository'])
        ->args([Category::class]);
};
