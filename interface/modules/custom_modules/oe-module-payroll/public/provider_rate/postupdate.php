<?php

/*
 *  package   OpenEMR
 *  link      http://www.open-emr.org
 *  author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace Juggernaut\Modules\Payroll;

require_once dirname(__FILE__, 6) . "/globals.php";

if (!empty($_GET['userid'])) {
    $rate = new ProviderRates();
    $id = filter_input(INPUT_GET, 'userid', FILTER_VALIDATE_INT);
    $percent = filter_input(INPUT_GET, 'percent', FILTER_VALIDATE_FLOAT);
    $flat = filter_input(INPUT_GET, 'flat', FILTER_VALIDATE_FLOAT);
    $update = $rate->savePayrollData($id, $percent, $flat);
    echo $update;
} else {
    echo xlt("empty!!");
}
