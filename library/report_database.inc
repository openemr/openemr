<?php

/**
 * Report tracking, storing and viewing functions using the report_results sql table.
 *
 * Supports generic tracking, storing and viewing of reports by utilizing a vertical
 * table entitled report_results. This allows flexible placement of tokens for report
 * setting etc. Also supports itemization of results (per patient tracking).
 * <pre>Tokens that are reserved include:
 *   'bookmark'          - Allows bookmarking of a new report id (used to allow tracking
 *                         progress via ajax calls). If exist, value is always set to '1'.
 *   'progress'          - Either set to 'pending' or 'complete'.
 *   'type'              - Set to type of report
 *   'total_items'       - Set to total number of items that will be processed (ie. such as patients)
 *   'progress_items'    - Set to number of items (ie. such as patients)
 *   'data'              - Contains the data of the report
 *   'date_report'       - Set to date of the report (date and time)
 *   'date_report_complete'       - Set to date of the report completion (date and time)
 * </pre>
 *
 * Copyright (C) 2012 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

use OpenEMR\Common\Csrf\CsrfUtils;

/**
 * Return listing of report results.
 *
 * @param   timestamp  $start  Start of date range
 * @param   timestamp  $end    End of date range
 * @return  sql-query          Listing of report results
 */
function listingReportDatabase($start_date = '', $end_date = '')
{

  // set $end_date to today's date if empty
    $end_date = ($end_date) ? $end_date : date('Y-m-d H:i:s');

  // Collect pertinent information as a pivot table (ie. converting vertical to horizontal row)
    if (empty($start_date)) {
        $res = sqlStatement("SELECT *, TIMESTAMPDIFF(MINUTE,pt.date_report,pt.date_report_complete) as `report_time_processing`
                         FROM (
                           SELECT `report_id`,
                           MAX(if( `field_id` = 'date_report', `field_value`, 0 )) as `date_report`,
                           MAX(if( `field_id` = 'date_report_complete', `field_value`, 0 )) as `date_report_complete`,
                           MAX(if( `field_id` = 'progress', `field_value`, 0 )) as `progress`,
                           MAX(if( `field_id` = 'total_items', `field_value`, 0 )) as `total_items`,
                           MAX(if( `field_id` = 'progress_items', `field_value`, 0 )) as `progress_items`,
                           MAX(if( `field_id` = 'type', `field_value`, 0 )) as `type`
                           FROM `report_results`
                           GROUP BY `report_id`
                         ) AS pt
                         WHERE pt.date_report < ?
                         ORDER BY pt.report_id", array($end_date));
    } else {
        $res = sqlStatement("SELECT *, TIMESTAMPDIFF(MINUTE,pt.date_report,pt.date_report_complete) as `report_time_processing`
                         FROM (
                           SELECT `report_id`,
                           MAX(if( `field_id` = 'date_report', `field_value`, 0 )) as `date_report`,
                           MAX(if( `field_id` = 'date_report_complete', `field_value`, 0 )) as `date_report_complete`,
                           MAX(if( `field_id` = 'progress', `field_value`, 0 )) as `progress`,
                           MAX(if( `field_id` = 'total_items', `field_value`, 0 )) as `total_items`,
                           MAX(if( `field_id` = 'progress_items', `field_value`, 0 )) as `progress_items`,
                           MAX(if( `field_id` = 'type', `field_value`, 0 )) as `type`
                           FROM `report_results`
                           GROUP BY `report_id`
                         ) AS pt
                         WHERE pt.date_report > ? AND pt.date_report < ?
                         ORDER BY pt.report_id", array($start_date,$end_date));
    }

    return $res;
}

/**
 * Simply reserves a report id for use in the report results tracking/storing/viewing item in database..
 *
 * @return  integer           Report id that was assigned in database
 */
function bookmarkReportDatabase()
{

  // Retrieve a new report id
    $query = sqlQuery("SELECT max(`report_id`) as max_report_id FROM `report_results`");
    if (empty($query)) {
        $new_report_id = 1;
    } else {
        $new_report_id = $query['max_report_id'] + 1;
    }

  // Set the bookmark token
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($new_report_id,"bookmark",1));

    return $new_report_id;
}

/**
 * Initiate a report results tracking/storing/viewing item in database.
 *
 * @param   string   $type       Report type identifier
 * @param   array    $fields     Array containing pertinent report details (Do NOT use 'bookmark', 'progress','type','progress_patients', 'data', 'date_report' or 'no_json_support' as keys in array; they will be ignored)
 * @param   integer  $report_id  Report id (if have already bookmarked a report id)
 * @return  integer              Report id that is assigned to the report
 */
function beginReportDatabase($type, $fields, $report_id = null)
{

  // Retrieve a new report id, if needed.
    if (empty($report_id)) {
        $query = sqlQuery("SELECT max(`report_id`) as max_report_id FROM `report_results`");
        if (empty($query)) {
            $new_report_id = 1;
        } else {
            $new_report_id = $query['max_report_id'] + 1;
        }
    } else {
        $new_report_id = $report_id;
    }

  // Set the required tokens
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($new_report_id,"progress","pending"));
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($new_report_id,"type",$type));
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($new_report_id,"progress_items","0"));
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($new_report_id,"data",""));
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($new_report_id,"date_report",date("Y-m-d H:i:s")));

  // Set the fields tokens
    if (!empty($fields)) {
        foreach ($fields as $key => $value) {
            // skip the special tokens
            if (
                ($key == "type") ||
                ($key == "data") ||
                ($key == "progress") ||
                ($key == "progress_items") ||
                ($key == "total_items") ||
                ($key == "date_report") ||
                ($key == "date_report_complete") ||
                ($key == "bookmark")
            ) {
                continue;
            }

            // place the token
            sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($new_report_id,$key,$value));
        }
    }

  // Return the report id
    return $new_report_id;
}

/**
 * Insert total items to process in database.
 * For performance reasons, it is assumed that the total_items does not already exists in current database entry.
 *
 * @param   integer  $report_id    Report id
 * @param   integer  $total_items  Total number of items that will be processed
 */
function setTotalItemsReportDatabase($report_id, $total_items)
{
  // Insert the total items that are to be processed
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($report_id,"total_items",$total_items));
}

/**
 * Update report results in database(basically update number of items (patients) that has been processed in pending reports).
 * For performance reasons, it is assumed that the progress_items token already exists in current database entry.
 *
 * @param   integer  $report_id           Report id
 * @param   integer  $items_processed  Number of items that have been processed
 */
function updateReportDatabase($report_id, $items_processed)
{
  // Update the items that have been processed
    sqlStatement("UPDATE `report_results` SET `field_value`=? WHERE `report_id`=? AND `field_id`='progress_items'", array($items_processed,$report_id));
}

/**
 * Store (finished) report results (in json format) in database.
 * For performance reasons, it is assumed that the data and progress tokens already exists in current database entry.
 * For performance reasons, it is assumed that the date_report_complete does not already exists in current database entry.
 *
 * @param   integer  $report_id  Report id
 * @param   string   $data       Report results/data
 */
function finishReportDatabase($report_id, $data)
{

  // Record the data
    sqlStatement("UPDATE `report_results` SET `field_value`=? WHERE `report_id`=? AND `field_id`='data'", array($data,$report_id));

  // Record the finish date/time
    sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($report_id,"date_report_complete",date("Y-m-d H:i:s")));

  // Set progress to complete
    sqlStatement("UPDATE `report_results` SET `field_value`='complete' WHERE `report_id`=? AND `field_id`='progress'", array($report_id));
}

/**
 * Collect report results from database.
 *
 * @param   integer  $report_id  Report id
 * @return  array                Array of id/values for a report
 */
function collectReportDatabase($report_id)
{

  // Collect the rows of data
    $res = sqlStatement("SELECT * FROM `report_results` WHERE `report_id`=?", array($report_id));

  // Convert data into an array
    $final_array = array();
    while ($row = sqlFetchArray($res)) {
        $final_array = array_merge($final_array, array($row['field_id'] => $row['field_value']));
    }

    return $final_array;
}

/**
 * Get status of report from database.
 *
 * @param   integer  $report_id  Report id
 * @return  string               Status report (PENDING, COMPLETE, or return a string with progress)
 */
function getStatusReportDatabase($report_id)
{

  // Collect the pertinent rows of data
    $res = sqlStatement("SELECT `field_id`, `field_value` FROM `report_results` WHERE `report_id`=? AND (`field_id`='progress' OR `field_id`='total_items' OR `field_id`='progress_items')", array($report_id));

  // If empty, then just return Pending, since stil haven't likely created the entries yet
    if (sqlNumRows($res) < 1) {
        return "PENDING";
    }

  // Place into an array for quick processing
    $final_array = array();
    while ($row = sqlFetchArray($res)) {
        $final_array = array_merge($final_array, array($row['field_id'] => $row['field_value']));
    }

    if ($final_array['progress'] == "complete") {
        // return COMPLETE
        return "COMPLETE";
    } else {
        $final_array['progress_items'] = ($final_array['progress_items']) ? $final_array['progress_items'] : 0;
        return $final_array['progress_items'] . " / " . $final_array['total_items'] . " " . xl("Patients");
    }
}

/**
 * Insert itemization item into database.
 *
 * @param   integer  $report_id         Report id
 * @param   integer  $itemized_test_id  Itemized test id
 * @param   integer  $pass              0 is fail, 1 is pass, 2 is exclude
 * @param   integer  $patient_id        Patient pid
 * @param   string   $numerator_label  Numerator label (if applicable)
 * @param   string   $rule_id  The name of the rule that was used to generate this item
 * @param   string   $itemized_details  JSON of itemized details about this rule (if applicable)
 */
function insertItemReportTracker($report_id, $itemized_test_id, $pass, $patient_id, $numerator_label = '', $rule_id = '', $itemizedDetails = '')
{
    $sqlParameters = array($report_id,$itemized_test_id,$numerator_label,$pass,$patient_id, $rule_id, $itemizedDetails);
    sqlStatementCdrEngine("INSERT INTO `report_itemized` (`report_id`,`itemized_test_id`,`numerator_label`,`pass`,`pid`,`rule_id`,`item_details`) VALUES (?,?,?,?,?,?,?)", $sqlParameters);
}

/**
 * Collect a rules display title for itemized report.
 *
 * @param   integer  $report_id         Report id
 * @param   integer  $itemized_test_id  Itemized test id
 * @param   integer  $numerator_label   Numerator label (if applicable)
 * @return  string/boolean              Rule title for itemization display (false if nothing found)
 */
function collectItemizedRuleDisplayTitle($report_id, $itemized_test_id, $numerator_label = '')
{
    $dispTitle = "";
    $report_view = collectReportDatabase($report_id);
    $type_report = $report_view['type'];
    $dataSheet = json_decode($report_view['data'], true);
    $display_group_provider_info = false;
    $group_label = '';
    $group_provider_label = '';

    // for our group calculation reports we want to display the group and the provider label information
    if ($report_view['provider'] == 'group_calculation') {
        $display_group_provider_info = true;
    }
    foreach ($dataSheet as $row) {
        if ($display_group_provider_info) {
            if (isset($row['is_provider_group'])) {
                $group_label = xlt('Group') . ': ' . text($row['name']) . ' ( ' . xlt('TIN') . ' '
                    . text($row['federaltaxid']) . ' )';
                $group_provider_label = $group_label;
            } else if (isset($row['is_provider'])) {
                $provider_label = ' ' . xlt('Provider') . ': ' . text($row['prov_fname']) . ',' . text($row['prov_lname'])
                    . ' ( ' . xlt('NPI') . ' ' . text($row['npi']) . ' ) ';
                if (isset($row['is_provider_in_group'])) {
                    $group_provider_label = $group_label . ' ' . $provider_label;
                } else {
                    $group_provider_label = $provider_label;
                }
            }
        }

        if (isset($row['is_main']) || isset($row['is_sub'])) {
            if (isset($row['is_main'])) {
                // Store this
                $dispTitle = $group_provider_label . ' ' . generate_display_field(array('data_type' => '1','list_id' => 'clinical_rules'), $row['id']);
            }

            if (($row['itemized_test_id'] == $itemized_test_id) && (($row['numerator_label'] ?? '') == $numerator_label)) {
                // We have a hit, build on the $dispTitle created above
                if (isset($row['is_main'])) {
                    $tempCqmAmcString = "";
                    if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
                        if (!empty($row['cqm_pqri_code'])) {
                            $tempCqmAmcString .= " " . xlt('PQRI') . ":" . text($row['cqm_pqri_code']) . " ";
                        }

                        if (!empty($row['cqm_nqf_code'])) {
                            $tempCqmAmcString .= " " . xlt('NQF') . ":" . text($row['cqm_nqf_code']) . " ";
                        }
                    }

                    if ($type_report == "amc") {
                        if (!empty($row['amc_code'])) {
                            $tempCqmAmcString .= " " . xlt('AMC-2011') . ":" . text($row['amc_code']) . " ";
                        }

                        if (!empty($row['amc_code_2014'])) {
                            $tempCqmAmcString .= " " . xlt('AMC-2014') . ":" . text($row['amc_code_2014']) . " ";
                        }
                    }

                    if ($type_report == "amc_2011") {
                        if (!empty($row['amc_code'])) {
                            $tempCqmAmcString .= " " . xlt('AMC-2011') . ":" . text($row['amc_code']) . " ";
                        }
                    }

                    if ($type_report == "amc_2014_stage1") {
                        if (!empty($row['amc_code_2014'])) {
                            $tempCqmAmcString .= " " . xlt('AMC-2014 Stage I') . ":" . text($row['amc_code_2014']) . " ";
                        }
                    }

                    if ($type_report == "amc_2014_stage2") {
                        if (!empty($row['amc_code_2014'])) {
                            $tempCqmAmcString .= " " . xlt('AMC-2014 Stage II') . ":" . text($row['amc_code_2014']) . " ";
                        }
                    }

                    if (!empty($tempCqmAmcString)) {
                        $dispTitle .=  "(" . $tempCqmAmcString . ")";
                    }

                    if (!(empty($row['concatenated_label']))) {
                        $dispTitle .= ", " . xlt($row['concatenated_label']) . " ";
                    }
                } else { // isset($row['is_sub']
                    $dispTitle .= " - " .  generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $row['action_category']);
                    $dispTitle .= ": " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $row['action_item']);
                }

                return $dispTitle;
            }
        }
    }

    return false;
}

/**
 * Collect patient listing from CDR reports itemization.
 *
 * @param   integer  $report_id         Report id
 * @param   integer  $itemized_test_id  Itemized test id
 * @param   string   $pass              options are 'fail', 'pass', 'exclude', 'init_patients', 'exception' and 'all'
 * @param   integer  $numerator_label   Numerator label (if applicable)
 * @param   integer  $sqllimit          Sql query pagination info
 * @param   integer  $fstart            Sql query pagination info
 * @return  array/integer               Array list or a count
 */
function collectItemizedPatientsCdrReport($report_id, $itemized_test_id, $pass = 'all', $numerator_label = '', $count = false, $sqllimit = 'all', $fstart = 0)
{

    if ($count) {
        $given = " COUNT(DISTINCT `patient_data`.`pid`) AS total_listings ";
    } else {
        $given = " DISTINCT `patient_data`.*, DATE_FORMAT(`patient_data`.`DOB`,'%m/%d/%Y') as DOB_TS ";
    }

    $orderby = " `patient_data`.`lname` ASC, `patient_data`.`fname` ASC ";

  // set $pass_sql
    switch ($pass) {
        case "fail":
            $pass_sql = 0;
            break;
        case "pass":
            $pass_sql = 1;
            break;
        case "exclude":
            $pass_sql = 2;
            break;
        case "init_patients":
            $pass_sql = 3;
            break;
        case "exception":
            $pass_sql = 4;
            break;
    }

    $sqlParameters = array($report_id,$itemized_test_id,$numerator_label);

    if ($pass == "all") {
        $sql_where = " WHERE `report_itemized`.`pass` != 3 AND `report_itemized`.`report_id` = ? AND `report_itemized`.`itemized_test_id` = ? AND `report_itemized`.`numerator_label` = ? ";
    } elseif ($pass == "fail") {
        $exlPidArr = array();
        $exludeResult = collectItemizedPatientsCdrReport($report_id, $itemized_test_id, 'exclude', $numerator_label, false, $sqllimit, $fstart);
        foreach ($exludeResult as $exlResArr) {
            $exlPidArr[] = $exlResArr['pid'];
        }

        $sql_where = " WHERE `report_itemized`.`report_id` = ? AND `report_itemized`.`itemized_test_id` = ? AND `report_itemized`.`numerator_label` = ? AND `report_itemized`.`pass` = ? ";

        if (count($exlPidArr) > 0) {
            $exlPids = implode(",", $exlPidArr);
            $sql_where .= " AND patient_data.pid NOT IN(" . add_escape_custom($exlPids) . ") ";
        }

        array_push($sqlParameters, $pass_sql);
    } else {
        $sql_where = " WHERE `report_itemized`.`report_id` = ? AND `report_itemized`.`itemized_test_id` = ? AND `report_itemized`.`numerator_label` = ? AND `report_itemized`.`pass` = ? ";
        array_push($sqlParameters, $pass_sql);
    }

    $sql_query = "SELECT " . $given . " FROM `patient_data` JOIN `report_itemized` ON `patient_data`.`pid` = `report_itemized`.`pid` " . $sql_where . " ORDER BY " . $orderby;

    if ($sqllimit != "all") {
        $sql_query .= " limit " . escape_limit($fstart) . ", " . escape_limit($sqllimit);
    }

    if ($count) {
        $rez = sqlQueryCdrEngine($sql_query, $sqlParameters);
        return $rez['total_listings'];
    } else {
        $rez = sqlStatementCdrEngine($sql_query, $sqlParameters);
        // create array of listing for return
        $returnval = array();
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $returnval[$iter] = $row;
        }

        return $returnval;
    }
}

/**
 * Formats the report data into a format that can be consumed by the twig rendering output.
 * @param $report_id The report that we are formatting
 * @param $data string json encoded report data retrieved from the database
 * @param $is_amc boolean True if this is an AMC report
 * @param $is_cqm boolean True if this is an CQM report
 * @param $type_report The specific report type (could be a subset of AMC or CQM)
 * @param $amc_report_types If an AMC report, the specific AMC report type
 * @return array A formatted array of record rows to be used for displaying a CQM/AMC/Standard report.
 */
function formatReportData($report_id, &$data, $is_amc, $is_cqm, $type_report, $amc_report_types = array())
{
    $dataSheet = json_decode($data, true) ?? [];
    $formatted = [];
    $main_pass_filter = 0;
    foreach ($dataSheet as $row) {
        $row['type'] = $type_report;
        $row['total_patients'] = $row['total_patients'] ?? 0;
        $failed_items = null;
        $displayFieldSubHeader = "";

        if ($is_cqm) {
            $row['type'] = 'cqm';
            $row['total_patients'] = $row['initial_population'] ?? 0;
            if (isset($row['cqm_pqri_code'])) {
                $displayFieldSubHeader .= " " . xl('PQRI') . ":" . $row['cqm_pqri_code'] . " ";
            }
            if (isset($row['cqm_nqf_code'])) {
                $displayFieldSubHeader .= " " . xl('NQF') . ":" . $row['cqm_nqf_code'] . " ";
            }
        } else if ($is_amc) {
            $row['type'] = 'amc';
            if (!empty($amc_report_types[$type_report]['code_col'])) {
                $code_col = $amc_report_types[$type_report]['code_col'];
                $displayFieldSubHeader .= " " . text($amc_report_types[$type_report]['abbr']) . ":"
                    . text($row[$code_col]) . " ";
            }
        }

        if (isset($row['is_main'])) {
            // note that the is_main record must always come before is_sub in the report or the data will not work.
            $main_pass_filter = $row['pass_filter'] ?? 0;
            $row['display_field'] = generate_display_field(array('data_type' => '1','list_id' => 'clinical_rules'), $row['id']);
            if ($type_report == "standard") {
                // Excluded is not part of denominator in standard rules so do not use in calculation
                $failed_items = $row['pass_filter'] - $row['pass_target'];
            } else {
                $failed_items = $row['pass_filter'] - $row['pass_target'] - $row['excluded'];
            }
            $row['display_field_sub'] = ($displayFieldSubHeader != "") ? "($displayFieldSubHeader)" : null;
        } else if (isset($row['is_sub'])) {
            $row['display_field'] = generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $row['action_category'])
                . ': ' . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $row['action_item']);
            // Excluded is not part of denominator in standard rules so do not use in calculation
            $failed_items = $main_pass_filter - $row['pass_target'];
        } else if (isset($row['is_plan'])) {
            $row['display_field'] = generate_display_field(array('data_type' => '1','list_id' => 'clinical_plans'), $row['id']);
        }

        if (isset($row['itemized_test_id'])) {
            $csrf_token = CsrfUtils::collectCsrfToken();

            $base_link = sprintf(
                "../main/finder/patient_select.php?from_page=cdr_report&report_id=%d"
                . "&itemized_test_id=%d&numerator_label=%s&csrf_token_form=%s",
                urlencode($report_id),
                urlencode($row['itemized_test_id']),
                urlencode($row['numerator_label'] ?? ''),
                urlencode($csrf_token)
            );

            // we need the provider & group id here...

            // denominator
            if (isset($row['pass_filter']) && $row['pass_filter'] > 0) {
                $row['display_pass_link'] = $base_link . "&pass_id=all";
            }

            // excluded denominator
            if (isset($row['excluded']) && ($row['excluded'] > 0)) {
                $row['display_excluded_link'] = $base_link . "&pass_id=exclude";
            }

            // passed numerator
            if (isset($row['pass_target']) && ($row['pass_target'] > 0)) {
                $row['display_target_link'] = $base_link . "&pass_id=pass";
            }
            // failed numerator
            if (isset($failed_items) && $failed_items > 0) {
                $row['display_failed_link'] = $base_link . "&pass_id=fail";
            }
            $row['failed_items'] = $failed_items;
        }

        $formatted[] = $row;
    }
    return $formatted;
}
