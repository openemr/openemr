<?php
/** 
 * DBC DUTCH SYSTEM
 * several functions used in DBC
 * 
 * the functions are used mainly with web sessions
 * for 'independent' functions check DBC_cfunctions
 *
 * used with library/dropdown.js by interface/main/add_edit_event.php
 * @author Cristian NAVALICI (lemonsoftware [..] gmail [.] com)
 * @version 1.0 24-08-2007
 */

if ( isset($_SESSION) ) {
    // trick to make a difference between CLI and webspace
    require_once(dirname(__FILE__) . '/../interface/globals.php');
}

if ( isset($_POST['code']) ) {
  request_for_records($_POST['code']);
}
  

if ( isset($_POST['vcode']) ) {
  verify_code($_POST['vcode']);
}

// if we edit the already entered activity in add_edit_event...
if ( isset($_POST['editactiv']) ) {
  $_SESSION['editactiv'] = TRUE;
}

$GLOBALS['full_name_activity'] = '';

//-----------------------------------------------------------------------------
/**
 * REQUEST FOR RECORDS
 * 
 * @param string $parent_code - code to look for
 * 
 */
function request_for_records($parent_code) {
  if ( !$parent_code ) return;
  $a = '';
  mysql_query("SET NAMES utf8");
  $q = sprintf("SELECT cl_activiteit_element AS elem, cl_activiteit_code AS code
   FROM cl_activiteit WHERE cl_activiteit_groepcode = '%s'  AND cl_activiteit_einddatum > '%s'
   AND cl_activiteit_begindatum < '%s'", $parent_code, $_SESSION['event_date'], $_SESSION['event_date']);

  $r = mysql_query($q) or die(mysql_error()); 
  if ( mysql_num_rows($r) ) {
    while ( $row = mysql_fetch_array($r) ) {
      $a .="<option value='{$row['code']}'>{$row['elem']}</option>";
    }
  } else {
    $a .= '<option value="0"></option>';
  }

  echo $a;
  //echo dirname(__FILE__).'/../interface/globals.php';
}


  
//-----------------------------------------------------------------------------
/**
 * VERIFY SELECTEERBAAR 
 * 
 * this field must be equal to 1 for saving into table
 * @param int $vcode - element code
 */
function verify_code($vcode, $where = 1) {
  if ( $where == 1) {
    $q = sprintf("SELECT cl_activiteit_selecteerbaar AS slbar, cl_activiteit_sysid AS sysid
     FROM cl_activiteit WHERE cl_activiteit_code = '%s'", $vcode);
  } elseif ( $where == 2) {
    $q = sprintf("SELECT cl_diagnose_selecteerbaar AS slbar, cl_diagnose_sysid AS sysid
     FROM cl_diagnose WHERE cl_diagnose_code = '%s'", $vcode);  
  }
  
  $r = mysql_query($q) or die(mysql_error());
  if ( mysql_num_rows($r) ) {  
    $row = mysql_fetch_array($r);
    return ( $row['slbar'] == 0 ) ? FALSE : TRUE;
  } else {
        return FALSE;
  }
}

//-----------------------------------------------------------------------------
/**
 * WHAT SYSID/NAME
 * 
 * find the sysid for a code in activiteit table
 * OR the name based on a known sysid
 * sysid must be unique in this table
 *  
 * @param string $code - activity's code
 * @param string $sysid - activity's sysid
 * @return int|string|bool(false)
 */
function what_sysid($code = 0, $sysid = 0) {
  if (!$code && !$sysid) return FALSE;

    if ( isset($_SESSION['event_date']) && $_SESSION['event_date']) {
        $today = $_SESSION['event_date'];
    } else {
        $today = date('Y-m-d');
    }

  if ( $code ) {
    $q = sprintf("SELECT cl_activiteit_sysid AS sysid
       FROM cl_activiteit WHERE cl_activiteit_code = '%s' AND
       cl_activiteit_einddatum > '%s' AND cl_activiteit_begindatum < '%s'", $code, $today, $today);
  } elseif ( $sysid ) {
    $q = sprintf("SELECT cl_activiteit_beschrijving AS besc
       FROM cl_activiteit WHERE cl_activiteit_sysid = %d AND
       cl_activiteit_einddatum > '%s' AND cl_activiteit_begindatum < '%s'", $sysid, $today, $today);
  }

  $r = mysql_query($q) or die(mysql_error());
  if ( mysql_num_rows($r) ) {  
    $row = mysql_fetch_array($r);
    if ( $code ) return $row['sysid'];
    else if ($sysid) return $row['besc'];
  } else {
    return false;    
  }

}


//-----------------------------------------------------------------------------
/**
 * FULL NAME FOR A SYSID
 *  
 * @param int $sysid (cl_activiteit_sysid)
 * @return 
 */
function what_full_sysid($sysid) {
    if ( !$sysid ) return FALSE;
    $full_str = '';

    $parent = what_groepcode_activiteit($sysid);

    if ( $parent['parent'] ) {
        $full_str .= what_sysid(0, $sysid) .' - ';      // first sysid (not included in reccursive) 
        rec_parent_activiteit($parent['parent']);       // produce the rest of the string
        $full_str .= $GLOBALS['full_name_activity'];    // concatenate the above 2 parts
    } else {
        // NO PARENT; THIS IS THE FIRST IN LINE
        $full_str = what_sysid(0, $parent['sysid']);
    }

    return $full_str;
}


//-----------------------------------------------------------------------------
/**
 * WHAT CODE FOR AN ACTIVITY
 * 
 * find the code for an activity
 *  
 * @param string $sysid - activity's code
 * @return string
 */
function what_code_activity($sysid = 0) {
    if ( !$sysid ) return FALSE;

    $q = sprintf("SELECT * FROM cl_activiteit WHERE cl_activiteit_sysid = %d", $sysid);
    $r = mysql_query($q) or die(mysql_error());

    if ( mysql_num_rows($r) ) {  
        $row = mysql_fetch_array($r);
        return $row['cl_activiteit_code'];
    } else {
        return '';
    }
}


//-----------------------------------------------------------------------------
/**
 * THE PARENT RECORD CODE FOR A SYSID
 *  
 * Look for the record with code / sysid
 * 
 * @param int $sysid (cl_activiteit_sysid)
 * @param string $code (cl_activiteit_code)
 * @return array - contains the parent code and its sysid
 */
function what_groepcode_activiteit($sysid = 0, $code = '') {
    if ( !$sysid && !$code ) return FALSE;
    $today = date('Y-m-d');
    $rez   = array('parent' => '', 'sysid' => ''); 

    if ( $sysid ) {
       $q = sprintf("SELECT cl_activiteit_groepcode AS clag 
            FROM cl_activiteit 
            WHERE cl_activiteit_sysid = %d AND cl_activiteit_begindatum < '%s' AND cl_activiteit_einddatum > '%s'", $sysid, $today, $today);
    } else if ( $code ) {
       $q = sprintf("SELECT cl_activiteit_groepcode AS clac, cl_activiteit_sysid AS clsy 
            FROM cl_activiteit 
            WHERE cl_activiteit_code = '%s' AND cl_activiteit_begindatum < '%s' AND cl_activiteit_einddatum > '%s' ", $code, $today, $today);
    }

    $r = mysql_query($q) or die(mysql_error());
    if ( mysql_num_rows($r) ) {  
        $row = mysql_fetch_array($r);
        if ( $sysid) {
            $rez['parent'] = trim($row['clag']); $rez['sysid'] = (int)$sysid; 
        } else { 
            // array who contains 1 - the parent code 2 - the record sysid (used to find its name)
            $rez['parent'] = trim($row['clac']); $rez['sysid'] = $row['clsy']; 
        }

        return $rez;
    } else {
        return false;    
    }

}


//-----------------------------------------------------------------------------
/**
 * RECURSIVE FUNCTION
 * 
 * @param string $parent - groepcode
 * @return void
 */
function rec_parent_activiteit($parent, $string = '') {
    $next = what_groepcode_activiteit(0, $parent); 
    $string .= what_sysid(0, $next['sysid']) .' - ';

    if ( $next['parent'] ) {
        rec_parent_activiteit($next['parent'], $string);
    } else {
        // write the result in a SESSION VARIABLE
        $GLOBALS['full_name_activity'] = $string; return 1;
    }
    
}

//-----------------------------------------------------------------------------
/**
 * RETURNS THE RECORDS FOR LEVEL 1 (main activities)
 * 
 * @param string $what ev - addeditevent, as - axes selection
 * @param integer $gaf - used only for AS5
 * @return array $result - contains arrays
 */
function records_level1 ($what = 'ev', $gaf = 0) {
  $result = array();
  $today = date('Y-m-d');
  mysql_query("SET NAMES utf8");


  switch ( $what ) {
    case 'ev' : // ADD EDIT EVENT
    $q = sprintf("SELECT cl_activiteit_beschrijving, cl_activiteit_sysid, cl_activiteit_code, cl_activiteit_element
     FROM cl_activiteit 
     WHERE cl_activiteit_hierarchieniveau = 1
     AND cl_activiteit_einddatum > '%s' AND cl_activiteit_begindatum < '%s'",
     $_SESSION['event_date'], $_SESSION['event_date']);
    break;

    case 'as1' : // AXES CASE
    $q = sprintf("SELECT cl_diagnose_beschrijving, cl_diagnose_sysid, cl_diagnose_code, cl_diagnose_element
     FROM cl_diagnose
     WHERE cl_diagnose_as = 1 AND cl_diagnose_hierarchieniveau = 2
     AND cl_diagnose_einddatum > '%s' AND cl_diagnose_begindatum < '%s'
     ORDER BY cl_diagnose_element",
     $today, $today);
    break;

    case 'as2' : // AXES CASE
    $q = sprintf("SELECT cl_diagnose_beschrijving, cl_diagnose_sysid, cl_diagnose_code, cl_diagnose_element
     FROM cl_diagnose
     WHERE cl_diagnose_as = 2 AND cl_diagnose_hierarchieniveau = 2
     AND cl_diagnose_einddatum > '%s' AND cl_diagnose_begindatum < '%s'
     ORDER BY cl_diagnose_element",
     $today, $today);
    break;

    case 'as3' : // AXES CASE
    $q = sprintf("SELECT cl_diagnose_beschrijving, cl_diagnose_sysid, cl_diagnose_code, cl_diagnose_element
     FROM cl_diagnose
     WHERE cl_diagnose_as = 3 
     AND cl_diagnose_einddatum > '%s' AND cl_diagnose_begindatum < '%s'",
     $today, $today); // cl_dia..._hierarchieniveau is 1 in this case, not 2
    break;

    case 'as4' : // AXES CASE
    $q = sprintf("SELECT cl_diagnose_beschrijving, cl_diagnose_sysid, cl_diagnose_code, cl_diagnose_element
     FROM cl_diagnose
     WHERE cl_diagnose_as = 4
     AND cl_diagnose_einddatum > '%s' AND cl_diagnose_begindatum < '%s'
     ORDER BY cl_diagnose_element",
     $today, $today); // cl_dia..._hierarchieniveau is 1 in this case, not 2
     break;
     
    case 'as5' : // AXES CASE
    $groepcode = 'as5_0' . (int)$gaf;
    $q = sprintf("SELECT cl_diagnose_beschrijving, cl_diagnose_sysid, cl_diagnose_code, cl_diagnose_element
     FROM cl_diagnose
     WHERE cl_diagnose_as = 5 AND cl_diagnose_groepcode = '%s'
     AND cl_diagnose_einddatum > '%s' AND cl_diagnose_begindatum < '%s'",
     $groepcode, $today, $today); // cl_dia..._hierarchieniveau is 1 in this case, not 2
     break;
  }

  $result[] = '';
  $r = mysql_query($q) or die( mysql_error() );
  while ( $row = mysql_fetch_array($r) ) {
    $result[] = $row;
  }

  //echo "<pre>" . print_r($result, true) . "</pre>"; // debug
  return $result;
}

//-----------------------------------------------------------------------------
/**
 * FIND SAVED ACTIVITY FOR AN EVENT
 * 
 * @param $eid - event id
 * @return string|bool(false) - sysid for that event|false
 */
function what_activity($eid) {
  $q = sprintf("SELECT activity_sysid FROM cl_event_activiteit WHERE event_id= %d ", $eid);
  $r = mysql_query($q) or die(mysql_error());
  if ( $r ) {
    $row = mysql_fetch_array($r);
    return $row['activity_sysid'];
  } else {
    return FALSE;
  }
}

//-----------------------------------------------------------------------------
/**
 * RETURN THE NAME OF A DIAGNOSE
 * 
 * @param string $ascode
 * @return string
 */
function what_as($ascode) {
  mysql_query("SET NAMES utf8");
  $q = sprintf("SELECT cl_diagnose_beschrijving FROM cl_diagnose WHERE cl_diagnose_code = '%s'", $ascode);
  $r = mysql_query($q) or die( mysql_error() );
  $row = mysql_fetch_array($r);
  return $row['cl_diagnose_beschrijving'];
}


//-----------------------------------------------------------------------------
/**
 * GENERATE AN UNIQUE PATIENT NUMBER
 * 
 * number user in DBC is generated at a new client
 * 
 * @param int $pid - patient id
 * @return none
 */
function generate_id1250($pid) {
if ($pid) {
  mysql_query("START TRANSACTION");
  $date = date('Ymd');
  // retrieve last allocated number for this day
  // let's check if for today there is record
  $qd = sprintf("SELECT aux_varc FROM cl_aux WHERE aux_id='dn_id1250'");
  $rd = mysql_query($qd) or die(mysql_error());
  $rez = mysql_fetch_array($rd);

  // if strings are NOT equal, then we must update the date field with today value
  // and reset the counter (aux_varn) to 0
  if ( $date !== trim($rez['aux_varc']) ) {
    $qc = sprintf("UPDATE cl_aux SET aux_varc = '%s', aux_varn = 0 WHERE aux_id='dn_id1250'", $date);  
    mysql_query($qc) or die(mysql_error());
  }

  $nq = mysql_query("SELECT aux_varn FROM cl_aux WHERE aux_id='dn_id1250'") or die(mysql_error());
  $nrow = mysql_fetch_array($nq);
  $current_number = $nrow['aux_varn'] + 1;
  $current_number = strval($current_number);

  // prepare new ID1250 number (date + 6 digits)
  $np = str_pad($current_number, 6, '0', STR_PAD_LEFT);
  $ns = $date . $np;

  // as an extra caution measure, we check for openemrid and newly generated number
  // in table; these must be unique
  $check = sprintf("SELECT * FROM cl_patient_number WHERE pn_oemrid = %d OR pn_id1250 = '%s'",
  $pid, $ns);
  $rezcheck = mysql_query($check) or die(mysql_error());
  if ( !mysql_num_rows($rezcheck) ) {
    // insert into db
    $q = sprintf("INSERT INTO cl_patient_number VALUES ('%d', '%s')", $pid, $ns);
    mysql_query($q) or die(mysql_error());

    // update auxiliary table
    $qu = sprintf("UPDATE cl_aux SET aux_varn = aux_varn + 1 WHERE aux_id='dn_id1250'");
    mysql_query($qu) or die(mysql_error());
  } else {
    echo '<script>alert("The generated number or patient id already exists!")</script>';
  }
  mysql_query("COMMIT");
}//if pid
  
}

//-----------------------------------------------------------------------------
/**
 * GET AN ID FOR A PATIENT
 * 
 * @param string $whatid
 * @param int $pid
 */
function get_id($whatid, $pid) {
  $q = FALSE;
  switch ($whatid) {
    case 'id1250': 
      $q = sprintf("SELECT pn_id1250 FROM cl_patient_number WHERE pn_oemrid = %d", $pid);
    break;
  }
  
  if ( $q ) {
    $res = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_row($res); 
    return $row[0];
  } else {
    return FALSE;  
  }
}

//-----------------------------------------------------------------------------
/**
 * GENERATE A CARE ROUTE NUMBER - ID 1007
 * (ZORGTRAJECTNUMMER)
 * 
 * this function actually checks for an opened CRN (ZTN - zorgtrajectnummer),
 * and returns it; only if it doesn't exist generate a new one
 * 
 * @param int $pid
 * @param string $eventdate
 * @return string - CRN(ZTN) if exists; new CRN(ZTN)
 */
function generate_id1007($pid, $eventdate) {
if ($pid) {
    mysql_query("START TRANSACTION");
    // check for previous opened CRN
    $qc = sprintf("SELECT * FROM cl_careroute_numbers WHERE cn_pid = %d AND cn_open = '1'", $pid);  
    $rez = mysql_query($qc) or die(mysql_error());
    if ( mysql_num_rows($rez) ) {
        $row = mysql_fetch_array($rez);
        return $row['cn_ztn'];
    } else {
        // generate a new one
        // get last value from auxiliary table
        $raux = mysql_query("SELECT aux_varn FROM cl_aux WHERE aux_id='dn_id1007'") or die(mysql_error());    
        $rez = mysql_fetch_array($raux);
        $current_val = strval($rez['aux_varn'] + 1);
        $ztn = str_pad($current_val, 7, '0', STR_PAD_LEFT);

        // insert new values into cl_careroute_numbers table and set it to opened
        $qi = sprintf("INSERT INTO cl_careroute_numbers(cn_ztn, cn_pid, cn_dopen, cn_open)
        VALUES('%s', %d, '%s', %d)", $ztn, $pid, $eventdate, 1);
        mysql_query($qi);
        $newid = mysql_insert_id();

        // update value from aux table
        $qu = sprintf("UPDATE cl_aux SET aux_varn = aux_varn + 1 WHERE aux_id='dn_id1007'");
        mysql_query($qu) or die(mysql_error());

        return $newid;
    }
    mysql_query("COMMIT");
} // if pid
}

//-----------------------------------------------------------------------------
/**
 * SAVE DBC RECORDS
 * 
 * @param none
 * @return void
 */
function save_dbc() {
if ( !vl_validate_diagnoses() ) {
    echo '<script>alert("One or more diagnoses are NOT valid or the diagnoses are NOT UNIQUE!")</script>';
    return FALSE;
}

if ( $_SESSION['save'] ) {

    mysql_query("START TRANSACTION");

    // check for an opened ztn
    $opened_ztn = verify_ztn();
 
    if ( !$opened_ztn ) $opened_ztn = generate_id1007($_SESSION['pid'], date('Y-m-d')); // if it's close, create a new one

    // insert a new DBC Route
    $today = date('Y-m-d');
    if ( $opened_ztn ) {
        // as a caution we check for posas. must be NOT null
      if ( !$_SESSION['posas'] ) $_SESSION['posas'] = 1; // default value
    
      // at as1 and as2 we must add radio postion for Main Diagnose
      $as1arr['content'] = $_SESSION['as1'];
      $as1arr['mainpos'] = (int)$_SESSION['posas'];
      $as2arr['content'] = $_SESSION['as2'];
      $as2arr['mainpos'] = (int)$_SESSION['posas'];
      $circode           = (int)$_SESSION['circuitcode'];

      if ( $_SESSION['show_axid'] ) {
        // ----------------------
        // edit case
        $q = sprintf("UPDATE cl_axes 
        SET ax_as1 = '%s', ax_as2 = '%s', ax_as3 = '%s', ax_as4 = '%s', ax_as5 = '%s' WHERE ax_id = %d", 
        serialize($as1arr), serialize($as2arr), serialize($_SESSION['as3']), serialize($_SESSION['as4']), 
        serialize($_SESSION['as5']), $_SESSION['show_axid'] );
        mysql_query($q) or die(mysql_error());

        // edit circuit-dbc connection
        $icircode = ( vl_validate_circuit($circode) ) ? $circode : '1' ; // validate the circuit
        $qcir = sprintf("INSERT INTO cl_circuit_dbc(ccd_dbcid, ccd_circuitcode) VALUES(%d, %d) ON DUPLICATE KEY
        UPDATE ccd_circuitcode = %d", $_SESSION['show_axid'], $icircode, $icircode);
        mysql_query($qcir) or die(mysql_error());

      } else {
        // ----------------------
        // new case

        // if it's the first DBC, open date is given by the event with @ flag (pc_apptstatus) after ZTN was opened)
        if ( first_dbc($_SESSION['show_axid']) ) {
            // open date for ztn
            $qztn   = sprintf("SELECT cn_dopen FROM cl_careroute_numbers WHERE cn_ztn = '%s' ", $opened_ztn);
            $rztn    = mysql_query($qztn) or die (mysql_error());
            $ztarr  = mysql_fetch_array($rztn); $ztnodate = $ztarr['cn_dopen'];

            // find first event who is visit (with @ flag after $ztnodate)
            $qv     = sprintf("SELECT MIN(pc_eventDate) as mindate FROM openemr_postcalendar_events WHERE pc_eventDate >= '%s' AND pc_apptstatus = '@' AND pc_pid = %d ", $ztnodate, $_SESSION['pid']);
            $rv     = mysql_query($qv) or die (mysql_error());

            $earr   = mysql_fetch_array($rv); 
            $opendate = $earr['mindate'];

            // no encounter found so we cannot insert a DBC
            if ( !$opendate ) {
                echo "<script>alert('No Encounter found! Please generate first at least one encounter.')</script>";
                exit();
            }
        
        } else {
            $opendate = $today;
        }

        $q = sprintf("INSERT INTO cl_axes (ax_ztn, ax_open, ax_as1, ax_as2, ax_as3, ax_as4, ax_as5, ax_odate)
        VALUES('%s', %d, '%s', '%s', '%s', '%s', '%s', '%s')", 
        $opened_ztn, 1, serialize($as1arr), serialize ($as2arr), serialize ($_SESSION['as3']), serialize ($_SESSION['as4']), serialize ($_SESSION['as5']), $opendate);
        mysql_query($q) or die(mysql_error()); // leave it here that next line to work

        // new circuit-dbc connection
        $rlast = mysql_insert_id();
        $icircode = ( vl_validate_circuit($circode) ) ? $circode : '1' ; // validate the circuit
        $qcir = sprintf("INSERT INTO cl_circuit_dbc(ccd_circuitcode, ccd_dbcid) VALUES(%d, %d)", $icircode, $rlast);
        mysql_query($qcir) or die(mysql_error());

      } // new | edit case

    } else {
      echo '<script>alert("No ZTN selected!")</script>'; return FALSE;
    }          
    
    // reset implied session variables
    $_SESSION['save'] = TRUE;
    for ($i = 1; $i <= 5; $i++) {
       $_SESSION["as$i"] = ''; 
    }

    mysql_query("COMMIT");

    return TRUE;

} //if
}

//-----------------------------------------------------------------------------
/**
 * VERIFY ZTN
 * 
 * verify for a patient if a ZTN is already opened
 * return the number of ztn if true, false otherwise
 * 
 * @param $pid - optionally; if provided, we'll ignore the session var
 * @return string $opened_ztn | bool false
 */
function verify_ztn($pid = 0){

    // patient id could be taken from a param or from a session var
    $cn_pid = ( $pid ) ? $pid : $_SESSION['pid'];

    // search for an opened ZTN (id1007)
    $qc = sprintf("SELECT cn_ztn FROM cl_careroute_numbers WHERE cn_pid = %d AND cn_open = '1'", $cn_pid);
    // echo $qc; // debug
    $rez = mysql_query($qc) or die(mysql_error()); $opened_ztn = '';
    if ( mysql_num_rows($rez) ) {
      $row = mysql_fetch_array($rez);
      $opened_ztn = $row['cn_ztn'];
      return $opened_ztn;
    } else {
      return false;   
    }

}

//-----------------------------------------------------------------------------
/**
 * LIST ZTN's
 * 
 * lists ZTN's from a patient
 * 
 * @param int $mode 0-all 1-only opened 2-only closed
 */
function lists_ztn($mode = 0){
  switch ($mode) {
    case 0: $qc = sprintf("SELECT * FROM cl_careroute_numbers WHERE cn_pid = %d ORDER BY cn_dopen", $_SESSION['pid']); break;
    case 1: $qc = sprintf("SELECT * FROM cl_careroute_numbers WHERE cn_pid = %d AND cn_open = '1' ORDER BY cn_dopen", $_SESSION['pid']); break;
    case 2: $qc = sprintf("SELECT * FROM cl_careroute_numbers WHERE cn_pid = %d AND cn_open = '0' ORDER BY cn_dopen", $_SESSION['pid']); break;
    default: $qc = sprintf("SELECT * FROM cl_careroute_numbers WHERE cn_pid = %d ORDER BY cn_dopen", $_SESSION['pid']); break;
  }

    $artn = array();
    $rez = mysql_query($qc) or die(mysql_error());
    if ( mysql_num_rows($rez) ) {
      while ( $row =  mysql_fetch_array($rez)) {
        $artn[] = $row; 
      }
      return $artn;
    } else {
      return false;   
    }
}

//-----------------------------------------------------------------------------
/**
 * LIST DIAGNOSES
 * 
 * lists diagnoses for a ZTN
 * if called for last, this means the open dbc
 * 
 * @param string ax_odate DESC default, 'last' for the last one (ordered by date) 
 * @return array | bool(false)
 */
function lists_diagnoses($order = 'ax_odate DESC'){

  // obtain opened ztn
  $ztn = verify_ztn();
  if ( $order == 'last') {
    $qc = sprintf("SELECT * FROM cl_axes WHERE ax_ztn = '%s' AND ax_open = 1 ORDER BY ax_odate DESC ", $ztn);
  } else {
    $qc = sprintf("SELECT * FROM cl_axes WHERE ax_ztn = '%s' ORDER BY %s ", $ztn, $order);
  }
  
  $artn = array();
  $rez = mysql_query($qc) or die(mysql_error());

  if ( mysql_num_rows($rez) ) {
     while ( $row =  mysql_fetch_array($rez)) {
       $artn[] = $row; 
     } 
    return $artn;
  } else {
    return false;   
  }
}

//-----------------------------------------------------------------------------
/**
 * LAST DIAGNOSE
 * 
 * specific function to return the last entered diagnose
 * a.k.a. the open DBC
 */
function last_diagnose() {
  $arr = lists_diagnoses('last');
  return $arr[0];
}

//-----------------------------------------------------------------------------
/**
 * ZORGTYPE CODES
 * 
 * @param none
 * @return array
*/
function zorgtype_codes() {
  mysql_query("SET NAMES utf8");
  $check = first_dbc($_SESSION['show_axid']);
  if ( $check ) { //first dbc
    $qc = sprintf("SELECT * FROM cl_zorg WHERE cl_zorgtype_groepcode = 100 AND cl_zorgtype_selecteerbaar = 1");
  } else {
    $qc = sprintf("SELECT * FROM cl_zorg WHERE cl_zorgtype_groepcode = 200 AND cl_zorgtype_selecteerbaar = 1");    
  }
  $rez = mysql_query($qc) or die(mysql_error());  

  $ra = array();
  while ($r = mysql_fetch_assoc($rez)) {
    $ra[] = $r;  
  }
  
  return $ra;
}

//-----------------------------------------------------------------------------
/**
 * FIRST DBC?
 * 
 * find if a dbc is the first , a 'follow-up' or there is no DBC yet.
 * (the same function is in DBC_files)
 * 
 * @param int $ax_id - dbc's id
 * @return bool - true if it's the first, 0 - otherwise
 */
function first_dbc($ax_id) {
    // to be the first means there is only one DBC per open ZTN  
    $openztn = verify_ztn();

    // look for all dbcs in a careroute
    $qz = sprintf("SELECT * FROM cl_axes WHERE ax_ztn='%s' ORDER BY ax_id", $openztn);
    $rez = mysql_query($qz) or die(mysql_error());

    while ( $row = mysql_fetch_array($rez) ) {
        $arrdbc[] = $row['ax_id'];
    }

    // and now, the analysis:
    // - first means the ax_id is the first in array
    // - followup means the ax_id is NOT the first in array (because after the first, all dbcs
    // are followups)
    return ( $arrdbc[0] == $ax_id );
}


//-----------------------------------------------------------------------------
/**
 * STATUS FOR A ZTN
 * 
 * find if a dbc is the first , a 'follow-up' or there is no DBC yet.
 * retcode 0 - no ZTN open
 *         1 - ZTN open, no DBC
 *         2 - ZTN open, (just) one opened DBC
 *         3 - ZTN open, (just) one closed DBC
 *         4 - ZTN open, more DBC's
 * 
 * @param none
 * @return int - for $retcode see above
 */
function ztn_status() {

    // return the open ztn if any
    $openztn = verify_ztn();

    if ( $openztn ) {
        $qz     = sprintf("SELECT * FROM cl_axes WHERE ax_ztn='%s'", $openztn);
        $rez    = mysql_query($qz) or die(mysql_error());
        $rows   = mysql_num_rows($rez);

        switch ($rows) {
            case 0: $retcode = 1; break; // no rows, means no DBC in ZTN
            case 1: 
                $ro = mysql_fetch_array($rez);
                $retcode = ( $ro['ax_open'] ) ? 2 : 3 ;
            break; // just one row, means one DBC (initial) in ZTN
            default: $retcode = 4;       // any other numbers means more DBC's, so we have initial + follow's up
        }
    } else {
        $retcode = 0;
    }

    return $retcode;
}


//-----------------------------------------------------------------------------
/**
 * REDENSLUITEN CODES
 * 
 * @param none
 * @return array - an array with all redensluiten codes
 */
function reden_codes() {
if ( $_SESSION['eind'] ) {
  mysql_query("SET NAMES utf8");
  $eind = mysql_real_escape_string($_SESSION['eind']);
  $qc = sprintf("SELECT * FROM cl_redensluiten clr WHERE clr.cl_redensluiten_begindatum < '%s' AND clr.cl_redensluiten_einddatum >'%s' ", $eind, $eind);
  $rez = mysql_query($qc) or die(mysql_error());  

  $ra = array();
  while ($r = mysql_fetch_assoc($rez)) {
    $ra[] = $r;
  }
  
  return $ra;
}//if
}

//-----------------------------------------------------------------------------
/**
 * CHECK FOR 'SENT TO INSURER' CASE
 * 
 * check if the open DBC was already sent to insurer or not
 * 
 * @param none
 * @return bool
 */
function sent_to_insurer() {
  $ldbc = last_diagnose(1); // return open DBC
  return ( $ldbc['ax_sti'] ) ? TRUE : FALSE;
}


//-----------------------------------------------------------------------------
/**
 * LOAD OPEN DBC 
 * 
 * load the values from the open dbc into session variables
 * to edit form
 */
function load_dbc() {
  $ldbc = last_diagnose(1);
  $as1 = unserialize($ldbc['ax_as1']);
  $_SESSION['as1'] = $as1['content']; $_SESSION['posas'] = (int)$as1['mainpos'];

  $as2 = unserialize($ldbc['ax_as2']); 
  $_SESSION['as2'] = $as2['content']; 

  $_SESSION['as3'] = unserialize($ldbc['ax_as3']);
  $_SESSION['as4'] = unserialize($ldbc['ax_as4']);
  $_SESSION['as5'] = unserialize($ldbc['ax_as5']);
}

//-----------------------------------------------------------------------------
/**
 * CLOSE AN OPEN DBC 
 * 
 * @param int $follow - specify if a DBC is followed by another one
 * created with the same content, or close + ZTN close
 * @param int $stoornis -  $_POST['stoornis']
 * @param int $ztc - $_POST['ztc']
 * @param int $rtc - $_POST['rtc']
 * @param array $gaf - array with 2 elements (hoogste/eind)
 * @param int $dbcid - dbcid if called from a script
 * @param string $eind - closing date if called from a script
 * @param int $patid - patient id if called from a script
 * @return void
*/
function close_dbc($follow = 0, $stoornis = 0, $ztc, $rtc, $gaf, $dbcid = 0, $eind = '', $patid = 0) {
    mysql_query('START TRANSACTION');

    $einddate = ( $dbcid ) ? $eind : $_SESSION['eind'];
    $dbc_id   = ( $dbcid ) ? $dbcid : $_SESSION['show_axid'];
    $pid      = ( $dbcid ) ? $patid : $_SESSION['pid'];
//echo "$dbcid / $stoornis / $ztc / $rtc / $gaf / $eind <br>"; // debug
    // close the open dbc; also mark for vektis
    //$q = sprintf("UPDATE cl_axes SET ax_open = 99, ax_cdate = '%s', ax_vkstatus = 1
    $q = sprintf("UPDATE cl_axes SET ax_open = 0, ax_cdate = '%s', ax_vkstatus = 1
                 WHERE ax_id = %d", $einddate, $dbc_id);
    mysql_query($q) or die(mysql_error());

    // update for the current
    // NOTE: we must run this here because the following statement
    // retrieves the content for DBC; otherwise the duplicated dbc will not contain
    // the new $gaf array

    update_gaf($dbc_id, $gaf);

    // this MUST be run after update_gaf
    $ldbc = content_diagnose($dbc_id);

    // write the closing parameters from dbc_sluiten form
    write_stoornis($stoornis, $dbc_id);
    write_zorg($ztc, $dbc_id);
    write_reden($rtc, $dbc_id);

    // generate prestatie code
    dt_prestatiecode($ztc, $stoornis, $dbc_id);

    // open a new one if it is the case
    if ( $follow ) {
        $newid = duplicate_dbc($ldbc);
        if ( !$dbcid ) $_SESSION['show_axid'] = $newid;

        return $newid;
    } else {
        $qztn = sprintf("UPDATE cl_careroute_numbers SET cn_open = 0, cn_dclosed = '%s' 
        WHERE cn_pid = %d", $einddate, $pid);  
        mysql_query($qztn) or die(mysql_error());
        
        if ( !$dbcid ) $_SESSION['show_axid'] = FALSE;
    }
    
    mysql_query('COMMIT');
}


//-----------------------------------------------------------------------------
/**
 * TOTAL TIME SPENT
 * 
 * adds all direct, indirect + travel time from the first day of this DBC to the last day
 * only for encounters (events with @ pc_apptstatus)
 * 
 * @param int $dbc - if used instead of $_SESSION
 * @return array - contains the three times (indirect, travel, total)
 */
function total_time_spent($dbc = 0, $btime = '', $etime = '') {
    // our big results
    $total_time = 0 ; $indirect_time = 0 ; $travel_time = 0;
    
    // DBC ID
    // we have a session var or a given value?
    $dbcid  = ( $dbc ) ? $dbc : $_SESSION['show_axid'];

    // begin date for DBC
    if ( $btime ) {
        $bd_dbc = $btime;
    } else {
        $cd   = content_diagnose($dbcid); 
        $bd_dbc = $cd['ax_odate'];
    }

    // end date of DBC
    if ( $etime ) {
        $ed_dbc = $etime;
    } else {
        $ed_dbc = ( $_SESSION['eind'] ) ? $_SESSION['eind'] : date('Y-m-d');
    }

    // if we have a $dbc (given as arg) we must find a $pid
    if ( $dbc ) {
        $pid = what_patient($dbcid);
    } else {
        $pid = $_SESSION['pid'];
    }

    // also, we don't check for first_dbc if we have $btime
    if ( $btime == '2008-01-01' || $etime == '2007-12-31' ) {
        $check_first = FALSE;
    } else {
        $check_first = TRUE;
    }

    // find all events between DBC's dates and sum up total times
    $q = sprintf("SELECT pc_eid, pc_duration FROM openemr_postcalendar_events 
    WHERE pc_pid = '%s' AND pc_eventDate >= '%s' AND pc_eventDate <= '%s' AND pc_apptstatus = '@' ",
    $pid, $bd_dbc, $ed_dbc);

    $r = mysql_query($q) or die(mysql_error());

    while ($row = mysql_fetch_array($r)) {
        $total_time += $row['pc_duration'];

        // get indirect+travel time
        $q1 = sprintf("SELECT * FROM cl_time_activiteit WHERE event_id = %d", $row['pc_eid']);
        $r1 = mysql_query($q1) or die(mysql_error());
        if ( mysql_num_rows($r1) ) {
            $row1 = mysql_fetch_array($r1);
            $indirect_time += $row1['indirect_time'];
            $travel_time += $row1['travel_time'];
        } 
    } // while

    // if it is the first DBC we look for previous events
    // which weren't included in DBC and add timing too
    if ( $check_first && first_dbc($dbc) ) {
        // begin date of ZTN
        $r = lists_ztn(1);
        $bdztn = $r[0]['cn_dopen'];

        // find all events between DBC's dates and sum up total times
        $q = sprintf("SELECT pc_eid, pc_duration FROM openemr_postcalendar_events
                    WHERE pc_pid = '%s' AND pc_eventDate > '%s' AND pc_eventDate < '%s'",
                    $pid, $bdztn, $bd_dbc);
        $r = mysql_query($q) or die(mysql_error()); 
        while ($row = mysql_fetch_array($r)) {
            $total_time += $row['pc_duration']; 
            // get indirect+travel time
            $q2 = sprintf("SELECT * FROM cl_time_activiteit WHERE event_id = %d", $row['pc_eid']);
            $r2 = mysql_query($q2) or die(mysql_error());
            if ( mysql_num_rows($r2) ) {
                $row2 = mysql_fetch_array($r2);
                $indirect_time += $row2['indirect_time'];
                $travel_time += $row2['travel_time'];
            } 
        } // while 
    } // if

    $total_time /= 60 ; //transform it to minutes from seconds
    $total_time += $indirect_time + $travel_time; 

    $time = array ('total_time' => $total_time, 'indirect_time' => $indirect_time, 'travel_time' => $travel_time); 
    return $time;
}

//-----------------------------------------------------------------------------
/**
 * GENERATE STOORNIS(DISEASE) DROPDOWN
 * 
 * NOTE maybe not used anymore; there is an automatic procedure (DBC_decisiontree)
 * 
 * generates a dropdown for stoornis section in
 * close dbc module
 * 
 * @param string $odate - opening date for DBC (which is gonna be closed)
 * @return string
 */
function stoornis_dropdown($odate) {
  $s = '<select name="stoornis" id="stoornis">';
  
  mysql_query("SET NAMES utf8");
  $qc = sprintf("SELECT * FROM cl_productgroep clp  
                WHERE clp.cl_productgroep_code_verblijf = 0
                AND clp.cl_productgroep_selecteerbaar = 1 
                AND clp.cl_productgroep_begindatum < '%s' AND clp.cl_productgroep_einddatum > '%s' ", $odate, $odate);
  $rez = mysql_query($qc) or die(mysql_error());

  while ( $row = mysql_fetch_array($rez) ) {
    $s .= '<option value="' .$row['cl_productgroep_sysid']. '">' .$row['cl_productgroep_beschrijving']. '</option>';
  }
  
  $s .= '</select>';
  return $s;
}

//-----------------------------------------------------------------------------
/**
 * GENERATE VERBLIJF(HOSPITAL STAY) DROPDOWN
 * 
 * generates a dropdown for verblijf section in close dbc module
 * 
 * @param return
 */
function verblijf_dropdown() { 
  $s = '<select name="verblijf" id="verblijf">';
  $s .= '<option value="0">Geen Verblijf</option>';
  $qc = sprintf("SELECT * FROM cl_productgroep clp WHERE clp.cl_productgroep_code < 999
  AND clp.cl_productgroep_selecteerbaar = 1"); 
  $rez = mysql_query($qc) or die(mysql_error());

  while ( $row = mysql_fetch_array($rez) ) { 
    $s .= '<option value="' .$row['cl_productgroep_sysid']. '">'.$row['cl_productgroep_beschrijving']. '</option>';
  }
  
  $s .= '</select>'; 
  return $s;
}

//-----------------------------------------------------------------------------
/**
 * GENERATE CIRCUITCODE DROPDOWN
 * 
 * @param int - $sel  the selected item
 * @return string
 */
function circuit_dropdown($sel = 0) {
    $today = date('Y-m-d');
    $s = '<select id="circuit" name="circuit">';
  
    mysql_query("SET NAMES utf8");
    $query = sprintf ("SELECT * FROM cl_circuit WHERE cl_circuit_begindatum < '%s' AND cl_circuit_einddatum > '%s'", $today, $today);
    $qr = mysql_query($query) or die (mysql_error());  
    while ( $row = mysql_fetch_array($qr) ) {
        $selected = ( $row['cl_circuit_code'] == $sel ) ? 'selected="selected"' : '';
        $s .= '<option value="' .$row['cl_circuit_code']. '" ' .$selected. '>' .$row['cl_circuit_beschrijving']. '</option>';
    }

    $s .= '</select>';
    return $s;
}

//-----------------------------------------------------------------------------
/**
 * HAS CIRCUIT
 * 
 * check if a dbc has a link with a circuit code in cl_circuit_dbc table
 * 
 * @param int $axid
 * @return int|bool - the circuit code or false
 */
function has_circuit($axid = 0) {
  if ( !$axid ) return 0;
  
 $qh = sprintf("SELECT * FROM cl_circuit_dbc WHERE ccd_dbcid = %d", $axid);
 $rh = mysql_query($qh) or die(mysql_error());

 $result = '';
 if ( mysql_num_rows($rh) ) {
   $row = mysql_fetch_array($rh);
   $result = $row['ccd_circuitcode'];
 } else {
    $result = 0;
 }

 return $result;
}

//-----------------------------------------------------------------------------
/**
 * GET CIRCUIT CODE
 * 
 * return circuit code for a dbc
 * 
 * @param int dbc id
 * @param bool|int
 */
function get_circuitcode($dbcid) {
  if ( !$dbcid) return FALSE;

  mysql_query("SET NAMES utf8");
  $qc = sprintf("SELECT cl_circuit_beschrijving FROM cl_circuit clc
  JOIN cl_circuit_dbc clcd ON clcd.ccd_circuitcode = clc.cl_circuit_code
  WHERE clcd.ccd_dbcid = %d", $dbcid);
  $r = mysql_query($qc) or die(mysql_error());

  if ( mysql_num_rows($r) ) {
    $row = mysql_fetch_array($r); 
    return $row['cl_circuit_beschrijving'];
  } else return FALSE;
  
}


//-----------------------------------------------------------------------------
/**
 * WRITE ZORG
 * 
 * write zorg sysid for a closing dbc
 * 
 * @param int - zorg sysid
 * @param int - $dbcid - dbc id
 * @return none
 */
function write_zorg($zsysid, $dbcid) {
    // validate the code
    $izsysid = ( vl_validate_zorg($zsysid) ) ? $zsysid : 1 ;

    $qz = sprintf("INSERT INTO cl_zorg_dbc VALUES(%d, %d)", $dbcid, $izsysid);
    $r = mysql_query($qz) or die(mysql_error());
}

//-----------------------------------------------------------------------------
/**
 * WRITE STOORNIS
 * 
 * save the value from stoornis dropdown
 * 
 * @param int - cl_productgroep_sysid
 * @param int - $dbcid - dbc id
 * @return none
 */
function write_stoornis($zsysid, $dbcid) {
    $qz = sprintf("INSERT INTO cl_productgroep_dbc VALUES(%d, %d)", $zsysid, $dbcid);
    $r = mysql_query($qz) or die(mysql_error());
}

//-----------------------------------------------------------------------------
/**
 * WRITE REDEN
 * 
 * @param int cl_redensluiten_code
 * @param int - $dbcid - dbc id
 * @return void
 */
function write_reden($rcode, $dbcid) {
    // validate the code
    $ircode = ( vl_validate_redencode($rcode) ) ? $rcode : 1 ;

    $qz = sprintf("INSERT INTO cl_redensluiten_dbc VALUES(%d, %d)", $ircode, $dbcid);
    $r = mysql_query($qz) or die(mysql_error());
}


//-----------------------------------------------------------------------------
/**
 * UPDATE GAF
 * 
 * When closing a DBC we still need two values from GAF dropdowns (middle and end).
 * The first one was on DBC opening.
 * 
 * @param int $dbcid
 * @param array $gaf
 * @return bool
 */
function update_gaf($dbcid, $gaf) {
    if ( !$dbcid ) return FALSE;

    $qz = sprintf("SELECT ax_as5 FROM cl_axes WHERE ax_id = %d", $dbcid);
    $rz = mysql_query($qz) or die(mysql_error());
    $rowrz = mysql_fetch_array($rz);

    // we update the missing values
    $unser = unserialize($rowrz['ax_as5']);
    $un['gaf1'] = $unser['gaf1'];
    $un['gaf2'] = mysql_real_escape_string($gaf['gaf2']);
    $un['gaf3'] = mysql_real_escape_string($gaf['gaf3']);
    $ser = serialize($un);
    // update the new values
    $qu = sprintf("UPDATE cl_axes SET ax_as5 = '%s' WHERE ax_id = %d", $ser, $dbcid);
    $ru = mysql_query($qu) or die(mysql_error());

    return TRUE;
}

//-----------------------------------------------------------------------------
/**
 * GET PRIMARY CARE PROVIDER INFOS
 * 
 * used at demographics_full
 * @param int patient_id
 * @return array
 */
function get_provider_DBC($pid = 0) {
  if ( !$pid ) return FALSE;
  $qz = sprintf("SELECT * FROM cl_providers WHERE pro_pid = %d", $pid);
  $r = mysql_query($qz) or die(mysql_error());
  return mysql_fetch_assoc($r);
}

//-----------------------------------------------------------------------------
/**
 * GET REFERER INFOS
 * 
 * used at demographics_full
 * @param int patient_id
 * @return array
 */
function get_referer_DBC($pid = 0) {
  if ( !$pid ) return FALSE;
  $qz = sprintf("SELECT * FROM cl_referers WHERE ref_pid = %d", $pid);
  $r = mysql_query($qz) or die(mysql_error());
  return mysql_fetch_assoc($r);
}


//-----------------------------------------------------------------------------
/**
 * RETURN JOB DESCRIPTION (BEROEP)
 * 
 * for a specified user
 * 
 * @param int user id
 * @param int $code - look for code or name field
 * @return string
 */
function what_beroep($pid = 0, $code = 0) {
  if ( !$pid ) return FALSE;
  
  $qz = sprintf("SELECT cl_beroep_element as cbe, cl_beroep_code as cbc
                FROM cl_beroep JOIN cl_user_beroep ON cl_beroep.cl_beroep_sysid = cl_user_beroep.cl_beroep_sysid 
                WHERE cl_user_beroep.cl_beroep_userid = %d", $pid);
  $rz = mysql_query($qz) or die(mysql_error());
  $rez = mysql_fetch_assoc($rz);

  return ( $code ) ? $rez['cbc'] : $rez['cbe'];
}

//-----------------------------------------------------------------------------
/**
 * RETURN FULL DUTCH NAME
 * 
 * for a specified patient
 * 
 * @param int $pid - patient id
 * @return string $full_name
 */
function dutch_name($pid = 0) {
  if ( !$pid ) return FALSE;

  mysql_query("SET NAMES utf8");

  $qn = sprintf("SELECT fname, lname FROM patient_data WHERE id = %d", $pid);
  $rn = mysql_query($qn) or die(mysql_error());
  $rez = mysql_fetch_assoc($rn);
  
  $full_name    = '';
  $first_name   = $rez['fname'];
  $last_name    = $rez['lname'];
    
  // then we look into patient_data_NL because it's not mandatory that there would be a record for this $pid
  // (so, don't use JOIN between these tables)
  $qn2 = sprintf("SELECT pdn_pxlast, pdn_pxlastpar, pdn_lastpar FROM patient_data_NL WHERE pdn_id = %d", $pid);
  $rn2 = mysql_query($qn2) or die(mysql_error());

  if ( mysql_num_rows($rn2) ) {
    $reznl      = mysql_fetch_assoc($rn2);
    // partner is prefix + last name
    $partner    = ( $reznl['pdn_lastpar'] ) ? $reznl['pdn_pxlastpar'] .' '. $reznl['pdn_lastpar'] .' - ' : '' ;
    $prefix     = $reznl['pdn_pxlast'];
    // we make the full name
    $full_name = $first_name .' '. $partner . $prefix .' '. $last_name;
  } else {
    $full_name = $first_name .' '. $last_name;
  }

  return $full_name;


}

//-----------------------------------------------------------------------------
/**
 * PREPARE STRINGS FOR UTF8 
 * 
 * check if a string is UTF8 encoded; if not, convert it
 * (the string must be ISO-8859-1 because we use utf8_encode function)
 * 
 * @param string $string (utf8 or latin1)
 * @return string - converted utf8 string
 */

function sutf8($string)
{
        $rez = preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
        
        $newst = ( $rez ) ? $string : utf8_encode($string);
        return $newst; 
}

//-----------------------------------------------------------------------------
/**
 * RETURN ALL THE ZTN's FOR A PATIENT
 * 
 * @param int $pid
 * @return array - contains all ztn's (closed, open) ; empty array if not ztn available
 */
function all_ztn($pid = 0) {
    if ( !$pid ) return FALSE;

    $result = array();
    $q = sprintf("SELECT * FROM cl_careroute_numbers WHERE cn_pid = %d ORDER BY cn_dopen", $pid);
    $r = mysql_query($q) or die( mysql_error() );
    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            $result[] = $row;
        }
    }
    
    return $result;
}

//-----------------------------------------------------------------------------
/**
 * RETURN ALL THE DBC's FOR A PATIENT
 * 
 * using ztn id
 * 
 * @param int $ztn
 * @return array - contains all dbc's (closed, open, sent to insurer) ; empty array if not ztn available
 */
function all_dbc($ztnid = 0) {
    if ( !$ztnid ) return FALSE;

    $result = array();
    $q = sprintf("SELECT * FROM cl_axes WHERE ax_ztn = '%s' ORDER BY ax_odate", $ztnid);
    $r = mysql_query($q) or die( mysql_error() );
    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            $result[] = $row;
        }
    }
    
    return $result;
}

//-----------------------------------------------------------------------------
/**
 * OPEN ZTN/DBC?
 * 
 * verify for a patient if a ZTN is already opened
 * if it is, verify further if there is any open DBC
 * 
 * DBC opened - DBC open           - 2
 * DBC closed - Geen DBC           - 1
 * ZTN closed - Dossier gesloten   - 0
 * 
 * @param $pid patient id
 * @return array - a string + a value
 */
function has_ztndbc($pid = 0){
    if ( !$pid ) return FALSE;
    $result = '';

    // search for an opened ZTN (id1007)
    $qc = sprintf("SELECT cn_ztn FROM cl_careroute_numbers WHERE cn_pid = %d AND cn_open = '1'", $pid);
    $rez = mysql_query($qc) or die(mysql_error());
    if ( mysql_num_rows($rez) ) {
      $row = mysql_fetch_array($rez);
      $opened_ztn = $row['cn_ztn'];

      $qb = sprintf("SELECT * FROM cl_axes WHERE ax_ztn = '%s'  AND ax_open = '1' ", $opened_ztn);
      $rezb = mysql_query($qb) or die(mysql_error());

      $result['str'] = ( mysql_num_rows($rezb) ) ? 'DBC open.' : 'Geen DBC!';
      $result['code'] = ( mysql_num_rows($rezb) ) ? 2 : 1;

    } else {
      $result['str'] = 'Dossier gesloten!'; $result['code'] = 0;
    }

    return $result;
}


//-----------------------------------------------------------------------------
/**
 * BEROEP DROPDOWN
 * 
 * build the dropdown for providers jobs
 * name: beroep id: beroep
 * 
 * @param int $selected
 * @return void - just echo the string - html encoded
 */
function beroep_dropdown($selected = 0){

    $string = '<select name="beroep" id="beroep">';
    $today = date('Y-m-d');
  
    $q = sprintf("  SELECT cl_beroep_element AS cbe, cl_beroep_sysid AS cbs
                    FROM cl_beroep 
                    WHERE cl_beroep_selecteerbaar = 1  
                    AND cl_beroep_einddatum > '%s' AND cl_beroep_begindatum < '%s'
                    ORDER BY cbe", $today, $today);
    $r = mysql_query($q) or die(mysql_error());
    while ( $row = mysql_fetch_array($r) ) {
        $sufix = ( $selected == $row['cbs']) ? 'selected="selected"' : '';
        $string .= "<option value='{$row['cbs']}' $sufix>{$row['cbe']}</option>";
    }

    $string .= '</select>';
    echo $string;

}

//-----------------------------------------------------------------------------
/**
 * ZORGTYPECODES DROPDOWN
 * 
 * build the dropdown for zorg types codes
 * in dbc closing section (dbc_close.php)
 * 
 * name="ztc" id="ztc"
 * 
 * @param none 
 * @return void - just echo the string - html encoded
 */
function zorgtype_dropdown(){

    $string = '<select name="ztc" id="ztc">';
    $today = date('Y-m-d');

    $ztc = zorgtype_codes();

    $display = 1;
    foreach($ztc as $z) {
        // zorgtype validation ;  we try to exclude 180104 if the activities validation failed
        if (  $z['cl_zorgtype_sysid'] == 180104 && !vl_zorgtype_104()  ) {
            $display = 0;
        }
        // zorgtype validation ;  we try to exclude 180104 if the direct time > 180
        if (  $z['cl_zorgtype_sysid'] == 180104 && !vl_zorgtype_880()  ) {
            $display = 0;
        }
        // zorgtype validation ;  we try to exclude 180106 if the activities total time validation failed
        if (  $z['cl_zorgtype_sysid'] == 180106 && !vl_zorgtype_106()  ) {
            $display = 0;
        }
        // zorgtype validation ;  we try to exclude 180111 if the providers job test failed
        if (  $z['cl_zorgtype_sysid'] == 180111 && !vl_zorgtype_111 ()  ) {
            $display = 0;
        }

        if ( $display ) 
            $string .= '<option value="' .$z['cl_zorgtype_sysid']. '">' .$z['cl_zorgtype_beschrijving']. '</option>'; 

        $display = 1;
    } 
    $string .= '</select>';

    echo $string;

}

//-----------------------------------------------------------------------------
/**
 * PATIENT AGE
 * 
 * return the patient age
 * (!) uses a function from OpenEMR
 * 
 * @param int $pid - patient ID 
 * @return int - age in years
 */
function patient_age($pid = 0){
    if ( !$pid ) return FALSE;

    // retrieve DOB for the patient
    $q = sprintf("SELECT DOB FROM patient_data WHERE id = %d ", $pid);
    $r = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_array($r);

    $dob = $row['DOB']; 
    if ( 0 == $dob ) {
        vl_log("Patient with ID: $pid doesn't have DOB!"); return FALSE;
    }

    $dobn = str_replace('-','', $dob);
    $age = getPatientAge($dobn); // function from library/patient.inc

    // $age can contain strings like 6 month, 8 month for age < 2 years old
    if ( is_string($age) ) $age = 2;

    return $age;
}

//-----------------------------------------------------------------------------
/**
 * HAS BEGIN GAF
 * 
 * for some patients (with age < 4) we don't fill a begin GAF - AS5
 * so, if we close the DBC, we don't ask for the other 2 GAF if we don't have the first one.
 * 
 * @param int $axid - DBC id
 * @return bool - true if there is a begin GAF
 */
function has_beginGAF($axid = 0){
    if ( !$axid ) return FALSE;

    $dbc = content_diagnose($axid);
    $ax5 = unserialize($dbc['ax_as5']);

    return ( !empty($ax5) );

}

//-----------------------------------------------------------------------------
/**
 * DISPLAY LINKS
 * 
 * display links as Add/Edit DSM-IV, Close DSM-IV, etc... in coding.php (patient_file)
 * 
 * @param none
 * @return void
 */
function display_links(){
    $retcode = ztn_status(); // find the ZTN situation

    switch ( $retcode ) {
        case 0: $msg = 'No ZTN opened!';  break;
        case 1: 
        case 3: $msg = '<dd><a class="text" href="javascript:selas()">Add DSM IV</a></dd>
            <dd><a class="text" href="#" id="closeztn">Close ZTN</a></dd>'; break;
        case 2: $msg = '<dd><a class="text" href="javascript:selas()">Edit DSM IV</a></dd>
            <dd><a class="text" href="javascript:selcl()">DBC Sluiten</a></dd>'; break;
        case 4: $msg = '<dd><a class="text" href="javascript:selfl()">Follow up display</a></dd>
            <dd><a class="text" href="javascript:selcl()">DBC Sluiten</a></dd>'; break;
    }

    echo $msg;
}

//-----------------------------------------------------------------------------
/**
 * RETURN THE DBC'S W/OUT FUTURE EVENTS
 * 
 * return opened dbc's without future events
 * 
 * @param none
 * @return array
 */
function df_future_events(){
    $resarr = array(); // dummy array for result
    $date = date('Y-m-d');
    $q = mysql_query("SELECT * FROM cl_axes ca WHERE ca.ax_open = 1 ORDER BY ca.ax_odate") or die(mysql_error());

    if ( mysql_num_rows($q) ) {
        while ( $row = mysql_fetch_array($q) ) {
            $count = 0;
            $pid = what_patient($row['ax_id']);
            $fe = mysql_query("SELECT COUNT(*) AS a FROM openemr_postcalendar_events WHERE pc_pid = $pid 
                              AND pc_eventDate > '$date' ") or die(mysql_error());
            $rfe = mysql_fetch_array($fe);
            $count = $rfe['a']; // how many future encounters

            if ( !$count ) { $row['pid'] = $pid; $resarr[] = $row; }

        } // while
    } 

    return $resarr;

}


//-----------------------------------------------------------------------------
/**
 * RETURN OPENED DBC'S WITH TOTAL TIME
 * 
 * the times are separated per years 2007/2008
 * 
 * @param none
 * @return array
 */
function df_allopendbc_wtimes(){
    $resarr = array(); // dummy array for result
    $today  = date('Y-m-d');
    $q = mysql_query("SELECT * FROM cl_axes ca WHERE ca.ax_open = 1 ORDER BY ca.ax_odate") or die(mysql_error());

    if ( mysql_num_rows($q) ) {
        $count = 1;
        while ( $row = mysql_fetch_array($q) ) {
            $odate   = $row['ax_odate'];
            $resarr[$count]['dbcid'] = $row['ax_id'];
            $resarr[$count]['odate'] = $odate;

            if ( $odate <= '2007-12-31' ) {
                $times2007          = total_time_spent($row['ax_id'], $odate, '2007-12-31');
                $times2008          = total_time_spent($row['ax_id'], '2008-01-01', $today);
                $resarr[$count]['2007']   = $times2007['total_time']; 
                $resarr[$count]['2008']   = $times2008['total_time'];
            } else {
                $times              = total_time_spent($row['ax_id'], '2008-01-01', $today);
                $resarr[$count]['2007'] = 0; 
                $resarr[$count]['2008']   = $times['total_time'];
            }

            $pid = what_patient($row['ax_id']);
            $resarr[$count]['pid'] = $pid;  // using $times as a returning array
            $count++;
        } // while
    } 
//echo '<pre>' . print_r($resarr, TRUE) . '</pre>'; // debug
    return $resarr;

}

//-----------------------------------------------------------------------------
/**
 * RETURNS SELECTED ACTIVITY FROM THE FORM
 * 
 * it's about add_edit_event.php form
 * @param none
 * @return string
 */
function selected_ac() {
    // same logic as in javascript validation
    if ( $_POST['box5'] ) $ac = $_POST['box5'];
    elseif ( $_POST['box4']) $ac = $_POST['box4'];
    elseif ( $_POST['box3']) $ac = $_POST['box3'];
    elseif ( $_POST['box2']) $ac = $_POST['box2'];
    elseif ( $_POST['box1']) $ac = $_POST['box1'];

    return $ac;
}


//-----------------------------------------------------------------------------
/**
 * RETURN OPENED DBC'S WITH MONEY VALUES FOR EACH
 * 
 * it simulates a closing for opened DBC's
 * 
 * @param none
 * @return array
 */
function df_dbcvalues(){
    $dbcdata = array(); // dummy array for result
    $ztn = 180202 ; // default zorgtraject at closing ()
    $today  = date('Y-m-d');
    $q = mysql_query("SELECT * FROM cl_axes ca WHERE ca.ax_open = 1 ORDER BY ca.ax_odate") or die(mysql_error());

    if ( mysql_num_rows($q) ) {
        $count = 1;
        global $rfsum;
        while ( $row = mysql_fetch_array($q) ) {
            $dbcid = $row['ax_id'];
            $odate = $row['ax_odate'];

            $rfsum = 0; // reset the rfsum!
            dt_main(1, $dbcid, $today);
            $z = dt_whatproductgroep($rfsum, $odate);
            $pcode = $z['id'];

            $prestatie = dt_prestatiecode($ztn, $pcode, $dbcid, 1);

            $declaratie = df_declaratie($prestatie, $today);
            $tariff = df_tariff($declaratie, $today);

            $dedamo = ( vk_is_overloop_dbc($dbcid) ) ? vk_deduction($dbcid) : 0;

            $tariff_per = round (((int)$tariff * C417) / 10000);
            $tariff_final = $tariff_per - $dedamo;


            $dbcdata[$count]['pid']         = what_patient($dbcid);
            $dbcdata[$count]['dbcid']       = $dbcid;
            $dbcdata[$count]['rfsum']       = $rfsum;
            $dbcdata[$count]['pcode']       = $pcode;
            $dbcdata[$count]['prestatie']   = $prestatie;
            $dbcdata[$count]['declaratie']  = $declaratie;
            $dbcdata[$count]['tariff']      = $tariff_final;
            $dbcdata[$count]['odate']       = $row['ax_odate'];

            $count++;
        } // while
    } 

    return $dbcdata;

}


//-----------------------------------------------------------------------------
/**
 * DECLARATIE CODE
 * 
 * used by df_dbcvalues()
 * 
 * @param 
 * @return 
 */
function df_declaratie($pcode, $cdate) {
    $q = sprintf("SELECT cl_declaratiecode AS cd FROM cl_prestatiecode WHERE cl_dbc_prestatiecode = '%s'
            AND cl_prestatiecode_begindatum <= '%s' AND cl_prestatiecode_einddatum >= '%s' ", $pcode, $cdate, $cdate);
    $r = mysql_query($q) or die(mysql_error()); 

    if ( $row = mysql_fetch_array($r) ) {
        $retval = $row['cd'];
    } else {
        $retval = 0;
    }

    return $retval;
}


function df_tariff($decode, $date) {
    $q = sprintf("SELECT cl_dbc_tarief FROM cl_dbc_tarief WHERE cl_declaratiecode = '%s' 
            AND cl_dbc_tarief_begindatum <= '%s' AND cl_dbc_tarief_einddatum >= '%s' ", $decode, $date, $date);
    $r = mysql_query($q) or die(mysql_error()); 

    if ( mysql_num_rows($r) ) {
       $row = mysql_fetch_array($r); $val = $row['cl_dbc_tarief'];
    } else {
        $val = 0;
    }

    return $val;
}

//-----------------------------------------------------------------------------
/**
 * RETURN PATIENTS WITH OPENED ZTN BUT NO OPENED DBC
 * 
 *  
 * @param 
 * @return 
 */
function df_opztn_nodbc() {
    $q = sprintf("SELECT id FROM patient_data WHERE 1");
    $r = mysql_query($q) or die(mysql_error()); 

    while ( $row = mysql_fetch_array($r) ) {
        $pid = $row['id'];

        $result = has_ztndbc($pid);
        if ( $result['code'] ) {
            $allztn = all_ztn($pid); 
            $lastztn = end($allztn);

            $alldbc = all_dbc($lastztn['cn_ztn']);
            $lastdbc = end($alldbc);

            if ( !$lastdbc['ax_open'] ) {
                $pidres[$pid]['result'] = $result;
                $pidres[$pid]['dbc'] = $lastdbc;
                $pidres[$pid]['ztn'] = $lastztn;
            } // if
        }
    } // while

    return $pidres;
}

//-----------------------------------------------------------------------------
?>
