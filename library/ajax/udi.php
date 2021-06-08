<?php

/**
 * This script services requests for UDI (Unique Device Identifier)
 * within OpenEMR. All returns are done via json.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\MedicalDevice\MedicalDevice;

header('Content-type: application/json');

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"], 'udi')) {
    CsrfUtils::csrfNotVerified(false);
}

$udi = $_GET["udi"] ?? null;
if (empty($udi)) {
    (new SystemLogger())->error("OpenEMR ERROR: Called udi.php script without sending a udi");
    die;
}

echo MedicalDevice::createStandardJson($udi);
exit;
