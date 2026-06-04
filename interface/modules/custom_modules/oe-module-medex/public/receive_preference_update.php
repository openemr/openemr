<?php
/**
 * PROPRIETARY AND CONFIDENTIAL
 * Copyright (c) 2024-2026 MedEx <support@MedExBank.com>
 * All Rights Reserved.
 *
 * Receives patient communication preference updates from the MedEx SaaS
 * patient preferences page and applies them to OpenEMR patient_data.
 *
 * @package   MedEx
 * @copyright Copyright (c) 2024-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

$ignoreAuth        = true;
$sessionAllowWrite = false;
require_once(__DIR__ . '/../../../../globals.php');

header('Content-Type: application/json');

$api_key     = trim((string)($_POST['api_key']     ?? ''));
$practice_id = (int)($_POST['practice_id'] ?? 0);
$pid         = (int)($_POST['pid']         ?? 0);
$allow_sms   = strtoupper(trim((string)($_POST['allow_sms']   ?? '')));
$allow_email = strtoupper(trim((string)($_POST['allow_email'] ?? '')));
$allow_avm   = strtoupper(trim((string)($_POST['allow_avm']   ?? '')));

if ($practice_id <= 0 || $pid <= 0 || $api_key === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Validate API key against stored key for this practice
$prefs = sqlQuery(
    "SELECT ME_api_key FROM medex_prefs WHERE MedEx_id = ? LIMIT 1",
    [$practice_id]
);
if (empty($prefs['ME_api_key']) || !hash_equals((string)$prefs['ME_api_key'], $api_key)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid API key']);
    exit;
}

// Normalise values to YES/NO (allow empty → keep existing via COALESCE)
$sms_val   = ($allow_sms   === 'YES') ? 'YES' : (($allow_sms   === 'NO') ? 'NO' : null);
$email_val = ($allow_email === 'YES') ? 'YES' : (($allow_email === 'NO') ? 'NO' : null);
$avm_val   = ($allow_avm   === 'YES') ? 'YES' : (($allow_avm   === 'NO') ? 'NO' : null);

// Build SET clauses for only the fields that were explicitly provided
$setClauses = [];
$params     = [];
if ($sms_val !== null)   { $setClauses[] = "hipaa_allowsms   = ?"; $params[] = $sms_val; }
if ($email_val !== null) { $setClauses[] = "hipaa_allowemail = ?"; $params[] = $email_val; }
if ($avm_val !== null)   { $setClauses[] = "hipaa_voice      = ?"; $params[] = $avm_val; }

if (empty($setClauses)) {
    echo json_encode(['success' => true, 'message' => 'Nothing to update']);
    exit;
}

$params[] = $pid;
sqlStatement(
    "UPDATE patient_data SET " . implode(', ', $setClauses) . " WHERE pid = ?",
    $params
);

error_log("[MedEx] receive_preference_update: pid={$pid} sms={$allow_sms} email={$allow_email} avm={$allow_avm}");

echo json_encode(['success' => true, 'pid' => $pid]);
