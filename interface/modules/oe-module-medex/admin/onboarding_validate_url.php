<?php
/**
 * MedEx Module - Onboarding OpenEMR URL validation
 *
 * Validates URL format and probes callback install from MedEx API side.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

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

function medexIsPrivateHostValidate(string $host): bool
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

function medexOnboardingDevModeEnabledValidate(): bool
{
    $env = strtolower(trim((string)getenv('MEDEX_ONBOARDING_DEV_MODE')));
    if (in_array($env, ['1', 'true', 'yes', 'on'], true)) {
        return true;
    }
    $global = strtolower(trim((string)($GLOBALS['medex_onboarding_dev_mode'] ?? '')));
    return in_array($global, ['1', 'true', 'yes', 'on'], true);
}

function medexNormalizeOpenEmrBaseUrlValidate(string $url): string
{
    $url = trim($url);
    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) {
        return '';
    }
    $devMode = medexOnboardingDevModeEnabledValidate();
    $scheme = $devMode ? strtolower((string)($parts['scheme'] ?? 'https')) : 'https';
    if (!in_array($scheme, ['http', 'https'], true)) {
        $scheme = 'https';
    }
    $host = strtolower((string)$parts['host']);
    $port = isset($parts['port']) ? ':' . (int)$parts['port'] : '';
    $path = trim((string)($parts['path'] ?? ''), '/');
    $callbackPath = 'interface/modules/custom_modules/oe-module-medex/public/callback.php';
    if ($path !== '' && stripos($path, $callbackPath) !== false) {
        $beforeCallback = substr($path, 0, stripos($path, $callbackPath));
        $path = trim((string)$beforeCallback, '/');
    }
    return $scheme . '://' . $host . $port . ($path !== '' ? '/' . $path : '');
}

function medexBuildCallbackUrlValidate(string $openEmrBaseUrl): array
{
    $baseUrl = medexNormalizeOpenEmrBaseUrlValidate($openEmrBaseUrl);
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
    $callbackUrl = rtrim($baseUrl, '/') .
        '/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=' .
        rawurlencode($token) .
        '&site=' . rawurlencode($siteId);
    return [true, $baseUrl, $callbackUrl, 'ok'];
}

$submitted = trim((string)($_POST['callback_url'] ?? ''));
$devMode = medexOnboardingDevModeEnabledValidate();
if ($submitted === '') {
    echo json_encode(['success' => false, 'error' => 'OpenEMR URL is required']);
    exit;
}

if ($devMode) {
    if (!preg_match('#^https?://#i', $submitted)) {
        echo json_encode(['success' => false, 'error' => 'OpenEMR URL must start with http:// or https:// in developer mode']);
        exit;
    }
} elseif (stripos($submitted, 'https://') !== 0) {
    echo json_encode(['success' => false, 'error' => 'OpenEMR URL must use HTTPS']);
    exit;
}

$parts = parse_url($submitted);
$host = strtolower((string)($parts['host'] ?? ''));
if ($host === '') {
    echo json_encode(['success' => false, 'error' => 'OpenEMR URL host is invalid']);
    exit;
}

if (!$devMode && medexIsPrivateHostValidate($host)) {
    echo json_encode(['success' => false, 'error' => 'OpenEMR URL cannot be a private or local host']);
    exit;
}

[$buildOk, $baseUrl, $callbackUrl, $buildErr] = medexBuildCallbackUrlValidate($submitted);
if (!$buildOk) {
    echo json_encode(['success' => false, 'error' => $buildErr]);
    exit;
}

$submittedHost = strtolower((string)(parse_url($baseUrl, PHP_URL_HOST) ?? ''));
$currentHost = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
if (($pos = strpos($currentHost, ':')) !== false) {
    $currentHost = substr($currentHost, 0, $pos);
}
if ($submittedHost === '' || $currentHost === '' || $submittedHost !== $currentHost) {
    echo json_encode([
        'success' => false,
        'error' => 'OpenEMR URL must match this server URL.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => $devMode ? 'OpenEMR URL accepted (developer mode).' : 'OpenEMR URL accepted.',
    'openemr_url' => $baseUrl
]);
exit;
