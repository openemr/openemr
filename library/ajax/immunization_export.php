<?php

/**
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c)  2022 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once(dirname(__FILE__, 3) . "/interface/globals.php");

use OpenEMR\Services\ImmunizationSpreadsheet;

$singlesheet = new ImmunizationSpreadsheet();
$query = json_decode($_GET['sql']);
$bindings = unserialize($_GET['bindings']);
$res = sqlStatement($query, $bindings);
$singlesheet->generateSpreadsheetArray($res, 'ImmunizationReport.xlsx');
