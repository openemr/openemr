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

$conciencia         = (string) filter_input(INPUT_POST, 'conciencia');
$obs_conciencia     = (string) filter_input(INPUT_POST, 'obs_conciencia');
$tono               = (string) filter_input(INPUT_POST, 'tono');
$obs_tono           = (string) filter_input(INPUT_POST, 'obs_tono');
$pupilas            = (string) filter_input(INPUT_POST, 'pupilas');
$obs_pupilas        = (string) filter_input(INPUT_POST, 'obs_pupilas');
$mucosas            = (string) filter_input(INPUT_POST, 'mucosas');
$obs_mucosas        = (string) filter_input(INPUT_POST, 'obs_mucosas');
$glasgow_ojos       = (string) filter_input(INPUT_POST, 'glasgow_ojos');
$obs_glasgow_ojos   = (string) filter_input(INPUT_POST, 'obs_glasgow_ojos');
$glasgow_motora     = (string) filter_input(INPUT_POST, 'glasgow_motora');
$obs_glasgow_motora = (string) filter_input(INPUT_POST, 'obs_glasgow_motora');
$glasgow_verbal     = (string) filter_input(INPUT_POST, 'glasgow_verbal');
$obs_glasgow_verbal = (string) filter_input(INPUT_POST, 'obs_glasgow_verbal');
$hora_raw           = (string) filter_input(INPUT_POST, 'hora_evaluacion');
$hora_evaluacion    = ($hora_raw !== '') ? $hora_raw : null;

// Calculate Glasgow score server-side
$scores_ojos   = ['ESPONTANEAMENTE' => 4, 'A ESTIMULOS AUDITIVOS' => 3, 'AL DOLOR' => 2, 'SIN RESPUESTA' => 1];
$scores_motora = ['OBEDECE ORDENES' => 6, 'LOCALIZA DOLOR' => 5, 'FLEXION DE DEFENSA' => 4, 'FLEXION ANORMAL' => 3, 'EXTENSION ANORMAL' => 2, 'NINGUNA' => 1];
$scores_verbal = ['ORIENTADO Y CONVERSA' => 5, 'DESORIENTADO Y CONVERSA' => 4, 'LENGUAJE INADECUADO' => 3, 'SONIDOS INCOMPRENSIBLES' => 2, 'NINGUNA' => 1];

$glasgow_total  = ($scores_ojos[$glasgow_ojos]     ?? 0)
                + ($scores_motora[$glasgow_motora] ?? 0)
                + ($scores_verbal[$glasgow_verbal] ?? 0);

$is_edit = ($id > 0);

if ($is_edit) {
    $check = QueryUtils::querySingleRow("SELECT id FROM form_evaluaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }
    QueryUtils::sqlStatementThrowException(
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
    $newid = QueryUtils::sqlInsert(
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
    (new FormService())->addForm($encounter, 'Nursing Evaluations', (int)$newid, 'evaluaciones', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump(OEGlobalsBag::getInstance()->getString('webroot') . "/interface/tableros/lista_internados.php");
formFooter();
