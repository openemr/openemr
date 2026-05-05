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
use Mpdf\Mpdf;
use OpenEMR\Pdf\Config_Mpdf;

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
    return in_array($global, ['1', 'true', 'yes', 'on'], true);
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

    $callbackUrl = rtrim($baseUrl, '/') .
        '/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=' .
        rawurlencode($token) .
        '&site=' . rawurlencode($siteId);
    return [true, $baseUrl, $callbackUrl, 'ok'];
}

function medexProbeDerivedCallbackUrl(string $callbackUrl): array
{
    if (medexOnboardingDevModeEnabled()) {
        return [true, 'dev_mode_probe_skipped'];
    }

    $parts = parse_url($callbackUrl);
    if (!$parts || empty($parts['host']) || empty($parts['path'])) {
        return [false, 'invalid_callback_url'];
    }
    $query = [];
    if (!empty($parts['query'])) {
        parse_str((string)$parts['query'], $query);
    }
    $token = trim((string)($query['token'] ?? ''));
    unset($query['token']);
    if ($token === '') {
        return [false, 'missing_callback_token'];
    }
    if (empty($query['site'])) {
        $query['site'] = 'default';
    }
    $scheme = strtolower((string)($parts['scheme'] ?? 'https'));
    $host = strtolower((string)$parts['host']);
    $port = isset($parts['port']) ? ':' . (int)$parts['port'] : '';
    $targetUrl = $scheme . '://' . $host . $port . (string)$parts['path'] . '?' . http_build_query($query);

    $payload = http_build_query(['action' => 'ping']);
    $timestamp = (string)time();
    $nonce = bin2hex(random_bytes(16));
    $signature = hash_hmac('sha256', $timestamp . "\n" . $nonce . "\n" . $payload, $token);
    $isHttps = ($scheme === 'https');
    $host = strtolower((string)($parts['host'] ?? ''));

    $ch = curl_init($targetUrl);
    if (!$ch) {
        return [false, 'probe_init_failed'];
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'X-MEDEX-TOKEN: ' . $token,
            'X-MEDEX-TIMESTAMP: ' . $timestamp,
            'X-MEDEX-NONCE: ' . $nonce,
            'X-MEDEX-SIGNATURE: ' . $signature,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => $isHttps ? true : false,
        CURLOPT_SSL_VERIFYHOST => $isHttps ? 2 : 0,
    ]);
    $body = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = trim((string)curl_error($ch));
    curl_close($ch);

    if ($curlErr !== '') {
        $requestHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        if (($hostPos = strpos($requestHost, ':')) !== false) {
            $requestHost = substr($requestHost, 0, $hostPos);
        }
        $sameHost = ($requestHost !== '' && $host === $requestHost);
        if ($sameHost && stripos($curlErr, 'connection reset by peer') !== false) {
            // Some k8s/LB paths reject pod->public-FQDN TLS hairpin. Probe locally as fallback.
            $localTargetUrl = 'http://127.0.0.1' . (string)$parts['path'] . '?' . http_build_query($query);
            $chLocal = curl_init($localTargetUrl);
            if ($chLocal) {
                curl_setopt_array($chLocal, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/x-www-form-urlencoded',
                        'X-MEDEX-TOKEN: ' . $token,
                        'X-MEDEX-TIMESTAMP: ' . $timestamp,
                        'X-MEDEX-NONCE: ' . $nonce,
                        'X-MEDEX-SIGNATURE: ' . $signature,
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 5,
                ]);
                $localBody = curl_exec($chLocal);
                $localHttpCode = (int)curl_getinfo($chLocal, CURLINFO_HTTP_CODE);
                $localErr = trim((string)curl_error($chLocal));
                curl_close($chLocal);
                if ($localErr === '' && $localHttpCode >= 200 && $localHttpCode < 300) {
                    $localJson = json_decode((string)$localBody, true);
                    if (!empty($localJson['success'])) {
                        return [true, 'ok_local_probe'];
                    }
                }
            }
        }
        return [false, 'probe_transport_error: ' . $curlErr];
    }
    if ($httpCode < 200 || $httpCode >= 300) {
        return [false, 'probe_http_' . $httpCode];
    }
    $json = json_decode((string)$body, true);
    if (empty($json['success'])) {
        return [false, 'probe_invalid_response'];
    }
    return [true, 'ok'];
}

function medexEnsureAgreementColumns(): void
{
    $alterStatements = [
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_version` varchar(32) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_accepted_at` datetime DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_accepted_ip` varchar(45) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_signer_name` varchar(190) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_signer_title` varchar(190) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_signed_at` datetime DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_version` varchar(32) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_accepted_at` datetime DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_accepted_ip` varchar(45) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_signer_name` varchar(190) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_signer_title` varchar(190) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_signed_at` datetime DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `agreement_user_agent` varchar(255) DEFAULT NULL",
        "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `agreement_legal_corporate_name` varchar(255) DEFAULT NULL",
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

function medexEnsureSignedAgreementsTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_signed_agreements` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `practice_id` varchar(64) NOT NULL,
            `customer_id` varchar(64) DEFAULT NULL,
            `agreement_type` varchar(20) NOT NULL,
            `agreement_version` varchar(32) NOT NULL,
            `practice_name` varchar(255) NOT NULL,
            `signer_name` varchar(190) NOT NULL,
            `signer_title` varchar(190) DEFAULT NULL,
            `signer_email` varchar(190) DEFAULT NULL,
            `signed_at` datetime NOT NULL,
            `accepted_ip` varchar(45) DEFAULT NULL,
            `user_agent` varchar(255) DEFAULT NULL,
            `document_html` longtext NOT NULL,
            `pdf_blob` longblob DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_practice_type` (`practice_id`, `agreement_type`),
            KEY `idx_signed_at` (`signed_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexFetchAgreementDocumentHtml(int $informationId): string
{
    $url = rtrim(MedExConfig::mainSiteUrl(), '/') . '/cart/upload/index.php?route=information/information/agree&information_id=' . $informationId;
    $raw = '';
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch) {
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_USERAGENT => 'MedEx-Onboarding/1.0',
            ]);
            $body = curl_exec($ch);
            $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (is_string($body) && $http >= 200 && $http < 300) {
                $raw = $body;
            }
        }
    }
    if (trim($raw) === '' && ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'header' => "User-Agent: MedEx-Onboarding/1.0\r\n",
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if (is_string($body)) {
            $raw = $body;
        }
    }
    return trim($raw);
}

function medexRemoveLegacyPrintInstruction(string $html): string
{
    $patterns = [
        '/Print a copy\s*Sign and return a copy to MedEx\s*support@medexbank\.com\.?/i',
        '/Print a copy\.?/i',
        '/Sign and return a copy to MedEx\s*support@medexbank\.com\.?/i',
    ];
    foreach ($patterns as $pattern) {
        $html = preg_replace($pattern, '', $html) ?? $html;
    }
    return $html;
}

function medexBuildSignedAgreementHtml(array $meta, string $agreementBodyHtml): string
{
    $title = htmlspecialchars((string)$meta['title'], ENT_QUOTES, 'UTF-8');
    $practiceName = htmlspecialchars((string)$meta['practice_name'], ENT_QUOTES, 'UTF-8');
    $version = htmlspecialchars((string)$meta['version'], ENT_QUOTES, 'UTF-8');
    $signerName = htmlspecialchars((string)$meta['signer_name'], ENT_QUOTES, 'UTF-8');
    $signerTitle = htmlspecialchars((string)$meta['signer_title'], ENT_QUOTES, 'UTF-8');
    $signedAt = htmlspecialchars((string)$meta['signed_at_display'], ENT_QUOTES, 'UTF-8');

    return '<!DOCTYPE html><html><head><meta charset="utf-8">'
        . '<title>' . $title . ' - Signed</title>'
        . '<style>'
        . 'body{font-family:Segoe UI,Arial,sans-serif;color:#0f172a;margin:24px;line-height:1.5;}'
        . 'h1{margin:0 0 8px;color:#0f4b8f;font-size:26px;}'
        . '.meta{margin:0 0 16px;padding:12px;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc;}'
        . '.meta-row{margin:2px 0;}'
        . '.meta-label{display:inline-block;min-width:170px;color:#475569;}'
        . '.agreement{margin-top:14px;}'
        . '</style></head><body>'
        . '<h1>' . $title . '</h1>'
        . '<div class="meta">'
        . '<div class="meta-row"><span class="meta-label">Company:</span>' . $practiceName . '</div>'
        . '<div class="meta-row"><span class="meta-label">Agreement Version:</span>' . $version . '</div>'
        . '<div class="meta-row"><span class="meta-label">Signer Name:</span>' . $signerName . '</div>'
        . '<div class="meta-row"><span class="meta-label">Signer Title:</span>' . $signerTitle . '</div>'
        . '<div class="meta-row"><span class="meta-label">Signed At (UTC):</span>' . $signedAt . '</div>'
        . '</div>'
        . '<div class="agreement">' . $agreementBodyHtml . '</div>'
        . '</body></html>';
}

function medexGeneratePdfBlob(string $html): ?string
{
    try {
        $config = Config_Mpdf::getConfigMpdf();
        $pdf = new Mpdf($config);
        $pdf->WriteHTML($html);
        $bin = $pdf->Output('', 'S');
        return is_string($bin) && $bin !== '' ? $bin : null;
    } catch (\Throwable $e) {
        error_log('[MedEx] Agreement PDF generation failed: ' . $e->getMessage());
        return null;
    }
}

function medexPersistSignedAgreement(
    string $practiceId,
    string $customerId,
    string $agreementType,
    string $agreementVersion,
    string $practiceName,
    string $signerName,
    string $signerTitle,
    string $signerEmail,
    string $signedAtUtc,
    string $acceptedIp,
    string $userAgent
): void {
    $informationId = ($agreementType === 'baa') ? 8 : 5;
    $title = ($agreementType === 'baa') ? 'MedEx Business Associate Agreement (BAA)' : 'MedEx Terms and Conditions';
    $agreementBodyHtml = medexFetchAgreementDocumentHtml($informationId);
    if ($agreementBodyHtml === '') {
        $agreementBodyHtml = '<p>Agreement content unavailable at signing time.</p>';
    }
    $agreementBodyHtml = medexRemoveLegacyPrintInstruction($agreementBodyHtml);
    $signedHtml = medexBuildSignedAgreementHtml([
        'title' => $title,
        'practice_name' => $practiceName,
        'version' => $agreementVersion,
        'signer_name' => $signerName,
        'signer_title' => $signerTitle,
        'signed_at_display' => $signedAtUtc,
    ], $agreementBodyHtml);
    $pdfBlob = medexGeneratePdfBlob($signedHtml);

    QueryUtils::sqlStatementThrowException(
        "INSERT INTO medex_signed_agreements
        (practice_id, customer_id, agreement_type, agreement_version, practice_name, signer_name, signer_title, signer_email, signed_at, accepted_ip, user_agent, document_html, pdf_blob)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $practiceId,
            $customerId !== '' ? $customerId : null,
            $agreementType,
            $agreementVersion,
            $practiceName,
            $signerName,
            $signerTitle !== '' ? $signerTitle : null,
            $signerEmail !== '' ? $signerEmail : null,
            $signedAtUtc,
            $acceptedIp !== '' ? $acceptedIp : null,
            $userAgent !== '' ? $userAgent : null,
            $signedHtml,
            $pdfBlob,
        ]
    );
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
    if (!medexOnboardingDevModeEnabled() && $proto !== 'https') {
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
    if (!empty($state['expires_at']) && (int)$state['expires_at'] < time()) {
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

function medexVersionAllowed(string $submitted, string $current, array $graceVersions = []): bool
{
    $submitted = trim($submitted);
    if ($submitted === '') {
        return false;
    }
    if ($submitted === $current) {
        return true;
    }
    return in_array($submitted, $graceVersions, true);
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
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    // Load MedEx API and Services
    $medexApiPath = __DIR__ . '/../src/MedExAPI.php';
    $practiceServicePath = __DIR__ . '/../src/Services/PracticeService.php';
    if (!is_file($medexApiPath) || !is_file($practiceServicePath)) {
        echo json_encode(['success' => false, 'error' => 'MedEx module files are not fully available yet. Please retry in a few seconds.']);
        exit;
    }
    require_once($medexApiPath);
    require_once($practiceServicePath);
    medexEnsureOnboardingAttemptsTable();
    medexEnsureEmailBlocklistTable();

    // Validate required fields (only email and password - practice details come from facility sync)
    $required = [
        'email',
        'password',
        'callback_url',
        'TERMS_yes',
        'BusAgree_yes',
        'otp_proof',
        'terms_signature_name',
        'terms_legal_corporate_name',
        'terms_signed_at',
        'baa_signature_name',
        'baa_legal_corporate_name',
        'baa_signed_at'
    ];
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
    $termsSignatureName = trim((string)($_POST['terms_signature_name'] ?? ''));
    $termsSignerTitle = trim((string)($_POST['terms_signer_title'] ?? ''));
    $termsSignedAtRaw = trim((string)($_POST['terms_signed_at'] ?? ''));
    $termsPracticeName = trim((string)($_POST['terms_practice_name'] ?? ''));
    $termsLegalCorporateName = trim((string)($_POST['terms_legal_corporate_name'] ?? ''));
    $baaSignatureName = trim((string)($_POST['baa_signature_name'] ?? ''));
    $baaSignerTitle = trim((string)($_POST['baa_signer_title'] ?? ''));
    $baaSignedAtRaw = trim((string)($_POST['baa_signed_at'] ?? ''));
    $baaPracticeName = trim((string)($_POST['baa_practice_name'] ?? ''));
    $baaLegalCorporateName = trim((string)($_POST['baa_legal_corporate_name'] ?? ''));
    if ($termsSignatureName === '' || $termsSignedAtRaw === '' || $termsLegalCorporateName === '') {
        echo json_encode(['success' => false, 'error' => 'Electronic signature for Terms & Conditions is required']);
        exit;
    }
    if ($baaSignatureName === '' || $baaSignedAtRaw === '' || $baaLegalCorporateName === '') {
        echo json_encode(['success' => false, 'error' => 'Electronic signature for Business Associate Agreement is required']);
        exit;
    }
    if ($termsLegalCorporateName !== '' && $baaLegalCorporateName !== '' && strcasecmp($termsLegalCorporateName, $baaLegalCorporateName) !== 0) {
        echo json_encode(['success' => false, 'error' => 'Legal corporate name must match on Terms and BAA signatures']);
        exit;
    }
    $termsSignedAtTs = strtotime($termsSignedAtRaw);
    $baaSignedAtTs = strtotime($baaSignedAtRaw);
    if ($termsSignedAtTs === false || $baaSignedAtTs === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid agreement signature timestamp']);
        exit;
    }
    $termsSignedAtUtc = gmdate('Y-m-d H:i:s', $termsSignedAtTs);
    $baaSignedAtUtc = gmdate('Y-m-d H:i:s', $baaSignedAtTs);
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
    $termsGraceVersions = ['2026-03-26'];
    $baaGraceVersions = ['2026-03-26'];
    if (!medexVersionAllowed($termsVersion, MedExConfig::TERMS_VERSION, $termsGraceVersions)) {
        error_log('[MedEx] Terms version normalized from submitted "' . $termsVersion . '" to current "' . MedExConfig::TERMS_VERSION . '"');
        $termsVersion = MedExConfig::TERMS_VERSION;
    }
    if (!medexVersionAllowed($baaVersion, MedExConfig::BAA_VERSION, $baaGraceVersions)) {
        error_log('[MedEx] BAA version normalized from submitted "' . $baaVersion . '" to current "' . MedExConfig::BAA_VERSION . '"');
        $baaVersion = MedExConfig::BAA_VERSION;
    }

    $submittedOpenEmrUrl = trim((string)($_POST['callback_url'] ?? ''));
    $helpUrl = 'help_center.php?site=' . rawurlencode((string)($_GET['site'] ?? 'default')) . '&topic=onboarding_url';
    [$callbackOk, $callbackErr] = medexValidateCallbackUrl($submittedOpenEmrUrl);
    if (!$callbackOk) {
        echo json_encode(['success' => false, 'error' => $callbackErr . '. See URL setup guide: ' . $helpUrl]);
        exit;
    }
    $submittedHost = strtolower((string)(parse_url($submittedOpenEmrUrl, PHP_URL_HOST) ?? ''));
    $currentHost = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
    if (($hostPos = strpos($currentHost, ':')) !== false) {
        $currentHost = substr($currentHost, 0, $hostPos);
    }
    if ($submittedHost === '' || $currentHost === '' || $submittedHost !== $currentHost) {
        echo json_encode(['success' => false, 'error' => 'OpenEMR URL must match this server URL. See URL setup guide: ' . $helpUrl]);
        exit;
    }
    [$derivedOk, $openEmrBaseUrl, $derivedCallbackUrl, $deriveErr] = medexBuildCallbackUrl($submittedOpenEmrUrl);
    if (!$derivedOk) {
        echo json_encode(['success' => false, 'error' => $deriveErr . '. See URL setup guide: ' . $helpUrl]);
        exit;
    }
    [$probeOk, $probeErr] = medexProbeDerivedCallbackUrl($derivedCallbackUrl);
    if (!$probeOk) {
        echo json_encode([
            'success' => false,
            'error' => 'Callback verification failed (' . $probeErr . '). Fix callback configuration before proceeding. See URL setup guide: ' . $helpUrl
        ]);
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
$requestIp = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
$requestUserAgent = substr(trim((string)($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 255);
$acceptedAtUtc = gmdate('Y-m-d H:i:s');
$legalCorporateName = ($termsLegalCorporateName !== '') ? $termsLegalCorporateName : $baaLegalCorporateName;

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
    'terms_signer_name' => $termsSignatureName,
    'terms_signer_title' => $termsSignerTitle,
    'terms_signed_at_utc' => $termsSignedAtUtc,
    'baa_accepted' => true,
    'baa_version' => $baaVersion,
    'baa_accepted_at_utc' => $acceptedAtUtc,
    'baa_signer_name' => $baaSignatureName,
    'baa_signer_title' => $baaSignerTitle,
    'baa_signed_at_utc' => $baaSignedAtUtc,
    'agreement_ip' => $requestIp
    ,'legal_corporate_name' => $legalCorporateName
    ,'otp_channel' => $otpChannel
    ,'otp_house_account' => $otpHouseAccount
    ,'otp_house_cost' => $otpHouseCost
    ,'comms_consent_at_utc' => ($hasCommsConsent ? $acceptedAtUtc : null)
    ,'comms_consent_ip' => ($hasCommsConsent ? $requestIp : null)
    ,'comms_consent_channel' => ($hasCommsConsent ? $otpChannel : null)
];

// Attempt registration
$result = $api->register($data);

// If registration successful, perform initial practice sync
if (!empty($result['success'])) {
    medexEnsureAgreementColumns();
    medexEnsureSignedAgreementsTable();

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
            terms_signer_name = ?,
            terms_signer_title = ?,
            terms_signed_at = ?,
            baa_version = ?,
            baa_accepted_at = ?,
            baa_accepted_ip = ?,
            baa_signer_name = ?,
            baa_signer_title = ?,
            baa_signed_at = ?,
            agreement_user_agent = ?,
            agreement_legal_corporate_name = ?,
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
            $termsSignatureName,
            $termsSignerTitle,
            $termsSignedAtUtc,
            $baaVersion,
            $acceptedAtUtc,
            $requestIp,
            $baaSignatureName,
            $baaSignerTitle,
            $baaSignedAtUtc,
            $requestUserAgent,
            $legalCorporateName,
            $otpChannel,
            $otpHouseAccount,
            $otpHouseCost,
            ($hasCommsConsent ? $acceptedAtUtc : null),
            ($hasCommsConsent ? $requestIp : null),
            ($hasCommsConsent ? $otpChannel : null),
            $data['email']
        ]
    );

    $practiceId = trim((string)($result['practice_id'] ?? $result['customer_id'] ?? ''));
    $customerId = trim((string)($result['customer_id'] ?? $result['practice_id'] ?? ''));
    $signedPracticeName = trim($practice_name);
    if ($termsLegalCorporateName !== '') {
        $signedPracticeName = $termsLegalCorporateName;
    } elseif ($baaLegalCorporateName !== '') {
        $signedPracticeName = $baaLegalCorporateName;
    } elseif ($termsPracticeName !== '') {
        $signedPracticeName = $termsPracticeName;
    } elseif ($baaPracticeName !== '') {
        $signedPracticeName = $baaPracticeName;
    }

    if ($practiceId !== '') {
        try {
            medexPersistSignedAgreement(
                $practiceId,
                $customerId,
                'terms',
                $termsVersion,
                $signedPracticeName,
                $termsSignatureName,
                $termsSignerTitle,
                $email,
                $termsSignedAtUtc,
                $requestIp,
                $requestUserAgent
            );
            medexPersistSignedAgreement(
                $practiceId,
                $customerId,
                'baa',
                $baaVersion,
                $signedPracticeName,
                $baaSignatureName,
                $baaSignerTitle,
                $email,
                $baaSignedAtUtc,
                $requestIp,
                $requestUserAgent
            );
        } catch (\Throwable $e) {
            error_log('[MedEx] Failed to persist signed agreements: ' . $e->getMessage());
        }
    }

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
