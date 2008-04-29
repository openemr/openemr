<?php
/** 
 * COMMON DBC DUTCH SYSTEM
 * several functions used in DBC
 * 
 * NOTE: these are functions that are used in DBC without the need of httpd server
 * in other words, the function can be used by scripts (e.g. cron scripts) w/out
 * requiring sessions or something else
 * 
 * @author Cristian NAVALICI 
 * @version 1.0 
 */

require_once(dirname(__FILE__) . '/sql.inc');


//-----------------------------------------------------------------------------
/**
 * RETURN AGE OF THE DBC
 * 
 * return the number of days between opening date and a specified date
 * if not specified, we use current date
 * 
 * @param int $dbcid - id for dbc 
 * @param string $cdate - closing date (mysql form YYYY-MM-DD)
 * @return int days
 */
function df_dbc_age($dbcid, $cdate = 0){
    if ( !$dbcid ) return FALSE;

    if ( !$cdate ) $cdate = date('Y-m-d');

    // if date is not in the valid form, then use the current
    $ard = split('-', $cdate);
    $y  = (int)$ard[0]; $m = $ard[1]; $d = $ard[2];
    if ( !checkdate($m, $d, $y) ) $cdate = date('Y-m-d');

    $dia    = content_diagnose($dbcid);
    $odate  = $dia['ax_odate'];

    $difference = (strtotime($cdate) - strtotime($odate))/(24*60*60);

    return (int)$difference;
}


//-----------------------------------------------------------------------------
/**
 * CONTENT FOR A SPECIFIED DBC
 * 
 * @param int $axid - id for diagnose
 * @return array - contains all info for a diagnose
 */
function content_diagnose($dbcid = 0){
   if ( !$dbcid ) return FALSE;

   $qc = sprintf("SELECT * FROM cl_axes WHERE ax_id = %d ", $dbcid);
   $rez = mysql_query($qc) or die(mysql_error());
   return mysql_fetch_array($rez);
}

//-----------------------------------------------------------------------------
/**
 * DUPLICATE DBC
 * 
 * closes a dbc (modify the flag) and open a new one with the same content
 * THE CLOSING OPERATION ITSELF IS NOT DONE BY THIS! (use close_dbc with $follow = 1)
 * for the new one, the opening date will be one day ahead (due of a restrain in validation stuff)
 * 
 * @param array|int $dbc - old dbc | $dbcid
 * @return int $dbc id - new id
 */
function duplicate_dbc($dbc = 0) {

    if ( !$dbc ) return FALSE;

    // if it's integer, than obtain the content
    if ( !is_array($dbc) ) {
        $dbc = content_diagnose($dbc);
    }

    mysql_query("START TRANSACTION");
    
    $cdate1     = ( $_SESSION['eind'] ) ? $_SESSION['eind'] : date('Y-m-d');
    $cdate2     = '9999-12-31'; // mysql default

    $odate1     = $dbc['ax_odate'];
    $odate2     = date ('Y-m-d', (strtotime($cdate1) + 86400)); // one day ahead
    
    
    // insert a new one
    $qi = sprintf("INSERT INTO cl_axes (ax_ztn, ax_open, ax_as1, ax_as2, ax_as3, ax_as4, ax_as5, ax_odate, ax_cdate, ax_sti)
    VALUES ('%s', %d,'%s','%s','%s','%s','%s','%s','%s','%s')", 
        $dbc['ax_ztn'], 
        1, 
        $dbc['ax_as1'],
        $dbc['ax_as2'],
        $dbc['ax_as3'],
        $dbc['ax_as4'],
        $dbc['ax_as5'],
        $odate2,
        $cdate2,
        0);
    mysql_query($qi) or die (msqyl_error());
    //echo "$qi \n";
    $newid = mysql_insert_id();

    // =====================
    // close the old one
    $qu = sprintf("UPDATE cl_axes SET ax_open = 0, ax_cdate = '%s' WHERE ax_id = %d", $cdate1, $dbc['ax_id']);
    mysql_query($qu) or die(mysql_error());
    //echo "$qu \n";

    // update the related tables (cl_circuit_dbc)
    $qc = sprintf("SELECT ccd_circuitcode FROM cl_circuit_dbc WHERE ccd_dbcid = %d ", $dbc['ax_id']);
    $rc = mysql_query($qc) or die(mysql_error());
    $circuit = mysql_fetch_array($rc);
    
    $qdc = sprintf("INSERT INTO cl_circuit_dbc(ccd_circuitcode, ccd_dbcid) VALUES (%d, %d)", $circuit['ccd_circuitcode'], $newid);
    mysql_query($qdc) or die(mysql_error());
    
    mysql_query("COMMIT");
    
    return $newid;
}


//-----------------------------------------------------------------------------
/**
 * CRON LOG
 * 
 * logs an event in cron functions
 * 
 * @param string - string to be written
 * @return void
 */
function df_cronlog($string){
   $file = '/tmp/DBC_cron.log';
    if ( !$h = fopen($file, 'ab') ) {
        echo "Cannot create file ($filename)";
        exit;
    }

    $content = date('d-m-Y H:i') . " $string \r\n";

    // WRITE DATA TO FILE
    if ( fwrite($h, $content) === FALSE ) {
        echo "Cannot write to file ($filename)";
        exit;
    }  

    fclose($h);
}


//-----------------------------------------------------------------------------
/**
 * FIND THE PATIENT FOR A GIVEN DBC / GIVEN ZTN
 * 
 * @param int $dbcid
 * @param int $ztnid
 * @return int patientid
 */
function what_patient($dbcid = 0, $ztnid = 0) {
    if ( !$dbcid && !$ztnid ) return FALSE;

    if ( $dbcid ) {
        $q = sprintf("SELECT cn_pid FROM cl_careroute_numbers ccn JOIN cl_axes ca ON ccn.cn_ztn = ca.ax_ztn
                      WHERE ca.ax_id = %d ", $dbcid);
    } else { 
        $q = sprintf("SELECT cn_pid FROM cl_careroute_numbers WHERE cn_ztn = '%s'", $ztn);
    }

    $r = mysql_query($q) or die(mysql_error());
    if ( mysql_num_rows($r) ) {
        $row = mysql_fetch_array($r);
        return $row['cn_pid'];
    } else {
        return 0;
    }

}

//-----------------------------------------------------------------------------
/**
 * DBC GET MAIN DIAGNOSE
 * 
 * return main diagnose for a given dbc
 * 
 * @param int $dbcid
 * @return string - the code for the diagnose (as*)
 */
function df_get_main_diagnose($dbcid = 0){
    if ( !$dbcid ) return 0;

    $dbc = content_diagnose($dbcid);
    $as1 = unserialize($dbc['ax_as1']);
      $as1c = $as1['content']; $mainpos = (int)$as1['mainpos']; // mainpos is written in both places
    $as2 = unserialize($dbc['ax_as2']);
      $as2c = $as2['content']; 

    // look for the main diagnose
    $counter = 1; $mainstr = '';
    foreach ( $as1c as $a) {
        if ( $counter == $mainpos ) $mainstr = $a;
        $counter++; 
    }

    // if it's not in the first array, we look further for it
    if ( !$mainstr ) {
        foreach ( $as2c as $a) {
            if ( $counter == $mainpos ) $mainstr = $a['code'];
            $counter++;
        }
    }

    return $mainstr;
}

//-----------------------------------------------------------------------------
/**
 * GET THE LAST ENCOUNTER FOR A PID
 * 
 * gets the last encounter for a patient; if month and year are provided,
 * compare if the last enc is before this date and return the comparision result
 * 
 * @param int $pid - patient id
 * @return array (date => date itself, bool => TRUE|FALSE)
 */
function last_encounter($pid, $month = '01', $year = '2008') {
    $q = sprintf("SELECT MAX(pc_eventDate) AS maxdate FROM openemr_postcalendar_events 
    WHERE pc_pid = %d AND pc_apptstatus = '@'", $pid);

    $r = mysql_query($q) or die(mysql_error());

    while ( $row = mysql_fetch_array($r) ) {
        $lastenc = $row['maxdate'];
        if ( $lastenc ) $result['bool'] = ( $lastenc <= "$year-$month-31" ); else $result['bool'] = FALSE;
        $result['date'] = $lastenc;
    }

    return $result;

}


//-----------------------------------------------------------------------------
/**
 * THERE ARE ANY FUTURE EVENTS?
 * 
 * finds out if there are future events for a patient after a specified date
 * 
 * @param int $pid - patient id
 * @param string $date - date
 * @return string - the last event
 */
function future_events($pid, $date) {
    $q = sprintf("SELECT MAX(pc_eventDate) AS maxdate FROM openemr_postcalendar_events 
                    WHERE pc_pid = %d AND pc_eventDate >= '%s'", $pid, $date);
    $r = mysql_query($q) or die(mysql_error());

    if ( mysql_num_rows($r) ) {
        $row = mysql_fetch_array($r);
        return $row['maxdate'];
    } else return 0;
}
//-----------------------------------------------------------------------------
?>
