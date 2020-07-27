<?php

/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c ) 2020.. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../../interface/globals.php";

use OpenEMR\Rx\OePrescriptions\OePrescriptions;

$fetch = new OePrescriptions();
$pid = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT);

print $fetch->listPrescriptions($pid);
