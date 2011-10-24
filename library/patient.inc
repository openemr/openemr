<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("{$GLOBALS['srcdir']}/sql.inc");
require_once(dirname(__FILE__) . "/classes/WSWrapper.class.php");
require_once("{$GLOBALS['srcdir']}/formdata.inc.php");

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
    $sql = "select $given from patient_data where pid=? order by date DESC limit 0,1";
    return sqlQuery($sql, array($pid) );
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

function getInsuranceProvider($ins_id) {
    
    $sql = "select name from insurance_companies where id=?";
    $row = sqlQuery($sql,array($ins_id));
    return $row['name'];
    
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
// Get one facility row.  If the ID is not specified, then get either the
// "main" (billing) facility, or the default facility of the currently
// logged-in user.  This was created to support genFacilityTitle() but
// may find additional uses.
//
function getFacility($facid=0) {

  //create a sql binding array
  $sqlBindArray = array();
  
  if ($facid > 0) {
    $query = "SELECT * FROM facility WHERE id = ?";
    array_push($sqlBindArray,$facid);
  }
  else if ($facid == 0) {
    $query = "SELECT * FROM facility ORDER BY " .
      "billing_location DESC, service_location, id LIMIT 1";
  }
  else {
    $query = "SELECT facility.* FROM users, facility WHERE " .
      "users.id = ? AND " .
      "facility.id = users.facility_id";
    array_push($sqlBindArray,$_SESSION['authUserID']);
  }
  return sqlQuery($query,$sqlBindArray);
}

// Generate a report title including report name and facility name, address
// and phone.
//
function genFacilityTitle($repname='', $facid=0) {
  $s = '';
  $s .= "<table class='ftitletable'>\n";
  $s .= " <tr>\n";
  $s .= "  <td class='ftitlecell1'>$repname</td>\n";
  $s .= "  <td class='ftitlecell2'>\n";
  $r = getFacility($facid);
  if (!empty($r)) {
    $s .= "<b>" . htmlspecialchars( $r['name'], ENT_NOQUOTES) . "</b>\n";
    if ($r['street']) $s .= "<br />" . htmlspecialchars( $r['street'], ENT_NOQUOTES) . "\n";
    if ($r['city'] || $r['state'] || $r['postal_code']) {
      $s .= "<br />";
      if ($r['city']) $s .= htmlspecialchars( $r['city'], ENT_NOQUOTES);
      if ($r['state']) {
        if ($r['city']) $s .= ", \n";
        $s .= htmlspecialchars( $r['state'], ENT_NOQUOTES);
      }
      if ($r['postal_code']) $s .= " " . htmlspecialchars( $r['postal_code'], ENT_NOQUOTES);
      $s .= "\n";
    }
    if ($r['country_code']) $s .= "<br />" . htmlspecialchars( $r['country_code'], ENT_NOQUOTES) . "\n";
    if (preg_match('/[1-9]/', $r['phone'])) $s .= "<br />" . htmlspecialchars( $r['phone'], ENT_NOQUOTES) . "\n";
  }
  $s .= "  </td>\n";
  $s .= " </tr>\n";
  $s .= "</table>\n";
  return $s;
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
    if ($providers_only === 'any') {
      $param1 = " AND authorized = 1 AND active = 1 ";
    }
    else if ($providers_only) {
      $param1 = " AND authorized = 1 AND calendar = 1 ";
    }

    //--------------------------------
    //(CHEMED) facility filter
    $param2 = "";
    if ($facility) {
      if ($GLOBALS['restrict_user_facility']) {
        $param2 = " AND (facility_id = $facility 
          OR  $facility IN
	        (select facility_id 
	        from users_facility
	        where tablename = 'users'
	        and table_id = id)
	        )
          ";
      }
      else {
        $param2 = " AND facility_id = $facility ";
      }
    }
    //--------------------------------

    $command = "=";
    if ($providerID == "%") {
        $command = "like";
    }
    $query = "select distinct id, username, lname, fname, authorized, info, facility " .
        "from users where username != '' and active = 1 and id $command '" .
        mysql_real_escape_string($providerID) . "' " . $param1 . $param2;
    // sort by last name -- JRM June 2008
    $query .= " ORDER BY lname, fname ";
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
    $pi = getProviderInfo($providerID, 'any');
    if (strlen($pi[0]["lname"]) > 0) {
        return $pi[0]['fname'] . " " . $pi[0]['lname'];
    }
    return "";
}

function getProviderId($providerName) {
    $query = "select id from users where username = ?";
    $rez = sqlStatement($query, array($providerName) );
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

function getHistoryData($pid, $given = "*", $dateStart='',$dateEnd='')
{

    if ($dateStart && $dateEnd) {
        $res = sqlQuery("select $given from history_data where pid = ? and date >= ? and date <= ? order by date DESC limit 0,1", array($pid,$dateStart,$dateEnd) );
    }
    else if ($dateStart && !$dateEnd) {
        $res = sqlQuery("select $given from history_data where pid = ? and date >= ? order by date DESC limit 0,1", array($pid,$dateStart) );
    }
    else if (!$dateStart && $dateEnd) {
        $res = sqlQuery("select $given from history_data where pid = ? and date <= ? order by date DESC limit 0,1", array($pid,$dateEnd) );
    }
    else {
        $res = sqlQuery("select $given from history_data where pid=? order by date DESC limit 0,1", array($pid) );
    }

    return $res;
}

// function getInsuranceData($pid, $type = "primary", $given = "insd.*, DATE_FORMAT(subscriber_DOB,'%m/%d/%Y') as subscriber_DOB, ic.name as provider_name")
function getInsuranceData($pid, $type = "primary", $given = "insd.*, ic.name as provider_name")
{
  $sql = "select $given from insurance_data as insd " .
    "left join insurance_companies as ic on ic.id = insd.provider " .
    "where pid = ? and type = ? order by date DESC limit 1";
  return sqlQuery($sql, array($pid, $type) );
}

function getInsuranceDataByDate($pid, $date, $type,
  $given = "insd.*, DATE_FORMAT(subscriber_DOB,'%m/%d/%Y') as subscriber_DOB, ic.name as provider_name")
{ // this must take the date in the following manner: YYYY-MM-DD
  // this function recalls the insurance value that was most recently enterred from the
  // given date. it will call up most recent records up to and on the date given,
  // but not records enterred after the given date
  $sql = "select $given from insurance_data as insd " .
    "left join insurance_companies as ic on ic.id = provider " .
    "where pid = ? and date_format(date,'%Y-%m-%d') <= ? and " .
    "type=? order by date DESC limit 1";
  return sqlQuery($sql, array($pid,$date,$type) );
}

function getEmployerData($pid, $given = "*")
{
    $sql = "select $given from employer_data where pid=? order by date DESC limit 0,1";
    return sqlQuery($sql, array($pid) );
}

function _set_patient_inc_count($limit, $count, $where, $whereBindArray=array()) {
  // When the limit is exceeded, find out what the unlimited count would be.
  $GLOBALS['PATIENT_INC_COUNT'] = $count;
  // if ($limit != "all" && $GLOBALS['PATIENT_INC_COUNT'] >= $limit) {
  if ($limit != "all") {
    $tmp = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE $where", $whereBindArray);
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
    if ((strlen($lname) < 1)|| ($lname{0} != strtoupper($lname{0}))) {$search_for_pieces1 = '%';}
    if ((strlen($fname) < 1)|| ($fname{0} != strtoupper($fname{0}))) {$search_for_pieces2 = '%';}

    $sqlBindArray = array();
    $where = "lname LIKE ? AND fname LIKE ? ";
    array_push($sqlBindArray, $search_for_pieces1.$lname."%", $search_for_pieces2.$fname."%");
        if (!empty($GLOBALS['pt_restrict_field'])) {
                if ( $_SESSION{"authUser"} != 'admin' || $GLOBALS['pt_restrict_admin'] ) {
                        $where .= "AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                            " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                            add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
			array_push($sqlBindArray, $_SESSION{"authUser"});
                }
        }

    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql, $sqlBindArray);

    $returnval=array();
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter] = $row;

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

function getPatientId($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{

    $sqlBindArray = array();
    $where = "pubpid LIKE ? ";
    array_push($sqlBindArray, $pid."%");
        if (!empty($GLOBALS['pt_restrict_field']) && $GLOBALS['pt_restrict_by_id'] ) {
                if ( $_SESSION{"authUser"} != 'admin' || $GLOBALS['pt_restrict_admin'] ) {
                        $where .= "AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                                " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                                add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
                        array_push($sqlBindArray, $_SESSION{"authUser"});
                }
        }

    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " limit $start, $limit";
    $rez = sqlStatement($sql, $sqlBindArray);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

function getByPatientDemographics($searchTerm = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
  $layoutCols = sqlStatement( "SELECT field_id FROM layout_options WHERE form_id='DEM' AND group_name not like (? ) AND uor !=0", array("%".Employer."%") );

  $sqlBindArray = array();
  $where = "";
  for($iter=0; $row=sqlFetchArray($layoutCols); $iter++) {
    if ( $iter > 0 ) {
      $where .= " or ";
    }
    $where .= " ".add_escape_custom($row["field_id"])." like ? ";
    array_push($sqlBindArray, "%".$searchTerm."%");
  }

  $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
  if ($limit != "all") $sql .= " limit $start, $limit";
  $rez = sqlStatement($sql, $sqlBindArray);
  for($iter=0; $row=sqlFetchArray($rez); $iter++)
    $returnval[$iter]=$row;
  _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
  return $returnval;
}

function getByPatientDemographicsFilter($searchFields, $searchTerm = "%",
  $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS",
  $orderby = "lname ASC, fname ASC", $limit="all", $start="0", $search_service_code='')
{
	$layoutCols = split( '~', $searchFields );
  $sqlBindArray = array();
  $where = "";
  $i = 0;
  foreach ($layoutCols as $val) {
    if (empty($val)) continue;
		if ( $i > 0 ) {
		   $where .= " or ";
		}
    if ($val == 'pid') {
  		$where .= " ".add_escape_custom($val)." = ? ";
                array_push($sqlBindArray, $searchTerm);
    }
    else {
  		$where .= " ".add_escape_custom($val)." like ? ";
                array_push($sqlBindArray, $searchTerm."%");
    }
		$i++;
	}

  // If no search terms, ensure valid syntax.
  if ($i == 0) $where = "1 = 1";

  // If a non-empty service code was given, then restrict to patients who
  // have been provided that service.  Since the code is used in a LIKE
  // clause, % and _ wildcards are supported.
  if ($search_service_code) {
    $where = "( $where ) AND " .
      "( SELECT COUNT(*) FROM billing AS b WHERE " .
      "b.pid = patient_data.pid AND " .
      "b.activity = 1 AND " .
      "b.code_type != 'COPAY' AND " .
      "b.code LIKE ? " .
      ") > 0";
    array_push($sqlBindArray, $search_service_code);
  }

  $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
  if ($limit != "all") $sql .= " limit $start, $limit";
  $rez = sqlStatement($sql, $sqlBindArray);
  for($iter=0; $row=sqlFetchArray($rez); $iter++)
      $returnval[$iter]=$row;
  _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
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

/* return a patient's name in the format LAST, FIRST */
function getPatientName($pid) {
    if (empty($pid)) return "";
    $patientData = getPatientPID(array("pid"=>$pid));
    if (empty($patientData[0]['lname'])) return "";
    $patientName =  $patientData[0]['lname'] . ", " . $patientData[0]['fname'];
    return $patientName;
}

/* find patient data by DOB */
function getPatientDOB($DOB = "%", $given = "pid, id, lname, fname, mname", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    $DOB = fixDate($DOB, $DOB);
    $sqlBindArray = array();
    $where = "DOB like ? ";
    array_push($sqlBindArray, $DOB."%");
        if (!empty($GLOBALS['pt_restrict_field'])) {
                if ( $_SESSION{"authUser"} != 'admin' || $GLOBALS['pt_restrict_admin'] ) {
                        $where .= "AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                                " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                                add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
                        array_push($sqlBindArray, $_SESSION{"authUser"});
                }
        }

    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";

    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql, $sqlBindArray);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

/* find patient data by SSN */
function getPatientSSN($ss = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    $sqlBindArray = array();
    $where = "ss LIKE ?";
    array_push($sqlBindArray, $ss."%");
    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql, $sqlBindArray);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

//(CHEMED) Search by phone number
function getPatientPhone($phone = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
{
    $phone = ereg_replace( "[[:punct:]]","", $phone );
    $sqlBindArray = array();
    $where = "REPLACE(REPLACE(phone_home, '-', ''), ' ', '') REGEXP ?";
    array_push($sqlBindArray, $phone);
    $sql="SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") $sql .= " LIMIT $start, $limit";

    $rez = sqlStatement($sql, $sqlBindArray);
    for($iter=0; $row=sqlFetchArray($rez); $iter++)
        $returnval[$iter]=$row;

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
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
                $regdate = ""
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
 
    if ( !$db_id ) {
      // find the last inserted id for new patient case
      $db_id = mysql_insert_id();
    }

    $foo = sqlQuery("select pid from patient_data where id='$id' order by date limit 0,1");

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
            // phone_country_code indicates format of ambiguous input dates.
            if ($GLOBALS['phone_country_code'] == 1)
              $fixed_date = sprintf("%04u-%02u-%02u", $dmy[2], $dmy[0], $dmy[1]);
            else
              $fixed_date = sprintf("%04u-%02u-%02u", $dmy[2], $dmy[1], $dmy[0]);
        }
    }

    return $fixed_date;
}

function pdValueOrNull($key, $value) {
  if (($key == 'DOB' || $key == 'regdate' || $key == 'contrastart' ||
    substr($key, 0, 8) == 'userdate') &&
    (empty($value) || $value == '0000-00-00'))
  {
    return "NULL";
  }
  else {
    return "'$value'";
  }
}

// Create or update patient data from an array.
//
function updatePatientData($pid, $new, $create=false)
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

  if ($create) {
    $sql = "INSERT INTO patient_data SET pid = '$pid', date = NOW()";
    foreach ($new as $key => $value) {
      if ($key == 'id') continue;
      $sql .= ", `$key` = " . pdValueOrNull($key, $value);
    }
    $db_id = sqlInsert($sql);
  }
  else {
    $db_id = $new['id'];
    $rez = sqlQuery("SELECT pid FROM patient_data WHERE id = '$db_id'");
    // Check for brain damage:
    if ($pid != $rez['pid']) {
      $errmsg = "Internal error: Attempt to change patient data with pid = '" .
        $rez['pid'] . "' when current pid is '$pid' for id '$db_id'";
      die($errmsg);
    }
    $sql = "UPDATE patient_data SET date = NOW()";
    foreach ($new as $key => $value) {
      $sql .= ", `$key` = " . pdValueOrNull($key, $value);
    }
    $sql .= " WHERE id = '$db_id'";
    sqlStatement($sql);
  }

  $rez = sqlQuery("SELECT * FROM patient_data WHERE id = '$db_id'");
  sync_patient($db_id,$rez['fname'],$rez['lname'],$rez['street'],$rez['city'],
    $rez['postal_code'],$rez['state'],$rez['phone_home'],$rez['phone_biz'],
    $rez['phone_cell'],$rez['email'],$rez['pid']);

  return $db_id;
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

// Create or update employer data from an array.
//
function updateEmployerData($pid, $new, $create=false)
{
  $colnames = array('name','street','city','state','postal_code','country');

  if ($create) {
    $set .= "pid = '$pid', date = NOW()";
    foreach ($colnames as $key) {
      $value = isset($new[$key]) ? $new[$key] : '';
      $set .= ", `$key` = '$value'";
    }
    return sqlInsert("INSERT INTO employer_data SET $set");
  }
  else {
    $set = '';
    $old = getEmployerData($pid);
    $modified = false;
    foreach ($colnames as $key) {
      $value = empty($old[$key]) ? '' : addslashes($old[$key]);
      if (isset($new[$key]) && strcmp($new[$key], $value) != 0) {
        $value = $new[$key];
        $modified = true;
      }
      $set .= "`$key` = '$value', ";
    }
    if ($modified) {
      $set .= "pid = '$pid', date = NOW()";
      return sqlInsert("INSERT INTO employer_data SET $set");
    }
    return $old['id'];
  }
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
    $sql .= "`$key` = '$value', ";
  $sql = substr($sql, 0, -2) . " WHERE id = '$id'";

  sqlStatement($sql);
}

function newHistoryData($pid, $new=false) {
  $arraySqlBind = array();
  $sql = "insert into history_data set pid = ?, date = NOW()";
  array_push($arraySqlBind,$pid);
  if ($new) {
    while(list($key, $value) = each($new)) {
      array_push($arraySqlBind,$value);
      $sql .= ", `$key` = ?";
    }
  }
  return sqlInsert($sql, $arraySqlBind );
}

function updateHistoryData($pid,$new)
{
        $real = getHistoryData($pid);
        while(list($key, $value) = each ($new))
                $real[$key] = $value;
        $real['id'] = "";
	// need to unset date, so can reset it below
	unset($real['date']);

        $arraySqlBind = array();
        $sql = "insert into history_data set `date` = NOW(), ";
        while(list($key, $value) = each($real)) {
	        array_push($arraySqlBind,$value);
                $sql .= "`$key` = ?, ";
	}
        $sql = substr($sql, 0, -2);

        return sqlInsert($sql, $arraySqlBind );
}

function sync_patient($id,$fname,$lname,$street,$city,$postal_code,$state,$phone_home,
                $phone_biz,$phone_cell,$email,$pid="")
{
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) return;
    if (!$GLOBALS['oer_config']['ws_accounting']['enabled']) return;

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

// Returns Age 
//   in months if < 2 years old
//   in years  if > 2 years old
// given YYYYMMDD from MySQL DATE_FORMAT(DOB,'%Y%m%d')
// (optional) nowYMD is a date in YYYYMMDD format
function getPatientAge($dobYMD, $nowYMD=null)
{
    // strip any dashes from the DOB
    $dobYMD = preg_replace("/-/", "", $dobYMD);
    $dobDay = substr($dobYMD,6,2); $dobMonth = substr($dobYMD,4,2); $dobYear = substr($dobYMD,0,4);
    
    // set the 'now' date values
    if ($nowYMD == null) {
        $nowDay = date("d");
        $nowMonth = date("m");
        $nowYear = date("Y");
    }
    else {
        $nowDay = substr($nowYMD,6,2);
        $nowMonth = substr($nowYMD,4,2);
        $nowYear = substr($nowYMD,0,4);
    }

    $dayDiff = $nowDay - $dobDay;
    $monthDiff = $nowMonth - $dobMonth;
    $yearDiff = $nowYear - $dobYear;

    $ageInMonths = (($nowYear * 12) + $nowMonth) - (($dobYear*12) + $dobMonth);

    if ( $ageInMonths > 24 ) {
        $age = $yearDiff;
        if (($monthDiff == 0) && ($dayDiff < 0)) { $age -= 1; }
        else if ($monthDiff < 0) { $age -= 1; }
    }
    else  {
        $age = "$ageInMonths month"; 
    }

    return $age;
}


// Returns Age in days
//   in months if < 2 years old
//   in years  if > 2 years old
// given YYYYMMDD from MySQL DATE_FORMAT(DOB,'%Y%m%d')
// (optional) nowYMD is a date in YYYYMMDD format
function getPatientAgeInDays($dobYMD, $nowYMD=null) {
    $age = -1;

    // strip any dashes from the DOB
    $dobYMD = preg_replace("/-/", "", $dobYMD);
    $dobDay = substr($dobYMD,6,2); $dobMonth = substr($dobYMD,4,2); $dobYear = substr($dobYMD,0,4);
    
    // set the 'now' date values
    if ($nowYMD == null) {
        $nowDay = date("d");
        $nowMonth = date("m");
        $nowYear = date("Y");
    }
    else {
        $nowDay = substr($nowYMD,6,2);
        $nowMonth = substr($nowYMD,4,2);
        $nowYear = substr($nowYMD,0,4);
    }

    // do the date math
    $dobtime = strtotime($dobYear."-".$dobMonth."-".$dobDay);
    $nowtime = strtotime($nowYear."-".$nowMonth."-".$nowDay);
    $timediff = $nowtime - $dobtime;
    $age = $timediff / 86400;

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

function DBToDate ($date)
{
    $date=substr ($date,5,2)."/".substr ($date,8,2)."/".substr($date, 0,4);
    return $date;
}

function get_patient_balance($pid) {
  if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
    $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
      "pid = ? AND activity = 1", array($pid) );
    $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
      "pid = ?", array($pid) );
    $drow = sqlQuery("SELECT SUM(pay_amount) AS payments, " .
      "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
      "pid = ?", array($pid) );
    return sprintf('%01.2f', $brow['amount'] + $srow['amount']
      - $drow['payments'] - $drow['adjustments']);
  }
  else if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
    // require_once($GLOBALS['fileroot'] . "/library/classes/WSWrapper.class.php");
    $conn = $GLOBALS['adodb']['db'];
    $customer_info['id'] = 0;
    $sql = "SELECT foreign_id FROM integration_mapping AS im " .
      "LEFT JOIN patient_data AS pd ON im.local_id = pd.id WHERE " .
      "pd.pid = '" . $pid . "' AND im.local_table = 'patient_data' AND " .
      "im.foreign_table = 'customer'";
    $result = $conn->Execute($sql);
    if($result && !$result->EOF) {
      $customer_info['id'] = $result->fields['foreign_id'];
    }
    $function['ezybiz.customer_balance'] = array(new xmlrpcval($customer_info,"struct"));
    $ws = new WSWrapper($function);
    if(is_numeric($ws->value)) {
      return sprintf('%01.2f', $ws->value);
    }
  }
  return '';
}

// Function to check if patient is deceased.
//  Param:
//    $pid  - patient id
//    $date - date checking if deceased (will default to current date if blank)
//  Return:
//    If deceased, then will return the number of
//      days that patient has been deceased.
//    If not deceased, then will return false.
function is_patient_deceased($pid,$date='') {

  // Set date to current if not set
  $date = (!empty($date)) ? $date : date('Y-m-d H:i:s');

  // Query for deceased status (gets days deceased if patient is deceased)
  $results = sqlQuery("SELECT DATEDIFF(?,`deceased_date`) as `days_deceased` " .
                      "FROM `patient_data` " .
                      "WHERE `pid` = ? AND `deceased_date` IS NOT NULL AND `deceased_date` != '0000-00-00 00:00:00' AND `deceased_date` <= ?", array($date,$pid,$date) );

  if (empty($results)) {
    // Patient is alive, so return false
    return false;
  }
  else {
    // Patient is dead, so return the number of days patient has been deceased.
    //  Don't let it be zero days or else will confuse calls to this function.
    if ($results['days_deceased'] === 0) {
      $results['days_deceased'] = 1;
    }
    return $results['days_deceased'];
  }
}

?>
