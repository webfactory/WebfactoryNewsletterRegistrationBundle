<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->import(__DIR__.'/../../../src/Controller.php', 'annotation');
};
