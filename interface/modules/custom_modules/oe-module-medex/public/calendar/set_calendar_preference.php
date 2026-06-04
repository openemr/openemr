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
 * Set calendar preference (MedEx vs OpenEMR)
 *
 * Supports two modes:
 *   1. AJAX (POST only, no redirect param) — returns JSON
 *   2. Direct navigation (GET/POST with redirect param) — sets session then redirects
 *      This is the preferred mode as it avoids AJAX race conditions.
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Session\SessionWrapperFactory;

// Only require the user to be authenticated — this endpoint just sets a UI preference.
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

if (empty($authUserId)) {
    http_response_code(403);
    die(json_encode(['error' => 'Not authenticated']));
}

$userId = (int)$authUserId;
if ($userId <= 0) {
    http_response_code(403);
    die(json_encode(['error' => 'Not authenticated']));
}

// Get the preference and optional redirect target
$preference = $_POST['preference'] ?? $_GET['preference'] ?? null;
$redirectTarget = $_POST['redirect'] ?? $_GET['redirect'] ?? null;

if ($preference === 'openemr') {
    $_SESSION['medex_use_openemr_calendar'] = true;
    $useFullCalendar = '0';
    error_log('[MedEx] User chose OpenEMR calendar - setting session flag');
} elseif ($preference === 'medex') {
    $_SESSION['medex_use_openemr_calendar'] = false;
    $useFullCalendar = '1';
    error_log('[MedEx] User chose MedEx calendar - clearing session flag');
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid preference. Must be "openemr" or "medex"']);
    exit;
}

sqlStatement(
    "INSERT INTO user_settings (setting_user, setting_label, setting_value)
     VALUES (?, 'global:medex_use_full_calendar', ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    [$userId, $useFullCalendar]
);

$existingLegacyPref = sqlQuery(
    "SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = 'medex_preferences' LIMIT 1",
    [$userId]
);
$legacyPrefs = [];
if (!empty($existingLegacyPref['setting_value'])) {
    $decodedPrefs = json_decode((string)$existingLegacyPref['setting_value'], true);
    if (is_array($decodedPrefs)) {
        $legacyPrefs = $decodedPrefs;
    }
}
$legacyPrefs['use_full_calendar'] = ($useFullCalendar === '1') ? 1 : 0;
$legacyJson = json_encode($legacyPrefs);

sqlStatement(
    "INSERT INTO user_settings (setting_user, setting_label, setting_value)
     VALUES (?, 'medex_preferences', ?)
     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    [$userId, $legacyJson]
);

// If a redirect target was provided, redirect there (session is already written)
if (!empty($redirectTarget)) {
    // Basic safety check — only allow relative paths starting with /
    $redirectTarget = '/' . ltrim($redirectTarget, '/');
    header('Location: ' . $redirectTarget);
    exit;
}

// AJAX mode — return JSON
echo json_encode(['success' => true, 'preference' => $preference]);
