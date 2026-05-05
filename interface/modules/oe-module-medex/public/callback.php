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

    return array_merge($_GET, $_POST);
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
if (!in_array($action, ['ping', 'get_module_status', 'set_module_enabled'], true) && ($GLOBALS['medex_enable'] ?? '0') != '1') {
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
        require_once($GLOBALS['srcdir'] . '/patient.inc.php');
        $providers = [];
        $providerRows = getProviderInfo('%', true);
        if (is_array($providerRows)) {
            foreach ($providerRows as $row) {
                $id = (string)($row['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $name = trim(((string)($row['fname'] ?? '')) . ' ' . ((string)($row['lname'] ?? '')));
                $providers[] = [
                    'id' => $id,
                    'name' => $name !== '' ? $name : ((string)($row['username'] ?? ('Provider ' . $id))),
                    'active' => true
                ];
            }
        }

        $facilities = [];
        $facilityStmt = sqlStatement("SELECT id, name FROM facility ORDER BY id ASC");
        while ($frow = sqlFetchArray($facilityStmt)) {
            $fid = (string)($frow['id'] ?? '');
            if ($fid === '') {
                continue;
            }
            $facilities[] = [
                'id' => $fid,
                'name' => (string)($frow['name'] ?? ('Facility ' . $fid))
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
