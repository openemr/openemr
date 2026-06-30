<?php

/**
 * FQHC module bootstrap entry point.
 *
 * Registers the module's PSR-4 namespace with the runtime module class loader
 * and wires its event subscribers. Loaded by OpenEMR's module manager — does
 * not touch any certified code path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\Fqhc\Bootstrap;

$fileRoot = OEGlobalsBag::getInstance()->get('fileroot');
$classLoader = new ModulesClassLoader($fileRoot);
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\Fqhc\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);

$eventDispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
