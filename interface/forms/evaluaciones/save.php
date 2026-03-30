<?php

/**
 * Nursing Evaluations Form - save.php
 * Handles INSERT (create) and UPDATE (edit) for the evaluaciones form.
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

$conciencia         = $_POST['conciencia']         ?? '';
$obs_conciencia     = $_POST['obs_conciencia']     ?? '';
$tono               = $_POST['tono']               ?? '';
$obs_tono           = $_POST['obs_tono']           ?? '';
$pupilas            = $_POST['pupilas']            ?? '';
$obs_pupilas        = $_POST['obs_pupilas']        ?? '';
$mucosas            = $_POST['mucosas']            ?? '';
$obs_mucosas        = $_POST['obs_mucosas']        ?? '';
$glasgow_ojos       = $_POST['glasgow_ojos']       ?? '';
$obs_glasgow_ojos   = $_POST['obs_glasgow_ojos']   ?? '';
$glasgow_motora     = $_POST['glasgow_motora']     ?? '';
$obs_glasgow_motora = $_POST['obs_glasgow_motora'] ?? '';
$glasgow_verbal     = $_POST['glasgow_verbal']     ?? '';
$obs_glasgow_verbal = $_POST['obs_glasgow_verbal'] ?? '';
$hora_evaluacion    = !empty($_POST['hora_evaluacion']) ? $_POST['hora_evaluacion'] : null;

// Calculate Glasgow score server-side
$scores_ojos   = ['ESPONTANEAMENTE' => 4, 'A ESTIMULOS AUDITIVOS' => 3, 'AL DOLOR' => 2, 'SIN RESPUESTA' => 1];
$scores_motora = ['OBEDECE ORDENES' => 6, 'LOCALIZA DOLOR' => 5, 'FLEXION DE DEFENSA' => 4, 'FLEXION ANORMAL' => 3, 'EXTENSION ANORMAL' => 2, 'NINGUNA' => 1];
$scores_verbal = ['ORIENTADO Y CONVERSA' => 5, 'DESORIENTADO Y CONVERSA' => 4, 'LENGUAJE INADECUADO' => 3, 'SONIDOS INCOMPRENSIBLES' => 2, 'NINGUNA' => 1];

$glasgow_total  = ($scores_ojos[$glasgow_ojos]     ?? 0)
                + ($scores_motora[$glasgow_motora] ?? 0)
                + ($scores_verbal[$glasgow_verbal] ?? 0);

$is_edit = ($id > 0);

if ($is_edit) {
    $check = sqlQuery("SELECT id FROM form_evaluaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }
    sqlStatement(
        "UPDATE form_evaluaciones SET
            date = NOW(), user = ?, groupname = ?, authorized = ?,
            conciencia = ?, obs_conciencia = ?,
            tono = ?, obs_tono = ?,
            pupilas = ?, obs_pupilas = ?,
            mucosas = ?, obs_mucosas = ?,
            glasgow_ojos = ?, obs_glasgow_ojos = ?,
            glasgow_motora = ?, obs_glasgow_motora = ?,
            glasgow_verbal = ?, obs_glasgow_verbal = ?,
            glasgow_total = ?, hora_evaluacion = ?
         WHERE id = ? AND pid = ? AND encounter = ?",
        [
            $user, $groupname, $authorized,
            $conciencia, $obs_conciencia,
            $tono, $obs_tono,
            $pupilas, $obs_pupilas,
            $mucosas, $obs_mucosas,
            $glasgow_ojos, $obs_glasgow_ojos,
            $glasgow_motora, $obs_glasgow_motora,
            $glasgow_verbal, $obs_glasgow_verbal,
            $glasgow_total, $hora_evaluacion,
            $id, $pid, $encounter,
        ]
    );
} else {
    $newid = sqlInsert(
        "INSERT INTO form_evaluaciones (
            date, pid, encounter, user, groupname, authorized, activity,
            conciencia, obs_conciencia, tono, obs_tono,
            pupilas, obs_pupilas, mucosas, obs_mucosas,
            glasgow_ojos, obs_glasgow_ojos,
            glasgow_motora, obs_glasgow_motora,
            glasgow_verbal, obs_glasgow_verbal,
            glasgow_total, hora_evaluacion
         ) VALUES (
            NOW(), ?, ?, ?, ?, ?, 1,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?
         )",
        [
            $pid, $encounter, $user, $groupname, $authorized,
            $conciencia, $obs_conciencia, $tono, $obs_tono,
            $pupilas, $obs_pupilas, $mucosas, $obs_mucosas,
            $glasgow_ojos, $obs_glasgow_ojos,
            $glasgow_motora, $obs_glasgow_motora,
            $glasgow_verbal, $obs_glasgow_verbal,
            $glasgow_total, $hora_evaluacion,
        ]
    );
    addForm($encounter, 'Nursing Evaluations', $newid, 'evaluaciones', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump();
formFooter();
