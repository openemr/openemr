<?php

/**
 * ccda_gateway.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\CDADocumentService;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once __DIR__ . "/../vendor/autoload.php";
SessionUtil::portalSessionStart();

$sessionAllowWrite = true;
if (isset($_SESSION['pid'], $_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth = true;
    require_once __DIR__ . "/../interface/globals.php";
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $_SESSION['pid']);
} else {
    SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once __DIR__ . "/../interface/globals.php";
    if (empty($_SESSION['authUserID'])) {
        header('Location: index.php');
        exit;
    }
    define('IS_DASHBOARD', $_SESSION['authUserID']);
    define('IS_PORTAL', false);
}

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

if (!isServiceEnabled()) {
    die(xlt("CDA generation service is disabled. Verify in Administration->Globals."));
}

$_SESSION['site_id'] ??= 'default';
session_write_close();

$action = $_REQUEST['action'] ?? '';
$pid ??= 0;

try {
    $cdaService = new CDADocumentService();

    switch ($action) {
        case 'dl':
        case 'report_ccd_download':
            sendZipDownload($cdaService->generateCCDZip($pid));
            break;

        case 'view':
            echo $cdaService->generateCCDHtml($pid);
            break;

        case 'report_ccd_view':
            $html = $cdaService->generateCCDHtml($pid);
            if (stripos($html, '/interface/login_screen.php') !== false) {
                http_response_code(401);
                echo xlt("Error: Not Authorized");
                exit;
            }
            echo $html;
            break;

        default:
            http_response_code(400);
            die(xlt("Error: Invalid action requested."));
    }
} catch (Exception $e) {
    (new SystemLogger())->errorLogCaller($e->getMessage(), ['action' => $action, 'pid' => $pid]);
    http_response_code(500);
    die(xlt("Error generating CDA document. Please contact support."));
}

/**
 * Check if CDA service is enabled for current context.
 */
function isServiceEnabled(): bool
{
    $setting = OEGlobalsBag::getInstance()->getInt('ccda_alt_service_enable', 0);

    if (empty($setting)) {
        return false;
    }
    if (IS_PORTAL && $setting < 2) {
        return false;
    }
    if (IS_DASHBOARD && $setting != 1 && $setting != 3) {
        return false;
    }

    return true;
}

/**
 * Send ZIP file as download response.
 */
function sendZipDownload(string $content): void
{
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=SummaryofCare.zip");
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . strlen($content));
    echo $content;
    exit;
}
