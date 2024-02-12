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

require_once dirname(__FILE__, 4) . '/globals.php';

$module_config = 1;
?>

<iframe src="templates/weno_setup.php?module_config=1" style="border:none;height:100vh;width:100%;"></iframe>