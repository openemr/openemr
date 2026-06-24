<?php

/**
 * Plugin management needs its own container in order to avoid circular
 * dependencies in building the primary container. Technically this could be
 * done inline without consequence, but we'd have to reinvent a bunch of
 * tooling for wiring and env handling.
 *
 * This file assumes that the env is configured (e.g. dotenv has already run)
 */

declare(strict_types=1);

use Firehed\Container\AutoDetect;
use OpenEMR\Plugins\PluginManager;

$builder = AutoDetect::getBuilder(compiledOutputPath: 'vendor/compiledModuleContainer.php');
$builder->addDirectory('src/Plugins/config/');
$container = $builder->build();

return $container->get(PluginManager::class);
