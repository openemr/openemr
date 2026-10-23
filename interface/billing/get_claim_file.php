<?php

/**
 * get_claim_file.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");

use OpenEMR\Billing\BatchFilePublisher;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

$globals = OEGlobalsBag::getInstance();
require_once $globals->getString('OE_SITE_DIR') . "/config.php";

if (!AclMain::aclCheckCore('acct', 'eob', '', 'write') && !AclMain::aclCheckCore('acct', 'bill', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/eob or acct/bill: Billing Manager", xl("Billing Manager"));
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

$request = CurrentRequest::get();
$contentType = "text/plain";
$key = $request->query->get('key');
$converted = is_string($key) ? convert_safe_file_dir_name($key) : null;
$safeName = is_string($converted) ? $converted : '';

// The billing tables store a file name, not a directory. The name is accepted
// only when that directory already lists it, so the request is not a path.
$location = $request->query->get('location');
$claimFile = null;
if ($location === 'tmp') {
    $temporary = rtrim($globals->getString('temporary_files_dir'), DIRECTORY_SEPARATOR);
    $claimFile = BatchFilePublisher::listedFile($temporary, $safeName);
}

$partner = $request->query->get('partner');
if ($claimFile === null && is_string($partner) && $partner !== '') {
    $row = QueryUtils::querySingleRow(
        "SELECT `X`.`id`, `X`.`x12_sftp_local_dir` FROM `x12_partners` `X` WHERE `X`.`id` = ? LIMIT 1",
        [$partner]
    );
    $partnerDirectory = is_array($row) ? ($row['x12_sftp_local_dir'] ?? null) : null;
    if (is_string($partnerDirectory) && $partnerDirectory !== '') {
        $claimFile = BatchFilePublisher::listedFile($partnerDirectory, $safeName);
    }
}

$claimFile ??= BatchFilePublisher::listedFile(
    $globals->getString('OE_SITE_DIR') . "/documents/edi",
    $safeName
);

if (
    !is_string($claimFile)
    || !BatchFilePublisher::downloadAllowed(dirname($claimFile), basename($claimFile))
) {
    echo xlt("The claim file: ") . text($safeName) . xlt(" could not be accessed.");
    exit;
}

if (strtolower(substr($claimFile, -4)) === ".pdf") {
    $contentType = "application/pdf";
}

$handle = fopen($claimFile, 'r');
if ($handle === false) {
    echo xlt("The claim file: ") . text($safeName) . xlt(" could not be accessed.");
    exit;
}

$size = filesize($claimFile);
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: " . $contentType);
if (is_int($size)) {
    header("Content-Length: " . $size);
}
header("Content-Disposition: attachment; filename=" . basename($claimFile));
fpassthru($handle);
fclose($handle);

$delete = $request->query->get('delete');
if ($delete === '1') {
    unlink($claimFile);
}

exit;
