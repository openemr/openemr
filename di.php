<?php

declare(strict_types=1);

use Firehed\Container\Builder;
use Firehed\Container\Compiler;
use Psr\Container\ContainerInterface;

// TODO: builder/compiler swap on env (assuming we use _this_ DI tooling)
$builder = new Compiler('vendor/di_container.php');
$files = glob('config/*.php');
if ($files === false) {
    throw new RuntimeException('Could not read config directory');
}
foreach ($files as $file) {
    $builder->addFile($file);
}

// Modules can define their own DI-related config; wire it in to the main
// container rather than doing something wacky like giving each module its own
// DI subsystem.
foreach (getModuleManager()->getEnabledModules() as $moduleInfo) {
    foreach ($moduleInfo->getConfigFiles() as $file) {
        $builder->addFile($file);
    }
}

return $builder->build();
