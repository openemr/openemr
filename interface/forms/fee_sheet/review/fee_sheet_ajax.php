<?php

/**
 * Controller for fee sheet related AJAX requests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");
require_once("fee_sheet_queries.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Forms\FeeSheet\Review\CodeInfo;
use OpenEMR\Forms\FeeSheet\Review\Procedure;

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::deny('Unauthorized access to fee sheet');
}

CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$req_pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT) ?: 0;
$req_encounter = filter_input(INPUT_POST, 'encounter', FILTER_VALIDATE_INT) ?: 0;
$task = filter_input(INPUT_POST, 'task');
$mode = filter_input(INPUT_POST, 'mode');
$prev_encounter_param = filter_input(INPUT_POST, 'prev_encounter', FILTER_VALIDATE_INT) ?: null;
$diags_raw = filter_input(INPUT_POST, 'diags');
$procs_raw = filter_input(INPUT_POST, 'procs');
$issues = [];

switch ($task) {
    case 'retrieve':
        $retval = [];
        switch ($mode) {
            case 'encounters':
                $encounters = select_encounters($req_pid, $req_encounter);
                $prev_enc = $prev_encounter_param ?? ($encounters[0] ?? null)?->getID();
                $procedures = [];
                if ($prev_enc !== null) {
                    fee_sheet_items($req_pid, $prev_enc, $issues, $procedures);
                }
                $retval['prev_encounter'] = $prev_enc;
                $retval['encounters'] = $encounters;
                $retval['procedures'] = $procedures;
                break;
            case 'issues':
                $issues = issue_diagnoses($req_pid, $req_encounter);
                break;
            case 'common':
                $issues = common_diagnoses();
                break;
        }
        $retval['issues'] = $issues;
        echo json_encode($retval);
        return;
    case 'add_diags':
        $json_diags = is_string($diags_raw) ? json_decode($diags_raw) : [];
        $json_diags = is_array($json_diags) ? $json_diags : [];
        $diags = array_map(
            fn($diag) => new CodeInfo($diag->code, $diag->code_type, $diag->description),
            $json_diags
        );

        $json_procs = is_string($procs_raw) ? json_decode($procs_raw) : [];
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
