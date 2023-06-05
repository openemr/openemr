<?php

/**
 * Clinical Decision Rules(CDR) engine functions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Medical Information Integration, LLC
 * @author    Ensofttek, LLC
 * @copyright Copyright (c) 2010-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Medical Information Integration, LLC
 * @copyright Copyright (c) 2011 Ensofttek, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/patient.inc.php");
require_once(dirname(__FILE__) . "/forms.inc.php");
require_once(dirname(__FILE__) . "/options.inc.php");
require_once(dirname(__FILE__) . "/report_database.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\ClinicialDecisionRules\AMC\CertificationReportTypes;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FacilityService;

/**
 * Return listing of CDR reminders in log.
 *
 * @param  string   $begin_date  begin date (optional)
 * @param  string   $end_date    end date (optional)
 * @return sqlret                sql return query
 */
function listingCDRReminderLog($begin_date = '', $end_date = '')
{

    if (empty($end_date)) {
        $end_date = date('Y-m-d H:i:s');
    }

    $sqlArray = array();
    $sql = "SELECT `date`, `pid`, `uid`, `category`, `value`, `new_value` FROM `clinical_rules_log` WHERE `date` <= ?";
    $sqlArray[] = $end_date;
    if (!empty($begin_date)) {
        $sql .= " AND `date` >= ?";
        $sqlArray[] = $begin_date;
    }

    $sql .= " ORDER BY `date` DESC";

    return sqlStatement($sql, $sqlArray);
}

/**
 * Display the clinical summary widget.
 *
 * @param  integer  $patient_id     pid of selected patient
 * @param  string   $mode           choose either 'reminders-all' or 'reminders-due' (required)
 * @param  string   $dateTarget     target date (format Y-m-d H:i:s). If blank then will test with current date as target.
 * @param  string   $organize_mode  Way to organize the results (default or plans)
 * @param  string   $user           If a user is set, then will only show rules that user has permission to see.
 */
function clinical_summary_widget($patient_id, $mode, $dateTarget = '', $organize_mode = 'default', $user = '')
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect active actions
    $actions = test_rules_clinic('', 'passive_alert', $dateTarget, $mode, $patient_id, '', $organize_mode, array(), 'primary', null, null, $user);

  // Display the actions
    $current_targets = array();
    echo "<div class=\"list-group list-group-flush\">";
    foreach ($actions as $action) {
        // Deal with plan names first
        if (isset($action['is_plan']) && $action['is_plan']) {
            echo "<br /><b>";
            echo xlt("Plan") . ": ";
            echo generate_display_field(array('data_type' => '1','list_id' => 'clinical_plans'), $action['id']);
            echo "</b><br />";
            continue;
        }

        echo "<div class=\"list-group-item p-1 d-flex w-100 justify-content-between\">";

        // Collect the Rule Title, Bibliographical citation, Rule Developer, Rule Funding Source, and Rule Release and show it when hover over the item.
        //  Show the link for Linked referential CDS (this is set via codetype:code)
        $tooltip = '';
        if (!empty($action['rule_id'])) {
            $rule_title = getListItemTitle("clinical_rules", $action['rule_id']);
            $ruleData = sqlQuery("SELECT `bibliographic_citation`, `developer`, `funding_source`, `release_version`, `web_reference`, `linked_referential_cds` " .
                           "FROM `clinical_rules` " .
                           "WHERE  `id`=? AND `pid`=0", array($action['rule_id']));
            $bibliographic_citation = $ruleData['bibliographic_citation'];
            $developer = $ruleData['developer'];
            $funding_source = $ruleData['funding_source'];
            $release = $ruleData['release_version'];
            $web_reference = $ruleData['web_reference'];
            $linked_referential_cds = $ruleData['linked_referential_cds'];
            if (!empty($rule_title)) {
                  $tooltip = xla('Rule Title') . ": " . attr($rule_title) . "&#013;";
            }

            if (!empty($bibliographic_citation)) {
                $tooltip .= xla('Rule Bibliographic Citation') . ": " . attr($bibliographic_citation) . "&#013;";
            }

            if (!empty($developer)) {
                  $tooltip .= xla('Rule Developer') . ": " . attr($developer) . "&#013;";
            }

            if (!empty($funding_source)) {
                  $tooltip .= xla('Rule Funding Source') . ": " . attr($funding_source) . "&#013;";
            }

            if (!empty($release)) {
                  $tooltip .= xla('Rule Release') . ": " . attr($release);
            }

            if ((!empty($tooltip)) || (!empty($web_reference))) {
                if (!empty($web_reference)) {
                    $tooltip = "<a href='" . attr($web_reference) . "' rel='noopener' target='_blank' style='white-space: pre-line;' title='" . $tooltip . "'><i class='fas fa-question-circle'></i></a>";
                } else {
                    $tooltip = "<span style='white-space: pre-line;' title='" . $tooltip . "'><i class='fas fa-question-circle'></i></span>";
                }
            }

            if (!empty($linked_referential_cds)) {
                $codeParse = explode(":", $linked_referential_cds);
                $codetype = $codeParse[0] ?? null;
                $code = $codeParse[1] ?? null;
                if (!empty($codetype) && !empty($code)) {
                    $tooltip .= "<a href='' title='" . xla('Link to Referential CDS') . "' onclick='referentialCdsClick(" . attr_js($codetype) . ", " . attr_js($code) . ")'><i class='fas fa-external-link-square-alt'></i></a>";
                }
            }
        }

        if ($action['custom_flag']) {
            // Start link for reminders that use the custom rules input screen
            $url = "../rules/patient_data.php?category=" . attr_url($action['category']);
            $url .= "&item=" . attr_url($action['item']);
            echo "<a href='" . $url . "' class='medium_modal' onclick='return top.restoreSession()'>";
        } elseif ($action['clin_rem_link']) {
            // Start link for reminders that use the custom rules input screen
            $pieces_url = parse_url($action['clin_rem_link']);
            $url_prefix = $pieces_url['scheme'] ?? '';
            if ($url_prefix == 'https' || $url_prefix == 'http') {
                echo "<a href='" . $action['clin_rem_link'] .
                "' class='medium_modal' onclick='return top.restoreSession()'>";
            } else {
                echo "<a href='../../../" . $action['clin_rem_link'] .
                "' class='medium_modal' onclick='return top.restoreSession()'>";
            }
        } else {
            // continue since no link is needed
        }

        // Display Reminder Details
        echo generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $action['category']) .
        ": " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $action['item']);

        if ($action['custom_flag'] || $action['clin_rem_link']) {
            // End link for reminders that use an html link
            echo "</a>";
        }

        // Display due status
        if ($action['due_status']) {
            // Color code the status (red for past due, purple for due, green for not due and black for soon due)
            if ($action['due_status'] == "past_due") {
                echo "<span class='text-danger'>";
            } elseif ($action['due_status'] == "due") {
                echo "<span class='text-warning'>";
            } elseif ($action['due_status'] == "not_due") {
                echo "<span class='text-success>";
            } else {
                echo "<span>";
            }

            echo generate_display_field(array('data_type' => '1','list_id' => 'rule_reminder_due_opt'), $action['due_status']);
        }

        // Display the tooltip
        if (!empty($tooltip)) {
            echo "&nbsp;{$tooltip}";
        }

        echo "</span>";

        // Add the target(and rule id and room for future elements as needed) to the $current_targets array.
        // Only when $mode is reminders-due
        if ($mode == "reminders-due" && $GLOBALS['enable_alert_log']) {
            $target_temp = $action['category'] . ":" . $action['item'];
            $current_targets[$target_temp] =  array('rule_id' => $action['rule_id'],'due_status' => $action['due_status']);
        }
        echo "</div>";
    }
    echo "</div>";

  // Compare the current with most recent action log (this function will also log the current actions)
  // Only when $mode is reminders-due
    if ($mode == "reminders-due" && $GLOBALS['enable_alert_log']) {
        $new_targets = compare_log_alerts($patient_id, $current_targets, 'clinical_reminder_widget', $_SESSION['authUserID']);
        if (!empty($new_targets) && $GLOBALS['enable_cdr_new_crp']) {
            // If there are new action(s), then throw a popup (if the enable_cdr_new_crp global is turned on)
            //  Note I am taking advantage of a slight hack in order to run javascript within code that
            //  is being passed via an ajax call by using a dummy image.
            echo '<img src="../../pic/empty.gif" onload="alert(\'' . xls('New Due Clinical Reminders') . '\n\n';
            foreach ($new_targets as $key => $value) {
                $category_item = explode(":", $key);
                $category = $category_item[0];
                $item = $category_item[1];
                echo generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $category) .
                   ': ' . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $item) . '\n';
            }

            echo '\n' . '(' . xls('See the Clinical Reminders widget for more details') . ')';
            echo '\');this.parentNode.removeChild(this);" />';
        }
    }
}

/**
 * Display the active screen reminder.
 *
 * @param  integer  $patient_id     pid of selected patient
 * @param  string   $mode           choose either 'reminders-all' or 'reminders-due' (required)
 * @param  string   $dateTarget     target date (format Y-m-d H:i:s). If blank then will test with current date as target.
 * @param  string   $organize_mode  Way to organize the results (default or plans)
 * @param  string   $user           If a user is set, then will only show rules that user has permission to see
 * @param  string   $test           Set to true when only checking if there are alerts (skips the logging then)
 * @return string                   html display output.
 */
function active_alert_summary($patient_id, $mode, $dateTarget = '', $organize_mode = 'default', $user = '', $test = false)
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect active actions
    $actions = test_rules_clinic('', 'active_alert', $dateTarget, $mode, $patient_id, '', $organize_mode, array(), 'primary', null, null, $user);

    if (empty($actions)) {
        return false;
    }

    $returnOutput = "";
    $current_targets = array();

  // Display the actions
    foreach ($actions as $action) {
        // Deal with plan names first
        if ($action['is_plan']) {
            $returnOutput .= "<br /><b>";
            $returnOutput .= xlt("Plan") . ": ";
            $returnOutput .= generate_display_field(array('data_type' => '1','list_id' => 'clinical_plans'), $action['id']);
            $returnOutput .= "</b><br />";
            continue;
        }

        // Display Reminder Details
        $returnOutput .= generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $action['category']) .
        ": " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $action['item']);

        // Display due status
        if ($action['due_status']) {
            // Color code the status (red for past due, purple for due, green for not due and black for soon due)
            if ($action['due_status'] == "past_due") {
                $returnOutput .= "&nbsp;&nbsp;(<span style='color:red'>";
            } elseif ($action['due_status'] == "due") {
                $returnOutput .= "&nbsp;&nbsp;(<span style='color:purple'>";
            } elseif ($action['due_status'] == "not_due") {
                $returnOutput .= "&nbsp;&nbsp;(<span style='color:green'>";
            } else {
                $returnOutput .= "&nbsp;&nbsp;(<span>";
            }

            $returnOutput .= generate_display_field(array('data_type' => '1','list_id' => 'rule_reminder_due_opt'), $action['due_status']) . "</span>)<br />";
        } else {
            $returnOutput .= "<br />";
        }

        // Add the target(and rule id and room for future elements as needed) to the $current_targets array.
        // Only when $mode is reminders-due and $test is FALSE
        if (($mode == "reminders-due") && ($test === false) && ($GLOBALS['enable_alert_log'])) {
            $target_temp = $action['category'] . ":" . $action['item'];
            $current_targets[$target_temp] =  array('rule_id' => $action['rule_id'],'due_status' => $action['due_status']);
        }
    }

  // Compare the current with most recent action log (this function will also log the current actions)
  // Only when $mode is reminders-due and $test is FALSE
    if (($mode == "reminders-due") && ($test === false) && ($GLOBALS['enable_alert_log'])) {
        $new_targets = compare_log_alerts($patient_id, $current_targets, 'active_reminder_popup', $_SESSION['authUserID']);
        if (!empty($new_targets)) {
            $returnOutput .= "<br />" . xlt('New Items (see above for details)') . ":<br />";
            foreach ($new_targets as $key => $value) {
                $category_item = explode(":", $key);
                $category = $category_item[0];
                $item = $category_item[1];
                $returnOutput .= generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $category) .
                   ': ' . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $item) . '<br />';
            }
        }
    }

    return $returnOutput;
}

/**
 * Process and return allergy conflicts (when a active medication or presciption is on allergy list).
 *
 * @param  integer  $patient_id     pid of selected patient
 * @param  string   $mode           either 'all' or 'new' (required)
 * @param  string   $user           If a user is set, then will only show rules that user has permission to see
 * @param  string   $test           Set to true when only checking if there are alerts (skips the logging then)
 * @return  array/boolean           Array of allergy alerts or FALSE is empty.
 */
function allergy_conflict($patient_id, $mode, $user, $test = false)
{

  // Collect allergies
    $sqlParam = array();
    $sqlParam[] = $patient_id;
    $res_allergies = sqlStatement("SELECT `title` FROM `lists` WHERE `type`='allergy' " .
                                "AND `activity`=1 " .
                                "AND ( " .
                                dateEmptySql('enddate') .
                                "OR `enddate` > NOW() ) " .
                                "AND `pid`=?", $sqlParam);
    $allergies = array();
    for ($iter = 0; $row = sqlFetchArray($res_allergies); $iter++) {
        $allergies[$iter] = $row['title'];
    }

  // Build sql element of IN for below queries
    $sqlParam = array();
    $sqlIN = '';
    $firstFlag = true;
    foreach ($allergies as $allergy) {
        $sqlParam[] = $allergy;
        if ($firstFlag) {
            $sqlIN .= "?";
            $firstFlag = false;
        } else {
            $sqlIN .= ",?";
        }
    }

  // Check if allergies conflict with medications or prescriptions
    $conflicts_unique = array();
    if (!empty($sqlParam)) {
        $conflicts = array();
        $sqlParam[] = $patient_id;
        $res_meds = sqlStatement("SELECT `title` FROM `lists` WHERE `type`='medication' " .
                             "AND `activity`=1 " .
                             "AND ( " .
                             dateEmptySql('enddate') .
                             "OR `enddate` > NOW() )" .
                             "AND `title` IN (" . $sqlIN . ") AND `pid`=?", $sqlParam);
        while ($urow = sqlFetchArray($res_meds)) {
              $conflicts[] = $urow['title'];
        }

        $res_rx = sqlStatement("SELECT `drug` FROM `prescriptions` WHERE `active`=1 " .
                           "AND `drug` IN (" . $sqlIN . ") AND `patient_id`=?", $sqlParam);
        while ($urow = sqlFetchArray($res_rx)) {
              $conflicts[] = $urow['drug'];
        }

        if (!empty($conflicts)) {
              $conflicts_unique = array_unique($conflicts);
        }
    }

  // If there are conflicts, $test is FALSE, and alert logging is on, then run through compare_log_alerts
    $new_conflicts = array();
    if ((!empty($conflicts_unique)) && $GLOBALS['enable_alert_log'] && ($test === false)) {
        $new_conflicts = compare_log_alerts($patient_id, $conflicts_unique, 'allergy_alert', $_SESSION['authUserID'], $mode);
    }

    if ($mode == 'all') {
        if (!empty($conflicts_unique)) {
            return $conflicts_unique;
        } else {
            return false;
        }
    } else { // $mode = 'new'
        if (!empty($new_conflicts)) {
            return $new_conflicts;
        } else {
            return false;
        }
    }
}

/**
 * Compare current alerts with prior (in order to find new actions)
 * Also functions to log the actions.
 *
 * @param  integer  $patient_id      pid of selected patient
 * @param  array    $current_targets array of targets
 * @param  string   $category        clinical_reminder_widget, active_reminder_popup, or allergy_alert
 * @param  integer  $userid          user id of user.
 * @param  string   $log_trigger     if 'all', then always log. If 'new', then only trigger log when a new item noted.
 * @return array                     array with targets with associated rule.
 */
function compare_log_alerts($patient_id, $current_targets, $category = 'clinical_reminder_widget', $userid = '', $log_trigger = 'all')
{

    if (empty($userid)) {
        $userid = $_SESSION['authUserID'];
    }

    if (empty($current_targets)) {
        $current_targets = array();
    }

  // Collect most recent action_log
    $prior_targets_sql = sqlQuery("SELECT `value` FROM `clinical_rules_log` " .
                                 "WHERE `category` = ? AND `pid` = ? AND `uid` = ? " .
                                 "ORDER BY `id` DESC LIMIT 1", array($category,$patient_id,$userid));
    $prior_targets = array();
    if (!empty($prior_targets_sql['value'])) {
        $prior_targets = json_decode($prior_targets_sql['value'], true);
    }

  // Compare the current with most recent log
    if (($category == 'clinical_reminder_widget') || ($category == 'active_reminder_popup')) {
        //using fancy structure to store multiple elements
        $new_targets = array_diff_key($current_targets, $prior_targets);
    } else { // $category == 'allergy_alert'
        //using simple array
        $new_targets = array_diff($current_targets, $prior_targets);
    }

  // Store current action_log and the new items
  //  If $log_trigger=='all'
  //  or If $log_trigger=='new' and there are new items
    if (($log_trigger == 'all') || (($log_trigger == 'new')  && (!empty($new_targets)))) {
        $current_targets_json = json_encode($current_targets);
        $new_targets_json = '';
        if (!empty($new_targets)) {
            $new_targets_json = json_encode($new_targets);
        }

        sqlStatement("INSERT INTO `clinical_rules_log` " .
              "(`date`,`pid`,`uid`,`category`,`value`,`new_value`) " .
              "VALUES (NOW(),?,?,?,?,?)", array($patient_id,$userid,$category,$current_targets_json,$new_targets_json));
    }

  // Return new actions (if there are any)
    return $new_targets;
}

/**
 * Process clinic rules via a batching method to improve performance and decrease memory overhead.
 *
 * Test the clinic rules of entire clinic and create a report or patient reminders (can also test
 * on one patient or patients of one provider). The structure of the returned results is dependent on the
 * $organize_mode and $mode parameters.
 * <pre>The results are dependent on the $organize_mode parameter settings
 *   'default' organize_mode:
 *     Returns a two-dimensional array of results organized by rules (dependent on the following $mode settings):
 *       'reminders-due' mode - returns an array of reminders (action array elements plus a 'pid' and 'due_status')
 *       'reminders-all' mode - returns an array of reminders (action array elements plus a 'pid' and 'due_status')
 *       'report' mode        - returns an array of rows for the Clinical Quality Measures (CQM) report
 *   'plans' organize_mode:
 *     Returns similar to default, but organizes by the active plans
 * </pre>
 *
 * @param  integer      $provider      id of a selected provider. If blank, then will test entire clinic. If 'collate_outer' or 'collate_inner', then will test each provider in entire clinic; outer will nest plans  inside collated providers, while inner will nest the providers inside the plans (note inner and outer are only different if organize_mode is set to plans).
 * @param  string       $type          rule filter (active_alert,passive_alert,cqm,cqm_2011,cqm_2014,amc,amc_2011,amc_2014,patient_reminder). If blank then will test all rules.
 * @param  string/array $dateTarget    target date (format Y-m-d H:i:s). If blank then will test with current date as target. If an array, then is holding two dates ('dateBegin' and 'dateTarget').
 * @param  string       $mode          choose either 'report' or 'reminders-all' or 'reminders-due' (required)
 * @param  string       $plan          test for specific plan only
 * @param  string       $organize_mode Way to organize the results (default, plans). See above for organization structure of the results.
 * @param  array        $options       can hold various option (for now, used to hold the manual number of labs for the AMC report)
 * @param  string       $pat_prov_rel  How to choose patients that are related to a chosen provider. 'primary' selects patients that the provider is set as primary provider. 'encounter' selectes patients that the provider has seen. This parameter is only applicable if the $provider parameter is set to a provider or collation setting.
 * @param  integer      $batchSize     number of patients to batch (default is 100; plan to optimize this default setting in the future)
 * @param  integer      $report_id     id of report in database (if already bookmarked)
 * @return array                       See above for organization structure of the results.
 */
function test_rules_clinic_batch_method($provider = '', $type = '', $dateTarget = '', $mode = '', $plan = '', $organize_mode = 'default', $options = array(), $pat_prov_rel = 'primary', $batchSize = '', $report_id = null)
{

  // Default to a batchsize, if empty
    if (empty($batchSize)) {
        $batchSize = 100;
    }

  // Collect total number of pertinent patients (to calculate batching parameters)
    // note for group_calculation we will have some inefficiencies here
    $totalNumPatients = (int)buildPatientArray('', $provider, $pat_prov_rel, null, null, true);

  // Cycle through the batches and collect/combine results
    if (($totalNumPatients % $batchSize) > 0) {
        // not perfectly divisible
        $totalNumberBatches = floor($totalNumPatients / $batchSize) + 1;
    } else {
        // perfectly divisible
        $totalNumberBatches = floor($totalNumPatients / $batchSize);
    }

    (new SystemLogger())->debug(
        "test_rules_clinic_batch_method()",
        ['totalNumPatients' => $totalNumPatients, 'totalNumberBatches' => $totalNumberBatches]
    );

  // Fix things in the $options array(). This now stores the number of labs to be used in the denominator in the AMC report.
  // The problem with this variable is that is is added in every batch. So need to fix it by dividing this number by the number
  // of planned batches(note the fixed array will go into the test_rules_clinic function, however the original will be used
  // in the report storing/tracking engine.
    $options_modified = $options;
    if (!empty($options_modified['labs_manual'])) {
        $options_modified['labs_manual'] = $options_modified['labs_manual'] / $totalNumberBatches;
    }

  // Prepare the database to track/store results
    $fields = array('provider' => $provider,'mode' => $mode,'plan' => $plan,'organize_mode' => $organize_mode,'pat_prov_rel' => $pat_prov_rel);
    if (is_array($dateTarget)) {
        $fields = array_merge($fields, array('date_target' => $dateTarget['dateTarget']));
        $fields = array_merge($fields, array('date_begin' => $dateTarget['dateBegin']));
    } else {
        if (empty($dateTarget)) {
            $fields = array_merge($fields, array('date_target' => date("Y-m-d H:i:s")));
        } else {
            $fields = array_merge($fields, array('date_target' => $dateTarget));
        }
    }

    if (!empty($options)) {
        foreach ($options as $key => $value) {
            $fields = array_merge($fields, array($key => $value));
        }
    }

    $report_id = beginReportDatabase($type, $fields, $report_id);
    setTotalItemsReportDatabase($report_id, $totalNumPatients);

  // Set ability to itemize report if this feature is turned on
    if (
        ( ($type == "active_alert" || $type == "passive_alert")          && ($GLOBALS['report_itemizing_standard']) ) ||
        ( ($type == "cqm" || $type == "cqm_2011" || $type == "cqm_2014") && ($GLOBALS['report_itemizing_cqm'])      ) ||
        ( (CertificationReportTypes::isAMCReportType($type)) && ($GLOBALS['report_itemizing_amc'])      )
    ) {
        $GLOBALS['report_itemizing_temp_flag_and_id'] = $report_id;
    } else {
        $GLOBALS['report_itemizing_temp_flag_and_id'] = 0;
    }

    for ($i = 0; $i < $totalNumberBatches; $i++) {
        // If itemization is turned on, then reset the rule id iterator
        if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
            $GLOBALS['report_itemized_test_id_iterator'] = 1;
        }

        $dataSheet_batch = test_rules_clinic($provider, $type, $dateTarget, $mode, '', $plan, $organize_mode, $options_modified, $pat_prov_rel, (($batchSize * $i) + 1), $batchSize);
        $dataSheet = array();
        if ($i == 0) {
            // For first cycle, simply copy it to dataSheet
            $dataSheet = $dataSheet_batch;
        } else {
            //debug
            //error_log("CDR: ".print_r($dataSheet,TRUE),0);
            //error_log("CDR: ".($batchSize*$i)." records",0);

            // Integrate batch results into main dataSheet
            foreach ($dataSheet_batch as $key => $row) {
                if (!$row['is_sub']) {
                    //skip this stuff for the sub entries (and use previous main entry in percentage calculation)
                    $total_patients = $dataSheet[$key]['total_patients'] + $row['total_patients'];
                    $dataSheet[$key]['total_patients'] = $total_patients;
                    $excluded = $dataSheet[$key]['excluded'] + $row['excluded'];
                    $dataSheet[$key]['excluded'] = $excluded;
                    $pass_filter = $dataSheet[$key]['pass_filter'] + $row['pass_filter'];
                    $dataSheet[$key]['pass_filter'] = $pass_filter;
                }

                $pass_target = $dataSheet[$key]['pass_target'] + $row['pass_target'];
                $dataSheet[$key]['pass_target'] = $pass_target;
                $dataSheet[$key]['percentage'] = calculate_percentage($pass_filter, $excluded, $pass_target);
            }
        }

        //Update database to track results
        updateReportDatabase($report_id, ($total_patients ?? null));
    }

  // Record results in database and send to screen, if applicable.
    if (!empty($dataSheet)) {
        finishReportDatabase($report_id, json_encode($dataSheet));
        return $dataSheet;
    } else {
        // make sure the report at least completes even if we have nothing here.
        finishReportDatabase($report_id, json_encode([]));
        return [];
    }
}

function rules_clinic_get_providers($billing_facility, $pat_prov_rel)
{
    $results = [];
    if ($pat_prov_rel == "encounter") {
        $rez = sqlStatementCdrEngine(
            "SELECT id AS provider_id, lname, fname, npi, federaltaxid FROM users WHERE authorized = 1 AND users.id IN( "
            . " SELECT DISTINCT `provider_id` FROM `form_encounter` WHERE `provider_id` IS NOT NULL and `billing_facility` = ? "
            . " UNION SELECT DISTINCT `supervisor_id` AS `provider_id` FROM `form_encounter` WHERE `supervisor_id` "
                . " IS NOT NULL and `billing_facility` = ? "
            . ") "
            . " ORDER BY provider_id ",
            array($billing_facility, $billing_facility)
        );
    } else if ($pat_prov_rel == "primary") {
        $rez = sqlStatementCdrEngine(
            "SELECT id AS provider_id , lname, fname, npi, federaltaxid FROM users WHERE authorized = 1 AND users.id IN ( "
            . "SELECT DISTINCT `providerID` AS provider_id FROM `patient_data` JOIN `users` providers ON providerID=providers.id "
            .    " WHERE `providers`.billing_facility_id = ? "
            . ") "
            . " ORDER BY provider_id ",
            array($billing_facility)
        );
    }

    if (!empty($rez)) {
        while ($urow = sqlFetchArray($rez)) {
            $results[] = $urow;
        }
    }

    return $results;
}

/**
 * Process clinic rules for the group_calculation provider method.  This will process clinical rules for each of the
 * billing facilities in the entire organization.  Rules are applied to the entire facility where patients are connected
 * to the billing facility either through encounters or their primary care provider.  Rules are then applied to each
 * individual provider who is connected to the billing facility.  This satisifies regulatory requirements where rule
 * calculations must be able to group results for one or more provider NPIs to a group tax id number (TIN).  One example
 * of this is in the United States where providers can reassign their medicaid/medicare reimbursements to another TIN and
 * need to report on calculations at both the group and provider group level.
 *
 * @param  string       $type          rule filter (active_alert,passive_alert,cqm,cqm_2011,cqm_2104,amc,amc_2011,amc_2014,patient_reminder). If blank then will test all rules.
 * @param  string/array $dateArray     Date filter to run the calculation on.  Should have two keys ('dateBegin' and 'dateTarget').
 * @param  string       $mode          choose either 'report' or 'reminders-all' or 'reminders-due' (required)
 * @param  integer      $patient_id    pid of patient. If blank then will check all patients.
 * @param  string       $plan          test for specific plan only
 * @param  string       $organize_mode Way to organize the results (default, plans). See above for organization structure of the results.
 * @param  array        $options       can hold various option (for now, used to hold the manual number of labs for the AMC report)
 * @param  string       $pat_prov_rel  How to choose patients that are related to a chosen provider. 'primary' selects patients that the provider is set as primary provider. 'encounter' selectes patients that the provider has seen.
 * @param  integer      $start         applicable patient to start at (when batching process)
 * @param  integer      $batchSize     number of patients to batch (when batching process)
 * @param  string       $user          If a user is set, then will only show rules that user has permission to see(only applicable for per patient and not when do reports).
 * @return array                       See above for organization structure of the results.
 */
function test_rules_clinic_group_calculation($type = '', array $dateArray = array(), $mode = '', $patient_id = '', $plan = '', $organize_mode = 'default', $options = array(), $pat_prov_rel = 'primary', $start = null, $batchSize = null, $user = '')
{
    (new SystemLogger())->debug(
        "test_rules_clinic_group_calculation()",
        array_combine(
            ['type', 'dateArray', 'mode', 'patient_id', 'plan', 'organize_mode'
            ,
            'options',
            'pat_prov_rel',
            'start',
            'batchSize',
            'user'],
            func_get_args()
        )
    );

    $results = [];
    $facilityService = new FacilityService();
    $billingLocations = $facilityService->getAllBillingLocations();
    if (!empty($billingLocations)) {
        //Collect applicable rules
        // Note that due to a limitation in the this function, the patient_id is explicitly
        //  for grouping items when not being done in real-time or for official reporting.
        //  So for cases such as patient reminders on a clinic scale, the calling function
        //  will actually need rather than pass in a explicit patient_id for each patient in
        //  a separate call to this function.
        $rules = resolve_rules_sql($type, $patient_id, false, $plan, $user);
        $filteredRules = array_filter($rules, function ($rule) {
            return $rule['amc_flag'] || $rule['cqm_flag'];
        });

        // TODO: @adunsulag I'd prefer to use a service here, but in order to be consistent with everything else in this file we will use sqlStatementCdrEngine
        $sql =  "SELECT id, name, federal_ein, facility_npi, tax_id_type FROM facility WHERE facility.billing_location = 1 "
        . " AND id IN (SELECT DISTINCT billing_facility FROM form_encounter) "
        . " ORDER BY facility.id ASC";
        $frez = sqlStatementCdrEngine($sql);
        while ($frow = sqlFetchArray($frez)) {
            // we run with the encounter_billing_facility

            $options['billing_facility_id'] = $frow['id'];
            $patientData = buildPatientArray($patient_id, 'group_calculation', $pat_prov_rel, $start, $batchSize, false, $frow['id']);

            (new SystemLogger())->debug(
                "test_rules_clinic_group_calculation() patientIds retrieved for facility",
                ['facilityId' => $frow['id'], 'patientData' => $patientData]
            );

            if (!empty($patientData)) {
                $group_item = [];
                $group_item['is_provider_group'] = true;
                $group_item['name'] = $frow['name'];
                $group_item['npi'] = $frow['facility_npi'];
                $group_item['federaltaxid'] = $frow['federal_ein'];
                $results[] = $group_item;

                foreach ($filteredRules as $rowRule) {
                    $tempResults = test_rules_clinic_cqm_amc_rule($rowRule, $patientData, $dateArray, $dateArray, $options, null, $pat_prov_rel);
                    if (!empty($tempResults)) {
                        $results = array_merge($results, $tempResults);
                    }
                    (new SystemLogger())->debug(
                        "test_rules_clinic_group_calculation() results returned for facility",
                        ['facilityId' => $frow['id'], 'results' => $tempResults]
                    );
                }

                // now we are going to do our providers
                $providers = rules_clinic_get_providers($frow['id'], $pat_prov_rel);
                if (!empty($providers)) { // should always be populated here
                    $facility_pat_prov_rel = $pat_prov_rel . "_billing_facility";
                    foreach ($providers as $prov) {
                        $newResults = test_rules_clinic($prov['provider_id'], $type, $dateArray, $mode, $patient_id, $plan, $organize_mode, $options, $facility_pat_prov_rel, $start, $batchSize, $user);
                        if (!empty($newResults)) {
                            $provider_item['is_provider'] = true;
                            $provider_item['is_provider_in_group'] = true;
                            $provider_item['group'] = [
                                'name' => $frow['name']
                                ,'npi' => $frow['facility_npi']
                                ,'federaltaxid' => $frow['federal_ein']
                            ];
                            $provider_item['prov_lname'] = $prov['lname'];
                            $provider_item['prov_fname'] = $prov['fname'];
                            $provider_item['npi'] = $prov['npi'];
                            $provider_item['federaltaxid'] = $prov['federaltaxid'];
                            $results[] = $provider_item;
                            $results = array_merge($results, $newResults);
                        }
                    }
                }
            }
        }
    }
    return $results;
}

/**
 * Process clinic rules for the collate outer and collate inner methods
 *
 * Test the clinic rules of entire clinic and create a report or patient reminders (can also test
 * on one patient or patients of one provider). The structure of the returned results is dependent on the
 * $organize_mode and $mode parameters.
 * <pre>The results are dependent on the $organize_mode parameter settings
 *   'default' organize_mode:
 *     Returns a two-dimensional array of results organized by rules (dependent on the following $mode settings):
 *       'reminders-due' mode - returns an array of reminders (action array elements plus a 'pid' and 'due_status')
 *       'reminders-all' mode - returns an array of reminders (action array elements plus a 'pid' and 'due_status')
 *       'report' mode        - returns an array of rows for the Clinical Quality Measures (CQM) report
 *   'plans' organize_mode:
 *     Returns similar to default, but organizes by the active plans
 * </pre>
 *
 * @param  integer      $provider      id of a selected provider. If blank, then will test entire clinic. If 'collate_outer' or 'collate_inner', then will test each provider in entire clinic; outer will nest plans  inside collated providers, while inner will nest the providers inside the plans (note inner and outer are only different if organize_mode is set to plans).
 * @param  string       $type          rule filter (active_alert,passive_alert,cqm,cqm_2011,cqm_2104,amc,amc_2011,amc_2014,patient_reminder). If blank then will test all rules.
 * @param  string/array $dateTarget    target date (format Y-m-d H:i:s). If blank then will test with current date as target. If an array, then is holding two dates ('dateBegin' and 'dateTarget').
 * @param  string       $mode          choose either 'report' or 'reminders-all' or 'reminders-due' (required)
 * @param  integer      $patient_id    pid of patient. If blank then will check all patients.
 * @param  string       $plan          test for specific plan only
 * @param  string       $organize_mode Way to organize the results (default, plans). See above for organization structure of the results.
 * @param  array        $options       can hold various option (for now, used to hold the manual number of labs for the AMC report)
 * @param  string       $pat_prov_rel  How to choose patients that are related to a chosen provider. 'primary' selects patients that the provider is set as primary provider. 'encounter' selectes patients that the provider has seen. This parameter is only applicable if the $provider parameter is set to a provider or collation setting.
 * @param  integer      $start         applicable patient to start at (when batching process)
 * @param  integer      $batchSize     number of patients to batch (when batching process)
 * @param  string       $user          If a user is set, then will only show rules that user has permission to see(only applicable for per patient and not when do reports).
 * @return array                       See above for organization structure of the results.
 */
function test_rules_clinic_collate($provider = '', $type = '', $dateTarget = '', $mode = '', $patient_id = '', $plan = '', $organize_mode = 'default', $options = array(), $pat_prov_rel = 'primary', $start = null, $batchSize = null, $user = '')
{
    $results = [];
    // If set the $provider to collate_outer (or collate_inner without plans organize mode),
    // then run through this function recursively and return results.
    if (($provider === "collate_outer") || ($provider === "collate_inner" && $organize_mode !== 'plans')) {
        // First, collect an array of all providers
        $query = "SELECT id, lname, fname, npi, federaltaxid FROM users WHERE authorized = 1 ORDER BY lname, fname";
        $ures = sqlStatementCdrEngine($query);
        // Second, run through each provider recursively
        while ($urow = sqlFetchArray($ures)) {
            $newResults = test_rules_clinic($urow['id'], $type, $dateTarget, $mode, $patient_id, $plan, $organize_mode, $options, $pat_prov_rel, $start, $batchSize, $user);
            if (!empty($newResults)) {
                $provider_item['is_provider'] = true;
                $provider_item['prov_lname'] = $urow['lname'];
                $provider_item['prov_fname'] = $urow['fname'];
                $provider_item['npi'] = $urow['npi'];
                $provider_item['federaltaxid'] = $urow['federaltaxid'];
                $results[] = $provider_item;
                $results = array_merge($results, $newResults);
            }
        }

        // done, so now can return results
        return $results;
    }

    // If set organize-mode to plans, then collects active plans and run through this
    // function recursively and return results.
    if ($organize_mode === "plans") {
        // First, collect active plans
        $plans_resolve = resolve_plans_sql($plan, $patient_id);
        // Second, run through function recursively
        foreach ($plans_resolve as $plan_item) {
            //  (if collate_inner, then nest a collation of providers within each plan)
            if ($provider === "collate_inner") {
                // First, collect an array of all providers
                $query = "SELECT id, lname, fname, npi, federaltaxid FROM users WHERE authorized = 1 ORDER BY lname, fname";
                $ures = sqlStatementCdrEngine($query);
                // Second, run through each provider recursively
                $provider_results = array();
                while ($urow = sqlFetchArray($ures)) {
                    $newResults = test_rules_clinic($urow['id'], $type, $dateTarget, $mode, $patient_id, $plan_item['id'], 'default', $options, $pat_prov_rel, $start, $batchSize, $user);
                    if (!empty($newResults)) {
                        $provider_item['is_provider'] = true;
                        $provider_item['prov_lname'] = $urow['lname'];
                        $provider_item['prov_fname'] = $urow['fname'];
                        $provider_item['npi'] = $urow['npi'];
                        $provider_item['federaltaxid'] = $urow['federaltaxid'];
                        $provider_results[] = $provider_item;
                        $provider_results = array_merge($provider_results, $newResults);
                    }
                }

                if (!empty($provider_results)) {
                    $plan_item['is_plan'] = true;
                    $results[] = $plan_item;
                    $results = array_merge($results, $provider_results);
                }
            } else {
                // (not collate_inner, so do not nest providers within each plan)
                $newResults = test_rules_clinic($provider, $type, $dateTarget, $mode, $patient_id, $plan_item['id'], 'default', $options, $pat_prov_rel, $start, $batchSize, $user);
                if (!empty($newResults)) {
                    $plan_item['is_plan'] = true;
                    $results[] = $plan_item;
                    $results = array_merge($results, $newResults);
                }
            }
        }

        // done, so now can return results
        return $results;
    }
}

/**
 * Runs the AMC or CQM calculations for a given rule.
 * @param $rowRule The rule we are going to run calculcations against
 * @param $patientData The list of patient pids we are going to calculate our rules on
 * @param $dateArray The start and end date of the rule for AMC calculation purposes
 * @param $dateTarget The end date of the rule for CQM purposes
 * @param $options Any options needed for AMC/CQM processing
 * @return array The list of rule calculations that have been generated
 * @throws Exception If a rule is invalid or not found
 */
function test_rules_clinic_cqm_amc_rule($rowRule, $patientData, $dateArray, $dateTarget, $options, $provider, $pat_prov_rel)
{
    // we need to give more context to some of our rules
    $ruleOptions = $options;
    $ruleOptions['pat_prov_rel'] = $pat_prov_rel;
    if (is_numeric($provider)) {
        $ruleOptions['provider_id'] = $provider;
    }
    require_once(dirname(__FILE__) . "/classes/rulesets/ReportManager.php");
    $manager = new ReportManager();
    if ($rowRule['amc_flag']) {
        // Send array of dates ('dateBegin' and 'dateTarget')
        $tempResults = $manager->runReport($rowRule, $patientData, $dateArray, $ruleOptions);
    } else {
        // Send target date
        $tempResults = $manager->runReport($rowRule, $patientData, $dateTarget);
    }
    return $tempResults;
}

/**
 * Process clinic rules.
 *
 * Test the clinic rules of entire clinic and create a report or patient reminders (can also test
 * on one patient or patients of one provider). The structure of the returned results is dependent on the
 * $organize_mode and $mode parameters.
 * <pre>The results are dependent on the $organize_mode parameter settings
 *   'default' organize_mode:
 *     Returns a two-dimensional array of results organized by rules (dependent on the following $mode settings):
 *       'reminders-due' mode - returns an array of reminders (action array elements plus a 'pid' and 'due_status')
 *       'reminders-all' mode - returns an array of reminders (action array elements plus a 'pid' and 'due_status')
 *       'report' mode        - returns an array of rows for the Clinical Quality Measures (CQM) report
 *   'plans' organize_mode:
 *     Returns similar to default, but organizes by the active plans
 * </pre>
 *
 * @param  integer      $provider      id of a selected provider. If blank, then will test entire clinic. If 'collate_outer' or 'collate_inner', then will test each provider in entire clinic; outer will nest plans  inside collated providers, while inner will nest the providers inside the plans (note inner and outer are only different if organize_mode is set to plans).
 * @param  string       $type          rule filter (active_alert,passive_alert,cqm,cqm_2011,cqm_2104,amc,amc_2011,amc_2014,patient_reminder). If blank then will test all rules.
 * @param  string/array $dateTarget    target date (format Y-m-d H:i:s). If blank then will test with current date as target. If an array, then is holding two dates ('dateBegin' and 'dateTarget').
 * @param  string       $mode          choose either 'report' or 'reminders-all' or 'reminders-due' (required)
 * @param  integer      $patient_id    pid of patient. If blank then will check all patients.
 * @param  string       $plan          test for specific plan only
 * @param  string       $organize_mode Way to organize the results (default, plans). See above for organization structure of the results.
 * @param  array        $options       can hold various option (for now, used to hold the manual number of labs for the AMC report)
 * @param  string       $pat_prov_rel  How to choose patients that are related to a chosen provider. 'primary' selects patients that the provider is set as primary provider. 'encounter' selectes patients that the provider has seen. This parameter is only applicable if the $provider parameter is set to a provider or collation setting.
 * @param  integer      $start         applicable patient to start at (when batching process)
 * @param  integer      $batchSize     number of patients to batch (when batching process)
 * @param  string       $user          If a user is set, then will only show rules that user has permission to see(only applicable for per patient and not when do reports).
 * @return array                       See above for organization structure of the results.
 */
function test_rules_clinic($provider = '', $type = '', $dateTarget = '', $mode = '', $patient_id = '', $plan = '', $organize_mode = 'default', $options = array(), $pat_prov_rel = 'primary', $start = null, $batchSize = null, $user = '')
{

  // If dateTarget is an array, then organize them.
    if (is_array($dateTarget)) {
        $dateArray = $dateTarget;
        $dateTarget = $dateTarget['dateTarget'];
    } else {
        $dateArray = [];
    }

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Prepare the results array
    $results = array();

    // we have a special mechanism for collation or plans organize method
    if ($provider === "collate_outer" || $organize_mode === 'plans') {
        return test_rules_clinic_collate($provider, $type, $dateTarget, $mode, $patient_id, $plan, $organize_mode, $options, $pat_prov_rel, $start, $batchSize, $user);
    }

    if ($provider === "group_calculation") {
        return test_rules_clinic_group_calculation($type, $dateArray, $mode, $patient_id, $plan, $organize_mode, $options, $pat_prov_rel, $start, $batchSize, $user);
    }

  // Collect applicable patient pids
    $patientData = buildPatientArray($patient_id, $provider, $pat_prov_rel, $start, $batchSize, false, $options['billing_facility_id'] ?? null);

  // Go through each patient(s)
  //
  //  If in report mode, then tabulate for each rule:
  //    Total Patients
  //    Patients that pass the filter
  //    Patients that pass the target
  //  If in reminders mode, then create reminders for each rule:
  //    Reminder that action is due soon
  //    Reminder that action is due
  //    Reminder that action is post-due

  //Collect applicable rules
  // Note that due to a limitation in the this function, the patient_id is explicitly
  //  for grouping items when not being done in real-time or for official reporting.
  //  So for cases such as patient reminders on a clinic scale, the calling function
  //  will actually need rather than pass in a explicit patient_id for each patient in
  //  a separate call to this function.
    $rules = resolve_rules_sql($type, $patient_id, false, $plan, $user);

    foreach ($rules as $rowRule) {
        // If using cqm or amc type, then use the hard-coded rules set.
        // Note these rules are only used in report mode.
        if ($rowRule['cqm_flag'] || $rowRule['amc_flag']) {
            $tempResults = test_rules_clinic_cqm_amc_rule($rowRule, $patientData, $dateArray, $dateTarget, $options, $provider, $pat_prov_rel);
            $results = array_merge($results, $tempResults);
            // Go on to the next rule
            continue;
        }

        // ALL OF THE BELOW RULES ARE FOR active_alert, passive_alert,patient_reminder
        // If in reminder mode then need to collect the measurement dates
        //  from rule_reminder table
        $target_dates = array();
        if ($mode != "report") {
            // Calculate the dates to check for
            if ($type == "patient_reminder") {
                $reminder_interval_type = "patient_reminder";
            } else { // $type == "passive_alert" or $type == "active_alert"
                $reminder_interval_type = "clinical_reminder";
            }

            $target_dates = calculate_reminder_dates($rowRule['id'], $dateTarget, $reminder_interval_type);
        } else { // $mode == "report"
            // Only use the target date in the report
            $target_dates[0] = $dateTarget;
        }

        //Reset the counters
        $total_patients = 0;
        $pass_filter = 0;
        $exclude_filter = 0;
        $pass_target = 0;

        // Find the number of target groups
        $targetGroups = returnTargetGroups($rowRule['id']);

        if ((count($targetGroups) == 1) || ($mode == "report")) {
            // If report itemization is turned on, then iterate the rule id iterator
            if (!empty($GLOBALS['report_itemizing_temp_flag_and_id'])) {
                $GLOBALS['report_itemized_test_id_iterator']++;
            }

            //skip this section if not report and more than one target group
            foreach ($patientData as $rowPatient) {
                // First, deal with deceased patients
                //  (for now will simply skip the patient)
                // If want to support rules for deceased patients then will need to migrate this below
                // in target_dates foreach(guessing won't ever need to do this, though).
                // Note using the dateTarget rather than dateFocus
                if (is_patient_deceased($rowPatient['pid'], $dateTarget)) {
                    continue;
                }

                // Count the total patients
                $total_patients++;

                $dateCounter = 1; // for reminder mode to keep track of which date checking
                // If report itemization is turned on, reset flag.
                if (!empty($GLOBALS['report_itemizing_temp_flag_and_id'])) {
                    $temp_track_pass = 1;
                }

                foreach ($target_dates as $dateFocus) {
                    //Skip if date is set to SKIP
                    if ($dateFocus == "SKIP") {
                        $dateCounter++;
                        continue;
                    }

                    //Set date counter and reminder token (applicable for reminders only)
                    if ($dateCounter == 1) {
                        $reminder_due = "soon_due";
                    } elseif ($dateCounter == 2) {
                        $reminder_due = "due";
                    } else { // $dateCounter == 3
                        $reminder_due = "past_due";
                    }

                    // Check if pass filter
                    $passFilter = test_filter($rowPatient['pid'], $rowRule['id'], $dateFocus);
                    if ($passFilter === "EXCLUDED") {
                        // increment EXCLUDED and pass_filter counters
                        //  and set as FALSE for reminder functionality.
                        $pass_filter++;
                        $exclude_filter++;
                        $passFilter = false;
                    }

                    if ($passFilter) {
                        // increment pass filter counter
                        $pass_filter++;
                        // If report itemization is turned on, trigger flag.
                        if (!empty($GLOBALS['report_itemizing_temp_flag_and_id'])) {
                            $temp_track_pass = 0;
                        }
                    } else {
                        $dateCounter++;
                        continue;
                    }

                    // Check if pass target
                    $passTarget = test_targets($rowPatient['pid'], $rowRule['id'], '', $dateFocus);
                    if ($passTarget) {
                        // increment pass target counter
                        $pass_target++;
                        // If report itemization is turned on, then record the "passed" item and set the flag
                        if (!empty($GLOBALS['report_itemizing_temp_flag_and_id'])) {
                            insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 1, $rowPatient['pid']);
                            $temp_track_pass = 1;
                        }

                        // send to reminder results
                        if ($mode == "reminders-all") {
                            // place the completed actions into the reminder return array
                            $actionArray = resolve_action_sql($rowRule['id'], '1');
                            foreach ($actionArray as $action) {
                                $action_plus = $action;
                                $action_plus['due_status'] = "not_due";
                                $action_plus['pid'] = $rowPatient['pid'];
                                $action_plus['rule_id'] = $rowRule['id'];
                                $results = reminder_results_integrate($results, $action_plus);
                            }
                        }

                        break;
                    } else {
                        // send to reminder results
                        if ($mode != "report") {
                            // place the uncompleted actions into the reminder return array
                            $actionArray = resolve_action_sql($rowRule['id'], '1');
                            foreach ($actionArray as $action) {
                                $action_plus = $action;
                                $action_plus['due_status'] = $reminder_due;
                                $action_plus['pid'] = $rowPatient['pid'];
                                $action_plus['rule_id'] = $rowRule['id'];
                                $results = reminder_results_integrate($results, $action_plus);
                            }
                        }
                    }

                    $dateCounter++;
                }

                // If report itemization is turned on, then record the "failed" item if it did not pass
                if (!empty($GLOBALS['report_itemizing_temp_flag_and_id']) && !($temp_track_pass)) {
                    insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 0, $rowPatient['pid']);
                }
            }
        }

        // Calculate and save the data for the rule
        $percentage = calculate_percentage($pass_filter, $exclude_filter, $pass_target);
        if ($mode == "report") {
            $newRow = array('is_main' => true,'total_patients' => $total_patients,'excluded' => $exclude_filter,'pass_filter' => $pass_filter,'pass_target' => $pass_target,'percentage' => $percentage);
            $newRow = array_merge($newRow, $rowRule);

            // If itemization is turned on, then record the itemized_test_id
            if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                $newRow = array_merge($newRow, array('itemized_test_id' => $GLOBALS['report_itemized_test_id_iterator']));
            }

            $results[] = $newRow;
        }

        // Now run through the target groups if more than one
        if (count($targetGroups) > 1) {
            foreach ($targetGroups as $i) {
                // If report itemization is turned on, then iterate the rule id iterator
                if (!empty($GLOBALS['report_itemizing_temp_flag_and_id'])) {
                    $GLOBALS['report_itemized_test_id_iterator']++;
                }

                //Reset the target counter
                $pass_target = 0;

                foreach ($patientData as $rowPatient) {
                    // First, deal with deceased patients
                    //  (for now will simply skip the patient)
                    // If want to support rules for deceased patients then will need to migrate this below
                    // in target_dates foreach(guessing won't ever need to do this, though).
                    // Note using the dateTarget rather than dateFocus
                    if (is_patient_deceased($rowPatient['pid'], $dateTarget)) {
                        continue;
                    }

                    $dateCounter = 1; // for reminder mode to keep track of which date checking
                    // If report itemization is turned on, reset flag.
                    if (!empty($GLOBALS['report_itemizing_temp_flag_and_id'])) {
                        $temp_track_pass = 1;
                    }

                    foreach ($target_dates as $dateFocus) {
                        //Skip if date is set to SKIP
                        if ($dateFocus == "SKIP") {
                            $dateCounter++;
                            continue;
                        }

                        //Set date counter and reminder token (applicable for reminders only)
                        if ($dateCounter == 1) {
                            $reminder_due = "soon_due";
                        } elseif ($dateCounter == 2) {
                            $reminder_due = "due";
                        } else { // $dateCounter == 3
                            $reminder_due = "past_due";
                        }

                        // Check if pass filter
                        $passFilter = test_filter($rowPatient['pid'], $rowRule['id'], $dateFocus);
                        if ($passFilter === "EXCLUDED") {
                            $passFilter = false;
                        }

                        if (!$passFilter) {
                            $dateCounter++;
                            continue;
                        } else {
                            // If report itemization is turned on, trigger flag.
                            if (!empty($GLOBALS['report_itemizing_temp_flag_and_id'])) {
                                $temp_track_pass = 0;
                            }
                        }

                        //Check if pass target
                        $passTarget = test_targets($rowPatient['pid'], $rowRule['id'], $i, $dateFocus);
                        if ($passTarget) {
                            // increment pass target counter
                            $pass_target++;
                            // If report itemization is turned on, then record the "passed" item and set the flag
                            if ($GLOBALS['report_itemizing_temp_flag_and_id'] ?? null) {
                                insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 1, $rowPatient['pid']);
                                $temp_track_pass = 1;
                            }

                            // send to reminder results
                            if ($mode == "reminders-all") {
                                // place the completed actions into the reminder return array
                                $actionArray = resolve_action_sql($rowRule['id'], $i);
                                foreach ($actionArray as $action) {
                                    $action_plus = $action;
                                    $action_plus['due_status'] = "not_due";
                                    $action_plus['pid'] = $rowPatient['pid'];
                                    $action_plus['rule_id'] = $rowRule['id'];
                                    $results = reminder_results_integrate($results, $action_plus);
                                }
                            }

                            break;
                        } else {
                            // send to reminder results
                            if ($mode != "report") {
                                // place the actions into the reminder return array
                                $actionArray = resolve_action_sql($rowRule['id'], $i);
                                foreach ($actionArray as $action) {
                                    $action_plus = $action;
                                    $action_plus['due_status'] = $reminder_due;
                                    $action_plus['pid'] = $rowPatient['pid'];
                                    $action_plus['rule_id'] = $rowRule['id'];
                                    $results = reminder_results_integrate($results, $action_plus);
                                }
                            }
                        }

                        $dateCounter++;
                    }

                    // If report itemization is turned on, then record the "failed" item if it did not pass
                    if (!empty($GLOBALS['report_itemizing_temp_flag_and_id']) && !($temp_track_pass)) {
                        insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 0, $rowPatient['pid']);
                    }
                }

                // Calculate and save the data for the rule
                $percentage = calculate_percentage($pass_filter, $exclude_filter, $pass_target);

                // Collect action for title (just use the first one, if more than one)
                $actionArray = resolve_action_sql($rowRule['id'], $i);
                $action = $actionArray[0];
                if ($mode == "report") {
                    $newRow = array('is_sub' => true,'action_category' => $action['category'],'action_item' => $action['item'],'total_patients' => '','excluded' => '','pass_filter' => '','pass_target' => $pass_target,'percentage' => $percentage);

                  // If itemization is turned on, then record the itemized_test_id
                    if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                        $newRow = array_merge($newRow, array('itemized_test_id' => $GLOBALS['report_itemized_test_id_iterator']));
                    }

                    $results[] = $newRow;
                }
            }
        }
    }

  // Return the data
    return $results;
}

/**
 * Process patient array that is to be tested.
 *
 * @param  integer       $provider      id of a selected provider. If blank, then will test entire clinic.
 * @param  integer       $patient_id    pid of patient. If blank then will check all patients.
 * @param  string        $pat_prov_rel  How to choose patients that are related to a chosen provider. 'primary' selects patients that the provider is set as primary provider. 'encounter' selectes patients that the provider has seen. This parameter is only applicable if the $provider parameter is set to a provider or collation setting.
 * @param  integer       $start         applicable patient to start at (when batching process)
 * @param  integer       $batchSize     number of patients to batch (when batching process)
 * @param  boolean       $onlyCount     If true, then will just return the total number of applicable records (ignores batching parameters)
 * @param  integer       $billing_facility id of the billing facility to constrain patient relationships to
 * @return array/integer                Array of patient pid values or number total pertinent patients (if $onlyCount is TRUE)
 */
function buildPatientArray($patient_id = '', $provider = '', $pat_prov_rel = 'primary', $start = null, $batchSize = null, $onlyCount = false, $billing_facility = null)
{
    (new SystemLogger())->debug(
        "buildPatientArray()",
        ['patient_id' => $patient_id, 'provider' => $provider, 'pat_prov_rel' => $pat_prov_rel, 'start' => $start
        ,
        'batchSize' => $batchSize,
        'onlyCount' => $onlyCount,
        'billing_facility' => $billing_facility]
    );

    $patientData = [];
    if (!empty($patient_id)) {
        // only look at the selected patient
        if ($onlyCount) {
            $patientNumber = 1;
        } else {
            $patientData[0]['pid'] = $patient_id;
        }
    } else {
        if (empty($provider)) {
            // Look at entire practice
            if ($start == null || $batchSize == null || $onlyCount) {
                $rez = sqlStatementCdrEngine("SELECT `pid` FROM `patient_data` ORDER BY `pid`");
                if ($onlyCount) {
                    $patientNumber = sqlNumRows($rez);
                }
            } else {
                // batching
                $rez = sqlStatementCdrEngine("SELECT `pid` FROM `patient_data` ORDER BY `pid` LIMIT ?,?", array(($start - 1),$batchSize));
            }
        } else {
            // Look at an individual physician
            if ($provider == 'group_calculation' && $pat_prov_rel == 'encounter') {
                return buildPatientArrayEncounterBillingFacility($start, $batchSize, $onlyCount, $billing_facility);
            } else if ($provider == 'group_calculation' && $pat_prov_rel == 'primary') {
                return buildPatientArrayPrimaryProviderBillingFacility($start, $batchSize, $onlyCount, $billing_facility);
            } else if ($pat_prov_rel == 'encounter_billing_facility' && is_numeric($provider)) {
                return buildPatientArrayEncounterBillingFacility($start, $batchSize, $onlyCount, $billing_facility, $provider);
            } else if ($pat_prov_rel == 'primary_billing_facility' && is_numeric($provider)) {
                return buildPatientArrayPrimaryProviderBillingFacility($start, $batchSize, $onlyCount, $billing_facility, $provider);
            } else if ($pat_prov_rel == 'encounter') {
                // Choose patients that are related to specific physician by an encounter (OR the provider was a referral originator)
                $sql = "select DISTINCT `pid` FROM `form_encounter` WHERE `provider_id` =? OR `supervisor_id` = ? "
                    . " UNION select DISTINCT `transactions`.`pid` FROM transactions "
                    . "   INNER JOIN lbt_data ON lbt_data.form_id = transactions.id AND lbt_data.field_id = 'refer_from' AND lbt_data.field_value = ? "
                    . " ORDER BY `pid`";
                if ($start == null || $batchSize == null || $onlyCount) {
                    // we need to include referrals here as a referral can occur w/o there being an encounter

                    $rez = sqlStatementCdrEngine($sql, array($provider, $provider, $provider));
                    if ($onlyCount) {
                        $patientNumber = sqlNumRows($rez);
                    }
                } else {
                    //batching
                    $sql .= " LIMIT " . intval($start) - 1 . "," . intval($batchSize);
                    $rez = sqlStatementCdrEngine($sql, array($provider, $provider, $provider));
                }
            } else {  //$pat_prov_rel == 'primary'
                // Choose patients that are assigned to the specific physician (primary physician in patient demographics)
                if ($start == null || $batchSize == null || $onlyCount) {
                    $rez = sqlStatementCdrEngine("SELECT `pid` FROM `patient_data` " .
                              "WHERE `providerID`=? ORDER BY `pid`", array($provider));
                    if ($onlyCount) {
                              $patientNumber = sqlNumRows($rez);
                    }
                } else {
                    $rez = sqlStatementCdrEngine("SELECT `pid` FROM `patient_data` " .
                              "WHERE `providerID`=? ORDER BY `pid` LIMIT ?,?", array($provider,($start - 1),$batchSize));
                }
            }
        }

        // convert the sql query results into an array if returning the array
        if (!$onlyCount) {
            for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
                $patientData[$iter] = $row;
            }
        }
    }

    if ($onlyCount) {
        // return the number of applicable patients
        return $patientNumber;
    } else {
        // return array of patient pids
        return $patientData;
    }
}

/**
 * Process patient array that is to be tested. This uses the patient relationship context of encounters linked to billing facilities
 *
 * @param  integer       $start         applicable patient to start at (when batching process)
 * @param  integer       $batchSize     number of patients to batch (when batching process)
 * @param  boolean       $onlyCount     If true, then will just return the total number of applicable records (ignores batching parameters)
 * @param  integer       $billing_facility id of the billing facility to constrain patient relationships to, if NULL it uses ALL billing facilities
 * @param  integer|null  $provider_id   The id of a provider to restrict patient data to if we have one
 * @return array/integer                Array of patient pid values or number total pertinent patients (if $onlyCount is TRUE)
 */
function buildPatientArrayEncounterBillingFacility($start, $batchSize, $onlyCount, $billing_facility, $provider_id = null)
{
    $sql = "SELECT DISTINCT `pid` FROM `form_encounter` ";
    $binds = [];

    $billing_facility = intval($billing_facility); // make sure its an integer
    if (empty($billing_facility)) {
        $sql .= "WHERE `form_encounter`.`billing_facility` IS NOT NULL ";
    } else {
        //            " WHERE (`provider_id`=? OR `supervisor_id`=?) AND `billing_facility` = ? ORDER BY `pid`", array($provider,$provider, $billing_facility));
        $sql .= " WHERE `form_encounter`.`billing_facility` = ? ";
        $binds[] = $billing_facility;
    }
    if (!empty($provider_id)) {
        $provider_id = intval($provider_id); // make sure we convert this to a pure int
        $sql .= " AND (`form_encounter`.`provider_id` = ? OR `form_encounter`.`supervisor_id` = ?)";
        $binds[] = $provider_id;
        $binds[] = $provider_id;
    }
    $sql .= "UNION SELECT DISTINCT `transactions`.`pid` FROM `transactions` ";
    $sql .= "INNER JOIN `lbt_data` ON `lbt_data`.`form_id` = `transactions`.`id` AND `lbt_data`.`field_id` = 'billing_facility_id' ";
    $sql .= "INNER JOIN lbt_data lbt_data2 ON lbt_data.form_id = transactions.id AND lbt_data2.field_id = 'refer_from' ";
    if (!empty($billing_facility)) {
        $sql .= " WHERE `lbt_data`.`field_value` = ? ";
        $binds[] = $billing_facility;
    }

    if (!empty($provider_id)) {
        $sql .= " AND `lbt_data2`.`field_value` = ? ";
        $binds[] = $provider_id;
    }
    $sql .= "ORDER BY `pid`";
    if (!($start == null || $batchSize == null || $onlyCount)) {
        $sql .= "LIMIT " . (intval($start) - 1) . "," . intval($batchSize);
    }
    $rez = sqlStatementCdrEngine($sql, $binds);

    if ($onlyCount) {
        $patientNumber = sqlNumRows($rez);
        return $patientNumber;
    }

    $patientData = [];
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $patientData[$iter] = $row;
    }
    return $patientData;
}

/**
 * Process patient array that is to be tested. This uses the patient relationship context of the primary provider who is
 * linked to a billing facility
 *
 * @param  integer       $start         applicable patient to start at (when batching process)
 * @param  integer       $batchSize     number of patients to batch (when batching process)
 * @param  boolean       $onlyCount     If true, then will just return the total number of applicable records (ignores batching parameters)
 * @param  integer       $billing_facility id of the billing facility to constrain patient relationships to, if NULL it uses ALL billing facilities
 * @param  integer|null  $provider_id   The id of a provider to restrict patient data to if we have one
 * @return array/integer                Array of patient pid values or number total pertinent patients (if $onlyCount is TRUE)
 */
function buildPatientArrayPrimaryProviderBillingFacility($start, $batchSize, $onlyCount, $billing_facility, $provider_id = null)
{
    $sql = "SELECT DISTINCT `pid` FROM `patient_data` JOIN users providers ON `patient_data`.`providerID`=providers.id ";
    $binds = [];

    $billing_facility = intval($billing_facility); // make sure its an integer
    if (empty($billing_facility)) {
        $sql .= "WHERE (`providers`.`billing_facility_id` IS NOT NULL)";
    } else {
        $sql .= " WHERE (`providers`.`billing_facility_id`=?)";
        $binds[] = $billing_facility;
    }
    if (!empty($provider_id)) {
        $sql .= " AND `providers.id`=?";
        $binds[] = $provider_id;
    }
    $sql .= "UNION SELECT DISTINCT `transactions`.`pid` FROM `transactions` ";
    $sql .= "INNER JOIN lbt_data ON lbt_data.form_id = transactions.id AND lbt_data.field_id = 'refer_from' ";
    $sql .= "INNER JOIN `users` providers ON `lbt_data`.`form_id` = `transactions`.`id` AND `lbt_data`.`form_value` = providers.id ";
    if (empty($billing_facility)) {
        $sql .= "WHERE (`providers`.`billing_facility_id` IS NOT NULL)";
    } else {
        $sql .= " WHERE (`providers`.`billing_facility_id`=?)";
        $binds[] = $billing_facility;
    }

    if (!empty($provider_id)) {
        $sql .= " AND `providers.id`=?";
        $binds[] = $provider_id;
    }

    $sql .= "ORDER BY `pid`";
    if (!($start == null || $batchSize == null || $onlyCount)) {
        $sql .= "LIMIT " . (intval($start) - 1) . "," . intval($batchSize);
    }
    $rez = sqlStatementCdrEngine($sql, $binds);

    if ($onlyCount) {
        $patientNumber = sqlNumRows($rez);
        return $patientNumber;
    }

    $patientData = [];
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $patientData[$iter] = $row;
    }
    return $patientData;
}

/**
 * Test filter of a selected rule on a selected patient
 *
 * @param  integer        $patient_id  pid of selected patient.
 * @param  string         $rule        id(string) of selected rule
 * @param  string         $dateTarget  target date (format Y-m-d H:i:s). If blank then will test with current date as target.
 * @return boolean/string              if pass filter then TRUE; if excluded then 'EXCLUDED'; if not pass filter then FALSE
 */
function test_filter($patient_id, $rule, $dateTarget)
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect patient information
    $patientData = getPatientData($patient_id, "sex, DATE_FORMAT(DOB,'%Y %m %d') as DOB_TS");

  //
  // ----------------- INCLUSIONS -----------------
  //

  // -------- Age Filter (inclusion) ------------
  // Calculate patient age in years and months
    $patientAgeYears = convertDobtoAgeYearDecimal($patientData['DOB_TS'], $dateTarget);
    $patientAgeMonths = convertDobtoAgeMonthDecimal($patientData['DOB_TS'], $dateTarget);

  // Min age (year) Filter (assume that there in not more than one of each)
    $filter = resolve_filter_sql($rule, 'filt_age_min');
    if (!empty($filter)) {
        $row = $filter[0];
        if ($row ['method_detail'] == "year") {
            if ($row['value'] && ($row['value'] > $patientAgeYears)) {
                return false;
            }
        }

        if ($row ['method_detail'] == "month") {
            if ($row['value'] && ($row['value'] > $patientAgeMonths)) {
                return false;
            }
        }
    }

  // Max age (year) Filter (assume that there in not more than one of each)
    $filter = resolve_filter_sql($rule, 'filt_age_max');
    if (!empty($filter)) {
        $row = $filter[0];
        if ($row ['method_detail'] == "year") {
            if ($row['value'] && ($row['value'] < $patientAgeYears)) {
                return false;
            }
        }

        if ($row ['method_detail'] == "month") {
            if ($row['value'] && ($row['value'] < $patientAgeMonths)) {
                return false;
            }
        }
    }

  // -------- Gender Filter (inclusion) ---------
  // Gender Filter (assume that there in not more than one of each)
    $filter = resolve_filter_sql($rule, 'filt_sex');
    if (!empty($filter)) {
        $row = $filter[0];
        if ($row['value'] && ($row['value'] != $patientData['sex'])) {
            return false;
        }
    }

  // -------- Database Filter (inclusion) ------
  // Database Filter
    $filter = resolve_filter_sql($rule, 'filt_database');
    if ((!empty($filter)) && !database_check($patient_id, $filter, '', $dateTarget)) {
        return false;
    }

  // -------- Lists Filter (inclusion) ----
  // Set up lists filter, which is fully customizable and currently includes diagnoses, meds,
  //   surgeries and allergies.
    $filter = resolve_filter_sql($rule, 'filt_lists');
    if ((!empty($filter)) && !lists_check($patient_id, $filter, $dateTarget)) {
        return false;
    }

  // -------- Procedure (labs,imaging,test,procedures,etc) Filter (inlcusion) ----
  // Procedure Target (includes) (may need to include an interval in the future)
    $filter = resolve_filter_sql($rule, 'filt_proc');
    if ((!empty($filter)) && !procedure_check($patient_id, $filter, '', $dateTarget)) {
        return false;
    }

  //
  // ----------------- EXCLUSIONS -----------------
  //

  // -------- Lists Filter (EXCLUSION) ----
  // Set up lists EXCLUSION filter, which is fully customizable and currently includes diagnoses, meds,
  //   surgeries and allergies.
    $filter = resolve_filter_sql($rule, 'filt_lists', 0);
    if ((!empty($filter)) && lists_check($patient_id, $filter, $dateTarget)) {
        return "EXCLUDED";
    }

  // Passed all filters, so return true.
    return true;
}

/**
 * Return an array containing existing group ids for a rule
 *
 * @param  string  $rule  id(string) of rule
 * @return array          listing of group ids
 */
function returnTargetGroups($rule)
{

    $sql = sqlStatementCdrEngine("SELECT DISTINCT `group_id` FROM `rule_target` " .
    "WHERE `id`=?", array($rule));

    $groups = array();
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $groups[] = $row['group_id'];
    }

    return $groups;
}

/**
 * Test targets of a selected rule on a selected patient
 *
 * @param  integer  $patient_id  pid of selected patient.
 * @param  string   $rule        id(string) of selected rule (if blank, then will ignore grouping)
 * @param  integer  $group_id    group id of target group
 * @param  string   $dateTarget  target date (format Y-m-d H:i:s).
 * @return boolean               if target passes then true, otherwise false
 */
function test_targets($patient_id, $rule, string $group_id = null, $dateTarget = null)
{

  // -------- Interval Target ----
    $interval = resolve_target_sql($rule, $group_id, 'target_interval');

  // -------- Database Target ----
  // Database Target (includes)
    $target = resolve_target_sql($rule, $group_id, 'target_database');
    if ((!empty($target)) && !database_check($patient_id, $target, $interval, $dateTarget)) {
        return false;
    }

  // -------- Procedure (labs,imaging,test,procedures,etc) Target ----
  // Procedure Target (includes)
    $target = resolve_target_sql($rule, $group_id, 'target_proc');
    if ((!empty($target)) && !procedure_check($patient_id, $target, $interval, $dateTarget)) {
        return false;
    }

  // -------- Appointment Target ----
  // Appointment Target (includes) (Specialized functionality for appointment reminders)
    $target = resolve_target_sql($rule, $group_id, 'target_appt');

    return !((!empty($target)) && appointment_check($patient_id, $dateTarget));
}

/**
 * Function to return active plans
 *
 * @param  string   $type             plan type filter (normal or cqm or blank)
 * @param  integer  $patient_id       pid of selected patient. (if custom plan does not exist then will use the default plan)
 * @param  boolean  $configurableOnly true if only want the configurable (per patient) plans (ie. ignore cqm plans)
 * @return array                      active plans
 */
function resolve_plans_sql($type = '', $patient_id = '0', $configurableOnly = false)
{

    if ($configurableOnly) {
        // Collect all default, configurable (per patient) plans into an array
        //   (ie. ignore the cqm rules)
        $sql = sqlStatementCdrEngine("SELECT * FROM `clinical_plans` WHERE `pid`=0 AND `cqm_flag` !=1 ORDER BY `id`");
    } else {
        // Collect all default plans into an array
        $sql = sqlStatementCdrEngine("SELECT * FROM `clinical_plans` WHERE `pid`=0 ORDER BY `id`");
    }

    $returnArray = array();
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $returnArray[] = $row;
    }

  // Now collect the pertinent plans
    $newReturnArray = array();

  // Need to select rules (use custom if exist)
    foreach ($returnArray as $plan) {
        $customPlan = sqlQueryCdrEngine("SELECT * FROM `clinical_plans` WHERE `id`=? AND `pid`=?", array($plan['id'],$patient_id));

        // Decide if use default vs custom plan (preference given to custom plan)
        if (!empty($customPlan)) {
            if ($type == "cqm") {
                // For CQM , do not use custom plans (these are to create standard clinic wide reports)
                $goPlan = $plan;
            } else {
                // merge the custom plan with the default plan
                $mergedPlan = array();
                foreach ($customPlan as $key => $value) {
                    if ($value == null && preg_match("/_flag$/", $key)) {
                        // use default setting
                        $mergedPlan[$key] = $plan[$key];
                    } else {
                        // use custom setting
                        $mergedPlan[$key] = $value;
                    }
                }

                $goPlan = $mergedPlan;
            }
        } else {
            $goPlan = $plan;
        }

        // Use the chosen plan if set
        if (!empty($type)) {
            if ($goPlan["{$type}_flag"] == 1) {
                // active, so use the plan
                $newReturnArray[] = $goPlan;
            }
        } else {
            if (
                $goPlan['normal_flag'] == 1 ||
                $goPlan['cqm_flag'] == 1
            ) {
                // active, so use the plan
                $newReturnArray[] = $goPlan;
            }
        }
    }

    $returnArray = $newReturnArray;

    return $returnArray;
}


/**
 * Function to return a specific plan
 *
 * @param  string   $plan        id(string) of plan
 * @param  integer  $patient_id  pid of selected patient. (if set to 0, then will return the default rule).
 * @return array                 a plan
 */
function collect_plan($plan, $patient_id = '0')
{

    return sqlQueryCdrEngine("SELECT * FROM `clinical_plans` WHERE `id`=? AND `pid`=?", array($plan,$patient_id));
}

/**
 * Function to set a specific plan activity for a specific patient
 *
 * @param  string   $plan        id(string) of plan
 * @param  string   $type        plan filter (normal,cqm)
 * @param  string   $setting     activity of plan (yes,no,default)
 * @param  integer  $patient_id  pid of selected patient.
 */
function set_plan_activity_patient($plan, $type, $setting, $patient_id)
{

  // Don't allow messing with the default plans here
    if ($patient_id == "0") {
        return;
    }

  // Convert setting
    if ($setting == "on") {
        $setting = 1;
    } elseif ($setting == "off") {
        $setting = 0;
    } else { // $setting == "default"
        $setting = null;
    }

  // Collect patient specific plan, if already exists.
    $query = "SELECT * FROM `clinical_plans` WHERE `id` = ? AND `pid` = ?";
    $patient_plan = sqlQueryCdrEngine($query, array($plan,$patient_id));

    if (empty($patient_plan)) {
        // Create a new patient specific plan with flags all set to default
        $query = "INSERT into `clinical_plans` (`id`, `pid`) VALUES (?,?)";
        sqlStatementCdrEngine($query, array($plan, $patient_id));
    }

  // Update patient specific row
    $query = "UPDATE `clinical_plans` SET `" . escape_sql_column_name($type . "_flag", array("clinical_plans")) . "`= ? WHERE id = ? AND pid = ?";
    sqlStatementCdrEngine($query, array($setting,$plan,$patient_id));
}

/**
 * Function to return active rules
 *
 * @param  string   $type             rule filter (active_alert,passive_alert,cqm,cqm_2011,cqm_2014,amc_2011,amc_2014,patient_reminder)
 * @param  integer  $patient_id       pid of selected patient. (if custom rule does not exist then will use the default rule)
 * @param  boolean  $configurableOnly true if only want the configurable (per patient) rules (ie. ignore cqm and amc rules)
 * @param  string   $plan             collect rules for specific plan
 * @param  string   $user             If a user is set, then will only show rules that user has permission to see
 * @return array                      rules
 */
function resolve_rules_sql($type = '', $patient_id = '0', $configurableOnly = false, $plan = '', $user = '')
{

    if ($configurableOnly) {
        // Collect all default, configurable (per patient) rules into an array
        //   (ie. ignore the cqm and amc rules)
        $sql = sqlStatementCdrEngine("SELECT * FROM `clinical_rules` WHERE `pid`=0 AND `cqm_flag` !=1 AND `amc_flag` !=1 ORDER BY `id`");
    } else {
        // Collect all default rules into an array
        $sql = sqlStatementCdrEngine("SELECT * FROM `clinical_rules` WHERE `pid`=0 ORDER BY `id`");
    }

    $returnArray = array();
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $returnArray[] = $row;
    }

  // Now filter rules for plan (if applicable)
    if (!empty($plan)) {
        $planReturnArray = array();
        foreach ($returnArray as $rule) {
            $standardRule = sqlQueryCdrEngine("SELECT * FROM `clinical_plans_rules` " .
                               "WHERE `plan_id`=? AND `rule_id`=?", array($plan,$rule['id']));
            if (!empty($standardRule)) {
                  $planReturnArray[] = $rule;
            }
        }

        $returnArray = $planReturnArray;
    }

  // Now collect the pertinent rules
    $newReturnArray = array();

  // Need to select rules (use custom if exist)
    foreach ($returnArray as $rule) {
        // If user is set, then check if user has access to the rule
        if (!empty($user)) {
            $access_control = explode(':', $rule['access_control']);
            if (!empty($access_control[0]) && !empty($access_control[1])) {
                // Section and ACO filters are not empty, so do the test for access.
                if (!AclMain::aclCheckCore($access_control[0], $access_control[1], $user)) {
                    // User does not have access to this rule, so skip the rule.
                    continue;
                }
            } else {
                // Section or ACO filters are empty, so use default patients:med aco
                if (!AclMain::aclCheckCore('patients', 'med', $user)) {
                    // User does not have access to this rule, so skip the rule.
                    continue;
                }
            }
        }

        $customRule = sqlQueryCdrEngine("SELECT * FROM `clinical_rules` WHERE `id`=? AND `pid`=?", array($rule['id'],$patient_id));

        // Decide if use default vs custom rule (preference given to custom rule)
        if (!empty($customRule)) {
            if ($type == "cqm" || CertificationReportTypes::isAMCReportType($type)) {
                // For CQM and AMC, do not use custom rules (these are to create standard clinic wide reports)
                $goRule = $rule;
            } else {
                // merge the custom rule with the default rule
                $mergedRule = array();
                foreach ($customRule as $key => $value) {
                    if ($value == null && preg_match("/_flag$/", $key)) {
                        // use default setting
                        $mergedRule[$key] = $rule[$key];
                    } else {
                        // use custom setting
                        $mergedRule[$key] = $value;
                    }
                }

                $goRule = $mergedRule;
            }
        } else {
            $goRule = $rule;
        }

        // Use the chosen rule if set
        if (!empty($type)) {
            if ($goRule["{$type}_flag"] == 1) {
                // active, so use the rule
                $newReturnArray[] = $goRule;
            }
        } else {
            // no filter, so return the rule
            $newReturnArray[] = $goRule;
        }
    }

    $returnArray = $newReturnArray;

    return $returnArray;
}

/**
 * Function to return a specific rule
 *
 * @param  string   $rule        id(string) of rule
 * @param  integer  $patient_id  pid of selected patient. (if set to 0, then will return the default rule).
 * @return array                 rule
 */
function collect_rule($rule, $patient_id = '0')
{

    return sqlQueryCdrEngine("SELECT * FROM `clinical_rules` WHERE `id`=? AND `pid`=?", array($rule,$patient_id));
}

/**
 * Function to set a specific rule activity for a specific patient
 *
 * @param  string   $rule        id(string) of rule
 * @param  string   $type        rule filter (active_alert,passive_alert,cqm,amc,patient_reminder)
 * @param  string   $setting     activity of rule (yes,no,default)
 * @param  integer  $patient_id  pid of selected patient.
 */
function set_rule_activity_patient($rule, $type, $setting, $patient_id)
{

  // Don't allow messing with the default rules here
    if ($patient_id == "0") {
        return;
    }

  // Convert setting
    if ($setting == "on") {
        $setting = 1;
    } elseif ($setting == "off") {
        $setting = 0;
    } else { // $setting == "default"
        $setting = null;
    }

  //Collect main rule to allow setting of the access_control
    $original_query = "SELECT * FROM `clinical_rules` WHERE `id` = ? AND `pid` = 0";
    $patient_rule_original = sqlQueryCdrEngine($original_query, array($rule));

  // Collect patient specific rule, if already exists.
    $query = "SELECT * FROM `clinical_rules` WHERE `id` = ? AND `pid` = ?";
    $patient_rule = sqlQueryCdrEngine($query, array($rule,$patient_id));

    if (empty($patient_rule)) {
        // Create a new patient specific rule with flags all set to default
        $query = "INSERT into `clinical_rules` (`id`, `pid`, `access_control`) VALUES (?,?,?)";
        sqlStatementCdrEngine($query, array($rule, $patient_id, $patient_rule_original['access_control']));
    }

  // Update patient specific row
    $query = "UPDATE `clinical_rules` SET `" . escape_sql_column_name($type . "_flag", ["clinical_rules"]) . "`= ?, `access_control` = ? WHERE id = ? AND pid = ?";
    sqlStatementCdrEngine($query, array($setting,$patient_rule_original['access_control'],$rule,$patient_id));
}

/**
 * Function to return applicable reminder dates (relative)
 *
 * @param  string  $rule             id(string) of selected rule
 * @param  string  $reminder_method  string label of filter type
 * @return array                      reminder features
 */
function resolve_reminder_sql($rule, $reminder_method)
{
    $sql = sqlStatementCdrEngine("SELECT `method_detail`, `value` FROM `rule_reminder` " .
    "WHERE `id`=? AND `method`=?", array($rule, $reminder_method));

    $returnArray = array();
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $returnArray[] = $row;
    }

    return $returnArray;
}

/**
 * Function to return applicable filters
 *
 * @param  string  $rule           id(string) of selected rule
 * @param  string  $filter_method  string label of filter type
 * @param  string  $include_flag   to allow selection for included or excluded filters
 * @return array                    filters
 */
function resolve_filter_sql($rule, $filter_method, $include_flag = 1)
{
    $sql = sqlStatementCdrEngine("SELECT `method_detail`, `value`, `required_flag` FROM `rule_filter` " .
    "WHERE `id`=? AND `method`=? AND `include_flag`=?", array($rule, $filter_method, $include_flag));

    $returnArray = array();
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $returnArray[] = $row;
    }

    return $returnArray;
}

/**
 * Function to return applicable targets
 *
 * @param  string   $rule           id(string) of selected rule
 * @param  integer  $group_id       group id of target group (if blank, then will ignore grouping)
 * @param  string   $target_method  string label of target type
 * @param  string   $include_flag   to allow selection for included or excluded targets
 * @return array                    targets
 */
function resolve_target_sql($rule, string $group_id = null, $target_method = '', $include_flag = 1)
{

    if ($group_id) {
        $sql = sqlStatementCdrEngine("SELECT `value`, `required_flag`, `interval` FROM `rule_target` " .
        "WHERE `id`=? AND `group_id`=? AND `method`=? AND `include_flag`=?", array($rule, $group_id, $target_method, $include_flag));
    } else {
        $sql = sqlStatementCdrEngine("SELECT `value`, `required_flag`, `interval` FROM `rule_target` " .
        "WHERE `id`=? AND `method`=? AND `include_flag`=?", array($rule, $target_method, $include_flag));
    }

    $returnArray = array();
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $returnArray[] = $row;
    }

    return $returnArray;
}

/**
 * Function to return applicable actions
 *
 * @param  string   $rule      id(string) of selected rule
 * @param  integer  $group_id  group id of target group (if blank, then will ignore grouping)
 * @return array               actions
 */
function resolve_action_sql($rule, $group_id = '')
{

    if ($group_id) {
        $sql = sqlStatementCdrEngine("SELECT b.category, b.item, b.clin_rem_link, b.reminder_message, b.custom_flag " .
        "FROM `rule_action` as a " .
        "JOIN `rule_action_item` as b " .
        "ON a.category = b.category AND a.item = b.item " .
        "WHERE a.id=? AND a.group_id=?", array($rule,$group_id));
    } else {
        $sql = sqlStatementCdrEngine("SELECT b.category, b.item, b.value, b.custom_flag " .
        "FROM `rule_action` as a " .
        "JOIN `rule_action_item` as b " .
        "ON a.category = b.category AND a.item = b.item " .
        "WHERE a.id=?", array($rule));
    }

    $returnArray = array();
    for ($iter = 0; $row = sqlFetchArray($sql); $iter++) {
        $returnArray[] = $row;
    }

    return $returnArray;
}

/**
 * Function to check database filters and targets
 *
 * @param  string  $patient_id  pid of selected patient.
 * @param  array   $filter      array containing filter/target elements
 * @param  array   $interval    array containing interval elements
 * @param  string  $dateTarget  target date(format Y-m-d H:i:s). blank is current date.
 * @return boolean              true if check passed, otherwise false
 */
function database_check($patient_id, $filter, $interval = '', $dateTarget = '')
{
    $isMatch = false; //matching flag

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Unpackage interval information
  // (Assume only one for now and only pertinent for targets)
    $intervalType = '';
    $intervalValue = '';
    if (!empty($interval)) {
        $intervalType = $interval[0]['value'];
        $intervalValue = $interval[0]['interval'];
    }

    $cond_loop = 0;
    foreach ($filter as $row) {
        // Row description
        //   [0]=>special modes
        $temp_df = explode("::", $row['value']);

        if ($temp_df[0] == "CUSTOM") {
            // Row description
            //   [0]=>special modes(CUSTOM) [1]=>category [2]=>item [3]=>complete? [4]=>number of hits comparison [5]=>number of hits
            if (exist_custom_item($patient_id, $temp_df[1], $temp_df[2], $temp_df[3], $temp_df[4], $temp_df[5], $intervalType, $intervalValue, $dateTarget)) {
                // Record the match
                $isMatch = true;
            } else {
               // If this is a required entry then return false
                if ($row['required_flag']) {
                    return false;
                }
            }
        } elseif ($temp_df[0] == "LIFESTYLE") {
            // Row description
            //   [0]=>special modes(LIFESTYLE) [1]=>column [2]=>status
            if (exist_lifestyle_item($patient_id, $temp_df[1], $temp_df[2], $dateTarget)) {
                // Record the match
                $isMatch = true;
            } else {
               // If this is a required entry then return false
                if ($row['required_flag']) {
                    return false;
                }
            }
        } else {
            // Default mode
            // Row description
            //   [0]=>special modes(BLANK) [1]=>table [2]=>column [3]=>value comparison [4]=>value [5]=>number of hits comparison [6]=>number of hits
            if (exist_database_item($patient_id, $temp_df[1], $temp_df[2], $temp_df[3], $temp_df[4], $temp_df[5], $temp_df[6], $intervalType, $intervalValue, $dateTarget)) {
                // Record the match
                if ($cond_loop > 0) { // For multiple condition check
                    $isMatch = $isMatch && 1;
                } else {
                    $isMatch = true;
                }
            } else {
               // If this is a required entry then return false
                if ($row['required_flag']) {
                    return false;
                }
            }
        }

        $cond_loop++;
    }

  // return results of check
    return $isMatch;
}

/**
 * Function to check procedure filters and targets
 *
 * @param  string  $patient_id  pid of selected patient.
 * @param  array   $filter      array containing filter/target elements
 * @param  array   $interval    array containing interval elements
 * @param  string  $dateTarget  target date(format Y-m-d H:i:s). blank is current date.
 * @return boolean              true if check passed, otherwise false
 */
function procedure_check($patient_id, $filter, $interval = '', $dateTarget = '')
{
    $isMatch = false; //matching flag

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Unpackage interval information
  // (Assume only one for now and only pertinent for targets)
    $intervalType = '';
    $intervalValue = '';
    if (!empty($interval)) {
        $intervalType = $interval[0]['value'];
        $intervalValue = $interval[0]['interval'];
    }

    foreach ($filter as $row) {
        // Row description
        // [0]=>title [1]=>code [2]=>value comparison [3]=>value [4]=>number of hits comparison [5]=>number of hits
        //   code description
        //     <type(ICD9,CPT4)>:<identifier>||<type(ICD9,CPT4)>:<identifier>||<identifier> etc.
        $temp_df = explode("::", $row['value']);
        if (exist_procedure_item($patient_id, $temp_df[0], $temp_df[1], $temp_df[2], $temp_df[3], $temp_df[4], $temp_df[5], $intervalType, $intervalValue, $dateTarget)) {
            // Record the match
            $isMatch = true;
        } else {
            // If this is a required entry then return false
            if ($row['required_flag']) {
                return false;
            }
        }
    }

  // return results of check
    return $isMatch;
}

/**
 * Function to check for appointment
 *
 * @todo Complete this to allow appointment reminders.
 * @param  string  $patient_id  pid of selected patient.
 * @param  string  $dateTarget  target date(format Y-m-d H:i:s). blank is current date.
 * @return boolean              true if appt exist, otherwise false
 */
function appointment_check($patient_id, $dateTarget = '')
{
    $isMatch = false; //matching flag

  // Set date to current if not set (although should always be set)
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');
    $dateTargetRound = date('Y-m-d', $dateTarget);

  // Set current date
    $currentDate = date('Y-m-d H:i:s');

  // Basically, if the appointment is within the current date to the target date,
  //  then return true. (will not send reminders on same day as appointment)
    $sql = sqlStatementCdrEngine("SELECT openemr_postcalendar_events.pc_eid, " .
    "openemr_postcalendar_events.pc_title, " .
    "openemr_postcalendar_events.pc_eventDate, " .
    "openemr_postcalendar_events.pc_startTime, " .
    "openemr_postcalendar_events.pc_endTime " .
    "FROM openemr_postcalendar_events " .
    "WHERE openemr_postcalendar_events.pc_eventDate > ? " .
    "AND openemr_postcalendar_events.pc_eventDate <= ? " .
    "AND openemr_postcalendar_events.pc_pid = ?", array($currentDate,$dateTarget,$patient_id));

  // return results of check
  //
  // TODO: Figure out how to have multiple appointment and changing appointment reminders.
  //       Plan to send back array of appt info (eid, time, date, etc.)
  //       to do this.
    if (sqlNumRows($sql) > 0) {
        $isMatch = true;
    }

    return $isMatch;
}

/**
 * Function to check lists filters and targets. Customizable and currently includes diagnoses, medications, allergies and surgeries.
 *
 * @param  string  $patient_id  pid of selected patient.
 * @param  array   $filter      array containing lists filter/target elements
 * @param  string  $dateTarget  target date(format Y-m-d H:i:s). blank is current date.
 * @return boolean              true if check passed, otherwise false
 */
function lists_check($patient_id, $filter, $dateTarget)
{
    $isMatch = false; //matching flag

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

    foreach ($filter as $row) {
        if (exist_lists_item($patient_id, $row['method_detail'], $row['value'], $dateTarget)) {
            // Record the match
            $isMatch = true;
        } else {
           // If this is a required entry then return false
            if ($row['required_flag']) {
                return false;
            }
        }
    }

  // return results of check
    return $isMatch;
}

/**
 * Function to check for existance of data in database for a patient
 *
 * @param  string   $patient_id       pid of selected patient.
 * @param  string   $table            selected mysql table
 * @param  string   $column           selected mysql column
 * @param  string   $data_comp        data comparison (eq,ne,gt,ge,lt,le)
 * @param  string   $data             selected data in the mysql database (1)(2)
 * @param  string   $num_items_comp   number items comparison (eq,ne,gt,ge,lt,le)
 * @param  integer  $num_items_thres  number of items threshold
 * @param  string   $intervalType     type of interval (ie. year)
 * @param  integer  $intervalValue    searched for within this many times of the interval type
 * @param  string   $dateTarget       target date(format Y-m-d H:i:s).
 * @return boolean                    true if check passed, otherwise false
 *
 * (1) If data ends with **, operators ne/eq are replaced by (NOT)LIKE operators
 * (2) If $data contains '#CURDATE#', then it will be converted to the current date.
 *
 */
function exist_database_item($patient_id, $table, string $column = null, $data_comp = '', string $data = null, $num_items_comp = null, $num_items_thres = null, $intervalType = '', $intervalValue = '', $dateTarget = '')
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect the correct column label for patient id in the table
    $patient_id_label = collect_database_label('pid', $table);

  // Get the interval sql query string
    $dateSql = sql_interval_string($table, $intervalType, $intervalValue, $dateTarget);

  // If just checking for existence (ie. data is empty),
  //   then simply set the comparison operator to ne.
    if (empty($data)) {
        $data_comp = "ne";
    }

  // get the appropriate sql comparison operator
    $compSql = convertCompSql($data_comp);

  // custom issues per table can be placed here
    $customSQL = '';
    if ($table == 'immunizations') {
        $customSQL = " AND `added_erroneously` = '0' ";
    }

  //adding table list for where condition
    $whereTables = '';
    if ($table == 'procedure_result') {
        $whereTables = ", procedure_order_code, " .
               "procedure_order, " .
               "procedure_report " ;
        $customSQL = " AND procedure_order.procedure_order_id = procedure_order_code.procedure_order_id AND " .
               "procedure_report.procedure_order_id = procedure_order.procedure_order_id AND " .
               "procedure_report.procedure_order_seq = procedure_order_code.procedure_order_seq AND " .
               "procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
    }

  // check for items
    if (empty($column)) {
        // simple search for any table entries
        $sql = sqlStatementCdrEngine("SELECT * " .
        "FROM `" . escape_table_name($table)  . "` " .
        " " . $whereTables . " " .
        "WHERE " . add_escape_custom($patient_id_label) . "=? " . $customSQL, array($patient_id));
    } else {
        // mdsupport : Allow trailing '**' in the strings to perform LIKE searches
        if ((substr($data, -2) == '**') && (($compSql == "=") || ($compSql == "!="))) {
            $compSql = ($compSql == "!=" ? " NOT" : "") . " LIKE CONCAT('%',?,'%') ";
            $data = substr_replace($data, '', -2);
        } else {
            $compSql = $compSql . "? ";
        }

        if ($whereTables == "" && strpos($table, 'form_') !== false) {
            //To handle standard forms starting with form_
            //In this case, we are assuming the date field is "date"
            $sql = sqlStatementCdrEngine(
                "SELECT b.`" . escape_sql_column_name($column, [$table]) . "` " .
                "FROM forms a " .
                "LEFT JOIN `" . escape_table_name($table) . "` " . " b " .
                "ON (a.form_id=b.id AND a.formdir LIKE '" . add_escape_custom(substr($table, 5)) . "') " .
                "WHERE a.deleted != '1' " .
                "AND b.`" . escape_sql_column_name($column, [$table]) . "`" . $compSql .
                "AND b." . add_escape_custom($patient_id_label) . "=? " . $customSQL
                . str_replace("`date`", "b.`date`", $dateSql),
                array($data, $patient_id)
            );
        } else {
            // This allows to enter the wild card #CURDATE# in the CDR Demographics filter criteria  at the value field
            // #CURDATE# is replace by the Current date allowing a dynamic date filtering
            if ($data == '#CURDATE#') {
                $data = date("Y-m-d");
            }

            // search for number of specific items
            $sql = sqlStatementCdrEngine("SELECT `" . escape_sql_column_name($column, [$table]) . "` " .
              "FROM `" . escape_table_name($table) . "` " .
              " " . $whereTables . " " .
              "WHERE `" . escape_sql_column_name($column, [$table]) . "`" . $compSql .
              "AND " . add_escape_custom($patient_id_label) . "=? " . $customSQL .
              $dateSql, array($data, $patient_id));
        }
    }

  // See if number of returned items passes the comparison
    return itemsNumberCompare($num_items_comp, $num_items_thres, sqlNumRows($sql));
}

/**
 * Function to check for existence of procedure(s) for a patient
 *
 * @param  string   $patient_id       pid of selected patient.
 * @param  string   $proc_title       procedure title
 * @param  string   $proc_code        procedure identifier code (array of <type(ICD9,CPT4)>:<identifier>||<type(ICD9,CPT4)>:<identifier>||<identifier> etc.)
 * @param  string   $results_comp     results comparison (eq,ne,gt,ge,lt,le)
 * @param  string   $result_data      results data (1)
 * @param  string   $num_items_comp   number items comparison (eq,ne,gt,ge,lt,le)
 * @param  integer  $num_items_thres  number of items threshold
 * @param  string   $intervalType     type of interval (ie. year)
 * @param  integer  $intervalValue    searched for within this many times of the interval type
 * @param  string   $dateTarget       target date(format Y-m-d H:i:s).
 * @return boolean                    true if check passed, otherwise false
 *
 * (1) If result_data ends with **, operators ne/eq are replaced by (NOT)LIKE operators
 *
 */
function exist_procedure_item($patient_id, $proc_title, $proc_code, $result_comp, string $result_data = null, $num_items_comp = null, $num_items_thres = null, $intervalType = '', $intervalValue = '', $dateTarget = '')
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Set the table exception (for looking up pertinent date and pid sql columns)
    $table = "PROCEDURE-EXCEPTION";

  // Collect the correct column label for patient id in the table
    $patient_id_label = collect_database_label('pid', $table);

  // Get the interval sql query string
    $dateSql = sql_interval_string($table, $intervalType, $intervalValue, $dateTarget);

  // If just checking for existence (ie result_data is empty),
  //   then simply set the comparison operator to ne.
    if (empty($result_data)) {
        $result_comp = "ne";
    }

  // get the appropriate sql comparison operator
    $compSql = convertCompSql($result_comp);

  // explode the code array
    $codes = array();
    if (!empty($proc_code)) {
        $codes = explode("||", $proc_code);
    } else {
        $codes[0] = '';
    }

  // ensure proc_title is at least blank
    if (empty($proc_title)) {
        $proc_title = '';
    }

  // collect specific items (use both title and/or codes) that fulfill request
    $sqlBindArray = array();
    $sql_query = "SELECT procedure_result.result FROM " .
               "procedure_order_code, " .
               "procedure_order, " .
               "procedure_type, " .
               "procedure_report, " .
               "procedure_result " .
               "WHERE " .
               "procedure_order_code.procedure_code = procedure_type.procedure_code AND " .
               "procedure_order.procedure_order_id = procedure_order_code.procedure_order_id AND " .
               "procedure_order.lab_id = procedure_type.lab_id AND " .
               "procedure_report.procedure_order_id = procedure_order.procedure_order_id AND " .
               "procedure_report.procedure_order_seq = procedure_order_code.procedure_order_seq AND " .
               "procedure_result.procedure_report_id = procedure_report.procedure_report_id AND " .
               "procedure_type.procedure_type = 'ord' AND ";
    foreach ($codes as $tem) {
        $sql_query .= "( ( (procedure_type.standard_code = ? AND procedure_type.standard_code != '') " .
                  "OR (procedure_type.procedure_code = ? AND procedure_type.procedure_code != '') ) OR ";
        array_push($sqlBindArray, $tem, $tem);
    }

  // mdsupport : Allow trailing '**' in the strings to perform LIKE searches
    if ((substr($result_data, -2) == '**') && (($compSql == "=") || ($compSql == "!="))) {
        $compSql = ($compSql == "!=" ? " NOT" : "") . " LIKE CONCAT('%',?,'%') ";
        $result_data = substr_replace($result_data, '', -2);
    } else {
        $compSql = $compSql . "? ";
    }

    $sql_query .= "(procedure_type.name = ? AND procedure_type.name != '') ) " .
                "AND procedure_result.result " . $compSql .
                "AND " . add_escape_custom($patient_id_label) . " = ? " . $dateSql;
    array_push($sqlBindArray, $proc_title, $result_data, $patient_id);

    $sql = sqlStatementCdrEngine($sql_query, $sqlBindArray);

  // See if number of returned items passes the comparison
    return itemsNumberCompare($num_items_comp, $num_items_thres, sqlNumRows($sql));
}

/**
 * Function to check for existance of data for a patient in the rule_patient_data table
 *
 * @param  string   $patient_id       pid of selected patient.
 * @param  string   $category         label in category column
 * @param  string   $item             label in item column
 * @param  string   $complete         label in complete column (YES,NO, or blank)
 * @param  string   $num_items_comp   number items comparison (eq,ne,gt,ge,lt,le)
 * @param  integer  $num_items_thres  number of items threshold
 * @param  string   $intervalType     type of interval (ie. year)
 * @param  integer  $intervalValue    searched for within this many times of the interval type
 * @param  string   $dateTarget       target date(format Y-m-d H:i:s).
 * @return boolean                    true if check passed, otherwise false
 */
function exist_custom_item($patient_id, $category, $item, $complete, $num_items_comp, $num_items_thres, string $intervalType = null, string $intervalValue = null, $dateTarget = null)
{

  // Set the table
    $table = 'rule_patient_data';

  // Collect the correct column label for patient id in the table
    $patient_id_label = collect_database_label('pid', $table);

  // Get the interval sql query string
    $dateSql = sql_interval_string($table, $intervalType, $intervalValue, $dateTarget);

  // search for number of specific items
    $sql = sqlStatementCdrEngine("SELECT `result` " .
    "FROM `" . escape_table_name($table)  . "` " .
    "WHERE `category`=? " .
    "AND `item`=? " .
    "AND `complete`=? " .
    "AND `" . add_escape_custom($patient_id_label)  . "`=? " .
    $dateSql, array($category,$item,$complete,$patient_id));

  // See if number of returned items passes the comparison
    return itemsNumberCompare($num_items_comp, $num_items_thres, sqlNumRows($sql));
}

/**
 * Function to check for existance of data for a patient in lifestyle section
 *
 * @param  string  $patient_id  pid of selected patient.
 * @param  string  $lifestyle   selected label of mysql column of patient history
 * @param  string  $status      specific status of selected lifestyle element
 * @param  string  $dateTarget  target date(format Y-m-d H:i:s). blank is current date.
 * @return boolean              true if check passed, otherwise false
 */
function exist_lifestyle_item($patient_id, $lifestyle, $status, $dateTarget)
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect pertinent history data
    // If illegal value in $lifestyle, then will die and report error (to prevent security vulnerabilities)
    escape_sql_column_name($lifestyle, ['history_data']);
    $history = getHistoryData($patient_id, $lifestyle, '', $dateTarget);

  // See if match
    $stringFlag = strstr(($history[$lifestyle] ?? ''), "|" . $status);
    if (empty($status)) {
        // Only ensuring any data has been entered into the field
        $stringFlag = true;
    }

    return !empty($history[$lifestyle]) &&
        $history[$lifestyle] != '|0|' &&
        $stringFlag;
}

/**
 * Function to check for lists item of a patient. Fully customizable and includes diagnoses, medications,
 * allergies, and surgeries.
 *
 * @param  string  $patient_id  pid of selected patient.
 * @param  string  $type        type (medical_problem, allergy, medication, etc)
 * @param  string  $value       value searching for (1)
 * @param  string  $dateTarget  target date(format Y-m-d H:i:s).
 * @return boolean              true if check passed, otherwise false
 *
 * (1) If value ends with **, operators ne/eq are replaced by (NOT)LIKE operators
 *
 */
function exist_lists_item($patient_id, $type, $value, $dateTarget)
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Attempt to explode the value into a code type and code (if applicable)
    $value_array = explode("::", $value);
    if (count($value_array) == 2) {
        // Collect the code type and code
        $code_type = $value_array[0];
        $code = $value_array[1];

        // Modify $code for both 'CUSTOM' and diagnosis searches
        // Note: Diagnosis is always 'LIKE' and should not have '**'
        if (substr($code, -2) == '**') {
            $sqloper = " LIKE CONCAT('%',?,'%') ";
            $code = substr_replace($code, '', -2);
        } else {
            $sqloper = "=?";
        }

        if ($code_type == 'CUSTOM') {
            // Deal with custom code type first (title column in lists table)
            $response = sqlQueryCdrEngine("SELECT * FROM `lists` " .
            "WHERE `type`=? " .
            "AND `pid`=? " .
            "AND `title` $sqloper " .
            "AND ( (`begdate` IS NULL AND `date`<=?) OR (`begdate` IS NOT NULL AND `begdate`<=?) ) " .
            "AND ( (`enddate` IS NULL) OR (`enddate` IS NOT NULL AND `enddate`>=?) )", array($type,$patient_id,$code,$dateTarget,$dateTarget,$dateTarget));
            if (!empty($response)) {
                return true;
            }
        } else {
            // Deal with the set code types (diagnosis column in lists table)
            $response = sqlQueryCdrEngine("SELECT * FROM `lists` " .
            "WHERE `type`=? " .
            "AND `pid`=? " .
            "AND `diagnosis` LIKE ? " .
            "AND ( (`begdate` IS NULL AND `date`<=?) OR (`begdate` IS NOT NULL AND `begdate`<=?) ) " .
            "AND ( (`enddate` IS NULL) OR (`enddate` IS NOT NULL AND `enddate`>=?) )", array($type,$patient_id,"%" . $code_type . ":" . $code . "%",$dateTarget,$dateTarget,$dateTarget));
            if (!empty($response)) {
                return true;
            }
        }
    } else { // count($value_array) == 1
        // Search the title column in lists table
        //   Yes, this is essentially the same as the code type listed as CUSTOM above. This provides flexibility and will ensure compatibility.

        // Check for '**'
        if (substr($value, -2) == '**') {
            $sqloper = " LIKE CONCAT('%',?,'%') ";
            $value = substr_replace($value, '', -2);
        } else {
            $sqloper = "=?";
        }

        $response = sqlQueryCdrEngine("SELECT * FROM `lists` " .
        "WHERE `type`=? " .
        "AND `pid`=? " .
        "AND `title` $sqloper " .
        "AND ( (`begdate` IS NULL AND `date`<=?) OR (`begdate` IS NOT NULL AND `begdate`<=?) ) " .
        "AND ( (`enddate` IS NULL) OR (`enddate` IS NOT NULL AND `enddate`>=?) )", array($type,$patient_id,$value,$dateTarget,$dateTarget,$dateTarget));
        if (!empty($response)) {
            return true;
        }

        if ($type == 'medication') { // Special case needed for medication as it need to be looked into current medications (prescriptions table) from ccda import
              $response = sqlQueryCdrEngine("SELECT * FROM `prescriptions` where `patient_id` = ? and `drug` $sqloper and `date_added` <= ?", array($patient_id,$value,$dateTarget));
            if (!empty($response)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Function to return part of sql query to deal with interval
 *
 * @param  string  $table          selected mysql table (or EXCEPTION(s))
 * @param  string  $intervalType   type of interval (ie. year)
 * @param  string  $intervalValue  searched for within this many times of the interval type
 * @param  string  $dateTarget     target date(format Y-m-d H:i:s).
 * @return string                  contains pertinent date interval filter for mysql query
 */
function sql_interval_string($table, $intervalType, $intervalValue, $dateTarget)
{

    $dateSql = "";

  // Collect the correct column label for date in the table
    $date_label = collect_database_label('date', $table);

  // Deal with interval
    if (!empty($intervalType)) {
        switch ($intervalType) {
            case "year":
                $dateSql = "AND (" . add_escape_custom($date_label) .
                " BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
                "', INTERVAL " . escape_limit($intervalValue) .
                " YEAR) AND '" . add_escape_custom($dateTarget) . "') ";
                break;
            case "month":
                $dateSql = "AND (" . add_escape_custom($date_label) .
                " BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
                "', INTERVAL " . escape_limit($intervalValue) .
                " MONTH) AND '" . add_escape_custom($dateTarget) . "') ";
                break;
            case "week":
                $dateSql = "AND (" . add_escape_custom($date_label) .
                " BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
                "', INTERVAL " . escape_limit($intervalValue) .
                " WEEK) AND '" . add_escape_custom($dateTarget) . "') ";
                break;
            case "day":
                $dateSql = "AND (" . add_escape_custom($date_label) .
                " BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
                "', INTERVAL " . escape_limit($intervalValue) .
                " DAY) AND '" . add_escape_custom($dateTarget) . "') ";
                break;
            case "hour":
                $dateSql = "AND (" . add_escape_custom($date_label) .
                " BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
                "', INTERVAL " . escape_limit($intervalValue) .
                " HOUR) AND '" . add_escape_custom($dateTarget) . "') ";
                break;
            case "minute":
                $dateSql = "AND (" . add_escape_custom($date_label) .
                " BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
                "', INTERVAL " . escape_limit($intervalValue) .
                " MINUTE) AND '" . add_escape_custom($dateTarget) . "') ";
                break;
            case "second":
                $dateSql = "AND (" . add_escape_custom($date_label) .
                " BETWEEN DATE_SUB('" . add_escape_custom($dateTarget) .
                "', INTERVAL " . escape_limit($intervalValue) .
                " SECOND) AND '" . add_escape_custom($dateTarget) . "') ";
                break;
            case "flu_season":
                // Flu season to be hard-coded as September thru February
                //  (Should make this modifiable in the future)
                //  ($intervalValue is not used)
                $dateArray = explode("-", $dateTarget);
                $Year = $dateArray[0];
                $dateThisYear = $Year . "-09-01";
                $dateLastYear = ($Year - 1) . "-09-01";
                $dateSql = " " .
                "AND ((" .
                    "MONTH('" . add_escape_custom($dateTarget) . "') < 9 " .
                    "AND " . add_escape_custom($date_label) . " >= '" . $dateLastYear . "' ) " .
                  "OR (" .
                    "MONTH('" . add_escape_custom($dateTarget) . "') >= 9 " .
                    "AND " . add_escape_custom($date_label) . " >= '" . $dateThisYear . "' ))" .
                "AND " . add_escape_custom($date_label) . " <= '" . add_escape_custom($dateTarget) . "' ";
                break;
        }
    } else {
        $dateSql = "AND " . add_escape_custom($date_label) .
        " <= '" . add_escape_custom($dateTarget)  . "' ";
    }

 // return the sql interval string
    return $dateSql;
}

/**
 * Function to collect generic column labels from tables. It currently works for date
 * and pid. Will need to expand this as algorithm grows.
 *
 * @param  string  $label  element (pid or date)
 * @param  string  $table  selected mysql table (or EXCEPTION(s))
 * @return string          contains official label of selected element
 */
function collect_database_label($label, $table)
{

    if ($table == 'PROCEDURE-EXCEPTION') {
        // return cell to get procedure collection
        // special case since reuqires joing of multiple
        // tables to get this value
        if ($label == "pid") {
            $returnedLabel = "procedure_order.patient_id";
        } elseif ($label == "date") {
            $returnedLabel = "procedure_report.date_collected";
        } else {
            // unknown label, so return the original label
            $returnedLabel = $label;
        }
    } elseif ($table == 'immunizations') {
        // return requested label for immunization table
        if ($label == "pid") {
            $returnedLabel = "patient_id";
        } elseif ($label == "date") {
            $returnedLabel = "`administered_date`";
        } else {
            // unknown label, so return the original label
            $returnedLabel = $label;
        }
    } elseif ($table == 'prescriptions') {
        // return requested label for prescriptions table
        if ($label == "pid") {
            $returnedLabel = "patient_id";
        } elseif ($label == "date") {
            $returnedLabel = 'date_added';
        } else {
            // unknown label, so return the original label
            $returnedLabel = $label;
        }
    } elseif ($table == 'procedure_result') {
        // return requested label for prescriptions table
        if ($label == "pid") {
            $returnedLabel = "procedure_order.patient_id";
        } elseif ($label == "date") {
            $returnedLabel = "procedure_report.date_collected";
        } else {
            // unknown label, so return the original label
            $returnedLabel = $label;
        }
    } elseif ($table == 'openemr_postcalendar_events') {
      // return requested label for prescriptions table
        if ($label == "pid") {
            $returnedLabel = "pc_pid";
        } elseif ($label == "date") {
            $returnedLabel = "pc_eventdate";
        } else {
          // unknown label, so return the original label
            $returnedLabel = $label;
        }
    } else {
        // return requested label for default tables
        if ($label == "pid") {
            $returnedLabel = "pid";
        } elseif ($label == "date") {
            $returnedLabel = "`date`";
        } else {
          // unknown label, so return the original label
            $returnedLabel = $label;
        }
    }

    return $returnedLabel;
}

/**
 * Simple function to avoid processing of duplicate actions
 *
 * @param  array  $actions  2-dimensional array with all current active targets
 * @param  array  $action   array of selected target to test for duplicate
 * @return boolean           true if duplicate, false if not duplicate
 */
function is_duplicate_action($actions, $action)
{
    foreach ($actions as $row) {
        if (
            $row['category'] == $action['category'] &&
            $row['item'] == $action['item'] &&
            $row['value'] == $action['value']
        ) {
            // Is a duplicate
            return true;
        }
    }

  // Not a duplicate
    return false;
}

/**
 * Calculate the reminder dates.
 *
 * This function returns an array that contains three elements (each element is a date).
 * <pre>The three dates are:
 *   first date is before the target date (past_due) (default of 1 month)
 *   second date is the target date (due)
 *   third date is after the target date (soon_due) (default of 2 weeks)
 * </pre>
 *
 * @param  string  $rule        id(string) of selected rule
 * @param  string  $dateTarget  target date(format Y-m-d H:i:s).
 * @param  string  $type        either 'patient_reminder' or 'clinical_reminder'
 * @return array                see above for description of returned array
 */
function calculate_reminder_dates($rule, string $dateTarget = null, $type = null)
{

  // Set date to current if not set
    $dateTarget = ($dateTarget) ? $dateTarget : date('Y-m-d H:i:s');

  // Collect the current date settings (to ensure not skip)
    $res = resolve_reminder_sql($rule, $type . '_current');
    if (!empty($res)) {
        $row = $res[0];
        if ($row ['method_detail'] == "SKIP") {
            $dateTarget = "SKIP";
        }
    }

  // Collect the past_due date
    $past_due_date = "";
    $res = resolve_reminder_sql($rule, $type . '_post');
    if (!empty($res)) {
        $row = $res[0];
        if ($row ['method_detail'] == "week") {
            $past_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -" . $row ['value'] . " week"));
        }

        if ($row ['method_detail'] == "month") {
            $past_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -" . $row ['value'] . " month"));
        }

        if ($row ['method_detail'] == "hour") {
            $past_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -" . $row ['value'] . " hour"));
        }

        if ($row ['method_detail'] == "SKIP") {
            $past_due_date = "SKIP";
        }
    } else {
        // empty settings, so use default of one month
        $past_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -1 month"));
    }

  // Collect the soon_due date
    $soon_due_date = "";
    $res = resolve_reminder_sql($rule, $type . '_pre');
    if (!empty($res)) {
        $row = $res[0];
        if ($row ['method_detail'] == "week") {
            $soon_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " +" . $row ['value'] . " week"));
        }

        if ($row ['method_detail'] == "month") {
            $soon_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " +" . $row ['value'] . " month"));
        }

        if ($row ['method_detail'] == "hour") {
            $soon_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " -" . $row ['value'] . " hour"));
        }

        if ($row ['method_detail'] == "SKIP") {
            $soon_due_date = "SKIP";
        }
    } else {
        // empty settings, so use default of one month
        $soon_due_date = date("Y-m-d H:i:s", strtotime($dateTarget . " +2 week"));
    }

  // Return the array of three dates
    return array($soon_due_date,$dateTarget,$past_due_date);
}

/**
 * Adds an action into the reminder array
 *
 * @param  array  $reminderOldArray  Contains the current array of reminders
 * @param  array  $reminderNew       Array of a new reminder
 * @return array                     Reminders
 */
function reminder_results_integrate($reminderOldArray, $reminderNew)
{

    $results = array();

  // If reminderArray is empty, then insert new reminder
    if (empty($reminderOldArray)) {
        $results[] = $reminderNew;
        return $results;
    }

  // If duplicate reminder, then replace the old one
    $duplicate = false;
    foreach ($reminderOldArray as $reminderOld) {
        if (
            $reminderOld['pid'] == $reminderNew['pid'] &&
            $reminderOld['category'] == $reminderNew['category'] &&
            $reminderOld['item'] == $reminderNew['item']
        ) {
            $results[] = $reminderNew;
            $duplicate = true;
        } else {
            $results[] = $reminderOld;
        }
    }

  // If a new reminder, then insert the new reminder
    if (!$duplicate) {
        $results[] = $reminderNew;
    }

    return $results;
}

/**
 * Compares number of items with requested comparison operator
 *
 * @param  string   $comp       Comparison operator(eq,ne,gt,ge,lt,le)
 * @param  string   $thres      Threshold used in comparison
 * @param  integer  $num_items  Number of items
 * @return boolean              Comparison results
 */
function itemsNumberCompare($comp, $thres, $num_items)
{

    if (($comp == "eq") && ($num_items == $thres)) {
        return true;
    } elseif (($comp == "ne") && ($num_items != $thres) && ($num_items > 0)) {
        return true;
    } elseif (($comp == "gt") && ($num_items > $thres)) {
        return true;
    } elseif (($comp == "ge") && ($num_items >= $thres)) {
        return true;
    } elseif (($comp == "lt") && ($num_items < $thres) && ($num_items > 0)) {
        return true;
    } elseif (($comp == "le") && ($num_items <= $thres) && ($num_items > 0)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Converts a text comparison operator to sql equivalent
 *
 * @param  string  $comp  Comparison operator(eq,ne,gt,ge,lt,le)
 * @return string         contains sql compatible comparison operator
 */
function convertCompSql($comp)
{

    if ($comp == "eq") {
        return "=";
    } elseif ($comp == "ne") {
        return "!=";
    } elseif ($comp == "gt") {
        return ">";
    } elseif ($comp == "ge") {
        return ">=";
    } elseif ($comp == "lt") {
        return "<";
    } else { // ($comp == "le")
        return "<=";
    }
}


/**
 * Function to find age in years (with decimal) on the target date
 *
 * @param  string  $dob     date of birth
 * @param  string  $target  date to calculate age on
 * @return float            years(decimal) from dob to target(date)
 */
function convertDobtoAgeYearDecimal($dob, $target)
{
    $ageInfo = parseAgeInfo($dob, $target);
    return $ageInfo['age'];
}

/**
 * Function to find age in months (with decimal) on the target date
 *
 * @param  string  $dob     date of birth
 * @param  string  $target  date to calculate age on
 * @return float            months(decimal) from dob to target(date)
 */
function convertDobtoAgeMonthDecimal($dob, $target)
{
    $ageInfo = parseAgeInfo($dob, $target);
    return $ageInfo['age_in_months'];
}

/**
 * Function to calculate the percentage for reports.
 *
 * @param  integer  $pass_filter     number of patients that pass filter
 * @param  integer  $exclude_filter  number of patients that are excluded
 * @param  integer  $pass_target     number of patients that pass target
 * @return string                    Number formatted into a percentage
 */
function calculate_percentage($pass_filt, $exclude_filt, $pass_targ)
{
    if ($pass_filt > 0) {
        $perc = number_format(($pass_targ / ($pass_filt - $exclude_filt)) * 100, 4) . xl('%');
    } else {
        $perc = "0" . xl('%');
    }

    return $perc;
}
