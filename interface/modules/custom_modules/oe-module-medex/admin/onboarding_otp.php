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
if ($destination === '') {
    if ($channel === 'email') {
        echo json_encode(['success' => false, 'error' => 'Valid administrator email is required']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Valid SMS number is required in +15551234567 format']);
    }
    exit;
}

if ($action === 'send') {
    $code = (string)random_int(100000, 999999);
    [$ok, $msg] = medexSendOtpThroughApi($channel, $destination, $code);
    if (!$ok) {
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

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
    echo json_encode(['success' => false, 'error' => 'Send one-time password first']);
    exit;
}
if (($state['channel'] ?? '') !== $channel || ($state['destination'] ?? '') !== $destination) {
    echo json_encode(['success' => false, 'error' => 'One-time password destination has changed. Send a new code.']);
    exit;
}
if ((int)($state['expires_at'] ?? 0) < time()) {
    unset($_SESSION[medexOtpSessionKey()]);
    echo json_encode(['success' => false, 'error' => 'One-time password expired. Send a new code.']);
    exit;
}

$code = trim((string)($_POST['otp_code'] ?? ''));
if (!preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['success' => false, 'error' => 'Enter a valid 6-digit one-time password']);
    exit;
}

$attempts = (int)($state['attempts'] ?? 0) + 1;
$state['attempts'] = $attempts;
if ($attempts > 5) {
    unset($_SESSION[medexOtpSessionKey()]);
    echo json_encode(['success' => false, 'error' => 'Too many invalid attempts. Send a new one-time password.']);
    exit;
}

if (!password_verify($code, (string)($state['code_hash'] ?? ''))) {
    $_SESSION[medexOtpSessionKey()] = $state;
    echo json_encode(['success' => false, 'error' => 'Incorrect one-time password']);
    exit;
}

$proof = bin2hex(random_bytes(16));
$state['verified'] = true;
$state['verified_at'] = time();
$state['proof'] = $proof;
$_SESSION[medexOtpSessionKey()] = $state;

echo json_encode([
    'success' => true,
    'otp_proof' => $proof,
    'message' => 'One-time password verified.'
]);
exit;
