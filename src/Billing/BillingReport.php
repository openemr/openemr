<?php

/**
 * This provides helper functions for the billing report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

class BillingReport
{
    public static function generateTheQueryPart($daysheet = false)
    {
        global $query_part, $query_part2, $billstring, $auth;

        if ($daysheet) {
            global $query_part_day, $query_part_day1;
            $query_part_day = '';
            $query_part_day1 = '';
        }

        //Search Criteria section.
        $billstring = '';
        $auth = '';
        $query_part = '';
        $query_part2 = '';
        if (isset($_REQUEST['final_this_page_criteria'])) {
            foreach ($_REQUEST['final_this_page_criteria'] as $criteria_key => $criteria_value) {
                //---------------------------------------------------------
                if (strpos($criteria_value, "billing.billed|=|1") !== false) {
                    $billstring .= ' AND ' . "billing.billed = '1'";
                } elseif (strpos($criteria_value, "billing.billed|=|0") !== false) {
                    //3 is an error condition
                    $billstring .= ' AND ' . "(billing.billed = '0' or (billing.billed = '1' and billing.bill_process = '3'))";
                } elseif (strpos($criteria_value, "billing.billed|=|7") !== false) {
                    $billstring .= ' AND ' . "billing.bill_process = '7'";
                } elseif (strpos($criteria_value, "billing.id|=|null") !== false) {
                    $billstring .= ' AND ' . "billing.id is null";
                } elseif (strpos($criteria_value, "billing.id|=|not null") !== false) {
                    $billstring .= ' AND ' . "billing.id is not null";
                } elseif (strpos($criteria_value, "patient_data.fname|like|") !== false) {
                    $elements = explode('|', $criteria_value);
                    $query_part .= " AND (patient_data.fname like '" . add_escape_custom($elements[2]) . "' or patient_data.lname like '" . add_escape_custom($elements[2]) . "')";
                } elseif (strpos($criteria_value, "form_encounter.pid|=|") !== false) {//comes like '781,780'
                    $elements = explode('|', $criteria_value);
                    $patients = explode(',', $elements[2]);
                    $sanitizedPatients = '';
                    foreach ($patients as $patient) {
                        $sanitizedPatients .= "'" . add_escape_custom($patient) . "',";
                    }
                    $sanitizedPatients = substr($sanitizedPatients, 0, -1);
                    $query_part .= ' AND form_encounter.pid in (' . $sanitizedPatients . ')';
                    $query_part2 .= ' AND pid in (' . $sanitizedPatients . ')';
                } elseif (strpos($criteria_value, "form_encounter.encounter|=|") !== false) {//comes like '781,780'
                    $elements = explode('|', $criteria_value);
                    $encounters = explode(',', $elements[2]);
                    $sanitizedEncounters = '';
                    foreach ($encounters as $encounter) {
                        $sanitizedEncounters .= "'" . add_escape_custom($encounter) . "',";
                    }
                    $sanitizedEncounters = substr($sanitizedEncounters, 0, -1);
                    $query_part .= ' AND form_encounter.encounter in (' . $sanitizedEncounters . ')';
                } elseif (strpos($criteria_value, "insurance_data.provider|=|1") !== false) {
                    $query_part .= ' AND ' . "insurance_data.provider > '0' and (insurance_data.date <= form_encounter.date OR insurance_data.date IS NULL)";
                } elseif (strpos($criteria_value, "insurance_data.provider|=|0") !== false) {
                    $query_part .= ' AND ' . "(insurance_data.provider = '0' or insurance_data.date > form_encounter.date)";
                } elseif (strpos($criteria_value, "form_encounter.date|between|") !== false) {
                    $elements = explode('|', $criteria_value);
                    $query_part .= ' AND ' . "(form_encounter.date between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                    if ($daysheet) {
                        $query_part_day .= ' AND ' . "(ar_activity.post_time between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                        $query_part_day1 .= ' AND ' . "(payments.dtime between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                    }
                } elseif (strpos($criteria_value, "billing.date|between|") !== false) {
                    $elements = explode('|', $criteria_value);
                    $query_part .= ' AND ' . "(billing.date between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                    if ($daysheet) {
                        $query_part_day .= ' AND ' . "(ar_activity.post_time between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                        $query_part_day1 .= ' AND ' . "(payments.dtime between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                    }
                } elseif (strpos($criteria_value, "claims.process_time|between|") !== false) {
                    $elements = explode('|', $criteria_value);
                    $query_part .= ' AND ' . "(claims.process_time between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                    if ($daysheet) {
                        $query_part_day .= ' AND ' . "(ar_activity.post_time between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                        $query_part_day1 .= ' AND ' . "(payments.dtime between '" . add_escape_custom($elements[2]) . "' and '" . add_escape_custom($elements[3]) . "')";
                    }
                } else {
                    $elements = explode('|', $criteria_value);
                    $criteriaItemsWhitelist = [
                        'claims.target',
                        'claims.payer_id',
                        'billing.authorized',
                        'form_encounter.last_level_billed',
                        'billing.x12_partner_id',
                        'billing.user'
                    ];
                    $criteriaComparisonWhitelist = [
                        '=',
                        'like'
                    ];
                    $query_part .= ' AND ' . escape_identifier($elements[0], $criteriaItemsWhitelist, true) . " " . escape_identifier($elements[1], $criteriaComparisonWhitelist, true) . " '" . add_escape_custom($elements[2]) . "'";

                    if (substr($criteria_value, 0, 12) === 'billing.user' && ($daysheet)) {
                        $query_part_day .=  ' AND ' . 'ar_activity.post_user' . " " . escape_identifier($elements[1], $criteriaComparisonWhitelist, true) . " '" . add_escape_custom($elements[2]) . "'";
                    }
                }
            }
        }
    }

    //date must be in nice format (e.g. 2002-07-11)
    public static function getBillsBetween(
        $code_type,
        $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
    ) {
        self::generateTheQueryPart();
        global $query_part, $billstring, $auth;
        // Selecting by the date in the billing table is wrong, because that is
        // just the data entry date; instead we want to go by the encounter date
        // which is the date in the form_encounter table.
        //
        $sql = "SELECT distinct form_encounter.date AS enc_date, form_encounter.pid AS enc_pid, " .
            "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* " .
            "FROM form_encounter " .
            "LEFT OUTER JOIN billing ON " .
            "billing.encounter = form_encounter.encounter AND " .
            "billing.pid = form_encounter.pid AND " .
            "billing.code_type LIKE ? AND " .
            "billing.activity = 1 " .
            "LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid " .
            "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
            "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' " .
            "WHERE 1=1 $query_part  " . " $auth " . " $billstring " .
            "ORDER BY form_encounter.provider_id, form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC";
        //echo $sql;
        $res = sqlStatement($sql, array($code_type));
        $all = false;
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }

        return $all;
    }

    public static function getBillsBetweenReport(
        $code_type,
        $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
    ) {
        self::generateTheQueryPart();
        global $query_part, $query_part2, $billstring, $auth;
        // Selecting by the date in the billing table is wrong, because that is
        // just the data entry date; instead we want to go by the encounter date
        // which is the date in the form_encounter table.
        //
        $sql = "SELECT distinct form_encounter.date AS enc_date, form_encounter.pid AS enc_pid, " .
            "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* " .
            "FROM form_encounter " .
            "LEFT OUTER JOIN billing ON " .
            "billing.encounter = form_encounter.encounter AND " .
            "billing.pid = form_encounter.pid AND " .
            "billing.code_type LIKE ? AND " .
            "billing.activity = 1 " .
            "LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid " .
            "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
            "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' " .
            "WHERE 1=1 $query_part  " . " $auth " . " $billstring " .
            "ORDER BY form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC";
        //echo $sql;
        $res = sqlStatement($sql, array($code_type));
        $all = false;
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $all[$iter] = $row;
        }

        $query = sqlStatement("SELECT pid, 'COPAY' AS code_type, pay_amount AS code, date(post_time) AS date " .
            "FROM ar_activity where deleted IS NULL $query_part2 and payer_type=0 and account_code='PCP'");
        //new fees screen copay gives account_code='PCP' openemr payment screen copay gives code='CO-PAY'
        for ($iter; $row = sqlFetchArray($query); $iter++) {
            $all[$iter] = $row;
        }

        return $all;
    }

    public static function getBillsListBetween(
        $code_type,
        $cols = "billing.id, form_encounter.date, billing.pid, billing.code_type, billing.code, billing.user"
    ) {
        self::generateTheQueryPart();
        global $query_part, $billstring, $auth;
        // See above comment in self::getBillsBetween().
        $array = array();
        $sql = "select distinct $cols " .
            "from form_encounter, billing, patient_data, claims, insurance_data where " .
            "billing.encounter = form_encounter.encounter and " .
            "billing.pid = form_encounter.pid and " .
            "patient_data.pid = form_encounter.pid and " .
            "claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter and " .
            "insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' " .
            $auth .
            $billstring . $query_part . " and " .
            "billing.code_type like ? and " .
            "billing.activity = 1 " .
            "order by billing.pid, billing.date ASC";
        $res = sqlStatement($sql, array($code_type));
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            array_push($array, $row["id"]);
        }
        return $array;
    }

    public static function billCodesList($list, $skip = [])
    {
        if (empty($list)) {
            return;
        }

        $sqlBindArray = array_diff($list, $skip);
        if (empty($sqlBindArray)) {
            return;
        }

        $in = str_repeat('?,', count($sqlBindArray) - 1) . '?';
        sqlStatement("update billing set billed=1 where id in ($in)", $sqlBindArray);

        return;
    }

    public static function returnOFXSql()
    {
        self::generateTheQueryPart();
        global $query_part, $billstring, $auth;

        $sql = "SELECT distinct billing.*, concat(patient_data.fname, ' ', patient_data.lname) as name from billing "
            . "join patient_data on patient_data.pid = billing.pid "
            . "join form_encounter on "
            . "billing.encounter = form_encounter.encounter AND "
            . "billing.pid = form_encounter.pid "
            . "join claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter "
            . "join insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' "
            . "where billed = '1' "
            . "$auth "
            . "$billstring  $query_part  "
            . "order by billing.pid,billing.encounter";

        return $sql;
    }

    //Parses the database value and prepares for display.
    public static function buildArrayForReport($Query)
    {
        $array_data = array();
        $res = sqlStatement($Query);
        while ($row = sqlFetchArray($res)) {
            $array_data[$row['id']] = attr($row['name']);
        }

        return $array_data;
    }

    //The criteria  "Insurance Company" is coded here.The ajax one
    public static function insuranceCompanyDisplay()
    {

        // TPS = This Page Search
        global $TPSCriteriaDisplay, $TPSCriteriaKey, $TPSCriteriaIndex, $web_root;

        echo '<table class="table table-borderless">' .
            '<tr>' .
            '<td colspan="2">' .
            '<iframe id="frame_to_hide" class="position-absolute" style="display:none; width:240px; height:100px" frameborder="0" scrolling="no" marginwidth="0" src="" marginheight="0">hello</iframe>' .
            '<input type="hidden" id="hidden_ajax_close_value" value="' . attr($_POST['type_code'] ?? '') . '" />' .
            '<input name="type_code" id="type_code" class="form-control"' .
            'title="' . xla("Type Id or Name.3 characters minimum (including spaces).") . '"' .
            'onfocus="hide_frame_to_hide();appendOptionTextCriteria(' . attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]) . ',' .
            '' . attr_js($TPSCriteriaKey[$TPSCriteriaIndex]) . ',' .
            'document.getElementById(\'type_code\').value,document.getElementById(\'div_insurance_or_patient\').innerHTML,' .
            '\' = \',' .
            '\'text\')" onblur="show_frame_to_hide()" onKeyDown="PreventIt(event)" value="' . attr($_POST['type_code'] ?? '') . '"  autocomplete="off" /><br />' .
            '<div id="ajax_div_insurance_section">' .
            '<div id="ajax_div_insurance_error"></div>' .
            '<div id="ajax_div_insurance" style="display:none;"></div>' .
            '</div>' .
            '</div>        </td>' .
            '</tr>' .
            '<tr height="5"><td colspan="2"></td></tr>' .
            '<tr>' .
            '<td><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text" style="border:1px solid black; padding-left:5px; width:50px; height:17px;">' . text($_POST['hidden_type_code'] ?? '') . '</div><input type="hidden" name="description"  id="description" /></td>' .
            '<td><a href="#" onClick="CleanUpAjax(' . attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]) . ',' .
            attr_js($TPSCriteriaKey[$TPSCriteriaIndex]) . ',\' = \')"><img src="' . $web_root . '/interface/pic/Clear.gif" border="0" /></a></td>' .
            '</tr>' .
            '</table>' .
            '<input type="hidden" name="hidden_type_code" id="hidden_type_code" value="' . attr($_POST['hidden_type_code'] ?? '') . '"/>';
    }
}
