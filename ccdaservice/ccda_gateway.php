<?php

/**
 * ccda_gateway.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//authenticate for portal or main- never know where it gets used

// Will start the (patient) portal OpenEMR session/cookie.

use OpenEMR\Common\Csrf\CsrfUtils;

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

$dowhat = $_REQUEST['action'] ?? '';
if ((!$GLOBALS['ccda_alt_service_enable']) > 0) {
    die("Cda generation service turned off: Verify in Administration->Globals! Click back to return home."); // Die an honorable death!!
}

if (!isset($_SESSION['site_id'])) {
    $_SESSION ['site_id'] = 'default';
}

session_write_close();

$parameterArray = array();
//$parameterArray['encounter'];
$parameterArray['combination'] = $pid;
$parameterArray['components'] = 'allergies|medications|problems|immunizations|procedures|results|plan_of_care|vitals|social_history|encounters|functional_status|referral|instructions|medical_devices|goals';
//$parameterArray['sections'];
$parameterArray['downloadccda'] = "download_ccda";
$parameterArray['latestccda'] = '0';
$parameterArray['send_to'] = 'download_all';
$parameterArray['sent_by_app'] = 'portal';
$parameterArray['downloadformat'] = 'ccda';
$parameterArray['ccda_pid'][] = $pid;
//$parameterArray['me'] = urlencode(session_id());
$parameterArray['view'] = 0;
$parameterArray['recipient'] = 'patient'; // emr_direct or hie
$parameterArray['site'] = $_SESSION ['site_id']; // set to an onsite portal user


$server_url = resolveHost();
// CCM returns viewable CCD html file or
// zip containing a CCDA.xml, CCDA.html and cda.xsl
$ccdaxml = portalccdafetching($pid, $server_url, $parameterArray, $dowhat);

if ($dowhat === 'dl') {
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=SummaryofCare.zip");
    header("Content-Type: application/download");
    header("Content-Transfer-Encoding: binary");
    echo $ccdaxml;
    exit;
}

echo($ccdaxml);

exit;

function portalccdafetching($pid, $server_url, $parameterArray = [], $action = 'view')
{
    $parameters = '';
    $site_id = $_SESSION['site_id'];
    $url = $server_url . "/interface/modules/zend_modules/public/encounterccdadispatch/index?site=" .
        urlencode($site_id) . "&me=" . urlencode(session_id()) .
        "&param=1&view=1&combination=" . urlencode($pid) . "&recipient=patient";
    if ($action === 'dl') {
        $parameters = http_build_query($parameterArray);
        $url = $server_url . "/interface/modules/zend_modules/public/encounterccdadispatch/index?me=" . urlencode(session_id());
    }
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HEADER, 0); // set true for look see
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=1'); // debug break on first line in public/index.php

        $result = curl_exec($ch) or die(curl_error($ch));
        curl_close($ch);
    } catch (Exception $e) {
        die($e->getMessage());
    }

    return $result;
}

function resolveHost(): string
{
    if (!empty($GLOBALS['site_addr_oath'])) {
        $host = rtrim(trim($GLOBALS['site_addr_oath']), "/");
        return rtrim(trim($host . $GLOBALS['webroot']), "/");
    }
    $scheme = $_SERVER['REQUEST_SCHEME'] . "://";
    $possibleHostSources = array('HTTP_X_FORWARDED_HOST', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR');
    $sourceTransformations = array(
        "HTTP_X_FORWARDED_HOST" => function ($value) {
            $elements = explode(',', $value);
            return trim(end($elements));
        }
    );
    $host = '';
    foreach ($possibleHostSources as $source) {
        if (!empty($host)) {
            break;
        }
        if (empty($_SERVER[$source])) {
            continue;
        }
        $host = $_SERVER[$source];
        if (array_key_exists($source, $sourceTransformations)) {
            $host = $sourceTransformations[$source]($host);
        }
    }
    // remove port
    $host = preg_replace('/:\d+$/', '', trim($host));

    return rtrim(trim($scheme . $host . $GLOBALS['webroot']), "/");
}
