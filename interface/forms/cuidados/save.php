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
/** @var string $srcdir */
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\FormService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') !== 'POST') {
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
$authorized = intval($session->get('userauthorized') ?? 1);
// Allowed patient position values
$allowed_positions = ['DLI', 'DLD', 'DS', 'DV', 'CABECERA 30°', ''];
$posicion_raw             = (string) filter_input(INPUT_POST, 'posicion_paciente');
$posicion_paciente        = in_array($posicion_raw, $allowed_positions, true) ? $posicion_raw : '';
$obs_posicion_paciente    = (string) filter_input(INPUT_POST, 'obs_posicion_paciente');
$enjuague_bucal           = (int)    filter_input(INPUT_POST, 'enjuague_bucal', FILTER_SANITIZE_NUMBER_INT);
$obs_enjuague_bucal       = (string) filter_input(INPUT_POST, 'obs_enjuague_bucal');
$higiene_manos            = (int)    filter_input(INPUT_POST, 'higiene_manos', FILTER_SANITIZE_NUMBER_INT);
$obs_higiene_manos        = (string) filter_input(INPUT_POST, 'obs_higiene_manos');
$aspirado_secreciones     = (int)    filter_input(INPUT_POST, 'aspirado_secreciones', FILTER_SANITIZE_NUMBER_INT);
$obs_aspirado_secreciones = (string) filter_input(INPUT_POST, 'obs_aspirado_secreciones');
$suspension_sedacion      = (int)    filter_input(INPUT_POST, 'suspension_sedacion', FILTER_SANITIZE_NUMBER_INT);
$obs_suspension_sedacion  = (string) filter_input(INPUT_POST, 'obs_suspension_sedacion');
$medicion_cuff            = (int)    filter_input(INPUT_POST, 'medicion_cuff', FILTER_SANITIZE_NUMBER_INT);
$obs_medicion_cuff        = (string) filter_input(INPUT_POST, 'obs_medicion_cuff');
$hora_raw                 = (string) filter_input(INPUT_POST, 'hora_cuidado');
$hora_cuidado             = ($hora_raw !== '') ? $hora_raw : null;
$is_edit = ($id > 0);
if ($is_edit) {
// Verify the record belongs to this patient/encounter
    $check = QueryUtils::querySingleRow("SELECT id FROM form_cuidados WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }

    QueryUtils::sqlStatementThrowException("UPDATE form_cuidados SET
            date = NOW(), user = ?, groupname = ?, authorized = ?,
            posicion_paciente = ?, obs_posicion_paciente = ?,
            enjuague_bucal = ?, obs_enjuague_bucal = ?,
            higiene_manos = ?, obs_higiene_manos = ?,
            aspirado_secreciones = ?, obs_aspirado_secreciones = ?,
            suspension_sedacion = ?, obs_suspension_sedacion = ?,
            medicion_cuff = ?, obs_medicion_cuff = ?,
            hora_cuidado = ?
         WHERE id = ? AND pid = ? AND encounter = ?", [
            $user, $groupname, $authorized,
            $posicion_paciente, $obs_posicion_paciente,
            $enjuague_bucal, $obs_enjuague_bucal,
            $higiene_manos, $obs_higiene_manos,
            $aspirado_secreciones, $obs_aspirado_secreciones,
            $suspension_sedacion, $obs_suspension_sedacion,
            $medicion_cuff, $obs_medicion_cuff,
            $hora_cuidado,
            $id, $pid, $encounter,
        ]);
} else {
    $newid = QueryUtils::sqlInsert("INSERT INTO form_cuidados (
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
         )", [
            $pid, $encounter, $user, $groupname, $authorized,
            $posicion_paciente, $obs_posicion_paciente,
            $enjuague_bucal, $obs_enjuague_bucal,
            $higiene_manos, $obs_higiene_manos,
            $aspirado_secreciones, $obs_aspirado_secreciones,
            $suspension_sedacion, $obs_suspension_sedacion,
            $medicion_cuff, $obs_medicion_cuff,
            $hora_cuidado,
        ]);
    if (!$newid) {
        die(xlt("Error: Could not save the record. Please try again."));
    }

    (new FormService())->addForm($encounter, 'Nursing Care Bundle', (int)$newid, 'cuidados', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump(OEGlobalsBag::getInstance()->getString('webroot') . "/interface/tableros/lista_internados.php");
formFooter();
