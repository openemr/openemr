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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\DrugSalesService;

// Ensure user has prescription write permission. The ACL section is
// "patients" (plural) — the previous "patient" (singular) value was a
// typo that did not match any registered ACL and silently denied every
// non-super-user.
if (!AclMain::aclCheckCore('patients', 'rx', '', 'write')) {
    echo xlt('ACL Administration Not Authorized');
    exit;
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$id = (isset($_POST['drugid'])) ? (int)$_POST['drugid'] : '';
if ((!empty($id)) && ($id > 0)) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

    /**
     * find the drug name in the prescription table
     */
    try {
        $drug_name = "SELECT patient_id, drug FROM prescriptions WHERE id = ?";
        $dn = sqlQuery($drug_name, [$id]);
    } catch (\Throwable $e) {
        echo 'Caught exception ', text($e->getMessage()), "\n";
        if ($e->getMessage()) {
            exit;
        }
    }

    /**
     * remove drug from the medication list if exist
     */
    try {
        $pid = $dn['patient_id'];
        $drugname = $dn['drug'];
        if (!empty($drugname)) {
            $medicationlist = "DELETE FROM lists WHERE pid = ? AND type = 'medication' AND title = ?";
            sqlStatement($medicationlist, [$pid, $drugname]);
            EventAuditLogger::getInstance()->newEvent("delete", $session->get('authUser'), $session->get('authProvider'), 1, $drugname . " prescription/medication removed", $pid);
        }
    } catch (\Throwable $e) {
        echo 'Caught exception ', text($e->getMessage()), "\n";
        if ($e->getMessage()) {
            exit;
        }
    }

    /**
     * Reverse any drug_sales attached to this prescription, optionally
     * restoring the dispensed quantity back to inventory. Without this,
     * the sales rows would survive with a dangling prescription_id and
     * keep appearing in encounter reports.
     */
    $restoreInventoryRaw = filter_input(INPUT_POST, 'restore_inventory');
    $restoreInventory = $restoreInventoryRaw !== null && $restoreInventoryRaw !== '' && $restoreInventoryRaw !== '0';
    try {
        $reversal = (new DrugSalesService())->reverseSalesForPrescription((int)$id, $restoreInventory);
        if ($reversal['sales_deleted'] > 0) {
            $auditDetail = sprintf(
                'Reversed %d sale(s) for deleted prescription %d (inventory %s, %s units)',
                $reversal['sales_deleted'],
                $id,
                $reversal['restored_inventory'] ? 'restored' : 'not restored',
                $reversal['units_restored']
            );
            $pidForAudit = isset($pid) && is_numeric($pid) ? (int)$pid : 0;
            EventAuditLogger::getInstance()->newEvent("delete", $session->get('authUser'), $session->get('authProvider'), 1, $auditDetail, $pidForAudit);
        }
    } catch (\Throwable $e) {
        echo 'Caught exception ', text($e->getMessage()), "\n";
        if ($e->getMessage()) {
            exit;
        }
    }

    /**
     * remove drug from the prescription
     */
    try {
        $sql = "delete from prescriptions where id = ?";
        sqlQuery($sql, [$id]);
    } catch (\Throwable $e) {
        echo 'Caught exception ', text($e->getMessage()), "\n";
        if ($e->getMessage()) {
            exit;
        }
    }
}
echo xlt("Finished Deleting");
