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
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcUrlValidator;
use OpenEMR\Common\Auth\Oidc\Identity\MinimalClaimMapper;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionHelper;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionRefreshHandler;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
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
if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?? '', $session)) {
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

// 7. Build the handler and delegate
$globals = OEGlobalsBag::getInstance();
$tempDir = $globals->getString('temporary_files_dir');
$cacheDir = $tempDir . DIRECTORY_SEPARATOR . 'oidc_cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0o755, true);
}

$httpClient = new GuzzleHttp\Client(['timeout' => 10]);
$cache = new Psr16Cache(new FilesystemAdapter('', 0, $cacheDir));
$clock = new SystemClock(new \DateTimeZone('UTC'));

// Strict SSRF policy in production, relaxed in dev so the docker oidc-mock
// service (plain HTTP on the docker bridge network) keeps working.
$strictUrlPolicy = !$globals->getKernel()->isDev();
$urlValidator = new OidcUrlValidator(
    requireHttps: $strictUrlPolicy,
    blockPrivateIps: $strictUrlPolicy,
);

$handler = new OidcSessionRefreshHandler(
    new OidcTokenValidator(
        $httpClient,
        new MinimalClaimMapper(),
        $clock,
        new JWTRepository(),
        $cache,
        urlValidator: $urlValidator,
    ),
    new OidcDiscoveryClient($httpClient, $cache, urlValidator: $urlValidator),
    new DatabaseOidcRefreshAuditLogger(),
    $globals->getInt('oidc_clock_skew_seconds'),
);

$username = is_string($session->get('authUser')) ? $session->get('authUser') : '';

$result = $handler->handle($idToken, $sessionIssuer, $sessionAudience, $sessionSubject, $username);

// 8. Act on result
if ($result->success && $result->validatedToken !== null) {
    OidcSessionHelper::updateTokenExpiry($result->validatedToken->expiresAt, $result->validatedToken->jti);
    OidcSessionHelper::recordRefresh(time());
}

http_response_code($result->httpStatus);
echo json_encode($result->body);
