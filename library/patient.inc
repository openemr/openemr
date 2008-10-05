<?php
include_once("{$GLOBALS['srcdir']}/sql.inc");
require_once(dirname(__FILE__) . "/classes/WSWrapper.class.php");

// These are for sports team use:
$PLAYER_FITNESSES = array(
  xl('Full Play'),
  xl('Full Training'),
  xl('Restricted Training'),
  xl('Injured Out'),
  xl('Rehabilitation'),
  xl('Illness'),
  xl('International Duty')
);
$PLAYER_FITCOLORS = array('#6677ff', '#00cc00', '#ffff00', '#ff3333', '#ff8800', '#ffeecc', '#ffccaa');

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

    return $returnval;
}

function getProviders() {
    $returnval = array("");
    $sql = "select fname, lname from users where authorized = 1 and " .
        "active = 1 and username != ''";
    $rez = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($rez); $iter++) {
        if (($row["fname"] != "") && ($row["lname"] != "")) {
            array_push($returnval, $row["fname"] . " " . $row["lname"]);
        }
    }
    return $returnval;
}

// ----------------------------------------------------------------------------
/**
 * GET ADDITIONAL INFORMATIONS 
 * 
 * only Dutch use
 * 
 * @param $pid - patient id
 * @return array
 */
function getPatientDataNL($pid) {
    sqlQuery("SET NAMES 'utf8'");
    $sql = "SELECT * FROM patient_data_NL WHERE pdn_id = '$pid'";
    return sqlQuery($sql);
}


/**
GET FACILITIES

returns all facilities or just the id for the first one
(FACILITY FILTERING (lemonsoftware))

@param string - if 'first' return first facility ordered by id
@return array | int for 'first' case
*/
function getFacilities($first = '') {
    $r = sqlStatement("SELECT * FROM facility ORDER BY id");
    $ret = array();
    while ( $row = sqlFetchArray($r) ) {
       $ret[] = $row;
}

        if ( $first == 'first') {
            return $ret[0]['id'];
        } else {
            return $ret;
        }
}

/**
GET SERVICE FACILITIES

returns all service_location facilities or just the id for the first one
(FACILITY FILTERING (CHEMED))

@param string - if 'first' return first facility ordered by id
@return array | int for 'first' case
*/
function getServiceFacilities($first = '') {
    $r = sqlStatement("SELECT * FROM facility WHERE service_location != 0 ORDER BY id");
    $ret = array();
    while ( $row = sqlFetchArray($r) ) {
       $ret[] = $row;
}

        if ( $first == 'first') {
            return $ret[0]['id'];
        } else {
            return $ret;
        }
}

//(CHEMED) facility filter
function getProviderInfo($providerID = "%", $providers_only = true, $facility = '' ) {
    $param1 = "";
    if ($providers_only) {
        $param1 = " AND authorized=1 ";
    }

    //--------------------------------
    //(CHEMED) facility filter
    $param2 = "";
    if ($facility) {
        $param2 = " AND facility_id = $facility ";
    }
    //--------------------------------

    $command = "=";
    if ($providerID == "%") {
        $command = "like";
    }
    $query = "select distinct id, username, lname, fname, authorized, info, facility " .
        "from users where username != '' and active = 1 and id $command '" .
        mysql_real_escape_string($providerID) . "' " . $param1 . $param2;
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
    $query = "select distinct id, username, lname, fname, authorized, info, facility " .
        "from users where active = 1 and username != '' and id $command '" .
        mysql_real_escape_string($providerID) . "' " . $param1;

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
  $sql = "select $given from insurance_data as insd " .
    "left join insurance_companies as ic on ic.id = insd.provider " .
    "where pid = '$pid' and type = '$type' order by date DESC limit 1";
  return sqlQuery($sql);
}

function getInsuranceDataByDate($pid, $date, $type,
  $given = "insd.*, DATE_FORMAT(subscriber_DOB,'%m/%d/%Y') as subscriber_DOB, ic.name as provider_name")
{ // this must take the date in the following manner: YYYY-MM-DD
  // this function recalls the insurance value that was most recently enterred from the
  // given date. it will call up most recent records up to and on the date given,
  // but not records enterred after the given date
  $sql = "select $given from insurance_data as insd " .
    "left join insurance_companies as ic on ic.id = provider " .
    "where pid = '$pid' and date_format(date,'%Y-%m-%d') <= '$date' and " .
    "type='$type' order by date DESC limit 1";
  return sqlQuery($sql);
}

function getEmployerData($pid, $given = "*")
{
    $sql = "select $given from employer_data where pid='$pid' order by date DESC limit 0,1";
    return sqlQuery($sql);
}

function _set_patient_inc_count($limit, $count, $where) {
  // When the limit is exceeded, find out what the unlimited count would be.
  $GLOBALS['PATIENT_INC_COUNT'] = $count;
  if ($limit != "all" && $GLOBALS['PATIENT_INC_COUNT'] >= $limit) {
    $tmp = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE $where");
    $GLOBALS['PATIENT_INC_COUNT'] = $tmp['count'];
  }
}

function getPatientLnames($lname = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    // Allow the last name to be followed by a comma and some part of a first name.
    // New behavior for searches:
    // Allows comma alone followed by some part of a first name
    // If the first letter of either name is capital, searches for name starting
    // with given substring (the expected behavior).  If it is lower case, it
    // it searches for the substring anywhere in the name.  This applies to either
    // last name or first name or both.  The arbitrary limit of 100 results is set
    // in the sql query below. --Mark Leeds
    $lname = trim($lname);
    $fname = '';
     if (preg_match('/^(.*),(.*)/', $lname, $matches)) {
         $lname = trim($matches[1]);
         $fname = trim($matches[2]);
    }
    $search_for_pieces1 = '';
    $search_for_pieces2 = '';
    if ($lname{0} != strtoupper($lname{0})) {$search_for_pieces1 = '%';}
    if ($fname{0} != strtoupper($fname{0})) {$search_for_pieces2 = '%';}

    $where = "lname LIKE '" . $search_for_pieces1 . "$lname%' " .
        "AND fname LIKE '" . $search_for_pieces2 . "$fname%'";
    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql);

    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter] = $row;

    _set_patient_inc_count($limit, count($returnval), $where);
    return $returnval;
}

function getPatientId($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    $where = "pubpid LIKE '$pid%'";
    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " limit $start, $limit";
    $rez = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where);
    return $returnval;
}

// return a collection of Patient PIDs
// new arg style by JRM March 2008
// orig function getPatientPID($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
function getPatientPID($args)
{
    $pid = "%";
    $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS";
    $orderby = "lname ASC, fname ASC";
    $limit="all";
    $start="0";

    // alter default values if defined in the passed in args
    if (isset($args['pid'])) { $pid = $args['pid']; }
    if (isset($args['given'])) { $given = $args['given']; }
    if (isset($args['orderby'])) { $orderby = $args['orderby']; }
    if (isset($args['limit'])) { $limit = $args['limit']; }
    if (isset($args['start'])) { $start = $args['start']; }

    $command = "=";
    if ($pid == -1) $pid = "%";
    elseif (empty($pid)) $pid = "NULL";

    if (strstr($pid,"%")) $command = "like";

    $sql="select $given from patient_data where pid $command '$pid' order by $orderby";
    if ($limit != "all") $sql .= " limit $start, $limit";

    $rez = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    return $returnval;
}

function getPatientName($pid) {
    if (empty($pid)) return "";
    $patientData = getPatientPID(array("pid"=>$pid));
    if (empty($patientData[0]['lname'])) return "";
    $patientName =  $patientData[0]['lname'] . ", " . $patientData[0]['fname'];
    return $patientName;
}

function getPatientDOB($DOB = "%", $given = "pid, id, lname, fname, mname", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    $DOB = fixDate($DOB, $DOB);
    $where = "DOB like '$DOB%'";
    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where);
    return $returnval;
}

function getPatientSSN($ss = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    $where = "ss LIKE '$ss%'";
    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where);
    return $returnval;
}

//(CHEMED) Search by phone number
function getPatientPhone($phone = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    $phone = ereg_replace( "[[:punct:]]","", $phone );
    $where = "REPLACE(REPLACE(phone_home, '-', ''), ' ', '') REGEXP '$phone'";
    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where);
    return $returnval;
}

function getPatientIds($given = "pid, id, lname, fname, mname", $orderby = "id ASC", $limit="all", $start="0")
{
    $sql="select $given from patient_data order by $orderby";

    if ($limit != "all")
        $sql .= " limit $start, $limit";

    $rez = sqlStatement($sql);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    return $returnval;
}

//----------------------input functions
function newPatientData(    $db_id="",
                $title = "",
                $fname = "",
                $lname = "",
                $mname = "",
                $sex = "",
                $DOB = "",
                $street = "",
                // DBC dutch use
                $nstreet = "",
                $nnr = "",
                $nadd = "",
                // EOS DBC
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
                $squad = 0,
                $pharmacy_id = 0,
                $drivers_license = "",
                $hipaa_notice = "",
                $hipaa_message = "",
                $regdate = "",
		// dutch specific
		$prefixlast = "",
		$prefixlastpartner = "",
		$lastpartner = "",
                $initials = "",
		$provider_data = array(),
		$referer_data = array()
		// DBC Dutch System

            )
{
    $DOB = fixDate($DOB);
    $regdate = fixDate($regdate);

    $fitness = 0;
    $referral_source = '';
    if ($pid) {
        $rez = sqlQuery("select id, fitness, referral_source from patient_data where pid = $pid");
        // Check for brain damage:
        if ($db_id != $rez['id']) {
            $errmsg = "Internal error: Attempt to change patient_data.id from '" .
              $rez['id'] . "' to '$db_id' for pid '$pid'";
            die($errmsg);
        }
        $fitness = $rez['fitness'];
        $referral_source = $rez['referral_source'];
    }

    // Get the default price level.
    $lrow = sqlQuery("SELECT option_id FROM list_options WHERE " .
      "list_id = 'pricelevel' ORDER BY is_default DESC, seq ASC LIMIT 1");
    $pricelevel = empty($lrow['option_id']) ? '' : $lrow['option_id'];

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
        drivers_license='$drivers_license',
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
        pharmacy_id = '$pharmacy_id',
        hipaa_mail = '$hipaa_mail',
        hipaa_voice = '$hipaa_voice',
        hipaa_notice = '$hipaa_notice',
        hipaa_message = '$hipaa_message',
        squad = '$squad',
        fitness='$fitness',
        referral_source='$referral_source',
        regdate='$regdate',
        pricelevel='$pricelevel',
        date=NOW()");

    $id = sqlInsert($query);

    // DBC 
    if ( !$db_id ) {
      // find the last inserted id for new patient case
      $db_id = empty($GLOBALS['dutchpc']) ? mysql_insert_id() : dsql_lastid();
    }
    // EOS DBC

    $foo = sqlQuery("select pid from patient_data where id='$id' order by date limit 0,1");

         // dutch use
	if ( $GLOBALS['dutchpc'] ) {
	        $init = strtoupper(preg_replace('/[\s\.\-_0-9]/','',$initials));
	        
            $querynl = ("REPLACE INTO patient_data_NL SET
                    pdn_id = '$db_id',
                    pdn_pxlast = '$prefixlast',
                    pdn_pxlastpar = '$prefixlastpartner',
                    pdn_lastpar = '$lastpartner',
                    pdn_street = '$nstreet',
                    pdn_number = '$nnr',
                    pdn_addition = '$nadd',
                    pdn_initials = '$init'
            ");

            if ( $db_id ) $idnl = sqlInsert($querynl); // only if exists
        }
        // EOS dutch use


       // DBC Dutch System
	// provider first
	if ( $GLOBALS['dutchpc'] && $db_id && $provider_data && $referer_data) {
	   $querypr = ("INSERT INTO cl_providers(pro_pid, pro_company, pro_initials, pro_prefix, pro_lname,
	           pro_street, pro_number, pro_addition, pro_city, pro_zipcode,  pro_phone, pro_fax, pro_email, pro_referer ) 
	           VALUES ($db_id, '{$provider_data['pro_company']}', '{$provider_data['pro_initials']}',
                  '{$provider_data['pro_prefix']}', '{$provider_data['pro_lname']}',  
                  '{$provider_data['pro_street']}', '{$provider_data['pro_number']}', '{$provider_data['pro_addition']}', 
                  '{$provider_data['pro_city']}',  '{$provider_data['pro_zipcode']}',
                  '{$provider_data['pro_phone']}', '{$provider_data['pro_fax']}',  '{$provider_data['pro_email']}', 
                  '{$provider_data['pro_referer']}') 
                  ON DUPLICATE KEY UPDATE  pro_referer = '{$provider_data['pro_referer']}',
                  pro_company = '{$provider_data['pro_company']}', pro_initials = '{$provider_data['pro_initials']}',
                  pro_prefix = '{$provider_data['pro_prefix']}',
                  pro_lname = '{$provider_data['pro_lname']}', pro_street = '{$provider_data['pro_street']}',
                  pro_number = '{$provider_data['pro_number']}', pro_addition = '{$provider_data['pro_addition']}',
                  pro_city = '{$provider_data['pro_city']}', pro_zipcode = '{$provider_data['pro_zipcode']}',
                  pro_phone = '{$provider_data['pro_phone']}', pro_fax = '{$provider_data['pro_fax']}',
                  pro_email = '{$provider_data['pro_email']}'
            ");
	   $idpr = sqlInsert($querypr);

	// referer if it's the case
	if ( $referer_data['ref_code'] ) {
	   $queryre = ("INSERT INTO cl_referers(ref_pid, ref_code, ref_company, ref_initials, ref_prefix,
                   ref_lname, ref_street, ref_number, ref_addition, ref_city, ref_zipcode,  ref_phone, ref_fax,
                   ref_email) 
	           VALUES ($db_id, {$referer_data['ref_code']}, '{$referer_data['ref_company']}', 
                   '{$referer_data['ref_initials']}', '{$referer_data['ref_prefix']}',
                   '{$referer_data['ref_lname']}', '{$referer_data['ref_street']}', '{$referer_data['ref_number']}', 
                   '{$referer_data['ref_addition']}', '{$referer_data['ref_city']}', '{$referer_data['ref_zipcode']}',
                  '{$referer_data['ref_phone']}', '{$referer_data['ref_fax']}',  '{$referer_data['ref_email']}')
	          ON DUPLICATE KEY UPDATE ref_code = {$referer_data['ref_code']},
                  ref_company = '{$referer_data['ref_company']}', ref_initials = '{$referer_data['ref_initials']}',
                  ref_prefix = '{$referer_data['ref_prefix']}',
                  ref_lname = '{$referer_data['ref_lname']}', ref_street = '{$referer_data['ref_street']}',
                  ref_number = '{$referer_data['ref_number']}', ref_addition = '{$referer_data['ref_addition']}',
                  ref_city = '{$referer_data['ref_city']}', ref_zipcode = '{$referer_data['ref_zipcode']}',
                  ref_phone = '{$referer_data['ref_phone']}', ref_fax = '{$referer_data['ref_fax']}',
                  ref_email = '{$referer_data['ref_email']}'
            ");
	   $idre = sqlInsert($queryre);
	} 
	} // if globals
        // EOS DBC

    sync_patient($id,$fname,$lname,$street,$city,$postal_code,$state,$phone_home,
                $phone_biz,$phone_cell,$email,$pid);

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
            if ($dmy[0] != 0 || $dmy[1] != 0 || $dmy[2] != 0) {
              if ($dmy[2] < 1000) $dmy[2] += 1900;
              if ($dmy[2] < 1910) $dmy[2] += 100;
            }
            $fixed_date = sprintf("%04u-%02u-%02u", $dmy[2], $dmy[0], $dmy[1]);
        }
    }

    return $fixed_date;
}

function updatePatientData($pid,$new)
{
  /*******************************************************************
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
  *******************************************************************/

  // The above was broken, though seems intent to insert a new patient_data
  // row for each update.  A good idea, but nothing is doing that yet so
  // the code below does not yet attempt it.

  $new['DOB'] = fixDate($new['DOB']);
  $db_id = $new['id'];

  $rez = sqlQuery("SELECT * FROM patient_data WHERE id = '$db_id'");
  // Check for brain damage:
  if ($pid != $rez['pid']) {
    $errmsg = "Internal error: Attempt to change patient data with pid = '" .
      $rez['pid'] . "' when current pid is '$pid' for id '$db_id'";
    die($errmsg);
  }
  $sql = "UPDATE patient_data SET date = NOW()";
  foreach ($new as $key => $value) {
    $sql .= ", $key = '$value'";
  }
  $sql .= " WHERE id = '$db_id'";
  $id = sqlInsert($sql);

  sync_patient($db_id,$rez['fname'],$rez['lname'],$rez['street'],$rez['city'],
    $rez['postal_code'],$rez['state'],$rez['phone_home'],$rez['phone_biz'],
    $rez['phone_cell'],$rez['email'],$rez['pid']);

  return $id;
}

function newEmployerData(    $pid,
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

function updateEmployerData($pid, $new)
{
  $old = getEmployerData($pid);
  $set = '';
  $modified = false;
  foreach (array('name','street','city','state','postal_code','country') as $key) {
    $value = empty($old[$key]) ? '' : addslashes($old[$key]);
    if (isset($new[$key]) && strcmp($new[$key], $value) != 0) {
      $value = $new[$key];
      $modified = true;
    }
    $set .= "$key = '$value', ";
  }
  if ($modified) {
    $set .= "pid = '$pid', date = NOW()";
    return sqlInsert("INSERT INTO employer_data SET $set");
  }
  return $old['id'];
}

// This updates or adds the given insurance data info, while retaining any
// previously added insurance_data rows that should be preserved.
// This does not directly support the maintenance of non-current insurance.
//
function newInsuranceData(
  $pid,
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
  $subscriber_sex = "",
  $effective_date = "0000-00-00",
  $accept_assignment = "TRUE")
{
  if (strlen($type) <= 0) return FALSE;

  // If a bad date was passed, err on the side of caution.
  $effective_date = fixDate($effective_date, date('Y-m-d'));

  $idres = sqlStatement("SELECT * FROM insurance_data WHERE " .
    "pid = '$pid' AND type = '$type' ORDER BY date DESC");
  $idrow = sqlFetchArray($idres);

  // Replace the most recent entry in any of the following cases:
  // * Its effective date is >= this effective date.
  // * It is the first entry and it has no (insurance) provider.
  // * There is no encounter that is earlier than the new effective date but
  //   on or after the old effective date.
  // Otherwise insert a new entry.

  $replace = false;
  if ($idrow) {
    if (strcmp($idrow['date'], $effective_date) > 0) {
      $replace = true;
    }
    else {
      if (!$idrow['provider'] && !sqlFetchArray($idres)) {
        $replace = true;
      }
      else {
        $ferow = sqlQuery("SELECT count(*) AS count FROM form_encounter " .
          "WHERE pid = '$pid' AND date < '$effective_date 00:00:00' AND " .
          "date >= '" . $idrow['date'] . " 00:00:00'");
        if ($ferow['count'] == 0) $replace = true;
      }
    }
  }

  if ($replace) {

    // TBD: This is a bit dangerous in that a typo in entering the effective
    // date can wipe out previous insurance history.  So we want some data
    // entry validation somewhere.
    sqlStatement("DELETE FROM insurance_data WHERE " .
      "pid = '$pid' AND type = '$type' AND date >= '$effective_date' AND " .
      "id != " . $idrow['id']);

    $data = array();
    $data['type'] = $type;
    $data['provider'] = $provider;
    $data['policy_number'] = $policy_number;
    $data['group_number'] = $group_number;
    $data['plan_name'] = $plan_name;
    $data['subscriber_lname'] = $subscriber_lname;
    $data['subscriber_mname'] = $subscriber_mname;
    $data['subscriber_fname'] = $subscriber_fname;
    $data['subscriber_relationship'] = $subscriber_relationship;
    $data['subscriber_ss'] = $subscriber_ss;
    $data['subscriber_DOB'] = $subscriber_DOB;
    $data['subscriber_street'] = $subscriber_street;
    $data['subscriber_postal_code'] = $subscriber_postal_code;
    $data['subscriber_city'] = $subscriber_city;
    $data['subscriber_state'] = $subscriber_state;
    $data['subscriber_country'] = $subscriber_country;
    $data['subscriber_phone'] = $subscriber_phone;
    $data['subscriber_employer'] = $subscriber_employer;
    $data['subscriber_employer_city'] = $subscriber_employer_city;
    $data['subscriber_employer_street'] = $subscriber_employer_street;
    $data['subscriber_employer_postal_code'] = $subscriber_employer_postal_code;
    $data['subscriber_employer_state'] = $subscriber_employer_state;
    $data['subscriber_employer_country'] = $subscriber_employer_country;
    $data['copay'] = $copay;
    $data['subscriber_sex'] = $subscriber_sex;
    $data['pid'] = $pid;
    $data['date'] = $effective_date;
    $data['accept_assignment'] = $accept_assignment;
    updateInsuranceData($idrow['id'], $data);
    return $idrow['id'];
  }
  else {
    return sqlInsert("INSERT INTO insurance_data SET
      type = '$type',
      provider = '$provider',
      policy_number = '$policy_number',
      group_number = '$group_number',
      plan_name = '$plan_name',
      subscriber_lname = '$subscriber_lname',
      subscriber_mname = '$subscriber_mname',
      subscriber_fname = '$subscriber_fname',
      subscriber_relationship = '$subscriber_relationship',
      subscriber_ss = '$subscriber_ss',
      subscriber_DOB = '$subscriber_DOB',
      subscriber_street = '$subscriber_street',
      subscriber_postal_code = '$subscriber_postal_code',
      subscriber_city = '$subscriber_city',
      subscriber_state = '$subscriber_state',
      subscriber_country = '$subscriber_country',
      subscriber_phone = '$subscriber_phone',
      subscriber_employer = '$subscriber_employer',
      subscriber_employer_city = '$subscriber_employer_city',
      subscriber_employer_street = '$subscriber_employer_street',
      subscriber_employer_postal_code = '$subscriber_employer_postal_code',
      subscriber_employer_state = '$subscriber_employer_state',
      subscriber_employer_country = '$subscriber_employer_country',
      copay = '$copay',
      subscriber_sex = '$subscriber_sex',
      pid = '$pid',
      date = '$effective_date',
      accept_assignment = '$accept_assignment'
    ");
  }
}

// This is used internally only.
function updateInsuranceData($id, $new)
{
  $fields = sqlListFields("insurance_data");
  $use = array();

  while(list($key, $value) = each ($new)) {
    if (in_array($key, $fields)) {
      $use[$key] = $value;
    }
  }

  $sql = "UPDATE insurance_data SET ";
  while(list($key, $value) = each($use))
    $sql .= $key . " = '$value', ";
  $sql = substr($sql, 0, -2) . " WHERE id = '$id'";

  sqlStatement($sql);
}

function newHistoryData($pid, $new=false) {
  $sql = "insert into history_data set pid = '$pid', date = NOW()";
  if ($new) {
    while(list($key, $value) = each($new)) {
      if (!get_magic_quotes_gpc()) $value = addslashes($value);
      $sql .= ", $key = '$value'";
    }
  }
  return sqlInsert($sql);
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
                $phone_biz,$phone_cell,$email,$pid="")
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
  $customer_info['phone3'] = $phone_cell;
  $customer_info['phone3comment'] = "Cell Phone";
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


// ----------------------------------------------------------------------------
/**
 * DROPDOWN FOR COUNTRIES
 * 
 * build a dropdown with all countries from geo_country_reference
 * 
 * @param int $selected - id for selected record
 * @param string $name - the name/id for select form
 * @return void - just echo the html encoded string
 */
function dropdown_countries($selected = 0, $name = 'country_code') {
    $r = sqlStatement("SELECT * FROM geo_country_reference ORDER BY countries_name");

    $string = "<select name='$name' id='$name'>";
    while ( $row = sqlFetchArray($r) ) {
        $sufix = ( $selected == $row['countries_id']) ? 'selected="selected"' : '';
        $string .= "<option value='{$row['countries_id']}' $sufix>{$row['countries_name']}</option>";
    }

    $string .= '</select>';
    echo $string;
}


// ----------------------------------------------------------------------------
/**
 * DROPDOWN FOR YES/NO
 * 
 * build a dropdown with two options (yes - 1, no - 0)
 * 
 * @param int $selected - id for selected record
 * @param string $name - the name/id for select form
 * @return void - just echo the html encoded string 
 */
function dropdown_yesno($selected = 0, $name = 'yesno') {
    $string = "<select name='$name' id='$name'>";

    $selected = (int)$selected;
    if ( $selected == 0) { $sel1 = 'selected="selected"'; $sel2 = ''; }
    else { $sel2 = 'selected="selected"'; $sel1 = ''; }

    $string .= "<option value='0' $sel1>" .xl('No'). "</option>";
    $string .= "<option value='1' $sel2>" .xl('Yes'). "</option>";
    $string .= '</select>';

    echo $string;
}

// ----------------------------------------------------------------------------
/**
 * DROPDOWN FOR MALE/FEMALE options
 * 
 * build a dropdown with three options (unselected/male/female)
 * 
 * @param int $selected - id for selected record
 * @param string $name - the name/id for select form
 * @return void - just echo the html encoded string
 */
function dropdown_sex($selected = 0, $name = 'sex') {
    $string = "<select name='$name' id='$name'>";

    if ( $selected == 1) { $sel1 = 'selected="selected"'; $sel2 = ''; $sel0 = ''; }
    else if ($selected == 2) { $sel2 = 'selected="selected"'; $sel1 = ''; $sel0 = ''; }
    else { $sel0 = 'selected="selected"'; $sel1 = ''; $sel2 = ''; }

    $string .= "<option value='0' $sel0>" .xl('Unselected'). "</option>";
    $string .= "<option value='1' $sel1>" .xl('Male'). "</option>";
    $string .= "<option value='2' $sel2>" .xl('Female'). "</option>";
    $string .= '</select>';

    echo $string;
}

// ----------------------------------------------------------------------------
/**
 * DROPDOWN FOR MARITAL STATUS
 * 
 * build a dropdown with marital status
 * 
 * @param int $selected - id for selected record
 * @param string $name - the name/id for select form
 * @return void - just echo the html encoded string
 */
function dropdown_marital($selected = 0, $name = 'status') {
    $string = "<select name='$name' id='$name'>";

    $statii = array('married','single','divorced','widowed','separated','domestic partner');

    foreach ( $statii as $st ) {
        $sel = ( $st == $selected ) ? 'selected="selected"' : '';
        $string .= '<option value="' .$st. '" '.$sel.' >' .xl($st). '</option>';
    }

    $string .= '</select>';

    echo $string;
}

// ----------------------------------------------------------------------------
/**
 * DROPDOWN FOR PROVIDERS
 * 
 * build a dropdown with all providers
 * 
 * @param int $selected - id for selected record
 * @param string $name - the name/id for select form
 * @return void - just echo the html encoded string
 */
function dropdown_providers($selected = 0, $name = 'status') {
    $provideri = getProviderInfo();

    $string = "<select name='$name' id='$name'>";
    $string .= '<option value="">' .xl('Unassigned'). '</option>';
    foreach ( $provideri as $s ) {
        $sel = ( $s['id'] == $selected ) ? 'selected="selected"' : '';
        $string .= '<option value="' .$s['id']. '" '.$sel.' >' .ucwords($s['fname']." ".$s['lname']). '</option>';
    }

    $string .= '</select>';

    echo $string;
}

// ----------------------------------------------------------------------------
/**
 * DROPDOWN FOR INSURANCE COMPANIES
 * 
 * build a dropdown with all insurers
 * 
 * @param int $selected - id for selected record
 * @param string $name - the name/id for select form
 * @return void - just echo the html encoded string
 */
function dropdown_insurance($selected = 0, $name = 'iprovider') {
    $insurancei = getInsuranceProviders();

    $string = "<select name='$name' id='$name'>";
    $string .= '<option value="0">Onbekend</option>';
    foreach ( $insurancei as $iid => $iname ) {
        $sel = ( strtolower($iid) == strtolower($selected) ) ? 'selected="selected"' : '';
        $string .= '<option value="' .$iid. '" '.$sel.' >' .$iname. '(' .$iid. ')</option>';
    }

    $string .= '</select>';

    echo $string;
}


// ----------------------------------------------------------------------------
/**
 * COUNTRY CODE
 * 
 * return the name or the country code, function of arguments
 * 
 * @param int $country_code
 * @param string $country_name
 * @return string | int - name or code
 */
function country_code($country_code = 0, $country_name = '') {
    $strint = '';
    if ( $country_code ) {
        $sql = "SELECT countries_name AS res FROM geo_country_reference WHERE countries_id = '$country_code'";
    } else {
        $sql = "SELECT countries_id AS res FROM geo_country_reference WHERE countries_name = '$country_name'";
    }

    $db = $GLOBALS['adodb']['db'];
    $result = $db->Execute($sql);
    if ($result && !$result->EOF) {
        $strint = $result->fields['res'];
    }

    return $strint;
}

// ----------------------------------------------------------------------------
/**
 * GET INSURANCE DATA
 * (Dutch usage)
 * 
 * return the insurer(s) and some patient
 * 
 * @param int $pid - patient id
 * @param int $current 1-current 0-all insurers
 * @return array
 */
function get_insurers_nl($pid = 0, $current = 1) {
    if ( !$pid ) return FALSE;

    $join = 'insurance_companies ic ON ic.id = pi.pin_provider';
    $r = dsql_select('*', 'patient_insurers_NL pi', array('pin_pid' => $pid), 'pin_date DESC', $join);

    while ( $row = mysql_fetch_array($r) ) {
        $rez[] = $row;
    } // while

    return ( $current ? $rez[0] : $rez );

}

// ----------------------------------------------------------------------------
/**
 * SET INSURANCE DATA
 * (Dutch usage)
 * 
 * set the insurer for a patient
 * 
 * @param int $pid - patient id
 * @param int $insid - insurer id
 * @return void
 */
function set_insurer_nl($pid = 0, $insid = 0, $date = '', $policy = '') {
    if ( !$pid || !$insid || ($date == '0000-00-00') ) return FALSE;

    $fields = array('pin_pid', 'pin_provider', 'pin_date', 'pin_policy');
    $values = array($pid, $insid, $date, $policy);
    dsql_insert('patient_insurers_NL', $fields, $values);
}

// ----------------------------------------------------------------------------
/**
 * FIND THE INSURERS STATUS FOR A PATIENT
 * (Dutch usage)
 * 
 * return the answer for the question: is this the first insurer or not?
 * 
 * @param int $pid - patient id
 * @return int - the number of insurer records ( 0 - no insurer; >0 some insurers)
 */
function total_insurers($pid = 0) {
    if ( !$pid ) return FALSE;

    $r = dsql_select('COUNT(*) AS c', 'patient_insurers_NL', array('pin_pid' => $pid));
    $row = mysql_fetch_array($r);

    return $row['c'];
}


function DBToDate ($date)
{
    $date=substr ($date,5,2)."/".substr ($date,8,2)."/".substr($date, 0,4);
    return $date;
}
?>
