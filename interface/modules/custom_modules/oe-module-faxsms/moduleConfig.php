<?php

/**
 * Config Module.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");
$module_config = 1;
?>

<div id="set-services">
    <h3 class="text-center"><?php echo xlt("Select Services"); ?></h3>
    <iframe src="library/setup_services.php?module_config=1" style="border:none;height:100vh;width:100%;"></iframe>
</div>
