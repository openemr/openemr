<?php

/**
 * portal/get_patient_info.php
 *
 * Patient portal login form POST target. Thin entry point: bootstraps the portal
 * session/autoloader, wires the production dependencies, invokes
 * PatientPortalLoginController, and applies the returned directive
 * (portalLog + maybe destroy session + maybe emit no-cache headers + redirect).
 *
 * All login logic lives in OpenEMR\Controllers\Portal\PatientPortalLoginController so
 * it can be unit-tested with an injected in-memory credentials repository. See
 * tests/Tests/Unit/Portal/PatientPortalLoginControllerTest.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Portal\PatientPortalLoginController;
use OpenEMR\Controllers\Portal\PortalAuditLogger;
use OpenEMR\Controllers\Portal\SessionUtilPortalSessionAccessor;
use OpenEMR\Controllers\Portal\SqlPortalLoginCredentialsRepository;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Globals\UserSettingsService;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once(__DIR__ . '/../vendor/autoload.php');

$globalsBag = OEGlobalsBag::getInstance();

// Prevent error 500 in case of cleaning cookies and site data once when the login page is already loaded.
if (SessionUtil::getAppCookie() === '') {
    $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
}

// Auth flow writes heavily to session and uses migrate(); explicitly opt out of the
// portal's default read-only session mode before obtaining the active session.
$sessionAllowWrite = true;
SessionWrapperFactory::getInstance()->setSessionReadOnly(false);
$session = SessionWrapperFactory::getInstance()->getActiveSession();
// Regenerate the session id to avoid session fixation attacks.
$session->migrate(true);

// OpenEMR globals + the legacy ApplicationTable class needed by the audit logger.
// (QueryUtils, AuthHash, CsrfUtils, and UserSettingsService are all PSR-4 and load
// via the composer autoloader.)
$ignoreAuth_onsite_portal = true;
require_once('../interface/globals.php');
require_once(__DIR__ . '/lib/appsql.class.php');

$logit = new ApplicationTable();

// PortalAuditLogger adapter that delegates to ApplicationTable::portalLog. Defined as an
// anonymous class here (rather than as a typed class in src/) so the legacy non-PSR-4
// reference to ApplicationTable stays out of the autoloaded surface.
$auditLogger = new class ($logit) implements PortalAuditLogger {
    public function __construct(private readonly ApplicationTable $delegate)
    {
    }

    public function portalLog(string $event, $patientId, string $comments, string $binds = '', string $success = '1'): void
    {
        $this->delegate->portalLog($event, $patientId, $comments, $binds, $success);
    }
};

// Wire the provider info lookup as a static-method callable so the repository itself
// does not have to reference the legacy global function.
$controller = new PatientPortalLoginController(
    new SqlPortalLoginCredentialsRepository(UserSettingsService::getUserIDInfo(...)),
    $auditLogger
);

/** @var array<string, mixed> $post */
$post = $_POST;
/** @var array<string, mixed> $request */
$request = $_REQUEST;

// Resolve the site id from the session, then $_GET, then the literal 'default'.
// Inline narrowing (rather than `(string) (... ?? ... ?? 'default')`) so unexpected
// types from the session or query string fall through to the default.
$siteId = 'default';
$fromSession = $session->get('site_id');
if (is_string($fromSession) && $fromSession !== '') {
    $siteId = $fromSession;
} else {
    $fromGet = $_GET['site'] ?? null;
    if (is_string($fromGet) && $fromGet !== '') {
        $siteId = $fromGet;
    }
}

$result = $controller->login(
    $siteId,
    $post,
    $request,
    new SessionUtilPortalSessionAccessor($session),
    $globalsBag
);

if ($result->establishCsrf) {
    // Set up the CSRF private key (for the patient portal). Note: this key always
    // remains private and never leaves server session; it is used to create the
    // CSRF tokens.
    CsrfUtils::setupCsrfKey($session);
}

if ($result->portalLogArgs !== null) {
    $logit->portalLog(...$result->portalLogArgs);
}

if ($result->destroySessionCookie) {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
}

$response = new RedirectResponse($result->redirectUrl);
if ($result->sendNoCacheHeaders) {
    $response->headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
    $response->headers->set('Cache-Control', 'no-cache');
    $response->headers->set('Pragma', 'no-cache');
}
$response->send();
