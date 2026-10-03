<?php

/**
 * Module Bootstrap File
 * 
 * <!-- AI-Generated Content Start -->
 * This file is automatically loaded by OpenEMR's module system during application
 * startup. It registers the module's namespace with the class loader and initializes
 * the bootstrap class which sets up event listeners and module hooks.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth;

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */

// Register module namespace - AI-Generated
$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\GcipAuth\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');

/**
 * @global EventDispatcher $eventDispatcher Injected by the OpenEMR module loader;
 */

// Initialize module bootstrap - AI-Generated
$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();