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
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApiException;

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::denyWithTemplate(
        "ACL check failed for acct/bill: ClaimRev Connect - ERA Download",
        xl("ClaimRev Connect - ERA Download")
    );
}

$rawEraId = $_GET['eraId'] ?? null;
$eraId = is_string($rawEraId) ? $rawEraId : '';

try {
    $fileData = EraPage::downloadEra($eraId);
} catch (\InvalidArgumentException) {
    http_response_code(400);
    echo xlt('Invalid ERA ID format');
    exit;
} catch (ClaimRevApiException) {
    http_response_code(500);
    echo xlt('Failed to download ERA file. Please try again later.');
    exit;
}

if ($fileData === false) {
    http_response_code(404);
    echo xlt('ERA file not found');
    exit;
}

/** @var string */
$fileText = $fileData['fileText'] ?? '';
/** @var string */
$fileName = $fileData['fileName'] ?? 'download.txt';

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/edi-x12");
header("Content-Length: " . strlen($fileText));
// Sanitize filename to prevent header injection
$safeFileName = str_replace(['"', "\r", "\n", "\0"], '', $fileName);
header('Content-Disposition: attachment; filename="' . $safeFileName . '"');
header("Content-Description: File Transfer");
echo $fileText; // nosemgrep: echoed-request
exit;
