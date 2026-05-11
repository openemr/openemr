<?php
/**
 * MedEx Module - Onboarding One-Time Password handler
 *
 * Handles OTP send/verify actions before registration step proceeds.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . '/../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\oeHttp;
use OpenEMR\Common\Session\SessionWrapperFactory;

header('Content-Type: application/json');

const MEDEX_OTP_CODE_TTL_SECONDS = 600;
const MEDEX_OTP_VERIFIED_TTL_SECONDS = 14400;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$session = null;
if (class_exists(SessionWrapperFactory::class)) {
    try {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
    } catch (\Throwable $e) {
        $session = null;
    }
}

if ($session) {
    if (empty($session->get('csrf_private_key', null))) {
        CsrfUtils::setupCsrfKey($session);
    }
} else {
    if (empty($_SESSION['csrf_private_key'] ?? null)) {
        CsrfUtils::setupCsrfKey();
    }
}
$csrfToken = trim((string)($_POST['csrf_token_form'] ?? ''));
$csrfOk = false;
if ($csrfToken !== '') {
    try {
        if ($session) {
            $csrfOk = CsrfUtils::verifyCsrfToken(token: $csrfToken, subject: 'default', session: $session) ||
                CsrfUtils::verifyCsrfToken(token: $csrfToken, subject: 'api', session: $session);
        } else {
            $csrfOk = CsrfUtils::verifyCsrfToken($csrfToken, 'default') ||
                CsrfUtils::verifyCsrfToken($csrfToken, 'api');
        }
    } catch (\Throwable $e) {
        $csrfOk = false;
    }
}
if (!$csrfOk) {
    $freshToken = '';
    try {
        if ($session) {
            $freshToken = (string) CsrfUtils::collectCsrfToken(subject: 'default', session: $session);
        } else {
            $freshToken = (string) CsrfUtils::collectCsrfToken('default');
        }
    } catch (\Throwable $e) {
        $freshToken = '';
    }
    echo json_encode(['success' => false, 'error' => 'Invalid security token', 'csrf_token' => $freshToken]);
    exit;
}

function medexOtpSessionKey(): string
{
    return 'medex_onboarding_otp';
}

function medexOtpActorKey(): string
{
    $site = strtolower(trim((string)($_SESSION['site_id'] ?? $_GET['site'] ?? 'default')));
    $user = strtolower(trim((string)($_SESSION['authUserID'] ?? $_SESSION['authUser'] ?? '')));
    return hash('sha256', $site . '|' . $user);
}

function medexOtpStateKey(string $channel, string $destination, string $email): string
{
    $identity = medexOtpActorKey() . '|' . strtolower(trim($channel)) . '|' . strtolower(trim($destination)) . '|' . strtolower(trim($email));
    return hash('sha256', $identity);
}

function medexEnsureOtpStateTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_onboarding_otp_state` (
            `state_key` varchar(64) NOT NULL,
            `state_json` mediumtext NOT NULL,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`state_key`),
            KEY `idx_updated_at` (`updated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexSaveOtpStateToDb(array $state): void
{
    $channel = (string)($state['channel'] ?? '');
    $destination = (string)($state['destination'] ?? '');
    $email = (string)($state['email'] ?? '');
    if ($channel === '' || $destination === '') {
        return;
    }
    medexEnsureOtpStateTable();
    $stateKey = medexOtpStateKey($channel, $destination, $email);
    QueryUtils::sqlStatementThrowException(
        "INSERT INTO medex_onboarding_otp_state (state_key, state_json, updated_at)
         VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE state_json = VALUES(state_json), updated_at = NOW()",
        [$stateKey, json_encode($state)]
    );
}

function medexLoadOtpStateFromDb(string $channel, string $destination, string $email): ?array
{
    if ($channel === '' || $destination === '') {
        return null;
    }
    medexEnsureOtpStateTable();
    $stateKey = medexOtpStateKey($channel, $destination, $email);
    $row = QueryUtils::querySingleRow(
        "SELECT state_json FROM medex_onboarding_otp_state WHERE state_key = ? LIMIT 1",
        [$stateKey]
    );
    $raw = (string)($row['state_json'] ?? '');
    if ($raw === '') {
        return null;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function medexDeleteOtpStateFromDb(string $channel, string $destination, string $email): void
{
    if ($channel === '' || $destination === '') {
        return;
    }
    medexEnsureOtpStateTable();
    $stateKey = medexOtpStateKey($channel, $destination, $email);
    QueryUtils::sqlStatementThrowException(
        "DELETE FROM medex_onboarding_otp_state WHERE state_key = ?",
        [$stateKey]
    );
}

function medexGetOtpState()
{
    global $session;
    $key = medexOtpSessionKey();
    if (isset($session) && is_object($session) && method_exists($session, 'get')) {
        $val = $session->get($key, null);
        if (is_array($val)) {
            return $val;
        }
    }
    $raw = $_SESSION[$key] ?? null;
    return is_array($raw) ? $raw : null;
}

function medexSetOtpState(array $state): void
{
    global $session;
    $key = medexOtpSessionKey();
    if (isset($session) && is_object($session) && method_exists($session, 'set')) {
        $session->set($key, $state);
    }
    $_SESSION[$key] = $state;
}

function medexClearOtpState(?string $channel = null, ?string $destination = null, ?string $email = null): void
{
    global $session;
    $key = medexOtpSessionKey();
    if (isset($session) && is_object($session) && method_exists($session, 'remove')) {
        $session->remove($key);
    }
    unset($_SESSION[$key]);
    if (!empty($channel) && !empty($destination)) {
        medexDeleteOtpStateFromDb((string)$channel, (string)$destination, (string)($email ?? ''));
    }
}

function medexNormalizeOtpChannel(string $channel): string
{
    $channel = strtolower(trim($channel));
    return in_array($channel, ['email', 'sms'], true) ? $channel : '';
}

function medexResolveOpenEmrBaseUrlForOtp(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '';
    }

    $basePath = '';
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $interfacePos = strpos($scriptName, '/interface/');
    if ($interfacePos !== false) {
        $basePath = rtrim(substr($scriptName, 0, $interfacePos), '/');
    }

    if ($basePath === '') {
        $webroot = trim((string)($GLOBALS['webroot'] ?? ''), '/');
        if ($webroot !== '') {
            $basePath = '/' . $webroot;
        }
    }

    if ($basePath === '') {
        $siteAddr = trim((string)($GLOBALS['site_addr_oath'] ?? ''));
        if ($siteAddr !== '') {
            $siteParts = parse_url($siteAddr);
            $sitePath = trim((string)($siteParts['path'] ?? ''), '/');
            if ($sitePath !== '') {
                $basePath = '/' . $sitePath;
            }
        }
    }

    return $scheme . '://' . $host . $basePath;
}

function medexIsNonPublicHostForOtp(string $host): bool
{
    $host = strtolower(trim($host));
    if ($host === '' || $host === 'localhost') {
        return true;
    }

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    foreach (['.local', '.localhost', '.internal', '.test', '.invalid', '.example'] as $suffix) {
        if (str_ends_with($host, $suffix)) {
            return true;
        }
    }

    return false;
}

function medexOtpPublicBaseUrl(): string
{
    foreach (['medex_callback_base_url', 'medex_preview_tunnel_public_base_url', 'site_addr_oath'] as $globalName) {
        $value = trim((string)($GLOBALS[$globalName] ?? ''));
        if ($value !== '') {
            return rtrim($value, '/');
        }
    }

    return '';
}

function medexOtpReachabilityStatus(): array
{
    $candidateUrl = medexOtpPublicBaseUrl();
    if ($candidateUrl === '') {
        $candidateUrl = rtrim(medexResolveOpenEmrBaseUrlForOtp(), '/');
    }

    if ($candidateUrl === '') {
        return [false, 'OpenEMR URL could not be detected. This install must have a publicly reachable HTTPS URL before MedEx registration can continue.'];
    }

    $parts = parse_url($candidateUrl);
    $scheme = strtolower((string)($parts['scheme'] ?? ''));
    $host = strtolower((string)($parts['host'] ?? ''));

    if ($scheme !== 'https') {
        return [false, 'OpenEMR must be reachable at a public HTTPS URL before MedEx registration can continue.'];
    }

    if ($host === '' || medexIsNonPublicHostForOtp($host)) {
        return [false, 'This OpenEMR URL is local or not publicly reachable. Assign a public preview or production URL before continuing.'];
    }

    return [true, ''];
}

function medexNormalizeSmsDestination(string $sms): string
{
    $sms = trim($sms);
    if (preg_match('/^\+\d{10,15}$/', $sms)) {
        return $sms;
    }
    return '';
}

function medexEnsureOtpAuditTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_onboarding_otp_audit` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `action` varchar(20) NOT NULL,
            `channel` varchar(20) NOT NULL,
            `destination` varchar(40) DEFAULT NULL,
            `country_code` varchar(8) DEFAULT NULL,
            `decision` varchar(20) NOT NULL,
            `reason` varchar(255) DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexAllowDevOtpFallback(): bool
{
    $host = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
    if ($host === 'emr-dev.hipaabank.net') {
        return true;
    }
    if ($host === 'localhost' || str_starts_with($host, '127.')) {
        return true;
    }
    return false;
}

function medexEnsureOtpIpRateTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_onboarding_otp_ip_rate` (
            `ip` varchar(45) NOT NULL,
            `window_start` datetime NOT NULL,
            `send_count` int(11) NOT NULL DEFAULT 0,
            `blocked_until` datetime DEFAULT NULL,
            `last_destination` varchar(64) DEFAULT NULL,
            `last_seen` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`ip`),
            KEY `idx_blocked_until` (`blocked_until`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexResolveClientIp(): string
{
    $xff = trim((string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    if ($xff !== '') {
        $parts = explode(',', $xff);
        foreach ($parts as $part) {
            $ip = trim($part);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    $remote = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($remote !== '' && filter_var($remote, FILTER_VALIDATE_IP)) {
        return $remote;
    }
    return '';
}

function medexCheckAndBumpOtpIpRate(string $ip, string $destination): array
{
    if ($ip === '') {
        return [true, 0, 0];
    }
    medexEnsureOtpIpRateTable();

    $windowSeconds = 60;
    $maxPerWindow = 20;
    $blockSeconds = 900; // 15 minutes
    $nowTs = time();
    $nowSql = gmdate('Y-m-d H:i:s', $nowTs);

    $row = QueryUtils::querySingleRow(
        "SELECT window_start, send_count, blocked_until
         FROM medex_onboarding_otp_ip_rate WHERE ip = ? LIMIT 1",
        [$ip]
    );

    $count = (int)($row['send_count'] ?? 0);
    $windowStartTs = !empty($row['window_start']) ? strtotime((string)$row['window_start']) : 0;
    $blockedUntilTs = !empty($row['blocked_until']) ? strtotime((string)$row['blocked_until']) : 0;
    if ($blockedUntilTs > $nowTs) {
        $remaining = $blockedUntilTs - $nowTs;
        return [false, $remaining, $count];
    }

    if ($windowStartTs <= 0 || ($nowTs - $windowStartTs) > $windowSeconds) {
        $count = 1;
        $windowStartSql = $nowSql;
    } else {
        $count++;
        $windowStartSql = (string)$row['window_start'];
    }

    $blockedUntilSql = null;
    if ($count > $maxPerWindow) {
        $blockedUntilSql = gmdate('Y-m-d H:i:s', $nowTs + $blockSeconds);
    }

    QueryUtils::sqlStatementThrowException(
        "INSERT INTO medex_onboarding_otp_ip_rate (ip, window_start, send_count, blocked_until, last_destination, last_seen)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            window_start = VALUES(window_start),
            send_count = VALUES(send_count),
            blocked_until = VALUES(blocked_until),
            last_destination = VALUES(last_destination),
            last_seen = VALUES(last_seen)",
        [$ip, $windowStartSql, $count, $blockedUntilSql, substr($destination, 0, 64), $nowSql]
    );

    if ($blockedUntilSql !== null) {
        return [false, $blockSeconds, $count];
    }
    return [true, 0, $count];
}

function medexAuditOtpDecision(string $action, string $channel, string $destination, string $countryCode, string $decision, string $reason): void
{
    try {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO medex_onboarding_otp_audit
             (action, channel, destination, country_code, decision, reason, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$action, $channel, $destination, $countryCode, $decision, substr($reason, 0, 255)]
        );
    } catch (\Throwable $e) {
        error_log('[MedEx OTP] audit insert failed: ' . $e->getMessage());
    }
}

function medexSmsCountryPolicy(string $sms): array
{
    // Country/location gate for onboarding OTP. Can be overridden by global.
    $allow = strtoupper(trim((string)($GLOBALS['medex_onboarding_sms_allowlist'] ?? 'US,CA')));
    $allowSet = array_filter(array_map('trim', explode(',', $allow)));

    if (!preg_match('/^\+(\d{8,15})$/', $sms, $m)) {
        return [false, '', 'invalid_number_format'];
    }

    // NANP +1 parsing: +1 followed by 10-digit national number.
    // Previous parsing used a greedy 1-3 digit country-code capture and could
    // misread +1XXXXXXXXXX as country code 155/141/etc.
    $digits = $m[1];
    if (!str_starts_with($digits, '1')) {
        $countryCallingCode = substr($digits, 0, 3);
        return [false, $countryCallingCode, 'country_not_allowed'];
    }

    $countryCallingCode = '1';
    $national = substr($digits, 1);
    if ($national === '') {
        return [false, '1', 'invalid_nanp_length'];
    }

    // NANP checks: 10-digit national number expected
    if (strlen($national) !== 10) {
        return [false, '1', 'invalid_nanp_length'];
    }

    // Block premium/high-risk patterns (900 NPA and 976 exchange)
    if (preg_match('/^900/', $national) || preg_match('/^\d{3}976/', $national)) {
        return [false, '1', 'premium_or_high_risk_prefix'];
    }

    // Block toll-free destinations for OTP delivery reliability
    if (preg_match('/^(800|888|877|866|855|844|833|822)/', $national)) {
        return [false, '1', 'toll_free_not_supported'];
    }

    // Allow only if US or CA is configured in allowlist.
    $hasUS = in_array('US', $allowSet, true);
    $hasCA = in_array('CA', $allowSet, true);
    if (!$hasUS && !$hasCA) {
        return [false, '1', 'allowlist_reject'];
    }

    return [true, '1', 'ok'];
}

function medexNormalizeEmailDestination(string $email): string
{
    $email = trim($email);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    return '';
}

function medexMaskOtpDestination(string $channel, string $destination): string
{
    if ($channel === 'sms') {
        $digits = preg_replace('/\D+/', '', $destination);
        if (strlen($digits) >= 4) {
            return ' ending in ' . substr($digits, -4);
        }
        return '';
    }

    if ($channel === 'email') {
        $parts = explode('@', $destination, 2);
        if (count($parts) === 2) {
            $local = $parts[0];
            $domain = $parts[1];
            $localMasked = strlen($local) <= 2
                ? substr($local, 0, 1) . '*'
                : substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 1));
            return ' to ' . $localMasked . '@' . $domain;
        }
    }

    return '';
}

function medexOtpSendSuccessMessage(string $channel, string $destination): string
{
    if ($channel === 'sms') {
        return 'One-time password sent by text message' . medexMaskOtpDestination($channel, $destination) . '.';
    }
    return 'One-time password sent by email' . medexMaskOtpDestination($channel, $destination) . '.';
}

function medexSendOtpThroughApi(string $channel, string $destination, string $code, string $signupIp): array
{
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $message = 'Your MedEx one-time password is ' . $code . '. It expires in 10 minutes.';
    $subject = 'Your MedEx One-Time Password';
    $payload = [
        'practice_id' => 'onboarding',
        'pid' => 'onboarding',
        'destination' => $destination,
        'method' => $channel,
        'message' => $message,
        'subject' => $subject,
        'type' => 'onboarding_otp',
        'signup_ip' => $signupIp
    ];

    try {
        $response = $api->makeRequest(
            '/index.php?route=api/send_secure_chat_link',
            $payload,
            'POST'
        );

        if (!empty($response['success'])) {
            return [true, 'One-time password sent.'];
        }

        $err = (string)($response['error'] ?? 'Unable to send one-time password');
        return [false, $err];
    } catch (\Throwable $e) {
        // Retry once against the public MedEx API host.
        error_log('[MedEx OTP] primary send error: ' . $e->getMessage());
    }

    try {
        $http = oeHttp::setOptions([
            'timeout' => 5,
            'verify' => false,
            'http_errors' => false
        ]);
        $fallbackUrl = 'https://api.hipaabank.net/cart/upload/index.php?route=api/send_secure_chat_link';
        $resp = $http->asFormParams()->post($fallbackUrl, $payload);
        $codeHttp = (int)$resp->getStatusCode();
        $body = (string)$resp->getBody();
        if ($codeHttp !== 200) {
            return [false, 'Unable to send one-time password right now.'];
        }
        $decoded = json_decode($body, true);
        if (!empty($decoded['success'])) {
            return [true, 'One-time password sent.'];
        }
        return [false, (string)($decoded['error'] ?? 'Unable to send one-time password right now.')];
    } catch (\Throwable $e) {
        error_log('[MedEx OTP] fallback send error: ' . $e->getMessage());
        return [false, 'Unable to send one-time password right now.'];
    }
}

$action = strtolower(trim((string)($_POST['action'] ?? '')));
if (!in_array($action, ['send', 'verify', 'status'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

$channel = medexNormalizeOtpChannel((string)($_POST['otp_channel'] ?? ''));
if ($channel === '') {
    echo json_encode(['success' => false, 'error' => 'Invalid one-time password method']);
    exit;
}

$email = medexNormalizeEmailDestination((string)($_POST['email'] ?? ''));
$sms = medexNormalizeSmsDestination((string)($_POST['otp_sms_destination'] ?? ''));
$destination = ($channel === 'email') ? $email : $sms;
medexEnsureOtpAuditTable();
if ($destination === '') {
    medexAuditOtpDecision($action, $channel, $destination, '', 'reject', 'missing_destination');
    if ($channel === 'email') {
        echo json_encode(['success' => false, 'error' => 'Valid administrator email is required']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Valid SMS number is required in +15551234567 format']);
    }
    exit;
}

[$otpReachabilityReady, $otpReachabilityMessage] = medexOtpReachabilityStatus();
if (!$otpReachabilityReady) {
    medexAuditOtpDecision($action, $channel, $destination, '', 'reject', 'non_public_openemr_url');
    if ($action === 'status') {
        echo json_encode([
            'success' => true,
            'verified' => false,
            'message' => $otpReachabilityMessage,
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $otpReachabilityMessage]);
    }
    exit;
}

if ($action === 'status') {
    $state = medexGetOtpState();
    if (!is_array($state)) {
        $state = medexLoadOtpStateFromDb($channel, $destination, $email);
        if (is_array($state)) {
            medexSetOtpState($state);
        }
    }

    if (!is_array($state)) {
        echo json_encode(['success' => true, 'verified' => false]);
        exit;
    }

    $verifiedExpiresAt = (int)($state['verified_expires_at'] ?? 0);
    $codeExpiresAt = (int)($state['expires_at'] ?? 0);
    $isExpired = false;
    if (!empty($state['verified'])) {
        $isExpired = ($verifiedExpiresAt > 0 && $verifiedExpiresAt < time());
    } else {
        $isExpired = ($codeExpiresAt > 0 && $codeExpiresAt < time());
    }

    if ($isExpired) {
        medexClearOtpState($channel, $destination, $email);
        echo json_encode(['success' => true, 'verified' => false]);
        exit;
    }

    if (!empty($state['verified']) && !empty($state['proof'])) {
        echo json_encode([
            'success' => true,
            'verified' => true,
            'otp_proof' => (string)$state['proof'],
            'message' => 'One-time password already verified. You can continue onboarding.',
            'verified_expires_at' => $verifiedExpiresAt,
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'verified' => false,
        'message' => ($codeExpiresAt > time())
            ? 'One-time password sent. Enter the code to continue.'
            : 'Send and verify your one-time password before continuing.'
    ]);
    exit;
}

if ($action === 'send') {
    $signupIp = medexResolveClientIp();
    [$rateAllowed, $retryAfterSec] = medexCheckAndBumpOtpIpRate($signupIp, $destination);
    if (!$rateAllowed) {
        medexAuditOtpDecision('send', $channel, $destination, '', 'reject', 'ip_rate_limited');
        echo json_encode([
            'success' => false,
            'error' => 'Too many OTP requests from this network. Please wait and try again.',
            'retry_after_seconds' => $retryAfterSec
        ]);
        exit;
    }

    if ($channel === 'sms') {
        [$smsOk, $countryCode, $smsReason] = medexSmsCountryPolicy($destination);
        if (!$smsOk) {
            medexAuditOtpDecision('send', $channel, $destination, $countryCode, 'reject', $smsReason);
            echo json_encode([
                'success' => false,
                'error' => 'SMS one-time password is not available for this destination. Use Email OTP or contact support.'
            ]);
            exit;
        }
    }

    $code = (string)random_int(100000, 999999);
    [$ok, $msg] = medexSendOtpThroughApi($channel, $destination, $code, $signupIp);
    $debugOtpCode = '';
    if (!$ok && $channel === 'email' && medexAllowDevOtpFallback()) {
        $lowerMsg = strtolower($msg);
        if (str_contains($lowerMsg, 'delivery is currently unavailable') || str_contains($lowerMsg, 'unable to send email')) {
            $ok = true;
            $msg = 'Email delivery is unavailable in this dev environment. Use the displayed development OTP code.';
            $debugOtpCode = $code;
            medexAuditOtpDecision('send', $channel, $destination, '', 'allow', 'dev_fallback_code_displayed');
        }
    }
    if (!$ok) {
        medexAuditOtpDecision('send', $channel, $destination, ($channel === 'sms' ? '1' : ''), 'reject', 'provider_send_failed');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    medexAuditOtpDecision('send', $channel, $destination, ($channel === 'sms' ? '1' : ''), 'allow', 'sent');

    $sendState = [
        'channel' => $channel,
        'destination' => $destination,
        'email' => $email,
        'code_hash' => password_hash($code, PASSWORD_DEFAULT),
        'sent_at' => time(),
        'expires_at' => time() + MEDEX_OTP_CODE_TTL_SECONDS,
        'attempts' => 0,
        'verified' => false,
        'verified_at' => 0,
        'verified_expires_at' => 0,
        'proof' => '',
    ];
    medexSetOtpState($sendState);
    medexSaveOtpStateToDb($sendState);

    $response = ['success' => true, 'message' => medexOtpSendSuccessMessage($channel, $destination)];
    if ($debugOtpCode !== '') {
        $response['debug_otp_code'] = $debugOtpCode;
    }
    echo json_encode($response);
    exit;
}

$state = medexGetOtpState();
if (!is_array($state)) {
    $state = medexLoadOtpStateFromDb($channel, $destination, $email);
}
if (!is_array($state)) {
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'no_state');
    echo json_encode(['success' => false, 'error' => 'Send one-time password first']);
    exit;
}
if (($state['channel'] ?? '') !== $channel || ($state['destination'] ?? '') !== $destination) {
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'destination_changed');
    echo json_encode(['success' => false, 'error' => 'One-time password destination has changed. Send a new code.']);
    exit;
}
if (!empty($state['verified']) && !empty($state['proof'])) {
    $verifiedExpiresAt = (int)($state['verified_expires_at'] ?? 0);
    if ($verifiedExpiresAt > time()) {
        echo json_encode([
            'success' => true,
            'otp_proof' => (string)$state['proof'],
            'message' => 'One-time password already verified.'
        ]);
        exit;
    }
}
if ((int)($state['expires_at'] ?? 0) < time()) {
    medexClearOtpState($channel, $destination, $email);
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'expired');
    echo json_encode(['success' => false, 'error' => 'One-time password expired. Send a new code.']);
    exit;
}

$code = trim((string)($_POST['otp_code'] ?? ''));
if (!preg_match('/^\d{6}$/', $code)) {
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'bad_code_format');
    echo json_encode(['success' => false, 'error' => 'Enter a valid 6-digit one-time password']);
    exit;
}

$attempts = (int)($state['attempts'] ?? 0) + 1;
$state['attempts'] = $attempts;
if ($attempts > 5) {
    medexClearOtpState($channel, $destination, $email);
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'too_many_attempts');
    echo json_encode(['success' => false, 'error' => 'Too many invalid attempts. Send a new one-time password.']);
    exit;
}

if (!password_verify($code, (string)($state['code_hash'] ?? ''))) {
    medexSetOtpState($state);
    medexSaveOtpStateToDb($state);
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'incorrect_code');
    echo json_encode(['success' => false, 'error' => 'Incorrect one-time password']);
    exit;
}

$proof = bin2hex(random_bytes(16));
$state['verified'] = true;
$state['verified_at'] = time();
$state['verified_expires_at'] = time() + MEDEX_OTP_VERIFIED_TTL_SECONDS;
$state['proof'] = $proof;
medexSetOtpState($state);
medexSaveOtpStateToDb($state);
medexAuditOtpDecision('verify', $channel, $destination, '', 'allow', 'verified');

echo json_encode([
    'success' => true,
    'otp_proof' => $proof,
    'message' => 'One-time password verified.'
]);
exit;
