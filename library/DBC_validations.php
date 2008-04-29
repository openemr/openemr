<?php
/** 
 * DBC DUTCH SYSTEM
 * Validation functions
 * 
 * Cristian NAVALICI (lemonsoftware [..] gmail [.] com)
 * January 2008
 */

// ----------------------------------------------------------------------------
/**
 * VALIDATE CIRCUIT VALUE
 * 
 * validates the circuit code
 * 
 * @param int $circode
 * @return bool - an integer for true, otherwise FALSE
 */
function vl_validate_circuit($circode = 0) {
    if ( !$circode ) return FALSE;

    $q = sprintf("SELECT * FROM cl_circuit WHERE cl_circuit_code = %d ", $circode);
    $r = mysql_query($q) or die (mysql_error()); 
    return mysql_num_rows($r);
}


// ----------------------------------------------------------------------------
/**
 * VALIDATE REDENCODE
 * 
 * validates the redensluiten code
 * 
 * @param int $redencode
 * @return bool - an integer for true, otherwise FALSE
 */
function vl_validate_redencode($redencode = 0) {
    if ( !$redencode ) return FALSE;

    $q = sprintf("SELECT * FROM cl_redensluiten WHERE cl_redensluiten_code = %d ", $redencode);
    $r = mysql_query($q) or die (mysql_error()); 
    return mysql_num_rows($r);
}


// ----------------------------------------------------------------------------
/**
 * VALIDATE DIAGNOSES
 * 
 * validates the diagnoses from dbc before saving them
 * 
 * @param none
 * @return bool
 */
function vl_validate_diagnoses() {
    $as1 = $_SESSION['as1']; $as2 = $_SESSION['as2']; $as3 = $_SESSION['as3'];
    $as4 = $_SESSION['as4']; $as5 = $_SESSION['as5']; 
    // as a caution we check for posas. must be NOT null
    $posas = ( $_SESSION['posas'] ) ? (int)$_SESSION['posas'] : 1; // default value

    // we presume that $good is true; one single value of FALSE, and everything blows up! ;)
    $good = TRUE;
    // an array with all diagnoses
    $alldiag = array(); 

    // diagnose type 1
    foreach ( $as1 as $a1 ) {
        $good &= vl_diagnose_exist($a1); 
        $alldiag[] = $a1; 
    }
    if ( !$good ) return FALSE;

    // diagnose type 2
    foreach ( $as2 as $a2 ) {
        $good &= vl_diagnose_exist($a2['code']); 
        $alldiag[] = $a2['code']; 
    }
    if ( !$good ) return FALSE;

    if ( vl_main_forbidden($alldiag[$posas-1]) ) return FALSE;

    // diagnose type 3 + 4
    $good &= vl_diagnose_exist($as3); $alldiag[] = $as3; 
    $good &= vl_diagnose_exist($as4); $alldiag[] = $as4; 

    // diagnose type 5
    if ( patient_age($_SESSION['pid']) >= 4 ) {
        $good &= vl_diagnose_exist($as5['gaf1']);
        $alldiag[] = $a5['gaf1']; 
    }

    // now we check for unique values
    // the arrays must be the same if there are no duplicated values
    $uniqdiag = array_unique($alldiag);
    if ( count($uniqdiag) != count($alldiag) ) $good &= FALSE;

    return $good;

}


// ----------------------------------------------------------------------------
/**
 * SIMPLE CHECK IF A DIAGNOSE EXISTS
 * 
 * check if a diagnose is valid (exists in database)
 * 
 * @access private
 * @param string $diag - diagnose to check
 * @return bool
 */
function vl_diagnose_exist($diag) {
    $q = sprintf("SELECT * FROM cl_diagnose WHERE cl_diagnose_code = '%s' ", trim($diag));
    $r = mysql_query($q) or die (mysql_error()); 
    return mysql_num_rows($r);
}


// ----------------------------------------------------------------------------
/**
 * VALIDATE ZORGCODE
 * 
 * validates the zorg sysid
 * 
 * @param int $zorgsysid
 * @return bool - an integer for true, otherwise FALSE
 */
function vl_validate_zorg($zorgsysid = 0) {
    if ( !$zorgsysid ) return FALSE;

    $q = sprintf("SELECT * FROM cl_zorg WHERE cl_zorgtype_sysid = %d ", $zorgsysid);
    $r = mysql_query($q) or die (mysql_error()); 
    return mysql_num_rows($r);
}


// ----------------------------------------------------------------------------
/**
 * CHECK IF THE MAIN DIAGNOSE IS NOT ON THE FORBIDDEN LIST OF ITEMS
 * 
 * @access private
 * @param string $diag - diagnose to check
 * @return bool - true if it's forbidden
 */
function vl_main_forbidden($diag) {

    $forbidden_list = array('as1_18.02', 'as1_18.03', 'as2_18.02', 'as2_18.03', 'as2_17.01', 'as2_01.01.01',
                            'as2_01.01.02', 'as2_01.01.03', 'as2_01.01.04', 'as2_01.01.05');
    $diag = trim($diag);
    
    return in_array($diag, $forbidden_list);

}




// ----------------------------------------------------------------------------
/**
 * LOG 
 * 
 * simple function to log different events
 * 
 * @param string $string
 * @return 
 */
function vl_log($string) {
    $file = TMPDIR_DBC . '/DBC_problems.log';
    if ( !$h = fopen($file, 'ab') ) {
        echo "Cannot create file ($filename)";
        exit;
    }

    $content = date('d-m-Y') . " $string \r\n";

    // WRITE DATA TO FILE
    if ( fwrite($h, $content) === FALSE ) {
        echo "Cannot write to file ($filename)";
        exit;
    }  

    fclose($h);
 
}


// ----------------------------------------------------------------------------
/**
 * VERIFY PATIENT AGE WHEN CLOSING A DBC
 * 
 * At the moment of closing DBC, if we have ZTC code = 115 | 210 (Ondertoezichtstelling)
 * the patient must have the age < 18 years at the moment of opening DBC.
 * We leave $ztcode here because we are sure that this code validation has already take place.
 * 
 * @param int $ztcode - zorg code from the dropdown
 * @param string $axodate - opening date for the DBC
 * @param int $pid - patient id
 * @return bool
 */
function vl_ztc_age($ztcode = 0, $axodate = 0, $pid = 0) {
    if ( !$ztcode || !$axodate || !$pid) return FALSE;

    // these are the only 2 checked codes
    if ( ( $ztcode != '180115') && ($ztcode != '180210') ) return TRUE;

    $age = patient_age($pid);

    return ( $age < 18 );

}

// ----------------------------------------------------------------------------
/**
 * VERIFY IF THE CLOSING DATE IS BIGGER THAN LAST ACTIVITY DATE
 * 
 * At the moment of closing DBC, we must have the closing date bigger than
 * the date of the last activity associated with that DBC.
 * With other words, the DBC must be closed AFTER all activities.
 * 
 * @param string $eind - closing date
 * @param int $axid - id for DBC
 * @param int $pid - patient ID
 * @return array - bool + string (result and lastdate)
 */
function vl_eind_event($eind = 0, $axid = 0, $pid = 0) {
    if ( !$eind || !$axid || !$pid) return FALSE;

    // begin date for DBC
    $cd = content_diagnose($axid);  
    $bd_dbc = $cd['ax_odate'];

    // find all events between DBC's dates and sum up total times
    $q = sprintf("SELECT MAX(pc_eventDate) AS pcdate 
                FROM openemr_postcalendar_events 
                WHERE pc_pid = %d AND pc_eventDate >= '%s' AND pc_apptstatus = '@' ",
                $pid, $bd_dbc);
    $r = mysql_query($q) or die(mysql_error());

    if ( !mysql_num_rows($r) ) return FALSE;
    $row = mysql_fetch_array($r);
    $lastdate   = strtotime($row['pcdate']);
    $axdate     = strtotime($eind);

    $result['bool'] = ( $axdate >= $lastdate );
    $result['date'] = $row['pcdate'];

    return $result;
}

// ----------------------------------------------------------------------------
/**
 * VALIDATE THE TIMINGS FOR A SPECIFIC ACTIVITY
 * 
 * if activity is act_1 or act_7% then no times (encounter, travel, indirect must be 0)
 * NOTE: apparently not used anymore 
 *
 * @param string $ac - activity string
 * @return bool - true (there are no timings) if activity matches conditions
 */
function vl_activity_timing($ac) {
    return ( $ac == 'act_1' || (strpos($ac, 'act_7') !== FALSE) );

}


// ----------------------------------------------------------------------------
/**
 * VALIDATE THE TRAVEL TIME FOR A SPECIFIC ACTIVITY
 * 
 * if activity has mag_reistijd code = N (in cl_activiteit table), then no travel time
 * 
 * @param string $ac - activity string
 * @return bool - true (there are no timings) if activity matches conditions
 */
function vl_activity_travel($ac) {
    $q = sprintf("SELECT cl_activiteit_mag_reistijd AS reis FROM cl_activiteit WHERE cl_activiteit_code = '%s' ", $ac);
    $r = mysql_query($q) or die (mysql_error()); 
    
    $row = mysql_fetch_array($r);
    return ( $row['reis'] == 'N' );
}

// ----------------------------------------------------------------------------
/**
 * CHECK FOR ZORGTYPE CODE VALUE OF 104
 * 
 * if zorgtype code  = 104 (sysid 180104), then all activities must have cl_activiteit_mag_groep = N
 * must be called from within the openemr/dbc closing form
 * 
 * @param none
 * @return bool - true if all activities meet conditions
 */
function vl_zorgtype_104() {
    $dia    = content_diagnose($_SESSION['show_axid']);
    $odate  = $dia['ax_odate'];
    $pid    = (int)$_SESSION['pid'];

    // find all events from DBC opening and look for activities
    $q = sprintf("SELECT pc_eid FROM openemr_postcalendar_events 
                WHERE pc_pid = %d AND pc_eventDate >= '%s' AND pc_apptstatus = '@' ",
                $pid, $odate);
    $r = mysql_query($q) or die(mysql_error());

    $res = TRUE;
    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_activiteit_mag_groep AS cgroep FROM cl_activiteit ca 
        JOIN cl_event_activiteit cea ON cea.activity_sysid = ca.cl_activiteit_sysid
        WHERE cea.event_id = %d", $row['pc_eid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        if ( $rowe['cgroep'] != 'N' ) {
            $res = FALSE; break;
        } 
    } // while

    return $res;
}


// ----------------------------------------------------------------------------
/**
 * CHECK FOR ZORGTYPE CODE VALUE OF 104
 * 
 * if zorgtype code  = 104 (sysid 180104), then direct time must be < 180
 * must be called from within the openemr/dbc closing form
 * 
 * @param none
 * @return bool - true if all activities meet conditions
 */
function vl_zorgtype_880() {
    $ttime = total_time_spent();

    return ( $ttime['total_time'] < 180);

}

// ----------------------------------------------------------------------------
/**
 * CHECK FOR ZORGTYPE CODE VALUE OF 106
 * 
 * if zorgtype code  = 106 (sysid 180106), then direct time must be <= 250
 * must be called from within the openemr/dbc closing form
 * 
 * @param none
 * @return bool - true if all activities meet conditions
 */
function vl_zorgtype_106() {
    $ttime = total_time_spent();
    $direct_time = $ttime['total_time'] - $ttime['indirect_time'] - $ttime['travel_time'];

    return ( $direct_time <= 250);

}

// ----------------------------------------------------------------------------
/**
 * CHECK FOR ZORGTYPE CODE VALUE OF 102
 * 
 * if zorgtype code  = 102 (sysid 180102), then total length of DBC must be < 29 days
 * must be called from within the openemr/dbc closing form
 * 
 * @param int $axid - DBC id
 * @param string $eind - ending date
 * @return bool - true if the conditions are met
 */
function vl_zorgtype_102($axid, $eind) {

    $len = df_dbc_age($axid, $eind); 

    return ( $len < 29);

}


// ----------------------------------------------------------------------------
/**
 * CHECK FOR ZORGTYPE CODE VALUE OF 111
 * 
 * if zorgtype code  = 111 (sysid 180111), then there is at least one provider with
 * job starting with MB*.
 * must be called from within the openemr/dbc closing form
 * 
 * @param none
 * @return bool  - true if it's at least one provider with MB
 */
function vl_zorgtype_111() {
    $dia    = content_diagnose($_SESSION['show_axid']);
    $odate  = $dia['ax_odate'];
    $pid    = (int)$_SESSION['pid'];

    // find all events from DBC opening and look for activities
    $q = sprintf("SELECT pc_aid, pc_eid FROM openemr_postcalendar_events 
                WHERE pc_pid = %d AND pc_eventDate >= '%s' AND pc_apptstatus = '@' ",
                $pid, $odate);
    $r = mysql_query($q) or die(mysql_error());

    $res = FALSE;
    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_beroep_code AS cbc FROM cl_beroep cb 
        JOIN cl_user_beroep cub ON cub.cl_beroep_sysid = cb.cl_beroep_sysid
        WHERE cub.cl_beroep_userid = %d ", $row['pc_aid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        if ( strpos($rowe['cbc'], 'MB.') === 0 ) {
            $res = TRUE; break;
        } 
    } // while

    // as a last resort, we check for the job of the provider who close the DBC
    if ( !$res ) {
        $ql = sprintf("SELECT cl_beroep_code AS cbc FROM cl_beroep cb 
                JOIN cl_user_beroep cub ON cub.cl_beroep_sysid = cb.cl_beroep_sysid
                WHERE cub.cl_beroep_userid = %d ", $_SESSION['authId']);
        $rl = mysql_query($ql) or die(mysql_error());
        $rowl = mysql_fetch_array($rl);
        if ( strpos($rowl['cbc'], 'MB.') === 0 ) {
            $res = TRUE;
        }
    }

    return $res;

}


// ----------------------------------------------------------------------------
/**
 * CHECK FOR REDENSLUITEN CODE 5 IF THERE ARE FORBIDDEN ACTIVITIES
 * 
 * cl_activiteit_code should NOT contain act_3*, act_4*, act_5*, act_9* or act_10*)
 * must be called from within the openemr/dbc closing form
 * 
 * @param none 
 * @return void
 */
function vl_redensluiten_5() {
    $dia    = content_diagnose($_SESSION['show_axid']);
    $odate  = $dia['ax_odate'];
    $pid    = (int)$_SESSION['pid'];

    // find all events from DBC opening and look for activities
    $q = sprintf("SELECT pc_eid FROM openemr_postcalendar_events 
                WHERE pc_pid = %d AND pc_eventDate >= '%s' AND pc_apptstatus = '@' ",
                $pid, $odate);
    $r = mysql_query($q) or die(mysql_error());

    $res = TRUE; 
    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_activiteit_code AS acode FROM cl_activiteit ca 
        JOIN cl_event_activiteit cea ON cea.activity_sysid = ca.cl_activiteit_sysid
        WHERE cea.event_id = %d", $row['pc_eid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        $c = $rowe['acode']; // activity code

        // check for forbidden activities
        $pos1 = strpos($c, 'act_3');
        $pos2 = strpos($c, 'act_4');
        $pos3 = strpos($c, 'act_5');
        $pos4 = strpos($c, 'act_9');
        $pos5 = strpos($c, 'act_10');
        if ( ($pos1 === FALSE) && ($pos2 === FALSE) && ($pos3 === FALSE) && ($pos4 === FALSE) && ($pos5 === FALSE) ) {
            $res = TRUE; 
        } else {
            $res = FALSE; break;
        }
    } // while

    return $res;
}


// ----------------------------------------------------------------------------
/**
 * CHECK FOR ALLOWED COMBINATION OF ACTIVITIES FOR A DBC
 * 
 * the following are forbidden:
 * ONLY act_4.2* or
 * ONLY act_9* or
 * act_4.2* in combination with act_1 || act_2 || act_7* || act_9*
 * 
 * @param $dbcid - dbc id
 * @return bool
 */
function vl_validdbc_combinations($dbcid) {
    $dia    = content_diagnose($dbcid);
    $odate  = $dia['ax_odate'];
    $cdate  = $dia['ax_cdate'];
    $pid    = what_patient($dbcid);

    // find all events from DBC opening to DBC closing and look for activities
    $q = sprintf("SELECT pc_eid FROM openemr_postcalendar_events 
                WHERE pc_pid = %d AND pc_eventDate >= '%s' AND pc_eventDate <= '%s' AND pc_apptstatus = '@' 
                AND pc_title != 'Blokkeren' ",
                $pid, $odate, $cdate);
    $r = mysql_query($q) or die(mysql_error());

    $acodes = array();
    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_activiteit_code AS acode FROM cl_activiteit ca 
        JOIN cl_event_activiteit cea ON cea.activity_sysid = ca.cl_activiteit_sysid
        WHERE cea.event_id = %d", $row['pc_eid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        // for forbidden ones, we use only pattern part
        $ac     = $rowe['acode']; // activity code
        if      ( strpos($ac, 'act_4.2') === 0 )    $acodes[] = 'act_4.2';
        else if ( strpos($ac, 'act_7') === 0 )      $acodes[] = 'act_7';
        else if ( strpos($ac, 'act_9') === 0 )      $acodes[] = 'act_9';
        else    $acodes[] = $ac;

    } // while

    // now we analyze all the activities codes
    // check if all values are act_4.2* / act_9 forms
    $c = array_count_values($acodes);
    if ( $c['act_4.2'] == count($acodes) ) return FALSE;
    if ( $c['act_9'] == count($acodes) ) return FALSE;

    // now we check for combinations for act_4.2
    if ( in_array('act_4.2', $c) ) {
        $rez_1 = !( in_array('act_1', $c) || in_array('act_2', $c) || in_array('act_7', $c) || in_array('act_9', $c) );
    } else $rez_1 = TRUE;

    // now we check for act_10.2 condition
    if ( in_array('act_10.2', $c) ) {
        $rez_2 = in_array('act_3.2', $c);
    } else $rez_2 = TRUE;

    /*if ( in_array('act_9', $c) ) {
            WORK ZONE !!!
    }*/

    // combine the results
    return ($rez_1 && $rez_2);
}
// ----------------------------------------------------------------------------
?>