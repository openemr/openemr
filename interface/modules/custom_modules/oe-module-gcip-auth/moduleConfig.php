<?php

/**
 * Module Configuration Handler
 * 
 * <!-- AI-Generated Content Start -->
 * This file serves as the entry point for module configuration when called
 * by the OpenEMR Module Manager. It sets up the class loader, registers
 * the module namespace, and loads the configuration interface.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\ModulesClassLoader;

require_once dirname(__FILE__, 4) . '/globals.php';

/* Required for config before install - AI-Generated */
$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\GcipAuth\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');

$module_config = 1;
/* Renders in a Laminas created iframe - AI-Generated */
require_once dirname(__FILE__) . '/templates/gcip_setup.php';
exit;