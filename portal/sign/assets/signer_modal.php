<?php

/**
 * Patient Portal Signer Modal Dynamic Template
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$is_portal = (isset($_SESSION['patient_portal_onsite_two']) && $_SESSION['authUser'] == 'portal-user') ? 1 : $_GET['isPortal'];

if (empty($is_portal)) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
} else {
    //landing page definition -- where to go if something goes wrong
    $landingpage = "index.php?site=" . urlencode($_SESSION['site_id'] ?? null);
    //
    if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
        $pid = $_SESSION['pid'];
    } else {
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit;
    }
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . '/../../../interface/globals.php');

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;

$aud = "admin-signature";
$cuser = attr($_SESSION['authUserID'] ?? "-patient-");
$cpid = attr($_SESSION['pid'] ?? "0");
$api_id = $_SESSION['api_csrf_token'] ?? ''; // portal doesn't do remote

$twigVars = [
    'is_portal' => $is_portal
    ,'cuser' => $cuser
    ,'cpid' => $cpid
    ,'aud' => $is_portal ? $aud = 'patient_signature' : $aud
];
$twigContainer = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
try {
    $modal = $twigContainer->render("portal/partial/_signer_modal.html.twig", $twigVars);
} catch (Exception $exception) {
    (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
    // we want the json to fail
    die(json_encode(['error' => 'Server died']));
}

echo js_escape($modal);
exit();
