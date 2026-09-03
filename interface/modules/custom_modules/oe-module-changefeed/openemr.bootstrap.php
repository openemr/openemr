<?php

/**
 * Change Feed module bootstrap.
 *
 * Registers the module PSR-4 namespace and wires the Bootstrap class into the
 * OpenEMR event dispatcher so the change-feed REST route is added to the API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\ChangeFeed\Bootstrap;

$file = OEGlobalsBag::getInstance()->getProjectDir();
$classLoader = new ModulesClassLoader($file);
$classLoader->registerNamespaceIfNotExists(
    'OpenEMR\\Modules\\ChangeFeed\\',
    __DIR__ . DIRECTORY_SEPARATOR . 'src'
);

$eventDispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
