<?php
require_once(dirname(__FILE__) . "/../interface/reports/report.inc.php");

/**
 * Returns an array, where the zero'th element is the billstring, and any following elements are bound params.
 *
 * @param $keyValues
 * @return array
 */
function GenerateBillstring($keyValues)
{
    $billstring = '';
    $params = [];
    if (isset($keyValues['billing.billed']) && $keyValues['billing.billed'][0] == '=' && $keyValues['billing.billed'][1] === "'1'") {
        $billstring .= ' AND billing.billed ' . $keyValues['billing.billed'][0] . ' ?';
        $params[] = $keyValues['billing.billed'][1];
    } elseif (isset($keyValues['billing.billed']) && $keyValues['billing.billed'][0] == '=' && $keyValues['billing.billed'][1] === "'0'") {
        $billstring .= " AND (billing.billed is null or billing.billed = '0' or (billing.billed = '1' and billing.bill_process = '3'))"; //3 is an error condition
    } elseif (isset($keyValues['billing.billed']) && $keyValues['billing.billed'][0] == '=' && $keyValues['billing.billed'][1] === "'7'") {
        $billstring .= " AND  billing.bill_process = '7'";
    } elseif ((isset($keyValues['billing.billed']) && $keyValues['billing.billed'][0] == '=' && $keyValues['billing.billed'][1] === "'null'")) {
        $billstring .= " AND billing.id is null";
    } elseif ((isset($keyValues['billing.billed']) && $keyValues['billing.billed'][0] == '=' && $keyValues['billing.billed'][1] === "'not null'")) {
        $billstring .= " AND billing.id is not null";
    }
    return array_merge([$billstring], $params);
}

function GenerateQueryPart($keyValues)
{
    $query_part = '';
    $params = [];
    if (isset($keyValues["patient_data.fname"])) {
        $query_part .= ' AND (patient_data.fname ' . $keyValues["patient_data.fname"][0] . ' ? or patient_data.lname like ?)';
        $params[] = $keyValues["patient_data.fname"][1];
        $params[] = $keyValues["patient_data.fname"][1];
    } //---------------------------------------------------------

    if (isset($keyValues['form_encounter.pid'])) {//comes like '781,780'
        $inValues = explode(',', $keyValues['form_encounter.pid'][1]);
        $params = array_merge($params, $inValues);
        $inStringArray = [];
        for ($i = 0; $i < count($inValues); $i++) {
            $inStringArray[] = '?';
        }
        $query_part .= ' AND form_encounter.pid in (' . implode(',', $inStringArray) . ')';
    }

    if (isset($keyValues['form_encounter.encounter'])) {//comes like '781,780'
        $inValues = explode(',', $keyValues['form_encounter.encounter'][1]);
        $params = array_merge($params, $inValues);
        $inStringArray = [];
        for ($i = 0; $i < count($inValues); $i++) {
            $inStringArray[] = '?';
        }
        $query_part .= ' AND form_encounter.encounter in (' . implode(',', $inStringArray) . ')';
    }
    if (isset($keyValues['insurance_data.provider'])) {
        if ($keyValues['insurance_data.provider'][1] === "'1'") {
            $query_part .= " AND insurance_data.provider > '0' and insurance_data.date <= form_encounter.date";
        }
        if ($keyValues['insurance_data.provider'][1] === "'2'") {
            $query_part .= " AND (insurance_data.provider = '0' or insurance_data.date > form_encounter.date)";
        }
    }

    return array_merge([$query_part], $params);
}

function GenerateQueryPartTwo($keyValues)
{
    $queryPartTwo = '';
    $params = [];
    if (isset($keyValues['form_encounter.pid'])) {//comes like '781,780'
        $inValues = explode(',', $keyValues['form_encounter.pid'][1]);
        $params = array_merge($params, $inValues);
        $inStringArray = [];
        for ($i = 0; $i < count($inValues); $i++) {
            $inStringArray[] = '?';
        }
        $queryPartTwo .= ' AND pid in (' . implode(',', $inValues) . ')';
    }

    return array_merge([$queryPartTwo], $params);
}

function GenerateAuthPart($params)
{
    return [(isset($params['billing.authorized'])
        ? ' AND billing.authorized ' . $params['billing.billed'][0] . ' ?'
        : ''
    ), $params['billing.billed'][1]];
}

/**
 * Takes an assortments of string built key value pairs and converts them to a key value array with a 'like' or '='
 * modifier to keep track when build the 'bound' query.
 *
 * @param $finalPageCriteria
 * @return array
 */
function loadParamsFromFinalPageCriteria($finalPageCriteria)
{
    $params = [];
    foreach ($finalPageCriteria as $criteria_key => $SearchItem) {

        if (strstr($SearchItem, ' like ') !== false) {
            $split = explode(' like ', $SearchItem);
            $params[$split[0]] = ['like', "'" . substr($split[1], 1, -1) . "'"];
        } elseif (strstr($SearchItem, ' = ') !== false) {
            $split = explode(' = ', $SearchItem);
            $params[$split[0]] = ['=', "'" . substr($split[1], 1, -1) . "'"];
        } else {
            $split = explode(' ', $SearchItem, 2); //comes like 781,780' - as if for an 'in' statement.
            $params[$split[0]] = ['', substr($split[1], 0, -1)];
        }
    }

    return $params;
}

//date must be in nice format (e.g. 2002-07-11)
function getBillsBetween($code_type, $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id")
{
    $keyValues = isset($_REQUEST['final_this_page_criteria']) ? loadParamsFromFinalPageCriteria($_REQUEST['final_this_page_criteria']) : [];

    list($query_part, $query_part_params) = GenerateQueryPart($keyValues);
    list($auth, $auth_params) = GenerateAuthPart($keyValues);
    list($billstring, $billstring_params) = GenerateBillstring($keyValues);
    $params = array_merge($query_part_params, $auth_params, $billstring_params);

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
    $res = sqlStatement($sql, array_merge([$code_type], $params));
    $all = false;
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getBillsBetweenReport(
    $code_type,
    $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id"
)
{
    $keyValues = isset($_REQUEST['final_this_page_criteria']) ? loadParamsFromFinalPageCriteria($_REQUEST['final_this_page_criteria']) : [];

    list($query_part, $query_part_params) = GenerateQueryPart($keyValues);
    list($auth, $auth_params) = GenerateAuthPart($keyValues);
    list($billstring, $billstring_params) = GenerateBillstring($keyValues);
    list($query_part2, $query_part2_params) = GenerateQueryPartTwo($keyValues);
    $params = array_merge($query_part_params, $auth_params, $billstring_params);

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
    $res = sqlStatement($sql, array_merge([$code_type], $params));
    $all = false;
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }

    $query = sqlStatement("SELECT pid, 'COPAY' AS code_type, pay_amount AS code, date(post_time) AS date " .
        "FROM ar_activity where 1=1 $query_part2 and payer_type=0 and account_code='PCP'");
    //new fees screen copay gives account_code='PCP' openemr payment screen copay gives code='CO-PAY'
    for ($iter; $row = sqlFetchArray($query, $query_part2_params); $iter++) {
        $all[$iter] = $row;
    }

    return $all;
}

function getBillsListBetween(
    $code_type,
    $cols = "billing.id, form_encounter.date, billing.pid, billing.code_type, billing.code, billing.user"
)
{

    $keyValues = isset($_REQUEST['final_this_page_criteria']) ? loadParamsFromFinalPageCriteria($_REQUEST['final_this_page_criteria']) : [];

    list($query_part, $query_part_params) = GenerateQueryPart($keyValues);
    list($auth, $auth_params) = GenerateAuthPart($keyValues);
    list($billstring, $billstring_params) = GenerateBillstring($keyValues);
    list($query_part2, $query_part2_params) = GenerateQueryPartTwo($keyValues);
    $params = array_merge($auth_params, $billstring_params, $query_part_params);

    // See above comment in getBillsBetween().
    //
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

    $res = sqlStatement($sql, array_merge($params, [$code_type]));
    $string = "( ";
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $string .= $row{"id"} . ",";
    }

    $string = substr($string, 0, strlen($string) - 1);
    $string .= ")";
    return $string;
}

function billCodesList($list, $skip = "()")
{
    if ($list == "()") {
        return;
    } else {
        $list = trim($list, '()');
    }

    $params = [];
    $inListValues = explode(',', $list);
    $params = array_merge($params, $inListValues);
    $inListStringArray = [];
    for ($i = 0; $i < count($inListValues); $i++) {
        $inListStringArray[] = '?';
    }

    if ($skip == "()") {
        sqlStatement("update billing set billed=1 where id in (" . implode(',', $inListStringArray) . ")", $params);
    } else {
        $skip = trim($skip, '()');
        $inSkipValues = explode(',', $skip);
        $params = array_merge($params, $inSkipValues);
        $inSkipStringArray = [];
        for ($i = 0; $i < count($inSkipValues); $i++) {
            $inSkipStringArray[] = '?';
        }

        sqlStatement("update billing set billed=1 where id in (" . implode(',', $inListStringArray) . ") and id not in (" . implode(',', $inSkipStringArray) . ")", $params);
    }

    return;
}

function ReturnOFXSql()
{
    $keyValues = isset($_REQUEST['final_this_page_criteria']) ? loadParamsFromFinalPageCriteria($_REQUEST['final_this_page_criteria']) : [];

    list($query_part, $query_part_params) = GenerateQueryPart($keyValues);
    list($auth, $auth_params) = GenerateAuthPart($keyValues);
    list($billstring, $billstring_params) = GenerateBillstring($keyValues);
    $params = array_merge($auth_params, $billstring_params, $query_part_params);

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

    return sqlStatement($sql, $params);
}
