<?php

/**
 * Controller for fee sheet related AJAX requests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");
require_once("fee_sheet_queries.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Forms\FeeSheet\Review\CodeInfo;
use OpenEMR\Forms\FeeSheet\Review\Procedure;

if (!AclMain::aclCheckCore('acct', 'bill')) {
    header("HTTP/1.0 403 Forbidden");
    echo "Not authorized for billing";
    return false;
}

if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
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
    $retval = [];
    if ($_REQUEST['mode'] == 'encounters') {
        $encounters = select_encounters($req_pid, $req_encounter);
        if (isset($_REQUEST['prev_encounter'])) {
            $prev_enc = $_REQUEST['prev_encounter'];
        } else {
            if (count($encounters) > 0) {
                $prev_enc = $encounters[0]->getID();
            }
        }

        $issues = [];
        $procedures = [];
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
    echo text(json_encode($retval));
    return;
}

if ($task == 'add_diags') {
    $json_diags = isset($_REQUEST['diags']) ? json_decode((string) $_REQUEST['diags']) : [];
    $json_diags = is_array($json_diags) ? $json_diags : [];
    $diags = array_map(
        fn($diag) => new CodeInfo($diag->code, $diag->code_type, $diag->description),
        $json_diags
    );

    $json_procs = isset($_REQUEST['procs']) ? json_decode((string) $_REQUEST['procs']) : [];
    $json_procs = is_array($json_procs) ? $json_procs : [];
    $procs = array_map(
        fn($proc) => new Procedure(
            $proc->code,
            $proc->code_type,
            $proc->description,
            $proc->fee,
            $proc->justify,
            $proc->modifiers,
            $proc->units,
            /** ai generated code by google-labs-jules starts */
            $proc->mod_size, // mod_size
            $proc->ndc_info ?? '' // ndc_info
            /** ai generated code by google-labs-jules end */
        ),
        $json_procs
    );

    $database->StartTrans();
    create_diags($req_pid, $req_encounter, $diags);
    update_issues($req_pid, $req_encounter, $diags);
    create_procs($req_pid, $req_encounter, $procs);
    $database->CompleteTrans();
    return;
}
