<?php

declare(strict_types=1);

use Firehed\Container\AutoDetect;
use Psr\Container\ContainerInterface;
// use Symfony\Component\DependencyInjection\ContainerBuilder;

use OpenEMR\Modules\Manager\ListModulesCommand;
use OpenEMR\Modules\Manager\ModuleFinder;

// $container = new ContainerBuilder();
// $container->setAutoconfigured(true);
// $container->autowire(ListModulesCommand::class, ListModulesCommand::class);
// $container->autowire(ModuleFinder::class, ModuleFinder::class);
// TODO: add defs.
// $container->compile();
// assert($container instanceof ContainerInterface);
// return $container;
putenv('ENVIRONMENT=development');
return AutoDetect::instance('config');
