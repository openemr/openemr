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

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', session: $session)) {
    CsrfUtils::csrfNotVerified();
}

$mode         = $_POST['mode'] ?? '';
$pid          = (int)($_POST['pid'] ?? 0);
$encounter_id = (int)($_POST['encounter_id'] ?? 0); // form_encounter.id for edit
$nro_registro = $_POST['nro_registro'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$servicio     = $_POST['servicio'] ?? '';
$cuarto       = $_POST['cuarto'] ?? '';
$cama         = $_POST['cama'] ?? '';
$date         = $_POST['form_date'] ?? date('Y-m-d');

// Sanitize date
$date = date('Y-m-d', strtotime($date));

// Resolve Inpatient category id
$catRow = sqlQuery(
    "SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_catname = 'Inpatient' LIMIT 1"
);
$inpatient_catid = $catRow ? (int)$catRow['pc_catid'] : 0;

if ($mode === 'new') {
    if ($pid <= 0 || $inpatient_catid === 0) {
        die(xlt('Invalid patient or category'));
    }

    // Get facility and provider from session/user
    $userRow = sqlQuery("SELECT facility_id FROM users WHERE username = ?", [$_SESSION['authUser']]);
    $facility_id = (int)($userRow['facility_id'] ?? 0);
    $facilityRow = $facility_id ? sqlQuery("SELECT name FROM facility WHERE id = ?", [$facility_id]) : null;
    $facility = $facilityRow['name'] ?? '';

    $encounter   = QueryUtils::generateId();
    $uuid        = (new UuidRegistry(['table_name' => 'form_encounter']))->createUuid();
    $provider_id = (int)$_SESSION['authUserID'];
    $user        = $_SESSION['authUser'];
    $group       = $_SESSION['authProvider'];

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

    $feRow = sqlQuery("SELECT pid, encounter FROM form_encounter WHERE id = ?", [$encounter_id]);
    if (empty($feRow)) {
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
