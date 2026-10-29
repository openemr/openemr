<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Composer\\\\ModuleInstallerPlugin\\\\CustomModuleInstaller does not have a constructor and must be instantiated without any parameters\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/openemr/oe-module-installer-plugin/src/Plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Composer\\\\ModuleInstallerPlugin\\\\ZendModuleInstaller does not have a constructor and must be instantiated without any parameters\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/openemr/oe-module-installer-plugin/src/Plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Class Symfony\\\\Component\\\\DependencyInjection\\\\Loader\\\\Configurator\\\\ExpressionConfigurator does not have a constructor and must be instantiated without any parameters\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/Configurator/ContainerConfigurator.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Composer\\\\ModuleInstallerPlugin\\\\CustomModuleInstaller does not have a constructor and must be instantiated without any parameters\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/openemr/oe-module-installer-plugin/src/Plugin.php',
];
$ignoreErrors[] = [
    'message' => '#^Class OpenEMR\\\\Composer\\\\ModuleInstallerPlugin\\\\ZendModuleInstaller does not have a constructor and must be instantiated without any parameters\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/openemr/oe-module-installer-plugin/src/Plugin.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
