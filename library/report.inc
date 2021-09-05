<?php

/**
 * report.inc
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS["srcdir"] . "/options.inc.php");

use OpenEMR\Common\Acl\AclMain;

$patient_data_array = array(
'title' => xl('Title') . ": ",
'fname' => xl('First Name') . ": ",
'mname' => xl('Middle Name') . ": ",
'lname' => xl('Last Name') . ": ",
'sex' => xl('Sex') . ": ",
'ss' => xl('SS') . ": ",
'DOB' => xl('Date of Birth') . ": ",
'street' => xl('Street') . ": ",
'city' => xl('City') . ": ",
'state' => xl('State') . ": ",
'postal_code' => xl('Zip') . ": ",
'country_code' => xl('Country') . ": ",
'occupation' => xl('Occupation') . ": ",
'phone_home' => xl('Home Phone') . ": ",
'phone_biz' => xl('Business Phone') . ": ",
'phone_contact' => xl('Contact Phone') . ": ",
'contact_relationship' => xl('Contact Person') . ": ",
'hipaa_mail' => xl('Allows Mail') . ": ",
'hipaa_voice' => xl('Allows Voice msgs') . ": ",
'hipaa_notice' => xl('Notice Received') . ": ",
'hipaa_message' => xl('Leave Message With') . ": "
);

$history_data_array = array(
'coffee' => xl('Coffee Use') . ": ",
'tobacco' => xl('Tobacco Use') . ": ",
'alcohol' => xl('Alcohol Use') . ": ",
'sleep_patterns' => xl('Sleep Patterns') . ": ",
'exercise_patterns' => xl('Exercise Patterns') . ": ",
'seatbelt_use' => xl('Seatbelt Use') . ": ",
'counseling' => xl('Counseling') . ": ",
'hazardous_activities' => xl('Hazardous Activities') . ": ",
'last_breast_exam' => xl('Last Breast Exam') . ": ",
'last_mammogram' => xl('Last Mammogram') . ": ",
'last_gynocological_exam' => xl('Last Gyn. Exam') . ": ",
'last_rectal_exam' => xl('Last Rectal Exam') . ": ",
'last_prostate_exam' => xl('Last Prostate Exam') . ": ",
'last_physical_exam' => xl('Last Physical Exam') . ": ",
'last_sigmoidoscopy_colonoscopy' => xl('Last Sigmoid/Colonoscopy') . ": ",
'cataract_surgery' => xl('Last Cataract Surgery') . ": ",
'tonsillectomy' => xl('Last Tonsillectomy') . ": ",
'cholecystestomy' => xl('Last Cholecystestomy') . ": ",
'heart_surgery' => xl('Last Heart Surgery') . ": ",
'hysterectomy' => xl('Last Hysterectomy') . ": ",
'hernia_repair' => xl('Last Hernia Repair') . ": ",
'hip_replacement' => xl('Last Hip Replacement') . ": ",
'knee_replacement' => xl('Last Knee Replacement') . ": ",
'appendectomy' => xl('Last Appendectomy') . ": ",
'history_mother' => xl('Mothers History') . ": ",
'history_father' => xl('Fathers History') . ": ",
'history_siblings' => xl('Sibling History') . ": ",
'history_offspring' => xl('Offspring History') . ": ",
'history_spouse' => xl('Spouses History') . ": ",
'relatives_cancer' => xl('Relatives Cancer') . ": ",
'relatives_tuberculosis' => xl('Relatives Tuberculosis') . ": ",
'relatives_diabetes' => xl('Relatives Diabetes') . ": ",
'relatives_high_blood_pressure' => xl('Relatives Blood Pressure') . ": ",
'relatives_heart_problems' => xl('Relatives Heart') . ": ",
'relatives_stroke' => xl('Relatives Stroke') . ": ",
'relatives_epilepsy' => xl('Relatives Epilepsy') . ": ",
'relatives_mental_illness' => xl('Relatives Mental Illness') . ": ",
'relatives_suicide' => xl('Relatives Suicide') . ": "
);

$employer_data_array = array(
'name' => xl('Employer') . ": ",
'street' => xl('Address') . ": ",
'city' => xl('City') . ": ",
'postal_code' => xl('Zip') . ": ",
'state' => xl('State') . ": ",
'country' => xl('Country') . ": "
);

$insurance_data_array = array(
'provider_name' => xl('Provider') . ": ",
'plan_name' => xl('Plan Name') . ": ",
'policy_number' => xl('Policy Number') . ": ",
'group_number' => xl('Group Number') . ": ",
'subscriber_fname' => xl('Subscriber First Name') . ": ",
'subscriber_mname' => xl('Subscriber Middle Name') . ": ",
'subscriber_lname' => xl('Subscriber Last Name') . ": ",
'subscriber_relationship' => xl('Subscriber Relationship') . ": ",
'subscriber_ss' => xl('Subscriber SS') . ": ",
'subscriber_DOB' => xl('Subscriber Date of Birth') . ": ",
'subscriber_phone' => xl('Subscribter Phone') . ": ",
'subscriber_street' => xl('Subscriber Address') . ": ",
'subscriber_postal_code' => xl('Subscriber Zip') . ": ",
'subscriber_city' => xl('Subscriber City') . ": ",
'subscriber_state' => xl('Subscriber State') . ": ",
'subscriber_country' => xl('Subscriber Country') . ": ",
'subscriber_employer' => xl('Subscriber Employer') . ": ",
'subscriber_employer_street' => xl('Subscriber Employer Street') . ": ",
'subscriber_employer_city' => xl('Subscriber Employer City') . ": ",
'subscriber_employer_postal_code' => xl('Subscriber Employer Zip') . ": ",
'subscriber_employer_state' => xl('Subscriber Employer State') . ": ",
'subscriber_employer_country' => xl('Subscriber Employer Country') . ": "
);

function getPatientReport($pid)
{
    $sql = "select * from patient_data where pid=? order by date ASC";
    $res = sqlStatement($sql, array($pid));
    while ($list = sqlFetchArray($res)) {
        foreach ($list as $key => $value) {
            if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date']) {
                $ret[$key]['title'] = $key;
                $ret[$key]['content'] = $value;
                $ret[$key]['date'] = $list['date'];
            }
        }
    }

    return $ret;
}

function getHistoryReport($pid)
{
    $sql = "select * from history_data where pid=? order by date ASC";
    $res = sqlStatement($sql, array($pid));
    while ($list = sqlFetchArray($res)) {
        foreach ($list as $key => $value) {
            if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date']) {
                $ret[$key]['content'] = $value;
                $ret[$key]['date'] = $list['date'];
            }
        }
    }

    return $ret;
}

function getInsuranceReport($pid, $type = "primary")
{
    $sql = "select * from insurance_data where pid=? and type=? order by date ASC";
    $res = sqlStatement($sql, array($pid, $type));
    while ($list = sqlFetchArray($res)) {
        foreach ($list as $key => $value) {
            if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date']) {
                $ret[$key]['content'] = $value;
                $ret[$key]['date'] = $list['date'];
            }
        }
    }

    return $ret;
}

function getEmployerReport($pid)
{
    $sql = "select * from employer_data where pid=? order by date ASC";
    $res = sqlStatement($sql, array($pid));
    while ($list = sqlFetchArray($res)) {
        foreach ($list as $key => $value) {
            if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date']) {
                $ret[$key]['content'] = $value;
                $ret[$key]['date'] = $list['date'];
            }
        }
    }

    return $ret;
}

function getListsReport($id)
{
    $sql = "select * from lists where id=? order by date ASC";
    $res = sqlStatement($sql, array($id));
    while ($list = sqlFetchArray($res)) {
        foreach ($list as $key => $value) {
            if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date']) {
                $ret[$key]['content'] = $value;
                $ret[$key]['date'] = $list['date'];
            }
        }
    }

    return $ret;
}

function printListData($pid, $list_type, $list_activity = "%")
{
    $res = sqlStatement("select * from lists where pid=? and type=? and activity like ? order by date", array($pid, $list_type, $list_activity));
    while ($result = sqlFetchArray($res)) {
        print "<span class='bold'>" . text($result["title"]) . ":</span><span class='text'> " . text($result["comments"]) . "</span><br />\n";
    }
}

function printPatientNotes($pid)
{
  // exclude ALL deleted notes
    $res = sqlStatement("select * from pnotes where pid = ? and deleted != 1 and activity = 1 order by date", array($pid));
    while ($result = sqlFetchArray($res)) {
        print "<span class='bold'>" . text(oeFormatSDFT(strtotime($result["date"]))) .
        ":</span><span class='text'> " .
            nl2br(text(oeFormatPatientNote($result['body']))) . "</span><br />\n";
    }
}

// Get the current value for a layout based transaction field.
//
function lbt_current_value($frow, $formid)
{
    $formname = $frow['form_id'];
    $field_id = $frow['field_id'];
    $currvalue = '';
    if ($formid) {
        $ldrow = sqlQuery("SELECT field_value FROM lbt_data WHERE " .
        "form_id = ? AND field_id = ?", array($formid, $field_id));
        if (!empty($ldrow)) {
            $currvalue = $ldrow['field_value'];
        }
    }

    return $currvalue;
}

// Display a particular transaction.
//
function lbt_report($id, $formname)
{
    $arr = array();
    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = ? AND uor > 0 " .
    "ORDER BY group_id, seq", array($formname));
    while ($frow = sqlFetchArray($fres)) {
        $field_id  = $frow['field_id'];
        $currvalue = lbt_current_value($frow, $id);
        // For brevity, skip fields without a value.
        if ($currvalue === '') {
            continue;
        }

        $arr[$field_id] = wordwrap($currvalue, 30, "\n", true);
    }

    echo "<table>\n";
    display_layout_rows($formname, $arr);
    echo "</table>\n";
}

// Display all transactions for the specified patient.
//
function printPatientTransactions($pid)
{
    $res = sqlStatement("SELECT * FROM transactions WHERE pid = ? ORDER BY date", array($pid));
    while ($row = sqlFetchArray($res)) {
        echo "<p><span class='bold'>" .
        text(oeFormatSDFT(strtotime($row['date']))) .
        " (" .
        generate_display_field(array('data_type' => '1','list_id' => 'transactions'), $row['title']) .
        ")</span><br />\n";
        lbt_report($row['id'], $row['title']);
        echo "</p>\n";
    }
}

function printPatientBilling($pid)
{
    $res = sqlStatement("select * from billing where pid=? order by date", array($pid));
    while ($result = sqlFetchArray($res)) {
        echo "<span class='bold'>" . text(oeFormatSDFT(strtotime($result["date"]))) . " : </span>";
        echo "<span class='text'>(" . text($result["code_type"]) . ") ";
        echo $result['code_type'] == 'COPAY' ? text(oeFormatMoney($result['code'])) : (text($result['code']) . ":" . text($result['modifier']));
        echo " - " . wordwrap(text($result['code_text']), 70, "\n", true) . "</span>";
        echo "<br />\n";
    }
}

function getPatientBillingEncounter($pid, $encounter)
{
    $erow = sqlQuery("SELECT provider_id FROM form_encounter WHERE " .
    "pid = ? AND encounter = ? " .
    "ORDER BY id DESC LIMIT 1", array($pid, $encounter));
    $inv_provider = $erow['provider_id'] + 0;
    $sql = "SELECT b.*, u.id, u.fname, u.mname, u.lname, " .
    "CONCAT(u.fname,' ', u.lname) AS provider_name, u.federaltaxid " .
    "FROM billing AS b " .
    "LEFT JOIN users AS u ON " .
    "( b.provider_id != 0 AND u.id = b.provider_id ) OR " .
    "( b.provider_id  = 0 AND u.id = ? ) " .
    "WHERE pid= ? AND " .
    "encounter = ? " .
    "AND activity = '1' ORDER BY date";

    $res = sqlStatement($sql, array($inv_provider, $pid, $encounter));
    $billings = array();
    while ($result = sqlFetchArray($res)) {
        $billings[] = $result;
    }

    return $billings;
}

function printPatientForms($pid, $cols)
{
    //this function takes a $pid
    $inclookupres = sqlStatement("select distinct formdir from forms where pid=? AND deleted=0", array($pid));
    while ($result = sqlFetchArray($inclookupres)) {
        include_once($GLOBALS['incdir'] . "/forms/" . $result["formdir"] . "/report.php");
    }

    $res = sqlStatement("select * from forms where pid=? AND deleted=0 order by date", array($pid));
    while ($result = sqlFetchArray($res)) {
        if ($result["form_name"] == "New Patient Encounter") {
            echo "<div class='text encounter'>\n";
            echo "<h1>" . text($result["form_name"]) . "</h1>";

            // display the provider info
            $tmp = sqlQuery("SELECT u.title, u.fname, u.mname, u.lname " .
                                    "FROM forms AS f, users AS u WHERE " .
                                    "f.pid = ? AND f.encounter = ? AND " .
                                    "f.formdir = 'newpatient' AND u.username = f.user " .
                                    " AND f.deleted=0 " . //--JRM--
                                    "ORDER BY f.id LIMIT 1", array($pid, $result['encounter']));
            echo " " . xlt('Provider') . ": " . text($tmp['title']) . " " .
                text($tmp['fname']) . " " . text($tmp['mname']) . " " . text($tmp['lname']);
            echo "<br/>";
        } else {
            echo "<div class='text encounter_form'>";
            echo "<h1>" . text($result["form_name"]) . "</h1>";
        }

        echo "(" . text(oeFormatSDFT(strtotime($result["date"]))) . ") ";

        if (AclMain::aclCheckCore('acct', 'rep') || AclMain::aclCheckCore('acct', 'eob') || AclMain::aclCheckCore('acct', 'bill')) {
            if ($result["form_name"] == "New Patient Encounter") {
                // display billing info
                echo "<br/>";
                $bres = sqlStatement(
                    "SELECT b.date, b.code, b.code_text " .
                    "FROM billing AS b, code_types AS ct WHERE " .
                    "b.pid = ? AND " .
                    "b.encounter = ? AND " .
                    "b.activity = 1 AND " .
                    "b.code_type = ct.ct_key AND " .
                    "ct.ct_diag = 0 " .
                    "ORDER BY b.date",
                    array($pid, $result['encounter'])
                );
                while ($brow = sqlFetchArray($bres)) {
                    echo "<span class='bold'>&nbsp;" . xlt('Procedure') . ": </span><span class='text'>" .
                        text($brow['code']) . " " . text($brow['code_text']) . "</span><br />\n";
                }
            }
        }

        call_user_func($result["formdir"] . "_report", $pid, $result["encounter"], $cols, $result["form_id"]);

        echo "</div>";
    }
}

function getRecHistoryData($pid)
{
    //data is returned as a multi-level array:
    //column name->dates->values
    //$return["lname"][0..n]["date"]
    //$return["lname"][0..n]["value"]
    $res = sqlStatement("select * from history_data where pid=? order by date", array($pid));

    while ($result = sqlFetchArray($res)) {
        foreach ($result as $key => $val) {
            if ($key == "pid" || $key == "date" || $key == "id") {
                continue;
            } else {
                $curdate = $result["date"];
                if (($retar[$key][$arcount[$key]]["value"] != $val)) {
                    $arcount[$key]++;
                    $retar[$key][$arcount[$key]]["value"] = $val;
                    $retar[$key][$arcount[$key]]["date"] = $curdate;
                }
            }
        }
    }

    return $retar;
}

function getRecEmployerData($pid)
{
    //data is returned as a multi-level array:
    //column name->dates->values
    //$return["lname"][0..n]["date"]
    //$return["lname"][0..n]["value"]
    $res = sqlStatement("select * from employer_data where pid=? order by date", array($pid));

    $retar = [];
    while ($result = sqlFetchArray($res)) {
        foreach ($result as $key => $val) {
            if ($key == "pid" || $key == "date" || $key == "id") {
                continue;
            } else {
                $curdate = $result["date"];

                $arcount[$key] = $arcount[$key] ?? null;
                if (empty($retar[$key][$arcount[$key]]["value"]) || ($retar[$key][$arcount[$key]]["value"] != $val)) {
                    $arcount[$key]++;
                    $retar[$key][$arcount[$key]]["value"] = $val;
                    $retar[$key][$arcount[$key]]["date"] = $curdate;
                }
            }
        }
    }

    return $retar;
}

function getRecPatientData($pid)
{
    //data is returned as a multi-level array:
    //column name->dates->values
    //$return["lname"][0..n]["date"]
    //$return["lname"][0..n]["value"]
    $res = sqlStatement("select * from patient_data where pid=? order by date", array($pid));

    $retar = [];
    $arcount = [];
    while ($result = sqlFetchArray($res)) {
        foreach ($result as $key => $val) {
            if ($key == "pid" || $key == "date" || $key == "id" || $key == "uuid") {
                continue;
            } else {
                $curdate = $result["date"];
                $arcount[$key] = $arcount[$key] ?? null;
                if (($retar[$key][$arcount[$key]]["value"] ?? '') != $val) {
                    $arcount[$key] = (!empty($arcount[$key])) ? ($arcount[$key] + 1) : 1;
                    $retar[$key][$arcount[$key]]["value"] = $val ?? '';
                    $retar[$key][$arcount[$key]]["date"] = $curdate;
                }
            }
        }
    }

    return $retar;
}

function getRecInsuranceData($pid, $ins_type)
{
    //data is returned as a multi-level array:
    //column name->dates->values
    //$return["lname"][0..n]["date"]
    //$return["lname"][0..n]["value"]
    $res = sqlStatement("select *, ic.name as provider_name from insurance_data left join insurance_companies as ic on ic.id = provider where pid=? and type=? order by date", array($pid,$ins_type));

    $retar = [];
    $arcount = [];
    while ($result = sqlFetchArray($res)) {
        foreach ($result as $key => $val) {
            if ($key == "pid" || $key == "date" || $key == "id" || $key == "uuid") {
                continue;
            } else {
                $curdate = $result["date"];
                $arcount[$key] = $arcount[$key] ?? null;
                if (($retar[$key][$arcount[$key]]["value"] ?? '') != $val) {
                    $arcount[$key] = (!empty($arcount[$key])) ? ($arcount[$key] + 1) : 1;
                    $retar[$key][$arcount[$key]]["value"] = $val ?? '';
                    $retar[$key][$arcount[$key]]["date"] = $curdate;
                }
            }
        }
    }

    return $retar;
}

function printRecData($data_array, $recres, $N)
{
    //this function generates a formatted history of all changes to the data
    //it is a multi-level recursive function that exhaustively displays all of
    //the changes, with dates, of any data in the database under the given
    //argument restrictions.
    //$data_array is an array with table_column_name => "display name"
    //$recres is the return from getRecPatientData for example
    //$N is the number of items to display in one row
    print "<table><tr>\n";
    $count = 0;
    foreach ($data_array as $akey => $aval) {
        if ($count == $N) {
            print "</tr><tr>\n";
            $count = 0;
        }

        print "<td valign='top'><span class='bold'>" . text($aval) . "</span><br /><span class=text>";
        printData($recres, $akey, "<br />", "Y-m-d");
        print "</span></td>\n";
        $count++;
    }

    print "</tr></table>\n";
}

function printData($retar, $key, $sep, $date_format)
{
    //$retar[$key]
    if (@array_key_exists($key, $retar)) {
        $length = sizeof($retar[$key]);
        for ($iter = $length; $iter >= 1; $iter--) {
            if ($retar[$key][$iter]["value"] != "0000-00-00 00:00:00") {
                print text($retar[$key][$iter]["value"]) . " (" . text(oeFormatSDFT(strtotime($retar[$key][$iter]["date"]))) . ")$sep";
            }
        }
    }
}

function printRecDataOne($data_array, $recres, $N)
{
    //this function is like printRecData except it will only print out those elements that
    //have values. when they do have values, this function will only print out the most recent
    //value of each element.
    //this may be considered a compressed data viewer.
    //this function generates a formatted history of all changes to the data
    //$data_array is an array with table_column_name => "display name"
    //$recres is the return from getRecPatientData for example
    //$N is the number of items to display in one row
    print "<table><tr>\n";
    $count = 0;
    foreach ($data_array as $akey => $aval) {
        if (!empty($recres[$akey]) && sizeof($recres[$akey]) > 0 && ($recres[$akey][1]["value"] != "0000-00-00 00:00:00")) {
            if ($count == $N) {
                print "</tr><tr>\n";
                $count = 0;
            }

            print "<td valign='top'><span class='bold'>" . text($aval) . "</span><br /><span class='text'>";
            printDataOne($recres, $akey, "<br />", "Y-m-d");
            print "</span></td>\n";
            $count++;
        }
    }

    print "</tr></table>\n";
}

function printDataOne($retar, $key, $sep, $date_format)
{
    //this function supports the printRecDataOne function above
    if (@array_key_exists($key, $retar)) {
        $length = sizeof($retar[$key]);
        if ($retar[$key][$length]["value"] != "0000-00-00 00:00:00") {
            $tmp = $retar[$key][$length]["value"];
            if (strstr($key, 'DOB')) {
                $tmp = oeFormatShortDate($tmp);
            }

            print text($tmp) . $sep;
        }
    }
}
