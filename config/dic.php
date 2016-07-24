<?php

use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder;
$containerBuilder->ignorePhpDocErrors(true);
$containerBuilder->addDefinitions(require(__DIR__ . '/middleware.php'));
$containerBuilder->addDefinitions(require(__DIR__ . '/model.php'));
$containerBuilder->addDefinitions(require(__DIR__ . '/front-routes.php'));
$containerBuilder->addDefinitions(require(__DIR__ . '/endpoint-routes.php'));

return $containerBuilder->build();
