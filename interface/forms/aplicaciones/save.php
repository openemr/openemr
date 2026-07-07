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
// Sanitize input fields
$medicamentos      = (int)    filter_input(INPUT_POST, 'medicamentos', FILTER_SANITIZE_NUMBER_INT);
$obs_medicamentos  = (string) filter_input(INPUT_POST, 'obs_medicamentos');
$sueros            = (int)    filter_input(INPUT_POST, 'sueros', FILTER_SANITIZE_NUMBER_INT);
$obs_sueros        = (string) filter_input(INPUT_POST, 'obs_sueros');
$vacunas           = (int)    filter_input(INPUT_POST, 'vacunas', FILTER_SANITIZE_NUMBER_INT);
$obs_vacunas       = (string) filter_input(INPUT_POST, 'obs_vacunas');
$expansiones       = (int)    filter_input(INPUT_POST, 'expansiones', FILTER_SANITIZE_NUMBER_INT);
$obs_expansiones   = (string) filter_input(INPUT_POST, 'obs_expansiones');
$sangre            = (int)    filter_input(INPUT_POST, 'sangre', FILTER_SANITIZE_NUMBER_INT);
$obs_sangre        = (string) filter_input(INPUT_POST, 'obs_sangre');
$hora_raw          = (string) filter_input(INPUT_POST, 'hora_registro');
$hora_registro     = ($hora_raw !== '') ? $hora_raw : null;
$is_edit = ($id > 0);
if ($is_edit) {
// Verify the record belongs to this patient/encounter
    $check = QueryUtils::querySingleRow("SELECT id FROM form_aplicaciones WHERE id = ? AND pid = ? AND encounter = ? LIMIT 1", [$id, $pid, $encounter]);
    if (!$check) {
        die(xlt("Error: Record not found or insufficient permissions."));
    }

    QueryUtils::sqlStatementThrowException("UPDATE form_aplicaciones SET
            date = NOW(), user = ?, groupname = ?, authorized = ?,
            medicamentos = ?, obs_medicamentos = ?,
            sueros = ?, obs_sueros = ?,
            vacunas = ?, obs_vacunas = ?,
            expansiones = ?, obs_expansiones = ?,
            sangre = ?, obs_sangre = ?,
            hora_registro = ?
         WHERE id = ? AND pid = ? AND encounter = ?", [
            $user, $groupname, $authorized,
            $medicamentos, $obs_medicamentos,
            $sueros, $obs_sueros,
            $vacunas, $obs_vacunas,
            $expansiones, $obs_expansiones,
            $sangre, $obs_sangre,
            $hora_registro,
            $id, $pid, $encounter,
        ]);
} else {
    $newid = QueryUtils::sqlInsert("INSERT INTO form_aplicaciones (
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
         )", [
            $pid, $encounter, $user, $groupname, $authorized,
            $medicamentos, $obs_medicamentos,
            $sueros, $obs_sueros,
            $vacunas, $obs_vacunas,
            $expansiones, $obs_expansiones,
            $sangre, $obs_sangre,
            $hora_registro,
        ]);
    if (!$newid) {
        die(xlt("Error: Could not save the record. Please try again."));
    }

    (new FormService())->addForm($encounter, 'Nursing Applications', (int)$newid, 'aplicaciones', $pid, $authorized);
}

formHeader(xlt("Redirecting..."));
formJump(OEGlobalsBag::getInstance()->getString('webroot') . "/interface/tableros/lista_internados.php");
formFooter();
