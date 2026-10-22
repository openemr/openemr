<?php

/**
 * Sets the visit's appointment to the status the fee sheet offered after Save
 * (global fee_sheet_save_appt_status). Answers JSON.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Billing\FeeSheetAppointmentStatusPrompt;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\AppointmentService;

header('Content-Type: application/json');

$request = CurrentRequest::get();
if (!$request->isMethod('POST')) {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}
$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, $session, dieOnFail: true);
if (!AclMain::aclCheckCore('acct', 'bill', '', 'write') && !AclMain::aclCheckCore('encounters', 'coding', '', 'write')) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

$pid = $session->get('pid');
$encounter = $request->request->getInt('encounter');
$eid = $request->request->getInt('eid');

// Only the appointment the fee sheet would ask about, for an encounter of the current patient.
$visitDate = is_numeric($pid) ? QueryUtils::fetchSingleValue(
    'SELECT DATE(date) AS visit_date FROM form_encounter WHERE pid = ? AND encounter = ?',
    'visit_date',
    [(int) $pid, $encounter]
) : null;
$prompt = is_string($visitDate)
    ? (new FeeSheetAppointmentStatusPrompt(OEGlobalsBag::getInstance()->getString(FeeSheetAppointmentStatusPrompt::GLOBAL_NAME)))
        ->appointmentFor((int) $pid, $visitDate)
    : null;
if ($prompt === null || $prompt->eid !== $eid) {
    http_response_code(409);
    echo json_encode(['ok' => false]);
    exit;
}

(new AppointmentService())->updateAppointmentStatus($prompt->eid, $prompt->statusCode, $session->get('authUserID'), $encounter);
echo json_encode(['ok' => true]);
