<?php
/** DBC DUTCH SYSTEM
 *  Cron only functions
 * 
 * @author Cristian NAVALICI (lemonsoftware [..] gmail [.] com)
 * @version 1.0
 */

require_once('DBC_cfunctions.php');
require_once('DBC_functions.php');
require_once('DBC_decisiontree.php');
require_once('DBC_validations.php');

dc_yearly_closing();

// ----------------------------------------------------------------------------
/**


@param 
@return 
*/
function dc_yearly_closing() {
    dc_connect();
    global $rfsum;

    // take all opened DBC
    $r = mysql_query("SELECT * FROM cl_axes ca JOIN cl_careroute_numbers ccn ON ca.ax_ztn = ccn.cn_ztn
                      WHERE ca.ax_open = 1");

    while ( $row = mysql_fetch_array($r) ) {
        $pid = $row['cn_pid'];
        $dbcid = $row['ax_id'];
        $today = date('Y-m-d');

        // and calculate the age
        $age = df_dbc_age($dbcid);
        if ( $age >= 340 ) { // MODIFICA IN 365!!!
            $rfsum = 0; // reset the rfsum!
            dt_main(1, $dbcid, $odate);
            $z = dt_whatproductgroep($rfsum, $odate);
            $pcode = $z['id'];

            $gafstr = unserialize($row['ax_as5']);
            $gaf['gaf1'] = $gafstr['gaf1'];
            $gaf['gaf2'] = preg_replace('/_01/', '_02', $gaf['gaf1']);
            $gaf['gaf3'] = preg_replace('/_01/', '_03', $gaf['gaf1']);

            $lastenc = last_encounter($pid);

            $fe = future_events($pid, $today); // any future events after today?

            $have_followup = ( $fe ) ? 1 : 0;

echo "$pid($dbcid) - $fe - LAST: {$lastenc['date']} F: $have_followup\n";
            $newid = close_dbc($have_followup, $pcode, '180101', '110003', $gaf, $dbcid, $today, $pid);

            if ( $newid ) {
                df_cronlog("DBC id: $dbcid was duplicated with DBC id: $newid ");
            } else {
                df_cronlog("DBC id: $dbcid was closed without a followup. ");
            }
        }
    }
}

//-----------------------------------------------------------------------------
/**
CONNECT TO DATABASE 

make the connections to openemr database
the variables are taken from sqlconf.php

@param none
@return void
*/
function dc_connect() {
    require_once(dirname(__FILE__) . '/sqlconf.php');
    global $host, $login, $pass, $dbase;

    $link = mysql_connect($host, $login, $pass);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }

    mysql_query("USE " . $dbase);
}
?>