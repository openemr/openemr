<?php
/**
 * OpenEMR Calendar Feed - HIPAA Compliant Direct iCal Generator
 *
 * Generates iCal feed DIRECTLY from OpenEMR appointments database.
 * No MedEx proxy needed - all data comes from local openemr_postcalendar_events.
 * Authentication against OpenEMR user database - password never leaves OpenEMR.
 *
 * Usage: https://your-openemr.com/.../calendar_feed.php?feed=TOKEN
 * Auth: HTTP Basic with OpenEMR username + password
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Minimal bootstrap - we need auth without full session (calendar apps don't support cookies)
$ignoreAuth = true;
require_once(__DIR__ . '/../../../../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Auth\AuthHash;

header('Content-Type: text/calendar; charset=utf-8');

// Get feed token
$feedToken = $_GET['feed'] ?? '';
if (empty($feedToken) || strlen($feedToken) !== 64) {
    logCalendarAccess(null, null, $feedToken, false, 'Invalid or missing feed token');
    http_response_code(400);
    echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nX-ERROR:Invalid feed token\r\nEND:VCALENDAR";
    exit;
}

// Get HTTP Basic Auth credentials
$username = $_SERVER['PHP_AUTH_USER'] ?? '';
$password = $_SERVER['PHP_AUTH_PW'] ?? '';

if (empty($username) || empty($password)) {
    header('WWW-Authenticate: Basic realm="OpenEMR Calendar Feed"');
    logCalendarAccess(null, null, $feedToken, false, 'No credentials provided');
    http_response_code(401);
    echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nX-ERROR:Authentication required\r\nEND:VCALENDAR";
    exit;
}

// Validate credentials against OpenEMR user database
$userId = validateOpenEMRCredentials($username, $password);
if (!$userId) {
    header('WWW-Authenticate: Basic realm="OpenEMR Calendar Feed"');
    logCalendarAccess(null, $username, $feedToken, false, 'Invalid credentials');
    http_response_code(401);
    echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nX-ERROR:Invalid username or password\r\nEND:VCALENDAR";
    exit;
}

// Get/verify user owns this feed and get feed configuration
$feedConfig = verifyFeedOwnership($feedToken, $userId, $username);
if (!$feedConfig['valid']) {
    logCalendarAccess($userId, $username, $feedToken, false, 'Feed not owned by user or not found');
    http_response_code(403);
    echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nX-ERROR:Access denied to this feed\r\nEND:VCALENDAR";
    exit;
}

// Generate iCal directly from OpenEMR appointments
$icalContent = generateIcalFromOpenEMR($feedConfig, $userId);

// Success - log and return iCal
logCalendarAccess($userId, $username, $feedToken, true, 'Success');
echo $icalContent;
exit;

/**
 * Generate iCal content directly from OpenEMR's openemr_postcalendar_events table
 */
function generateIcalFromOpenEMR(array $feedConfig, int $userId): string
{
    $lines = [];
    $lines[] = "BEGIN:VCALENDAR";
    $lines[] = "VERSION:2.0";
    $lines[] = "PRODID:-//OpenEMR//MedEx Calendar Feed//EN";
    $lines[] = "CALSCALE:GREGORIAN";
    $lines[] = "METHOD:PUBLISH";
    $lines[] = "X-WR-CALNAME:OpenEMR Appointments";
    
    // Build WHERE clause based on feed configuration
    $where = ["e.pc_recurrtype = 0"]; // Non-recurring only for simplicity
    $params = [];
    
    // Filter by providers if specified
    $providerIds = $feedConfig['provider_ids'] ?? [];
    if (!empty($providerIds)) {
        $placeholders = implode(',', array_fill(0, count($providerIds), '?'));
        $where[] = "e.pc_aid IN ($placeholders)";
        $params = array_merge($params, $providerIds);
    }
    
    // Filter by facilities if specified
    $facilityIds = $feedConfig['facility_ids'] ?? [];
    if (!empty($facilityIds)) {
        $placeholders = implode(',', array_fill(0, count($facilityIds), '?'));
        $where[] = "e.pc_facility IN ($placeholders)";
        $params = array_merge($params, $facilityIds);
    }
    
    // Date range: past 30 days to future 90 days
    $startDate = date('Y-m-d', strtotime('-30 days'));
    $endDate = date('Y-m-d', strtotime('+90 days'));
    $where[] = "e.pc_eventDate >= ?";
    $where[] = "e.pc_eventDate <= ?";
    $params[] = $startDate;
    $params[] = $endDate;
    
    $whereClause = implode(' AND ', $where);
    
    // Query appointments with patient and provider info
    $sql = "
        SELECT 
            e.pc_eid,
            e.pc_eventDate,
            e.pc_startTime,
            e.pc_endTime,
            e.pc_duration,
            e.pc_hometext,
            e.pc_title,
            e.pc_apptstatus,
            e.pc_aid,
            e.pc_facility,
            e.pc_pid,
            CONCAT(pd.fname, ' ', pd.lname) AS patient_name,
            CONCAT(u.fname, ' ', u.lname) AS provider_name,
            f.name AS facility_name
        FROM openemr_postcalendar_events e
        LEFT JOIN patient_data pd ON e.pc_pid = pd.pid
        LEFT JOIN users u ON e.pc_aid = u.id
        LEFT JOIN facility f ON e.pc_facility = f.id
        WHERE $whereClause
        ORDER BY e.pc_eventDate, e.pc_startTime
    ";
    
    $result = sqlStatement($sql, $params);
    
    $statusMap = [
        '-' => 'Pending',
        '+' => 'Arrived',
        'x' => 'Cancelled',
        '?' => 'No Show',
        '@' => 'Arrived',
        '~' => 'Left',
        '!' => 'Arrived Late',
        '#' => 'Ins Pending',
        '<' => 'In Room',
        '>' => 'Checked Out',
        '$' => 'Complete',
        '%' => 'Cancelled',
        '^' => 'Pending'
    ];
    
    while ($row = sqlFetchArray($result)) {
        $eventId = $row['pc_eid'];
        $eventDate = $row['pc_eventDate'];
        $startTime = $row['pc_startTime'];
        $duration = (int)$row['pc_duration'];
        
        // Calculate end time
        if (!empty($row['pc_endTime']) && $row['pc_endTime'] !== '00:00:00') {
            $endTime = $row['pc_endTime'];
        } else {
            $startTs = strtotime("$eventDate $startTime");
            $endTs = $startTs + ($duration * 60);
            $endTime = date('H:i:s', $endTs);
        }
        
        // Format dates for iCal (YYYYMMDDTHHMMSS)
        $dtStart = date('Ymd\THis', strtotime("$eventDate $startTime"));
        $dtEnd = date('Ymd\THis', strtotime("$eventDate $endTime"));
        $dtStamp = gmdate('Ymd\THis\Z');
        
        // Build summary
        $patientName = $row['patient_name'] ?: 'No Patient';
        $providerName = $row['provider_name'] ?: 'Unassigned';
        $status = $statusMap[$row['pc_apptstatus']] ?? $row['pc_apptstatus'];
        $summary = escapeIcal("$patientName - $providerName [$status]");
        
        // Build description
        $desc = [];
        if (!empty($row['pc_title'])) {
            $desc[] = "Type: " . $row['pc_title'];
        }
        $desc[] = "Status: $status";
        $desc[] = "Provider: $providerName";
        if (!empty($row['facility_name'])) {
            $desc[] = "Location: " . $row['facility_name'];
        }
        if (!empty($row['pc_hometext'])) {
            $desc[] = "Notes: " . $row['pc_hometext'];
        }
        $description = escapeIcal(implode('\n', $desc));
        
        // Location
        $location = escapeIcal($row['facility_name'] ?? '');
        
        $lines[] = "BEGIN:VEVENT";
        $lines[] = "UID:openemr-$eventId@" . ($_SERVER['HTTP_HOST'] ?? 'openemr.local');
        $lines[] = "DTSTAMP:$dtStamp";
        $lines[] = "DTSTART:$dtStart";
        $lines[] = "DTEND:$dtEnd";
        $lines[] = "SUMMARY:$summary";
        if (!empty($description)) {
            $lines[] = "DESCRIPTION:$description";
        }
        if (!empty($location)) {
            $lines[] = "LOCATION:$location";
        }
        $lines[] = "END:VEVENT";
    }
    
    $lines[] = "END:VCALENDAR";
    
    return implode("\r\n", $lines);
}

/**
 * Escape text for iCal format
 */
function escapeIcal(string $text): string
{
    $text = str_replace("\\", "\\\\", $text);
    $text = str_replace(",", "\\,", $text);
    $text = str_replace(";", "\\;", $text);
    $text = str_replace("\n", "\\n", $text);
    $text = str_replace("\r", "", $text);
    return $text;
}

/**
 * Validate OpenEMR credentials
 * @return int|false User ID on success, false on failure
 */
function validateOpenEMRCredentials(string $username, string $password)
{
    $user = sqlQuery(
        "SELECT u.id, u.username, us.password 
         FROM users u
         JOIN users_secure us ON us.id = u.id AND BINARY us.username = u.username
         WHERE BINARY u.username = ? AND u.active = 1 
         LIMIT 1",
        [$username]
    );
    
    if (empty($user) || empty($user['password'])) {
        return false;
    }
    
    $valid = AuthHash::passwordVerify($password, $user['password']);
    if (!$valid) {
        return false;
    }
    
    return (int)$user['id'];
}

/**
 * Verify user owns this feed token and get feed configuration
 */
function verifyFeedOwnership(string $token, int $userId, string $username): array
{
    $feed = sqlQuery(
        "SELECT id, openemr_user_id, openemr_username, providers, facilities FROM medex_calendar_feeds WHERE token = ? LIMIT 1",
        [$token]
    );
    
    if (empty($feed)) {
        return ['valid' => false, 'reason' => 'Feed not found'];
    }
    
    $ownerId = (int)($feed['openemr_user_id'] ?? 0);
    $ownerUsername = (string)($feed['openemr_username'] ?? '');
    $isOwner = ($ownerId > 0 && $ownerId === $userId);
    $isAdmin = AclMain::aclCheckCore('admin', 'super');

    if (!$isOwner) {
        if ($ownerId > 0 && !$isAdmin) {
            return ['valid' => false, 'reason' => 'Feed belongs to different user'];
        }
        // Legacy feeds may only have username; tolerate case differences.
        if ($ownerId <= 0 && $ownerUsername !== '' && strcasecmp($ownerUsername, $username) !== 0 && !$isAdmin) {
            return ['valid' => false, 'reason' => 'Username mismatch'];
        }
    }
    
    // Parse provider/facility filters - could be JSON array or comma-separated or single value
    $providerIds = parseIdList($feed['providers'] ?? '');
    $facilityIds = parseIdList($feed['facilities'] ?? '');
    
    return [
        'valid' => true,
        'feed_id' => $feed['id'],
        'provider_ids' => $providerIds,
        'facility_ids' => $facilityIds
    ];
}

/**
 * Parse ID list from various formats: JSON array, comma-separated, or single value
 */
function parseIdList($value): array
{
    if (empty($value)) {
        return [];
    }
    
    // Try JSON decode first
    $decoded = json_decode($value, true);
    if (is_array($decoded)) {
        return array_map('intval', $decoded);
    }
    
    // If it's a string with commas, split it
    if (is_string($value) && strpos($value, ',') !== false) {
        return array_map('intval', explode(',', $value));
    }
    
    // Single value
    if (is_numeric($value)) {
        return [(int)$value];
    }
    
    return [];
}

/**
 * Log calendar feed access for HIPAA compliance
 */
function logCalendarAccess(?int $userId, ?string $username, string $feedToken, bool $success, string $message): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 500);
    
    $logMessage = sprintf(
        'Calendar feed access: user=%s, feed=%s, success=%s, ip=%s, reason=%s',
        $username ?? 'unknown',
        substr($feedToken, 0, 16) . '...',
        $success ? 'yes' : 'no',
        $ip,
        $message
    );
    
    if (class_exists(EventAuditLogger::class)) {
        try {
            $auditLogger = null;
            if (is_callable([EventAuditLogger::class, 'getInstance'])) {
                $auditLogger = EventAuditLogger::getInstance();
            } elseif (is_callable([EventAuditLogger::class, 'instance'])) {
                $auditLogger = EventAuditLogger::instance();
            } else {
                error_log('[MedEx Calendar Feed] EventAuditLogger singleton accessor unavailable; audit event skipped.');
            }
            if ($auditLogger) {
                $auditLogger->newEvent(
                    'calendar-feed-access',
                    $username ?? 'unknown',
                    'default',
                    $success ? 1 : 0,
                    $logMessage
                );
            }
        } catch (\Throwable $auditError) {
            error_log('[MedEx Calendar Feed] Audit log failed: ' . $auditError->getMessage());
        }
    }
    
    static $tableChecked = false;
    if (!$tableChecked) {
        createAccessLogTable();
        $tableChecked = true;
    }

    $available = getAccessLogColumns();
    if (empty($available)) {
        return;
    }

    $values = [
        'feed_token' => $feedToken,
        'openemr_user_id' => $userId,
        'openemr_username' => $username,
        'ip_address' => $ip,
        'user_agent' => $userAgent,
        'success' => $success ? 1 : 0,
        'message' => $message,
    ];
    // Backward compatibility with older table variants.
    if (in_array('username', $available, true) && !in_array('openemr_username', $available, true)) {
        $values['username'] = $username;
    }
    if (in_array('user_id', $available, true) && !in_array('openemr_user_id', $available, true)) {
        $values['user_id'] = $userId;
    }

    $insertCols = [];
    $insertVals = [];
    foreach ($values as $col => $val) {
        if (in_array($col, $available, true)) {
            $insertCols[] = $col;
            $insertVals[] = $val;
        }
    }

    if (in_array('accessed_at', $available, true)) {
        $insertCols[] = 'accessed_at';
    }
    if (empty($insertCols)) {
        return;
    }

    $sqlParts = [];
    $params = [];
    foreach ($insertCols as $col) {
        if ($col === 'accessed_at') {
            $sqlParts[] = 'NOW()';
            continue;
        }
        $sqlParts[] = '?';
        $params[] = array_shift($insertVals);
    }

    @sqlStatement(
        "INSERT INTO medex_calendar_feed_access_log (" . implode(', ', $insertCols) . ")
         VALUES (" . implode(', ', $sqlParts) . ")",
        $params
    );
}

/**
 * Create access log table if it doesn't exist
 */
function createAccessLogTable(): void
{
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

function getAccessLogColumns(): array
{
    static $columns = null;
    if (is_array($columns)) {
        return $columns;
    }

    $columns = [];
    $result = @sqlStatement("SHOW COLUMNS FROM medex_calendar_feed_access_log");
    if (!$result) {
        return $columns;
    }
    while ($row = sqlFetchArray($result)) {
        $field = (string)($row['Field'] ?? '');
        if ($field !== '') {
            $columns[] = $field;
        }
    }
    return $columns;
}
