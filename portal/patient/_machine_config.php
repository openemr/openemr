<?php

/**
 * @package Patient
 *
 * MACHINE-SPECIFIC CONFIGURATION SETTINGS
 *
 * The configuration settings in this file can be changed to suit the
 * machine on which the app is running (ex. local, staging or production).
 *
 * This file should not be added to version control, rather a template
 * file should be added instead and then copied for each install
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

/* */

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
