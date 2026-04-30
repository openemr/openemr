<?php
/**
 * MedEx Module - Callback Endpoint
 *
 * Secure endpoint for MedEx server to push/pull data
 * Replaces library/MedEx/MedEx.php functionality but within the module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}
$ignoreAuth = true;

require_once(__DIR__ . "/../../../../globals.php");

// Set JSON response header
header('Content-Type: application/json');

$rawBody = file_get_contents('php://input');
if ($rawBody === false) {
    $rawBody = '';
}
$requestId = bin2hex(random_bytes(8));
error_log('[MedEx Callback][' . $requestId . '] Request from: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
error_log('[MedEx Callback][' . $requestId . '] Method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));

/**
 * Read MedEx callback security setting from globals.
 */
function medexGetCallbackSetting(string $name, string $default = ''): string
{
    $row = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
        "SELECT gl_value FROM globals WHERE gl_name = ?",
        [$name]
    );
    $value = (string)($row['gl_value'] ?? '');
    return $value !== '' ? $value : $default;
}

/**
 * Require HMAC signature headers and prevent replay when enabled.
 *
 * Signature format:
 *   HMAC_SHA256(token, "{timestamp}\n{nonce}\n{rawBody}")
 */
function medexValidateSignature(string $token, string $rawBody, bool $requireSignature, string $requestId): bool
{
    $timestamp = trim((string)($_SERVER['HTTP_X_MEDEX_TIMESTAMP'] ?? ''));
    $nonce = trim((string)($_SERVER['HTTP_X_MEDEX_NONCE'] ?? ''));
    $signature = strtolower(trim((string)($_SERVER['HTTP_X_MEDEX_SIGNATURE'] ?? '')));
    $hasSignatureHeaders = ($timestamp !== '' || $nonce !== '' || $signature !== '');

    if (!$requireSignature && !$hasSignatureHeaders) {
        return true;
    }
    if ($timestamp === '' || $nonce === '' || $signature === '') {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Missing signature headers');
        return false;
    }
    if (!ctype_digit($timestamp)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid signature timestamp');
        return false;
    }
    $tsInt = (int)$timestamp;
    if (abs(time() - $tsInt) > 300) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Signature timestamp out of window');
        return false;
    }
    if (!preg_match('/^[A-Za-z0-9_-]{12,128}$/', $nonce)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid signature nonce');
        return false;
    }

    sqlStatement("
        CREATE TABLE IF NOT EXISTS `medex_callback_nonce_log` (
            `nonce` VARCHAR(128) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`nonce`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    sqlStatement("DELETE FROM `medex_callback_nonce_log` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $existingNonce = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
        "SELECT nonce FROM medex_callback_nonce_log WHERE nonce = ? LIMIT 1",
        [$nonce]
    );
    if (!empty($existingNonce['nonce'])) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Replay nonce detected');
        return false;
    }
    sqlStatement("INSERT INTO `medex_callback_nonce_log` (`nonce`) VALUES (?)", [$nonce]);

    $expected = hash_hmac('sha256', $timestamp . "\n" . $nonce . "\n" . $rawBody, $token);
    if (!hash_equals($expected, $signature)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid request signature');
        return false;
    }

    return true;
}

/**
 * Validate callback token and optional signature policy.
 */
function validateCallbackAuth(string $rawBody, string $requestId): bool
{
    $allowQueryToken = medexGetCallbackSetting('medex_callback_allow_query_token', '1') === '1';
    $requireHeaderToken = medexGetCallbackSetting('medex_callback_require_header_token', '0') === '1';
    $requireSignature = medexGetCallbackSetting('medex_callback_require_signature', '0') === '1';

    $providedToken = trim((string)($_SERVER['HTTP_X_MEDEX_TOKEN'] ?? ''));
    if ($providedToken === '' && !$requireHeaderToken && $allowQueryToken) {
        $providedToken = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
    }
    if ($providedToken === '') {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: No callback token provided');
        return false;
    }

    $storedToken = medexGetCallbackSetting('medex_callback_token', '');
    if ($storedToken === '') {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: No callback token configured');
        return false;
    }
    if (!hash_equals($storedToken, $providedToken)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid callback token');
        return false;
    }
    if (!medexValidateSignature($storedToken, $rawBody, $requireSignature, $requestId)) {
        return false;
    }

    return true;
}

/**
 * Get request data (handles both JSON and form data).
 */
function getRequestData(string $rawBody): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode($rawBody, true);
        return $data ?? [];
    }

    $formData = $_POST;
    if (empty($formData) && $rawBody !== '') {
        parse_str($rawBody, $parsedFormData);
        if (is_array($parsedFormData) && !empty($parsedFormData)) {
            $formData = $parsedFormData;
        }
    }

    return array_merge($_GET, $formData);
}

function medexResolveDateDisplayFormat($value): string
{
    $value = trim((string)$value);
    $map = [
        '0' => 'm/d/Y',
        '1' => 'm/d/Y',
        '2' => 'd/m/Y',
        '3' => 'Y-m-d',
        '4' => 'M j, Y',
    ];
    if (isset($map[$value])) {
        return $map[$value];
    }
    return $value !== '' ? $value : 'm/d/Y';
}

function medexResolveTimeDisplayFormat($value): string
{
    $value = trim((string)$value);
    $map = [
        '0' => 'g:i A',
        '1' => 'g:i A',
        '2' => 'H:i',
    ];
    if (isset($map[$value])) {
        return $map[$value];
    }
    return $value !== '' ? $value : 'g:i A';
}

function medexCallbackBaseUrl(): string
{
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '';
    }
    $proto = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
    if ($proto === '') {
        $https = (string)($_SERVER['HTTPS'] ?? '');
        $proto = (!empty($https) && strtolower($https) !== 'off') ? 'https' : 'http';
    } else {
        $proto = trim(explode(',', $proto)[0]);
    }
    if ($proto !== 'https' && $proto !== 'http') {
        $proto = 'https';
    }
    if ($proto === 'http') {
        $proto = 'https';
    }
    return $proto . '://' . $host;
}

function medexEnsureCalendarFeedTables(): void
{
    sqlStatement("
        CREATE TABLE IF NOT EXISTS medex_calendar_feeds (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            openemr_user_id INT,
            openemr_username VARCHAR(255),
            providers TEXT,
            facilities TEXT,
            provider_names TEXT,
            facility_names TEXT,
            created_at DATETIME,
            INDEX idx_token (token),
            INDEX idx_user_id (openemr_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    sqlStatement("
        CREATE TABLE IF NOT EXISTS medex_calendar_feed_access_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            feed_token VARCHAR(64),
            openemr_user_id INT,
            openemr_username VARCHAR(255),
            ip_address VARCHAR(45),
            user_agent TEXT,
            success TINYINT(1) DEFAULT 0,
            message VARCHAR(255),
            accessed_at DATETIME,
            INDEX idx_feed_token (feed_token),
            INDEX idx_user_id (openemr_user_id),
            INDEX idx_accessed_at (accessed_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function medexDecodeFeedNames($raw): array
{
    if (!is_string($raw) || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return array_values(array_filter(array_map(static function ($item) {
            return trim((string)$item);
        }, $decoded), static function ($item) {
            return $item !== '';
        }));
    }
    $parts = array_map('trim', explode(',', $raw));
    return array_values(array_filter($parts, static function ($item) {
        return $item !== '';
    }));
}

function medexCountFeedScope($raw): int
{
    if (!is_string($raw) || trim($raw) === '') {
        return 0;
    }
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return count(array_filter($decoded, static function ($value) {
            return trim((string)$value) !== '';
        }));
    }
    $parts = array_filter(array_map('trim', explode(',', $raw)), static function ($value) {
        return $value !== '';
    });
    return count($parts);
}

function medexFormatFeedScopeLabel(int $count, string $singular, string $plural): string
{
    $count = max(0, $count);
    return $count . ' ' . ($count === 1 ? $singular : $plural);
}

function medexSimplifyCalendarClient($userAgent): string
{
    $ua = strtolower(trim((string)$userAgent));
    if ($ua === '') {
        return 'Unknown';
    }
    $map = [
        'google calendar' => 'Google Calendar',
        'applewebkit' => 'Apple Calendar',
        'ical/' => 'Apple Calendar',
        'outlook' => 'Outlook',
        'thunderbird' => 'Thunderbird',
        'fantastical' => 'Fantastical',
        'davx5' => 'DAVx5',
    ];
    foreach ($map as $needle => $label) {
        if (strpos($ua, $needle) !== false) {
            return $label;
        }
    }
    $firstToken = preg_split('/[\s\/;]+/', trim((string)$userAgent));
    $label = trim((string)($firstToken[0] ?? 'Unknown'));
    return $label !== '' ? substr($label, 0, 40) : 'Unknown';
}

function medexSystemUsernames(): array
{
    return ['admin', 'oe-system', 'phimail-service', 'portal-user'];
}

function medexSystemNames(): array
{
    return ['admin', 'administrator', 'system operation user', 'patient portal user'];
}

function medexNormalizeDisplayName($fname, $lname): string
{
    $displayName = trim(trim((string)$fname) . ' ' . trim((string)$lname));
    $displayName = preg_replace('/\s+/', ' ', $displayName ?? $displayName);
    return strtolower(trim((string)$displayName));
}

function medexBuildPracticeSetupAnalysis(): array
{
    $providers = [];
    $providerSeen = [];
    $duplicateProviders = [];
    $adminCandidates = [];
    $authorizedProviderCount = 0;
    $calendarProviderCount = 0;

    $providerStmt = sqlStatement("
        SELECT id, username, fname, lname, authorized, active, calendar, facility_id
        FROM users
        WHERE active = 1
        ORDER BY username, id ASC
    ");
    while ($row = sqlFetchArray($providerStmt)) {
        $id = (string)($row['id'] ?? '');
        $username = strtolower(trim((string)($row['username'] ?? '')));
        $fname = trim((string)($row['fname'] ?? ''));
        $lname = trim((string)($row['lname'] ?? ''));
        $displayName = trim($fname . ' ' . $lname);
        $normalizedName = medexNormalizeDisplayName($fname, $lname);
        $authorized = ((int)($row['authorized'] ?? 0) === 1);
        $calendar = ((int)($row['calendar'] ?? 0) === 1);

        if ($authorized) {
            $authorizedProviderCount++;
        }
        if ($authorized && $calendar) {
            $calendarProviderCount++;
        }

        $isSystemUsername = in_array($username, medexSystemUsernames(), true);
        $isSystemName = in_array($normalizedName, medexSystemNames(), true);
        if (($isSystemUsername || $isSystemName) && ($authorized || $calendar)) {
            $adminCandidates[] = [
                'id' => $id,
                'username' => $username,
                'name' => $displayName !== '' ? $displayName : ($username !== '' ? $username : ('User ' . $id)),
                'authorized' => $authorized,
                'calendar' => $calendar,
            ];
        }

        if (
            $id === ''
            || !$authorized
            || !$calendar
            || $username === ''
            || $normalizedName === ''
            || $isSystemUsername
            || $isSystemName
        ) {
            continue;
        }

        if (isset($providerSeen[$username])) {
            $duplicateProviders[] = [
                'username' => $username,
                'ids' => [$providerSeen[$username], $id],
                'name' => $displayName !== '' ? $displayName : ('Provider ' . $id),
            ];
            continue;
        }

        $providerSeen[$username] = $id;
        $providers[] = [
            'id' => $id,
            'username' => $username,
            'name' => $displayName !== '' ? $displayName : ('Provider ' . $id),
            'facility_id' => (string)($row['facility_id'] ?? ''),
        ];
    }

    usort($providers, static function (array $a, array $b): int {
        return strcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
    });

    $facilities = [];
    $serviceLocationCount = 0;
    $primaryBusinessCount = 0;
    $namedFacilityCount = 0;
    $facilityStmt = sqlStatement("
        SELECT id, name, service_location, billing_location, primary_business_entity, street, city, state, postal_code
        FROM facility
        ORDER BY id ASC
    ");
    while ($frow = sqlFetchArray($facilityStmt)) {
        $name = trim((string)($frow['name'] ?? ''));
        if ($name !== '') {
            $namedFacilityCount++;
        }
        if ((int)($frow['service_location'] ?? 0) === 1) {
            $serviceLocationCount++;
        }
        if ((int)($frow['primary_business_entity'] ?? 0) === 1) {
            $primaryBusinessCount++;
        }
        $facilities[] = [
            'id' => (string)($frow['id'] ?? ''),
            'name' => $name !== '' ? $name : ('Facility ' . (string)($frow['id'] ?? '')),
            'service_location' => ((int)($frow['service_location'] ?? 0) === 1),
            'billing_location' => ((int)($frow['billing_location'] ?? 0) === 1),
            'primary_business_entity' => ((int)($frow['primary_business_entity'] ?? 0) === 1),
        ];
    }

    return [
        'provider_count' => count($providers),
        'authorized_provider_count' => $authorizedProviderCount,
        'calendar_provider_count' => $calendarProviderCount,
        'duplicate_provider_count' => count($duplicateProviders),
        'duplicate_providers' => $duplicateProviders,
        'admin_provider_count' => count($adminCandidates),
        'admin_provider_candidates' => $adminCandidates,
        'providers' => array_slice($providers, 0, 12),
        'facility_count' => count($facilities),
        'service_location_count' => $serviceLocationCount,
        'primary_business_entity_count' => $primaryBusinessCount,
        'named_facility_count' => $namedFacilityCount,
        'facilities' => array_slice($facilities, 0, 12),
        'practice_ready' => (count($providers) > 0 && $serviceLocationCount > 0),
    ];
}

// Validate token first
if (!validateCallbackAuth($rawBody, $requestId)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized: Invalid or missing callback token'
    ]);
    exit;
}

// Get request data
$data = getRequestData($rawBody);
$action = $data['action'] ?? 'unknown';

error_log('[MedEx Callback][' . $requestId . '] Action: ' . $action);

// Allow token-auth ping/status/toggle actions even before module is fully enabled/configured.
if (!in_array($action, ['ping', 'get_module_status', 'set_module_enabled', 'analyze_practice_setup'], true) && ($GLOBALS['medex_enable'] ?? '0') != '1') {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'MedEx is not enabled'
    ]);
    exit;
}

// Route to appropriate handler based on action
switch ($action) {
    case 'ping':
        // Simple health check
        echo json_encode([
            'success' => true,
            'message' => 'OpenEMR MedEx module is active',
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'module_enabled' => (($GLOBALS['medex_enable'] ?? '0') == '1')
        ]);
        break;

    case 'get_module_status':
        // Explicit status snapshot for embedded dashboard controls.
        echo json_encode([
            'success' => true,
            'module_enabled' => (($GLOBALS['medex_enable'] ?? '0') == '1'),
            'timestamp' => date('c')
        ]);
        break;

    case 'get_locale_settings':
        $timezone = \OpenEMR\Common\Database\QueryUtils::fetchSingleValue(
            "SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1",
            'gl_value',
            ['gbl_time_zone']
        );
        $dateDisplayFormat = \OpenEMR\Common\Database\QueryUtils::fetchSingleValue(
            "SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1",
            'gl_value',
            ['date_display_format']
        );
        $timeDisplayFormat = \OpenEMR\Common\Database\QueryUtils::fetchSingleValue(
            "SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1",
            'gl_value',
            ['time_display_format']
        );
        if (!is_string($timezone) || trim($timezone) === '') {
            $timezone = date_default_timezone_get() ?: 'UTC';
        }
        $dateDisplayFormat = medexResolveDateDisplayFormat($dateDisplayFormat);
        $timeDisplayFormat = medexResolveTimeDisplayFormat($timeDisplayFormat);
        echo json_encode([
            'success' => true,
            'timezone' => (string)$timezone,
            'date_display_format' => (string)$dateDisplayFormat,
            'time_display_format' => (string)$timeDisplayFormat,
            'timestamp' => date('c')
        ]);
        break;

    case 'set_locale_settings':
        $timezone = trim((string)($data['timezone'] ?? ''));
        if ($timezone === '' || !in_array($timezone, timezone_identifiers_list(), true)) {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid timezone'
            ]);
            break;
        }
        $existing = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
            "SELECT gl_name FROM globals WHERE gl_name = ? LIMIT 1",
            ['gbl_time_zone']
        );
        if (!empty($existing['gl_name'])) {
            sqlStatement("UPDATE globals SET gl_value = ? WHERE gl_name = ?", [$timezone, 'gbl_time_zone']);
        } else {
            sqlStatement(
                "INSERT INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)",
                ['gbl_time_zone', $timezone]
            );
        }
        $GLOBALS['gbl_time_zone'] = $timezone;
        @date_default_timezone_set($timezone);
        echo json_encode([
            'success' => true,
            'timezone' => $timezone,
            'message' => 'Locale time zone updated'
        ]);
        break;

    case 'set_calendar_feed_policy':
        $feedSecurity = strtolower(trim((string)($data['feed_security'] ?? 'secure')));
        if (!in_array($feedSecurity, ['secure', 'insecure'], true)) {
            $feedSecurity = 'secure';
        }
        $menuAcl = strtolower(trim((string)($data['menu_acl'] ?? 'patients|appt')));
        if (!in_array($menuAcl, ['patients|appt', 'admin|super'], true)) {
            $menuAcl = 'patients|appt';
        }

        foreach ([
            'medex_calendar_feed_security' => $feedSecurity,
            'medex_calendar_feed_menu_acl' => $menuAcl,
        ] as $glName => $glValue) {
            $existing = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
                "SELECT gl_name FROM globals WHERE gl_name = ? LIMIT 1",
                [$glName]
            );
            if (!empty($existing['gl_name'])) {
                sqlStatement("UPDATE globals SET gl_value = ? WHERE gl_name = ?", [$glValue, $glName]);
            } else {
                sqlStatement(
                    "INSERT INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)",
                    [$glName, $glValue]
                );
            }
            $GLOBALS[$glName] = $glValue;
        }

        echo json_encode([
            'success' => true,
            'feed_security' => $feedSecurity,
            'menu_acl' => $menuAcl,
        ]);
        break;

    case 'set_module_enabled':
        // Toggle OpenEMR MedEx global from authenticated dashboard controls.
        $enabled = ((string)($data['enabled'] ?? '0') === '1') ? '1' : '0';
        $existing = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
            "SELECT gl_name FROM globals WHERE gl_name = ? LIMIT 1",
            ['medex_enable']
        );
        if (!empty($existing['gl_name'])) {
            sqlStatement("UPDATE globals SET gl_value = ? WHERE gl_name = ?", [$enabled, 'medex_enable']);
        } else {
            sqlStatement(
                "INSERT INTO globals (gl_name, gl_index, gl_value) VALUES (?, 0, ?)",
                ['medex_enable', $enabled]
            );
        }
        $GLOBALS['medex_enable'] = $enabled;
        echo json_encode([
            'success' => true,
            'module_enabled' => ($enabled === '1'),
            'message' => ($enabled === '1') ? 'MedEx enabled' : 'MedEx disabled'
        ]);
        break;

    case 'get_appointments':
        // MedEx requesting appointment data
        require_once(__DIR__ . '/../src/CallbackHandlers/AppointmentHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\AppointmentHandler();
        $result = $handler->getAppointments($data);
        echo json_encode($result);
        break;

    case 'get_status_changes':
        // MedEx requesting lightweight status snapshot (pc_eid + pc_apptstatus only)
        require_once(__DIR__ . '/../src/CallbackHandlers/AppointmentHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\AppointmentHandler();
        $result = $handler->getStatusChanges($data);
        echo json_encode($result);
        break;

    case 'update_appointment_status':
        // MedEx updating appointment status (confirmed, cancelled, etc)
        require_once(__DIR__ . '/../src/CallbackHandlers/AppointmentHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\AppointmentHandler();
        $result = $handler->updateAppointmentStatus($data);
        echo json_encode($result);
        break;

    case 'get_recalls':
        // MedEx requesting recall data
        require_once(__DIR__ . '/../src/CallbackHandlers/RecallHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\RecallHandler();
        $result = $handler->getRecalls($data);
        echo json_encode($result);
        break;

    case 'update_recall_status':
        // MedEx updating recall status
        require_once(__DIR__ . '/../src/CallbackHandlers/RecallHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\RecallHandler();
        $result = $handler->updateRecallStatus($data);
        echo json_encode($result);
        break;

    case 'log_message':
        // MedEx logging a sent message (SMS, email, voice)
        require_once(__DIR__ . '/../src/CallbackHandlers/MessageHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\MessageHandler();
        $result = $handler->logMessage($data);
        echo json_encode($result);
        break;

    case 'message_reply':
    case 'message_status':
        // MedEx sending message reply or status update (CONFIRMED, CALL, STOP, SENT, READ, FAILED, BOUNCE)
        require_once(__DIR__ . '/../src/Services/MessageReceiveService.php');
        $receiveService = new \OpenEMR\Modules\MedEx\Services\MessageReceiveService();
        $result = $receiveService->receive($data);
        echo json_encode($result);
        break;

    case 'get_patient':
        // MedEx requesting patient demographics
        require_once(__DIR__ . '/../src/CallbackHandlers/PatientHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\PatientHandler();
        $result = $handler->getPatient($data);
        echo json_encode($result);
        break;

    case 'get_preferences':
        // MedEx requesting practice preferences
        $prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT * FROM medex_prefs LIMIT 1", []);
        echo json_encode([
            'success' => true,
            'preferences' => $prefs
        ]);
        break;

    case 'get_provider_roster':
        // MedEx requesting current providers/facilities for admin scope controls
        $providers = [];
        $providerSeen = [];
        $providerStmt = sqlStatement("
            SELECT id, fname, lname, username
            FROM users
            WHERE active = 1
              AND calendar = 1
            ORDER BY username, id DESC
        ");
        while ($row = sqlFetchArray($providerStmt)) {
            $id = (string)($row['id'] ?? '');
            $username = strtolower(trim((string)($row['username'] ?? '')));
            $fname = trim((string)($row['fname'] ?? ''));
            $lname = trim((string)($row['lname'] ?? ''));
            $displayName = trim($fname . ' ' . $lname);
            $normalizedName = strtolower(trim((string)(preg_replace('/\s+/', ' ', $displayName) ?? $displayName)));
            if (
                $id === ''
                || $username === ''
                || in_array($username, ['admin', 'oe-system', 'phimail-service', 'portal-user'], true)
                || $normalizedName === ''
                || in_array($normalizedName, ['admin', 'administrator', 'system operation user', 'patient portal user'], true)
            ) {
                continue;
            }
            if (isset($providerSeen[$username])) {
                continue;
            }
            $providerSeen[$username] = true;
            $providers[] = [
                'id' => $id,
                'name' => $displayName !== '' ? $displayName : ('Provider ' . $id),
                'active' => true
            ];
        }
        usort($providers, static function (array $a, array $b): int {
            return strcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
        });

        $facilities = [];
        $facilityStmt = sqlStatement("SELECT id, name, city FROM facility ORDER BY id ASC");
        while ($frow = sqlFetchArray($facilityStmt)) {
            $fid = (string)($frow['id'] ?? '');
            if ($fid === '') {
                continue;
            }
            $facilities[] = [
                'id' => $fid,
                'name' => (string)($frow['name'] ?? ('Facility ' . $fid)),
                'city' => trim((string)($frow['city'] ?? ''))
            ];
        }

        // Keep FullCalendar day-start/day-end aligned with OpenEMR Calendar globals.
        if (!isset($GLOBALS['schedule_start']) || !isset($GLOBALS['schedule_end'])) {
            $gStmt = sqlStatement("SELECT gl_name, gl_value FROM globals WHERE gl_name IN ('schedule_start', 'schedule_end')");
            while ($gRow = sqlFetchArray($gStmt)) {
                if (!empty($gRow['gl_name'])) {
                    $GLOBALS[(string)$gRow['gl_name']] = (string)($gRow['gl_value'] ?? '');
                }
            }
        }
        $scheduleStart = (int)($GLOBALS['schedule_start'] ?? 8);
        $scheduleEnd = (int)($GLOBALS['schedule_end'] ?? 17);
        if ($scheduleEnd <= $scheduleStart) {
            $scheduleEnd = min(23, $scheduleStart + 1);
        }

        echo json_encode([
            'success' => true,
            'providers' => $providers,
            'facilities' => $facilities,
            'schedule_start' => $scheduleStart,
            'schedule_end' => $scheduleEnd
        ]);
        break;

    case 'analyze_practice_setup':
        echo json_encode([
            'success' => true,
            'practice_setup' => medexBuildPracticeSetupAnalysis(),
            'timestamp' => date('c')
        ]);
        break;

    case 'get_calendar_template_context':
        $providerFilter = $data['provider_ids'] ?? [];
        if (!is_array($providerFilter)) {
            $providerFilter = [];
        }
        $providerFilter = array_values(array_unique(array_filter(array_map('intval', $providerFilter), static function ($id) {
            return $id > 0;
        })));

        $providerMap = [];
        $providerSeen = [];
        $providerStmt = sqlStatement("
            SELECT id, fname, lname, username
            FROM users
            WHERE active = 1
              AND calendar = 1
            ORDER BY username, id DESC
        ");
        while ($row = sqlFetchArray($providerStmt)) {
            $id = (int)($row['id'] ?? 0);
            $username = strtolower(trim((string)($row['username'] ?? '')));
            $displayName = trim((string)($row['fname'] ?? '') . ' ' . (string)($row['lname'] ?? ''));
            $normalizedName = strtolower(trim((string)(preg_replace('/\s+/', ' ', $displayName) ?? $displayName)));
            if (
                $id <= 0
                || $username === ''
                || in_array($username, ['admin', 'oe-system', 'phimail-service', 'portal-user'], true)
                || $normalizedName === ''
                || in_array($normalizedName, ['admin', 'administrator', 'system operation user', 'patient portal user'], true)
            ) {
                continue;
            }
            if (isset($providerSeen[$username])) {
                continue;
            }
            $providerSeen[$username] = true;
            if (!empty($providerFilter) && !in_array($id, $providerFilter, true)) {
                continue;
            }
            $providerMap[$id] = $displayName !== '' ? $displayName : ('Provider ' . $id);
        }

        $templateRows = [];
        if (!empty($providerMap)) {
            $providerSql = implode(',', array_map('intval', array_keys($providerMap)));
            if (!empty(sqlQuery("SHOW TABLES LIKE 'medex_schedule_templates'"))) {
                $templateStmt = sqlStatement("
                    SELECT t.template_id, t.provider_id, t.template_name, t.day_of_week, t.start_time, t.end_time,
                           t.preferred_category_id, t.slot_duration, cat.pc_catname AS preferred_category_name
                    FROM medex_schedule_templates t
                    LEFT JOIN openemr_postcalendar_categories cat ON cat.pc_catid = t.preferred_category_id
                    WHERE t.is_active = 1
                      AND t.provider_id IN (" . $providerSql . ")
                    ORDER BY t.provider_id, t.day_of_week, t.start_time
                ");
                while ($templateRow = sqlFetchArray($templateStmt)) {
                    $templateRows[] = [
                        'template_id' => (int)($templateRow['template_id'] ?? 0),
                        'provider_id' => (int)($templateRow['provider_id'] ?? 0),
                        'provider_name' => (string)($providerMap[(int)($templateRow['provider_id'] ?? 0)] ?? ''),
                        'template_name' => trim((string)($templateRow['template_name'] ?? '')),
                        'day_of_week' => (int)($templateRow['day_of_week'] ?? 0),
                        'start_time' => substr((string)($templateRow['start_time'] ?? ''), 0, 5),
                        'end_time' => substr((string)($templateRow['end_time'] ?? ''), 0, 5),
                        'preferred_category_id' => (int)($templateRow['preferred_category_id'] ?? 0),
                        'preferred_category_name' => trim((string)($templateRow['preferred_category_name'] ?? '')),
                        'slot_duration' => (int)($templateRow['slot_duration'] ?? 0),
                    ];
                }
            }
        }

        $preferredSlotPatterns = [];
        if (!empty($providerMap)) {
            $providerSql = implode(',', array_map('intval', array_keys($providerMap)));
            $slotStmt = sqlStatement("
                SELECT
                    pc.pc_aid AS provider_id,
                    DAYOFWEEK(pc.pc_eventDate) AS mysql_day_of_week,
                    MIN(TIME_FORMAT(pc.pc_startTime, '%H:%i')) AS earliest_time,
                    MAX(TIME_FORMAT(pc.pc_endTime, '%H:%i')) AS latest_time,
                    COUNT(*) AS slot_count,
                    SUM(CASE WHEN COALESCE(pc.pc_pid, 0) = 0 THEN 1 ELSE 0 END) AS open_slot_count,
                    pref.pc_catname AS preferred_category_name
                FROM openemr_postcalendar_events pc
                LEFT JOIN openemr_postcalendar_categories pref ON pref.pc_catid = pc.pc_prefcatid
                WHERE pc.pc_aid IN (" . $providerSql . ")
                  AND pc.pc_eventstatus = 1
                  AND pc.pc_eventDate >= CURDATE()
                  AND pc.pc_eventDate <= DATE_ADD(CURDATE(), INTERVAL 180 DAY)
                  AND COALESCE(pc.pc_prefcatid, 0) > 0
                GROUP BY pc.pc_aid, DAYOFWEEK(pc.pc_eventDate), pref.pc_catname
                ORDER BY pc.pc_aid, mysql_day_of_week, slot_count DESC, pref.pc_catname ASC
            ");

            $aggregate = [];
            while ($slotRow = sqlFetchArray($slotStmt)) {
                $providerId = (int)($slotRow['provider_id'] ?? 0);
                if ($providerId <= 0) {
                    continue;
                }
                if (!isset($aggregate[$providerId])) {
                    $aggregate[$providerId] = [
                        'provider_id' => $providerId,
                        'provider_name' => (string)($providerMap[$providerId] ?? ('Provider ' . $providerId)),
                        'days' => [],
                        'earliest_time' => '',
                        'latest_time' => '',
                        'preferred_categories' => [],
                        'slot_count' => 0,
                        'open_slot_count' => 0,
                    ];
                }
                $dayOfWeek = ((int)($slotRow['mysql_day_of_week'] ?? 1) + 5) % 7;
                $aggregate[$providerId]['days'][$dayOfWeek] = true;
                $earliest = trim((string)($slotRow['earliest_time'] ?? ''));
                $latest = trim((string)($slotRow['latest_time'] ?? ''));
                if ($earliest !== '' && ($aggregate[$providerId]['earliest_time'] === '' || strcmp($earliest, $aggregate[$providerId]['earliest_time']) < 0)) {
                    $aggregate[$providerId]['earliest_time'] = $earliest;
                }
                if ($latest !== '' && ($aggregate[$providerId]['latest_time'] === '' || strcmp($latest, $aggregate[$providerId]['latest_time']) > 0)) {
                    $aggregate[$providerId]['latest_time'] = $latest;
                }
                $categoryName = trim((string)($slotRow['preferred_category_name'] ?? ''));
                if ($categoryName !== '') {
                    $aggregate[$providerId]['preferred_categories'][$categoryName] = (int)($aggregate[$providerId]['preferred_categories'][$categoryName] ?? 0) + (int)($slotRow['slot_count'] ?? 0);
                }
                $aggregate[$providerId]['slot_count'] += (int)($slotRow['slot_count'] ?? 0);
                $aggregate[$providerId]['open_slot_count'] += (int)($slotRow['open_slot_count'] ?? 0);
            }

            foreach ($aggregate as $providerId => $summary) {
                $days = array_keys($summary['days']);
                sort($days);
                arsort($summary['preferred_categories'], SORT_NUMERIC);
                $topCategories = [];
                foreach ($summary['preferred_categories'] as $categoryName => $slotCount) {
                    $topCategories[] = [
                        'category_name' => (string)$categoryName,
                        'slot_count' => (int)$slotCount,
                    ];
                    if (count($topCategories) >= 5) {
                        break;
                    }
                }
                $preferredSlotPatterns[] = [
                    'provider_id' => (int)$providerId,
                    'provider_name' => (string)$summary['provider_name'],
                    'days' => $days,
                    'earliest_time' => (string)$summary['earliest_time'],
                    'latest_time' => (string)$summary['latest_time'],
                    'slot_count' => (int)$summary['slot_count'],
                    'open_slot_count' => (int)$summary['open_slot_count'],
                    'top_preferred_categories' => $topCategories,
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'templates' => $templateRows,
            'preferred_slot_patterns' => $preferredSlotPatterns,
        ]);
        break;

    case 'get_calendar_feeds_admin':
        medexEnsureCalendarFeedTables();

        $baseUrl = medexCallbackBaseUrl();
        $webroot = rtrim((string)($GLOBALS['webroot'] ?? ''), '/');
        $feeds = [];
        $feedStmt = sqlStatement("
            SELECT id, token, name, openemr_user_id, openemr_username, providers, facilities, provider_names, facility_names, created_at
            FROM medex_calendar_feeds
            ORDER BY created_at DESC, id DESC
        ");
        while ($row = sqlFetchArray($feedStmt)) {
            $token = trim((string)($row['token'] ?? ''));
            if ($token === '') {
                continue;
            }
            $providerNames = medexDecodeFeedNames((string)($row['provider_names'] ?? ''));
            $facilityNames = medexDecodeFeedNames((string)($row['facility_names'] ?? ''));
            $providerCount = medexCountFeedScope((string)($row['providers'] ?? ''));
            $facilityCount = medexCountFeedScope((string)($row['facilities'] ?? ''));
            $feeds[$token] = [
                'feed_id' => (int)($row['id'] ?? 0),
                'token' => $token,
                'name' => trim((string)($row['name'] ?? '')),
                'owner_username' => trim((string)($row['openemr_username'] ?? '')),
                'provider_count' => $providerCount,
                'facility_count' => $facilityCount,
                'provider_scope_label' => medexFormatFeedScopeLabel($providerCount, 'provider', 'providers'),
                'facility_scope_label' => medexFormatFeedScopeLabel($facilityCount, 'facility', 'facilities'),
                'provider_names' => $providerNames,
                'facility_names' => $facilityNames,
                'created_at' => (string)($row['created_at'] ?? ''),
                'last_access' => '',
                'hits_30d' => 0,
                'source_counts' => [],
                'test_url' => ($baseUrl !== '')
                    ? ($baseUrl . $webroot . '/interface/modules/custom_modules/oe-module-medex/public/calendar_feed.php?feed=' . rawurlencode($token))
                    : '',
            ];
        }

        $accessRows = [];
        $accessStmt = @sqlStatement("
            SELECT feed_token, user_agent, success, accessed_at
            FROM medex_calendar_feed_access_log
            ORDER BY accessed_at DESC, id DESC
        ");
        if ($accessStmt) {
            while ($row = sqlFetchArray($accessStmt)) {
                $accessRows[] = $row;
            }
        }
        foreach ($accessRows as $row) {
            $token = trim((string)($row['feed_token'] ?? ''));
            if ($token === '' || empty($feeds[$token])) {
                continue;
            }
            $accessedAt = trim((string)($row['accessed_at'] ?? ''));
            if ($accessedAt !== '' && $feeds[$token]['last_access'] === '') {
                $feeds[$token]['last_access'] = $accessedAt;
            }
            if ($accessedAt !== '' && strtotime($accessedAt) >= (time() - (30 * 86400))) {
                $feeds[$token]['hits_30d']++;
            }
            $client = medexSimplifyCalendarClient((string)($row['user_agent'] ?? ''));
            if (!isset($feeds[$token]['source_counts'][$client])) {
                $feeds[$token]['source_counts'][$client] = 0;
            }
            $feeds[$token]['source_counts'][$client]++;
        }
        foreach ($feeds as &$feed) {
            if (!empty($feed['source_counts']) && is_array($feed['source_counts'])) {
                arsort($feed['source_counts']);
                $sourceCounts = [];
                foreach ($feed['source_counts'] as $client => $count) {
                    $sourceCounts[] = [
                        'client' => (string)$client,
                        'count' => (int)$count,
                    ];
                }
                $feed['source_counts'] = $sourceCounts;
            } else {
                $feed['source_counts'] = [];
            }
        }
        unset($feed);

        echo json_encode([
            'success' => true,
            'feeds' => array_values($feeds),
        ]);
        break;

    case 'delete_calendar_feed_admin':
        medexEnsureCalendarFeedTables();
        $feedId = trim((string)($data['feed_id'] ?? ''));
        $token = trim((string)($data['token'] ?? ''));
        $row = null;
        if ($feedId !== '' && ctype_digit($feedId)) {
            $row = sqlQuery("SELECT id, token FROM medex_calendar_feeds WHERE id = ? LIMIT 1", [(int)$feedId]);
        } elseif ($token !== '') {
            $row = sqlQuery("SELECT id, token FROM medex_calendar_feeds WHERE token = ? LIMIT 1", [$token]);
        }
        if (empty($row)) {
            echo json_encode([
                'success' => false,
                'error' => 'Calendar feed not found'
            ]);
            break;
        }
        sqlStatement("DELETE FROM medex_calendar_feeds WHERE id = ?", [(int)$row['id']]);
        @sqlStatement("DELETE FROM medex_calendar_feed_access_log WHERE feed_token = ?", [(string)($row['token'] ?? '')]);
        echo json_encode([
            'success' => true,
            'message' => 'Calendar feed deleted'
        ]);
        break;

    case 'force_full_sync':
        // Trigger full OpenEMR -> MedEx practice sync from callback-authenticated admin action.
        try {
            require_once(__DIR__ . '/../src/MedExAPI.php');
            require_once(__DIR__ . '/../src/Services/PracticeService.php');

            $api = new \OpenEMR\Modules\MedEx\MedExAPI();
            if (!$api->isConfigured()) {
                echo json_encode([
                    'success' => false,
                    'error' => 'MedEx API is not configured in this OpenEMR instance'
                ]);
                break;
            }

            $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);
            $syncResult = $practiceService->performInitialSync();
            if (!empty($syncResult['success'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Full sync completed',
                    'result' => $syncResult
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => (string)($syncResult['error'] ?? 'sync_failed')
                ]);
            }
        } catch (\Throwable $syncEx) {
            echo json_encode([
                'success' => false,
                'error' => 'sync_exception: ' . $syncEx->getMessage()
            ]);
        }
        break;

    case 'upsert_calendar_category':
        $categoryName = trim((string)($data['category_name'] ?? ''));
        $categoryId = (int)($data['category_id'] ?? 0);
        $duration = (int)($data['duration'] ?? 15);
        $duration = max(5, min(480, $duration));
        $color = trim((string)($data['color'] ?? '#8fa6bd'));
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#8fa6bd';
        }
        if ($categoryName === '') {
            echo json_encode(['success' => false, 'error' => 'Missing category_name']);
            break;
        }

        $existing = null;
        if ($categoryId > 0) {
            $existing = sqlQuery("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1", [$categoryId]);
        }
        if (empty($existing)) {
            $existing = sqlQuery("SELECT pc_catid FROM openemr_postcalendar_categories WHERE LOWER(pc_catname) = LOWER(?) LIMIT 1", [$categoryName]);
        }

        if (!empty($existing['pc_catid'])) {
            $resolvedId = (int)$existing['pc_catid'];
            sqlStatement(
                "UPDATE openemr_postcalendar_categories
                 SET pc_catname = ?, pc_catdesc = ?, pc_catcolor = ?, pc_duration = ?, pc_cattype = 0, pc_active = 1
                 WHERE pc_catid = ?",
                [$categoryName, 'Created in Schedule Assistant', $color, $duration, $resolvedId]
            );
            echo json_encode([
                'success' => true,
                'category_id' => $resolvedId,
                'message' => 'Calendar category updated'
            ]);
            break;
        }

        $seqRow = sqlQuery("SELECT COALESCE(MAX(pc_seq), 0) AS max_seq FROM openemr_postcalendar_categories");
        $nextSeq = ((int)($seqRow['max_seq'] ?? 0)) + 1;
        $constantId = 'medex_' . strtolower(preg_replace('/[^a-z0-9]+/', '_', $categoryName));
        $constantId = trim($constantId, '_');
        if (strlen($constantId) > 120) {
            $constantId = substr($constantId, 0, 120);
        }
        if ($constantId === 'medex') {
            $constantId = 'medex_category_' . time();
        }

        $baseConstantId = $constantId;
        $suffix = 1;
        while (true) {
            $dup = sqlQuery("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_constant_id = ? LIMIT 1", [$constantId]);
            if (empty($dup)) {
                break;
            }
            $constantId = substr($baseConstantId, 0, 110) . '_' . $suffix;
            $suffix++;
        }

        $newId = sqlInsert(
            "INSERT INTO openemr_postcalendar_categories
                (pc_catname, pc_constant_id, pc_catdesc, pc_catcolor, pc_recurrtype, pc_recurrspec, pc_recurrfreq, pc_duration,
                 pc_dailylimit, pc_end_date_flag, pc_end_date_type, pc_end_date_freq, pc_end_all_day, pc_cattype, pc_active, pc_seq, aco_spec)
             VALUES (?, ?, ?, ?, 0, ?, 0, ?, 0, 0, 0, 0, 0, 0, 1, ?, 'encounters|notes')",
            [$categoryName, $constantId, 'Created in Schedule Assistant', $color, '', $duration, $nextSeq]
        );

        echo json_encode([
            'success' => true,
            'category_id' => (int)$newId,
            'message' => 'Calendar category created'
        ]);
        break;

    case 'deactivate_calendar_category':
        $categoryId = (int)($data['category_id'] ?? 0);
        if ($categoryId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Missing category_id']);
            break;
        }
        sqlStatement(
            "UPDATE openemr_postcalendar_categories
             SET pc_active = 0
             WHERE pc_catid = ?",
            [$categoryId]
        );
        echo json_encode([
            'success' => true,
            'category_id' => $categoryId,
            'message' => 'Calendar category deactivated'
        ]);
        break;

    case 'apply_schedule_slots':
        $slots = $data['slots'] ?? [];
        if (!is_array($slots) || empty($slots)) {
            echo json_encode(['success' => false, 'error' => 'No slots provided']);
            break;
        }
        $replaceExisting = ((int)($data['replace_existing'] ?? 0) === 1);
        $source = trim((string)($data['source'] ?? 'schedule_assistant'));
        if ($source === '') {
            $source = 'schedule_assistant';
        }
        $sourceTag = 'MEDEX_' . strtoupper(preg_replace('/[^a-z0-9]+/i', '_', $source));
        $sourceTag = substr($sourceTag, 0, 120);

        // Resolve schema support once.
        $hasLocation = !empty(sqlQuery("SHOW COLUMNS FROM openemr_postcalendar_events LIKE 'pc_location'"));
        $hasFacility = !empty(sqlQuery("SHOW COLUMNS FROM openemr_postcalendar_events LIKE 'pc_facility'"));

        // Optional cleanup of previously generated slots for same provider/date/source.
        if ($replaceExisting) {
            $seen = [];
            foreach ($slots as $slot) {
                $providerId = (int)($slot['provider_id'] ?? 0);
                $date = trim((string)($slot['date'] ?? ''));
                if ($providerId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    continue;
                }
                $key = $providerId . '|' . $date;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                if ($hasLocation) {
                    sqlStatement(
                        "DELETE FROM openemr_postcalendar_events
                         WHERE pc_aid = ?
                           AND pc_eventDate = ?
                           AND COALESCE(pc_pid,'') = ''
                           AND pc_location = ?",
                        [$providerId, $date, $sourceTag]
                    );
                } else {
                    sqlStatement(
                        "DELETE FROM openemr_postcalendar_events
                         WHERE pc_aid = ?
                           AND pc_eventDate = ?
                           AND COALESCE(pc_pid,'') = ''",
                        [$providerId, $date]
                    );
                }
            }
        }

        $inserted = 0;
        $skipped = 0;
        $dayCoverage = [];
        $facilityIds = [];
        $facilityStmt = sqlStatement("SELECT id FROM facility");
        while ($facilityRow = sqlFetchArray($facilityStmt)) {
            $facilityIds[(int)$facilityRow['id']] = true;
        }
        $categoryNames = [];
        $catStmt = sqlStatement("SELECT pc_catid, pc_catname FROM openemr_postcalendar_categories");
        while ($catRow = sqlFetchArray($catStmt)) {
            $categoryNames[(int)$catRow['pc_catid']] = (string)($catRow['pc_catname'] ?? '');
        }
        $providerFacilityCache = [];
        $lunchCategoryRow = sqlQuery(
            "SELECT pc_catid FROM openemr_postcalendar_categories WHERE LOWER(pc_catname) = 'lunch' LIMIT 1"
        );
        $lunchCategoryId = (int)($lunchCategoryRow['pc_catid'] ?? 8);
        if ($lunchCategoryId <= 0) {
            $lunchCategoryId = 8;
        }
        foreach ($slots as $slot) {
            $providerId = (int)($slot['provider_id'] ?? 0);
            $date = trim((string)($slot['date'] ?? ''));
            $start = trim((string)($slot['start'] ?? ''));
            $end = trim((string)($slot['end'] ?? ''));
            $title = trim((string)($slot['title'] ?? 'Open Slot'));
            $preferredCategoryId = (int)($slot['preferred_category_id'] ?? 0);
            $facilityId = (int)($slot['facility_id'] ?? 0);

            if ($providerId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $skipped++;
                continue;
            }

            if ($facilityId <= 0 || empty($facilityIds[$facilityId])) {
                if (!array_key_exists($providerId, $providerFacilityCache)) {
                    $providerRow = sqlQuery(
                        "SELECT facility_id FROM users WHERE id = ? LIMIT 1",
                        [$providerId]
                    );
                    $providerFacilityCache[$providerId] = (int)($providerRow['facility_id'] ?? 0);
                }
                $providerFacilityId = (int)($providerFacilityCache[$providerId] ?? 0);
                $facilityId = !empty($facilityIds[$providerFacilityId]) ? $providerFacilityId : 0;
            }
            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end)) {
                $skipped++;
                continue;
            }
            if (strlen($start) === 5) {
                $start .= ':00';
            }
            if (strlen($end) === 5) {
                $end .= ':00';
            }
            $st = strtotime($date . ' ' . $start);
            $et = strtotime($date . ' ' . $end);
            if ($st === false || $et === false || $et <= $st) {
                $skipped++;
                continue;
            }
            $durationSeconds = (int)round($et - $st);
            if ($durationSeconds <= 0) {
                $skipped++;
                continue;
            }
            // Hard lunch break for automated slot generation. Staff can still override manually in calendar.
            $lunchStartTs = strtotime($date . ' 12:00:00');
            $lunchEndTs = strtotime($date . ' 13:00:00');
            if ($lunchStartTs !== false && $lunchEndTs !== false && $st < $lunchEndTs && $et > $lunchStartTs) {
                $skipped++;
                continue;
            }

            $coverageKey = $providerId . '|' . $date;
            if (!isset($dayCoverage[$coverageKey])) {
                $dayCoverage[$coverageKey] = [
                    'provider_id' => $providerId,
                    'date' => $date,
                    'facility_id' => $facilityId,
                    'earliest' => $st,
                    'latest' => $et
                ];
            } else {
                if ($st < (int)$dayCoverage[$coverageKey]['earliest']) {
                    $dayCoverage[$coverageKey]['earliest'] = $st;
                }
                if ($et > (int)$dayCoverage[$coverageKey]['latest']) {
                    $dayCoverage[$coverageKey]['latest'] = $et;
                }
                if ((int)$dayCoverage[$coverageKey]['facility_id'] <= 0 && $facilityId > 0) {
                    $dayCoverage[$coverageKey]['facility_id'] = $facilityId;
                }
            }

            if ($preferredCategoryId > 0) {
                $preferredName = trim((string)($categoryNames[$preferredCategoryId] ?? ''));
                if ($preferredName !== '') {
                    $titleLower = strtolower($title);
                    if (
                        $title === ''
                        || $titleLower === 'open slot'
                        || $titleLower === '[ai] available'
                        || str_starts_with($titleLower, 'open slot - ')
                    ) {
                        $title = 'Open Slot - ' . $preferredName;
                    }
                }
            }

            // Keep IN/OUT markers separate and persist actual slot category directly.
            $eventCatId = $preferredCategoryId > 0 ? $preferredCategoryId : 2;

            if ($hasLocation && $hasFacility) {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location, pc_facility)
                     VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?, ?, ?)",
                    [$eventCatId, $providerId, $title, $date, $date, $durationSeconds, $start, $end, $preferredCategoryId, $sourceTag, $facilityId]
                );
            } elseif ($hasLocation) {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location)
                     VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?, ?)",
                    [$eventCatId, $providerId, $title, $date, $date, $durationSeconds, $start, $end, $preferredCategoryId, $sourceTag]
                );
            } else {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid)
                     VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, ?)",
                    [$eventCatId, $providerId, $title, $date, $date, $durationSeconds, $start, $end, $preferredCategoryId]
                );
            }
            $inserted++;
        }

        // Document lunch as an explicit non-available block for automation logic.
        // Insert only when the generated workday spans over lunch hours.
        foreach ($dayCoverage as $coverage) {
            $providerId = (int)$coverage['provider_id'];
            $date = (string)$coverage['date'];
            $facilityId = (int)$coverage['facility_id'];
            $earliest = (int)$coverage['earliest'];
            $latest = (int)$coverage['latest'];

            // Create explicit day boundaries for OpenEMR availability checks.
            // OpenEMR's find_appt logic treats IN/OUT as state toggles, not ranges.
            $inOfficeStart = date('H:i:s', $earliest);
            $outOfficeStart = date('H:i:s', $latest);
            if ($outOfficeStart <= $inOfficeStart) {
                continue;
            }

            if ($hasLocation && $hasFacility) {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location, pc_facility)
                     VALUES (2, 0, ?, '', 'In Office', NOW(), '', ?, ?, 0, ?, ?, 0, '-', 1, 0, ?, ?)",
                    [$providerId, $date, $date, $inOfficeStart, $inOfficeStart, $sourceTag, $facilityId]
                );
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location, pc_facility)
                     VALUES (3, 0, ?, '', 'Out Of Office', NOW(), '', ?, ?, 0, ?, ?, 0, '-', 1, 0, ?, ?)",
                    [$providerId, $date, $date, $outOfficeStart, $outOfficeStart, $sourceTag, $facilityId]
                );
            } elseif ($hasLocation) {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location)
                     VALUES (2, 0, ?, '', 'In Office', NOW(), '', ?, ?, 0, ?, ?, 0, '-', 1, 0, ?)",
                    [$providerId, $date, $date, $inOfficeStart, $inOfficeStart, $sourceTag]
                );
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location)
                     VALUES (3, 0, ?, '', 'Out Of Office', NOW(), '', ?, ?, 0, ?, ?, 0, '-', 1, 0, ?)",
                    [$providerId, $date, $date, $outOfficeStart, $outOfficeStart, $sourceTag]
                );
            } else {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid)
                     VALUES (2, 0, ?, '', 'In Office', NOW(), '', ?, ?, 0, ?, ?, 0, '-', 1, 0)",
                    [$providerId, $date, $date, $inOfficeStart, $inOfficeStart]
                );
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid)
                     VALUES (3, 0, ?, '', 'Out Of Office', NOW(), '', ?, ?, 0, ?, ?, 0, '-', 1, 0)",
                    [$providerId, $date, $date, $outOfficeStart, $outOfficeStart]
                );
            }

            $lunchStartTs = strtotime($date . ' 12:00:00');
            $lunchEndTs = strtotime($date . ' 13:00:00');

            if ($lunchStartTs === false || $lunchEndTs === false) {
                continue;
            }
            if (!($earliest < $lunchStartTs && $latest > $lunchEndTs)) {
                continue;
            }

            $lunchStart = '12:00:00';
            $lunchEnd = '13:00:00';
            $lunchDuration = 3600;

            if ($hasLocation && $hasFacility) {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location, pc_facility)
                     VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, 0, ?, ?)",
                    [$lunchCategoryId, $providerId, 'Lunch', $date, $date, $lunchDuration, $lunchStart, $lunchEnd, $sourceTag, $facilityId]
                );
            } elseif ($hasLocation) {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid, pc_location)
                     VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, 0, ?)",
                    [$lunchCategoryId, $providerId, 'Lunch', $date, $date, $lunchDuration, $lunchStart, $lunchEnd, $sourceTag]
                );
            } else {
                sqlInsert(
                    "INSERT INTO openemr_postcalendar_events
                     (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_eventDate, pc_endDate, pc_duration, pc_startTime, pc_endTime,
                      pc_alldayevent, pc_apptstatus, pc_eventstatus, pc_prefcatid)
                     VALUES (?, 0, ?, '', ?, NOW(), '', ?, ?, ?, ?, ?, 0, '-', 1, 0)",
                    [$lunchCategoryId, $providerId, 'Lunch', $date, $date, $lunchDuration, $lunchStart, $lunchEnd]
                );
            }
        }

        echo json_encode([
            'success' => true,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'message' => 'Schedule slots applied'
        ]);
        break;

    default:
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Unknown action: ' . $action);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action: ' . $action
        ]);
        break;
}

// Log successful completion
error_log('[MedEx Callback][' . $requestId . '] Request completed successfully');
