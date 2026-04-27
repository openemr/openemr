<?php

/**
 * GCIP Auth Module bootstrap — entry point loaded by OpenEMR's module system.
 *
 * Registers the module's PSR-4 namespace and subscribes to events when
 * the module is enabled. The $classLoader and $eventDispatcher variables
 * are injected by ModulesApplication::loadCustomModule().
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\GcipAuth\Bootstrap;

/** @var ModulesClassLoader $classLoader */
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\GcipAuth\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src',
);

$eventDispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
