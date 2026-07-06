<?php

/**
 * Mechanical Ventilation Record Form - save.php
 * Handles INSERT (create) and UPDATE (edit) for the registro_vm form.
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
// Ventilation mode whitelist
$allowed_modos = ['ESPONTANEA', 'VENTILACION MECANICA', ''];
$modo_ventilacion_raw        = (string) filter_input(INPUT_POST, 'modo_ventilacion');
$modo_ventilacion            = in_array($modo_ventilacion_raw, $allowed_modos, true) ? $modo_ventilacion_raw : '';
$obs_modo                    = (string) filter_input(INPUT_POST, 'obs_modo');
$presion                     = (int)    filter_input(INPUT_POST, 'presion', FILTER_SANITIZE_NUMBER_INT);
$obs_presion                 = (string) filter_input(INPUT_POST, 'obs_presion');
$volumen                     = (int)    filter_input(INPUT_POST, 'volumen', FILTER_SANITIZE_NUMBER_INT);
$obs_volumen                 = (string) filter_input(INPUT_POST, 'obs_volumen');
$simv                        = (int)    filter_input(INPUT_POST, 'simv', FILTER_SANITIZE_NUMBER_INT);
$obs_simv                    = (string) filter_input(INPUT_POST, 'obs_simv');
$psv                         = (int)    filter_input(INPUT_POST, 'psv', FILTER_SANITIZE_NUMBER_INT);
$obs_psv                     = (string) filter_input(INPUT_POST, 'obs_psv');
$otros                       = (int)    filter_input(INPUT_POST, 'otros', FILTER_SANITIZE_NUMBER_INT);
$obs_otros                   = (string) filter_input(INPUT_POST, 'obs_otros');
$frecuencia_respiratoria     = (int)    filter_input(INPUT_POST, 'frecuencia_respiratoria', FILTER_SANITIZE_NUMBER_INT);
$obs_frecuencia_respiratoria = (string) filter_input(INPUT_POST, 'obs_frecuencia_respiratoria');
$p_inspiratorio              = (int)    filter_input(INPUT_POST, 'p_inspiratorio', FILTER_SANITIZE_NUMBER_INT);
$obs_p_inspiratorio          = (string) filter_input(INPUT_POST, 'obs_p_inspiratorio');
$p_media                     = (int)    filter_input(INPUT_POST, 'p_media', FILTER_SANITIZE_NUMBER_INT);
$obs_p_media                 = (string) filter_input(INPUT_POST, 'obs_p_media');
$p_max                       = (int)    filter_input(INPUT_POST, 'p_max', FILTER_SANITIZE_NUMBER_INT);
$obs_p_max                   = (string) filter_input(INPUT_POST, 'obs_p_max');
$chst                        = (int)    filter_input(INPUT_POST, 'chst', FILTER_SANITIZE_NUMBER_INT);
$obs_chst                    = (string) filter_input(INPUT_POST, 'obs_chst');
$disparo                     = (int)    filter_input(INPUT_POST, 'disparo', FILTER_SANITIZE_NUMBER_INT);
$obs_disparo                 = (string) filter_input(INPUT_POST, 'obs_disparo');
$fvt                         = (int)    filter_input(INPUT_POST, 'fvt', FILTER_SANITIZE_NUMBER_INT);
$obs_fvt                     = (string) filter_input(INPUT_POST, 'obs_fvt');
$vol_tidal                   = (int)    filter_input(INPUT_POST, 'vol_tidal', FILTER_SANITIZE_NUMBER_INT);
$obs_vol_tidal               = (string) filter_input(INPUT_POST, 'obs_vol_tidal');
$vm_programado               = (int)    filter_input(INPUT_POST, 'vm_programado', FILTER_SANITIZE_NUMBER_INT);
$obs_vm_programado           = (string) filter_input(INPUT_POST, 'obs_vm_programado');
$petco2                      = (int)    filter_input(INPUT_POST, 'petco2', FILTER_SANITIZE_NUMBER_INT);
$obs_petco2                  = (string) filter_input(INPUT_POST, 'obs_petco2');
$vdvt                        = (int)    filter_input(INPUT_POST, 'vdvt', FILTER_SANITIZE_NUMBER_INT);
$obs_vdvt                    = (string) filter_input(INPUT_POST, 'obs_vdvt');
$ko2                         = (int)    filter_input(INPUT_POST, 'ko2', FILTER_SANITIZE_NUMBER_INT);
$obs_ko2                     = (string) filter_input(INPUT_POST, 'obs_ko2');
$hora_raw                    = (string) filter_input(INPUT_POST, 'hora_registro');
$hora_registro               = ($hora_raw !== '') ? $hora_raw : null;
$is_edit = ($id > 0);
if ($is_edit) {
    $check = QueryUtils::querySingleRow("SELECT id FROM form_registro_vm WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }

    $upd = sqlStatement("UPDATE form_registro_vm SET
            date = NOW(), user = ?, groupname = ?, authorized = ?,
            modo_ventilacion = ?, obs_modo = ?,
            presion = ?, obs_presion = ?,
            volumen = ?, obs_volumen = ?,
            simv = ?, obs_simv = ?,
            psv = ?, obs_psv = ?,
            otros = ?, obs_otros = ?,
            frecuencia_respiratoria = ?, obs_frecuencia_respiratoria = ?,
            p_inspiratorio = ?, obs_p_inspiratorio = ?,
            p_media = ?, obs_p_media = ?,
            p_max = ?, obs_p_max = ?,
            chst = ?, obs_chst = ?,
            disparo = ?, obs_disparo = ?,
            fvt = ?, obs_fvt = ?,
            vol_tidal = ?, obs_vol_tidal = ?,
            vm_programado = ?, obs_vm_programado = ?,
            petco2 = ?, obs_petco2 = ?,
            vdvt = ?, obs_vdvt = ?,
            ko2 = ?, obs_ko2 = ?,
            hora_registro = ?
         WHERE id = ? AND pid = ? AND encounter = ?", [
            $user, $groupname, $authorized,
            $modo_ventilacion, $obs_modo,
            $presion, $obs_presion, $volumen, $obs_volumen,
            $simv, $obs_simv, $psv, $obs_psv, $otros, $obs_otros,
            $frecuencia_respiratoria, $obs_frecuencia_respiratoria,
            $p_inspiratorio, $obs_p_inspiratorio, $p_media, $obs_p_media,
            $p_max, $obs_p_max, $chst, $obs_chst, $disparo, $obs_disparo,
            $fvt, $obs_fvt, $vol_tidal, $obs_vol_tidal,
            $vm_programado, $obs_vm_programado, $petco2, $obs_petco2,
            $vdvt, $obs_vdvt, $ko2, $obs_ko2,
            $hora_registro,
            $id, $pid, $encounter,
        ]);
    if ($upd === false) {
        die(xlt("Error: Could not update the record. Please try again."));
    }
} else {
    $newid = sqlInsert("INSERT INTO form_registro_vm (
            date, pid, encounter, user, groupname, authorized, activity,
            modo_ventilacion, obs_modo,
            presion, obs_presion, volumen, obs_volumen,
            simv, obs_simv, psv, obs_psv, otros, obs_otros,
            frecuencia_respiratoria, obs_frecuencia_respiratoria,
            p_inspiratorio, obs_p_inspiratorio, p_media, obs_p_media,
            p_max, obs_p_max, chst, obs_chst, disparo, obs_disparo,
            fvt, obs_fvt, vol_tidal, obs_vol_tidal,
            vm_programado, obs_vm_programado, petco2, obs_petco2,
            vdvt, obs_vdvt, ko2, obs_ko2, hora_registro
         ) VALUES (
            NOW(), ?, ?, ?, ?, ?, 1,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?
         )", [
            $pid, $encounter, $user, $groupname, $authorized,
            $modo_ventilacion, $obs_modo,
            $presion, $obs_presion, $volumen, $obs_volumen,
            $simv, $obs_simv, $psv, $obs_psv, $otros, $obs_otros,
            $frecuencia_respiratoria, $obs_frecuencia_respiratoria,
            $p_inspiratorio, $obs_p_inspiratorio, $p_media, $obs_p_media,
            $p_max, $obs_p_max, $chst, $obs_chst, $disparo, $obs_disparo,
            $fvt, $obs_fvt, $vol_tidal, $obs_vol_tidal,
            $vm_programado, $obs_vm_programado, $petco2, $obs_petco2,
            $vdvt, $obs_vdvt, $ko2, $obs_ko2, $hora_registro,
        ]);
    if (!$newid) {
        die(xlt("Error: Could not save the record. Please try again."));
    }

    addForm($encounter, 'Mechanical Ventilation Record', $newid, 'registro_vm', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump(OEGlobalsBag::getInstance()->getString('webroot') . "/interface/tableros/lista_internados.php");
formFooter();
