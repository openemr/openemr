<?php

/**
 * Safety Sentinel Tab Content
 *
 * Reads the active patient from session, looks up their UUID and name,
 * then renders an iframe to the Safety Sentinel FastAPI frontend
 * pre-populated with the current patient context.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Path depth: public/ → oe-module-safety-sentinel/ → custom_modules/ → modules/ → interface/ → openemr root
require_once dirname(__FILE__, 5) . "/globals.php";

use OpenEMR\Core\Header;

// ── Safety Sentinel backend URL ──────────────────────────────────────────────
// Set $GLOBALS['safety_sentinel_url'] in the OpenEMR globals table for deployed usage:
//   UPDATE globals SET gl_value='http://161.35.61.60:8001' WHERE gl_name='safety_sentinel_url';
// Falls back to localhost for local development.
$sentinelUrl = $GLOBALS['safety_sentinel_url'] ?? 'http://localhost:8001';

// ── Current patient from session ─────────────────────────────────────────────
$pid = (int)($_SESSION['pid'] ?? 0);

if (empty($pid)) {
    ?>
    <div style="padding:40px;text-align:center;color:#6b7280;font-family:system-ui,sans-serif;">
        <p><?php echo xlt("Please select a patient from the patient list first."); ?></p>
    </div>
    <?php
    exit;
}

// ── Look up UUID and name via SQL ─────────────────────────────────────────────
// UUIDs are stored as binary in OpenEMR; HEX() converts to a 32-char hex string
// which we reassemble into standard UUID format (8-4-4-4-12).
$pt = sqlQuery(
    "SELECT HEX(uuid) AS uuid_hex, fname, lname FROM patient_data WHERE pid = ?",
    [$pid]
);

$puuid       = '';
$patientName = '';

if ($pt) {
    $hex = strtolower($pt['uuid_hex'] ?? '');
    if (strlen($hex) === 32) {
        $puuid = implode('-', [
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ]);
    }
    $patientName = trim(($pt['fname'] ?? '') . ' ' . ($pt['lname'] ?? ''));
}

if (empty($puuid)) {
    ?>
    <div style="padding:40px;text-align:center;color:#ef4444;font-family:system-ui,sans-serif;">
        <p><?php echo xlt("Could not resolve patient UUID. Please try reloading the chart."); ?></p>
    </div>
    <?php
    exit;
}

// ── Build iframe URL ──────────────────────────────────────────────────────────
$iframeUrl = $sentinelUrl . '/?' . http_build_query([
    'patient_id'   => $puuid,
    'patient_name' => $patientName,
]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo xla('Safety Check'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        #safety-sentinel-frame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>
<body>
<iframe
    id="safety-sentinel-frame"
    src="<?php echo attr($iframeUrl); ?>"
    title="<?php echo xla('Safety Sentinel Clinical Safety Check'); ?>"
    sandbox="allow-scripts allow-same-origin allow-forms"
></iframe>
</body>
</html>
