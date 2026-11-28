<?php

/**
 *  @package   OpenEMR
 *  @link      https://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

require_once "../interface/globals.php";

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\DrugSalesService;

// The caller (general_list.html.twig) reads a JSON status rather than
// comparing translated text, so the outcome survives localisation.
header('Content-Type: application/json');

// Ensure user has prescription write permission. The ACL section is
// "patients" (plural) — the previous "patient" (singular) value was a
// typo that did not match any registered ACL and silently denied every
// non-super-user.
if (!AclMain::aclCheckCore('patients', 'rx', '', 'write')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => xl('ACL Administration Not Authorized')]);
    exit;
}

$id = filter_input(INPUT_POST, 'drugid', FILTER_VALIDATE_INT);
if ($id === null || $id === false || $id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => xl('Missing or invalid prescription id')]);
    exit;
}
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$authUser = $session->get('authUser');
$authProvider = $session->get('authProvider');
$restoreInventoryRaw = filter_input(INPUT_POST, 'restore_inventory');
$restoreInventory = $restoreInventoryRaw !== null && $restoreInventoryRaw !== '' && $restoreInventoryRaw !== '0';

$row = QueryUtils::fetchRecords("SELECT patient_id, drug FROM prescriptions WHERE id = ?", [$id])[0] ?? null;
if (!is_array($row)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => xl('Prescription not found')]);
    exit;
}
$patientIdRaw = $row['patient_id'] ?? null;
$pid = is_numeric($patientIdRaw) ? (int)$patientIdRaw : 0;
$drugNameRaw = $row['drug'] ?? null;
$drugName = is_string($drugNameRaw) ? $drugNameRaw : '';

// Everything below succeeds or fails together: reversing the sales
// (inventory and drug_sales rows), removing the medication-list entry and
// deleting the prescription itself. The audit events are written inside
// the same transaction so a rollback takes them with it, and the reversal
// runs first so a failure there leaves the patient record untouched.
$failed = false;
try {
    QueryUtils::inTransaction(function () use ($id, $restoreInventory, $pid, $drugName, $authUser, $authProvider): void {
        $reversal = (new DrugSalesService())->reverseSalesForPrescription($id, $restoreInventory);
        if ($reversal['sales_deleted'] > 0) {
            $auditDetail = sprintf(
                'Reversed %d sale(s) for deleted prescription %d (inventory %s, %s units)',
                $reversal['sales_deleted'],
                $id,
                $reversal['restored_inventory'] ? 'restored' : 'not restored',
                $reversal['units_restored']
            );
            EventAuditLogger::getInstance()->newEvent("delete", $authUser, $authProvider, 1, $auditDetail, $pid);
        }

        if ($drugName !== '') {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM lists WHERE pid = ? AND type = 'medication' AND title = ?",
                [$pid, $drugName]
            );
            EventAuditLogger::getInstance()->newEvent("delete", $authUser, $authProvider, 1, $drugName . " prescription/medication removed", $pid);
        }

        QueryUtils::sqlStatementThrowException("DELETE FROM prescriptions WHERE id = ?", [$id]);
    });
} catch (\RuntimeException $e) {
    ServiceContainer::getLogger()->error('Could not delete prescription', ['prescription_id' => $id, 'exception' => $e]);
    $failed = true;
}

if ($failed) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => xl('The prescription could not be deleted. Nothing was changed.')]);
    exit;
}

echo json_encode(['success' => true]);
