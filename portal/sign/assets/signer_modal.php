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

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// this script is used by both the patient portal and main openemr; below does authorization.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../../vendor/autoload.php");
$globalsBag = OEGlobalsBag::getInstance();
$session = SessionWrapperFactory::instance()->getWrapper();

$is_portal = ($session->isSymfonySession() && $session->has('patient_portal_onsite_two') && $session->get('authUser') === 'portal-user') ? 1 : $_GET['isPortal'];

if (empty($is_portal)) {
    SessionUtil::portalSessionCookieDestroy();
} else {
    if ($session->isSymfonySession() && $session->has('pid') && $session->has('patient_portal_onsite_two')) {
        $pid = $session->get('pid');
    } else {
        //landing page definition -- where to go if something goes wrong
        $landingpage = "index.php?site=" . urlencode((string) $session->get('site_id', null));
        //
        SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit;
    }
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . '/../../../interface/globals.php');

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;

$aud = "admin-signature";
$cuser = attr($session->get('authUserID', null) ?? "-patient-");
$cpid = attr($session->get('pid', null) ?? "0");
$api_id = $session->get('api_csrf_token', ''); // portal doesn't do remote

$twigVars = [
    'is_portal' => $is_portal
    ,'cuser' => $cuser
    ,'cpid' => $cpid
    ,'aud' => $is_portal ? $aud = 'patient-signature' : $aud
];
$twigContainer = (new TwigContainer(null, $globalsBag->get('kernel')))->getTwig();
try {
    $modal = $twigContainer->render("portal/partial/_signer_modal.html.twig", $twigVars);
} catch (Exception $exception) {
    (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
    // we want the json to fail
    die(json_encode(['error' => 'Server died']));
}

echo js_escape($modal);
exit();
