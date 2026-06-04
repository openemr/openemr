<?php
/**
 * PROPRIETARY AND CONFIDENTIAL
 * Copyright (c) 2024-2026 MedEx <support@MedExBank.com>
 * All Rights Reserved.
 *
 * This file is part of the MedEx SaaS platform and is NOT open-source software.
 * Unauthorized copying, distribution, modification, or use of this file, via any
 * medium, is strictly prohibited without the express written permission of MedEx.
 *
 * @package   MedEx
 * @copyright Copyright (c) 2024-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

/**
 * Server-Sent Events stream for MedEx Full Calendar sync.
 * Pushes lightweight change notifications so clients refetch only when needed.
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . '/../../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;

if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    exit;
}

$sessionWrapper = null;
try {
    $sessionWrapper = SessionWrapperFactory::getInstance()->getActiveSession();
} catch (\Throwable $e) {
    $sessionWrapper = null;
}

$authUserId = $_SESSION['authUserID'] ?? null;
if (empty($authUserId) && $sessionWrapper) {
    $authUserId = $sessionWrapper->get('authUserID') ?: $sessionWrapper->get('authUser');
}

$api = new \OpenEMR\Modules\MedEx\MedExAPI();
if (!$api->hasServiceEntitlement('calendar_full')) {
    http_response_code(403);
    exit;
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', '0');
@ini_set('implicit_flush', '1');
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);

set_time_limit(0);
ignore_user_abort(true);

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$providers = isset($_GET['providers']) ? explode(',', (string)$_GET['providers']) : [];
$facilities = isset($_GET['facilities']) ? explode(',', (string)$_GET['facilities']) : [];

$providers = array_values(array_filter(array_map('trim', $providers), static function ($value) {
    return $value !== '';
}));

$facilities = array_values(array_filter(array_map('trim', $facilities), static function ($value) {
    return ctype_digit((string)$value);
}));

if (empty($start) || empty($end)) {
    $start = date('Y-m-d 00:00:00');
    $end = date('Y-m-d 23:59:59', strtotime('+30 days'));
}

/**
 * Build a compact signature for visible calendar rows.
 * Any move/resize/provider/facility/status change in this slice should alter signature.
 */
function getCalendarSliceSignature($start, $end, $providers, $facilities)
{
    $sql = "SELECT
                COUNT(*) AS row_count,
                COALESCE(SUM(CRC32(CONCAT_WS('|',
                    pc.pc_eid,
                    pc.pc_eventDate,
                    pc.pc_startTime,
                    pc.pc_endTime,
                    pc.pc_duration,
                    pc.pc_aid,
                    pc.pc_facility,
                    pc.pc_pid,
                    pc.pc_apptstatus,
                    pc.pc_eventstatus
                ))), 0) AS sig_sum
            FROM openemr_postcalendar_events pc
            LEFT JOIN users u ON pc.pc_aid = u.id
            WHERE pc.pc_eventstatus = 1
              AND pc.pc_eventDate >= ?
              AND pc.pc_eventDate <= ?";

    $params = [$start, $end];

    if (!empty($providers)) {
        $placeholders = implode(',', array_fill(0, count($providers), '?'));
        $sql .= " AND u.username IN ($placeholders)";
        $params = array_merge($params, $providers);
    }

    if (!empty($facilities)) {
        $placeholders = implode(',', array_fill(0, count($facilities), '?'));
        $sql .= " AND pc.pc_facility IN ($placeholders)";
        $params = array_merge($params, $facilities);
    }

    $row = sqlQuery($sql, $params);
    if (!$row) {
        return '0:0';
    }

    return (string)($row['row_count'] ?? '0') . ':' . (string)($row['sig_sum'] ?? '0');
}

function sseEmit($event, $payload)
{
    echo "event: " . $event . "\n";
    echo "data: " . json_encode($payload) . "\n\n";
    @flush();
}

$previousSignature = '';
$startedAt = time();
$maxRuntimeSeconds = 55;
$tickSeconds = 4;
$heartbeatEveryTicks = 5;
$tick = 0;

sseEmit('connected', ['ok' => true, 'ts' => time()]);

while (!connection_aborted() && (time() - $startedAt) < $maxRuntimeSeconds) {
    $signature = getCalendarSliceSignature($start, $end, $providers, $facilities);

    if ($signature !== $previousSignature) {
        sseEmit('calendar-update', ['signature' => $signature, 'ts' => time()]);
        $previousSignature = $signature;
    } elseif ($tick % $heartbeatEveryTicks === 0) {
        sseEmit('heartbeat', ['ts' => time()]);
    }

    $tick++;
    sleep($tickSeconds);
}

sseEmit('disconnect', ['reason' => 'reconnect']);
