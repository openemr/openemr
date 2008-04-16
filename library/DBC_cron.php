<?php
/** DBC DUTCH SYSTEM
 *  Cron only functions
 * 
 * @author Cristian NAVALICI (lemonsoftware [..] gmail [.] com)
 * @version 1.0
 */

require_once('DBC_cfunctions.php');

dc_yearly_closing();

// ----------------------------------------------------------------------------
/**


@param 
@return 
*/
function dc_yearly_closing() {
    dc_connect();

    // take all opened DBC
    $r = mysql_query("SELECT * FROM cl_axes WHERE ax_open = 1");
    while ( $row = mysql_fetch_array($r) ) {
        // and calculate the age
        $age = df_dbc_age($row['ax_id']);
        if ( $age >= 365 ) {
            $newid = duplicate_dbc($row['ax_id']);
            df_cronlog("DBC id: {$row['ax_id']} was duplicated with DBC id: $newid ");
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