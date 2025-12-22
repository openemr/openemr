<?php

 /**
  *
  * @package OpenEMR
  * @link    http://www.open-emr.org
  *
  * @author    Brad Sharp <brad.sharp@claimrev.com>
  * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

namespace OpenEMR\Modules\Dorn;

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\Dorn\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

/**
 * @global EventDispatcherInterface $eventDispatcher Injected by the OpenEMR module loader;
 */

$bootstrap = new Bootstrap($eventDispatcher, $GLOBALS['kernel']);
$bootstrap->subscribeToEvents();
