<?php

/**
 * OIDC session refresh endpoint.
 *
 * Accepts a fresh OIDC ID token via POST and extends the PHP session.
 * Provider-agnostic — validates the token against the issuer and audience
 * already stored in the session. The client-side refresh logic (e.g.
 * Firebase JS SDK's getIdToken(true)) is provider-specific and lives in
 * the module.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

require_once(__DIR__ . "/../../interface/globals.php");

use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Identity\MinimalClaimMapper;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionHelper;
use OpenEMR\Common\Auth\Oidc\Token\JwksClient;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\OidcValidationParameters;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionTracker;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

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

// 3. Per-session rate limiting — reject if last refresh was too recent
if (OidcSessionHelper::isRefreshOnCooldown(time())) {
    http_response_code(429);
    echo json_encode(['error' => 'too_many_requests']);
    exit;
}

// 4. CSRF validation (standard post-auth token)
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

// 4. Extract token from POST
$idToken = filter_input(INPUT_POST, 'oidc_id_token');
if (!is_string($idToken) || $idToken === '') {
    http_response_code(400);
    echo json_encode(['error' => 'missing_token']);
    exit;
}

// 5. Get session metadata for validation
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

// 6. Build validation pipeline (provider-agnostic, core components only)
$globals = OEGlobalsBag::getInstance();
$tempDir = $globals->getString('temporary_files_dir');
$cacheDir = $tempDir . DIRECTORY_SEPARATOR . 'oidc_cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0o755, true);
}

$httpClient = new GuzzleHttp\Client(['timeout' => 10]);
$cache = new FilesystemCache($cacheDir);
$discoveryClient = new OidcDiscoveryClient($httpClient, $cache);
$jwksClient = new JwksClient($httpClient, $cache);
$clock = new SystemClock(new \DateTimeZone('UTC'));
$tokenValidator = new OidcTokenValidator($jwksClient, new MinimalClaimMapper(), $clock);

$username = is_string($session->get('authUser')) ? $session->get('authUser') : '';

// 7. Discover provider metadata
try {
    $metadata = $discoveryClient->getMetadata($sessionIssuer);
} catch (\Throwable) {
    EventAuditLogger::getInstance()->newEvent(
        'login',
        $username,
        '',
        0,
        'OIDC refresh failed: discovery error',
    );
    http_response_code(401);
    echo json_encode(['error' => 'token_invalid', 'message' => 'Discovery failed']);
    exit;
}

// 8. Validate the token
$clockSkew = $globals->getInt('oidc_clock_skew_seconds');
$parameters = new OidcValidationParameters(
    expectedIssuer: $sessionIssuer,
    expectedAudience: $sessionAudience,
    clockSkewSeconds: $clockSkew > 0 ? $clockSkew : 30,
);

try {
    $validatedToken = $tokenValidator->validate($idToken, $metadata->jwksUri, $parameters);
} catch (OidcTokenValidationException) {
    EventAuditLogger::getInstance()->newEvent(
        'login',
        $username,
        '',
        0,
        'OIDC refresh failed: token validation',
    );
    http_response_code(401);
    echo json_encode(['error' => 'token_invalid', 'message' => 'Token validation failed']);
    exit;
}

// 9. Issuer pinning — must match session
if ($validatedToken->identity->issuer !== $sessionIssuer) {
    EventAuditLogger::getInstance()->newEvent(
        'login',
        $username,
        '',
        0,
        'OIDC refresh failed: issuer mismatch',
    );
    http_response_code(401);
    echo json_encode(['error' => 'issuer_mismatch']);
    exit;
}

// 10. Subject pinning — must match session (if stored)
if ($sessionSubject !== null && $validatedToken->identity->externalId !== $sessionSubject) {
    EventAuditLogger::getInstance()->newEvent(
        'login',
        $username,
        '',
        0,
        'OIDC refresh failed: subject mismatch',
    );
    http_response_code(401);
    echo json_encode(['error' => 'subject_mismatch']);
    exit;
}

// 11. Update session metadata
OidcSessionHelper::updateTokenExpiry($validatedToken->expiresAt, $validatedToken->jti);
OidcSessionHelper::recordRefresh(time());

// 12. Audit log
EventAuditLogger::getInstance()->newEvent(
    'login',
    $username,
    '',
    1,
    'OIDC session refreshed',
);

echo json_encode([
    'success' => true,
    'expires_at' => $validatedToken->expiresAt->getTimestamp(),
]);
