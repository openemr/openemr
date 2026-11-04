<?php

/**
 * Session bootstrap for portal/patient: uses the portal patient's (or registering visitor's)
 * session, or else the staff session, which must hold the patientportal/portal ACL. Loads
 * interface/globals.php, so it has to be required at file scope from portal/patient/index.php.
 *
 * Began as the Phreeze package's machine configuration; none of that code remains.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$globalsBag = OEGlobalsBag::getInstance();

if ($session->has('pid') && ($session->has('patient_portal_onsite_two') || $session->get('register') === true)) {
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    if (!$session->has('portal_init')) {
        SessionUtil::setSession('portal_init', true);
    }
    require_once(__DIR__ . "/../../interface/globals.php");
} else {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    $session = SessionWrapperFactory::getInstance()->getCoreSession();
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!$session->has('authUserID')) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
    // Core-user fallback: this branch runs for staff hitting /portal/patient/*
    // without a portal-patient session (e.g. `Provider.Home` dashboard).
    // Historically the branch ran with NO acl gate, so any authenticated
    // staff account could reach portal-scoped controllers (OnsiteDocument
    // CRUD, etc.) — which use `bootstrap_pid`-only patient binding and
    // therefore leak data across patients when `bootstrap_pid` is empty.
    // Require the same `patientportal/portal` ACL that `ProviderController::Home()`
    // enforces at the template layer, so the gate lives at the bootstrap
    // choke point and covers every downstream controller uniformly.
    if (!AclMain::aclCheckCore('patientportal', 'portal')) {
        // Do not leak whether the section exists; behave the same as an
        // unauthenticated visitor and bounce to the login landing.
        SessionWrapperFactory::getInstance()->destroyCoreSession();
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }
}
