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
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\RecallService;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!CsrfUtils::verifyCsrfToken($_REQUEST['csrf_token_form'] ?? '', session: $session)) {
    CsrfUtils::csrfNotVerified();
}

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
    if (!AclMain::aclCheckCore('patients', 'appt')) {
        http_response_code(403);
        echo json_encode(['error' => xl('Access denied')]);
        exit;
    }

    // Build recall data — delegate to RecallBoardService to avoid duplicate SQL
    $data = [
        'new_pid'          => $_REQUEST['new_pid'] ?? $_REQUEST['pid'] ?? '',
        'new_provider'     => $_REQUEST['new_provider'] ?? $_REQUEST['provider'] ?? '',
        'new_facility'     => $_REQUEST['new_facility'] ?? $_REQUEST['facility'] ?? '',
        'form_recall_date' => $_REQUEST['form_recall_date'] ?? '',
        'new_reason'       => $_REQUEST['new_reason'] ?? $_REQUEST['reason'] ?? '',
    ];

    // Only include demographic fields when the user holds the write permission
    if (AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
        $data += [
            'new_phone_home'  => $_REQUEST['new_phone_home'] ?? null,
            'new_phone_cell'  => $_REQUEST['new_phone_cell'] ?? null,
            'new_email'       => $_REQUEST['new_email'] ?? null,
            'new_email_allow' => $_REQUEST['new_email_allow'] ?? null,
            'new_voice'       => $_REQUEST['new_voice'] ?? null,
            'new_allowsms'    => $_REQUEST['new_allowsms'] ?? null,
            'new_address'     => $_REQUEST['new_address'] ?? null,
            'new_postal_code' => $_REQUEST['new_postal_code'] ?? null,
            'new_city'        => $_REQUEST['new_city'] ?? null,
            'new_state'       => $_REQUEST['new_state'] ?? null,
        ];
    }

    (new RecallService())->saveWithDemographics($data);
    echo json_encode('saved');
    exit;
}

if ((($_REQUEST['action'] ?? '') == 'delete_Recall') && !empty($_REQUEST['pid'])) {
    if (!AclMain::aclCheckCore('patients', 'appt')) {
        http_response_code(403);
        echo json_encode(['error' => xl('Access denied')]);
        exit;
    }
    (new RecallService())->deleteRecallByPidOrId((int) $_REQUEST['pid'], null);
    echo json_encode('deleted');
    exit;
}

// Clear the pidList session whenever this page is loaded.
// session 'pidList' will hold array of patient ids
// which is then used to print 'postcards' and 'Address Labels'
SessionUtil::unsetSession('pidList');
$pid_list = [];

if (($_REQUEST['action'] ?? '') == "process") {
    $item = (string)($_POST['item'] ?? '');
    $decodedPcEid = json_decode((string)($_POST['pc_eid'] ?? ''), true);
    $decodedPidList = json_decode((string)($_POST['parameter'] ?? ''), true);
    $pc_eidList = is_array($decodedPcEid) ? $decodedPcEid : [];
    $pidList = is_array($decodedPidList) ? $decodedPidList : [];

    // Persist the action to recall_board_actions
    $authUserID = (int) ($session->get('authUserID') ?? 0);
    $recallService = new RecallService();
    if (($item === 'notes' || $item === 'phone') && isset($pidList[0])) {
        $pid = (int) $pidList[0];
        $noteText = (string)($_POST['msg_notes'] ?? '');
        $recallService->addAction('recall_' . $pid, $item, $authUserID, $noteText);
    } elseif ($item === 'postcards' || $item === 'labels') {
        foreach ($pidList as $pid) {
            $recallService->addAction('recall_' . (int) $pid, $item, $authUserID);
        }
    }

    $sessionSetArray = [];
    $sessionSetArray['pc_eidList'] = $pc_eidList[0] ?? null;
    $sessionSetArray['pidList'] = $pidList;
    SessionUtil::setSession($sessionSetArray);

    echo json_encode(['success' => true, 'item' => $item, 'pids' => $pidList]);
    exit;
}
if (($_REQUEST['action'] ?? '') == 'save_postcard_template') {
    if (!AclMain::aclCheckCore('patients', 'appt', '', 'write')) {
        http_response_code(403);
        echo json_encode(['error' => xl('Access denied')]);
        exit;
    }
    $val = $_POST['postcard_top'] ?? '';
    $existing = QueryUtils::fetchRecords("SELECT gl_value FROM globals WHERE gl_name = 'recall_board_postcard_top' LIMIT 1");
    if (!empty($existing)) {
        QueryUtils::sqlStatementThrowException("UPDATE globals SET gl_value = ? WHERE gl_name = 'recall_board_postcard_top'", [$val]);
    } else {
        QueryUtils::sqlStatementThrowException("INSERT INTO globals (gl_name, gl_value) VALUES ('recall_board_postcard_top', ?)", [$val]);
    }
    echo json_encode(['success' => true]);
    exit;
}

exit;
