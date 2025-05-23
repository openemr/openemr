<?php

/**
* library/daysheet.inc.php Functions used in the end of day report.
*
* Functions for Generating an End of Day report
*
*
* Copyright (C) 2014-2015 Terry Hill <terry@lillysystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
*
* @package OpenEMR
* @author Terry Hill <terry@lillysystems.com>
* @link https://www.open-emr.org
*/

/**
* @return Returns the array sorted as required
* @param $aryData Array containing data to sort
* @param $strIndex Name of column to use as an index
* @param $strSortBy Column to sort the array by
* @param $strSortType String containing either asc or desc [default to asc]
* @desc Naturally sorts an array using by the column $strSortBy
*/

use OpenEMR\Billing\BillingReport;

function array_natsort($aryData, $strIndex, $strSortBy, $strSortType = false)
{
    //    if the parameters are invalid
    if (!is_array($aryData) || !$strIndex || !$strSortBy) {
        //    return the array
        return $aryData;
    }

    //    create our temporary arrays
    $arySort = $aryResult = array();
    //    loop through the array
    foreach ($aryData as $aryRow) {
        //    set up the value in the array
        $arySort[$aryRow[$strIndex]] = $aryRow[$strSortBy];
    }

    //    apply the natural sort
    natsort($arySort);
    //    if the sort type is descending
    if ($strSortType == "desc") {
        //    reverse the array
        arsort($arySort);
    }

    //    loop through the sorted and original data
    foreach ($arySort as $arySortKey => $arySorted) {
        foreach ($aryData as $aryOriginal) {
            //    if the key matches
            if ($aryOriginal[$strIndex] == $arySortKey) {
                //    add it to the output array
                array_push($aryResult, $aryOriginal);
            }
        }
    }

    //    return the return
    return $aryResult;
}

// date must be in nice format (e.g. 2002-07-11)
function getBillsBetweendayReport(
    $code_type,
    $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
) {

    BillingReport::GenerateTheQueryPart($daysheet = true);
    global $query_part,$query_part2,$query_part_day,$query_part_day1,$billstring,$auth;

    $sql = "SELECT distinct form_encounter.pid AS enc_pid, form_encounter.date AS enc_date, concat(lname, ' ', fname) as 'fulname', lname as 'last', fname as 'first', " .
    "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* , date(billing.date) as date  " .
    "FROM form_encounter " .
    "LEFT OUTER JOIN billing ON " .
    "billing.encounter = form_encounter.encounter AND " .
    "billing.pid = form_encounter.pid AND " .
    "billing.code_type LIKE ? AND " .
    "billing.activity = 1 " .
    "LEFT OUTER JOIN patient_data ON patient_data.pid = form_encounter.pid " .
    "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
    "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' " .
    "WHERE 1=1 $query_part AND billing.fee!=0 " . " $auth " . " $billstring " .
    "ORDER BY  fulname ASC, date ASC, pid ";

    $res = sqlStatement($sql, array($code_type));
    $all = false;
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    $query = sqlStatement("SELECT ar_activity.pid as pid, 'Patient Payment' AS code_type, ar_activity.pay_amount AS pat_code, date(ar_activity.post_time) AS date," .
    "ar_activity.encounter AS encounter_ar, concat(lname, ' ', fname) as 'fulname', lname as 'last', fname as 'first', ar_activity.payer_type AS payer," .
    "ar_activity.session_id AS sesid, ar_activity.account_code AS paytype, ar_activity.post_user AS user, ar_activity.memo AS reason," .
    "ar_activity.adj_amount AS pat_adjust_dollar, providerid as 'provider_id' " .
    "FROM ar_activity LEFT OUTER JOIN patient_data ON patient_data.pid = ar_activity.pid " .
    "WHERE ar_activity.deleted IS NULL $query_part_day AND payer_type = 0 ORDER BY fulname ASC, date ASC, pid");

    for ($iter; $row = sqlFetchArray($query); $iter++) {
        $all[$iter] = $row;
    }

    $query = sqlStatement("SELECT ar_activity.pid as pid, 'Insurance Payment' AS code_type, ar_activity.pay_amount AS ins_code, date(ar_activity.post_time) AS date," .
    "ar_activity.encounter AS encounter_ar, concat(lname, ' ', fname) as 'fulname', lname as 'last', fname as 'first', ar_activity.payer_type AS payer," .
    "ar_activity.session_id AS sesid, ar_activity.account_code AS paytype, ar_activity.post_user AS user, ar_activity.memo AS reason," .
    "ar_activity.adj_amount AS ins_adjust_dollar, providerid as 'provider_id' " .
    "FROM ar_activity LEFT OUTER JOIN patient_data ON patient_data.pid = ar_activity.pid " .
    "WHERE ar_activity.deleted IS NULL $query_part_day AND payer_type != 0 ORDER BY fulname ASC, date ASC, pid");

    for ($iter; $row = sqlFetchArray($query); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}
