<?php
/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
//
require_once("./libs/controller/oeFaxSMSClient.php");

// kick off app endpoints controller
$clientApp = new oeFaxSMSClient();

echo "<script>var pid='" . js_escape($pid) . "'</script>";
