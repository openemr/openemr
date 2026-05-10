<?php

/**
 * Patient-view chooser launcher.
 *
 * Bridges the legacy OpenEMR patient-finder click flow into a small
 * "which view do you want" page. Two buttons:
 *
 *   - Modern Dashboard (Next.js)  -> ${DASHBOARD_URL}/patient/<uuid>
 *     Click escapes OpenEMR's iframe chrome (window.top.location.replace)
 *     because the modern dashboard sets CSP frame-ancestors 'none' and
 *     browsers refuse to render it inside any iframe.
 *
 *   - Legacy View (OpenEMR)       -> demographics.php?set_pid=<pid>
 *     Click stays inside the same iframe slot (window.location.href),
 *     so OpenEMR's normal patient-summary chrome renders as before.
 *
 * If `DASHBOARD_URL` is unset OR the patient has no FHIR UUID, the
 * chooser is skipped and we transparently fall through to the legacy
 * view. Same fallback behavior as before, just with the chooser added
 * for the happy path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ruijing Wang <wrjgouwu@gmail.com>
 * @copyright Copyright (c) 2026 Ruijing Wang
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Uuid\UuidRegistry;

function dashboard_redirect_fallback(): void
{
    // Pass all original query params through to the legacy view so any
    // caller that depended on extra args keeps working.
    header('Location: demographics.php?' . http_build_query($_GET ?? []));
    exit;
}

$dashboardUrl = getenv('DASHBOARD_URL') ?: '';
if ($dashboardUrl === '') {
    dashboard_redirect_fallback();
}

$setPid = $_GET['set_pid'] ?? null;
if ($setPid === null || !ctype_digit((string) $setPid)) {
    dashboard_redirect_fallback();
}

$row = sqlQuery("SELECT `uuid`, `fname`, `lname` FROM `patient_data` WHERE `pid` = ?", [$setPid]);
if (empty($row['uuid'])) {
    dashboard_redirect_fallback();
}

$uuid = UuidRegistry::uuidToString($row['uuid']);
// OpenEMR convention: keep session pid in sync so any other
// OpenEMR-internal "current patient" lookups continue to resolve.
$_SESSION['pid'] = (int) $setPid;

$patientName = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
if ($patientName === '') {
    $patientName = 'this patient';
}

$modernUrl = rtrim($dashboardUrl, '/') . '/patient/' . urlencode($uuid);
$legacyUrl = 'demographics.php?' . http_build_query($_GET ?? []);

$modernUrlJson = json_encode($modernUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT);
$legacyUrlJson = json_encode($legacyUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT);
$modernUrlHtml = htmlspecialchars($modernUrl, ENT_QUOTES, 'UTF-8');
$legacyUrlHtml = htmlspecialchars($legacyUrl, ENT_QUOTES, 'UTF-8');
$patientNameHtml = htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Choose patient view — <?php echo $patientNameHtml; ?></title>
<style>
    :root {
        color-scheme: light dark;
        --bg: #f8fafc;
        --card: #ffffff;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --secondary: #475569;
        --secondary-hover: #1e293b;
    }
    @media (prefers-color-scheme: dark) {
        :root {
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --border: #334155;
            --secondary: #cbd5e1;
            --secondary-hover: #f1f5f9;
        }
    }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 2rem;
        max-width: 28rem;
        width: 100%;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
        text-align: center;
    }
    h1 {
        margin: 0 0 0.25rem;
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -0.01em;
    }
    p.subtitle {
        margin: 0 0 1.5rem;
        font-size: 0.875rem;
        color: var(--muted);
    }
    .patient-name {
        color: var(--text);
        font-weight: 600;
    }
    .actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }
    button {
        font: inherit;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: background-color 0.12s ease, border-color 0.12s ease;
    }
    button.primary {
        background: var(--primary);
        color: #ffffff;
    }
    button.primary:hover { background: var(--primary-hover); }
    button.secondary {
        background: transparent;
        color: var(--secondary);
        border-color: var(--border);
    }
    button.secondary:hover {
        color: var(--secondary-hover);
        border-color: var(--secondary);
    }
    .badge {
        display: inline-block;
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: rgba(37, 99, 235, 0.1);
        color: var(--primary);
        padding: 0.125rem 0.5rem;
        border-radius: 999px;
        margin-left: 0.5rem;
        vertical-align: 1px;
    }
    .hint {
        margin: 1.25rem 0 0;
        font-size: 0.7rem;
        color: var(--muted);
    }
    .hint a { color: var(--muted); }
</style>
</head>
<body>
<main class="card">
    <h1>Open patient record</h1>
    <p class="subtitle">
        Choose how to view <span class="patient-name"><?php echo $patientNameHtml; ?></span>.
    </p>
    <div class="actions">
        <button class="primary" type="button" id="openModern">
            Open in Modern Dashboard
            <span class="badge">Next.js</span>
        </button>
        <button class="secondary" type="button" id="openLegacy">
            Open in Legacy View (OpenEMR)
        </button>
    </div>
    <p class="hint">
        Direct links:
        <a href="<?php echo $modernUrlHtml; ?>" target="_top">modern</a> ·
        <a href="<?php echo $legacyUrlHtml; ?>">legacy</a>
    </p>
</main>
<script>
(function () {
    var modernUrl = <?php echo $modernUrlJson; ?>;
    var legacyUrl = <?php echo $legacyUrlJson; ?>;

    document.getElementById('openModern').addEventListener('click', function () {
        // Modern dashboard refuses to be iframed (CSP frame-ancestors 'none').
        // Escape OpenEMR's iframe chrome by navigating the top window.
        try { window.top.location.replace(modernUrl); }
        catch (_) { window.location.replace(modernUrl); }
    });

    document.getElementById('openLegacy').addEventListener('click', function () {
        // Legacy view stays inside OpenEMR's normal frame slot.
        window.location.href = legacyUrl;
    });
})();
</script>
</body>
</html><?php
exit;
