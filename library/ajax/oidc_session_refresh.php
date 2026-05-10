<?php

/**
 * OIDC session refresh endpoint (thin HTTP shim).
 *
 * Handles pre-conditions (session checks, CSRF, rate limiting, input
 * extraction), delegates business logic to {@see OidcSessionRefreshHandler},
 * and translates the result to a JSON response.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

require_once(__DIR__ . "/../../interface/globals.php");

use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Auth\Oidc\Audit\DatabaseOidcRefreshAuditLogger;
use OpenEMR\Common\Auth\Oidc\Cache\OidcCacheDirectoryFactory;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidator;
use OpenEMR\Common\Auth\Oidc\Discovery\SsrfSafeHttpClient;
use OpenEMR\Common\Auth\Oidc\Identity\MinimalClaimMapper;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionHelper;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionRefreshHandler;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\TokenRevocationService;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionTracker;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

header('Content-Type: application/json');

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// 1. Session expiration check
if (SessionTracker::isSessionExpired()) {
    http_response_code(401);
    echo json_encode(['error' => 'session_expired']);
    exit;
}

// 2. Must be an OIDC session
if (!OidcSessionHelper::isOidcSession()) {
    http_response_code(400);
    echo json_encode(['error' => 'not_oidc_session']);
    exit;
}

// 3. Per-session rate limiting
if (OidcSessionHelper::isRefreshOnCooldown(time())) {
    http_response_code(429);
    echo json_encode(['error' => 'too_many_requests']);
    exit;
}

// 4. CSRF validation
//
// Aisle round-5 #11 (CWE-248) companion. `verifyCsrfToken` calls
// `collectCsrfToken` internally, which throws RuntimeException
// when the session has no `csrf_private_key`. In practice this
// endpoint is post-auth so the key should be present, but a
// session corruption / custom auth path / OIDC metadata that
// outlives the CSRF key would otherwise propagate the throw as
// a 500 instead of the structured `{error: csrf_failed}` JSON
// response. Treat the throw as a normal CSRF failure — same
// audit log + 403 path as a wrong token.
try {
    $csrfOk = CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?? '', $session);
} catch (\RuntimeException) {
    $csrfOk = false;
}
if (!$csrfOk) {
    $username = is_string($session->get('authUser')) ? $session->get('authUser') : '';
    EventAuditLogger::getInstance()->newEvent(
        'login',
        $username,
        '',
        0,
        'OIDC refresh failed: CSRF verification',
    );
    http_response_code(403);
    echo json_encode(['error' => 'csrf_failed']);
    exit;
}

// 5. Extract token from POST
$idToken = filter_input(INPUT_POST, 'oidc_id_token');
if (!is_string($idToken) || $idToken === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_token']);
    exit;
}

// 6. Read session metadata for validation
$sessionIssuer = OidcSessionHelper::getIssuer();
$sessionAudience = OidcSessionHelper::getAudience();
$sessionSubject = OidcSessionHelper::getSubject();

if ($sessionIssuer === null || $sessionIssuer === '') {
    http_response_code(400);
    echo json_encode(['error' => 'not_oidc_session']);
    exit;
}

if ($sessionAudience === null || $sessionAudience === '') {
    http_response_code(400);
    echo json_encode(['error' => 'not_oidc_session']);
    exit;
}

if ($sessionSubject === null || $sessionSubject === '') {
    http_response_code(400);
    echo json_encode(['error' => 'not_oidc_session']);
    exit;
}

// 7. Build the handler and delegate
$globals = OEGlobalsBag::getInstance();
// Build / validate the OIDC filesystem-cache directory through the
// hardened factory (refuses symlinks, canonicalizes the base via
// realpath, restricts perms to 0700, fails loudly on mkdir errors).
// Any RuntimeException propagates to the global ErrorHandler installed
// in globals.php, which logs and responds. See OidcCacheDirectoryFactory
// for the threat model (CWE-377 — round-3 finding #6).
$cacheDir = (new OidcCacheDirectoryFactory())->create(
    $globals->getString('temporary_files_dir'),
);

$cache = new Psr16Cache(new FilesystemAdapter('', 0, $cacheDir));
$clock = new SystemClock(new \DateTimeZone('UTC'));

// Strict SSRF policy in production, relaxed in dev so the docker oidc-mock
// service (plain HTTP on the docker bridge network) keeps working.
$strictUrlPolicy = !$globals->getKernel()->isDev();
$urlValidator = new OidcUrlValidator(
    requireHttps: $strictUrlPolicy,
    blockPrivateIps: $strictUrlPolicy,
);

// Wrap the Guzzle client in SsrfSafeHttpClient so every outbound request
// resolves DNS once via the validator and pins the connection (cURL
// CURLOPT_RESOLVE) to the validated IPs — closing the rebinding/TOCTOU
// window between the validator's DNS check and Guzzle's connect-time
// lookup. Redirects are disabled by the wrapper to prevent a 3xx hop
// from re-opening the window for a fresh hostname.
$httpClient = new SsrfSafeHttpClient(
    new GuzzleHttp\Client(['timeout' => 10]),
    $urlValidator,
);

$handler = new OidcSessionRefreshHandler(
    new OidcTokenValidator(
        $httpClient,
        new MinimalClaimMapper(),
        $clock,
        new JWTRepository(),
        new TokenRevocationService(),
        $cache,
        urlValidator: $urlValidator,
    ),
    new OidcDiscoveryClient($httpClient, $cache, urlValidator: $urlValidator),
    new DatabaseOidcRefreshAuditLogger(),
    $globals->getInt('oidc_clock_skew_seconds'),
);

$username = is_string($session->get('authUser')) ? $session->get('authUser') : '';

// Aisle round-5 #6 (CWE-400). Record the refresh ATTEMPT time
// before invoking the handler so failed attempts also feed the
// per-session cooldown gate at step 3 above. Pre-fix only the
// success branch called recordRefresh(), letting an attacker
// with a valid session + CSRF token hammer the endpoint with
// invalid ID tokens and run the full validation pipeline (JWKS
// fetch, signature check, replay/revocation lookups) on every
// request. Recording on attempt — not just on success — means
// the next request hits the 429 cooldown gate regardless of
// the outcome.
OidcSessionHelper::recordRefresh(time());

$result = $handler->handle($idToken, $sessionIssuer, $sessionAudience, $sessionSubject, $username);

// 8. Act on result
if ($result->success && $result->validatedToken !== null) {
    // Persist the validator's revocation key (literal jti when present, or
    // the synthetic per-issuance identifier when the IdP omits jti). The
    // session previously stored the literal jti, which was null for
    // jti-less tokens — making logout/revocation downstream a no-op for
    // those users. The revocation key is always non-null and matches what
    // jwt_grant_history and oidc_token_revocation index by.
    OidcSessionHelper::updateTokenExpiry(
        $result->validatedToken->expiresAt,
        $result->validatedToken->revocationKey,
    );
}

http_response_code($result->httpStatus);
echo json_encode($result->body);
