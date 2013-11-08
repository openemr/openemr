<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("{$GLOBALS['srcdir']}/sql.inc");
require_once("{$GLOBALS['srcdir']}/acl.inc");
require_once("{$GLOBALS['srcdir']}/formatting.inc.php");
require_once("{$GLOBALS['srcdir']}/formdata.inc.php");

$patient_data_array = array(title => "Title: ",
fname => "First Name: ",
mname => "Middle Name: ",
lname => "Last Name: ",
sex => "Sex",
ss => "SS: ",
DOB => "Date of Birth: ",
street => "Street: ",
city => "City: ",
state => "State: ",
postal_code => "Zip: ",
country_code => "Country: ",
occupation => "Occupation: ",
phone_home => "Home Phone: ",
phone_biz => "Business Phone: ",
phone_contact => "Contact Phone: ",
contact_relationship => "Contact Person",
hipaa_mail => "Allows Mail: ",
hipaa_voice => "Allows Voice msgs: ",
hipaa_notice => "Notice Received: ",
hipaa_message => "Leave Message With: "
);

$history_data_array = array(
coffee => "Coffee Use: ",
tobacco => "Tobacco Use: ",
alcohol => "Alcohol Use: ",
sleep_patterns => "Sleep Patterns: ",
exercise_patterns => "Exercise Patterns: ",
seatbelt_use => "Seatbelt Use: ",
counseling => "Counseling: ",
hazardous_activities => "Hazardous Activities: ",
last_breast_exam => "Last Breast Exam: ",
last_mammogram => "Last Mammogram: ",
last_gynocological_exam => "Last Gyn. Exam: ",
last_rectal_exam => "Last Rectal Exam: ",
last_prostate_exam => "Last Prostate Exam: ",
last_physical_exam => "Last Physical Exam: ",
last_sigmoidoscopy_colonoscopy => "Last Sigmoid/Colonoscopy: ",
cataract_surgery => "Last Cataract Surgery: ",
tonsillectomy => "Last Tonsillectomy: ",
cholecystestomy => "Last Cholecystestomy: ",
heart_surgery => "Last Heart Surgery: ",
hysterectomy => "Last Hysterectomy: ",
hernia_repair => "Last Hernia Repair: ",
hip_replacement => "Last Hip Replacement: ",
knee_replacement => "Last Knee Replacement: ",
appendectomy => "Last Appendectomy: ",
history_mother => "Mother's History: ",
history_father => "Father's History: ",
history_siblings => "Sibling History: ",
history_offspring => "Offspring History: ",
history_spouse => "Spouse's History: ",
relatives_cancer => "Relatives Cancer: ",
relatives_tuberculosis => "Relatives Tuberculosis: ",
relatives_diabetes => "Relatives Diabetes: ",
relatives_high_blood_pressure => "Relatives Blood Pressure: ",
relatives_heart_problems => "Relatives Heart: ",
relatives_stroke => "Relatives Stroke: ",
relatives_epilepsy => "Relatives Epilepsy: ",
relatives_mental_illness => "Relatives Mental Illness: ",
relatives_suicide => "Relatives Suicide: ");

$employer_data_array = array(
name => "Employer: ",
street => "Address: ",
city => "City: ",
postal_code => "Zip: ",
state => "State",
country => "Country: ");

$insurance_data_array = array(
provider_name => "Provider: ",
plan_name => "Plan Name: ",
policy_number => "Policy Number: ",
group_number => "Group Number: ",
subscriber_fname => "Subscriber First Name: ",
subscriber_mname => "Subscriber Middle Name: ",
subscriber_lname => "Subscriber Last Name: ",
subscriber_relationship => "Subscriber Relationship: ",
subscriber_ss => "Subscriber SS: ",
subscriber_DOB => "Subscriber Date of Birth: ",
subscriber_phone => "Subscriber Phone: ",
subscriber_street => "Subscriber Address: ",
subscriber_postal_code => "Subscriber Zip: ",
subscriber_city => "Subscriber City: ",
subscriber_state => "Subscriber State: ",
subscriber_country => "Subscriber Country: ",
subscriber_employer => "Subscriber Employer: ",
subscriber_employer_street => "Subscriber Employer Street: ",
subscriber_employer_city => "Subscriber Employer City: ",
subscriber_employer_postal_code => "Subscriber Employer Zip: ",
subscriber_employer_state => "Subscriber Employer State: ",
subscriber_employer_country => "Subscriber Employer Country: "
);

function getPatientReport($pid)
{
	$sql = "select * from patient_data where pid='$pid' order by date ASC";
	$res = sqlStatement("$sql");
	while($list = sqlFetchArray($res))
	{
		while(list($key, $value) = each($list))
		{
			if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date'])
			{
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
        $sql = "select * from history_data where pid='$pid' order by date ASC";
        $res = sqlStatement("$sql");
        while($list = sqlFetchArray($res))
        {
                while(list($key, $value) = each($list))
                {
                        if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date'])
                        {
                                $ret[$key]['content'] = $value;
                                $ret[$key]['date'] = $list['date'];
                        }
                }
        }
        return $ret;
}

function getInsuranceReport($pid, $type = "primary")
{
        $sql = "select * from insurance_data where pid='$pid' and type='$type' order by date ASC";
        $res = sqlStatement("$sql");
        while($list = sqlFetchArray($res))
        {
                while(list($key, $value) = each($list))
                {
                        if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date'])
                        {
                                $ret[$key]['content'] = $value;
                                $ret[$key]['date'] = $list['date'];
                        }
                }
        }
        return $ret;
}

function getEmployerReport($pid)
{
        $sql = "select * from employer_data where pid='$pid' order by date ASC";
        $res = sqlStatement("$sql");
        while($list = sqlFetchArray($res))
        {
                while(list($key, $value) = each($list))
                {
                        if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date'])
                        {
                                $ret[$key]['content'] = $value;
                                $ret[$key]['date'] = $list['date'];
                        }
                }
        }
        return $ret;
}

function getListsReport($pid)
{
        $sql = "select * from lists where id='$id' order by date ASC";
        $res = sqlStatement("$sql");
        while($list = sqlFetchArray($res))
        {
                while(list($key, $value) = each($list))
                {
                        if ($ret[$key]['content'] != $value && $ret[$key]['date'] < $list['date'])
                        {
                                $ret[$key]['content'] = $value;
                                $ret[$key]['date'] = $list['date'];
                        }
                }
        }
        return $ret;
}

function printListData($pid, $list_type, $list_activity = "%") {
	$res = sqlStatement("select * from lists where pid='$pid' and type='$list_type' and activity like '$list_activity' order by date");
	while($result = sqlFetchArray($res)) {
		print "<span class=bold>" . $result{"title"} . ":</span><span class=text> " . $result{"comments"} . "</span><br>\n";
	}
}

function printPatientNotes($pid) {
  // exclude ALL deleted notes
  $res = sqlStatement("select * from pnotes where pid = '$pid' and deleted != 1 and activity = 1 order by date");
  while($result = sqlFetchArray($res)) {
    print "<span class=bold>" . oeFormatSDFT(strtotime($result{"date"})) .
      ":</span><span class=text> " .
      stripslashes(oeFormatPatientNote($result['body'])) . "</span><br>\n";
  }
}

function printPatientTransactions($pid) {
	$res = sqlStatement("select * from transactions where pid=? order by date", array($pid) );
	while($result = sqlFetchArray($res)) {
		print "<span class=bold>" .
		htmlspecialchars( oeFormatSDFT(strtotime($result{"date"})), ENT_NOQUOTES) .
		":</span><span class=text>(" .
		generate_display_field(array('data_type'=>'1','list_id'=>'transactions'), $result{"title"}) .
		") " . htmlspecialchars( $result{"body"}, ENT_NOQUOTES) .
		"</span><br>\n";
	}
}

function printPatientBilling($pid) {
  $res = sqlStatement("select * from billing where pid='$pid' order by date");
  while($result = sqlFetchArray($res)) {
    echo "<span class=bold>" . oeFormatSDFT(strtotime($result{"date"})) . " : </span>";
    echo "<span class=text>(".$result{"code_type"}.") ";
    echo $result['code_type'] == 'COPAY' ? oeFormatMoney($result['code']) : $result['code'];
    echo " - ". $result['code_text']."</span>";
    echo "<br>\n";
	}
}

function getPatientBillingEncounter($pid, $encounter) {
  /*******************************************************************
	$sql = "SELECT billing.*, u.id, u.fname, u.mname, u.lname, " .
    "CONCAT(u.fname,' ', u.lname) AS provider_name, u.federaltaxid " .
    "FROM billing LEFT JOIN users AS u ON u.id = billing.provider_id " .
    "WHERE pid='" . mysql_real_escape_string($pid) . "' AND " .
    "encounter = '" . mysql_real_escape_string($encounter) .
    "' AND activity='1' ORDER BY date";
  *******************************************************************/
  $erow = sqlQuery("SELECT provider_id FROM form_encounter WHERE " .
    "pid = '$pid' AND encounter = '$encounter' " .
    "ORDER BY id DESC LIMIT 1");
  $inv_provider = $erow['provider_id'] + 0;
	$sql = "SELECT b.*, u.id, u.fname, u.mname, u.lname, " .
    "CONCAT(u.fname,' ', u.lname) AS provider_name, u.federaltaxid " .
    "FROM billing AS b " .
    "LEFT JOIN users AS u ON " .
    "( b.provider_id != 0 AND u.id = b.provider_id ) OR " .
    "( b.provider_id  = 0 AND u.id = $inv_provider ) " .
    "WHERE pid='" . mysql_real_escape_string($pid) . "' AND " .
    "encounter = '" . mysql_real_escape_string($encounter) .
    "' AND activity = '1' ORDER BY date";
  /******************************************************************/
	$res = sqlStatement($sql);
	$billings = array();
	while($result = sqlFetchArray($res)) {
		$billings[] = $result;
	}
	return $billings;
}

function printPatientForms($pid, $cols) {
    //this function takes a $pid
    $inclookupres = sqlStatement("select distinct formdir from forms where pid='$pid' AND deleted=0");
    while($result = sqlFetchArray($inclookupres)) {
        include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
    }
	
    $res = sqlStatement("select * from forms where pid='$pid' AND deleted=0 order by date");
    while($result = sqlFetchArray($res)) {
        if ($result{"form_name"} == "New Patient Encounter") {
            echo "<div class='text encounter'>\n";
            echo "<h1>" . $result["form_name"] . "</h1>";

            // display the provider info
            $tmp = sqlQuery("SELECT u.title, u.fname, u.mname, u.lname " .
                                    "FROM forms AS f, users AS u WHERE " .
                                    "f.pid = '$pid' AND f.encounter = ".$result['encounter']." AND " .
                                    "f.formdir = 'newpatient' AND u.username = f.user " .
                                    " AND f.deleted=0 ". //--JRM--
                                    "ORDER BY f.id LIMIT 1");
            echo " ".xl('Provider').": ".$tmp['title']." ".
                $tmp['fname']." ".$tmp['mname']." ".$tmp['lname'];
            echo "<br/>";
        }
        else {
            echo "<div class='text encounter_form'>";
            echo "<h1>" . $result["form_name"] . "</h1>";
        }
        echo "(" . oeFormatSDFT(strtotime($result["date"])) . ") ";

        if (acl_check('acct', 'rep') || acl_check('acct', 'eob') || acl_check('acct', 'bill')) {
            if ($result{"form_name"} == "New Patient Encounter") {
                // display billing info
                echo "<br/>";
                $bres = sqlStatement("SELECT b.date, b.code, b.code_text " .
                  "FROM billing AS b, code_types AS ct WHERE " .
                  "b.pid = ? AND " .
                  "b.encounter = ? AND " .
                  "b.activity = 1 AND " .
                  "b.code_type = ct.ct_key AND " .
                  "ct.ct_diag = 0 " .
                  "ORDER BY b.date",
                  array($pid, $result['encounter']));
                while ($brow=sqlFetchArray($bres)) {
                    echo "<span class='bold'>&nbsp;".xl('Procedure').": </span><span class='text'>" .
                        $brow['code'] . " " . $brow['code_text'] . "</span><br>\n";
                }
            }
        }

        call_user_func($result{"formdir"} . "_report", $pid, $result{"encounter"}, $cols, $result{"form_id"});

        echo "</div>";
    }
}

function getRecHistoryData ($pid) {
	//data is returned as a multi-level array:
	//column name->dates->values
	//$return{"lname"}[0..n]{"date"}
	//$return{"lname"}[0..n]{"value"}
	$res = sqlStatement("select * from history_data where pid=? order by date", array($pid) );
	
	while($result = sqlFetchArray($res)) {
		foreach ($result as $key => $val) {
			if ($key == "pid" || $key == "date" || $key == "id") {
				next;
			} else {
				$curdate = $result{"date"};
				if (($retar{$key}[$arcount{$key}]{"value"} != $val)) {
					$arcount{$key}++;
					$retar{$key}[$arcount{$key}]{"value"} = $val;
					$retar{$key}[$arcount{$key}]{"date"} = $curdate;
				}
			}
		}
	}
	return $retar;
}

function getRecEmployerData ($pid) {
	//data is returned as a multi-level array:
	//column name->dates->values
	//$return{"lname"}[0..n]{"date"}
	//$return{"lname"}[0..n]{"value"}
	$res = sqlStatement("select * from employer_data where pid=? order by date", array($pid) );
	
	while($result = sqlFetchArray($res)) {
		foreach ($result as $key => $val) {
			if ($key == "pid" || $key == "date" || $key == "id") {
				next;
			} else {
				$curdate = $result{"date"};
				if (($retar{$key}[$arcount{$key}]{"value"} != $val)) {
					$arcount{$key}++;
					$retar{$key}[$arcount{$key}]{"value"} = $val;
					$retar{$key}[$arcount{$key}]{"date"} = $curdate;
				}
			}
		}
	}
	return $retar;
}

function getRecPatientData ($pid) {
	//data is returned as a multi-level array:
	//column name->dates->values
	//$return{"lname"}[0..n]{"date"}
	//$return{"lname"}[0..n]{"value"}
	$res = sqlStatement("select * from patient_data where pid=? order by date", array($pid) );
	
	while($result = sqlFetchArray($res)) {
		foreach ($result as $key => $val) {
			if ($key == "pid" || $key == "date" || $key == "id") {
				next;
			} else {
				$curdate = $result{"date"};
				if (($retar{$key}[$arcount{$key}]{"value"} != $val)) {
					$arcount{$key}++;
					$retar{$key}[$arcount{$key}]{"value"} = $val;
					$retar{$key}[$arcount{$key}]{"date"} = $curdate;
				}
			}
		}
	}
	return $retar;
}

function getRecInsuranceData ($pid, $ins_type) {
	//data is returned as a multi-level array:
	//column name->dates->values
	//$return{"lname"}[0..n]{"date"}
	//$return{"lname"}[0..n]{"value"}
	$res = sqlStatement("select *, ic.name as provider_name from insurance_data left join insurance_companies as ic on ic.id = provider where pid=? and type=? order by date", array($pid,$ins_type) );
	
	while($result = sqlFetchArray($res)) {
		foreach ($result as $key => $val) {
			if ($key == "pid" || $key == "date" || $key == "id") {
				next;
			} else {
				$curdate = $result{"date"};
				if (($retar{$key}[$arcount{$key}]{"value"} != $val)) {
					$arcount{$key}++;
					$retar{$key}[$arcount{$key}]{"value"} = $val;
					$retar{$key}[$arcount{$key}]{"date"} = $curdate;
				}
			}
		}
	}
	return $retar;
}

function printRecData($data_array, $recres, $N) {
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
		print "<td valign=top><span class=bold>$aval</span><br><span class=text>";
		printData($recres, $akey, "<br>", "Y-m-d");
		print "</span></td>\n";
		$count++;
	}
	print "</tr></table>\n";
}

function printData ($retar, $key, $sep, $date_format) {
	//$retar{$key}
	if (@array_key_exists($key,$retar)) {
		$length = sizeof($retar{$key});
		for ($iter = $length;$iter>=1;$iter--) {
			if ($retar{$key}[$iter]{"value"} != "0000-00-00 00:00:00") {
				print $retar{$key}[$iter]{"value"} . " (" . oeFormatSDFT(strtotime($retar{$key}[$iter]{"date"})) . ")$sep";
			}
		}
	}
}

function printRecDataOne($data_array, $recres, $N) {
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
		if (sizeof($recres{$akey})>0 && ($recres{$akey}[1]{"value"}!="0000-00-00 00:00:00")) {
			if ($count == $N) {
				print "</tr><tr>\n";
				$count = 0;
			}
			print "<td valign=top><span class=bold>" . text($aval) . "</span><br><span class=text>";
			printDataOne($recres, $akey, "<br>", "Y-m-d");
			print "</span></td>\n";
			$count++;
		}
	}
	print "</tr></table>\n";
}

function printDataOne ($retar, $key, $sep, $date_format) {
	//this function supports the printRecDataOne function above
	if (@array_key_exists($key,$retar)) {
		$length = sizeof($retar{$key});
		if ($retar{$key}[$length]{"value"} != "0000-00-00 00:00:00") {
      $tmp = $retar{$key}[$length]{"value"};
      if (strstr($key, 'DOB')) $tmp = oeFormatShortDate($tmp);
			print text($tmp) . $sep;
		}
	}
}

?>
