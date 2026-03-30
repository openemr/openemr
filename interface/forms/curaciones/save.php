<?php

/**
 * Nursing Wound Care Form - save.php
 * Handles INSERT (create) and UPDATE (edit) for the curaciones form.
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
$herida_operatoria      = (int)($_POST['herida_operatoria']      ?? 0);
$obs_herida_operatoria  = $_POST['obs_herida_operatoria']         ?? '';
$traqueostomia          = (int)($_POST['traqueostomia']          ?? 0);
$obs_traqueostomia      = $_POST['obs_traqueostomia']             ?? '';
$ostomias               = (int)($_POST['ostomias']               ?? 0);
$obs_ostomias           = $_POST['obs_ostomias']                  ?? '';
$escaras                = (int)($_POST['escaras']                ?? 0);
$obs_escaras            = $_POST['obs_escaras']                   ?? '';
$via_venosa_central     = (int)($_POST['via_venosa_central']     ?? 0);
$obs_via_venosa_central = $_POST['obs_via_venosa_central']        ?? '';
$via_venosa             = (int)($_POST['via_venosa']             ?? 0);
$obs_via_venosa         = $_POST['obs_via_venosa']                ?? '';
$hora_operacion         = !empty($_POST['hora_operacion']) ? $_POST['hora_operacion'] : null;
$is_edit = ($id > 0);
if ($is_edit) {
// Verify the record belongs to this patient/encounter
    $check = sqlQuery("SELECT id FROM form_curaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }

    $upd = sqlStatement("UPDATE form_curaciones SET
            date = NOW(), user = ?, groupname = ?, authorized = ?,
            herida_operatoria = ?, obs_herida_operatoria = ?,
            traqueostomia = ?, obs_traqueostomia = ?,
            ostomias = ?, obs_ostomias = ?,
            escaras = ?, obs_escaras = ?,
            via_venosa_central = ?, obs_via_venosa_central = ?,
            via_venosa = ?, obs_via_venosa = ?,
            hora_operacion = ?
         WHERE id = ? AND pid = ? AND encounter = ?", array(
            $user, $groupname, $authorized,
            $herida_operatoria, $obs_herida_operatoria,
            $traqueostomia, $obs_traqueostomia,
            $ostomias, $obs_ostomias,
            $escaras, $obs_escaras,
            $via_venosa_central, $obs_via_venosa_central,
            $via_venosa, $obs_via_venosa,
            $hora_operacion,
            $id, $pid, $encounter,
        ));
    if ($upd === false) {
        die(xlt("Error: Could not update the record. Please try again."));
    }
} else {
    $newid = sqlInsert("INSERT INTO form_curaciones (
            date, pid, encounter, user, groupname, authorized, activity,
            herida_operatoria, obs_herida_operatoria,
            traqueostomia, obs_traqueostomia,
            ostomias, obs_ostomias,
            escaras, obs_escaras,
            via_venosa_central, obs_via_venosa_central,
            via_venosa, obs_via_venosa,
            hora_operacion
         ) VALUES (
            NOW(), ?, ?, ?, ?, ?, 1,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?
         )", array(
            $pid, $encounter, $user, $groupname, $authorized,
            $herida_operatoria, $obs_herida_operatoria,
            $traqueostomia, $obs_traqueostomia,
            $ostomias, $obs_ostomias,
            $escaras, $obs_escaras,
            $via_venosa_central, $obs_via_venosa_central,
            $via_venosa, $obs_via_venosa,
            $hora_operacion,
        ));
    if (!$newid) {
        die(xlt("Error: Could not save the record. Please try again."));
    }

    addForm($encounter, 'Nursing Wound Care', $newid, 'curaciones', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump($GLOBALS['webroot'] . "/interface/tableros/lista_internados.php");
formFooter();
