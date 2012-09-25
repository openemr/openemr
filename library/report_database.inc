<?php
/**
 * Report tracking, storing and viewing functions using the report_results sql table.
 *
 * Supports generic tracking, storing and viewing of reports by utilizing a vertical
 * table entitled report_results. This allows flexible placement of tokens for report
 * setting etc.
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
 * These functions should not ever attempt to write to
 * session variables, because the session_write_close() function
 * is typically called before utilizing these functions.
 *
 * Copyright (C) 2012 Brady Miller <brady@sparmy.com>
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
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

/**
 * Return listing of report results.
 *
 * @param   timestamp  $start  Start of date range
 * @param   timestamp  $end    End of date range
 * @return  sql-query          Listing of report results
 */
function listingReportDatabase($start_date='',$end_date='') {

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
  }
  else {
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
function bookmarkReportDatabase() {

  // Retrieve a new report id
  $query = sqlQuery("SELECT max(`report_id`) as max_report_id FROM `report_results`");
  if (empty($query)) {
    $new_report_id = 1;
  }
  else {
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
function beginReportDatabase($type,$fields,$report_id=NULL) {

  // Retrieve a new report id, if needed.
  if (empty($report_id)) {
    $query = sqlQuery("SELECT max(`report_id`) as max_report_id FROM `report_results`");
    if (empty($query)) {
      $new_report_id = 1;
    }
    else {
     $new_report_id = $query['max_report_id'] + 1;
    }
  }
  else {
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
   foreach ($fields as $key=>$value) {

    // skip the special tokens
    if (($key == "type") ||
        ($key == "data") ||
        ($key == "progress") ||
        ($key == "progress_items") ||
        ($key == "total_items") ||
        ($key == "date_report") ||
        ($key == "date_report_complete") ||
        ($key == "bookmark") ) continue;

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
function setTotalItemsReportDatabase($report_id,$total_items) {
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
function updateReportDatabase($report_id,$items_processed) {
  // Update the items that have been processed
  sqlStatement("UPDATE `report_results` SET `field_value`=? WHERE `report_id`=? AND `field_id`='progress_items'", array($items_processed,$report_id) );
}

/**
 * Store (finished) report results (in json format) in database.
 * For performance reasons, it is assumed that the data and progress tokens already exists in current database entry.
 * For performance reasons, it is assumed that the date_report_complete does not already exists in current database entry.
 *
 * @param   integer  $report_id  Report id
 * @param   string   $data       Report results/data
 */
function finishReportDatabase($report_id,$data) {

  // Record the data
  sqlStatement("UPDATE `report_results` SET `field_value`=? WHERE `report_id`=? AND `field_id`='data'", array($data,$report_id) );

  // Record the finish date/time
  sqlStatement("INSERT INTO `report_results` (`report_id`,`field_id`,`field_value`) VALUES (?,?,?)", array ($report_id,"date_report_complete",date("Y-m-d H:i:s")));

  // Set progress to complete
  sqlStatement("UPDATE `report_results` SET `field_value`='complete' WHERE `report_id`=? AND `field_id`='progress'", array($report_id) );
}

/**
 * Collect report results from database.
 *
 * @param   integer  $report_id  Report id
 * @return  array                Array of id/values for a report
 */
function collectReportDatabase($report_id) {

  // Collect the rows of data
  $res = sqlStatement("SELECT * FROM `report_results` WHERE `report_id`=?", array($report_id));
  
  // Convert data into an array
  $final_array = array();
  while ($row = sqlFetchArray($res)) {
    $final_array = array_merge($final_array,array($row['field_id']=>$row['field_value']));
  }

  return $final_array;
}

/**
 * Get status of report from database.
 *
 * @param   integer  $report_id  Report id
 * @return  string               Status report (PENDING, COMPLETE, or return a string with progress)
 */
function getStatusReportDatabase($report_id) {

  // Collect the pertinent rows of data
  $res = sqlStatement("SELECT `field_id`, `field_value` FROM `report_results` WHERE `report_id`=? AND (`field_id`='progress' OR `field_id`='total_items' OR `field_id`='progress_items')", array($report_id));

  // If empty, then just return Pending, since stil haven't likely created the entries yet
  if (sqlNumRows($res) < 1) {
    return "PENDING";
  }

  // Place into an array for quick processing
  $final_array = array();
  while ($row = sqlFetchArray($res)) {
    $final_array = array_merge($final_array,array($row['field_id']=>$row['field_value']));
  }

  if ($final_array['progress'] == "complete") {
    // return COMPLETE
    return "COMPLETE";
  }
  else {
    $final_array['progress_items'] = ($final_array['progress_items']) ? $final_array['progress_items'] : 0;
    return $final_array['progress_items'] . " / " . $final_array['total_items'] . " " . xl("Patients");
  }
}
?>
