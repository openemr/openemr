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
use Symfony\Component\HttpFoundation\Request;

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
// `$landingpage` must be defined before including interface/globals.php — its
// multisite site-mismatch handler treats a non-empty $landingpage as "this is a
// portal request" and redirects there; without it the user is bounced to
// interface/login/login.php instead of the portal login. The real landing page
// is rebuilt inside the controller from the resolved site id.
$landingpage = 'index.php?site=default';
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

$symfonyRequest = Request::createFromGlobals();
/** @var array<string, mixed> $post */
$post = $symfonyRequest->request->all();
// Controller only reads $request['redirect']; the legacy script sourced it from
// $_REQUEST, which (with default request_order GP) is POST shadowing GET.
/** @var array<string, mixed> $request */
$request = [
    'redirect' => $symfonyRequest->request->get('redirect')
        ?? $symfonyRequest->query->get('redirect'),
];

// Site id resolution preserves the legacy expression:
//   (string) ($session->get('site_id', false) ?? $_GET['site'] ?? 'default')
// SessionInterface::get returns the provided default (`false`) when the key is missing,
// and `??` only short-circuits on null — so a missing session key produces literal `''`
// rather than falling through to the query string. The query-string fallback only runs
// when site_id is present and explicitly null. This is a quirk of the legacy code, kept
// for bit-for-bit behavior preservation.
$fromSession = $session->get('site_id', false);
if ($fromSession === false) {
    $siteId = '';
} elseif ($fromSession === null) {
    $fromGet = $symfonyRequest->query->get('site');
    $siteId = is_string($fromGet) ? $fromGet : 'default';
} elseif (is_string($fromSession)) {
    $siteId = $fromSession;
} else {
    $siteId = '';
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
