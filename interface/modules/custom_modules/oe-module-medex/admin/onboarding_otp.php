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

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', 'default')) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

function medexOtpSessionKey(): string
{
    return 'medex_onboarding_otp';
}

function medexNormalizeOtpChannel(string $channel): string
{
    $channel = strtolower(trim($channel));
    return in_array($channel, ['email', 'sms'], true) ? $channel : '';
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

    if (!preg_match('/^\+(\d{1,3})(\d{7,14})$/', $sms, $m)) {
        return [false, '', 'invalid_number_format'];
    }

    // Basic code extraction (NANP +1 currently supported for onboarding OTP)
    $countryCallingCode = $m[1];
    $national = $m[2];

    if ($countryCallingCode !== '1') {
        // Explicitly reject non-NANP for now (e.g., +91 India).
        return [false, $countryCallingCode, 'country_not_allowed'];
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

function medexSendOtpThroughApi(string $channel, string $destination, string $code): array
{
    $api = new \OpenEMR\Modules\MedEx\MedExAPI();
    $message = 'Your MedEx one-time password is ' . $code . '. It expires in 10 minutes.';
    $subject = 'Your MedEx One-Time Password';

    try {
        $response = $api->makeRequest(
            '/index.php?route=api/send_secure_chat_link',
            [
                'practice_id' => 'onboarding',
                'pid' => 'onboarding',
                'destination' => $destination,
                'method' => $channel,
                'message' => $message,
                'subject' => $subject,
                'type' => 'onboarding_otp'
            ],
            'POST'
        );

        if (!empty($response['success'])) {
            return [true, 'One-time password sent.'];
        }

        $err = (string)($response['error'] ?? 'Unable to send one-time password');
        return [false, $err];
    } catch (\Throwable $e) {
        error_log('[MedEx OTP] send error: ' . $e->getMessage());
        return [false, 'Unable to send one-time password right now.'];
    }
}

$action = strtolower(trim((string)($_POST['action'] ?? '')));
if (!in_array($action, ['send', 'verify'], true)) {
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

if ($action === 'send') {
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
    [$ok, $msg] = medexSendOtpThroughApi($channel, $destination, $code);
    if (!$ok) {
        medexAuditOtpDecision('send', $channel, $destination, ($channel === 'sms' ? '1' : ''), 'reject', 'provider_send_failed');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    medexAuditOtpDecision('send', $channel, $destination, ($channel === 'sms' ? '1' : ''), 'allow', 'sent');

    $_SESSION[medexOtpSessionKey()] = [
        'channel' => $channel,
        'destination' => $destination,
        'email' => $email,
        'code_hash' => password_hash($code, PASSWORD_DEFAULT),
        'sent_at' => time(),
        'expires_at' => time() + (10 * 60),
        'attempts' => 0,
        'verified' => false,
        'proof' => '',
    ];

    echo json_encode(['success' => true, 'message' => 'One-time password sent.']);
    exit;
}

$state = $_SESSION[medexOtpSessionKey()] ?? null;
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
if ((int)($state['expires_at'] ?? 0) < time()) {
    unset($_SESSION[medexOtpSessionKey()]);
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
    unset($_SESSION[medexOtpSessionKey()]);
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'too_many_attempts');
    echo json_encode(['success' => false, 'error' => 'Too many invalid attempts. Send a new one-time password.']);
    exit;
}

if (!password_verify($code, (string)($state['code_hash'] ?? ''))) {
    $_SESSION[medexOtpSessionKey()] = $state;
    medexAuditOtpDecision('verify', $channel, $destination, '', 'reject', 'incorrect_code');
    echo json_encode(['success' => false, 'error' => 'Incorrect one-time password']);
    exit;
}

$proof = bin2hex(random_bytes(16));
$state['verified'] = true;
$state['verified_at'] = time();
$state['proof'] = $proof;
$_SESSION[medexOtpSessionKey()] = $state;
medexAuditOtpDecision('verify', $channel, $destination, '', 'allow', 'verified');

echo json_encode([
    'success' => true,
    'otp_proof' => $proof,
    'message' => 'One-time password verified.'
]);
exit;
