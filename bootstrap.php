<?php

declare(strict_types=1);

// Setup for ALL paths - web, CLI, etc. Do not add code that is sensitive to
// the request context.

chdir(__DIR__);

date_default_timezone_set('UTC');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
// Docker wants logs written to stdout. This may need to vary by SAPI.
ini_set('error_log', '/dev/stdout');
ini_set('log_errors', '1');

error_reporting(E_ALL);
error_reporting(E_ALL & ~E_USER_DEPRECATED);

require_once 'vendor/autoload.php';

// This probably needs to initialize the module manager in such a way that
// enabled modules can be determined before the main DI container is
// initialized. Doing so will allow modules to vend autowirable services into
// the DI system.
//
// Loosely:
// $moduleManager = ... // early uinit
// foreach ($moduleManager->getEnabledModules() as $modileInfo) {
//   foreach ($moduleInfo->getAutowiredClasses() as $fqcn) {
//     $container->autowire($fqcn)
//   }
// }
//
// After this happens, the application should be able to access the
// fully-prepared DI container to do whatever needs doing.
//
// While the manager should be available within the continer, it might be
// necessary to do some early magic.
