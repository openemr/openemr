<?php

// Copyright (C) 2011 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This file contains functions to:
//   --manage AMC items in the amc_misc_data sql table
//   --support the AMC Tracking report

// Main function to process items in the amc_misc_data sql table
// Parameter:
//   $amc_id     - amc rule id
//   $complete   - boolean for whether to complete the date_completed row
//   $mode       - 'add' or 'remove'
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
//   $date_created - specifically for the uncomplete_safe mode only to ensure safe for duplicate entries.
function processAmcCall($amc_id, $complete, $mode, $patient_id, $object_category = '', $object_id = '0', $date_created = '')
{

  // Ensure empty variables are set correctly
    if (empty($object_category)) {
        $object_category = '';
    }

    if (empty($date_created)) {
        $date_created = '';
    }

    if (empty($object_id)) {
        $object_id = '0';
    }

  // Ensure $complete is a boolean
  // (since this is run via javascript/ajax, need to convert the boolean)
    if (!($complete === true || $complete === false)) {
        if ($complete === "true") {
            $complete = true;
        }

        if ($complete === "false") {
            $complete = false;
        }
    }

    if ($mode == "add") {
        amcAdd($amc_id, $complete, $patient_id, $object_category, $object_id);
    }

    if ($mode == "add_force") {
        amcAddForce($amc_id, $complete, $patient_id, $object_category, $object_id);
    } elseif ($mode == "remove") {
        amcRemove($amc_id, $patient_id, $object_category, $object_id);
    } elseif ($mode == "complete") {
        amcComplete($amc_id, $patient_id, $object_category, $object_id);
    } elseif ($mode == "complete_safe") {
        amcCompleteSafe($amc_id, $patient_id, $object_category, $object_id, $date_created);
    } elseif ($mode == "uncomplete") {
        amcUnComplete($amc_id, $patient_id, $object_category, $object_id);
    } elseif ($mode == "uncomplete_safe") {
        amcUnCompleteSafe($amc_id, $patient_id, $object_category, $object_id, $date_created);
    } elseif ($mode == "soc_provided") {
        amcSoCProvided($amc_id, $patient_id, $object_category, $object_id);
    } elseif ($mode == "no_soc_provided") {
        amcNoSoCProvided($amc_id, $patient_id, $object_category, $object_id);
    } else {
        // do nothing
        return;
    }
}

// Function to add an item to the amc_misc_data sql table
//   $amc_id     - amc rule id
//   $complete   - boolean for whether to complete the date_completed row
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcAdd($amc_id, $complete, $patient_id, $object_category = '', $object_id = '0')
{

  // Attempt to collect the item
    $item = amcCollect($amc_id, $patient_id, $object_category, $object_id);

    if (empty($item)) {
        // does not yet exist, so add the item
        $sqlBindArray = array($amc_id,$patient_id,$object_category,$object_id);
        if ($complete) {
            sqlStatement("INSERT INTO `amc_misc_data` (`amc_id`,`pid`,`map_category`,`map_id`,`date_created`,`date_completed`) VALUES(?,?,?,?,NOW(),NOW())", $sqlBindArray);
        } else {
            sqlStatement("INSERT INTO `amc_misc_data` (`amc_id`,`pid`,`map_category`,`map_id`,`date_created`) VALUES(?,?,?,?,NOW())", $sqlBindArray);
        }
    } else {
        // already exist, so only ensure complete date is set (if applicable)
        if ($complete) {
            amcComplete($amc_id, $patient_id, $object_category, $object_id);
        }
    }
}

// Function to add an item to the amc_misc_data sql table
//  This function will allow duplicates (unlike the above amcAdd function)
//   $amc_id     - amc rule id
//   $complete   - boolean for whether to complete the date_completed row
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcAddForce($amc_id, $complete, $patient_id, $object_category = '', $object_id = '0')
{

  // add the item
    $sqlBindArray = array($amc_id,$patient_id,$object_category,$object_id);
    if ($complete) {
        sqlStatement("INSERT INTO `amc_misc_data` (`amc_id`,`pid`,`map_category`,`map_id`,`date_created`,`date_completed`) VALUES(?,?,?,?,NOW(),NOW())", $sqlBindArray);
    } else {
        sqlStatement("INSERT INTO `amc_misc_data` (`amc_id`,`pid`,`map_category`,`map_id`,`date_created`) VALUES(?,?,?,?,NOW())", $sqlBindArray);
    }
}

// Function to remove an item from the amc_misc_data sql table
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcRemove($amc_id, $patient_id, $object_category = '', $object_id = '0')
{
    sqlStatement("DELETE FROM `amc_misc_data` WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=?", array($amc_id,$patient_id,$object_category,$object_id));
}

// Function to complete an item from the amc_misc_data sql table
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcComplete($amc_id, $patient_id, $object_category = '', $object_id = '0')
{
    sqlStatement(
        "UPDATE `amc_misc_data` SET `date_completed`=NOW() WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=? AND " .
        dateEmptySql('date_completed', true),
        array($amc_id,$patient_id,$object_category,$object_id)
    );
}

// Function to complete an item from the amc_misc_data sql table
//  As opposed to above function, this is safe for when expect to have duplicate entries
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
//   $date_created - date created.
function amcCompleteSafe($amc_id, $patient_id, $object_category = '', $object_id = '0', $date_created = '')
{
    sqlStatement("UPDATE `amc_misc_data` SET `date_completed`=NOW() WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=? AND" .
        dateEmptySql('date_completed', true) .
        "AND `date_created`=?", array($amc_id,$patient_id,$object_category,$object_id,$date_created));
}

// Function to remove completion date/flag from  an item in the amc_misc_data sql table
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcUnComplete($amc_id, $patient_id, $object_category = '', $object_id = '0')
{
    sqlStatement("UPDATE `amc_misc_data` SET `date_completed`=NULL WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=?", array($amc_id,$patient_id,$object_category,$object_id));
}

// Function to remove completion date/flag from  an item in the amc_misc_data sql table
//  As opposed to above function, this is safe for when expect to have duplicate entries
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
//   $date_created - date created.
function amcUnCompleteSafe($amc_id, $patient_id, $object_category = '', $object_id = '0', $date_created = '')
{
    sqlStatement("UPDATE `amc_misc_data` SET `date_completed`=NULL WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=? AND `date_created`=?", array($amc_id,$patient_id,$object_category,$object_id,$date_created));
}

// Function to complete an item from the amc_misc_data sql table
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcCollect($amc_id, $patient_id, $object_category = '', $object_id = '0')
{
    return sqlQuery("SELECT * FROM `amc_misc_data` WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=?", array($amc_id,$patient_id,$object_category,$object_id));
}

// Function to support the AMC tracking report
// $amc_id      - amc rule id
// $start       - date start range
// $end         - date end range
// $provider_id - provider id
function amcTrackingRequest($amc_id, $start = '', $end = '', $provider_id = '')
{
    $where = '';

  # Collect the patient list first (from the provider)
    $patients = array();
    if (empty($provider)) {
        // Look at entire practice
        $rez = sqlStatement("SELECT `pid`, `fname`, `lname` FROM `patient_data`");
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $patients[$iter] = $row;
        }
    } else {
        // Look at one provider
        $rez = sqlStatement("SELECT `pid`, `fname`, `lname` FROM `patient_data` " .
        "WHERE providerID=?", array($provider));
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
             $patients[$iter] = $row;
        }
    }

    $results = array();
    foreach ($patients as $patient) {
        $tempResults = array();

        if ($amc_id == "send_sum_amc") {
            $sqlBindArray = array();
            array_push($sqlBindArray, $patient['pid']);
            if (!(empty($start))) {
                $where = " AND `date`>=? ";
                array_push($sqlBindArray, $start);
            }

            if (!(empty($end))) {
                $where .= " AND `date`<=? ";
                array_push($sqlBindArray, $end);
            }

            $rez = sqlStatement("SELECT `id`, `date` FROM `transactions` WHERE `title` = 'LBTref' AND `pid` = ? $where ORDER BY `date` DESC", $sqlBindArray);
            while ($res = sqlFetchArray($rez)) {
                $amcCheck = amcCollect("send_sum_amc", $patient['pid'], "transactions", $res['id']);
                if (empty($amcCheck)) {
                    // Records have not been sent, so send this back
                    array_push($tempResults, array("pid" => $patient['pid'], "fname" => $patient['fname'], "lname" => $patient['lname'], "date" => $res['date'], "id" => $res['id']));
                }
            }
        } elseif ($amc_id == "provide_rec_pat_amc") {
            $sqlBindArray = array();
            array_push($sqlBindArray, $patient['pid']);
            if (!(empty($start))) {
                $where = " AND `date_created`>=? ";
                array_push($sqlBindArray, $start);
            }

            if (!(empty($end))) {
                $where .= " AND `date_created`<=? ";
                array_push($sqlBindArray, $end);
            }

            $rez = sqlStatement("SELECT * FROM `amc_misc_data` WHERE `amc_id`='provide_rec_pat_amc' AND `pid`=? AND " .
            dateEmptySql('date_completed', true) .
            "$where ORDER BY `date_created` DESC", $sqlBindArray);
            while ($res = sqlFetchArray($rez)) {
                // Records have not been sent, so send this back
                array_push($tempResults, array("pid" => $patient['pid'], "fname" => $patient['fname'], "lname" => $patient['lname'], "date" => $res['date_created']));
            }
        } elseif ($amc_id == "provide_sum_pat_amc") {
            $sqlBindArray = array();
            array_push($sqlBindArray, $patient['pid']);
            if (!(empty($start))) {
                $where = " AND `date`>=? ";
                array_push($sqlBindArray, $start);
            }

            if (!(empty($end))) {
                $where .= " AND `date`<=? ";
                array_push($sqlBindArray, $end);
            }

            $rez = sqlStatement("SELECT `encounter`, `date` FROM `form_encounter` WHERE `pid`=? $where ORDER BY `date` DESC", $sqlBindArray);
            while ($res = sqlFetchArray($rez)) {
                $amcCheck = amcCollect("provide_sum_pat_amc", $patient['pid'], "form_encounter", $res['encounter']);
                if (empty($amcCheck)) {
                    // Records have not been given, so send this back
                    array_push($tempResults, array("pid" => $patient['pid'], "fname" => $patient['fname'], "lname" => $patient['lname'], "date" => $res['date'], "id" => $res['encounter']));
                }
            }
        } else {
            // report nothing
            return;
        }

        // process results
        $results = array_merge($results, $tempResults);
    }

  // send results
    return $results;
}

// The function returns the no. of business days between two dates and it skips the holidays
// $start in YYYY-MM-DD
// $end in YYYY-MM-DD
// $holiday is an array containing YYYY-MM-DD
function businessDaysDifference($startDate, $endDate, $holidays = array())
{
  //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
  //We add one to include both dates in the interval.
    $days = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

  //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", strtotime($startDate));
    $the_last_day_of_week = date("N", strtotime($endDate));

  //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
  //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) {
            $no_remaining_days--;
        }

        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) {
            $no_remaining_days--;
        }
    } else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        } else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

  //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
  //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
    $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0) {
        $workingDays += $no_remaining_days;
    }

  //We subtract the holidays
    foreach ($holidays as $holiday) {
        $time_stamp = strtotime($holiday);
        //If the holiday doesn't fall in weekend
        if (strtotime($startDate) <= $time_stamp && $time_stamp <= strtotime($endDate) && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7) {
            $workingDays--;
        }
    }

    return $workingDays;
}

// Function to set summary of care provided for a encounter/patient from the amc_misc_data sql table
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcSoCProvided($amc_id, $patient_id, $object_category = '', $object_id = '0')
{
         sqlStatement("UPDATE `amc_misc_data` SET `soc_provided` = NOW() WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=? ", array($amc_id,$patient_id,$object_category,$object_id));
}
// Function to set summary of care provided for a encounter/patient from the amc_misc_data sql table
//   $amc_id     - amc rule id
//   $patient_id - pid
//   $object_category - specific item category (such as prescriptions, transactions etc.)
//   $object_id  - specific item id (such as encounter id, prescription id, etc.)
function amcNoSoCProvided($amc_id, $patient_id, $object_category = '', $object_id = '0')
{
         sqlStatement("UPDATE `amc_misc_data` SET `soc_provided` = NULL WHERE `amc_id`=? AND `pid`=? AND `map_category`=? AND `map_id`=? ", array($amc_id,$patient_id,$object_category,$object_id));
}
