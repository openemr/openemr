<?php
/**
 * MedEx Module - Registration Processing
 *
 * Handles AJAX registration requests
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\MedEx\MedExConfig;

const MEDEX_ONBOARDING_VERIFICATION_TTL_SECONDS = 14400;

function medexIsPrivateHost(string $host): bool
{
    $host = strtolower(trim($host));
    if ($host === '' || $host === 'localhost') {
        return true;
    }

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    return false;
}

function medexOnboardingDevModeEnabled(): bool
{
    $env = strtolower(trim((string)getenv('MEDEX_ONBOARDING_DEV_MODE')));
    if (in_array($env, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }

    $global = strtolower(trim((string)($GLOBALS['medex_onboarding_dev_mode'] ?? '')));
    if (in_array($global, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }

    $host = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
    if (($pos = strpos($host, ':')) !== false) {
        $host = substr($host, 0, $pos);
    }

    return medexIsPrivateHost($host);
}

function medexValidateCallbackUrl(string $url): array
{
    $url = trim($url);
    $devMode = medexOnboardingDevModeEnabled();
    if ($url === '') {
        return [false, 'OpenEMR URL is required'];
    }

    if ($devMode) {
        if (!preg_match('#^https?://#i', $url)) {
            return [false, 'OpenEMR URL must start with http:// or https:// in developer mode'];
        }
    } elseif (stripos($url, 'https://') !== 0) {
        return [false, 'OpenEMR URL must use HTTPS'];
    }

    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    if ($host === '') {
        return [false, 'OpenEMR URL host is invalid'];
    }
    if (!$devMode && medexIsPrivateHost($host)) {
        return [false, 'OpenEMR URL cannot be a private or local host'];
    }
    return [true, 'ok'];
}

function medexNormalizeOpenEmrBaseUrl(string $url): string
{
    $url = trim($url);
    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) {
        return '';
    }

    $devMode = medexOnboardingDevModeEnabled();
    $scheme = $devMode ? strtolower((string)($parts['scheme'] ?? 'https')) : 'https';
    if (!in_array($scheme, ['http', 'https'], true)) {
        $scheme = 'https';
    }
    $host = strtolower((string)$parts['host']);
    $port = isset($parts['port']) ? ':' . (int)$parts['port'] : '';
    $path = trim((string)($parts['path'] ?? ''), '/');

    // Backward compatibility: if user pastes full callback endpoint, collapse to OpenEMR base.
    $callbackPath = 'interface/modules/custom_modules/oe-module-medex/public/callback.php';
    if ($path !== '' && stripos($path, $callbackPath) !== false) {
        $beforeCallback = substr($path, 0, stripos($path, $callbackPath));
        $path = trim((string)$beforeCallback, '/');
    }

    return $scheme . '://' . $host . $port . ($path !== '' ? '/' . $path : '');
}

function medexResolveClientIp(): string
{
    $candidateHeaders = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_X_CLIENT_IP',
        'REMOTE_ADDR',
    ];

    $parsedIps = [];
    foreach ($candidateHeaders as $header) {
        $raw = trim((string)($_SERVER[$header] ?? ''));
        if ($raw === '') {
            continue;
        }
        $parts = ($header === 'HTTP_X_FORWARDED_FOR') ? explode(',', $raw) : [$raw];
        foreach ($parts as $part) {
            $ip = trim($part);
            if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
                $parsedIps[] = $ip;
            }
        }
    }

    foreach ($parsedIps as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }
    }

    return $parsedIps[0] ?? '';
}

function medexBuildCallbackUrl(string $openEmrBaseUrl): array
{
    $baseUrl = medexNormalizeOpenEmrBaseUrl($openEmrBaseUrl);
    if ($baseUrl === '') {
        return [false, '', '', 'OpenEMR URL is invalid'];
    }

    $tokenRow = QueryUtils::querySingleRow(
        "SELECT gl_value FROM globals WHERE gl_name = 'medex_callback_token' LIMIT 1",
        []
    );
    $token = trim((string)($tokenRow['gl_value'] ?? ''));
    if ($token === '') {
        $token = bin2hex(random_bytes(32));
        QueryUtils::sqlStatementThrowException(
            "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_callback_token', 0, ?)",
            [$token]
        );
    }

    $siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['site'] ?? 'default'));
    if ($siteId === '') {
        $siteId = 'default';
    }
    $callbackBaseUrl = MedExConfig::callbackBaseUrl($baseUrl);
    $callbackUrl = rtrim($callbackBaseUrl, '/') .
        '/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=' .
        rawurlencode($token) .
        '&site=' . rawurlencode($siteId);
    return [true, $baseUrl, $callbackUrl, 'ok'];
}

function medexEnsureAgreementColumns(): void
{
    $alterStatements = [
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_version` varchar(32) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_accepted_at` datetime DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_accepted_ip` varchar(45) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_version` varchar(32) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_accepted_at` datetime DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_accepted_ip` varchar(45) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `agreement_user_agent` varchar(255) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `otp_channel` varchar(20) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `otp_house_account` varchar(50) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `otp_house_cost` decimal(10,4) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `comms_consent_at` datetime DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `comms_consent_ip` varchar(45) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `comms_consent_channel` varchar(20) DEFAULT NULL",
    ];

    foreach ($alterStatements as $sql) {
        try {
            QueryUtils::sqlStatementThrowException($sql, []);
        } catch (\Throwable $e) {
            error_log('[MedEx] agreement schema update skipped: ' . $e->getMessage());
        }
    }
}

function medexEnsureOnboardingAttemptsTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_onboarding_attempts` (
            `email` varchar(190) NOT NULL,
            `fail_count` int(11) NOT NULL DEFAULT 0,
            `window_start` datetime DEFAULT NULL,
            `locked_until` datetime DEFAULT NULL,
            `last_reason` varchar(255) DEFAULT NULL,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexEnsureEmailBlocklistTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_onboarding_email_blocklist` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `match_type` varchar(20) NOT NULL DEFAULT 'email',
            `match_value` varchar(190) NOT NULL,
            `reason` varchar(255) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_match` (`match_type`, `match_value`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexBlockedEmailReason(string $email): string
{
    $email = strtolower(trim($email));
    if ($email === '' || strpos($email, '@') === false) {
        return '';
    }
    $domain = substr(strrchr($email, '@'), 1);
    if ($domain === false || $domain === '') {
        return '';
    }

    $exact = QueryUtils::querySingleRow(
        "SELECT reason FROM medex_onboarding_email_blocklist
         WHERE is_active = 1 AND match_type = 'email' AND LOWER(match_value) = ? LIMIT 1",
        [$email]
    );
    if (!empty($exact)) {
        return (string)($exact['reason'] ?? 'blocked_email');
    }

    $domainHit = QueryUtils::querySingleRow(
        "SELECT reason FROM medex_onboarding_email_blocklist
         WHERE is_active = 1 AND match_type = 'domain' AND LOWER(match_value) = ? LIMIT 1",
        [$domain]
    );
    if (!empty($domainHit)) {
        return (string)($domainHit['reason'] ?? 'blocked_domain');
    }

    return '';
}

function medexGetAttemptState(string $email): array
{
    $row = QueryUtils::querySingleRow(
        "SELECT fail_count, window_start, locked_until, last_reason
         FROM medex_onboarding_attempts WHERE email = ? LIMIT 1",
        [$email]
    );
    return [
        'fail_count' => (int)($row['fail_count'] ?? 0),
        'window_start' => (string)($row['window_start'] ?? ''),
        'locked_until' => (string)($row['locked_until'] ?? ''),
        'last_reason' => (string)($row['last_reason'] ?? ''),
    ];
}

function medexIsLocked(string $email): array
{
    $state = medexGetAttemptState($email);
    if (!empty($state['locked_until']) && strtotime($state['locked_until']) > time()) {
        return [true, $state['locked_until']];
    }
    return [false, ''];
}

function medexRecordAutoApprovalFailure(string $email, string $reason): array
{
    $now = gmdate('Y-m-d H:i:s');
    $windowSeconds = 30 * 60;
    $maxAttempts = 3;
    $lockSeconds = 24 * 60 * 60;

    $state = medexGetAttemptState($email);
    $windowStart = !empty($state['window_start']) ? strtotime($state['window_start']) : 0;
    $count = (int)$state['fail_count'];

    if ($windowStart <= 0 || (time() - $windowStart) > $windowSeconds) {
        $count = 1;
        $windowStartSql = $now;
    } else {
        $count++;
        $windowStartSql = $state['window_start'];
    }

    $lockedUntil = null;
    if ($count >= $maxAttempts) {
        $lockedUntil = gmdate('Y-m-d H:i:s', time() + $lockSeconds);
    }

    QueryUtils::sqlStatementThrowException(
        "INSERT INTO medex_onboarding_attempts (email, fail_count, window_start, locked_until, last_reason, updated_at)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            fail_count = VALUES(fail_count),
            window_start = VALUES(window_start),
            locked_until = VALUES(locked_until),
            last_reason = VALUES(last_reason),
            updated_at = VALUES(updated_at)",
        [$email, $count, $windowStartSql, $lockedUntil, substr($reason, 0, 255), $now]
    );

    return [
        'count' => $count,
        'remaining' => max(0, $maxAttempts - $count),
        'locked' => !empty($lockedUntil),
        'locked_until' => (string)($lockedUntil ?? ''),
    ];
}

function medexClearAutoApprovalFailures(string $email): void
{
    QueryUtils::sqlStatementThrowException(
        "DELETE FROM medex_onboarding_attempts WHERE email = ?",
        [$email]
    );
}

function medexDetectedOpenEmrBaseUrl(): string
{
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    if ($host === '') {
        return '';
    }

    $proto = trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($proto === '') {
        $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
        $proto = ($https === 'on' || $https === '1') ? 'https' : 'http';
    } else {
        $proto = strtolower(explode(',', $proto)[0]);
    }
    if ($proto !== 'https') {
        $proto = 'https';
    }

    $webroot = trim((string)($GLOBALS['webroot'] ?? ''), '/');
    return $proto . '://' . $host . ($webroot !== '' ? '/' . $webroot : '');
}

function medexIsAutoApprovalFailureMessage(string $message): bool
{
    $m = strtolower(trim($message));
    if ($m === '') {
        return false;
    }
    $needles = [
        'unreachable',
        'reachable',
        'auto-approval',
        'auto approval',
        'callback',
        'production',
        'private',
        'local host',
        'site url',
        'pending review',
    ];
    foreach ($needles as $needle) {
        if (strpos($m, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function medexIsExistingAccountMessage(string $message): bool
{
    $m = strtolower(trim($message));
    if ($m === '') {
        return false;
    }
    $needles = [
        'already exists',
        'already registered',
        'already have an account',
        'account already exists',
        'email already',
        'user already',
        'duplicate email',
        'duplicate user',
        'existing account',
        'account exists',
        'ip address mismatch',
        'ip mismatch',
    ];
    foreach ($needles as $needle) {
        if (strpos($m, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function medexNormalizeSmsDestination(string $sms): string
{
    $sms = trim($sms);
    if (preg_match('/^\+\d{10,15}$/', $sms)) {
        return $sms;
    }
    return '';
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

function medexLoadOtpStateFromDb(string $channel, string $destination, string $email): ?array
{
    if ($channel === '' || $destination === '') {
        return null;
    }

    $row = QueryUtils::querySingleRow(
        "SELECT state_json FROM medex_onboarding_otp_state WHERE state_key = ? LIMIT 1",
        [medexOtpStateKey($channel, $destination, $email)]
    );
    $raw = (string)($row['state_json'] ?? '');
    if ($raw === '') {
        return null;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function medexValidateOtpProof(string $email, string $channel, string $smsDestination, string $proof): array
{
    global $session;
    $key = 'medex_onboarding_otp';
    $state = null;
    if (isset($session) && is_object($session) && method_exists($session, 'get')) {
        $state = $session->get($key, null);
    }
    if (!is_array($state)) {
        $raw = $_SESSION[$key] ?? null;
        $state = is_array($raw) ? $raw : null;
    }
    if (!is_array($state)) {
        $destination = ($channel === 'email') ? $email : $smsDestination;
        $state = medexLoadOtpStateFromDb($channel, $destination, $email);
    }
    if (!is_array($state)) {
        return [false, 'Send and verify your one-time password before continuing'];
    }
    if (empty($state['verified']) || empty($state['proof'])) {
        return [false, 'One-time password is not verified'];
    }
    if (!hash_equals((string)$state['proof'], $proof)) {
        return [false, 'One-time password verification proof is invalid'];
    }
    $verifiedExpiresAt = (int)($state['verified_expires_at'] ?? 0);
    if ($verifiedExpiresAt > 0) {
        if ($verifiedExpiresAt < time()) {
            return [false, 'One-time password expired. Send and verify a new code'];
        }
    } elseif (!empty($state['expires_at']) && (int)$state['expires_at'] < time()) {
        return [false, 'One-time password expired. Send and verify a new code'];
    }
    if (($state['channel'] ?? '') !== $channel) {
        return [false, 'One-time password method does not match the verified method'];
    }
    if ($channel === 'email') {
        if (strtolower((string)($state['email'] ?? '')) !== strtolower($email)) {
            return [false, 'One-time password was verified for a different email'];
        }
    } elseif ($channel === 'sms') {
        if (($state['destination'] ?? '') !== $smsDestination) {
            return [false, 'One-time password was verified for a different mobile number'];
        }
    }

    return [true, 'ok'];
}

function medexClearOtpSession(): void
{
    global $session;
    $key = 'medex_onboarding_otp';
    if (isset($session) && is_object($session) && method_exists($session, 'remove')) {
        $session->remove($key);
    }
    unset($_SESSION[$key]);
}

function medexIsRecentOnboardingTimestamp(string $rawTimestamp): bool
{
    $rawTimestamp = trim($rawTimestamp);
    if ($rawTimestamp === '') {
        return false;
    }
    $ts = strtotime($rawTimestamp);
    if ($ts === false || $ts <= 0) {
        return false;
    }
    return (time() - $ts) <= MEDEX_ONBOARDING_VERIFICATION_TTL_SECONDS;
}

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
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

$csrfToken = (string)($_POST['csrf_token_form'] ?? '');
$csrfOk = false;
if ($session) {
    $csrfOk = CsrfUtils::verifyCsrfToken(token: $csrfToken, subject: 'default', session: $session) ||
        CsrfUtils::verifyCsrfToken(token: $csrfToken, subject: 'api', session: $session);
} else {
    $csrfOk = CsrfUtils::verifyCsrfToken($csrfToken, 'default') ||
        CsrfUtils::verifyCsrfToken($csrfToken, 'api');
}
if (!$csrfOk) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    // Load MedEx API and Services
    require_once(__DIR__ . '/../src/MedExAPI.php');
    require_once(__DIR__ . '/../src/Services/PracticeService.php');
    medexEnsureOnboardingAttemptsTable();
    medexEnsureEmailBlocklistTable();

    // Validate required fields (only email and password - practice details come from facility sync)
    $required = ['email', 'password', 'callback_url', 'TERMS_yes', 'BusAgree_yes', 'otp_proof'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
            exit;
        }
    }
    if ((string)($_POST['TERMS_yes'] ?? '') !== '1') {
        echo json_encode(['success' => false, 'error' => 'You must agree to the Terms & Conditions before signing up']);
        exit;
    }
    if ((string)($_POST['BusAgree_yes'] ?? '') !== '1') {
        echo json_encode(['success' => false, 'error' => 'You must agree to the HIPAA Business Associate Agreement before signing up']);
        exit;
    }
    $hasCommsConsent = ((string)($_POST['comms_consent'] ?? '0') === '1');
    $password = (string)($_POST['password'] ?? '');
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
        echo json_encode([
            'success' => false,
            'error' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character'
        ]);
        exit;
    }
    $email = trim((string)($_POST['email'] ?? ''));
    if ($email === '') {
        echo json_encode(['success' => false, 'error' => 'E-mail is required']);
        exit;
    }
    $blockedReason = medexBlockedEmailReason($email);
    if ($blockedReason !== '') {
        echo json_encode([
            'success' => false,
            'pending_review' => true,
            'error' => 'Auto-approval is not available for this email. Please contact support@medexbank.com for review.'
        ]);
        exit;
    }
    [$isLocked, $lockedUntil] = medexIsLocked($email);
    if ($isLocked) {
        echo json_encode([
            'success' => false,
            'error' => 'Auto-approval is temporarily disabled due to repeated unreachable/mismatch checks. Please contact support@medexbank.com or retry after ' . $lockedUntil . ' UTC.'
        ]);
        exit;
    }
    $otpChannel = strtolower(trim((string)($_POST['otp_channel'] ?? 'email')));
    $allowedOtpChannels = ['email', 'sms', 'whatsapp'];
    if (!in_array($otpChannel, $allowedOtpChannels, true)) {
        echo json_encode(['success' => false, 'error' => 'Invalid OTP channel selected']);
        exit;
    }
    if ($otpChannel === 'whatsapp' && !MedExConfig::OTP_WHATSAPP_ENABLED) {
        echo json_encode(['success' => false, 'error' => 'WhatsApp OTP is not enabled yet']);
        exit;
    }

    $otpHouseAccount = match ($otpChannel) {
        'sms' => MedExConfig::OTP_HOUSE_ACCOUNT_SMS,
        'whatsapp' => MedExConfig::OTP_HOUSE_ACCOUNT_WHATSAPP,
        default => MedExConfig::OTP_HOUSE_ACCOUNT_EMAIL,
    };
    $otpHouseCost = ($otpChannel === 'email') ? (float) MedExConfig::OTP_HOUSE_EMAIL_COST : 0.0;
    $otpSmsDestination = medexNormalizeSmsDestination((string)($_POST['otp_sms_destination'] ?? ''));
    if ($otpChannel === 'sms' && $otpSmsDestination === '') {
        echo json_encode(['success' => false, 'error' => 'A valid SMS number is required for SMS one-time password verification']);
        exit;
    }
    $otpProof = trim((string)($_POST['otp_proof'] ?? ''));
    [$otpProofOk, $otpProofErr] = medexValidateOtpProof($email, $otpChannel, $otpSmsDestination, $otpProof);
    if (!$otpProofOk) {
        echo json_encode(['success' => false, 'error' => $otpProofErr]);
        exit;
    }
    $termsVersion = trim((string)($_POST['terms_version'] ?? MedExConfig::TERMS_VERSION));
    $baaVersion = trim((string)($_POST['baa_version'] ?? MedExConfig::BAA_VERSION));
    if ($termsVersion === '' || $termsVersion !== MedExConfig::TERMS_VERSION) {
        echo json_encode([
            'success' => false,
            'refresh_required' => true,
            'current_terms_version' => MedExConfig::TERMS_VERSION,
            'current_baa_version' => MedExConfig::BAA_VERSION,
            'error' => 'Terms and Conditions version mismatch. Refresh and review current Terms.'
        ]);
        exit;
    }
    if ($baaVersion === '' || $baaVersion !== MedExConfig::BAA_VERSION) {
        echo json_encode([
            'success' => false,
            'refresh_required' => true,
            'current_terms_version' => MedExConfig::TERMS_VERSION,
            'current_baa_version' => MedExConfig::BAA_VERSION,
            'error' => 'Business Associate Agreement version mismatch. Refresh and review current BAA.'
        ]);
        exit;
    }
    $termsSignedAt = trim((string)($_POST['terms_signed_at'] ?? ''));
    if (!medexIsRecentOnboardingTimestamp($termsSignedAt)) {
        echo json_encode([
            'success' => false,
            'error' => 'Terms and Conditions signature expired. Re-open and sign the current Terms again.'
        ]);
        exit;
    }
    $baaSignedAt = trim((string)($_POST['baa_signed_at'] ?? ''));
    if (!medexIsRecentOnboardingTimestamp($baaSignedAt)) {
        echo json_encode([
            'success' => false,
            'error' => 'Business Associate Agreement signature expired. Re-open and sign the current BAA again.'
        ]);
        exit;
    }

    $submittedOpenEmrUrl = trim((string)($_POST['callback_url'] ?? ''));
    [$callbackOk, $callbackErr] = medexValidateCallbackUrl($submittedOpenEmrUrl);
    if (!$callbackOk) {
        echo json_encode(['success' => false, 'error' => $callbackErr]);
        exit;
    }
    $submittedHost = strtolower((string)(parse_url($submittedOpenEmrUrl, PHP_URL_HOST) ?? ''));
    $currentHost = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
    if (($hostPos = strpos($currentHost, ':')) !== false) {
        $currentHost = substr($currentHost, 0, $hostPos);
    }
    if ($submittedHost === '' || $currentHost === '' || $submittedHost !== $currentHost) {
        echo json_encode(['success' => false, 'error' => 'OpenEMR URL must match this server URL']);
        exit;
    }
    [$derivedOk, $openEmrBaseUrl, $derivedCallbackUrl, $deriveErr] = medexBuildCallbackUrl($submittedOpenEmrUrl);
    if (!$derivedOk) {
        echo json_encode(['success' => false, 'error' => $deriveErr]);
        exit;
    }
    $detectedBaseUrl = medexNormalizeOpenEmrBaseUrl(medexDetectedOpenEmrBaseUrl());
    if ($detectedBaseUrl === '') {
        $attempt = medexRecordAutoApprovalFailure($email, 'missing_detected_site_url');
        $msg = 'Auto-approval unavailable: site URL could not be detected from current request headers.';
        if ($attempt['locked']) {
            $msg .= ' Too many failed attempts. Please contact support@medexbank.com.';
        } else {
            $msg .= ' Attempts remaining before support is required: ' . $attempt['remaining'] . '.';
        }
        echo json_encode(['success' => false, 'error' => $msg, 'pending_review' => true]);
        exit;
    }
    if ($detectedBaseUrl !== $openEmrBaseUrl) {
        $attempt = medexRecordAutoApprovalFailure($email, 'submitted_url_mismatch');
        $msg = 'Auto-approval unavailable: submitted OpenEMR URL does not match detected site URL.';
        if ($attempt['locked']) {
            $msg .= ' Too many failed attempts. Please contact support@medexbank.com.';
        } else {
            $msg .= ' Attempts remaining before support is required: ' . $attempt['remaining'] . '.';
        }
        echo json_encode(['success' => false, 'error' => $msg, 'pending_review' => true]);
        exit;
    }

// Create API instance
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Get primary facility details as default practice info
$facility = sqlQuery("SELECT name, phone, street, city, state, postal_code, country_code FROM facility WHERE primary_business_entity = 1 ORDER BY id LIMIT 1");
if (!$facility) {
    $facility = sqlQuery("SELECT name, phone, street, city, state, postal_code, country_code FROM facility ORDER BY id LIMIT 1");
}

$practice_name = $facility['name'] ?? $GLOBALS['openemr_name'] ?? 'OpenEMR Practice';
$practice_phone = $facility['phone'] ?? '';
$practice_street = trim($facility['street'] ?? '');
$practice_city = trim($facility['city'] ?? '');
$practice_state = trim($facility['state'] ?? '');
$practice_postcode = trim($facility['postal_code'] ?? '');
$practice_country_code = strtoupper(trim($facility['country_code'] ?? 'US'));
$providerCountRow = sqlQuery("SELECT COUNT(*) AS c FROM users WHERE authorized = 1 AND active = 1");
$facilityCountRow = sqlQuery("SELECT COUNT(*) AS c FROM facility WHERE service_location = 1");
$insuranceCountRow = sqlQuery("SELECT COUNT(*) AS c FROM insurance_companies");
$siteUrl = $openEmrBaseUrl;
$requestIp = medexResolveClientIp();
$requestUserAgent = substr(trim((string)($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 255);
$acceptedAtUtc = gmdate('Y-m-d H:i:s');

// Prepare registration data
    $data = [
    'email' => $email,
    'password' => $_POST['password'],
    'practice_name' => $practice_name,
    'phone' => $practice_phone,
    'address' => $practice_street,
    'street' => $practice_street,
    'city' => $practice_city,
    'state' => $practice_state,
    'postcode' => $practice_postcode,
    'country_code' => $practice_country_code,
    'callback_url' => $derivedCallbackUrl,
    'site_url' => $siteUrl,
    'provider_count' => (int)($providerCountRow['c'] ?? 0),
    'facility_count' => (int)($facilityCountRow['c'] ?? 0),
    'insurance_count' => (int)($insuranceCountRow['c'] ?? 0),
    'ehr' => 'OpenEMR',
    'ehr_version' => $GLOBALS['v_major'] . '.' . $GLOBALS['v_minor'] . '.' . $GLOBALS['v_patch'],
    'terms_accepted' => true,
    'terms_version' => $termsVersion,
    'terms_accepted_at_utc' => $acceptedAtUtc,
    'baa_accepted' => true,
    'baa_version' => $baaVersion,
    'baa_accepted_at_utc' => $acceptedAtUtc,
    'agreement_ip' => $requestIp
    ,'otp_channel' => $otpChannel
    ,'otp_house_account' => $otpHouseAccount
    ,'otp_house_cost' => $otpHouseCost
    ,'comms_consent_at_utc' => ($hasCommsConsent ? $acceptedAtUtc : null)
    ,'comms_consent_ip' => ($hasCommsConsent ? $requestIp : null)
    ,'comms_consent_channel' => ($hasCommsConsent ? $otpChannel : null)
];

// Attempt registration
$result = $api->register($data);

if (empty($result['success']) && !empty($result['error']) && medexIsExistingAccountMessage((string)$result['error'])) {
    $siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['site'] ?? 'default'));
    if ($siteId === '') {
        $siteId = 'default';
    }
    $webroot = (string)($GLOBALS['webroot'] ?? '');
    $result['existing_account'] = true;
    $result['reconnect_url'] = $webroot
        . '/interface/modules/custom_modules/oe-module-medex/admin/reconnect.php?site='
        . rawurlencode($siteId)
        . '&email=' . rawurlencode($email);
    if (empty($result['error']) || medexIsExistingAccountMessage((string)$result['error'])) {
        $result['error'] = 'A MedEx account already exists for this email. Reconnect it instead.';
    }
}

// If registration successful, perform initial practice sync
if (!empty($result['success'])) {
    medexEnsureAgreementColumns();

    // Pre-fetch and DB-cache pricing immediately so the Services tab never hits the server on first open.
    // This is a fire-and-forget; failure is non-fatal — getPricing() has built-in defaults.
    try {
        $api->getPricing();
    } catch (\Exception $e) {
        error_log('[MedEx] Non-fatal: could not pre-cache pricing on registration: ' . $e->getMessage());
    }

    // Auto-configure all facilities and providers with calendars on first registration

    // Get all facilities
    $facility_records = \OpenEMR\Common\Database\QueryUtils::fetchRecords("SELECT id FROM facility WHERE service_location = 1 ORDER BY id");
    $facility_ids = [];
    foreach ($facility_records as $fac) {
        $facility_ids[] = $fac['id'];
    }

    // Get all providers who have calendars
    $provider_records = \OpenEMR\Common\Database\QueryUtils::fetchRecords("
        SELECT DISTINCT u.id
        FROM users u
        WHERE u.authorized = 1
        AND u.active = 1
        AND u.calendar = 1
        ORDER BY u.id
    ");
    $provider_ids = [];
    foreach ($provider_records as $prov) {
        $provider_ids[] = $prov['id'];
    }

    // medex_prefs row was already written by MedExAPI::register() with the full api_key.
    // Just update facilities/providers on that row (never overwrite api_key here — globals.gl_value
    // is varchar(255) and would truncate it; medex_prefs.ME_api_key is TEXT and holds the full key).
    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "UPDATE medex_prefs SET
            ME_facilities = ?,
            ME_providers = ?,
            terms_version = ?,
            terms_accepted_at = ?,
            terms_accepted_ip = ?,
            baa_version = ?,
            baa_accepted_at = ?,
            baa_accepted_ip = ?,
            agreement_user_agent = ?,
            otp_channel = ?,
            otp_house_account = ?,
            otp_house_cost = ?,
            comms_consent_at = ?,
            comms_consent_ip = ?,
            comms_consent_channel = ?,
            MedEx_lastupdated = NOW()
         WHERE ME_username = ?",
        [
            !empty($facility_ids) ? implode('|', $facility_ids) : '',
            !empty($provider_ids) ? implode('|', $provider_ids) : '',
            $termsVersion,
            $acceptedAtUtc,
            $requestIp,
            $baaVersion,
            $acceptedAtUtc,
            $requestIp,
            $requestUserAgent,
            $otpChannel,
            $otpHouseAccount,
            $otpHouseCost,
            ($hasCommsConsent ? $acceptedAtUtc : null),
            ($hasCommsConsent ? $requestIp : null),
            ($hasCommsConsent ? $otpChannel : null),
            $data['email']
        ]
    );

    // Background services are not used by the module. External sync is managed outside OpenEMR.

    // Now perform initial sync with all facilities and providers
    if (!empty($facility_ids) || !empty($provider_ids)) {
        $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);
        $syncResult = $practiceService->performInitialSync();

        // Add sync status to result
        $result['sync_performed'] = true;
        $result['sync_success'] = $syncResult['success'] ?? false;
        $result['facilities_synced'] = count($facility_ids);
        $result['providers_synced'] = count($provider_ids);

        if (!empty($syncResult['error'])) {
            $result['sync_error'] = $syncResult['error'];
        }
    } else {
        $result['sync_performed'] = false;
        $result['sync_message'] = 'No facilities or providers with calendars found to sync';
    }
    medexClearAutoApprovalFailures($email);
    medexClearOtpSession();
} elseif (!empty($result['pending_review'])) {
    $reviewMsg = $result['message'] ?? 'Signup pending review by MedEx support.';
    if (medexIsAutoApprovalFailureMessage($reviewMsg)) {
        $attempt = medexRecordAutoApprovalFailure($email, 'auto_approval_pending_review');
        if ($attempt['locked']) {
            $reviewMsg .= ' Too many failed auto-approval attempts. Please contact support@medexbank.com.';
        } else {
            $reviewMsg .= ' Attempts remaining before support is required: ' . $attempt['remaining'] . '.';
        }
    }
    $result['success'] = false;
    $result['error'] = $reviewMsg;
}

    // Return result
    echo json_encode($result);

} catch (\Exception $e) {
    error_log("Registration process error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'Registration error: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
} catch (\Error $e) {
    error_log("Registration process fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
}
