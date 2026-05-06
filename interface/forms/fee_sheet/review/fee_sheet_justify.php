<?php

/**
 * Controller for fee sheet justification AJAX requests
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

if (!AclMain::aclCheckCore('acct', 'bill')) {
    AccessDeniedHelper::deny('Unauthorized access to fee sheet justification');
}

CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$req_pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT) ?: 0;
$req_encounter = filter_input(INPUT_POST, 'encounter', FILTER_VALIDATE_INT) ?: 0;
$task = filter_input(INPUT_POST, 'task');
$billing_id = filter_input(INPUT_POST, 'billing_id', FILTER_VALIDATE_INT) ?: 0;
$skip_issues = filter_input(INPUT_POST, 'skip_issues', FILTER_VALIDATE_BOOLEAN) ?: false;
$diags_raw = filter_input(INPUT_POST, 'diags');

switch ($task) {
    case 'retrieve':
        $retval = [];
        $patient = issue_diagnoses($req_pid, $req_encounter);
        $common = common_diagnoses();
        $retval['patient'] = $patient;
        $retval['common'] = $common;
        $fee_sheet_diags = [];
        $fee_sheet_procs = [];
        fee_sheet_items($req_pid, $req_encounter, $fee_sheet_diags, $fee_sheet_procs);
        $retval['current'] = $fee_sheet_diags;
        echo json_encode($retval);
        return;
    case 'update':
        $diags = [];
        $json_diags = [];
        if (is_string($diags_raw)) {
            $decoded = json_decode($diags_raw);
            $json_diags = is_array($decoded) ? $decoded : [];
        }

        foreach ($json_diags as $diag) {
            $new_diag = new CodeInfo($diag->{'code'}, $diag->{'code_type'}, $diag->{'description'});
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
        break;
}
