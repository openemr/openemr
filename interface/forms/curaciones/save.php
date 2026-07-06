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
/** @var string $srcdir */
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(xlt("Method not allowed"));
}

// Verify CSRF token
$csrf_token = (string) filter_input(INPUT_POST, 'csrf_token_form');
if (!CsrfUtils::verifyCsrfToken($csrf_token, session: $session)) {
    CsrfUtils::csrfNotVerified();
}

$pid       = (int) filter_input(INPUT_POST, 'pid', FILTER_SANITIZE_NUMBER_INT);
$encounter = (int) filter_input(INPUT_POST, 'encounter', FILTER_SANITIZE_NUMBER_INT);
$id        = (int) filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$pid || !$encounter) {
    die(xlt("Error: Missing required data (PID or Encounter)"));
}

$user       = (string) ($session->get('authUser')       ?? 'admin');
$groupname  = (string) ($session->get('authProvider')   ?? 'Default');
$authorized = (int)    ($session->get('userauthorized') ?? 1);
// Sanitize input fields
$herida_operatoria      = (int)    filter_input(INPUT_POST, 'herida_operatoria', FILTER_SANITIZE_NUMBER_INT);
$obs_herida_operatoria  = (string) filter_input(INPUT_POST, 'obs_herida_operatoria');
$traqueostomia          = (int)    filter_input(INPUT_POST, 'traqueostomia', FILTER_SANITIZE_NUMBER_INT);
$obs_traqueostomia      = (string) filter_input(INPUT_POST, 'obs_traqueostomia');
$ostomias               = (int)    filter_input(INPUT_POST, 'ostomias', FILTER_SANITIZE_NUMBER_INT);
$obs_ostomias           = (string) filter_input(INPUT_POST, 'obs_ostomias');
$escaras                = (int)    filter_input(INPUT_POST, 'escaras', FILTER_SANITIZE_NUMBER_INT);
$obs_escaras            = (string) filter_input(INPUT_POST, 'obs_escaras');
$via_venosa_central     = (int)    filter_input(INPUT_POST, 'via_venosa_central', FILTER_SANITIZE_NUMBER_INT);
$obs_via_venosa_central = (string) filter_input(INPUT_POST, 'obs_via_venosa_central');
$via_venosa             = (int)    filter_input(INPUT_POST, 'via_venosa', FILTER_SANITIZE_NUMBER_INT);
$obs_via_venosa         = (string) filter_input(INPUT_POST, 'obs_via_venosa');
$hora_raw               = (string) filter_input(INPUT_POST, 'hora_operacion');
$hora_operacion         = ($hora_raw !== '') ? $hora_raw : null;
$is_edit = ($id > 0);
if ($is_edit) {
// Verify the record belongs to this patient/encounter
    $check = QueryUtils::querySingleRow("SELECT id FROM form_curaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
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
formJump(OEGlobalsBag::getInstance()->getString('webroot') . "/interface/tableros/lista_internados.php");
formFooter();
