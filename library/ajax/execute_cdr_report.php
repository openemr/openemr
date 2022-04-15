<?php

/**
 * Run a CDR engine report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../clinical_rules.php");

use OpenEMR\ClinicialDecisionRules\AMC\CertificationReportTypes;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//Remove time limit, since script can take many minutes
set_time_limit(0);

// Set the "nice" level of the process for these reports. When the "nice" level
// is increased, these cpu intensive reports will have less affect on the performance
// of other server activities, albeit it may negatively impact the performance
// of this report (note this is only applicable for linux).
if (!empty($GLOBALS['cdr_report_nice'])) {
    proc_nice($GLOBALS['cdr_report_nice']);
}

//  Start a report, which will be stored in the report_results sql table..
if (!empty($_POST['execute_report_id'])) {
    $target_date = (!empty($_POST['date_target'])) ? $_POST['date_target'] : date('Y-m-d H:i:s');
    $rule_filter = (!empty($_POST['type'])) ? $_POST['type'] : "";
    $plan_filter = (!empty($_POST['plan'])) ? $_POST['plan'] : "";
    $organize_method = (empty($plan_filter)) ? "default" : "plans";
    $provider  = $_POST['provider'];
    $pat_prov_rel = (empty($_POST['pat_prov_rel'])) ? "primary" : $_POST['pat_prov_rel'];


  // Process a new report and collect results
    $options = array();
    $array_date = array();

    // all 'amc' reports start with 'amc_', will need to make sure a user can't define their own rule with this pattern
    if (CertificationReportTypes::isAMCReportType($rule_filter)) {
        // For AMC:
        //   need to make $target_date an array with two elements ('dateBegin' and 'dateTarget')
        //   need to send a manual data entry option (number of labs)
        $array_date['dateBegin'] = $_POST['date_begin'];
        $array_date['dateTarget'] = $target_date;
        $options = array('labs_manual' => $_POST['labs'] ?? 0);
    } else {
        // For others, use the unmodified target date array and send an empty options array
        $array_date = $target_date;
    }

    test_rules_clinic_batch_method($provider, $rule_filter, $array_date, "report", $plan_filter, $organize_method, $options, $pat_prov_rel, '', $_POST['execute_report_id']);
} else {
    echo "ERROR";
}
