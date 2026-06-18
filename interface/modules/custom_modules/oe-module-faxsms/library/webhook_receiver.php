<?php

/**
 * SignalWire Webhook Receiver
 * Handles incoming fax notifications from SignalWire
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    SignalWire Integration Sherwin Gladdis
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gladdis
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Allow webhook access without authentication. Keep this endpoint narrow:
// validation below rejects non-SignalWire requests before any fax data is stored.
$ignoreAuth = true;
$sessionAllowWrite = true;

// globals.php reads auth/site very early, so set this before requiring globals.
$_GET['auth'] = $_GET['auth'] ?? 'portal';

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\FaxSMS\Utils\SignalWireWebhookValidator;

/**
 * Echo an empty cXML response to acknowledge SignalWire callbacks.
 *
 * @return void
 */
function signalwireWebhookAck(): void
{
    http_response_code(200);
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
}

/**
 * Load and decrypt the site-level SignalWire credentials.
 *
 * @return array<string, mixed>
 */
function signalwireLoadCredentials(): array
{
    $row = QueryUtils::querySingleRow(
        "SELECT credentials FROM module_faxsms_credentials WHERE vendor = ? AND auth_user = 0",
        ['_signalwire']
    );

    if (!is_array($row) || empty($row['credentials']) || !is_string($row['credentials'])) {
        return [];
    }

    try {
        $decrypted = ServiceContainer::getCrypto()->decryptFromDatabase($row['credentials']);
    } catch (\Throwable $e) {
        error_log('SignalWire Webhook: Failed to decrypt credentials: ' . $e->getMessage());
        return [];
    }

    $credentials = json_decode($decrypted, true);
    return is_array($credentials) ? $credentials : [];
}

/**
 * Fetch the first non-empty credential value by key.
 *
 * @param array<string, mixed> $credentials
 * @param array<int, string> $keys
 * @return string
 */
function signalwireCredentialValue(array $credentials, array $keys): string
{
    foreach ($keys as $key) {
        $value = SignalWireWebhookValidator::scalarString($credentials[$key] ?? '');
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

/**
 * Validate the SignalWire signature before trusting a public webhook payload.
 *
 * @param string $rawInput Raw php://input body captured before JSON parsing.
 * @param array<string, mixed> $credentials
 * @return bool
 */
function signalwireValidateRequestSignature(string $rawInput, array $credentials): bool
{
    $signingKey = signalwireCredentialValue($credentials, [
        'signing_key',
        'signingKey',
        'webhook_signing_key',
        'webhookSigningKey',
        'signalwire_signing_key',
        'signalwireSigningKey',
        'auth_token',
        'authToken',
    ]);

    // Do not break existing working installs that have not yet added the
    // SignalWire signing key to module setup. When a key is configured, the
    // request must validate. When no key is configured, preserve the current
    // receive-fax behavior and log a setup warning instead of rejecting.
    if ($signingKey === '') {
        error_log('SignalWire Webhook: No signing key configured; signature validation skipped. Add the SignalWire signing key to enable forged-request protection.');
        return true;
    }

    $signatureHeader = SignalWireWebhookValidator::getSignatureHeader($_SERVER);
    if ($signatureHeader === '') {
        error_log('SignalWire Webhook: Signing key is configured but signature header is missing. Request rejected.');
        return false;
    }

    $requestUrl = SignalWireWebhookValidator::buildRequestUrl($_SERVER);

    // Optional override for reverse proxies/tunnels where PHP reconstructs a
    // URL different from the public URL SignalWire actually called. Store the
    // full webhook URL including query string when this is needed.
    $configuredWebhookUrl = signalwireCredentialValue($credentials, [
        'webhook_url',
        'webhookUrl',
        'public_webhook_url',
        'publicWebhookUrl',
    ]);
    if ($configuredWebhookUrl !== '') {
        $requestUrl = $configuredWebhookUrl;
    }

    return SignalWireWebhookValidator::validateSignature(
        $signingKey,
        $signatureHeader,
        $requestUrl,
        $rawInput,
        $_POST
    );
}


/**
 * Map SignalWire JSON/SWML execute payloads into the common POST-like shape.
 *
 * @param string $rawInput
 * @return array<string, mixed>|null Null means this was an ignorable non-fax event.
 */
function signalwirePayloadFromJson(string $rawInput): ?array
{
    if ($rawInput === '') {
        return [];
    }

    $jsonData = json_decode($rawInput, true);
    if (!is_array($jsonData) || json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }

    if (isset($jsonData['call'], $jsonData['vars']) && is_array($jsonData['call']) && is_array($jsonData['vars'])) {
        $vars = $jsonData['vars'];
        $receiveFaxDocument = SignalWireWebhookValidator::scalarString($vars['receive_fax_document'] ?? '');

        if ($receiveFaxDocument === '') {
            return null;
        }

        $receiveFaxResult = strtolower(SignalWireWebhookValidator::scalarString($vars['receive_fax_result'] ?? ''));

        return [
            'FaxSid' => SignalWireWebhookValidator::scalarString(
                $vars['receive_fax_id'] ?? $jsonData['call']['call_id'] ?? uniqid('fax_', true)
            ),
            'Status' => ($receiveFaxResult === 'success') ? 'received' : 'failed',
            'From' => SignalWireWebhookValidator::scalarString($jsonData['call']['from'] ?? ''),
            'To' => SignalWireWebhookValidator::scalarString($jsonData['call']['to'] ?? ''),
            'NumPages' => SignalWireWebhookValidator::scalarString($vars['receive_fax_pages'] ?? '0'),
            'MediaUrl' => $receiveFaxDocument,
            'Direction' => 'inbound',
            'ErrorCode' => SignalWireWebhookValidator::scalarString($vars['receive_fax_result_code'] ?? ''),
            'ErrorMessage' => SignalWireWebhookValidator::scalarString($vars['receive_fax_result_text'] ?? ''),
        ];
    }

    if (isset($jsonData['FaxSid']) || isset($jsonData['Sid'])) {
        return $jsonData;
    }

    return [];
}

/**
 * Normalize the inbound webhook body into a single payload array.
 *
 * @param string $rawInput
 * @return array<string, mixed>|null Null means this was an ignorable non-fax event.
 */
function signalwireBuildPayload(string $rawInput): ?array
{
    $contentType = SignalWireWebhookValidator::scalarString($_SERVER['CONTENT_TYPE'] ?? '');

    if (str_contains(strtolower($contentType), 'application/json')) {
        return signalwirePayloadFromJson($rawInput);
    }

    return $_POST;
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$rawInput = file_get_contents('php://input');
$rawInput = is_string($rawInput) ? $rawInput : '';

if (empty($_GET['site'])) {
    error_log('SignalWire Webhook: Site ID missing.');
    http_response_code(400);
    exit('Missing site');
}

$vendor = SignalWireWebhookValidator::scalarString($_GET['vendor'] ?? '');
$type = SignalWireWebhookValidator::scalarString($_GET['type'] ?? '');

if ($vendor !== 'signalwire' || $type !== 'fax') {
    http_response_code(400);
    error_log('SignalWire Webhook: Invalid webhook vendor or type: vendor=' . $vendor . ', type=' . $type);
    exit('Invalid request');
}

$credentials = signalwireLoadCredentials();
if (!signalwireValidateRequestSignature($rawInput, $credentials)) {
    http_response_code(403);
    error_log('SignalWire Webhook: Invalid or missing signature. Request rejected.');
    exit('Forbidden');
}

$payload = signalwireBuildPayload($rawInput);
if ($payload === null) {
    signalwireWebhookAck();
    exit();
}

$siteId = SignalWireWebhookValidator::validateSiteId(
    SignalWireWebhookValidator::scalarString($session->get('site_id') ?? $_GET['site'] ?? 'default')
);

$faxSid = SignalWireWebhookValidator::validateFaxId(
    SignalWireWebhookValidator::scalarString($payload['FaxSid'] ?? $payload['Sid'] ?? '')
);
$status = SignalWireWebhookValidator::validateFaxStatus(
    SignalWireWebhookValidator::scalarString($payload['Status'] ?? $payload['FaxStatus'] ?? 'unknown')
);
$from = SignalWireWebhookValidator::validatePhoneNumber(
    SignalWireWebhookValidator::scalarString($payload['From'] ?? $payload['RemoteStationId'] ?? '')
);
$to = SignalWireWebhookValidator::validatePhoneNumber(
    SignalWireWebhookValidator::scalarString($payload['To'] ?? $payload['OriginalTo'] ?? '')
);
$numPages = SignalWireWebhookValidator::validateInteger($payload['NumPages'] ?? $payload['Pages'] ?? 0, 0, 9999);
$mediaUrl = SignalWireWebhookValidator::validateString(
    SignalWireWebhookValidator::scalarString($payload['MediaUrl'] ?? ''),
    2048
);
$direction = SignalWireWebhookValidator::validateDirection(
    SignalWireWebhookValidator::scalarString($payload['Direction'] ?? 'inbound')
);
$errorCode = SignalWireWebhookValidator::validateString(
    SignalWireWebhookValidator::scalarString($payload['ErrorCode'] ?? ''),
    100
);
$errorMessage = SignalWireWebhookValidator::validateString(
    SignalWireWebhookValidator::scalarString($payload['ErrorMessage'] ?? ''),
    1000
);

if ($faxSid === '') {
    signalwireWebhookAck();
    exit();
}

try {
    $faxData = [
        'job_id' => $faxSid,
        'status' => $status,
        'from' => $from,
        'to' => $to,
        'num_pages' => $numPages,
        'media_url' => $mediaUrl,
        'direction' => $direction,
        'error_code' => $errorCode,
        'error_message' => $errorMessage,
        'raw_payload' => json_encode($payload) ?: '{}',
    ];

    $existingFax = QueryUtils::querySingleRow(
        "SELECT id, status, patient_id FROM oe_faxsms_queue WHERE job_id = ? AND site_id = ?",
        [$faxSid, $siteId]
    );
    $existingFax = is_array($existingFax) ? $existingFax : [];

    // Discard failures. A failed / no-answer / busy / canceled fax produced no
    // document; soft-delete any existing row for audit and never insert one.
    if (in_array($status, ['failed', 'no-answer', 'busy', 'canceled'], true)) {
        if (!empty($existingFax)) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE oe_faxsms_queue
                 SET deleted = 1, status = ?, details_json = ?, date = NOW()
                 WHERE job_id = ? AND site_id = ?",
                [$status, json_encode($faxData) ?: '{}', $faxSid, $siteId]
            );
        }
        signalwireWebhookAck();
        exit();
    }

    // Deferred-storage model: record metadata + media URL only. The document
    // stays on SignalWire until the user disposes of the fax from the UI, which
    // downloads it, stores it, and deletes it from SignalWire.
    if (!empty($existingFax)) {
        QueryUtils::sqlStatementThrowException(
            "UPDATE oe_faxsms_queue
             SET status = ?, details_json = ?, date = NOW()
             WHERE job_id = ? AND site_id = ?",
            [$status, json_encode($faxData) ?: '{}', $faxSid, $siteId]
        );
    } else {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oe_faxsms_queue
             (job_id, calling_number, called_number, status, direction, details_json, site_id, date, receive_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $faxSid,
                $from,
                $to,
                $status,
                $direction,
                json_encode($faxData) ?: '{}',
                $siteId,
            ]
        );
    }

    signalwireWebhookAck();
} catch (\Throwable $e) {
    error_log('SignalWire Webhook: Error processing webhook: ' . $e->getMessage());
    http_response_code(500);
    exit('Internal server error');
}
