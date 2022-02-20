<?php

/**
 * ccda_gateway.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\CDADocumentService;

// authenticate for portal or main- never know where it gets used
// Will start the (patient) portal OpenEMR session/cookie.
require_once(__DIR__ . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$sessionAllowWrite = true;
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth = true;
    require_once(__DIR__ . "/../interface/globals.php");
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $_SESSION['pid']);
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../interface/globals.php");
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
    define('IS_DASHBOARD', $_SESSION['authUserID']);
    define('IS_PORTAL', false);
}

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (empty($GLOBALS['ccda_alt_service_enable'])) {
    die("Cda generation service turned off: Verify in Administration->Globals! Click back to return home."); // Die an honorable death!!
}
if (IS_PORTAL && $GLOBALS['ccda_alt_service_enable'] < 2) {
    die("Cda generation service turned off: Verify in Administration->Globals! Click back to return home."); // Die an honorable death!!
}
if (IS_DASHBOARD && ($GLOBALS['ccda_alt_service_enable'] != 1 && $GLOBALS['ccda_alt_service_enable'] != 3)) {
    die("Cda generation service turned off: Verify in Administration->Globals! Click back to return home."); // Die an honorable death!!
}

if (!isset($_SESSION['site_id'])) {
    $_SESSION ['site_id'] = 'default';
}

session_write_close();

$cdaService = new CDADocumentService();

if ($_REQUEST['action'] === 'dl') {
    $ccda_xml = $cdaService->portalGenerateCCDZip($pid);
    // download zip containing CCDA.xml, CCDA.html and cda.xsl files
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=SummaryofCare.zip");
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: binary");
    echo $ccda_xml;
    exit;
}
if ($_REQUEST['action'] === 'view') {
    $ccda_xml = $cdaService->portalGenerateCCD($pid);
    // CCM returns viewable CCD html file
    // that displays to new tab opened from home
    echo $ccda_xml;
    exit;
}
if ($_REQUEST['action'] === 'report_ccd_view') {
    $ccda_xml = $cdaService->generateCCDHtml($pid);
    if (stripos($ccda_xml, '/interface/login_screen.php') !== false) {
        echo(xlt("Error. Not Authorized."));
        exit;
    }
    echo $ccda_xml;

    exit;
}
if ($_REQUEST['action'] === 'report_ccd_download') {
    $ccda_xml = $cdaService->generateCCDZip($pid);
    // download zip containing CCDA.xml, CCDA.html and cda.xsl files
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=SummaryofCare.zip");
    header("Content-Type: application/zip");
    header("Content-Transfer-Encoding: binary");
    echo $ccda_xml;
    exit;
}
die(xlt("Error. Nothing to do."));
