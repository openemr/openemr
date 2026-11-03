<?php

// Disable PHP timeout
@ini_set('max_execution_time', '0');

require_once dirname(__DIR__, 4) . "/globals.php";

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\WenoModule\Services\DownloadWenoPharmacies;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;
use OpenEMR\Modules\WenoModule\Services\WenoValidate;

// Gate: match the ACL enforced on the calling page (download_log_viewer.php).
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    exit;
}
CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

// Ensure the stored Weno encryption key is valid; auto-reset if needed.
$wenoValidate = new WenoValidate();
$isKey = $wenoValidate->validateAdminCredentials(true, "Pharmacy Directory");

$cryptoGen = ServiceContainer::getCrypto();
$weno_username = OEGlobalsBag::getInstance()->get('weno_admin_username') ?? '';
$weno_password = $cryptoGen->decryptFromDatabase(OEGlobalsBag::getInstance()->get('weno_admin_password') ?? '');
$encryption_key = $cryptoGen->decryptFromDatabase(OEGlobalsBag::getInstance()->get('weno_encryption_key') ?? '');
$baseurl = "https://online.wenoexchange.com/en/EPCS/DownloadPharmacyDirectory";

$pharmacyDownloadService = new DownloadWenoPharmacies();
$wenoLog = new WenoLogService();

$data = [
    "UserEmail" => $weno_username,
    "MD5Password" => md5($weno_password),
    "ExcludeNonWenoTest" => "N",
    "Daily" => $_GET['daily'] ?? 'N'
];

$logMessage = "User Initiated Daily Pharmacy Update";
$isFullDirectory = ($data['Daily'] == 'N');
if ($isFullDirectory) {
    $logMessage = "User Initiated Weekly Pharmacy Update";
    // The existing directory is cleared by downloadAndImport() inside its
    // import transaction, so a failed download leaves the current data intact.
}

$json_object = json_encode($data);
$method = 'aes-256-cbc';
$key = substr(hash('sha256', $encryption_key, true), 0, 32);
$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
$encrypted = base64_encode(openssl_encrypt($json_object, $method, $key, OPENSSL_RAW_DATA, $iv));
$fileUrl = $baseurl . "?useremail=" . urlencode((string) $weno_username) . "&data=" . urlencode($encrypted);

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$comment = $isFullDirectory
    ? "User Initiated Unscheduled Weekly Pharmacy Import"
    : "User Initiated Unscheduled Daily Pharmacy Import";
EventAuditLogger::getInstance()->newEvent(
    "pharmacy_log",
    $session->get('authUser'),
    $session->get('authProvider'),
    1,
    $comment
);

// No 'Start'/'End File Download' breadcrumb rows: they were the newest rows in
// weno_download_log, so the widgets that read the last row saw a breadcrumb
// instead of the import result. The debug log below covers the same ground.
ServiceContainer::getLogger()->debug('Weno pharmacy file download started');
$wenoLog->insertWenoLog("Pharmacy Directory", $logMessage);

$count = $pharmacyDownloadService->downloadAndImport(
    $fileUrl,
    isFullRebuild: $isFullDirectory,
    logContext: "Pharmacy Directory"
);

ServiceContainer::getLogger()->debug('Weno pharmacy file download finished');

if ($count === false) {
    // downloadAndImport already wrote a precise failure status.
    $last = $wenoLog->getLastPharmacyDownloadStatus();
    $message = $last['status'] ?? 'Pharmacy download/import failed.';
    die(js_escape($message));
}

// Success status already logged by downloadAndImport.
