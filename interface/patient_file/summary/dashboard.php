<?php

/**
 * Modern dashboard launcher.
 *
 * Bridges the legacy OpenEMR patient-finder click flow into the new
 * Next.js patient dashboard. Reads `set_pid`, looks up the patient's
 * FHIR UUID, and 302-redirects the browser to
 * `${DASHBOARD_URL}/patient/<uuid>`.
 *
 * The new dashboard handles its own OAuth flow (PKCE against this
 * OpenEMR instance) and lands the user on the per-patient view.
 *
 * Falls back to the legacy `demographics.php` view if the
 * `DASHBOARD_URL` environment variable is unset, or if no UUID is on
 * file for the patient. This keeps the page working when the modern
 * dashboard service is offline or not yet provisioned.
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

$row = sqlQuery("SELECT `uuid` FROM `patient_data` WHERE `pid` = ?", [$setPid]);
if (empty($row['uuid'])) {
    dashboard_redirect_fallback();
}

$uuid = UuidRegistry::uuidToString($row['uuid']);
// OpenEMR convention: keep session pid in sync so any other
// OpenEMR-internal "current patient" lookups continue to resolve.
$_SESSION['pid'] = (int) $setPid;

header('Location: ' . rtrim($dashboardUrl, '/') . '/patient/' . urlencode($uuid));
exit;
