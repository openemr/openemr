<?php

/**
 * /interface/main/messages/save.php
 *
 * Handles AJAX actions for the Message Center — recall CRUD, postcard/label
 * printing preparation, and message updates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2017 Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2024 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../globals.php";
require_once "$srcdir/lists.inc.php";
require_once "$srcdir/forms.inc.php";
require_once "$srcdir/patient.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionUtil;

if (!empty($_REQUEST['pid']) && (($_REQUEST['action'] ?? '') == "new_recall")) {
    $query = "SELECT * FROM patient_data WHERE pid=?";
    $result = sqlQuery($query, [$_REQUEST['pid']]);
    // Calculate age from DOB
    if (!empty($result['DOB'])) {
        $dobDate = new DateTime($result['DOB']);
        $now = new DateTime();
        $result['age'] = $dobDate->diff($now)->y;
    } else {
        $result['age'] = '';
    }
    // uuid is binary and will break json_encode in binary form (not needed, so will remove it from $result array)
    unset($result['uuid']);

    /**
     *  Did the clinician create a PLAN at the last visit?
     *  To do an in office test, and get paid for it,
     *  we must have an order (and a report of the findings).
     *  If the practice is using the eye form then uncomment the 5 lines below.
     *  It provides the PLAN and orders for next visit.
     *  As forms mature, there should be a uniform way to find the PLAN?
     *  And when that day comes we'll put it here...
     *  The other option is to use Visit Categories here.  Maybe both?  Consensus?
     */
    $query = "SELECT ORDER_DETAILS FROM form_eye_mag_orders WHERE pid=? AND ORDER_DATE_PLACED < NOW() ORDER BY ORDER_DATE_PLACED DESC LIMIT 1";
    $result2 = sqlQuery($query, [$_REQUEST['pid']]);
    if (!empty($result2)) {
        $result['PLAN'] = $result2['ORDER_DETAILS'];
    }

    $query = "SELECT * FROM openemr_postcalendar_events WHERE pc_pid =? ORDER BY pc_eventDate DESC LIMIT 1";
    $result2 = sqlQuery($query, [$_REQUEST['pid']]);
    if ($result2) { //if they were never actually scheduled this would be blank
        $result['DOLV']     = oeFormatShortDate($result2['pc_eventDate']);
        $result['provider'] = $result2['pc_aid'];
        $result['facility'] = $result2['pc_facility'];
    }
    /**
     * Is there an existing Recall in place already????
     * If so we need to use that info...
     */
    $query = "SELECT * FROM patient_recalls WHERE r_pid=?";
    $result3 = sqlQuery($query, [$_REQUEST['pid']]);
    if ($result3) {
        $result['recall_date']  = $result3['r_eventDate'];
        $result['PLAN']         = $result3['r_reason'];
        $result['facility']     = $result3['r_facility'];
        $result['provider']     = $result3['r_provider'];
    }
    echo json_encode($result);
    exit;
}

if ((($_REQUEST['action'] ?? '') == 'addRecall') || ($_REQUEST['add_new'] ?? null)) {
    // Save or update a patient recall
    $r_pid       = (int) ($_REQUEST['new_pid'] ?? $_REQUEST['pid'] ?? 0);
    $r_eventDate = $_REQUEST['form_recall_date'] ?? '';
    $r_provider  = (int) ($_REQUEST['new_provider'] ?? $_REQUEST['provider'] ?? 0);
    $r_facility  = (int) ($_REQUEST['new_facility'] ?? $_REQUEST['facility'] ?? 0);
    $r_reason    = $_REQUEST['new_reason'] ?? $_REQUEST['reason'] ?? '';

    if ($r_pid > 0) {
        // Check if recall already exists for this patient
        $existing = sqlQuery("SELECT r_ID FROM patient_recalls WHERE r_pid = ?", [$r_pid]);
        if ($existing) {
            sqlStatement(
                "UPDATE patient_recalls SET r_eventDate = ?, r_provider = ?, r_facility = ?, r_reason = ? WHERE r_pid = ?",
                [$r_eventDate, $r_provider, $r_facility, $r_reason, $r_pid]
            );
        } else {
            sqlStatement(
                "INSERT INTO patient_recalls (r_pid, r_eventDate, r_provider, r_facility, r_reason, r_created) VALUES (?, ?, ?, ?, ?, NOW())",
                [$r_pid, $r_eventDate, $r_provider, $r_facility, $r_reason]
            );
        }

        // Update patient demographics (phone, email, address, HIPAA preferences)
        sqlStatement(
            "UPDATE patient_data SET phone_home = ?, phone_cell = ?, email = ?, hipaa_allowemail = ?, hipaa_voice = ?, hipaa_allowsms = ?, street = ?, postal_code = ?, city = ?, state = ? WHERE pid = ?",
            [
                $_REQUEST['new_phone_home'] ?? '',
                $_REQUEST['new_phone_cell'] ?? '',
                $_REQUEST['new_email'] ?? '',
                $_REQUEST['new_email_allow'] ?? 'NO',
                $_REQUEST['new_voice'] ?? 'NO',
                $_REQUEST['new_allowsms'] ?? 'NO',
                $_REQUEST['new_address'] ?? '',
                $_REQUEST['new_postal_code'] ?? '',
                $_REQUEST['new_city'] ?? '',
                $_REQUEST['new_state'] ?? '',
                $r_pid,
            ]
        );
    }
    echo json_encode('saved');
    exit;
}

if ((($_REQUEST['action'] ?? '') == 'delete_Recall') && !empty($_REQUEST['pid'])) {
    $r_pid = (int) $_REQUEST['pid'];
    sqlStatement("DELETE FROM patient_recalls WHERE r_pid = ?", [$r_pid]);
    echo json_encode('deleted');
    exit;
}

// Clear the pidList session whenever this page is loaded.
// $_SESSION['pidList'] will hold array of patient ids
// which is then used to print 'postcards' and 'Address Labels'
SessionUtil::unsetSession('pidList');
$pid_list = [];

if (($_REQUEST['action'] ?? '') == "process") {
    $new_pid = json_decode((string) $_POST['parameter'], true);
    $new_pc_eid = json_decode((string) $_POST['pc_eid'], true);

    $pc_eidList = json_decode((string) $_POST['pc_eid'], true);
    $pidList = json_decode((string) $_POST['parameter'], true);
    $sessionSetArray['pc_eidList'] = $pc_eidList[0];
    $sessionSetArray['pidList'] = $pidList;
    SessionUtil::setSession($sessionSetArray);

    echo text(json_encode($pidList));
    exit;
}
if (($_REQUEST['go'] ?? '') == "Messages") {
    if (!empty($_REQUEST['msg_id'])) {
        $result = updateMessage($_REQUEST['msg_id']);
        echo json_encode($result);
        exit;
    }
}
exit;
