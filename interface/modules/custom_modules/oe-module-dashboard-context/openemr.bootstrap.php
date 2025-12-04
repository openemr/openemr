<?php

/**
 * Dashboard Context Manager Module Bootstrap
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */

use OpenEMR\Modules\DashboardContext\Bootstrap;

$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\DashboardContext\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

/**
 * @global EventDispatcher $eventDispatcher Injected by the OpenEMR module loader;
 */

$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
