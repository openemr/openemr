<?php

/**
 * Config Module.
 * Call the module setup page if present.
 * Included in all modules and called by Module Manager.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-24 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\ModulesClassLoader;

require_once dirname(__FILE__, 4) . '/globals.php';

/* required for config before install */
$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\ClaimRevConnector\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');

$module_config = 1;

exit;
