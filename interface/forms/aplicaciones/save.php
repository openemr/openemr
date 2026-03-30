<?php

/**
 * Nursing Applications Form - save.php
 * Handles INSERT (create) and UPDATE (edit) for the aplicaciones form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");
use OpenEMR\Common\Csrf\CsrfUtils;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(xlt("Method not allowed"));
}

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'], session: $session)) {
    CsrfUtils::csrfNotVerified();
}

$pid       = (int)($_POST['pid']       ?? 0);
$encounter = (int)($_POST['encounter'] ?? 0);
$id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if (!$pid || !$encounter) {
    die(xlt("Error: Missing required data (PID or Encounter)"));
}

$user       = $session->get('authUser')       ?? 'admin';
$groupname  = $session->get('authProvider')   ?? 'Default';
$authorized = $session->get('userauthorized') ?? 1;
// Sanitize input fields
$medicamentos      = (int)($_POST['medicamentos']      ?? 0);
$obs_medicamentos  = $_POST['obs_medicamentos']         ?? '';
$sueros            = (int)($_POST['sueros']            ?? 0);
$obs_sueros        = $_POST['obs_sueros']               ?? '';
$vacunas           = (int)($_POST['vacunas']           ?? 0);
$obs_vacunas       = $_POST['obs_vacunas']              ?? '';
$expansiones       = (int)($_POST['expansiones']       ?? 0);
$obs_expansiones   = $_POST['obs_expansiones']          ?? '';
$sangre            = (int)($_POST['sangre']            ?? 0);
$obs_sangre        = $_POST['obs_sangre']               ?? '';
$hora_registro     = !empty($_POST['hora_registro']) ? $_POST['hora_registro'] : null;
$is_edit = ($id > 0);
if ($is_edit) {
// Verify the record belongs to this patient/encounter
    $check = sqlQuery("SELECT id FROM form_aplicaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }

    $upd = sqlStatement("UPDATE form_aplicaciones SET
            date = NOW(), user = ?, groupname = ?, authorized = ?,
            medicamentos = ?, obs_medicamentos = ?,
            sueros = ?, obs_sueros = ?,
            vacunas = ?, obs_vacunas = ?,
            expansiones = ?, obs_expansiones = ?,
            sangre = ?, obs_sangre = ?,
            hora_registro = ?
         WHERE id = ? AND pid = ? AND encounter = ?", array(
            $user, $groupname, $authorized,
            $medicamentos, $obs_medicamentos,
            $sueros, $obs_sueros,
            $vacunas, $obs_vacunas,
            $expansiones, $obs_expansiones,
            $sangre, $obs_sangre,
            $hora_registro,
            $id, $pid, $encounter,
        ));
    if ($upd === false) {
        die(xlt("Error: Could not update the record. Please try again."));
    }
} else {
    $newid = sqlInsert("INSERT INTO form_aplicaciones (
            date, pid, encounter, user, groupname, authorized, activity,
            medicamentos, obs_medicamentos,
            sueros, obs_sueros,
            vacunas, obs_vacunas,
            expansiones, obs_expansiones,
            sangre, obs_sangre,
            hora_registro
         ) VALUES (
            NOW(), ?, ?, ?, ?, ?, 1,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?
         )", array(
            $pid, $encounter, $user, $groupname, $authorized,
            $medicamentos, $obs_medicamentos,
            $sueros, $obs_sueros,
            $vacunas, $obs_vacunas,
            $expansiones, $obs_expansiones,
            $sangre, $obs_sangre,
            $hora_registro,
        ));
    if (!$newid) {
        die(xlt("Error: Could not save the record. Please try again."));
    }

    addForm($encounter, 'Nursing Applications', $newid, 'aplicaciones', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump($GLOBALS['webroot'] . "/interface/tableros/lista_internados.php");
formFooter();
