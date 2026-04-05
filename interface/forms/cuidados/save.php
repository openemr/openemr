<?php

/**
 * Nursing Care Bundle Form - save.php
 * Handles INSERT (create) and UPDATE (edit) for the cuidados form.
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
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

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
// Allowed patient position values
$allowed_positions = ['DLI', 'DLD', 'DS', 'DV', 'CABECERA 30°', ''];
$posicion_paciente        = in_array($_POST['posicion_paciente'] ?? '', $allowed_positions)
                            ? ($_POST['posicion_paciente'] ?? '')
                            : '';
$obs_posicion_paciente    = $_POST['obs_posicion_paciente']    ?? '';
$enjuague_bucal           = (int)($_POST['enjuague_bucal']           ?? 0);
$obs_enjuague_bucal       = $_POST['obs_enjuague_bucal']       ?? '';
$higiene_manos            = (int)($_POST['higiene_manos']            ?? 0);
$obs_higiene_manos        = $_POST['obs_higiene_manos']        ?? '';
$aspirado_secreciones     = (int)($_POST['aspirado_secreciones']     ?? 0);
$obs_aspirado_secreciones = $_POST['obs_aspirado_secreciones'] ?? '';
$suspension_sedacion      = (int)($_POST['suspension_sedacion']      ?? 0);
$obs_suspension_sedacion  = $_POST['obs_suspension_sedacion']  ?? '';
$medicion_cuff            = (int)($_POST['medicion_cuff']            ?? 0);
$obs_medicion_cuff        = $_POST['obs_medicion_cuff']        ?? '';
$hora_cuidado             = !empty($_POST['hora_cuidado']) ? $_POST['hora_cuidado'] : null;
$is_edit = ($id > 0);
if ($is_edit) {
// Verify the record belongs to this patient/encounter
    $check = sqlQuery("SELECT id FROM form_cuidados WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }

    $upd = sqlStatement("UPDATE form_cuidados SET
            date = NOW(), user = ?, groupname = ?, authorized = ?,
            posicion_paciente = ?, obs_posicion_paciente = ?,
            enjuague_bucal = ?, obs_enjuague_bucal = ?,
            higiene_manos = ?, obs_higiene_manos = ?,
            aspirado_secreciones = ?, obs_aspirado_secreciones = ?,
            suspension_sedacion = ?, obs_suspension_sedacion = ?,
            medicion_cuff = ?, obs_medicion_cuff = ?,
            hora_cuidado = ?
         WHERE id = ? AND pid = ? AND encounter = ?", array(
            $user, $groupname, $authorized,
            $posicion_paciente, $obs_posicion_paciente,
            $enjuague_bucal, $obs_enjuague_bucal,
            $higiene_manos, $obs_higiene_manos,
            $aspirado_secreciones, $obs_aspirado_secreciones,
            $suspension_sedacion, $obs_suspension_sedacion,
            $medicion_cuff, $obs_medicion_cuff,
            $hora_cuidado,
            $id, $pid, $encounter,
        ));
    if ($upd === false) {
        die(xlt("Error: Could not update the record. Please try again."));
    }
} else {
    $newid = sqlInsert("INSERT INTO form_cuidados (
            date, pid, encounter, user, groupname, authorized, activity,
            posicion_paciente, obs_posicion_paciente,
            enjuague_bucal, obs_enjuague_bucal,
            higiene_manos, obs_higiene_manos,
            aspirado_secreciones, obs_aspirado_secreciones,
            suspension_sedacion, obs_suspension_sedacion,
            medicion_cuff, obs_medicion_cuff,
            hora_cuidado
         ) VALUES (
            NOW(), ?, ?, ?, ?, ?, 1,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?
         )", array(
            $pid, $encounter, $user, $groupname, $authorized,
            $posicion_paciente, $obs_posicion_paciente,
            $enjuague_bucal, $obs_enjuague_bucal,
            $higiene_manos, $obs_higiene_manos,
            $aspirado_secreciones, $obs_aspirado_secreciones,
            $suspension_sedacion, $obs_suspension_sedacion,
            $medicion_cuff, $obs_medicion_cuff,
            $hora_cuidado,
        ));
    if (!$newid) {
        die(xlt("Error: Could not save the record. Please try again."));
    }

    addForm($encounter, 'Nursing Care Bundle', $newid, 'cuidados', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump($GLOBALS['webroot'] . "/interface/tableros/lista_internados.php");
formFooter();
