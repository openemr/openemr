<?php
/** 
 * DBC DUTCH SYSTEM
 * Decision Tree - beslisboom
 * 
 * functions used at the moment of closing dbc to choose a productgroep in the end
 * Cristian Navalici lemonsoftware [ @ ] gmail [ . ] com
 *
 * FUNCTIONS' LIST
 *
 * int dt_main(int $node) - main function; it's the decision point in our algorithm
 * string dt_whatparam(string $kmcode) - what param is needed for km* function
 * string dt_nodefunction(int $node) - what function is associated with a node
 * int dt_comparison(string $function, int $rval) - compare the values to make a decision
 * int dt_kma001()
 * int dt_kma002()
 */

/**
 * global variables used in script
 */

$beslis_table = ''; // we use different tables for 2007/08
$rfsum = 0; // remember for sum - this we add all values from the algorithm
$dbcid = 0; // dbc id
$enddate = ''; // closing date for dbc (if used from a CLI script)

/**
 * if you run from a cron script (outside the webserver), use it this way
 *      include_once('library/DBC_cfunctions.php');
 *      include_once('library/DBC_decisiontree.php');
 *      global $rfsum;
 *      dt_main(1, 365, '2008-04-02');
 *      $z = dt_whatproductgroep($rfsum, '2008-04-02');
 *          1 - starting node, 365 - dbcid, '2008-04-02' - closing date 
 */


//-----------------------------------------------------------------------------
/**
 * MAIN FUNCTION
 * 
 * calls different functions, in a deadly precise algorithm
 * 
 * @param int $node - in what node we are (default:1)
 * @param int $dbcid - in case we are calling from a script
 * @param string $end - closing date for dbc (used in dt_allevents)
 * @return int $rfsum - remember for sum
 */
function dt_main($node = 1, $dbcid = 0, $end = '') {
    // if it's the first time, run the pre_init function
    if ( $node == 1 ) dt_preinit($dbcid, $end);

    $function = dt_nodefunction($node); 

    switch ($function) {
        case 'KMA_001': $r = dt_kma001(); break;
        case 'KMA_002': $r = dt_kma002(); break;
        case 'KMA_003': $r = dt_kma003(); break;
        case 'KMA_004': $r = dt_kma004(); break;
        case 'KMA_005': $r = dt_kma005(); break;
        case 'KMT_001': $r = dt_kmt001($node); break;
        case 'KMT_002': $r = dt_kmt002(); break;
        case 'KMT_003': $r = dt_kmt003($node); break;
        case 'KMT_004': $r = dt_kmt004(); break;
        case 'KMN_001': $r = dt_kmn001($node); break;
        case 'KMP_001': $r = dt_kmp001(); break;
        case 'KMC_001': $r = dt_kmc001(); break;
    }

    global $rfsum; 

    $direction = dt_comparison($node, $r); 
    //echo "F:$function R: $r D: $direction SUM: $rfsum <br /> --------------- <br> "; //debug
    //echo "F:$function R: $r D: $direction SUM: $rfsum \n"; // debug for CLI

    if ( $direction ) dt_main($direction);
    else {
        global $enddate, $dbcid;
        if ( !$dbcid ) $enddate = $_SESSION['eind']; // called from webinterface

        if ( !$dbcid ) {
            // for a script, we must called this ourselves using global $rfsum and closing date
            $a = dt_whatproductgroep($rfsum, $enddate);
            // called from within openemr
            $_SESSION['pgroep'] = $a['id'];
            echo $a['name']; 
        } 
    }


}

//-----------------------------------------------------------------------------
/**
 * DECIDES WHAT TABLE WILL BE USED
 * 
 * because the beslisboom has 2 tables (2007/2008), we must decide which one we'll use
 * 
 * @param int $dbcid - in case we called this from a script (and we don't have $_SESSION)
 * @param string $end - closing date for dbc
 * @return void
 */
function dt_preinit($dbc, $end) {
    // it sets some globals
    global $beslis_table, $dbcid, $enddate;
    $dbcid = $dbc;
    $enddate = ( $end ) ? $end : $_SESSION['eind'];

    $dbc = content_diagnose($dbcid);
    $odate = $dbc['ax_odate'];

    if ( $odate <= '2007-12-31' ) {
        $beslis_table = 'cl_beslisboom_2007';
    } elseif ( $odate >= '2008-01-01') {
        $beslis_table = 'cl_beslisboom';
    } else {
        // leave room for more versions :)
    }

}


//-----------------------------------------------------------------------------
/**
 * COMPARISON
 * 
 * function for comparison
 * 
 * @param int $node - node to look for (and retrieve the one value)
 * @param int $value to compare
 * @return int - next node
 */
function dt_comparison($node, $rval) {
    global $beslis_table;
    $q = sprintf("SELECT * FROM %s WHERE cbe_nodeorigin = %d ", $beslis_table, $node);

    $r = mysql_query($q) or die(mysql_error());

    if ( !mysql_num_rows($r) ) {
        return 0;
    } else {
        $row = mysql_fetch_array($r);

        // compare the values function of the operator
        switch ( $row['cbe_operator'] ) {
            case '<=':  $rez = ( $rval <= $row['cbe_value1'] ); break;
            case '<':   $rez = ( $rval < $row['cbe_value1'] ); break;
            case '=':   $rez = ( $rval == $row['cbe_value1'] ); break;
            case '>=':  $rez = ( $rval >= $row['cbe_value1'] ); break;
        }

        // update the remember for sum variable
        global $rfsum;
        $rfsum += ( $rez ) ? $row['cbe_rfsumT'] : $row['cbe_rfsumF'];

        // next hop (node)
        $ret = $rez ? $row['cbe_nodegoal_T'] : $row['cbe_nodegoal_F'];

        return $ret;
    }
}

//-----------------------------------------------------------------------------
/**
 * WHAT FUNCTION IS USED FOR A SPECIFIED NODE
 * 
 * function of node, we call a function
 * 
 * @param int node
 * @return string function name (in fact, a code from the tables)
 */
function dt_nodefunction($node) {
    global $beslis_table;
    $q = sprintf("SELECT cbe_checkKM AS km FROM %s WHERE cbe_nodeorigin = %d ", $beslis_table, $node);
    $r = mysql_query($q) or die(mysql_error());

    if ( !mysql_num_rows($r) ) {
        return 0;
    } else {
        $row = mysql_fetch_array($r);
        return $row['km'];
    }
}

//-----------------------------------------------------------------------------
/**
 * WHAT PARAMETER IS NEEDED FOR KM* FUNCTION
 * 
 * some KM* functions work with some parameter; this function retrieves from db
 * 
 * @param int $node - KM node
 * @param int $par - first or second param (1/2)
 * @return string $param (empty string for no param)
 */
function dt_whatparam($node, $par = 1) {
    global $beslis_table;

    if ( $par == 1 ) {
        $q = sprintf("SELECT cbe_KMpar1 AS param FROM %s WHERE cbe_nodeorigin = %d ", $beslis_table, $node);
    } else if ( $par == 2) {
        $q = sprintf("SELECT cbe_KMpar2 AS param FROM %s WHERE cbe_nodeorigin = %d ", $beslis_table, $node);
    }
    $r = mysql_query($q) or die(mysql_error());

    if ( !mysql_num_rows($r) ) {
        return '';
    } else {
        $row = mysql_fetch_array($r);
        return $row['param'];
    }
}

//-----------------------------------------------------------------------------
/**
 * KMA001
 * 
 * Number of days in clinic
 * Days in clinic can be recognised by CL_ACTIVITEIT_SOORT=VERBLIJFSDAG. 
 * Exception: act_8.1.6*
 * 
 * NOTE we don't use it now so it's always 0
 * 
 * @param none
 * @return int
 */
function dt_kma001() {
    return 0;
}

//-----------------------------------------------------------------------------
/**
 * KMA002
 * 
 * ver 1: Sum of all direct time of activities + total time dagbesteding
 * If CL_ACTIVITEIT_SOORT = TIJDSCHRIJVEN then sum up all DIRECT_PATIENT-GEBONDEN_TIJD. Add time (if CL_ACTIVITEIT_SOORT  * = DAGBESTEDING) *60 (because dagbesteding/daycare is registered in hours; not minutes)
 * 
 * ver 2: Sum of all direct time of activities + 15 times the hours spent in dagbesteding
 * If CL_ACTIVITEIT_SOORT = TIJDSCHRIJVEN then sum up all DIRECT_PATIENT-GEBONDEN_TIJD. Add time (if CL_ACTIVITEIT_SOORT  * = DAGBESTEDING) *15 (because dagbesteding/daycare is registered in hours; not minutes)
 * 
 * @param none
 * @return int $totaltime
 */ 
function dt_kma002() {
    $r = dt_allevents();
    $totaltime = 0;
   
    //choose here what $ver we'll use function of a date
    //$dbc = content_diagnose($_SESSION['show_axid']);
    //$odate = $dbc['ax_odate']; 
    //$ver    = ( $odate <= '2007-12-31') ? 1 : 2;
    $ver = 2 ; //we'll just use 2 because we don't have dagbestending

    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_activiteit_soort AS cas FROM cl_activiteit ca 
        JOIN cl_event_activiteit cea ON cea.activity_sysid = ca.cl_activiteit_sysid
        WHERE cea.event_id = %d", $row['pc_eid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        $cas = trim($rowe['cas']);

        // those three times for an event (direct, indirect, travel)
        $times = dt_times($row['pc_eid']);

        // SUM UP TIMES
        if ( $cas == 'Verblijfsdag' ) {
            /*if ( $ver == 1) {
                // because dagbesteding/daycare is registered in hours; not minutes
                //$totaltime += ( $times['dirtime'] * 60 ); 
            } else if ( $ver == 2 ){
                // 15 times the hours spent in dagbesteding
                //$totaltime += ( $times['dirtime'] * 15 ); 
            }*/
        } else if ( $cas == 'Tijdschrijven' ) {
            $totaltime += $times['dirtime'];
        }
    } // while

    return $totaltime;
}

//-----------------------------------------------------------------------------
/**
 * KMA003
 * 
 * total minutes total time (Sum of all direct, indirect and travel time of activities + total time dagbesteding)
 * 
 * @param none 
 * @return int
 */
function dt_kma003() {
    $r = dt_allevents();
    $totaltime = 0;
    
    //choose here what $ver we'll use function of a date
    //$dbc = content_diagnose($_SESSION['show_axid']);
    //$odate = $dbc['ax_odate']; 
    //$ver    = ( $odate <= '2007-12-31') ? 1 : 2;
    $ver = 2; // it doesn't matter now.

    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_activiteit_soort AS cas FROM cl_activiteit ca
        JOIN cl_event_activiteit cea ON cea.activity_sysid = ca.cl_activiteit_sysid
        WHERE cea.event_id = %d", $row['pc_eid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        // those three times for an event (direct, indirect, travel)
        $times = dt_times($row['pc_eid']);
        $totalt = $times['dirtime'] + $times['tratime'] + $times['indtime'];
//echo "{$rowe['cas']} TIME KMA003: $totalt <br>"; //debug
        // SUM UP TIMES
        if ( $rowe['cas'] == 'Verblijfsdag' ) {
            /*if ( $ver == 1) {
                // because dagbesteding/daycare is registered in hours; not minutes
                $totaltime += ( $times['dirtime'] * 60 ); 
            } else if ( $ver == 2 ) {
                // 15 times the hours spent in dagbesteding
                $totaltime += ( $times['dirtime'] * 15 ); 
            }*/
        } else if ( $rowe['cas'] == 'Tijdschrijven' ) {
            $totaltime += $totalt;
        }
//echo "TOTALTIME KMA003: $totaltime EV: {$row['pc_eid']}<br>"; //debug
    } // while

    return $totaltime;
}

//-----------------------------------------------------------------------------
/**
 * KMA004
 * 
 * number of separate kinds of jobs (e.g. Psychologist, psychiatrist, nurse, etc.)
 * Count how many distinct CL_BEROEPEN_CODE values are in this DBC
 * 
 * NOTE: openemr doesn't use it in 2008
 * 
 * @param none
 * @return int - distinct values for cl_beroep_code
 */
function dt_kma004() {
    $r = dt_allevents();
    $beroep = array();

    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_beroep_code FROM cl_beroep cb JOIN cl_user_beroep cub ON cb.cl_beroep_sysid = cub.cl_beroep_sysid WHERE cub.cl_beroep_userid = %d ", $row['pc_aid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        $beroep[] = $rowe['cl_beroep_code'];
    } // while

    // find and return distict values from the jobs array
    return count(array_unique($beroep));

}

//-----------------------------------------------------------------------------
/**
 * KMA005
 * 
 * number of days in clinic without stay overnight
 * Total number of CL_ACTIVITEIT_SOORT=VERBLIJFSDAG, 
 * but only for act_8.1.6, 8.2.6, 8.3.6, 8.4.6, 8.5.6, 8.6.6
 * 
 * NOTE we don't use it at this moment
 * 
 * @param none
 * @return int
 */
function dt_kma005() {
    return 0;
}

//-----------------------------------------------------------------------------
/**
 * KMT001
 * 
 * total minutes time on activity (parameter1)
 * old ver - Add up total time (direct, indirect, travel, dagbesteding) for activities that are hierarchically under   CL_ACTIVITEIT=parameter1
 * new ver - Add up total time (direct, indirect, travel, 15*dagbesteding) for activities that are hierarchically under  CL_ACTIVITEIT=parameter1, but ONLY activities with CL_ACTIVITEIT_SOORT = tijdschrijven or CL_ACTIVITEIT_SOORT =   dagbesteding
 * 
 * @param int $node - node to look parameter for
 * @return int
*/
function dt_kmt001($node) {
    global $dbcid;

    $par = dt_whatparam($node, 1);
    $r = dt_allevents();
    $totaltime = 0;
//echo "PAR KMT001: $par <br>"; //debug
    // choose here what $ver we'll use function of a date
    $dbc = content_diagnose($dbcid);
    $odate = $dbc['ax_odate']; 
    $ver    = ( $odate <= '2007-12-31') ? 1 : 2;

    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_activiteit_soort AS cas, cl_activiteit_groepcode AS cag 
        FROM cl_activiteit ca JOIN cl_event_activiteit cea ON cea.activity_sysid = ca.cl_activiteit_sysid
        WHERE cea.event_id = %d", $row['pc_eid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);
        
        $cas = trim($rowe['cas']);
        $cag = trim($rowe['cag']);

        // under the parameter
        if ( strpos($cag, $par) === 0 ) {
            // those three times for an event (direct, indirect, travel)
            $times = dt_times($row['pc_eid']);
            $totalt = $times['dirtime'] + $times['tratime'] + $times['indtime'];

            if ( $ver == 1 ) {
                $totaltime += $totalt;
            } else if ( $ver == 2 ) {
                if ( $cas == 'Tijdschrijven' || $cas == 'Dagbesteding' ) $totaltime += $totalt;
            }
        } // if

    } // while
//echo "TOTALTIME KMT001: $totaltime <br>";
    return $totaltime;
}

//-----------------------------------------------------------------------------
/**
 * KMT002
 * 
 * NOTE: openemr doesn't use it in 2008
 * 
 * @param none
 * @return int
 */
function dt_kmt002() {
    // TO BE IMPLEMENTED
}

//-----------------------------------------------------------------------------
/**
 * KMT003
 * 
 * total minutes direct time on activity (parameter1)
 * 
 * ver 1 - If CL_ACTIVITEIT_SOORT = TIJDSCHRIJVEN then sum up all DIRECT_PATIENT-GEBONDEN_TIJD. Add time (if  CL_ACTIVITEIT_SOORT = DAGBESTEDING) *60 (because dagbesteding/daycare is registered in hours; not minutes) BUT ONLY for activities that are hierarchically under parameter1 of CL_ACTIVITEIT
 * 
 * ver 2 - If CL_ACTIVITEIT_SOORT = TIJDSCHRIJVEN then sum up all DIRECT_PATIENT-GEBONDEN_TIJD. 
 * Add time (if CL_ACTIVITEIT_SOORT = DAGBESTEDING) * 15 (because dagbesteding/daycare is registered in hours; not minutes) BUT ONLY for activities that are hierarchically under parameter1 of CL_ACTIVITEIT;
 * 
 * @param int $node- node to look parameter for
 * @return int
 */
function dt_kmt003($node) {
    global $dbcid;

    $par = dt_whatparam($node, 1);
    $r = dt_allevents();
    $totaltime = 0;

    //choose here what $ver we'll use function of a date
    $dbc = content_diagnose($dbcid);
    $odate = $dbc['ax_odate']; 
    $ver    = ( $odate <= '2007-12-31') ? 1 : 2;

    while ( $row = mysql_fetch_array($r) ) {
        $qe = sprintf("SELECT cl_activiteit_soort AS cas, cl_activiteit_groepcode AS cag
        FROM cl_activiteit ca JOIN cl_event_activiteit cea ON cea.activity_sysid = ca.cl_activiteit_sysid
        WHERE cea.event_id = %d", $row['pc_eid']);
        $re = mysql_query($qe) or die(mysql_error());
        $rowe = mysql_fetch_array($re);

        $cas = trim($rowe['cas']);
        $cag = trim($rowe['cag']);

        // under the parameter
        if ( strpos($cag, $par) === 0 ) {
            // those three times for an event (direct, indirect, travel)
            $times = dt_times($row['pc_eid']);

            if ( $ver == 1 ) {
                if ( $cas == 'Tijdschrijven' ) $totaltime += $times['dirtime'];
                if ( $cas == 'Dagbesteding' )  $totaltime += ($times['dirtime'] * 15); // not used yet!
            } else if ( $ver == 2 ) {
                //if ( $cas == 'Tijdschrijven' || $cas == 'Dagbesteding' ) $totaltime += $times['dirtime'];
                if ( $cas == 'Tijdschrijven' ) $totaltime += $times['dirtime'];
                if ( $cas == 'Dagbesteding' )  $totaltime += ($times['dirtime'] * 60); // not used yet!
            }

        } // if
    } // while

    return $totaltime;
}

//-----------------------------------------------------------------------------
/**
 * KMT004
 * 
 * NOTE: openemr doesn't use it in 2008
 * 
 * @param none
 * @return void
 */
function dt_kmt004() {
    // TO BE IMPLEMENTED
}

//-----------------------------------------------------------------------------
/**
 * KMN001
 * 
 * The registered primary (main) diagnosis falls under category parameter1 (CL_DIAGNOSE)
 * 
 * Example: if the asked value of CL_DIAGNOSE_CODE is AS1_6 then all CL_DIAGNOSE_CODE 
 * values As1_6* are valid (all values in the same hierarchy)
 * 
 * @param int $node- node to look parameter for
 * @return bool 
 */
function dt_kmn001($node) {
    global $dbcid;

    $par = dt_whatparam($node, 1);
    $maindia = df_get_main_diagnose($dbcid);

    return (strpos($maindia, $par) === 0);
}

//-----------------------------------------------------------------------------
/**
 * KMP001
 * 
 * NOTE: not used by us
 * @param none
 * @return int
 */
function dt_kmp001() {
    return 0;
}

//-----------------------------------------------------------------------------
/**
 * KMC001
 * 
 * NOTE: openemr doesn't use it in 2008
 * 
 * @param none 
 * @return void
 */
function dt_kmc001() {
    // TO BE IMPLEMENTED
}


//-----------------------------------------------------------------------------
/**
 * ALL EVENTS FOR A DBC
 * 
 * between opening date and closing date
 * 
 * @param none
 * @return mysqlresult
 */
function dt_allevents() {
    global $dbcid, $enddate;

    $dia    = content_diagnose($dbcid);
    $odate  = mysql_real_escape_string($dia['ax_odate']);
    $cdate  = ( isset($_SESSION['eind']) && $_SESSION['eind'] ) ? mysql_real_escape_string($_SESSION['eind']) : $enddate;
    if ( !$cdate ) $cdate = date('Y-m-d'); //as a precaution
    $pid    = ( $dbcid ) ? what_patient($dbcid) : $_SESSION['pid'];

    // find all events from DBC opening 'till closing date
    $q = sprintf("SELECT pc_eid, pc_aid FROM openemr_postcalendar_events 
                WHERE pc_pid = %d AND pc_eventDate >= '%s' AND pc_eventDate <= '%s' AND pc_apptstatus = '@' ",
                $pid, $odate, $cdate);
    $r = mysql_query($q) or die(mysql_error());

    return $r;
}


//-----------------------------------------------------------------------------
/**
 * TIMES FOR AN EVENT
 * 
 * returns direct, indirect and travel time (all in minutes)
 * 
 * @param int $eid - event id
 * @return array
 */
function dt_times($eid = 0) {
    if ( !$eid ) return 0;

    // for JOIN - if the second table has inconsistency of data (missing record for an events)
    // a 'bug' occur and the direct time become 0
    //$qt = sprintf("SELECT ope.pc_duration, cta.indirect_time, cta.travel_time FROM openemr_postcalendar_events ope JOIN cl_time_activiteit cta ON ope.pc_eid = cta.event_id WHERE pc_eid = %d ", $eid);
    $qt = sprintf("SELECT pc_duration FROM openemr_postcalendar_events WHERE pc_eid = %d ", $eid);
    $rt = mysql_query($qt) or die(mysql_error());
    $rowt = mysql_fetch_array($rt);

    // direct time
    $rez['dirtime'] = $rowt['pc_duration'] / 60;


    $qi = sprintf("SELECT indirect_time, travel_time FROM cl_time_activiteit WHERE event_id = %d ", $eid);
    $ri = mysql_query($qi) or die(mysql_error());

    if ( mysql_num_rows($ri) ) {
        $rowi = mysql_fetch_array($ri);
        // indirect time
        $rez['indtime'] = $rowi['indirect_time'];
        // travel time
        $rez['tratime'] = $rowi['travel_time'];
    } else {
        $rez['indtime'] = $rez['tratime'] = 0;
    }

    return $rez;
}

//-----------------------------------------------------------------------------
/**
 * WHAT PRODUCTGROEP
 * 
 * after we have a result, we look up the right productgroep
 * 
 * @param int $rfsum 
 * @param string $endate - ending date for the DBC
 * @return array - two values id|name
 */
function dt_whatproductgroep($rfsum, $endate) {
    // to avoid the bug: first time, it doesn't display properly
    if ( !$endate ) {
        global $dbcid;
        $dc = content_diagnose($dbcid);
        $endate = $dc['ax_cdate'];
    }


    $q = sprintf("SELECT cl_productgroep_sysid as cps, cl_productgroep_beschrijving as cpb 
    FROM cl_productgroep WHERE cl_productgroep_code = %d 
    AND cl_productgroep_begindatum <= '%s' AND cl_productgroep_einddatum >= '%s' ", $rfsum, $endate, $endate);
    $r = mysql_query($q) or die(mysql_error());

    $row = mysql_fetch_array($r);
    $res = array();
    $res['id'] = $row['cps'];     $res['name'] = $row['cpb'];  

    return $res;
}

//-----------------------------------------------------------------------------
/**
 * SET PRESTATIECODE
 * 
 * last step in the decision tree
 * if called with $retflag = 1, just return the value
 *
 * @param int $zsysid - zorg sysid
 * @param int $pgroep - productgroep sysid
 * @param int $dbcid - dbcid
 * @return string|void
 */
function dt_prestatiecode($zsysid, $pgroep, $dbcid = 0, $retflag = 0) {
    $resstr = '';

    // ZORGTYPE ----------------
    $qz = sprintf("SELECT cl_zorgtype_prestatiecodedeel AS czp FROM cl_zorg WHERE cl_zorgtype_sysid = %d ", $zsysid);
    $rz = mysql_query($qz) or die(mysql_error());
    $rowz = mysql_fetch_array($rz);

    $czp = (int)$rowz['czp']; $czp = str_pad($czp, 3, '0', STR_PAD_LEFT);
    $resstr .= substr($rowz['czp'], 0, 3); // only 3 characters allowed

    // DIAGNOSE ----------------
    $qd = sprintf("SELECT cl_productgroep_diagnose_blinderen AS cdb, cl_productgroep_code as cpc
    FROM cl_productgroep WHERE cl_productgroep_sysid = %d ", $pgroep);
    $rd = mysql_query($qd) or die(mysql_error());
    $rod = mysql_fetch_array($rd);

    // posible values 1 - 0
    if ( $rod['cdb'] == 1 ) {
        $resstr .= '000';
    } else {
        // find CL_DIAGNOSE_PRESTATIECODEDEEL for the main diagnose
        $maind = df_get_main_diagnose($dbcid);
        $qpre = sprintf("SELECT cl_diagnose_prestatiecodedeel FROM cl_diagnose WHERE cl_diagnose_code = '%s' ",
                        $maind); 
        $rpre = mysql_query($qpre) or die(mysql_error());
        $rowpre = mysql_fetch_array($rpre);

        $resstr .= ( $rowpre['cl_diagnose_prestatiecodedeel'] ) ? $rowpre['cl_diagnose_prestatiecodedeel'] : '000' ;
    }

    // PRODUCTGROEP CODE ----------------
    $resstr .= str_pad($rod['cpc'], 6, '0', STR_PAD_LEFT);

    // save it to the table only if $dbcid is given
    // otherwise just return the value
    if ( !$retflag ) {
        $qu = sprintf("UPDATE cl_axes SET ax_pcode='%s' WHERE ax_id = %d ", $resstr, $dbcid);
        mysql_query($qu) or die(mysql_error());
    } else {
        return $resstr;
    }

}
//-----------------------------------------------------------------------------

?>