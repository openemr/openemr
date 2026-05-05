<?php
/**
 * Set calendar preference (MedEx vs OpenEMR)
 *
 * Supports two modes:
 *   1. AJAX (POST only, no redirect param) — returns JSON
 *   2. Direct navigation (GET/POST with redirect param) — sets session then redirects
 *      This is the preferred mode as it avoids AJAX race conditions.
 */

require_once(__DIR__ . "/../../../../../globals.php");

// Only require the user to be authenticated — this endpoint just sets a UI preference.
if (empty($_SESSION['authUserID'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Not authenticated']));
}

// Get the preference and optional redirect target
$preference = $_POST['preference'] ?? $_GET['preference'] ?? null;
$redirectTarget = $_POST['redirect'] ?? $_GET['redirect'] ?? null;

if ($preference === 'openemr') {
    $_SESSION['medex_use_openemr_calendar'] = true;
    error_log('[MedEx] User chose OpenEMR calendar - setting session flag');
} elseif ($preference === 'medex') {
    $_SESSION['medex_use_openemr_calendar'] = false;
    error_log('[MedEx] User chose MedEx calendar - clearing session flag');
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid preference. Must be "openemr" or "medex"']);
    exit;
}

// If a redirect target was provided, redirect there (session is already written)
if (!empty($redirectTarget)) {
    // Basic safety check — only allow relative paths starting with /
    $redirectTarget = '/' . ltrim($redirectTarget, '/');
    header('Location: ' . $redirectTarget);
    exit;
}

// AJAX mode — return JSON
echo json_encode(['success' => true, 'preference' => $preference]);
