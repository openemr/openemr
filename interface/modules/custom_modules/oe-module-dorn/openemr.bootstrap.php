<?php

 /**
  *
  * @package OpenEMR
  * @link    http://www.open-emr.org
  *
  * @author    Brad Sharp <brad.sharp@claimrev.com>
<<<<<<< HEAD
  * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
=======
  * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
>>>>>>> d11e3347b (modules setup and UI changes)
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

namespace OpenEMR\Modules\Dorn;

<<<<<<< HEAD
/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\Dorn\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

=======
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\Dorn\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');
>>>>>>> d11e3347b (modules setup and UI changes)
/**
 * @global EventDispatcher $eventDispatcher Injected by the OpenEMR module loader;
 */

$bootstrap = new Bootstrap($eventDispatcher, $GLOBALS['kernel']);
$bootstrap->subscribeToEvents();
