<?php

/**
 * SSO Module Bootstrap
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO;

/**
 * @global \OpenEMR\Core\ModulesClassLoader $classLoader
 */
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\SSO\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

/**
 * @global \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
 */
$bootstrap = new Bootstrap($eventDispatcher, $GLOBALS['kernel']);
$bootstrap->subscribeToEvents();
