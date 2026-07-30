<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('twig', [
        'paths' => [
            '%kernel.project_dir%/src/Resources/views' => 'WebfactoryNewsletterRegistration',
        ],
        'form_themes' => ['form_div_layout.html.twig'],
    ]);
};
