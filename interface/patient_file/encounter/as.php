<?php
include_once("../../globals.php");
include_once("../../../library/DBC_decisiontree.php");
include_once("$srcdir/sql.inc");

$val = $_POST['z'];
$level = $_POST['lvl'];
if ( $level ) echo valas($val, $level);

$reset = $_POST['reset'];
if ( $reset ) resetas($reset);

$check = $_POST['check'];
if ( $check ) checkas($check);

$rtc = $_POST['rtc'];
if ( $rtc ) rtc_dropdown();

$ein = $_POST['seteinddatum'];
if ( $ein ) setein();

if ( isset($_POST['closedbc']) ) {
  closedbc();
}

$remove_referer = $_POST['remove'];
if ( $remove_referer ) remove_referer($remove_referer);

// position for radiobuttons
if ( $_POST['posas'] ) set_main_diag($_POST['posas']);

$circuit = (int)$_POST['circuit'];
if ( $circuit ) $_SESSION['circuitcode'] = $circuit;

// check the link between the ZORG code and DOB for the patient
$ztc = $_POST['ztc'];
if ( $ztc ) validate_zorg($ztc, $_POST['odate']);

// length of the DBC in days (how many days from the opening)
if ( $_POST['len'] ) show_dbclength();


if ( $_POST['cztn'] ) closeZTN();

if ( $_POST['decision'] ) decision_tree();

//------------------------------------------------------------------------
/**
DROPDOWN

@param string $val
@param string $level
*/
function valas($val, $level) {
    mysql_query("SET NAMES utf8");

    // if is an edit, we must find the dbc opening date
    // otherwise we use today value
    if ( isset($_SESSION['show_axid']) && $_SESSION['show_axid'] ) {
        $dbc = content_diagnose($_SESSION['show_axid']); $curdate = $dbc['ax_odate'];
    } else {
        $curdate = date('Y-m-d');
    }

    $q = sprintf("SELECT * 
                    FROM cl_diagnose 
                    WHERE cl_diagnose_as = %d AND cl_diagnose_groepcode = '%s'
                    AND cl_diagnose_einddatum > '%s' AND cl_diagnose_begindatum < '%s'", 
                    $level, mysql_real_escape_string($val), $curdate, $curdate);
    $r = mysql_query($q) or die(mysql_error());
    
    $a = '<option value="0"></option>';
    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
        $a .="<option value='{$row['cl_diagnose_code']}'>{$row['cl_diagnose_element']}</option>";
        }
    } else {
        $a = '<option value="0">---- No value ----</option>';
    }
    
    return $a;
} // function

//------------------------------------------------------------------------
/**
EINDATUM

Set a session var with EINDDATUM value
check the string for the format YYYY-MM-DD

@param none
@return void
*/
function setein() {
    $eind = $_POST['seteinddatum'];
    $ard = split('-', $eind);
    $y  = $ard[0]; $m = $ard[1]; $d = $ard[2];

    if ( !checkdate($m, $d, $y) ) $rdate = date('Y-m-d');
    else $rdate = $eind;

    $_SESSION['eind'] = $eind;
}
//------------------------------------------------------------------------
/**
GENERATE RTC DROPDOWN

@param none
@return void
*/
function rtc_dropdown() {
    $rtc = reden_codes();
    $str = '<strong>DBC Reden Sluiten Code(ID891):</strong> <select name="rtc" id="rtc">';

    foreach( $rtc as $r ) {
        $display = TRUE;
        if ( ($r['cl_redensluiten_code'] == 5) && !vl_redensluiten_5() ) $display = FALSE;

        if ( $display ) {
            $str .= "<option value='{$r['cl_redensluiten_code']}'>{$r['cl_redensluiten_beschrijving']}</option>";
        }
    }// foreach

    $str .= "</select>";

    echo $str;

}

//------------------------------------------------------------------------
/**
CHECK AS

check the DSM-IV for all axes (must be completed)
there is one exception: if (age < 4) for the patient, AS 5 is not required.

@param string $checkas
@return void
*/
function checkas($check) {

// check if all axes are completed
if ( $check ) {

    // we consider TRUE; one value of FALSE and the result will be FALSE
    $_SESSION['save'] = TRUE;

    for ($i = 1; $i <= 4; $i++) {
        if ( isset($_SESSION["as$i"]) && count($_SESSION["as$i"]) ) {
            $_SESSION['save']  &= TRUE;
        } else {
            $_SESSION['save'] &= FALSE;
        }
    } // for

    // check for AS5
    if ( patient_age($_SESSION['pid']) >= 4 ) {
        if ( isset($_SESSION["as5"]) && count($_SESSION["as5"]) ) {
            $_SESSION['save']  &= TRUE;
        } else {
            $_SESSION['save'] &= FALSE;
        }
    }

    // usually, we can't encounter this situation but we'll double check
    // ztn_status = 3 means this is a follow up dbc and cannot be modified (!)
    // how you get here?
    if ( $_SESSION['save'] && (ztn_status() != 3) ) {
        $a = save_dbc();
        $_SESSION['save'] = FALSE;
        if ( $a ) echo 'closewindow';
    } else {
        echo '<font color="red"><strong>De diagnose is niet volledig. Controleer of op alle 5 assen tenminste n diagnose is ingevuld.</strong></font>';
    }
} //  check

} // f checkas



//------------------------------------------------------------------------
/**
RESET SESSION VARS FOR AXES

@param string $reset

*/
function resetas( $reset ) {
// reseting sessions variable
// for differents axes
  switch ($reset) {
    case 'as1' :   unset($_SESSION['as1']); break;
    case 'as2' :   unset ($_SESSION['as2']); break;
    case 'as3' :   unset($_SESSION['as3']); break;
    case 'as4' :   unset($_SESSION['as4']); break; 
    case 'as5' :   unset($_SESSION['as5']); break;
  }
} // function

//------------------------------------------------------------------------
/**
REMOVE REFERER FOR A PATIENT

@param int $pid patient id
*/
function remove_referer($pid) {
  if ( !$pid ) return;
  $qd = sprintf('DELETE FROM cl_referers WHERE ref_pid = %d', $pid);
  mysql_query($qd) or die(mysql_error());
  
  // update the field 'has referer' from cl_providers
  $qu = sprintf("UPDATE cl_providers SET pro_referer = 1 WHERE pro_pid = %d", $pid);
  mysql_query($qu) or die(mysql_error());
}


//------------------------------------------------------------------------
/**
ZTC VALIDATION AGAINST AGE

This function is used for REALTIME validation (dropdown-ajax)
It just calls the validation function and echo a message.
Patient id is taken from $_SESSION.

@param int $ztc - zorg code
@param string $odate (YYYYMMDD)
*/
function validate_zorg($ztc, $odate) {
    $rez = vl_ztc_age($ztc, $odate, $_SESSION['pid']);
    if ( !$rez ) echo 'The patient was not < 18 yr old at the date of DBC opening.'; else echo ' ';
}

//------------------------------------------------------------------------
/**
SET MAIN DIAGNOSE

check if the main diag is NOT in a forbidden list
(also a check in DBC_validations.php)

@param $pos - position for main diagnose in the full array of diagnoses
@return 
*/
function set_main_diag($pos) {
    $forbidden_list = array('as1_18.02', 'as1_18.03', 'as2_18.02', 'as2_18.03', 'as2_17.01', 'as2_01.01.01',
                            'as2_01.01.02', 'as2_01.01.03', 'as2_01.01.04', 'as2_01.01.05');

    $alldiag = array();
    if ( count($_SESSION['as1']) ) {
        foreach ( $_SESSION['as1'] as $as1 ) {
            $alldiag[] = $as1;
        }
    }
    if ( count($_SESSION['as2']) ) {
        foreach ( $_SESSION['as2'] as $as2 ) {
            $alldiag[] = $as2['code'];
        }
    }

    $pos = (int)$pos; $needle = $alldiag[$pos-1];
    $result = in_array($needle, $forbidden_list); //if true, it's forbidden

    if ( $result ) {
        echo 'Forbidden value for main diagnose!';
        return FALSE;
    } else {
        // setup a session variable
        $_SESSION['posas'] = $pos; 
        echo '[OK] Main diagnose set.';
    }
}

//------------------------------------------------------------------------
/**
DBC LENGTH 

display the length of the dbc

@param int $ztc - zorg code
@param string $odate (YYYYMMDD)
*/
function show_dbclength() {
    if ( !$_SESSION['eind']) $string = 'No einddatum set yet.';
    else {
        $length = df_dbc_age($_SESSION['show_axid'], $_SESSION['eind']);
        $string = 'DBC Length ' .$length. ' days.';
        if ( $length > 29) $string .= ' You are not allowed to choose zorgtype = "Eenmalig spoedeisend consult/crisisinterventie" ';
    }
    echo $string;
}


//------------------------------------------------------------------------
/**
CLOSE A ZTN

it's used to close a ztn (w/out any open DBC)

@param none
@return void
*/
function closeZTN() {
    $val = ztn_status();
    $openztn = verify_ztn();
    if ( $val != 2 && $val != 0 ) {
        $qd = sprintf("UPDATE cl_careroute_numbers SET cn_open = 0, cn_dclosed = '%s' WHERE cn_ztn = '%s' ",
                       date('Y-m-d'), $openztn);
        mysql_query($qd) or die(mysql_error());
    }
}

//------------------------------------------------------------------------
/**
DISPLAY THE RESULT OF THE DECISION TREE ALGORITHM

@param none
@return void
*/
function decision_tree() {
   dt_main(); //echo $a;
}
//------------------------------------------------------------------------
?>