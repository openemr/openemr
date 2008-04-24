<?php
/**
 * DBC DUTCH SYSTEM - DBC_FILES
 * several functions used to generate txt files for reporting
 * 
 * 
 * Cristian NAVALICI (lemonsoftware [..] gmail [.] com)
 * @version 1.0
 */

//-----------------------------------------------------------------------------
// some constants in our newly generated files
//

require_once('sql.inc');

define('DECLINST', '73730925');   // declaring institution code
define('LOCATION', '0');          // location code for declaring institution
define('TEPR', 'TEST');          // TEST or PRODuction ?
define('DBC_WORKINGDIR', $webserver_root . '/temp/dbc'); // working directory for our archive - server path w/out trailing slash
define('HOST', $host );      // mysql host
define('USER', $login );           // user for database quering
define('PASS', $pass );       // password for user
define('DATABASE', $dbase );    // database name

$pk_patients = 0;
$pk_careroutes = 0;
$pk_dbcs = 0;
$pk_diagnoses = 0;
$pk_tijdschrijven = 0;

//-----------------------------------------------------------------------------
/**
 * MAIN FUNCTION 
 * 
 * @param string $file
 * @param int $simulate - is simulation or real (the difference is that in case of
 * real, the db gets updated)
 * @return string - generated archive name
 */
function dbc_generatefile($file = 'all', $simulate = 1) {
    //db_connect();

    switch($file) {
        case 'dbctraject': gf_dbctraject(); break;
        case 'zorgtraject': gf_zorgtraject(); break;
        case 'diagnose': gf_diagnose(); break;
        case 'patient': gf_patient(); break;
        case 'empty': gf_empty(); break; 
        case 'tijdschrijven': gf_tijdschrijven(); break;
        case 'pakbon': gf_pakbon(); break;
        case 'all': gf_dbctraject(); gf_zorgtraject(); gf_diagnose(); gf_patient(); gf_tijdschrijven(); gf_empty(); break;
    }

   // final step; count the votes!
   gf_pakbon();
 
    // function to update some records (eg sti field from cl_axes AFTER generating
    // all the required files
    // THIS IS THE LAST STEP AFTER *ALL* ERRORS WERE CORRECTED!
    //if ( !$simulate ) update_db();
    if ( !$simulate ) update_db_2007();
 
    // create zip archive
    // if the directory it's not there, we'll create it
    $dbc_workingdir = TMPDIR_DBC . '/dbc';

    if ( !file_exists($dbc_workingdir) ) {
        mkdir($dbc_workingdir);
    }

    // also in pakbon.txt
    $archive_name = 'DIS_GGZ_DBC_' .TEPR. '_030_' .DECLINST. '_' .LOCATION. '_' .date('Ym'). '01.zip';
    $st = 'cd ' . $dbc_workingdir .'; zip ' .$archive_name. ' *';
    exec($st);

    return (DBC_WORKINGDIR .'/'. $archive_name);
}


//-----------------------------------------------------------------------------
/**
 * DBC_TRAJECT 
 * 
 * generate dbc_traject.txt file
 * 
 * @param none
 * @return void
 */
function gf_dbctraject() {
    dbc_message('DBCTRAJECT GENERATING');
    $file = DBC_WORKINGDIR . '/dbc_traject.txt';
    if ( !$h = fopen($file, 'wb') ) {
        echo "Cannot create file ($filename)";
        exit;
    } 

    //$q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0 and ax_open = 0");
    $q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0 and ax_open = 99");
    $r = mysql_query($q) or die(mysql_error());

    global $dbcid_arr;

    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            $dbcid_arr[] = $row['ax_id'];

            // prepare data128
            $_128   = DECLINST;
            $_129   = LOCATION;
            $_1008  = str_pad($row['ax_id'], 20, ' ');
            $_222   = ' ';
            $_997   = str_pad($row['ax_ztn'], 20, ' ');
            $_1002  = str_replace('-','', $row['ax_odate']);
            $_1003  = str_replace('-','', $row['ax_cdate']);
            $_133   = '    '; // 4 spaces

            $_1004  = str_pad(what_zorg(0, $row['ax_id']), 20, ' ', STR_PAD_RIGHT);
            fl_mb_beroep($_1004, $row['ax_odate'], $row['ax_cdate'], $row['ax_ztn']);

            $_890   = str_pad(what_circuit_new($row['ax_id']), 20, ' ', STR_PAD_RIGHT);
            $_891   = str_pad(what_reden($row['ax_id']), 20, ' ', STR_PAD_RIGHT);
            $_979   = str_pad('0',11, ' ', STR_PAD_LEFT);
            $_1064  = 'J';
            $_1254  = str_pad(what_stoornis(0, $row['ax_id']), 6, '0', STR_PAD_LEFT);

            $display = vl_validdbc_combinations($row['ax_id']);

            if ( $display ) {
                $content = $_128 . $_129 . $_1008 . $_222 . $_997 . $_1002 . $_1003 . $_133 . $_1004 . $_890 . $_891 . $_979 . $_1064 . $_1254 ."\r\n"; 
                //$content = $_128 .'|'. $_129 .'|'. $_1008 .'|'. $_222 .'|'. $_997 .'|'. $_1002 .'|'. $_1003 .'|'. $_133 .'|'. $_1004 .'|'. $_890 .'|'. $_891 .'|'. $_979 .'|'. $_1064 .'|'. $_1254 ."\r\n"; 
                $GLOBALS['pk_dbcs']++;
            } else {
                vl_log("DBC {$row['ax_id']} is not valid! Activities combinations validation failed.");
            }

            // WRITE DATA TO FILE
            if ( fwrite($h, $content) === FALSE ) {
                echo "Cannot write to file ($filename)";
                exit;
            }
        }
    }

    fclose($h);
}


//-----------------------------------------------------------------------------
/**
ZORGTRAJECT

generate zorgtraject.txt file

@param
@return
*/
function gf_zorgtraject() {
    dbc_message('ZORGTRAJECT GENERATING');

    $file = DBC_WORKINGDIR . '/zorgtraject.txt';
    if ( !$h = fopen($file, 'wb') ) {
        echo "Cannot create file ($filename)";
        exit;
    } 

    // get all ZTNs associated with processed DBCs (not sent to insurer and closed - because a ZTN can contain multiple
    // closed DBC's and just ONE opened DBC
    //$q = sprintf("SELECT * FROM cl_careroute_numbers JOIN cl_axes ON cl_axes.ax_ztn = cl_careroute_numbers.cn_ztn
    //WHERE cl_axes.ax_sti = 0 AND cl_axes.ax_open = 0");
    $q = sprintf("SELECT * FROM cl_careroute_numbers JOIN cl_axes ON cl_axes.ax_ztn = cl_careroute_numbers.cn_ztn
    WHERE cl_axes.ax_sti = 0 AND cl_axes.ax_open = 99");
    $r = mysql_query($q) or die(mysql_error());
    
    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            // prepare data
            $_1000  = DECLINST;
            $_1001  = LOCATION;
            $_1007  = str_pad($row['cn_ztn'], 20, ' ');
            $_1255  = ' '; //status flag J-for removed
            $_998   = str_replace('-','', $row['cn_dopen']);
            $_999   = ( $row['cn_dclosed'] == '9999-12-31' ) ? '        ' : str_replace('-','', $row['cn_dclosed']);
            $_1252  = str_pad(what_joining_number($row['cn_pid']), 15, ' ');
            $_1056  = '        '; // 8 spaces -  health care send that sent patient; not used for us

//            $qs = sprintf("SELECT * FROM cl_axes WHERE ax_ztn = '%s' AND ax_open = 0", $row['cn_ztn']);
            $qs = sprintf("SELECT * FROM cl_axes WHERE ax_ztn = '%s' AND ax_open = 99", $row['cn_ztn']);
            $rs = mysql_query($qs) or die(mysql_error());
            $dbc = mysql_fetch_assoc($rs);
 
            $as1 = unserialize($dbc['ax_as1']);
            $as1c = $as1['content']; $mainpos = (int)$as1['mainpos']; // mainpos is written in both places
            $as2brut = unserialize($dbc['ax_as2']); $as2 = $as2brut['content'];

            // first we look in referer table
            $qr  = sprintf("SELECT * FROM cl_referers WHERE ref_pid = %d", $row['cn_pid']);
            $rr = mysql_query($qr) or die(mysql_error());

            // referer type (optional)
            $_1057 = ''; 
            if (  mysql_num_rows($rr) )  {
                $ref_res = mysql_fetch_assoc($rr);
                $_1057 = str_pad($ref_res['ref_code'], 4, '0', STR_PAD_LEFT);
            } else {
                // then look in provider table
                $qpr  = sprintf("SELECT * FROM cl_providers WHERE pro_pid = %d", $row['cn_pid']);
                $rpr = mysql_query($qpr) or die(mysql_error());
                if ( mysql_num_rows($rpr) ) {
                    $_1057 = '0100'; //always huisarts
                } else {
                    $_1057 = '    '; // optional but must be filled with spaces
                }
            }
            $_1057 = substr($_1057, 0, 4); // assure there's only 4 chars here

            $_948   = '';
            if ( count($as1['content']) >= $mainpos) {
                $_1058 = ' ';
                $_948 = $as1['content'][$mainpos - 1];
            } else {
                $nr = $mainpos - count($as1['content']);
                $record = $as2[$nr-1];
                $_1058 = ( $record['trekken']) ? 'J' : ' ';
                $_948 = $record['code'];
            }

            $_948 = str_pad($_948, 20, ' '); 
            $_949 = str_replace('-','', $dbc['ax_odate']);

            $content = $_1000 . $_1001 . $_1007 . $_1255 . $_998 . $_999 . $_1252 . $_1056 . $_1057 . $_948 . $_1058 . $_949 ."\r\n";
            //$content = $_1000 .'|'. $_1001 .'|'. $_1007 .'|'. $_1255 .'|'. $_998 .'|'. $_999 .'|'. $_1252 .'|'. $_1056 .'|'. $_1057 .'|'. $_948 .'|'. $_1058 .'|'. $_949 ."\r\n";

            $GLOBALS['pk_careroutes']++;

            // WRITE DATA TO FILE
            if ( fwrite($h, $content) === FALSE ) {
                echo "Cannot write to file ($filename)";
                exit;
            }
        }
    }

    fclose($h);
}


//-----------------------------------------------------------------------------
/**
PATIENT 

generate patient.txt file

@param
@return
*/
function gf_patient() {
    dbc_message('PATIENT GENERATING');

    $file = DBC_WORKINGDIR . '/patient.txt';
    if ( !$h = fopen($file, 'wb') ) {
        echo "Cannot create file ($filename)";
        exit;
    } 

    // select all patients for ready-to-be-sent DBCs
    //$q = sprintf("SELECT DISTINCT cn_pid FROM cl_careroute_numbers JOIN cl_axes ON cl_axes.ax_ztn = cl_careroute_numbers.cn_ztn WHERE cl_axes.ax_sti = 0 and cl_axes.ax_open = 0");
    $q = sprintf("SELECT DISTINCT cn_pid FROM cl_careroute_numbers JOIN cl_axes ON cl_axes.ax_ztn = cl_careroute_numbers.cn_ztn WHERE cl_axes.ax_sti = 0 and cl_axes.ax_open = 99");
    $r = mysql_query($q) or die(mysql_error());

    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            // prepare data
            $infopatient = info_patient($row['cn_pid']);
            switch ((int)$infopatient['sex']) {
                case 0: $sex = 0; break; // unknown
                case 1: $sex = 1; break; // male
                case 2: $sex = 2; break; // female
                case 9: $sex = 9; break; // not specified
                default: $sex = 9;
            }

            $names  = names($row['cn_pid']);

            $_7     = DECLINST;
            $_8     = LOCATION;
            $_1250  = str_pad(what_joining_number($row['cn_pid']), 15, ' ');
            $_10    = str_pad('', 25, 'X'); // for now, we just anonimise; otherwise use $names array
            $_11    = str_pad('', 10, 'X');
            $_12    = $names['code_fpn'];
            $_13    = str_pad('', 25, 'X'); // for now, we just anonimise; otherwise use $names array
            $_14    = str_pad('', 10, 'X');
            $_15    = $names['code_lpn'];
            $_16    = str_pad('', 6, 'X');
            $_17    = substr($infopatient['postal_code'], 0, 4) . 'AA'; // anonimise postalcode (first 4 char + 2 A's)
            //$_18    = str_pad($infopatient['pdn_number'], 5, ' ', STR_PAD_LEFT); // house number
            $_18    = str_pad('1', 5, ' ', STR_PAD_LEFT);
            $_19    = str_pad('', 4, 'X');
            $_20    = substr(vk_countrycode($infopatient['country_code']), 0, 2); // assure there's only 2 chars here
            if ( !$_20 ) $_20 = 'XX'; // default value in case of blank values 
            
            $_21    = str_replace('-','', $infopatient['DOB']);
            if ( 0 == $_21 ) fl_log("Patient with ID: {$infopatient['id']} doesn't have DOB!");

            $_22    = $sex;
            $_804   = str_pad($infopatient['ss'], 9, ' ');

            $content = $_7 . $_8 . $_1250 . $_10 . $_11 . $_12 . $_13 . $_14 . $_15 . $_16 . $_17 . $_18 . $_19 . $_20 . $_21 . $_22 . $_804 . "\r\n"; 
            //$content = $_7 .'|'. $_8 .'|'. $_1250 .'|'. $_10 .'|'. $_11 .'|'. $_12 .'|'. $_13 .'|'. $_14 .'|'. $_15 .'|'. $_16 .'|'. $_17 .'|'. $_18 .'|'. $_19 .'|'. $_20 .'|'. $_21 .'|'. $_22 .'|'. $_804 . "\r\n";
            $GLOBALS['pk_patients']++; // count for pakbon 

            // WRITE DATA TO FILE
            if ( fwrite($h, $content) === FALSE ) {
                echo "Cannot write to file ($filename)";
                exit;
            } 
        }
    }

    fclose($h);
}



//-----------------------------------------------------------------------------
/**
DIAGNOSE 

@param
@return
*/
function gf_diagnose() {
    dbc_message('DIAGNOSE GENERATING');

    $file = DBC_WORKINGDIR . '/diagnose.txt';
    if ( !$h = fopen($file, 'wb') ) {
        echo "Cannot create file ($filename)";
        exit;
    } 

    //$q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0 and ax_open = 0");
    $q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0 and ax_open = 99");
    $r = mysql_query($q) or die(mysql_error());

    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            // prepare data
            $_950 = DECLINST;
            $_951 = LOCATION;

            $as1 = unserialize($row['ax_as1']);
            $as2 = unserialize($row['ax_as2']);
            $as3 = unserialize($row['ax_as3']);
            $as4 = unserialize($row['ax_as4']);
            $as5 = unserialize($row['ax_as5']);
            $as1c = $as1['content']; $mainpos = (int)$as1['mainpos']; // mainpos is written in both places
            $as2c = $as2['content'];

            $_882 = str_pad($row['ax_id'], 20, ' ');
            $_887 = str_replace('-','', $row['ax_odate']);;

            // we must avoid MAIN DIAGNOSE (mainpos); this is written in zorgtraject

            $partial_content = ''; // represents all the lines associated with a single DBC
            $counter = 1;
            $GLOBALS['pk_diagnoses'] = 0;
            
            foreach ( $as1c as $a) {
                if ( $counter != $mainpos ) {
                    $_883 = str_pad($a, 20, ' ');
                    $_885 = ' ';
                    $partial_content .= $_950 . $_951 . $_882 . $_887 . $_883 . $_885 ."\r\n";
                    //$partial_content .= $_950 .'|'. $_951 .'|'. $_882 .'|'. $_887 .'|'. $_883 .'|'. $_885 ."\r\n";
                    $GLOBALS['pk_diagnoses']++;  
                }
                $counter++;
            }

            if ( $as2c ) {
                foreach ( $as2c as $a) {
                    if ( $counter != $mainpos ) {
                        $_883 = str_pad($a['code'], 20, ' ');
                        $_885 = ( $a['trekken'] ) ? 'J' : '';
                        $partial_content .= $_950 . $_951 . $_882 . $_887 . $_883 . $_885 ."\r\n";
                        //$partial_content .= $_950 .'|'. $_951 .'|'. $_882 .'|'. $_887 .'|'. $_883 .'|'. $_885 ."\r\n";
                        $GLOBALS['pk_diagnoses']++;
                    }
                    $counter++;
                }
            } // if $as2c


            $_883 = str_pad($as3, 20, ' '); $_885 = ' ';
            $partial_content .= $_950 . $_951 . $_882 . $_887 . $_883 . $_885 ."\r\n"; 
            //$partial_content .= $_950 .'|'. $_951 .'|'. $_882 .'|'. $_887 .'|'. $_883 .'|'. $_885 ."\r\n";
            $GLOBALS['pk_diagnoses']++;

            $_883 = str_pad($as4, 20, ' '); $_885 = ' ';
            $partial_content .= $_950 . $_951 . $_882 . $_887 . $_883 . $_885 ."\r\n"; 
            //$partial_content .= $_950 .'|'. $_951 .'|'. $_882 .'|'. $_887 .'|'. $_883 .'|'. $_885 ."\r\n";
            $GLOBALS['pk_diagnoses']++;


            // if we don't have the second and last GAF, we fill them with gaf1 value
            // for official validation purposes
            if ( empty($as5['gaf2']) ) $as5['gaf2'] = $as5['gaf1'];
            if ( empty($as5['gaf3']) ) $as5['gaf3'] = $as5['gaf1'];
            foreach ( $as5 as $a) {
                $_883 = str_pad($a, 20, ' '); $_885 = ' ';
                $partial_content .= $_950 . $_951 . $_882 . $_887 . $_883 . $_885 ."\r\n";
                //$partial_content .= $_950 .'|'. $_951 .'|'. $_882 .'|'. $_887 .'|'. $_883 .'|'. $_885 ."\r\n";  
                $GLOBALS['pk_diagnoses']++;
            }

            // WRITE DATA TO FILE
            if ( fwrite($h, $partial_content) === FALSE ) {
                echo "Cannot write to file ($filename)";
                exit;
            }

       } // while
       } // if
    
    fclose($h);

}


//-----------------------------------------------------------------------------
/**
GELEVERD ZORGPROFIEL TIJDSCHRIJVEN 

@param
@return
*/
function gf_tijdschrijven() {
    dbc_message('TIJDSCHRIJVEN GENERATING');


    $file = DBC_WORKINGDIR . '/geleverd_zorgprofiel_tijdschrijven.txt';
    if ( !$h = fopen($file, 'wb') ) {
        echo "Cannot create file ($filename)";
        exit;
    } 

    $content = '';

    // for every DBC we find events associated
    //$q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0");
    //$q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0 and ax_open = 0");
    $q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0 and ax_open = 99");
    $r = mysql_query($q) or die(mysql_error());

    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            // prepare data
            $_919 = DECLINST;
            $_920 = LOCATION;
            $_921 = str_pad($row['ax_id'], 20, ' ');

            // set begin and end date(if exists - for closed but not sent DBC)
            // begin date is ax_odate only for the first DBC in ZTN
            // the followers take the beginning date from the previous DBC - closing date
            $sign = '>';
            if ( first_dbc_2($row['ax_id'], $row['ax_ztn']) ) {
                $bd_dbc = $row['ax_odate']; $sign = '>='; 
            } else {
                $bd_dbc = previous_dbc($row['ax_id'], $row['ax_ztn']); 
            }

            $ed_dbc = ( ($row['ax_cdate'] !== '0000-00-00') && (!empty($row['ax_cdate'])) ) ? $row['ax_cdate'] : date('Y-m-d');
            $pid = what_patient($row['ax_id']);

            // find all events between DBC's dates and sum up total times
            $qevent = sprintf("SELECT * FROM openemr_postcalendar_events 
            WHERE pc_pid = '%s' AND pc_eventDate $sign '%s' AND pc_eventDate <= '%s' AND pc_apptstatus = '@'",
            $pid, $bd_dbc, $ed_dbc);
            $revent = mysql_query($qevent) or die(mysql_error());

  
            // we are doing this because in the case of enabled multiple providers option,
            // there are some events duplicated but with the same content (except for providers field)
            $m_arr = array(); // array with distinct values for pc_multiple
            $revent_good = array();
            while ( $rowe = mysql_fetch_array($revent) ) {
                // MULTIPLE PROVIDERS CASE
                if ( $rowe['pc_multiple'] ) {
                    if ( !in_array($rowe['pc_multiple'], $m_arr) ) {
                        $revent_good[] = $rowe; 
                        $m_arr[] = $rowe['pc_multiple']; 
                    }
                // SINGLE PROVIDERS CASE
                } else {
                    $revent_good[] = $rowe; 
                }
            }
           
  
            // we build for every event a $content
            foreach ( $revent_good as $rg ) {
                $_922 = str_pad($rg['pc_eid'], 20, ' ');
                $_873 = str_pad(what_activity_event($rg['pc_eid']), 20, ' ');

                if ( empty($rg['pc_eventDate']) ) 
                    fl_log("Event eid = {$rg['eid']} has an empty date for pc_eventDate!");
                $_874 = str_replace('-','', $rg['pc_eventDate']);

                $_877 = str_pad(what_profession_provider($rg['pc_aid']), 20, ' ');

                if ( empty($rg['pc_duration']) ) $rg['pc_duration'] = 0;
                $_880 = str_pad($rg['pc_duration']/60, 6, ' ');

                // get indirect+travel time
                $time = what_time_event($rg['pc_eid']);
                if ( empty($time['indirect_time']) )    $time['indirect_time'] = 0;
                if ( empty($time['travel_time']) )      $time['travel_time'] = 0;
                $_954 = str_pad($time['indirect_time'], 6, ' ');
                $_955 = str_pad($time['travel_time'], 6, ' ');

                // time validation
                if ( fl_sumup_time(trim($_880), $time['indirect_time'], $time['travel_time'], $_921, $_922) ) {
                    $content .= $_919 . $_920 . $_921 . $_922 . $_873 . $_874 . $_877 . $_880 . $_954 . $_955 ."\r\n";
                    //$content .= $_919 .'|'. $_920 .'|'. $_921 .'|'. $_922 .'|'. $_873 .'|'. $_874 .'|'. $_877 .'|'. $_880 .'|'. $_954 .'|'. $_955 ."\r\n";
                    $GLOBALS['pk_tijdschrijven']++; 
                }
            } // for each
       } // while
    } // if



    // WRITE DATA TO FILE
    if ( fwrite($h, $content) === FALSE ) {
        echo "Cannot write to file ($filename)";
        exit;
    }

    fclose($h);

}

//-----------------------------------------------------------------------------
/**
PAKBON

generate pakbon.txt file

@param
@return
*/
function gf_pakbon() {
    dbc_message('PAKBON GENERATING');

    $file = DBC_WORKINGDIR . '/pakbon.txt';
    if ( !$h = fopen($file, 'wb') ) {
        echo "Cannot create file ($filename)";
        exit;
    } 

    //$q = sprintf("SELECT * FROM cl_axes WHERE ax_sti = 0");
    //$r = mysql_query($q) or die(mysql_error());

    // prepare data128
    $_996   = DECLINST;
    $_997   = LOCATION;
    $_995   = '03.0';
    $_980   = date('Ymd');
    $_981   = 'DIS_GGZ_DBC_' .TEPR. '_030_' .DECLINST. '_' .LOCATION. '_' .date('Ym'). '01.zip';

    // TESTS | PROD
    $_1233  = str_pad('OpenEMR-DBC01', 15, ' ');
    $_982   = str_pad($GLOBALS['pk_patients'], 7, ' ', STR_PAD_LEFT);
    $_1013  = str_pad($GLOBALS['pk_careroutes'], 7, ' ', STR_PAD_LEFT);
    $_987   = str_pad($GLOBALS['pk_dbcs'], 7, ' ', STR_PAD_LEFT);
    $_988   = str_pad($GLOBALS['pk_diagnoses'], 7, ' ', STR_PAD_LEFT);
    $_990   = str_pad($GLOBALS['pk_tijdschrijven'], 7, ' ', STR_PAD_LEFT);
    $_991   = str_pad(0, 7, ' ', STR_PAD_LEFT);
    $_992   = str_pad(0, 7, ' ', STR_PAD_LEFT);
    $_1234  = str_pad(0, 7, ' ', STR_PAD_LEFT);
    $_994   = str_pad(0, 7, ' ', STR_PAD_LEFT);
    $content = $_996 . $_997 . $_995 . $_980 . $_981 . $_1233 . $_982 . $_1013 . $_987 . $_988 . $_990 .
    $_991 . $_992 . $_1234 . $_994 ."\r\n"; 
    //$content = $_996 .'|'. $_997 .'|'. $_995 .'|'. $_980 .'|'. $_981 .'|'. $_1233 .'|'. $_982 .'|'. $_1013 .'|'. $_987 .'|'. $_988 .'|'. $_990 .'|'. $_991 .'|'. $_992 .'|'. $_1234 .'|'. $_994 ."\r\n"; 

    // WRITE DATA TO FILE
    if ( fwrite($h, $content) === FALSE ) {
        echo "Cannot write to file ($filename)";
        exit;
    }
    fclose($h);
}


//-----------------------------------------------------------------------------
/**
EMPTY

generate empties files

@param
@return
*/
function gf_empty() {
    // array with empties files
    $empties = array('overige_verrichting.txt', 'geleverd_zorgprofiel_verblijfsdagen.txt', 'geleverd_zorgprofiel_verrichtingen.txt',
     'geleverd_zorgprofiel_dagbesteding.txt');

    foreach ( $empties as $e ) {
        $file = DBC_WORKINGDIR . '/' . $e;
        if ( !$h = fopen($file, 'wb') ) {
            echo "Cannot create file ($filename)";
            exit;
        } 
        fclose($h);
    }

}


//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
/**
CONNECT TO DATABASE 

make the connections to openemr database

@param none
@return void
*/
function db_connect() {
    $link = mysql_connect(HOST, USER, PASS);
    if (!$link) {
        die('Could not connect: ' . mysql_error());
    }

    mysql_query("USE " . DATABASE);
}


//-----------------------------------------------------------------------------
/**
RETURN THE CODE FOR A ZORG IDENTIFIED BY SYSID

@param int sysid - zorg sysid
@param int dbc sysid - return the code for ASSOCIATED zorg with this dbc
@return string
*/
function what_zorg($id = 0, $dbc = 0) {
    if ( !$id && !$dbc) return FALSE;

    if ( $dbc ) {
        $q = sprintf("SELECT * FROM cl_zorg_dbc WHERE zd_dbc = %d ", $dbc);
        $r = mysql_query($q) or die( mysql_error() );
        $row = mysql_fetch_array($r);
        $id = (int)$row['zd_zorg']; if ( !$id ) return FALSE;
    }

    $q = sprintf("SELECT * FROM cl_zorg WHERE cl_zorgtype_sysid = %d ", $id);
    $r = mysql_query($q) or die( mysql_error() );

    if ( mysql_num_rows($r) ) {
        $row = mysql_fetch_array($r);
        return $row['cl_zorgtype_code'];
    } else {
        fl_log("DBC with ID: $id doesn't have a zorg code in cl_zorg_dbc!");
        return 0;
    }
}


//-----------------------------------------------------------------------------
/**
RETURN THE CODE FOR REDENSLUITEN

@param int $dbcid
@return int
*/
function what_reden($dbcid) {
    if ( !$dbcid ) return FALSE;

    $q = sprintf("SELECT * FROM cl_redensluiten_dbc WHERE rd_dbc = %d ", $dbcid);
    $r = mysql_query($q) or die( mysql_error() );
    
    if ( mysql_num_rows($r) ) {
        $row = mysql_fetch_array($r);
        return $row['rd_redencode'];
    } else {
        fl_log("DBC with ID: $dbcid doesn't have a redensluiten code in cl_redensluiten_dbc!");
        return 0;
    }
}


//-----------------------------------------------------------------------------
/**
RETURN THE CODE FOR CIRCUIT

NEW VERSION !!!!

@param int $ztnid - we based our search on ztn id
@return int
*/
function what_circuit_new($dbcid) {
    if ( !$dbcid ) return FALSE;

    // find patient id
    $qs = sprintf("SELECT ccd_circuitcode FROM cl_circuit_dbc WHERE ccd_dbcid = %d", $dbcid);
    $rs = mysql_query($qs) or die(mysql_error());
    $row = mysql_fetch_array($rs);
    $pid = $row['ccd_circuitcode'];

    return $pid;

}

//-----------------------------------------------------------------------------
/**
RETURN THE CODE FOR PRODUCTGROEP
    
@param int sysid - productgroep sysid
@param int dbc sysid - return the code for ASSOCIATED zorg with this dbc
@return string
*/
function what_stoornis($id = 0, $dbcid = 0) {
    if ( !$id && !$dbcid) return FALSE;

    if ( $dbcid ) {
        $q = sprintf("SELECT * FROM cl_productgroep_dbc WHERE pc_dbc = %d ", $dbcid);
        $r = mysql_query($q) or die( mysql_error() );
        $row = mysql_fetch_array($r);
        $id = (int)$row['pc_productcode']; if ( !$id ) return FALSE;
    }

    $q = sprintf("SELECT * FROM cl_productgroep WHERE cl_productgroep_sysid = %d ", $id);
    $r = mysql_query($q) or die( mysql_error() );
    $row = mysql_fetch_array($r);
    return $row['cl_productgroep_code'];
}


//-----------------------------------------------------------------------------
/**
RETURN THE UNIQUE GENERATED CODE FOR A PATIENT

return the id1250 for patient.txt file

@param int $patientid - patient id as in OpenEMR tables
@return string
*/
function what_joining_number($patientid = 0) {
    if ( !$patientid ) return FALSE;

    $patq = sprintf("SELECT * FROM cl_patient_number WHERE pn_oemrid = %d", $patientid);
    $r = mysql_query($patq) or die( mysql_error() );
    
    if ( mysql_num_rows($r) ) {
        $row = mysql_fetch_array($r);
        return $row['pn_id1250'];
    } else {
        fl_log("Patient with ID: $patientid doesn't have a unique number in cl_patient_number!");
        return 0;
    }
}

//-----------------------------------------------------------------------------
/**
RETURN ALL INFOS ABOUT A PATIENT

query patient_data original database

@param int $patientid - patient id as in OpenEMR tables
@return array
*/
function info_patient($patientid = 0) {
    if ( !$patientid ) return FALSE;

    $patq = sprintf("SELECT * FROM patient_data pd, patient_data_NL pdn JOIN patient_data_NL ON
    pdn.pdn_id = pd.id WHERE pd.id = %d", $patientid);
    $r = mysql_query($patq) or die( mysql_error() );
    $row = mysql_fetch_array($r);

    return $row;
}

//-----------------------------------------------------------------------------
/**
RETURN ACTIVITY CODE 

query cl_event_activiteit for activity id

@param int $eid - eventid
@return string
*/
function what_activity_event($eid = 0) {
    if ( !$eid ) return FALSE;

    $eidq = sprintf("SELECT cl_activiteit_code FROM cl_activiteit ca JOIN cl_event_activiteit cea 
    ON ca.cl_activiteit_sysid = cea.activity_sysid WHERE cea.event_id = %d", $eid);
    //$eidq = sprintf("SELECT activity_sysid FROM cl_event_activiteit WHERE event_id = %d", $eid);
    $r = mysql_query($eidq) or die( mysql_error() );
    if ( mysql_num_rows($r) ) {
        $row = mysql_fetch_array($r);
        //return (int)$row['activity_sysid'];
        return $row['cl_activiteit_code'];
    } else {
        fl_log("Event with eid = $eid don't have an associated activity code in cl_event_activiteit");
        return 0;
    }
}

//-----------------------------------------------------------------------------
/**
RETURN PROFESSION CODE

query cl_user_beroep for profession id

@param int $pid -  provider id
@return int
*/
function what_profession_provider($pid = 0) {
    if ( !$pid ) return FALSE;

    //$pidq = sprintf("SELECT * FROM cl_user_beroep WHERE cl_beroep_userid = %d", $pid);
    $pidq = sprintf("SELECT cl_beroep_code FROM cl_beroep cb JOIN cl_user_beroep cub 
    ON cb.cl_beroep_sysid = cub.cl_beroep_sysid WHERE cub.cl_beroep_userid = %d", $pid);
    $r = mysql_query($pidq) or die( mysql_error() );
    if ( mysql_num_rows($r) ) {
        $row = mysql_fetch_array($r);
        return $row['cl_beroep_code'];
        //return $row['cl_beroep_sysid'];
    } else {
        fl_log("Provider with $pid don't have an associated beroep (job) code in cl_user_beroep");
        return 0;
    }
}

//-----------------------------------------------------------------------------
/**
RETURN TIMES FOR AN EVENT

query cl_time_activiteit for an event

@param int $eid -  activity id
@return array
*/
function what_time_event($eid = 0) {
    if ( !$eid ) return FALSE;

    // check if this id is part of a multiple providers record
    // what is multiple key around this $eid?
    $rq = sprintf("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = %d", $eid);
    $rezq = mysql_query($rq) or die( mysql_error() );
    $rowmulti = mysql_fetch_array($rezq);

    if ( $rowmulti['pc_multiple'] ) {
        // --------------- MULTIPLE PROVIDERS CASE ----------------
        // what are all pc_eid's grouped by multiple key
        $eventsrow = array();
        $rezev = mysql_query("SELECT pc_eid FROM openemr_postcalendar_events WHERE pc_multiple = {$rowmulti['pc_multiple']}");
        while ( $row = mysql_fetch_array($rezev) ) {
            $eventsrow[] = $row['pc_eid'];
        }
    
        // we look in cl_time_activiteit for a matching record
        $timerow = '';
        foreach ( $eventsrow as $ev) {
            $time = mysql_query("SELECT * FROM cl_time_activiteit WHERE event_id = $ev");
            if ( mysql_num_rows($time) ) {
                $timeres = mysql_fetch_array($time);
                $timerow = ( $timeres ) ? $timeres : '';
            } else {
                $timerow = array('indirect_time' => 0, 'travel_time' => 0); // as a last solution, return an empty array
            }
        }
        // --------------- EOS MULTIPLE PROVIDERS CASE ----------------
    } else {
        // --------------- SINGLE PROVIDERS CASE ----------------
        $time = mysql_query("SELECT * FROM cl_time_activiteit WHERE event_id = $eid");
        if ( mysql_num_rows($time) ) {
            $timerow = mysql_fetch_array($time);
        } else {
            $timerow = array('indirect_time' => 0, 'travel_time' => 0); // as a last solution, return an empty array
        }
        // --------------- EOS SINGLE PROVIDERS CASE ----------------
    }

    return $timerow;
}


//-----------------------------------------------------------------------------
/**
UPDATE DATABASE


@param none
@return void
*/
function update_db() {
    // for every open DBC generate a duplicate one; the older one
    // will be marked as sent_to_insurer and closed

    $date = date('Y-m-d');
    mysql_query('START TRANSACTION');

    // look for openened dbc
    $q = sprintf("SELECT * FROM cl_axes WHERE ax_open = 1 AND ax_sti = 0");
    $r = mysql_query($q) or die(mysql_error());

    if ( !mysql_num_rows($r) ) return;

    while ( $row = mysql_fetch_array($r) ) {
        transform_dbc($row['ax_id']);
    } // while

    mysql_query('COMMIT');

}

//-----------------------------------------------------------------------------
/**
UPDATE DATABASE

FUNCTION ONLY FOR DIRTY TRICKS: CLOSING PER MONTHS IN 2007!!!!
DON'T USE IT IN A REGULAR PRODUCTION ENV

@param none
@return void
*/
function update_db_2007() {
    global $dbcid_arr;

    foreach ( $dbcid_arr as $dbc ) {
        $q = sprintf("UPDATE cl_axes SET ax_sti = 1, ax_open = 0 WHERE ax_id = %d", $dbc);
        echo $q . '<br />';
        mysql_query($q) or die(mysql_error());
    }
}

//-----------------------------------------------------------------------------
/**
TRANSFORM DBC

duplicate a DBC and then mark the older one with sent to insurer flag

@param int $dbc id 
@return void
*/
function transform_dbc($dbcid = 0) {

    if ( !$dbcid ) return FALSE;

    mysql_query("START TRANSACTION");

    $date = date('Y-m-d');

    // read the current dbc
    $q = sprintf("SELECT * FROM cl_axes WHERE ax_id = %d", $dbcid);
    $r = mysql_query($q) or die(mysql_error());
    $dbc = mysql_fetch_array($r);

    // duplicate it (sent to insurer flag = 0 for the new one, 1 for the older one)
    $qi = sprintf("INSERT INTO cl_axes (ax_ztn, ax_open, ax_as1, ax_as2, ax_as3, ax_as4, ax_as5, ax_odate, ax_cdate, ax_sti)
    VALUES ('%s', %d,'%s','%s','%s','%s','%s','%s','%s','%s')", $dbc['ax_ztn'], 1, $dbc['ax_as1'], $dbc['ax_as2'],
    $dbc['ax_as3'],$dbc['ax_as4'],$dbc['ax_as5'], $date, 0, 0);
    mysql_query($qi) or die (mysql_error());

    // close the old one
    $qu = sprintf("UPDATE cl_axes SET ax_open = 0, ax_cdate = '%s', ax_sti = 1 WHERE ax_id = %d", $date, $dbcid);
    mysql_query($qu) or die(mysql_error());
    
    // update the related tables (cl_circuit_dbc)
    $qc = sprintf("SELECT ccd_circuitcode FROM cl_circuit_dbc WHERE ccd_dbcid = %d ", $dbcid);
    $rc = mysql_query($qc) or die(mysql_error());
    $circuit = mysql_fetch_array($rc);
  
    mysql_query("INSERT INTO cl_circuit_dbc(ccd_circuitcode, ccd_dbcid) VALUES (%d, %d)", $circuit['ccd_circuitcode'], $dbcid);

    mysql_query("COMMIT");
}

//-----------------------------------------------------------------------------
/**
NAAMCODE

1 then naam_1 is the name of the married partner; 2 - name of the patient

@param int $pid 
@return int (1 or 2)
*/
function naamcode_1($pid = 0) {
    if (!$pid) return 0;
    
    $qp = sprintf("SELECT fname FROM patient_data WHERE id = %d ", $pid);
    $rp = mysql_query($qp) or die(mysql_error());
    $row = mysql_fetch_array($rp);
    
    return ( $row['fname'] ) ? 2 : 1;
   
}

//-----------------------------------------------------------------------------
/**
NAMES

return the names for patient
(we use patient names or if we don't have, partner name)

@param int $pid - patient id
@return array
*/
function names($pid = 0) {
    if (!$pid) return 0;

    $qp1 = sprintf("SELECT fname, lname FROM patient_data WHERE id = %d ", $pid);
    $rp1 = mysql_query($qp1) or die(mysql_error());
    $row1 = mysql_fetch_array($rp1);

    $qp2 = sprintf("SELECT pdn_pxlast, pdn_pxlastpar,pdn_lastpar FROM patient_data_NL WHERE pdn_id = %d ", $pid);
    $rp2 = mysql_query($qp2) or die(mysql_error());
    $row2 = mysql_fetch_array($rp2);

    $ret = array();
    
    // first part of the name (if we don't have it, the pad just 25X)
    // first part = firstname + lastname patient
    // second part = lastname partner (if any)
    $firstpart = $row1['fname'] .' '. $row1['lname'];
    $ret['firstpname'] = str_pad($firstpart, 25, ' ', STR_PAD_RIGHT);
    $ret['prefix_fpn'] = ( $row2['pdn_pxlast'] ) ? str_pad($row2['pdn_pxlast'], 10, ' ') : str_pad('', 10, 'X');
    $ret['code_fpn'] = '2'; // name of the patient
    
    // last part of the name (partner ones)
    if ( $row2['pdn_lastpar'] ) {
        $ret['lastpname']    = str_pad($row2['pdn_lastpar'], 25, ' ', STR_PAD_RIGHT);
        $ret['prefix_lpn']   = str_pad($row2['pdn_pxlastpar'], 10, ' ', STR_PAD_RIGHT);
        $ret['code_lpn']     = '1'; // name of the patient
    } else {
        // empty values
        $ret['lastnpame']    = str_pad('', 25, ' ', STR_PAD_RIGHT);
        $ret['prefix_lpn']   = str_pad('', 10, ' ', STR_PAD_RIGHT);
        $ret['code_lpn']     = ' '; 
    }

    return $ret;
}

//-----------------------------------------------------------------------------
/**
FIRST DBC?

find if a dbc is the first or a 'follow-up' or there is no DBC yet.
(the same function is in DBC_functions with a small modification here --> ztn arg)

@param int - ax_id - the DBC id
@param int - ztn id
@return bool | int
*/
function first_dbc_2($ax_id, $ztn_id) {
    // to be the first means there is only one DBC per open ZTN  
    $qz = sprintf("SELECT * FROM cl_axes WHERE ax_ztn='%s' AND ax_id < %d", $ztn_id, $ax_id);
    $rez = mysql_query($qz) or die(mysql_error());
    
    return ( !mysql_num_rows($rez) );
}

//-----------------------------------------------------------------------------
/**
PREVIOUS DBC

find the previous dbc for a given one and return its closing date

@param int - ax id
@param int $ztn_id - ztn id
@return string
*/
function previous_dbc($ax_id, $ztn_id) {
    $qp = sprintf("SELECT ax_cdate FROM cl_axes WHERE ax_ztn='%s' AND ax_id < %d ORDER BY ax_id DESC", $ztn_id, $ax_id);
    $rez = mysql_query($qp) or die(mysql_error());
    $r = mysql_fetch_array($rez);

    return $r['ax_cdate'];

}

// ----------------------------------------------------------------------------
/**
SIMILAR LOG FUNCTION WITH THE ONE FROM DBC_VALIDATIONS 

simple function to log different events

@param string $string
@return 
*/
function fl_log($string) {
    $file = TMPDIR_DBC . '/DBC_problems.log'; 
    if ( !$h = fopen($file, 'ab') ) {
        echo "Cannot create file ($file)";
        exit;
    }

    $content = date('d-m-Y') . " $string \r\n";

    // WRITE DATA TO FILE
    if ( fwrite($h, $content) === FALSE ) {
        echo "Cannot write to file ($file)";
        exit;
    }  

    fclose($h);
 
}

// ----------------------------------------------------------------------------
/**
VERIFY IF THE TOTAL TIME IF GREATER THAN 0
AND ALSO IF INDIRECT TIME>0 AND DIRECT TIME = 0

@param int $duration - encounter duration
@param int $indirect - indirect time
@param int $travel - travel time
@param int $axid - id for DBC
@param int $eid - event id 
@return bool
*/
function fl_sumup_time($duration, $indirect = 0, $travel = 0, $axid = 0, $eid = 0) {
    $sum = (int)$duration + (int)$indirect + (int)$travel;
    
    if ( !$sum ) {
        fl_log("DBC id:$axid EVENT: $eid has total time = 0 (E:$duration/I:$indirect/T:$travel)");
        return FALSE;
    } else if ( $indirect > 0 && ($duration == 0 && $travel == 0) ) {
        fl_log("DBC id:$axid has indirect time = $indirect but direct and travel time = 0.");
        return FALSE;
    }

    return TRUE;

}


// ----------------------------------------------------------------------------
/**
VERIFY IF THE PATIENT HAS SOME REQUIRED PROVIDERS WITH THE RIGHT JOBS

@param string $zorg
@param string $odate - opening date for dbc
@param string $cdate - closing date for dbc
@param string $ztn   - ztn id

*/
function fl_mb_beroep($zorg, $odate, $cdate, $ztn) {
    // the zorg values who'll be checked for beroep
    $checklist = array(110, 206, 116, 211);

    $zo = (int)trim($zorg);
    if ( !in_array($zo, $checklist) ) {
        return TRUE;
    } else {
        // find the id for the user
        $qu = sprintf("SELECT cn_pid FROM cl_careroute_numbers WHERE cn_ztn = '%s'", trim($ztn));
        $ru = mysql_query($qu) or die(mysql_error());
        $rowu = mysql_fetch_array($ru);
        $user = $rowu['cn_pid'];

        // find all events between the opening and closing of the DBC
        // and look for every provider's job
        $qe = sprintf("SELECT * FROM openemr_postcalendar_events WHERE pc_pid = '%s' AND pc_apptstatus = '@' 
        AND pc_eventDate >= '%s' AND pc_eventDate <= '%s' ", $user, $odate, $cdate);
        $re = mysql_query($qe) or die(mysql_error());

        $has = FALSE;
        if ( mysql_num_rows($re) ) {
            while ( $row = mysql_fetch_array($re) ) {
                $job = what_beroep($row['pc_aid'], 1);
                if ( preg_match('/^MB\./', $job) ) $has |= TRUE;
            }
        } // if num_rows

        if ( !$has ) fl_log("USER/ZTN: $user/$ztn doesn't have at least one provider with job (MB.%)");

    } // if else

//
}

// ----------------------------------------------------------------------------
/**
 * DISPLAY A MESSAGE
 * 
 * used to display messages in DBC generation phase 
 * must be called only with hardcoded (and safe) strings!
 *
 * @param string $msg
 */
function dbc_message($msg) {
    $str = "$msg <br>";
    echo $str;
}


// ----------------------------------------------------------------------------

?>