<?php

/**
 * Controller for fee sheet justification AJAX requests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");
require_once("fee_sheet_queries.php");

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('acct', 'bill')) {
    header("HTTP/1.0 403 Forbidden");
    echo "Not authorized for billing";
    return false;
}


if (isset($_REQUEST['pid'])) {
    $req_pid = $_REQUEST['pid'];
}

if (isset($_REQUEST['encounter'])) {
    $req_encounter = $_REQUEST['encounter'];
}

if (isset($_REQUEST['task'])) {
    $task = $_REQUEST['task'];
}

if (isset($_REQUEST['billing_id'])) {
    $billing_id = $_REQUEST['billing_id'];
}

if ($task == 'retrieve') {
    $retval = array();
    $patient = issue_diagnoses($req_pid, $req_encounter);
    $common = common_diagnoses();
    $retval['patient'] = $patient;
    $retval['common'] = $common;
    $fee_sheet_diags = array();
    $fee_sheet_procs = array();
    fee_sheet_items($req_pid, $req_encounter, $fee_sheet_diags, $fee_sheet_procs);
    $retval['current'] = $fee_sheet_diags;
    echo json_encode($retval);
    return;
}

if ($task == 'update') {
    $skip_issues = false;
    if (isset($_REQUEST['skip_issues'])) {
        $skip_issues = $_REQUEST['skip_issues'] == 'true';
    }

    $diags = array();
    if (isset($_REQUEST['diags'])) {
        $json_diags = json_decode($_REQUEST['diags']);
    }

    foreach ($json_diags as $diag) {
        $new_diag = new code_info($diag->{'code'}, $diag->{'code_type'}, $diag->{'description'});
        if (isset($diag->{'prob_id'})) {
            $new_diag->db_id = $diag->{'prob_id'};
        } else {
            $new_diag->db_id = null;
            $new_diag->create_problem = $diag->{'create_problem'};
        }

        $diags[] = $new_diag;
    }

    $database->StartTrans();
    create_diags($req_pid, $req_encounter, $diags);
    if (!$skip_issues) {
        update_issues($req_pid, $req_encounter, $diags);
    }

    update_justify($req_pid, $req_encounter, $diags, $billing_id);
    $database->CompleteTrans();

    // save current bill input values
    if (isset($_REQUEST['billing'])) {
        $billing = json_decode($_REQUEST['billing'], true);
        $billData = array();
        $fields = array(
            'auth' => 'authorized',
            'price' => 'fee',
            'mod' => 'modifier',
            'provid' => 'provider_id',
            'ndcnum' => 'ndc_info'
        );

        foreach($billing as $key => $value) {
            preg_match('/bill\[(.*)\]\[(.*)\]/', $key, $arr);
            $id = $arr[1];
            $name = $arr[2];
            $billData[$id][$name] = $value;
        }

        sqlBeginTrans();

        foreach($billData as $bill) {
            $id = $bill['id'];

            foreach($bill as $key => $value) {
               if (in_array($key, array('code', 'auth', 'units', 'price', 'pricelevel', 'mod', 'provid', 'ndcnum', 'notecodes', 'exclude'))) {
                   $field = isset($fields[$key]) ? $fields[$key] : $key;
                   sqlStatement("UPDATE billing SET `$field` = ? WHERE id = ?", array($value, $id));
               }
            }
        }

        sqlCommitTrans();
    }
}
