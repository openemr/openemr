<?php

/**
 * Controller for fee sheet related AJAX requests
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

if ($task == 'retrieve') {
    $retval = array();
    if ($_REQUEST['mode'] == 'encounters') {
        $encounters = select_encounters($req_pid, $req_encounter);
        if (isset($_REQUEST['prev_encounter'])) {
            $prev_enc = $_REQUEST['prev_encounter'];
        } else {
            if (count($encounters) > 0) {
                $prev_enc = $encounters[0]->getID();
            }
        }

        $issues = array();
        $procedures = array();
        fee_sheet_items($req_pid, ($prev_enc ?? null), $issues, $procedures);
        $retval['prev_encounter'] = $prev_enc ?? null;
        $retval['encounters'] = $encounters;
        $retval['procedures'] = $procedures;
    }

    if ($_REQUEST['mode'] == 'issues') {
        $issues = issue_diagnoses($req_pid, $req_encounter);
    }

    if ($_REQUEST['mode'] == 'common') {
            $issues = common_diagnoses();
    }

    $retval['issues'] = $issues;
    echo json_encode($retval);
    return;
}

if ($task == 'add_diags') {
    if (isset($_REQUEST['diags'])) {
        $json_diags = json_decode($_REQUEST['diags']);
    }

    $diags = array();
    foreach ($json_diags as $diag) {
        $diags[] = new code_info($diag->{'code'}, $diag->{'code_type'}, $diag->{'description'});
    }

    $procs = array();
    if (isset($_REQUEST['procs'])) {
        $json_procs = json_decode($_REQUEST['procs']);
    }

    foreach ($json_procs as $proc) {
        $procs[] = new procedure($proc->{'code'}, $proc->{'code_type'}, $proc->{'description'}, $proc->{'fee'}, $proc->{'justify'}, $proc->{'modifiers'}, $proc->{'units'}, 0);
    }

    $database->StartTrans();
    create_diags($req_pid, $req_encounter, $diags);
    update_issues($req_pid, $req_encounter, $diags);
    create_procs($req_pid, $req_encounter, $procs);
    $database->CompleteTrans();
    return;
}
