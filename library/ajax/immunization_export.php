<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c)  2022 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2022-2023 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once(dirname(__FILE__, 3) . "/interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\SpreadSheetService;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$query = json_decode($_GET['sql']);
$bindings = unserialize($_GET['bindings']);
$res = sqlStatement($query, $bindings);
while ($row = sqlFetchArray($res)) {
    $immunizations[] = $row;
}

$spreadsheet = new SpreadSheetService($immunizations, null, 'immunizations');
if (!empty($spreadsheet->buildSpreadsheet())) {
    $spreadsheet->downloadSpreadsheet('Xls');
}
