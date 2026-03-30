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
// Ventilation mode whitelist
$allowed_modos = ['ESPONTANEA', 'VENTILACION MECANICA', ''];
$modo_ventilacion            = in_array($_POST['modo_ventilacion'] ?? '', $allowed_modos)
                               ? ($_POST['modo_ventilacion'] ?? '') : '';
$obs_modo                    = $_POST['obs_modo']                    ?? '';
$presion                     = (int)($_POST['presion']                     ?? 0);
$obs_presion                  = $_POST['obs_presion']                  ?? '';
$volumen                     = (int)($_POST['volumen']                     ?? 0);
$obs_volumen                  = $_POST['obs_volumen']                  ?? '';
$simv                        = (int)($_POST['simv']                        ?? 0);
$obs_simv                     = $_POST['obs_simv']                     ?? '';
$psv                         = (int)($_POST['psv']                         ?? 0);
$obs_psv                      = $_POST['obs_psv']                      ?? '';
$otros                       = (int)($_POST['otros']                       ?? 0);
$obs_otros                    = $_POST['obs_otros']                    ?? '';
$frecuencia_respiratoria     = (int)($_POST['frecuencia_respiratoria']     ?? 0);
$obs_frecuencia_respiratoria  = $_POST['obs_frecuencia_respiratoria']  ?? '';
$p_inspiratorio              = (int)($_POST['p_inspiratorio']              ?? 0);
$obs_p_inspiratorio           = $_POST['obs_p_inspiratorio']           ?? '';
$p_media                     = (int)($_POST['p_media']                     ?? 0);
$obs_p_media                  = $_POST['obs_p_media']                  ?? '';
$p_max                       = (int)($_POST['p_max']                       ?? 0);
$obs_p_max                    = $_POST['obs_p_max']                    ?? '';
$chst                        = (int)($_POST['chst']                        ?? 0);
$obs_chst                     = $_POST['obs_chst']                     ?? '';
$disparo                     = (int)($_POST['disparo']                     ?? 0);
$obs_disparo                  = $_POST['obs_disparo']                  ?? '';
$fvt                         = (int)($_POST['fvt']                         ?? 0);
$obs_fvt                      = $_POST['obs_fvt']                      ?? '';
$vol_tidal                   = (int)($_POST['vol_tidal']                   ?? 0);
$obs_vol_tidal                = $_POST['obs_vol_tidal']                ?? '';
$vm_programado               = (int)($_POST['vm_programado']               ?? 0);
$obs_vm_programado            = $_POST['obs_vm_programado']            ?? '';
$petco2                      = (int)($_POST['petco2']                      ?? 0);
$obs_petco2                   = $_POST['obs_petco2']                   ?? '';
$vdvt                        = (int)($_POST['vdvt']                        ?? 0);
$obs_vdvt                     = $_POST['obs_vdvt']                     ?? '';
$ko2                         = (int)($_POST['ko2']                         ?? 0);
$obs_ko2                      = $_POST['obs_ko2']                      ?? '';
$hora_registro               = !empty($_POST['hora_registro']) ? $_POST['hora_registro'] : null;
$is_edit = ($id > 0);
if ($is_edit) {
    $check = sqlQuery("SELECT id FROM form_registro_vm WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", array($id, $pid, $encounter));
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
         WHERE id = ? AND pid = ? AND encounter = ?", array(
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
        ));
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
         )", array(
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
        ));
    if (!$newid) {
        die(xlt("Error: Could not save the record. Please try again."));
    }

    addForm($encounter, 'Mechanical Ventilation Record', $newid, 'registro_vm', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump($GLOBALS['webroot'] . "/interface/tableros/lista_internados.php");
formFooter();
