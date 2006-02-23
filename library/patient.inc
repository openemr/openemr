<?php
include_once("{$GLOBALS['srcdir']}/sql.inc");
require_once(dirname(__FILE__) . "/classes/WSWrapper.class.php");

function getPatientData($pid, $given = "*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") {
	$sql = "select $given from patient_data where pid='$pid' order by date DESC limit 0,1";
	return sqlQuery($sql);
}

function getLanguages() {
	$returnval = array('','english');
	$sql = "select distinct lower(language) as language from patient_data";
	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++) {
		if (($row["language"] != "english") && ($row["language"] != "")) {
			array_push($returnval, $row["language"]);
		}
	}


	return $returnval;
}

function getInsuranceProviders() {
	$returnval = array();

	if (true) {
		$sql = "select name, id from insurance_companies order by name, id";
		$rez = sqlStatement($sql);
		for($iter=0; $row=sqlFetchArray($rez); $iter++) {
			$returnval[$row['id']] = $row['name'];
		}
	}

	// Please leave this here. I have a user who wants to see zip codes and PO
	// box numbers listed along with the insurance company names, as many companies
	// have different billing addresses for different plans.  -- Rod Roark
	//
	else {
		$sql = "select insurance_companies.name, insurance_companies.id, " .
		  "addresses.zip, addresses.line1 " .
		  "from insurance_companies, addresses " .
		  "where addresses.foreign_id = insurance_companies.id " .
		  "order by insurance_companies.name, addresses.zip";

		$rez = sqlStatement($sql);

		for($iter=0; $row=sqlFetchArray($rez); $iter++) {
			preg_match("/\d+/", $row['line1'], $matches);
			$returnval[$row['id']] = $row['name'] . " (" . $row['zip'] .
			  "," . $matches[0] . ")";
		}
	}

	// print_r($retval);

	return $returnval;
}


function getProviders() {
	$returnval = array("");
	$sql = "select fname,lname from users where authorized=1";
	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++) {
		if (($row["fname"] != "") && ($row["lname"] != "")) {
			array_push($returnval, $row["fname"] . " " . $row["lname"]);
		}
	}


	return $returnval;
}

function getProviderInfo($providerID = "%", $providers_only = true) {
	$param1 = "";
	if ($providers_only) {
		$param1 = "AND authorized=1";
	}
	$command = "=";
	if ($providerID == "%") {
		$command = "like";
	}
	$query = "select distinct id,username,lname,fname, authorized, info, facility from users where id $command '" . mysql_real_escape_string($providerID) . "' " . $param1;
	$rez = sqlStatement($query);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	//if only one result returned take the key/value pairs in array [0] and merge them down the the base array so that $resultval[0]['key'] is also
	//accessible from $resultval['key']

	if($iter==1) {
		$akeys = array_keys($returnval[0]);
		foreach($akeys as $key) {

			$returnval[0][$key] = $returnval[0][$key];
		}
	}
	return $returnval;
}

//same as above but does not reduce if only 1 row returned
function getCalendarProviderInfo($providerID = "%", $providers_only = true) {
	$param1 = "";
	if ($providers_only) {
		$param1 = "AND authorized=1";
	}
	$command = "=";
	if ($providerID == "%") {
		$command = "like";
	}
	$query = "select distinct id,username,lname,fname, authorized, info, facility from users where id $command '" . mysql_real_escape_string($providerID) . "' " . $param1;

	$rez = sqlStatement($query);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}



function getProviderName($providerID) {

	$pi = getProviderInfo($providerID);

	if (strlen($pi[0]["lname"]) > 0) {
		return $pi[0]['fname'] . " " . $pi[0]['lname'];
	}

	return "";
}

function getProviderId($providerName) {
	$query = "select id from users where username = '". mysql_real_escape_string($providerName)."'";

	$rez = sqlStatement($query);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

function getEthnoRacials() {
	$returnval = array("");
	$sql = "select distinct lower(ethnoracial) as ethnoracial from patient_data";
	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++) {
		if (($row["ethnoracial"] != "")) {
			array_push($returnval, $row["ethnoracial"]);
		}
	}


	return $returnval;
}

function getHistoryData($pid, $given = "*")
{
	$sql = "select $given from history_data where pid='$pid' order by date DESC limit 0,1";
	return sqlQuery($sql);
}

// function getInsuranceData($pid, $type = "primary", $given = "insd.*, DATE_FORMAT(subscriber_DOB,'%m/%d/%Y') as subscriber_DOB, ic.name as provider_name")
function getInsuranceData($pid, $type = "primary", $given = "insd.*, ic.name as provider_name")
{
	$sql = "select $given from insurance_data as insd left join insurance_companies as ic on ic.id = insd.provider where pid='$pid' and type='$type' order by date DESC limit 0,1";
	return sqlQuery($sql);
}

function getInsuranceDataByDate( $pid, $date, $type, $given = "insd.*, DATE_FORMAT(subscriber_DOB,'%m/%d/%Y') as subscriber_DOB, ic.name as provider_name")
{ //this must take the date in the following manner: YYYY-MM-DD
	//this function recalls the insurance value that was most recently enterred from the
	//given date. it will call up most recent records up to and on the date given,
	//but not records enterred after the given date
	$sql = "select $given from insurance_data as insd left join insurance_companies as ic on ic.id = provider where pid='$pid' and  date_format(date,'%Y-%m-%d')<='$date' and type='$type' order by date DESC limit 0,1";
	return sqlQuery($sql);
}


function getEmployerData($pid, $given = "*")
{
	$sql = "select $given from employer_data where pid='$pid' order by date DESC limit 0,1";
	return sqlQuery($sql);
}

function getPatientLnames($lname = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
	/****
	$sql="select pid, MAX(id) as id from patient_data group by pid DESC order by pid ASC";
	$res = sqlStatement($sql);
	$sql="select $given from patient_data where lname like '$lname%' and (";
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$sql.=" id='{$row['id']}' or";
	if ($iter > 0)
		$sql = substr($sql, 0, -3) . ") order by $orderby";
	else
		$sql = substr($sql, 0, -5)."order by $orderby";
	****/

	// WTF? That was a good way to create a 200KB sql statement.

	// Allow the last name to be followed by a comma and some part of a first name.
	$lname = trim($lname);
	$fname = '';
 	if (preg_match('/^(.*),(.*)/', $lname, $matches)) {
 		$lname = trim($matches[1]);
 		$fname = trim($matches[2]);
	}

	$sql="select $given from patient_data where lname like '$lname%' " .
 		"and fname like '%$fname%' " .
		"order by $orderby";

	if ($limit != "all")
		$sql .= " limit $start, $limit";
	$rez = sqlStatement($sql);

	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

function getPatientId($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
	/****
	$sql="select pid, MAX(id) as id from patient_data group by pid DESC order by pid ASC";
	$res = sqlStatement($sql);
	$sql="select $given from patient_data where pubpid like '$pid%' and (";
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$sql.=" id='{$row['id']}' or";
	if ($iter > 0)
		$sql = substr($sql, 0, -3) . ") order by $orderby";
	else
		$sql = substr($sql, 0, -5)."order by $orderby";
	****/

	$sql = "select $given from patient_data where pubpid like '$pid%' " .
		"order by $orderby";

	if ($limit != "all")
		$sql .= " limit $start, $limit";
	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

function getPatientPID($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
	$command = "=";
	if ($pid == -1)
		$pid = "%";
	elseif (empty($pid))
		$pid = "NULL";

	if (strstr($pid,"%"))
		$command = "like";

	$sql="select $given from patient_data where pid $command '$pid' order by $orderby";

	if ($limit != "all")
		$sql .= " limit $start, $limit";

	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;


	return $returnval;
}

function getPatientName($pid) {
	if (empty($pid))
		return "";
	$patientData = getPatientPID($pid);
	if (empty($patientData[0]['lname']))
		return "";
	$patientName =  $patientData[0]['lname'] . ", " . $patientData[0]['fname'];
	return $patientName;
}


function getPatientDOB($DOB = "%", $given = "pid, id, lname, fname, mname", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
	/****
	$sql="select pid, MAX(id) as id from patient_data group by pid DESC order by pid ASC";
	$res = sqlStatement($sql);
	$sql="select $given from patient_data where DOB like '$DOB%' and (";
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$sql.=" id='{$row['id']}' or";
	if ($iter > 0)
		$sql = substr($sql, 0, -3) . ") order by $orderby";
	else
		$sql = substr($sql, 0, -5)."order by $orderby";
	****/

	$DOB = fixDate($DOB, $DOB);

	$sql="select $given from patient_data where DOB like '$DOB%' " .
		"order by $orderby";

	if ($limit != "all")
		$sql .= " limit $start, $limit";

	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

function getPatientSSN($ss = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
	/****
	$sql="select pid, MAX(id) as id from patient_data group by pid DESC order by pid ASC";
	$res = sqlStatement($sql);
	$sql="select $given from patient_data where ss like '$ss%' and (";
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$sql.=" id='{$row['id']}' or";
	if ($iter > 0)
		$sql = substr($sql, 0, -3) . ") order by $orderby";
	else
		$sql = substr($sql, 0, -5)."order by $orderby";
	****/

	$sql="select $given from patient_data where ss like '$ss%' " .
		"order by $orderby";

	if ($limit != "all")
		$sql .= " limit $start, $limit";

	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

function getPatientIds($given = "pid, id, lname, fname, mname", $orderby = "id ASC", $limit="all", $start="0")
{
	/****
	$sql="select pid, MAX(id) as id from patient_data group by pid DESC order by pid ASC";
	$res = sqlStatement($sql);
	$sql="select $given from patient_data where ";
	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$sql.="id='{$row['id']}' or ";
	$sql = substr($sql, 0, -3) . "order by $orderby";
	****/

	$sql="select $given from patient_data order by $orderby";

	if ($limit != "all")
		$sql .= " limit $start, $limit";

	$rez = sqlStatement($sql);
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

//----------------------input functions
function newPatientData(	$db_id="",
				$title = "",
				$fname = "",
				$lname = "",
				$mname = "",
				$sex = "",
				$DOB = "",
				$street = "",
				$postal_code = "",
				$city = "",
				$state = "",
				$country_code = "",
				$ss = "",
				$occupation = "",
				$phone_home = "",
				$phone_biz = "",
				$phone_contact = "",
				$status = "",
				$contact_relationship = "",
				$referrer = "",
				$referrerID = "",
				$email = "",
				$language = "",
				$ethnoracial = "",
				$interpretter = "",
				$migrantseasonal = "",
				$family_size = "",
				$monthly_income = "",
				$homeless = "",
				$financial_review = "",
				$pubpid = "",
				$pid = "MAX(pid)+1",
				$providerID = "",
				$genericname1 = "",
				$genericval1 = "",
				$genericname2 = "",
				$genericval2 = "",
				$phone_cell = "",
				$hipaa_mail = "",
				$hipaa_voice = "",
				$squad = 0
			)
{
	$DOB = fixDate($DOB);

	// Looking for problems... suspecting that $pid might be clobbered.
	//
	$fitness = 0;
	if ($pid) {
		$rez = sqlQuery("select id, fitness from patient_data where pid = $pid");
		if ($db_id != $rez['id']) {
			$errmsg = "Internal error: Attempt to change patient_data.id from '" .
			  $rez['id'] . "' to '$db_id' for pid '$pid'";
			die($errmsg);
		}
		$fitness = $rez['fitness'];
	}

	$query = ("replace into patient_data set
		id='$db_id',
		title='$title',
		fname='$fname',
		lname='$lname',
		mname='$mname',
		sex='$sex',
		DOB='$DOB',
		street='$street',
		postal_code='$postal_code',
		city='$city',
		state='$state',
		country_code='$country_code',
		ss='$ss',
		occupation='$occupation',
		phone_home='$phone_home',
		phone_biz='$phone_biz',
		phone_contact='$phone_contact',
		status='$status',
		contact_relationship='$contact_relationship',
		referrer='$referrer',
		referrerID='$referrerID',
		email='$email',
		language='$language',
		ethnoracial='$ethnoracial',
		interpretter='$interpretter',
		migrantseasonal='$migrantseasonal',
		family_size='$family_size',
		monthly_income='$monthly_income',
		homeless='$homeless',
		financial_review='$financial_review',
		pubpid='$pubpid',
		pid = $pid,
		providerID = '$providerID',
		genericname1 = '$genericname1',
		genericval1 = '$genericval1',
		genericname2 = '$genericname2',
		genericval2 = '$genericval2',
		phone_cell = '$phone_cell',
		hipaa_mail = '$hipaa_mail',
		hipaa_voice = '$hipaa_voice',
		squad = '$squad',
		fitness='$fitness',
		date=NOW()
			");

	$id = sqlInsert($query);
	$foo = sqlQuery("select pid from patient_data where id='$id' order by date limit 0,1");

	sync_patient($id,$fname,$lname,$street,$city,$postal_code,$state,$phone_home,
				$phone_biz,$email,$pid);

	return $foo['pid'];
}

// Supported input date formats are:
//   mm/dd/yyyy
//   mm/dd/yy   (assumes 20yy for yy < 10, else 19yy)
//   yyyy/mm/dd
//   also mm-dd-yyyy, etc. and mm.dd.yyyy, etc.
//
function fixDate($date, $default="0000-00-00") {
    $fixed_date = $default;
    $date = trim($date);
    if (preg_match("'^[0-9]{1,4}[/.-][0-9]{1,2}[/.-][0-9]{1,4}$'", $date)) {
        $dmy = preg_split("'[/.-]'", $date);
        if ($dmy[0] > 99) {
            $fixed_date = sprintf("%04u-%02u-%02u", $dmy[0], $dmy[1], $dmy[2]);
        } else {
            if ($dmy[2] < 1000) $dmy[2] += 1900;
            if ($dmy[2] < 1910) $dmy[2] += 100;
            $fixed_date = sprintf("%04u-%02u-%02u", $dmy[2], $dmy[0], $dmy[1]);
        }
    }

    return $fixed_date;
}


function updatePatientData($pid,$new)
{
	$real = getPatientData($pid);
	$new['DOB'] = fixDate($new['DOB']);
	while(list($key, $value) = each ($new))
		$real[$key] = $value;
	$real['date'] = "'+NOW()+'";
	$real['id'] = "";

	$sql = "insert into patient_data set ";
	while(list($key, $value) = each($real))
		$sql .= $key." = '$value', ";
	$sql = substr($sql, 0, -2);


	return sqlInsert($sql);
}


function newEmployerData(	$pid,
				$name = "",
				$street = "",
				$postal_code = "",
				$city = "",
				$state = "",
				$country = ""
			)
{
	return sqlInsert("insert into employer_data set
		name='$name',
		street='$street',
		postal_code='$postal_code',
		city='$city',
		state='$state',
		country='$country',
		pid='$pid',
		date=NOW()
		");
}

function updateEmployerData($pid,$new)
{
        $real = getEmployerData($pid);
        while(list($key, $value) = each ($new))
                $real[$key] = $value;
        $real['date'] = "'+NOW()+'";
        $real['id'] = "";

        $sql = "insert into employer_data set ";
        while(list($key, $value) = each($real))
                $sql .= $key." = '$value', ";
        $sql = substr($sql, 0, -2);


        return sqlInsert($sql);
}

function newInsuranceData(	$pid,
				$type = "",
				$provider = "",
				$policy_number = "",
				$group_number = "",
				$plan_name = "",
				$subscriber_lname = "",
				$subscriber_mname = "",
				$subscriber_fname = "",
				$subscriber_relationship = "",
				$subscriber_ss = "",
				$subscriber_DOB = "",
				$subscriber_street = "",
				$subscriber_postal_code = "",
				$subscriber_city = "",
				$subscriber_state = "",
				$subscriber_country = "",
				$subscriber_phone = "",
				$subscriber_employer = "",
				$subscriber_employer_street = "",
				$subscriber_employer_city = "",
				$subscriber_employer_postal_code = "",
				$subscriber_employer_state = "",
				$subscriber_employer_country = "",
				$copay = "",
				$subscriber_sex = ""
			)
{
	if (strlen($type) > 0) {
	$query = "select * from insurance_data where type='" . $type  . "' and pid = " . $pid .  " limit 1";
	}
	else {
		return FALSE;
	}
	$res = sqlQuery ($query);

	if ($res) {
		$data['type'] = $type;
		$data['provider'] = $provider;
		$data['policy_number']=$policy_number;
                $data['group_number']=$group_number;
                $data['plan_name']=$plan_name;
                $data['subscriber_lname']=$subscriber_lname;
		$data['subscriber_mname']=$subscriber_mname;
                $data['subscriber_fname']=$subscriber_fname;
                $data['subscriber_relationship']=$subscriber_relationship;
                $data['subscriber_ss']=$subscriber_ss;
                $data['subscriber_DOB']=$subscriber_DOB;
                $data['subscriber_street']=$subscriber_street;
                $data['subscriber_postal_code']=$subscriber_postal_code;
                $data['subscriber_city']=$subscriber_city;
                $data['subscriber_state']=$subscriber_state;
                $data['subscriber_country']=$subscriber_country;
                $data['subscriber_phone']=$subscriber_phone;
                $data['subscriber_employer']=$subscriber_employer;
                $data['subscriber_employer_city']=$subscriber_employer_city;
                $data['subscriber_employer_street']=$subscriber_employer_street;
                $data['subscriber_employer_postal_code']=$subscriber_employer_postal_code;
                $data['subscriber_employer_state']=$subscriber_employer_state;
                $data['subscriber_employer_country']=$subscriber_employer_country;
                $data['copay']=$copay;
                $data['subscriber_sex']=$subscriber_sex;
                $data['pid']=$pid;
	        $data['date']="NOW()";
	//	echo "updating<br><br>";

		return updateInsuranceData($pid,$data);
	}
	else {
	return sqlInsert("insert into insurance_data set
		type='$type',
		provider='$provider',
		policy_number='$policy_number',
		group_number='$group_number',
		plan_name='$plan_name',
		subscriber_lname='$subscriber_lname',
		subscriber_mname='$subscriber_mname',
		subscriber_fname='$subscriber_fname',
		subscriber_relationship='$subscriber_relationship',
		subscriber_ss='$subscriber_ss',
		subscriber_DOB='$subscriber_DOB',
		subscriber_street='$subscriber_street',
		subscriber_postal_code='$subscriber_postal_code',
		subscriber_city='$subscriber_city',
		subscriber_state='$subscriber_state',
		subscriber_country='$subscriber_country',
		subscriber_phone='$subscriber_phone',
		subscriber_employer = '$subscriber_employer',
		subscriber_employer_city='$subscriber_employer_city',
		subscriber_employer_street='$subscriber_employer_street',
		subscriber_employer_postal_code='$subscriber_employer_postal_code',
		subscriber_employer_state='$subscriber_employer_state',
		subscriber_employer_country='$subscriber_employer_country',
		copay='$copay',
		subscriber_sex='$subscriber_sex',
		pid='$pid',
		date=NOW()
		");
	}
}

function updateInsuranceData($pid,$new)
{
		$fields = sqlListFields("insurance_data");

        $real = getInsuranceData($pid);
        $use = array();
        while(list($key, $value) = each ($new)) {
        		if (in_array($key,$fields)){
                	$use[$key] = $value;
        		}
		}
        $real['date'] = "'+NOW()+'";
        $real['id'] = "";

        $sql = "replace into insurance_data set ";
        while(list($key, $value) = each($use))
                $sql .= $key." = '$value', ";


        $sql = substr($sql, 0, -2);

	//echo $sql;
	//exit;
        return sqlInsert($sql);
}


function newHistoryData(	$pid,
				$coffee = "",
				$tobacco = "",
				$alcohol = "",
				$sleep_patterns = "",
				$exercise_patterns = "",
				$seatbelt_use = "",
				$counseling = "",
				$hazardous_activities = "",
				$last_breast_exam = "",
				$last_mammogram = "",
				$last_gynocological_exam = "",
				$last_rectal_exam = "",
				$last_prostate_exam = "",
				$last_physical_exam = "",
				$last_sigmoidoscopy_colonoscopy = "",
				$history_mother = "",
				$history_father = "",
				$history_siblings = "",
				$history_offspring = "",
				$history_spouse = "",
				$relatives_cancer = "",
				$relatives_tuberculosis = "",
				$relatives_diabetes = "",
				$relatives_high_blood_pressure = "",
				$relatives_heart_problems = "",
				$relatives_stroke = "",
				$relatives_epilepsy = "",
				$relatives_mental_illness = "",
				$relatives_suicide = "",
				$cataract_surgery = "",
				$tonsillectomy = "",
				$appendectomy = "",
				$cholecystestomy = "",
				$heart_surgery = "",
				$hysterectomy = "",
				$hernia_repair = "",
				$hip_replacement = "",
				$knee_replacement = "",
				$name_1 = "",
				$value_1 = "",
				$name_2 = "",
				$value_2 = "",
				$additional_history = "",
				$last_ecg = "",
				$last_cardiac_echo = "",
				$last_exam_results = ""
				)
{
	return sqlInsert("insert into history_data set
		coffee='$coffee',
		tobacco='$tobacco',
		alcohol='$alcohol',
		sleep_patterns='$sleep_patterns',
		exercise_patterns='$exercise_patterns',
		seatbelt_use='$seatbelt_use',
		counseling='$counseling',
		hazardous_activities='$hazardous_activities',
		last_breast_exam='$last_breast_exam',
		last_mammogram='$last_mammogram',
		last_gynocological_exam='$last_gynocological_exam',
		last_rectal_exam='$last_rectal_exam',
		last_prostate_exam='$last_prostate_exam',
		last_physical_exam='$last_physical_exam',
		last_sigmoidoscopy_colonoscopy='$last_sigmoidoscopy_colonoscopy',
		last_ecg='$last_ecg',
		last_cardiac_echo='$last_cardiac_echo',
		last_exam_results='$last_exam_results',
		history_mother='$history_mother',
		history_father='$history_father',
		history_siblings='$history_siblings',
		history_offspring='$history_offspring',
		history_spouse='$history_spouse',
		relatives_cancer='$relatives_cancer',
		relatives_tuberculosis ='$relatives_tuberculosis',
		relatives_diabetes='$relatives_diabetes',
		relatives_high_blood_pressure='$relatives_high_blood_pressure',
		relatives_heart_problems='$relatives_heart_problems',
		relatives_stroke='$relatives_stroke',
		relatives_epilepsy='$relatives_epilepsy',
		relatives_mental_illness='$relatives_mental_illness',
		relatives_suicide='$relatives_suicide',
		cataract_surgery='$cataract_surgery',
		tonsillectomy='$tonsillectomy',
		appendectomy='$appendectomy',
		cholecystestomy='$cholecystestomy',
		heart_surgery='$heart_surgery',
		hysterectomy='$hysterectomy',
		hernia_repair='$hernia_repair',
		hip_replacement='$hip_replacement',
		knee_replacement='$knee_replacement',
		name_1 = '$name_1',
		value_1 = '$value_1',
		name_2 = '$name_2',
		value_2 = '$value_2',
		additional_history = '$additional_history',
		date=NOW(),
		pid='$pid'
		");
}

function updateHistoryData($pid,$new)
{
        $real = getHistoryData($pid);
        while(list($key, $value) = each ($new))
                $real[$key] = $value;
        $real['date'] = "'+NOW()+'";
        $real['id'] = "";

        $sql = "insert into history_data set ";
        while(list($key, $value) = each($real))
                $sql .= $key." = '$value', ";
        $sql = substr($sql, 0, -2);


        return sqlInsert($sql);
}

function sync_patient($id,$fname,$lname,$street,$city,$postal_code,$state,$phone_home,
				$phone_biz,$email,$pid="")
{
	$db = $GLOBALS['adodb']['db'];
	$customer_info = array();

	$sql = "SELECT foreign_id,foreign_table FROM integration_mapping where local_table = 'patient_data' and local_id = '" . $id . "'";
	$result = $db->Execute($sql);
	if ($result && !$result->EOF) {
		$customer_info['foreign_update'] = true;
		$customer_info['foreign_id'] = $result->fields['foreign_id'];
		$customer_info['foreign_table'] = $result->fields['foreign_table'];
	}

	///xml rpc code to connect to accounting package and add user to it
	$customer_info['firstname'] = $fname;
	$customer_info['lastname'] = $lname;
	$customer_info['address'] = $street;
	$customer_info['suburb'] = $city;
	$customer_info['state'] = $state;
	$customer_info['postcode'] = $postal_code;

	//ezybiz wants state as a code rather than abbreviation
	$customer_info['geo_zone_id'] = "";
	$sql = "SELECT zone_id from geo_zone_reference where zone_code = '" . strtoupper($state) . "'";
	$db = $GLOBALS['adodb']['db'];
	$result = $db->Execute($sql);
	if ($result && !$result->EOF) {
		$customer_info['geo_zone_id'] = $result->fields['zone_id'];
	}

	//ezybiz wants country as a code rather than abbreviation
	$customer_info['geo_country_id'] = "";
	$sql = "SELECT countries_id from geo_country_reference where countries_iso_code_2 = '" . strtoupper($country_code) . "'";
	$db = $GLOBALS['adodb']['db'];
	$result = $db->Execute($sql);
	if ($result && !$result->EOF) {
		$customer_info['geo_country_id'] = $result->fields['countries_id'];
	}


	$customer_info['phone1'] = $phone_home;
	$customer_info['phone1comment'] = "Home Phone";
	$customer_info['phone2'] = $phone_biz;
	$customer_info['phone2comment'] = "Business Phone";
	$customer_info['email'] = $email;
	$customer_info['customernumber'] = $pid;

	$function['ezybiz.add_customer'] = array(new xmlrpcval($customer_info,"struct"));
	$ws = new WSWrapper($function);

	// if the remote patient was added make an entry in the local mapping table to that updates can be made correctly
	if (is_numeric($ws->value)) {
		$sql = "REPLACE INTO integration_mapping set id = '" . $db->GenID("sequences") . "', foreign_id ='" . $ws->value . "', foreign_table ='customer', local_id = '" . $id . "', local_table = 'patient_data' ";
		$db->Execute($sql) or die ("error: " . $db->ErrorMsg());
	}
}

// Returns Date of Birth given YYYYMMDD from MySQL DATE_FORMAT(DOB,'%Y%m%d')
function getPatientAge($dobYMD)
{
    $tdyYMD=date("Ymd");
    $yearDiff = substr($tdyYMD,0,4) - substr($dobYMD,0,4);
    $ageInMonths = ((substr($tdyYMD,0,4)*12)+substr($tdyYMD,4,2)) -
                   ((substr($dobYMD,0,4)*12)+substr($dobYMD,4,2));
    $dayDiff = substr($tdyYMD,6,2) - substr($dobYMD,6,2);
    if ( $dayDiff < 0 ) {
        $ageInMonths -= 1;
    }
    if ( $ageInMonths > 24 ) {
        $age = intval($ageInMonths/12);
    }
    else  {
        $age = "$ageInMonths month";
    }
    return $age;
}

function dateToDB ($date) 
{
	$date=substr ($date,6,4)."-".substr ($date,3,2)."-".substr($date, 0,2);
	return $date;
}




function DBToDate ($date)
{
	$date=substr ($date,5,2)."/".substr ($date,8,2)."/".substr($date, 0,4);
	return $date;
}
?>
