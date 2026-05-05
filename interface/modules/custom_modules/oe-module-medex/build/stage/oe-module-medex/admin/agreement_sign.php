<?php
/**
 * MedEx Agreement Signature Viewer
 *
 * Renders Terms/BAA with required electronic signature capture for onboarding.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExConfig;

const MEDEX_ONBOARDING_VERIFICATION_TTL_SECONDS = 14400;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<html><body>" . xlt('Access denied') . "</body></html>";
    exit;
}

$type = strtolower(trim((string)($_GET['type'] ?? 'terms')));
if (!in_array($type, ['terms', 'baa'], true)) {
    $type = 'terms';
}

$currentVersion = ($type === 'terms') ? MedExConfig::TERMS_VERSION : MedExConfig::BAA_VERSION;
$version = $currentVersion;
$title = ($type === 'terms') ? xlt('MedEx Terms and Conditions') : xlt('MedEx Business Associate Agreement (BAA)');
$displayUrl = ($type === 'terms') ? MedExConfig::termsUrl() : MedExConfig::baaUrl();
$informationId = ($type === 'terms') ? 5 : 8;
// Use server-side base URL for content fetch (k8s-safe and cluster-safe).
$fetchBaseUrl = rtrim(MedExConfig::baseUrl(), '/');
$bodyUrl = $fetchBaseUrl . '/index.php?route=information/information/agree&information_id=' . $informationId;
$action = strtolower(trim((string)($_REQUEST['action'] ?? '')));
if ($action !== '' && !in_array($action, ['save_receipt', 'status'], true)) {
    $action = '';
}
$forceEdit = !empty($_GET['edit']);
$artifactOnly = !empty($_GET['artifact']);
$autoDownload = !empty($_GET['autodownload']);
$autoPrint = !empty($_GET['autoprint']);

function medexAgreementSession()
{
    if (!class_exists(SessionWrapperFactory::class)) {
        return null;
    }
    try {
        return SessionWrapperFactory::getInstance()->getActiveSession();
    } catch (\Throwable $e) {
        return null;
    }
}

function medexCollectAgreementCsrfToken(): string
{
    $session = medexAgreementSession();
    if ($session) {
        if (empty($session->get('csrf_private_key', null))) {
            CsrfUtils::setupCsrfKey($session);
        }
        return (string) CsrfUtils::collectCsrfToken(session: $session);
    }
    if (empty($_SESSION['csrf_private_key'] ?? null)) {
        CsrfUtils::setupCsrfKey();
    }
    return (string) CsrfUtils::collectCsrfToken('default');
}

function medexVerifyAgreementCsrfToken(string $token): bool
{
    $session = medexAgreementSession();
    if ($token === '') {
        return false;
    }
    try {
        if ($session) {
            return CsrfUtils::verifyCsrfToken(token: $token, subject: 'default', session: $session) ||
                CsrfUtils::verifyCsrfToken(token: $token, subject: 'api', session: $session);
        }
        return CsrfUtils::verifyCsrfToken($token, 'default') ||
            CsrfUtils::verifyCsrfToken($token, 'api');
    } catch (\Throwable $e) {
        return false;
    }
}

function medexGetPracticeName(): string
{
    $facility = sqlQuery("SELECT name FROM facility WHERE primary_business_entity = 1 ORDER BY id LIMIT 1");
    if (!$facility) {
        $facility = sqlQuery("SELECT name FROM facility ORDER BY id LIMIT 1");
    }
    $name = trim((string)($facility['name'] ?? ''));
    if ($name !== '') {
        return $name;
    }
    return trim((string)($GLOBALS['openemr_name'] ?? 'OpenEMR Practice'));
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

function medexEnsureAgreementReceiptTable(): void
{
    QueryUtils::sqlStatementThrowException(
        "CREATE TABLE IF NOT EXISTS `medex_agreement_receipt_records` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `receipt_uid` varchar(64) NOT NULL,
            `agreement_type` varchar(20) NOT NULL,
            `agreement_version` varchar(32) NOT NULL,
            `practice_name` varchar(255) DEFAULT NULL,
            `legal_corporate_name` varchar(255) DEFAULT NULL,
            `signer_name` varchar(255) DEFAULT NULL,
            `signer_title` varchar(255) DEFAULT NULL,
            `signed_at` datetime DEFAULT NULL,
            `payload_sha256` char(64) DEFAULT NULL,
            `payload_json` mediumtext NOT NULL,
            `body_html` mediumtext DEFAULT NULL,
            `receipt_status` varchar(32) NOT NULL DEFAULT 'signed_html',
            `pdf_origin` varchar(32) NOT NULL DEFAULT 'remote_pending',
            `remote_receipt_id` varchar(128) DEFAULT NULL,
            `remote_pdf_url` text DEFAULT NULL,
            `local_pdf_path` text DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_medex_agreement_receipt_uid` (`receipt_uid`),
            KEY `idx_medex_agreement_receipt_lookup` (`agreement_type`, `agreement_version`, `signed_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        []
    );
}

function medexLoadAgreementReceipt(string $type, string $version): ?array
{
    medexEnsureAgreementReceiptTable();
    $row = QueryUtils::querySingleRow(
        "SELECT receipt_uid, payload_json, body_html, created_at, updated_at,
                practice_name, legal_corporate_name, signer_name, signer_title,
                signed_at, receipt_status, pdf_origin, remote_receipt_id,
                remote_pdf_url, local_pdf_path, payload_sha256
           FROM medex_agreement_receipt_records
          WHERE agreement_type = ? AND agreement_version = ?
          ORDER BY COALESCE(signed_at, created_at) DESC, id DESC
          LIMIT 1",
        [$type, $version]
    );
    $payloadRaw = (string)($row['payload_json'] ?? '');
    if ($payloadRaw === '') {
        return null;
    }
    $payload = json_decode($payloadRaw, true);
    if (!is_array($payload)) {
        return null;
    }
    return [
        'receipt_uid' => (string)($row['receipt_uid'] ?? ''),
        'payload' => $payload,
        'body_html' => (string)($row['body_html'] ?? ''),
        'created_at' => (string)($row['created_at'] ?? ''),
        'updated_at' => (string)($row['updated_at'] ?? ''),
        'receipt_status' => (string)($row['receipt_status'] ?? ''),
        'pdf_origin' => (string)($row['pdf_origin'] ?? ''),
        'remote_receipt_id' => (string)($row['remote_receipt_id'] ?? ''),
        'remote_pdf_url' => (string)($row['remote_pdf_url'] ?? ''),
        'local_pdf_path' => (string)($row['local_pdf_path'] ?? ''),
        'payload_sha256' => (string)($row['payload_sha256'] ?? ''),
    ];
}

function medexIsAgreementReceiptFresh(?array $receipt): bool
{
    if (!is_array($receipt)) {
        return false;
    }
    $payload = is_array($receipt['payload'] ?? null) ? $receipt['payload'] : [];
    $signedAtRaw = trim((string)($payload['signed_at'] ?? ''));
    if ($signedAtRaw === '') {
        $signedAtRaw = trim((string)($receipt['created_at'] ?? ''));
    }
    if ($signedAtRaw === '') {
        return false;
    }
    $signedAtTs = strtotime($signedAtRaw);
    if ($signedAtTs === false || $signedAtTs <= 0) {
        return false;
    }
    return (time() - $signedAtTs) <= MEDEX_ONBOARDING_VERIFICATION_TTL_SECONDS;
}

function medexSaveAgreementReceipt(string $type, string $version, array $payload, string $bodyHtml): array
{
    medexEnsureAgreementReceiptTable();
    $payloadJson = json_encode($payload);
    $payloadHash = hash('sha256', $payloadJson . "\n" . $bodyHtml);
    $existing = QueryUtils::querySingleRow(
        "SELECT receipt_uid, payload_json, body_html, created_at, updated_at,
                receipt_status, pdf_origin, remote_receipt_id, remote_pdf_url,
                local_pdf_path, payload_sha256
           FROM medex_agreement_receipt_records
          WHERE agreement_type = ? AND agreement_version = ? AND payload_sha256 = ?
          ORDER BY id DESC
          LIMIT 1",
        [$type, $version, $payloadHash]
    );
    if (!empty($existing['receipt_uid'])) {
        return [
            'receipt_uid' => (string)$existing['receipt_uid'],
            'payload' => $payload,
            'body_html' => (string)($existing['body_html'] ?? ''),
            'created_at' => (string)($existing['created_at'] ?? ''),
            'updated_at' => (string)($existing['updated_at'] ?? ''),
            'receipt_status' => (string)($existing['receipt_status'] ?? ''),
            'pdf_origin' => (string)($existing['pdf_origin'] ?? ''),
            'remote_receipt_id' => (string)($existing['remote_receipt_id'] ?? ''),
            'remote_pdf_url' => (string)($existing['remote_pdf_url'] ?? ''),
            'local_pdf_path' => (string)($existing['local_pdf_path'] ?? ''),
            'payload_sha256' => (string)($existing['payload_sha256'] ?? ''),
        ];
    }

    $receiptUid = 'agr_' . bin2hex(random_bytes(12));
    $signedAt = trim((string)($payload['signed_at'] ?? ''));
    $practiceName = trim((string)($payload['practice_name'] ?? ''));
    $legalCorporateName = trim((string)($payload['legal_corporate_name'] ?? ''));
    $signerName = trim((string)($payload['signer_name'] ?? ''));
    $signerTitle = trim((string)($payload['signer_title'] ?? ''));
    QueryUtils::sqlStatementThrowException(
        "INSERT INTO medex_agreement_receipt_records
            (receipt_uid, agreement_type, agreement_version, practice_name, legal_corporate_name,
             signer_name, signer_title, signed_at, payload_sha256, payload_json, body_html,
             receipt_status, pdf_origin, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'signed_html', 'remote_pending', NOW(), NOW())",
        [$receiptUid, $type, $version, $practiceName, $legalCorporateName, $signerName, $signerTitle, $signedAt ?: null, $payloadHash, $payloadJson, $bodyHtml]
    );
    return medexLoadAgreementReceipt($type, $version) ?? [
        'receipt_uid' => $receiptUid,
        'payload' => $payload,
        'body_html' => $bodyHtml,
        'created_at' => '',
        'updated_at' => '',
        'receipt_status' => 'signed_html',
        'pdf_origin' => 'remote_pending',
        'remote_receipt_id' => '',
        'remote_pdf_url' => '',
        'local_pdf_path' => '',
        'payload_sha256' => $payloadHash,
    ];
}

function medexFetchAgreementBody(string $url): string
{
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
            $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
            ]
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if (is_string($body)) {
            $raw = $body;
        }
    }

    return (trim($raw) === '') ? '' : $raw;
}

function medexFormatAgreementSignedMoments(string $signedAt): array
{
    $signedAt = trim($signedAt);
    if ($signedAt === '') {
        return [
            'local' => '',
            'utc' => '',
        ];
    }

    try {
        $date = new DateTimeImmutable($signedAt);
        $localZone = new DateTimeZone(date_default_timezone_get() ?: 'UTC');
        return [
            'local' => $date->setTimezone($localZone)->format('F j, Y g:i:s A T'),
            'utc' => $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s T'),
        ];
    } catch (\Throwable $e) {
        return [
            'local' => $signedAt,
            'utc' => $signedAt,
        ];
    }
}

function medexBuildAgreementCertificateHtml(array $payload, string $title, string $version, array $receipt = []): string
{
    $signedMoments = medexFormatAgreementSignedMoments((string)($payload['signed_at'] ?? ''));
    $companyName = trim((string)($payload['legal_corporate_name'] ?? $payload['practice_name'] ?? ''));
    $signerName = trim((string)($payload['signer_name'] ?? ''));
    $signerTitle = trim((string)($payload['signer_title'] ?? ''));
    $receiptUid = trim((string)($receipt['receipt_uid'] ?? ''));
    $payloadHash = trim((string)($receipt['payload_sha256'] ?? ''));

    ob_start();
    ?>
    <section class="signature-cert">
        <div class="signature-cert-title"><?php echo xlt('Electronic Signature Certificate'); ?></div>
        <div class="signature-cert-copy"><?php echo xlt('This agreement was electronically signed and affirmed on behalf of the business identified below.'); ?></div>
        <div class="signature-grid">
            <div class="signature-row"><span class="meta-label"><?php echo xlt('Legal Business Name'); ?>:</span><?php echo text($companyName); ?></div>
            <div class="signature-row"><span class="meta-label"><?php echo xlt('Signer Name'); ?>:</span><?php echo text($signerName); ?></div>
            <div class="signature-row"><span class="meta-label"><?php echo xlt('Signer Title'); ?>:</span><?php echo text($signerTitle !== '' ? $signerTitle : xl('Not provided')); ?></div>
            <div class="signature-row"><span class="meta-label"><?php echo xlt('Signature Method'); ?>:</span><?php echo xlt('Electronic signature accepted in onboarding flow'); ?></div>
            <div class="signature-row"><span class="meta-label"><?php echo xlt('Signed At'); ?>:</span><?php echo text($signedMoments['local']); ?></div>
            <div class="signature-row"><span class="meta-label"><?php echo xlt('Signed At UTC'); ?>:</span><?php echo text($signedMoments['utc']); ?></div>
            <div class="signature-row"><span class="meta-label"><?php echo xlt('Agreement'); ?>:</span><?php echo text($title . ' (' . $version . ')'); ?></div>
            <?php if ($receiptUid !== ''): ?>
                <div class="signature-row"><span class="meta-label"><?php echo xlt('Receipt UID'); ?>:</span><?php echo text($receiptUid); ?></div>
            <?php endif; ?>
            <?php if ($payloadHash !== ''): ?>
                <div class="signature-row"><span class="meta-label"><?php echo xlt('Receipt Hash'); ?>:</span><?php echo text($payloadHash); ?></div>
            <?php endif; ?>
        </div>
        <div class="signature-attest"><?php echo xlt('Attestation: The signer represented they were authorized to sign this agreement electronically on behalf of the business.'); ?></div>
    </section>
    <?php
    return trim((string)ob_get_clean());
}

function medexRenderAgreementArtifactPage(string $title, string $version, string $agreementHtml, ?array $receipt, bool $autoDownload, bool $autoPrint): void
{
    $payload = is_array($receipt) ? ($receipt['payload'] ?? null) : null;
    $documentHtml = is_array($receipt) ? trim((string)($receipt['body_html'] ?? '')) : '';
    if ($documentHtml === '') {
        $documentHtml = $agreementHtml;
    }
    $certificateHtml = is_array($payload)
        ? medexBuildAgreementCertificateHtml($payload, $title, $version, $receipt ?? [])
        : '';
    $printMode = $autoDownload || $autoPrint;

    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo text($title . ' Receipt'); ?></title>
        <style>
            @page { margin: 16mm 14mm; }
            html, body {
                margin: 0;
                padding: 0;
                background: #edf4ff;
                color: #0f172a;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            }
            body {
                padding: 24px;
            }
            .artifact-shell {
                max-width: 980px;
                margin: 0 auto;
            }
            .artifact-head {
                margin-bottom: 16px;
                border: 1px solid #cfe0fb;
                border-radius: 14px;
                padding: 18px 20px;
                background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            }
            .artifact-title {
                margin: 0 0 6px;
                font-size: 28px;
                font-weight: 800;
                color: #0f4b8f;
            }
            .artifact-subtitle {
                margin: 0;
                color: #526277;
                font-size: 14px;
            }
            .artifact-note {
                margin-top: 12px;
                padding: 12px 14px;
                border: 1px solid #bfdbfe;
                border-radius: 10px;
                background: #eff6ff;
                color: #1d4ed8;
                font-size: 13px;
                font-weight: 700;
            }
            .artifact-warning {
                border: 1px solid #fecaca;
                border-radius: 14px;
                padding: 18px 20px;
                background: #fff7ed;
                color: #9a3412;
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            }
            .document-paper {
                border: 1px solid #b99f6a;
                border-radius: 8px;
                padding: 18px 20px;
                background-color: #f4e8cf;
                background-image:
                    radial-gradient(circle at 18% 22%, rgba(110, 82, 35, 0.07) 0, rgba(110, 82, 35, 0) 30%),
                    radial-gradient(circle at 80% 74%, rgba(110, 82, 35, 0.06) 0, rgba(110, 82, 35, 0) 28%),
                    repeating-linear-gradient(
                        0deg,
                        rgba(84, 61, 24, 0.025) 0px,
                        rgba(84, 61, 24, 0.025) 1px,
                        rgba(244, 232, 207, 0) 2px,
                        rgba(244, 232, 207, 0) 6px
                    );
                font-family: "Times New Roman", Times, serif;
                font-size: 16px;
                line-height: 1.6;
                color: #1f1a12;
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
            }
            .document-paper h2,
            .document-paper h3,
            .document-paper h4 {
                color: #1b1510;
                font-family: "Times New Roman", Times, serif;
            }
            .signature-cert {
                margin: 0 0 18px;
                padding: 14px 16px;
                border: 2px solid #0f4b8f;
                border-radius: 10px;
                background: rgba(248, 251, 255, 0.94);
                page-break-inside: avoid;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            }
            .signature-cert-title {
                margin: 0 0 6px;
                font-size: 18px;
                font-weight: 800;
                color: #0f4b8f;
            }
            .signature-cert-copy {
                margin: 0 0 10px;
                color: #334155;
                font-size: 13px;
            }
            .signature-row {
                margin: 4px 0;
                font-size: 13px;
            }
            .meta-label {
                display: inline-block;
                min-width: 170px;
                color: #475569;
                font-weight: 700;
            }
            .signature-attest {
                margin-top: 10px;
                padding-top: 10px;
                border-top: 1px solid #cbd5e1;
                font-size: 12px;
                color: #334155;
                font-style: italic;
            }
            @media print {
                html, body {
                    background: #fff;
                }
                body {
                    padding: 0;
                }
                .artifact-head {
                    box-shadow: none;
                }
                .artifact-note {
                    display: none;
                }
                .document-paper {
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="artifact-shell">
            <div class="artifact-head">
                <h1 class="artifact-title"><?php echo text($title); ?></h1>
                <p class="artifact-subtitle"><?php echo xlt('Agreement version'); ?> <?php echo text($version); ?>.</p>
            </div>
            <?php if (!is_array($payload) || $documentHtml === ''): ?>
                <div class="artifact-warning"><?php echo xlt('No signed agreement receipt is available yet for this agreement.'); ?></div>
            <?php else: ?>
                <article class="document-paper">
                    <?php echo $certificateHtml; ?>
                    <?php echo $documentHtml; ?>
                    <div style="margin-top:18px;"><?php echo $certificateHtml; ?></div>
                </article>
            <?php endif; ?>
        </div>
        <?php if ($printMode): ?>
            <script>
                window.addEventListener("load", function () {
                    window.setTimeout(function () {
                        window.print();
                    }, 280);
                });
            </script>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

$csrfToken = medexCollectAgreementCsrfToken();
$agreementHtml = medexFetchAgreementBody($bodyUrl);
$agreementHtml = medexRemoveLegacyPrintInstruction($agreementHtml);
$practiceName = medexGetPracticeName();
$existingReceipt = $forceEdit ? null : medexLoadAgreementReceipt($type, $version);

if ($action === 'status') {
    $isFresh = medexIsAgreementReceiptFresh($existingReceipt);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'signed' => $isFresh,
        'expired' => is_array($existingReceipt) && !$isFresh,
        'verification_window_seconds' => MEDEX_ONBOARDING_VERIFICATION_TTL_SECONDS,
        'agreement_version' => $version,
        'payload' => $isFresh && is_array($existingReceipt) ? ($existingReceipt['payload'] ?? null) : null,
    ]);
    exit;
}

if ($action === 'save_receipt') {
    header('Content-Type: application/json');
    $token = trim((string)($_POST['csrf_token_form'] ?? ''));
    if (!medexVerifyAgreementCsrfToken($token)) {
        echo json_encode(['success' => false, 'error' => 'Invalid security token']);
        exit;
    }
    $payload = [
        'source' => 'medex-agreement-signer',
        'action' => 'signed',
        'type' => $type,
        'agreement_version' => $version,
        'practice_name' => trim((string)($_POST['practice_name'] ?? $practiceName)),
        'legal_corporate_name' => trim((string)($_POST['legal_corporate_name'] ?? '')),
        'signer_name' => trim((string)($_POST['signer_name'] ?? '')),
        'signer_title' => trim((string)($_POST['signer_title'] ?? '')),
        'signed_at' => trim((string)($_POST['signed_at'] ?? '')),
    ];
    if ($payload['legal_corporate_name'] === '' || $payload['signer_name'] === '' || $payload['signed_at'] === '') {
        echo json_encode(['success' => false, 'error' => 'Missing required signature fields']);
        exit;
    }
    $receipt = medexSaveAgreementReceipt($type, $version, $payload, $agreementHtml);
    echo json_encode([
        'success' => true,
        'payload' => $payload,
        'agreement_version' => $version,
        'receipt_uid' => (string)($receipt['receipt_uid'] ?? ''),
        'receipt_status' => (string)($receipt['receipt_status'] ?? 'signed_html'),
        'pdf_origin' => (string)($receipt['pdf_origin'] ?? 'remote_pending'),
    ]);
    exit;
}

if (is_array($existingReceipt) && trim((string)($existingReceipt['body_html'] ?? '')) !== '') {
    $agreementHtml = (string)$existingReceipt['body_html'];
}
$initialSignedPayload = is_array($existingReceipt) ? ($existingReceipt['payload'] ?? null) : null;
if (is_array($initialSignedPayload) && empty($initialSignedPayload['agreement_version'])) {
    $initialSignedPayload['agreement_version'] = $version;
}
if ($artifactOnly) {
    medexRenderAgreementArtifactPage($title, $version, $agreementHtml, $existingReceipt, $autoDownload, $autoPrint);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo text($title); ?></title>
    <?php Header::setupHeader(['fontawesome']); ?>
    <style>
        :root {
            --brand: #0f4b8f;
            --line: #dbeafe;
            --muted: #64748b;
        }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: linear-gradient(180deg, #eff6ff 0%, #f8fafc 100%);
        }
        .page {
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 14px;
            box-sizing: border-box;
            gap: 12px;
        }
        .head {
            border: 1px solid #dbe7f5;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            padding: 18px 20px;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            position: sticky;
            top: 0;
            z-index: 3;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #0f4b8f;
            margin-bottom: 8px;
        }
        .title {
            font-size: 24px;
            font-weight: 800;
            color: var(--brand);
            margin: 0 0 6px;
        }
        .sub {
            margin: 0;
            font-size: 14px;
            color: #526277;
        }
        .head-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            margin-top: 12px;
            border: 1px solid #cfe0fb;
            border-radius: 10px;
            background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
            color: #1e3a8a;
            font-size: 13px;
            font-weight: 700;
        }
        .head-status i {
            color: #0f4b8f;
        }
        .head-status.complete {
            border-color: #bbf7d0;
            background: linear-gradient(180deg, #ecfdf5 0%, #f7fff9 100%);
            color: #166534;
        }
        .body {
            flex: 1 1 auto;
            min-height: 420px;
            overflow-y: auto;
            background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
            padding: 12px;
            border: 1px solid #dbe7f5;
            border-radius: 16px;
            line-height: 1.55;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }
        .legal-paper {
            background-color: #f4e8cf;
            background-image:
                radial-gradient(circle at 18% 22%, rgba(110, 82, 35, 0.07) 0, rgba(110, 82, 35, 0) 30%),
                radial-gradient(circle at 80% 74%, rgba(110, 82, 35, 0.06) 0, rgba(110, 82, 35, 0) 28%),
                repeating-linear-gradient(
                    0deg,
                    rgba(84, 61, 24, 0.025) 0px,
                    rgba(84, 61, 24, 0.025) 1px,
                    rgba(244, 232, 207, 0) 2px,
                    rgba(244, 232, 207, 0) 6px
                );
            border: 1px solid #b99f6a;
            border-radius: 6px;
            padding: 14px 16px;
            min-height: 100%;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            line-height: 1.6;
            color: #1f1a12;
        }
        .legal-paper h2,
        .legal-paper h3,
        .legal-paper h4 {
            font-family: "Times New Roman", Times, serif;
            color: #1b1510;
        }
        .fallback {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 12px;
        }
        .sign {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            padding: 18px 20px 20px;
            display: grid;
            gap: 10px;
            border: 1px solid #dbe7f5;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }
        .sign-head {
            display: grid;
            gap: 4px;
            margin-bottom: 4px;
        }
        .sign-title {
            font-size: 20px;
            font-weight: 800;
            color: #132238;
            margin: 0;
        }
        .sign-copy {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            color: #526277;
        }
        .practice {
            border: 1px solid #dbeafe;
            background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            color: #1e3a8a;
        }
        .practice strong {
            color: #0f172a;
        }
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }
        .input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
        }
        .input:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96,165,250,.18);
        }
        .note {
            font-size: 13px;
            color: #475569;
        }
        .readiness {
            font-size: 12px;
            color: #475569;
            font-weight: 700;
        }
        .readiness.ok {
            color: #15803d;
            font-weight: 600;
        }
        .error {
            color: #b91c1c;
            font-size: 12px;
            display: none;
        }
        .ok {
            color: #15803d;
            font-size: 12px;
            display: none;
            font-weight: 700;
        }
        .btn {
            background: linear-gradient(180deg, #0f4b8f 0%, #0a3460 100%);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 11px 16px;
            font-weight: 700;
            cursor: pointer;
            width: fit-content;
            box-shadow: 0 10px 22px rgba(15, 75, 143, 0.18);
        }
        .btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
        }
        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
        }
        .btn-secondary {
            min-width: 92px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #c7d7ec;
            background: #fff;
            color: #0f4b8f;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-secondary:disabled {
            color: #94a3b8;
            border-color: #cbd5e1;
            background: #f1f5f9;
            cursor: not-allowed;
        }
        .btn-danger {
            min-width: 92px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid #dc2626;
            background: linear-gradient(180deg, #ef4444 0%, #b91c1c 100%);
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 10px 22px rgba(185, 28, 28, 0.18);
        }
        .btn-danger:hover {
            background: linear-gradient(180deg, #dc2626 0%, #991b1b 100%);
        }
        @media (max-width: 760px) {
            .row {
                grid-template-columns: 1fr;
            }
            .body {
                min-height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="head">
            <div class="eyebrow"><?php echo xlt('Onboarding Agreement'); ?></div>
            <p class="title"><?php echo text($title); ?></p>
            <p class="sub"><?php echo xlt('Version'); ?> <?php echo text($version); ?> • <?php echo xlt('Review and complete your electronic signature to continue onboarding.'); ?></p>
            <div id="scroll_cue" class="head-status">
                <span><?php echo xlt('Scroll to the bottom of the agreement to unlock signing.'); ?></span>
                <i class="fa fa-arrow-down" aria-hidden="true"></i>
            </div>
        </div>
        <div class="body">
            <div class="legal-paper">
                <?php if ($agreementHtml !== ''): ?>
                    <?php echo $agreementHtml; ?>
                <?php else: ?>
                    <div class="fallback">
                        <?php echo xlt('Unable to load agreement text in embedded view right now.'); ?>
                    </div>
                <?php endif; ?>
                <div id="agreement-end-marker" style="height:1px;"></div>
            </div>
        </div>
        <div class="sign">
            <div class="sign-head">
                <p class="sign-title"><?php echo xlt('Electronic Signature'); ?></p>
                <p class="sign-copy"><?php echo xlt('Complete the signer details below after reviewing the full agreement. Once signed, onboarding will unlock the related checkbox automatically.'); ?></p>
            </div>
            <div class="practice">
                <?php echo xlt('Company'); ?>:
                <strong id="practice_name_display"><?php echo text($practiceName); ?></strong>
            </div>
            <div class="row">
                <input type="text" id="legal_corporate_name" class="input" placeholder="<?php echo xla('Legal Corporate Name (required)'); ?>" value="<?php echo attr($practiceName); ?>" disabled>
                <input type="text" id="signer_name" class="input" placeholder="<?php echo xla('Legal Name (required)'); ?>" disabled>
                <input type="text" id="signer_title" class="input" placeholder="<?php echo xla('Title/Role (optional)'); ?>" disabled>
            </div>
            <label class="note">
                <input type="checkbox" id="attest" disabled> <?php echo xlt('I am authorized to sign this agreement on behalf of my practice and agree to these terms electronically.'); ?>
            </label>
            <div id="sign_error" class="error"><?php echo xlt('Enter your legal corporate name, legal signer name, and confirm authorization to sign.'); ?></div>
            <div id="sign_ok" class="ok"><?php echo xlt('Electronic signature recorded.'); ?></div>
            <div class="actions">
                <button type="button" id="sign_btn" class="btn" disabled><?php echo xlt('Sign'); ?></button>
                <button type="button" id="download_btn" class="btn-secondary" title="<?php echo xla('Open signed agreement for PDF download'); ?>" disabled>
                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> <?php echo xlt('Download PDF'); ?>
                </button>
                <button type="button" id="print_btn" class="btn-secondary" title="<?php echo xla('Print or download signed agreement'); ?>" disabled>
                    <i class="fa fa-print" aria-hidden="true"></i> <?php echo xlt('Print'); ?>
                </button>
                <button type="button" id="close_btn" class="btn-danger" title="<?php echo xla('Close signed agreement'); ?>">
                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo xlt('Close'); ?>
                </button>
            </div>
            <div id="readiness_state" class="readiness"><?php echo xlt('Scroll through the full agreement and complete required fields to enable signing.'); ?></div>
        </div>
    </div>

    <script>
        (function () {
            const signBtn = document.getElementById("sign_btn");
            const downloadBtn = document.getElementById("download_btn");
            const printBtn = document.getElementById("print_btn");
            const closeBtn = document.getElementById("close_btn");
            const legalCorporateNameEl = document.getElementById("legal_corporate_name");
            const signerNameEl = document.getElementById("signer_name");
            const signerTitleEl = document.getElementById("signer_title");
            const attestEl = document.getElementById("attest");
            const errorEl = document.getElementById("sign_error");
            const okEl = document.getElementById("sign_ok");
            const readinessEl = document.getElementById("readiness_state");
            const scrollCueEl = document.getElementById("scroll_cue");
            const bodyEl = document.querySelector(".body");
            const endMarkerEl = document.getElementById("agreement-end-marker");
            const practiceName = <?php echo json_encode($practiceName); ?>;
            const agreementTitle = <?php echo json_encode($title); ?>;
            const agreementVersion = <?php echo json_encode($version); ?>;
            const agreementType = <?php echo json_encode($type); ?>;
            const agreementCsrfToken = <?php echo json_encode($csrfToken); ?>;
            const initialSignedPayload = <?php echo json_encode($initialSignedPayload); ?>;
            const forceEdit = <?php echo $forceEdit ? 'true' : 'false'; ?>;
            const autoDownload = <?php echo $autoDownload ? 'true' : 'false'; ?>;
            const autoPrint = <?php echo $autoPrint ? 'true' : 'false'; ?>;
            const pdfFileBase = agreementType === "terms" ? "MedEX_Terms" : "MedEX_BAA";
            const signedLabel = <?php echo json_encode(xl('Signed')); ?>;
            let signedPayload = null;
            let agreementRead = false;
            let endObserver = null;

            function setError(show) {
                errorEl.style.display = show ? "block" : "none";
            }

            function setOk(show) {
                okEl.style.display = show ? "block" : "none";
            }

            function hasRequiredInputs() {
                const legalCorporateName = (legalCorporateNameEl.value || "").trim();
                const signerName = (signerNameEl.value || "").trim();
                return !!legalCorporateName && !!signerName && !!attestEl.checked;
            }

            function setFieldsEnabled(enabled) {
                legalCorporateNameEl.disabled = !enabled;
                signerNameEl.disabled = !enabled;
                signerTitleEl.disabled = !enabled;
                attestEl.disabled = !enabled;
            }

            function updateSignEnabledState() {
                const canSign = agreementRead && hasRequiredInputs() && !signedPayload;
                signBtn.disabled = !canSign;
                if (signedPayload) {
                    setFieldsEnabled(false);
                    readinessEl.textContent = "";
                    readinessEl.style.display = "none";
                    readinessEl.classList.remove("ok");
                    return;
                }
                setFieldsEnabled(agreementRead);
                if (agreementRead) {
                    readinessEl.textContent = "";
                    readinessEl.style.display = "none";
                    readinessEl.classList.remove("ok");
                    if (scrollCueEl) {
                        scrollCueEl.classList.add("complete");
                        scrollCueEl.innerHTML = '<span>Agreement fully reviewed. You can sign below.</span><i class="fa fa-check-circle" aria-hidden="true"></i>';
                    }
                    return;
                }
                readinessEl.style.display = "block";
                readinessEl.textContent = "Scroll to the bottom of the agreement, then complete the signer fields to enable signing.";
                readinessEl.classList.remove("ok");
                if (scrollCueEl) {
                    scrollCueEl.classList.remove("complete");
                    scrollCueEl.innerHTML = '<span>Scroll to the bottom of the agreement to unlock signing.</span><i class="fa fa-arrow-down" aria-hidden="true"></i>';
                }
            }

            function evaluateAgreementRead() {
                if (!bodyEl) {
                    agreementRead = true;
                    updateSignEnabledState();
                    return;
                }
                if (agreementRead) {
                    updateSignEnabledState();
                    return;
                }
                const scrollHeight = Math.max(0, bodyEl.scrollHeight || 0);
                const clientHeight = Math.max(0, bodyEl.clientHeight || 0);
                const scrollTop = Math.max(0, bodyEl.scrollTop || 0);
                const maxScrollTop = Math.max(0, scrollHeight - clientHeight);
                const remaining = maxScrollTop - scrollTop;
                let markerReached = false;
                if (endMarkerEl) {
                    const markerRect = endMarkerEl.getBoundingClientRect();
                    const bodyRect = bodyEl.getBoundingClientRect();
                    markerReached = markerRect.top <= (bodyRect.bottom - 8);
                }
                if (maxScrollTop <= 2) {
                    agreementRead = true;
                } else if (remaining <= 24 || markerReached) {
                    agreementRead = true;
                }
                updateSignEnabledState();
            }

            function markAgreementRead() {
                if (agreementRead) {
                    return;
                }
                agreementRead = true;
                if (endObserver) {
                    endObserver.disconnect();
                    endObserver = null;
                }
                updateSignEnabledState();
            }

            function formatSignedAt(iso) {
                try {
                    const d = new Date(iso);
                    if (isNaN(d.getTime())) {
                        return iso;
                    }
                    return d.toLocaleString();
                } catch (e) {
                    return iso;
                }
            }

            function getAgreementBodyHtml() {
                return bodyEl ? bodyEl.innerHTML : "";
            }

            function applySignedPayload(payload, announceParent) {
                if (!payload) {
                    return;
                }
                signedPayload = payload;
                legalCorporateNameEl.value = String(payload.legal_corporate_name || payload.practice_name || "");
                signerNameEl.value = String(payload.signer_name || "");
                signerTitleEl.value = String(payload.signer_title || "");
                attestEl.checked = true;
                setError(false);
                setOk(true);
                signBtn.disabled = true;
                signBtn.textContent = signedLabel;
                downloadBtn.disabled = false;
                printBtn.disabled = false;
                closeBtn.style.display = "inline-flex";
                legalCorporateNameEl.readOnly = true;
                signerNameEl.readOnly = true;
                signerTitleEl.readOnly = true;
                attestEl.disabled = true;
                updateSignEnabledState();
                if (announceParent && window.parent && window.parent !== window) {
                    window.parent.postMessage(payload, "*");
                }
            }

            function buildArtifactUrl(options = {}) {
                const url = new URL(window.location.href);
                url.searchParams.delete("action");
                url.searchParams.delete("edit");
                url.searchParams.set("artifact", "1");
                if (options.autodownload) {
                    url.searchParams.set("autodownload", "1");
                } else {
                    url.searchParams.delete("autodownload");
                }
                if (options.autoprint) {
                    url.searchParams.set("autoprint", "1");
                } else {
                    url.searchParams.delete("autoprint");
                }
                return url.toString();
            }

            function openArtifactDocument(options = {}) {
                const targetUrl = buildArtifactUrl(options);
                const opened = window.open(targetUrl, "_blank", "noopener,noreferrer");
                if (!opened && options.sameWindowFallback) {
                    window.location.assign(targetUrl);
                }
            }

            signBtn.addEventListener("click", function () {
                const signerName = (signerNameEl.value || "").trim();
                const legalCorporateName = (legalCorporateNameEl.value || "").trim();
                const signerTitle = (signerTitleEl.value || "").trim();
                const attest = !!attestEl.checked;
                if (!legalCorporateName || !signerName || !attest) {
                    setOk(false);
                    setError(true);
                    return;
                }
                if (!agreementRead) {
                    setOk(false);
                    setError(true);
                    return;
                }
                const payload = {
                    source: "medex-agreement-signer",
                    action: "signed",
                    type: agreementType,
                    practice_name: practiceName,
                    legal_corporate_name: legalCorporateName,
                    signer_name: signerName,
                    signer_title: signerTitle,
                    signed_at: new Date().toISOString()
                };
                fetch(window.location.pathname + window.location.search + (window.location.search ? "&" : "?") + "action=save_receipt", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                    },
                    credentials: "same-origin",
                    body: new URLSearchParams({
                        csrf_token_form: agreementCsrfToken,
                        practice_name: String(payload.practice_name || ""),
                        legal_corporate_name: String(payload.legal_corporate_name || ""),
                        signer_name: String(payload.signer_name || ""),
                        signer_title: String(payload.signer_title || ""),
                        signed_at: String(payload.signed_at || "")
                    }).toString()
                }).then(function (response) {
                    return response.json();
                }).then(function (response) {
                    if (!response || !response.success || !response.payload) {
                        throw new Error((response && response.error) ? response.error : "Unable to save signed receipt.");
                    }
                    applySignedPayload(response.payload, true);
                }).catch(function (error) {
                    setOk(false);
                    setError(true);
                    errorEl.textContent = error && error.message ? error.message : "Unable to save signed receipt.";
                });
            });

            printBtn.addEventListener("click", function () {
                if (!signedPayload) {
                    return;
                }
                openArtifactDocument({ autoprint: true });
            });

            downloadBtn.addEventListener("click", function () {
                if (!signedPayload) {
                    return;
                }
                openArtifactDocument({ autodownload: true });
            });

            closeBtn.addEventListener("click", function () {
                try {
                    if (window.parent && window.parent !== window && typeof window.parent.medexCloseAgreementModal === "function") {
                        window.parent.medexCloseAgreementModal();
                        return;
                    }
                } catch (e) {
                    // Cross-frame access can fail; fall back to postMessage below.
                }
                const payload = {
                    source: "medex-agreement-signer",
                    action: "close",
                    type: agreementType
                };
                if (window.parent && window.parent !== window) {
                    window.parent.postMessage(payload, "*");
                    return;
                }
                window.close();
            });

            if (bodyEl) {
                bodyEl.addEventListener("scroll", evaluateAgreementRead);
            }
            if (bodyEl && endMarkerEl && typeof IntersectionObserver !== "undefined") {
                endObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.target === endMarkerEl && entry.isIntersecting) {
                            markAgreementRead();
                        }
                    });
                }, {
                    root: bodyEl,
                    threshold: 1.0
                });
                endObserver.observe(endMarkerEl);
            }
            legalCorporateNameEl.addEventListener("input", updateSignEnabledState);
            signerNameEl.addEventListener("input", updateSignEnabledState);
            attestEl.addEventListener("change", updateSignEnabledState);
            if (initialSignedPayload) {
                applySignedPayload(initialSignedPayload, true);
                if (autoDownload || autoPrint) {
                    openArtifactDocument({
                        autodownload: autoDownload,
                        autoprint: autoPrint,
                        sameWindowFallback: true
                    });
                }
            }
            evaluateAgreementRead();
            setTimeout(evaluateAgreementRead, 250);
            setTimeout(evaluateAgreementRead, 800);
        })();
    </script>
</body>
</html>
