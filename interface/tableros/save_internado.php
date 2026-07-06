<?php

/**
 * Save handler for inpatient admissions (new and edit).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    g0tazu
 * @copyright Copyright (c) 2026 g0tazu
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../globals.php");
/** @var string $srcdir */
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!AclMain::aclCheckCore('patients', 'med')) {
    die(xlt('Access denied'));
}

$csrf_token = (string) filter_input(INPUT_POST, 'csrf_token_form');
if (!CsrfUtils::verifyCsrfToken($csrf_token, session: $session)) {
    CsrfUtils::csrfNotVerified();
}

$mode         = (string) filter_input(INPUT_POST, 'mode');
$pid          = (int)    filter_input(INPUT_POST, 'pid', FILTER_SANITIZE_NUMBER_INT);
$encounter_id = (int)    filter_input(INPUT_POST, 'encounter_id', FILTER_SANITIZE_NUMBER_INT);
$nro_registro = (string) filter_input(INPUT_POST, 'nro_registro');
$departamento = (string) filter_input(INPUT_POST, 'departamento');
$servicio     = (string) filter_input(INPUT_POST, 'servicio');
$cuarto       = (string) filter_input(INPUT_POST, 'cuarto');
$cama         = (string) filter_input(INPUT_POST, 'cama');
$date_raw     = (string) filter_input(INPUT_POST, 'form_date');
$date         = $date_raw !== '' ? $date_raw : date('Y-m-d');

// Sanitize date
$date = date('Y-m-d', strtotime($date));

// Resolve Inpatient category id
$catRow = QueryUtils::querySingleRow(
    "SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_catname = 'Inpatient' LIMIT 1"
);
$inpatient_catid = $catRow ? (int) $catRow['pc_catid'] : 0;

if ($mode === 'new') {
    if ($pid <= 0 || $inpatient_catid === 0) {
        die(xlt('Invalid patient or category'));
    }

    // Get facility and provider from session/user
    $userRow = QueryUtils::querySingleRow("SELECT facility_id FROM users WHERE username = ?", [(string) ($session->get('authUser') ?? '')]);
    $facility_id = (int) ($userRow['facility_id'] ?? 0);
    $facilityRow = $facility_id ? QueryUtils::querySingleRow("SELECT name FROM facility WHERE id = ?", [$facility_id]) : null;
    $facility = (string) ($facilityRow['name'] ?? '');

    $encounter   = QueryUtils::generateId();
    $uuid        = (new UuidRegistry(['table_name' => 'form_encounter']))->createUuid();
    $provider_id = (int)    ($session->get('authUserID') ?? 0);
    $user        = (string) ($session->get('authUser')   ?? '');
    $group       = (string) ($session->get('authProvider') ?? '');

    $fe_id = sqlInsert(
        "INSERT INTO form_encounter
            (pid, encounter, uuid, date, facility, facility_id, pc_catid, provider_id, billing_facility,
             nro_registro, departamento, servicio, cuarto, cama)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$pid, $encounter, $uuid, $date . ' 00:00:00', $facility, $facility_id,
         $inpatient_catid, $provider_id, $facility_id,
         $nro_registro, $departamento, $servicio, $cuarto, $cama]
    );

    addForm($encounter, "New Patient Encounter", $fe_id, "newpatient", $pid, $provider_id, $date, $user, $group);

    header("Location: lista_internados.php?update=1");
    exit;
}

if ($mode === 'edit') {
    if ($encounter_id <= 0) {
        die(xlt('Invalid encounter'));
    }

    $feRow = QueryUtils::querySingleRow("SELECT pid, encounter FROM form_encounter WHERE id = ?", [$encounter_id]);
    if (!$feRow) {
        die(xlt('Encounter not found'));
    }

    sqlStatement(
        "UPDATE form_encounter
         SET nro_registro = ?, departamento = ?, servicio = ?, cuarto = ?, cama = ?
         WHERE id = ?",
        [$nro_registro, $departamento, $servicio, $cuarto, $cama, $encounter_id]
    );

    header("Location: lista_internados.php?update=1");
    exit;
}

die(xlt('Invalid mode'));
