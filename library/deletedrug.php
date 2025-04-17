<?php

/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
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

//ensure user has proper access
if (!AclMain::aclCheckCore('patient', 'rx', '', 'write')) {
    echo xlt('ACL Administration Not Authorized');
    exit;
}

$id = (isset($_POST['drugid'])) ? (int)$_POST['drugid'] : '';
if ((!empty($id)) && ($id > 0)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    /**
     * find the drug name in the prescription table
     */
    try {
        $drug_name = "SELECT patient_id, drug FROM prescriptions WHERE id = ?";
        $dn = sqlQuery($drug_name, [$id]);
    } catch (Exception $e) {
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
            EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, $drugname . " prescription/medication removed", $pid);
        }
    } catch (Exception $e) {
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
    } catch (Exception $e) {
        echo 'Caught exception ', text($e->getMessage()), "\n";
        if ($e->getMessage()) {
            exit;
        }
    }
}
echo xlt("Finished Deleting");
