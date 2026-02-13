<?php

/**
 * ERA file download handler
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\ClaimRevConnector\EraPage;

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - ERA Download",
        xl("ClaimRev Connect - ERA Download")
    );
}

$rawEraId = $_GET['eraId'] ?? null;
$eraId = is_string($rawEraId) ? $rawEraId : '';

try {
    $fileViewModel = EraPage::downloadEra($eraId);
} catch (\InvalidArgumentException) {
    http_response_code(400);
    echo xlt('Invalid ERA ID format');
    exit;
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/edi-x12");
header("Content-Length: " . strlen((string) $fileViewModel->fileText));
header('Content-Disposition: attachment; filename="' . $fileViewModel->fileName . '"');
header("Content-Description: File Transfer");
echo (string) $fileViewModel->fileText; // nosemgrep: echoed-request
exit;
