<?php

 /**
  *
  * @package OpenEMR
  * @link    https://www.open-emr.org
  *
  * @author    Brad Sharp <brad.sharp@claimrev.com>
  * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

/**
 * @var \OpenEMR\Core\ModulesClassLoader $classLoader
 */

$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\ClaimRevConnector\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');
/**
 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher Injected by the OpenEMR module loader;
 */

$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();
