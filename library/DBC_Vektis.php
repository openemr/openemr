<?php
/** 
 * DBC DUTCH SYSTEM
 * VEKTIS SYSTEM
 * 
 * @author Cristian NAVALICI
 * @version 1.0 feb 2008
 *
 * CAN BE RUN ONLY FROM THE INTERFACE
 * NEEDS GLOBALS.PHP BEFORE
 */

require_once('patient.inc');
require_once('DBC_Vektis_constants.php');

$dbcidglobal    = 0;
$patientglobal  = 0;
$_407global     = 1;
$filepointer    = NULL;
$lines2         = 0; // total records for part 2
$lines3         = 0; // total records for part 3
$lines4         = 0; // total records for part 4
$totalclaim     = 0; // total insurance claim
$uniqueid       = 0; // the id for lines 202,402,etc
$arr_database   = array(); // array with all the values which will be saved into a table
$filename       = ''; // filename used to write/read vektis stuff (with full path)

//-----------------------------------------------------------------------------
/**
 * MAIN FUNCTION
 * 
 * generates the lines for every DBC
 * 
 * @param int $dbcid
 * @return 
 */
function vk_main($dbcid) {
    // set some global variables first
    global $dbcidglobal;
    $dbcidglobal = (int)$dbcid;

    global $patientglobal;
    $patientglobal = what_patient($dbcid);

    $lines = array();
    $lines[]  = vk_generate_insurer();
    //$lines[]  = vk_generate_debtor();
    $lines[]  = vk_generate_service();

    //$line6  = vk_generate_closing() . VK_EOF;

    foreach ( $lines as $ln ) {
        $line = $ln."\r\n";
        vk_write_file($line);
    }

}

//-----------------------------------------------------------------------------
/**
 * INITIALIZATION FUNCTION  
 * 
 * must be called BEFORE any other function around here
 * 
 * @param none
 * @return void
 */
function vk_init() {
    $working_dir = TMPDIR_DBC . '/vektis';

    // if it's not there, we'll create it
    if ( !file_exists($working_dir) ) {
        mkdir($working_dir);
    }

    // generate the filename and attempt to create it
    global $filename;
    $filen       = C112.date('dmY').'.txt';
    $filename    = TMPDIR_DBC .'/vektis/'. $filen;
    $fp = fopen($filename, "wb");
    
    global $filepointer, $lines2, $lines3, $lines4, $totalclaim, $uniqueid, $arr_database;
    $filepointer = $fp; 
    $lines2 = 0; $lines3 = 0; $lines4 = 0; $totalclaim = 0; $uniqueid = 0;
    $arr_database = array();

    // generate an unique ID for this vektis session
    $arr_database['cvd_session'] = vk_vektis_session();
}


//-----------------------------------------------------------------------------
/**
 * FINALIZATION FUNCTION  
 * 
 * must be called AFTER any other function around here
 * 
 * @param none
 * @return void
 */
function vk_last() {
    global $filepointer;
    if ( $filepointer ) fclose($filepointer);

    global $arr_database;
    if ( $arr_database ) $_SESSION['arrdb'] = $arr_database;
}

//-----------------------------------------------------------------------------
/**
 * GENERATE PREAMBLE  
 * 
 * line 01
 * this appears only one time / generated file (the same as closing line)
 * 
 * @param none
 * @return string
 */
function vk_generate_preamble() {
    // 0101	ATTRIBUTE RECORD
    $_101 = '01';

    // 0102	CODE EXTERNAL INTEGRATION MESSAGE
    $_102 = C102;

    // 0103	VERSION NUMBER MESSAGE STANDARD
    $_103 = C103;

    // 0104	SUB-VERSION NUMBER MESSAGE STANDARD
    $_104 = C104;

    // 0105	MESSAGE TYPE
    $_105 = C105;

    // 0106	CODE INFORMATION SYSTEM SOFTWARE VENDOR
    $_106 = C106;

    // 0107	VERSION INDICATION INFORMATION SYSTEM SOFTWARE VENDOR
    $_107 = str_pad(C107, 10, ' ', STR_PAD_LEFT);

    // 0108	UZOVI-NUMBER
    $_108 = str_repeat('0', 4); // 4 chars empty string

    // 0109	CODE SERVICE OFFICE
    $_109 = C109;

    // 0110	HEALTHCARE PROVIDER CODE
    $_110 = str_repeat('0', 8); // 8 chars empty string

    // 0111	PRAKTIJKCODE
    $_111 = str_repeat('0', 8); // 8 chars empty string

    // 0112	Instititution code
    $_112 = C112;

    // 0113	IDENTIFYING CODE PAYMENT TO
    $_113 = C113;

    // 0114	START DATE CLAIM PERIOD
    $_114 = date('Ymd', strtotime(date('Ymd')) - (24 * 3600)); // one day before today

    // 0115	END DATE CLAIM PERIOD
    $_115 = date('Ymd'); // today

    // 0116	INVOICE NUMBER PARTICIPANT
    $_116 = vk_invoice_number();
    global $arr_database;
    $arr_database['cvd_116'] = $_116;

    // 0117	INVOICE DATE
    $_117 = date('Ymd'); // today
    $arr_database['cvd_date'] = $_117;


    // 0118	VAT-IDENTIFICATION NUMBER
    $_118 = str_repeat(' ', 14); // 14 chars empty string

    // 0119	CURRENCY CODE
    $_119 = C119;

    // 0180	RESERVE
    $_180 = str_repeat(' ', 193); // 193 chars empty string

    // concatenate the big string
    $bigline = '';
    for ( $i = 1 ; $i <= 19 ; $i++ ) {
        $avar = sprintf('_1%02s', $i);
        $bigline .= ${$avar};
    }

    $bigline .= $_180 . "\r\n";

    vk_write_file($bigline); // this is written just once per file

}

//-----------------------------------------------------------------------------
/**
 * GENERATE INSURER RECORD 
 * 
 * line 02
 * 
 * @param none
 * @return void
 */
function vk_generate_insurer() {
    // 0201	ATTRIBUTE RECORD
    $_201 = '02';

    // 0202	IDENTIFICATION DETAIL RECORD
    $_202 = vk_unique_line_record();

    // 0203	CIVILIAN SERVICE NUMBER (BSN) INSURED
    $_203 = vk_patientdata('ssn');

    // 0204	UZOVI-NUMBER
    $_204 = vk_whatinsurance(1);
    global $arr_database;
    $arr_database['cvd_uzovi'][] = $_204;

    // 0205	INSURED NUMBER (REGISTRATION NUMBER, RELATION NUMBER)
    $_205 = vk_whatinsurance(2);

    // 0206	PATIENT (IDENTIFICATION) NUMBER
    $_206 = vk_patientdata('pid');
    $arr_database['cvd_pid'][] = $_206;

    // 0207	DATE OF BIRTH INSURED
    $_207 = vk_patientdata('dob');

    // 0208	GENDER CODE INSURED
    $_208 = vk_patientdata('sex');

    // 0209	NAME CODE/NAME USAGE (01)
    $_209 = vk_patientdata('namecode_patient');

    // 0210	NAME INSURED (01)
    $_210 = vk_patientdata('namepat');

    // 0211	PREFIX INSURED (01)
    $_211 = vk_patientdata('pxnamepat');

    // 0212	NAME CODE/NAME USAGE (02)
    $_212 = vk_patientdata('namecode_partner');

    // 0213	NAME INSURED (02)
    $_213 = vk_patientdata('namepar');

    // 0214	PREFIX INSURED (02)
    $_214 = vk_patientdata('pxnamepar');

    // 0215	INITIALS INSURED
    $_215 = vk_patientdata('initials');

    // 0216	NAME CODE/NAME USAGE (03)
    $_216 = vk_patientdata('nameusage');

    //0217	POSTAL CODE (HOME ADDRESS) INSURED
    $_217 = vk_patientdata('postalcode');

    //0218	FOREIGN POSTAL CODE
    $_218 = vk_patientdata('foreignpostalcode');

    // 0219	STREET NUMBER (HOME ADDRESS) INSURED
    $_219 = vk_patientdata('stnumber');

    // 0220	STREET NUMBER AFFIX (HOME ADDRESS) INSURED
    $_220 = vk_patientdata('stnumberaffix');

    // 0221	COUNTRY CODE INSURED
    $_221 = vk_patientdata('countrycode');

    // 0222	DEBTOR NUMBER
    $_222 = str_pad(C222, 11, ' ', STR_PAD_LEFT);

    // 0223	INDICATION CUSTOMER DECEASED
    $_223 = '2';

    // 0280	RESERVE
    $_280 = str_repeat(' ', 129); // 129 chars empty string

    // concatenate the big string
    $bigline = '';
    for ( $i = 1 ; $i <= 23 ; $i++ ) {
        $avar = sprintf('_2%02s', $i);
        $bigline .= ${$avar};
        //echo "LEN $avar " .mb_strlen(${$avar},'UTF-8') . '<br />';
    }

    $bigline .= $_280;

    // count the total lines
    global $lines2;
    $lines2++;

    return $bigline;
}


//-----------------------------------------------------------------------------
/**
 * GENERATE DEBTOR RECORD
 * 
 * line 03
 * NOTE: we don't use it yet!
 * 
 * @param none
 * @return string
 */
function vk_generate_debtor() {
    //0301	ATTRIBUTE RECORD
    $_301 = '03';

    // the total lenght for this line is 310
    $line = str_repeat(' ', 308); 

    // count the total lines
    global $lines3;
    $lines3++;

    $bigline = $_301 . $line;

    return $bigline;
}


//-----------------------------------------------------------------------------
/**
 * GENERATE SERVICE RECORD
 * 
 * line 04
 * 
 * @param none
 * @return string
 */
function vk_generate_service() {
    // 0401	ATTRIBUTE RECORD
    $_401 = '04';

    // 0402	IDENTIFICATION DETAIL RECORD
    $_402 = vk_unique_line_record();

    // 0403	CIVILIAN SERVICE NUMBER (BSN) INSURED
    $_403 = vk_patientdata('ssn');

    // 0404	UZOVI-NUMBER
    $_404 = vk_whatinsurance(1);

    // 0405	INSURED NUMBER (REGISTRATION NUMBER, RELATION NUMBER)
    $_405 = vk_whatinsurance(2);

    // 0406	AUTOMATIC PAYMENT NUMBER
    $_406 = str_repeat(' ', 15);

    // 0407	FORWARDING ALLOWED
    $_407 = C407;

    // 0408	INDICATION SERVICE CODE LIST
    $_408 = C408;

    // 0409	CLAIM CODE
    $_409 = vk_claimcode('declaratie');

    // 0410     START DATE
    $_410 = vk_dbcdates(1);

    // 0411	END DATE
    $_411 = vk_dbcdates(2);

    // 0412	DBC SERVICE CODE
    $_412 = vk_claimcode('prestatie');

    // 0413	NUMBER OF SERVICES PERFORMED
    $_413 = C413;

    // 0414	HEALTHCARE TRAJECTORY NUMBER
    $_414 = vk_dbcinfo('ztn');
    global $arr_database, $dbcidglobal;
    $arr_database['cvd_ztn'][] = $_414;
    $arr_database['cvd_dbcid'][] = $dbcidglobal; // we save also dbc id for db

    // 0415	TYPE OF SPECIALIST
    $_415 = C415;

    // 0416	HEALTHCARE PROVIDER CODE
    $_416 = C112; // agb code

    // 0417	CALCULATION PERCENTAGE
    $_417 = C417;

    // 0418	TARIFF DBC/SERVICE (VAT INCL.)
    $_418 = vk_dbcinfo('tariff');

    if ( vk_is_overloop_dbc($dbcidglobal) ) {
        // 0419	DEDUCTION TYPE
        $_419 = '04';

        // 0420	DEDUCTION AMOUNT
        $dedamo = vk_deduction($dbcidglobal);
        $_420 = str_pad($dedamo, 8, '0', STR_PAD_LEFT);
    } else {
        // 0419	DEDUCTION TYPE
        $_419 = C419;

        // 0420	DEDUCTION AMOUNT
        $_420 = str_pad(C420, 8, '0', STR_PAD_LEFT);

    }

    // 0421	AMOUNT CHARGED (VAT INCL.)
    $_421i = round (((int)$_418 * $_417) / 10000);
    $_421 = str_pad($_421i, 8, '0', STR_PAD_LEFT);

    // 0422	INDICATION DEBIT/CREDIT (01)
    $_422 = C422;

    // 0423	VAT-PERCENTAGE CLAIM AMOUNT
    $_423 = str_pad(C423, 4, '0', STR_PAD_LEFT);

    // 0424	CLAIM AMOUNT (VAT INCL.)
    $_424i = $_421i - $dedamo;
    if ( $_424i < 0 ) $_424i = 0;
    $arr_database['cvd_tariff'][] = $_424i;
    $_424 = str_pad($_424i, 8, '0', STR_PAD_LEFT);

    // 0425	INDICATION DEBIT/CREDIT (02)
    $_425 = C422;

    // 0426	REFERENCE NUMBER THIS SERVICE RECORD
    $_426 = vk_invoice_service();
    $arr_database['cvd_426'][] = $_426;

    // 0427	REFERENCE NUMBER PRECEDING RELATED SERVICE RECORD
    $_427 = str_pad(C427, 20, ' ', STR_PAD_LEFT);

    // 0480	RESERVE
    $_480 = str_repeat(' ', 94); // 94 chars empty string

    // concatenate the big string
    $bigline = '';
    for ( $i = 1 ; $i <= 27 ; $i++ ) {
        $avar = sprintf('_4%02s', $i);
        $bigline .= ${$avar};
    }

    $bigline .= $_480;

    // count the total lines
    global $lines4, $totalclaim;
    $lines4++;
    $totalclaim += $_424i;

    return $bigline;
}


//-----------------------------------------------------------------------------
/**
 * GENERATE COMMENT
 * 
 * line 05
 * NOTE: we don't use it yet!
 * 
 * @param none
 * @return string
*/
function vk_generate_comment() {
    //9801	ATTRIBUTE RECORD
    $_9801 = '98';

    // the total lenght for this line is 310
    $line = str_repeat(' ', 308); 

    $bigline = $_9801 . $line;

    return $bigline;
}


//-----------------------------------------------------------------------------
/**
 * GENERATE CLOSING RECORD
 * 
 * line 06
 * 
 * @param none
 * @return string
 */
function vk_generate_closing() {
    global $lines2, $lines3, $lines4, $totalclaim;

    //9901	ATTRIBUTE RECORD
    $_9901 = '99';

    //9902	NUMBER OF INSURED RECORDS
    $_9902 = str_pad($lines2, 6, '0', STR_PAD_LEFT);

    //9903	NUMBER OF DEBTOR RECORDS
    $_9903 = str_pad($lines3, 6, '0', STR_PAD_LEFT);

    //9904	NUMBER OF SERVICE RECORDS
    $_9904 = str_pad($lines4, 6, '0', STR_PAD_LEFT);

    //9905	NUMBER OF COMMENT RECORDS
    $_9905 = '000000';

    //9906	TOTAL NUMBER OF DETAIL RECORDS
    $sumrow = (int)($_9902 + $_9903 + $_9904 + $_9905);
    $_9906 = str_pad($sumrow, 7, '0', STR_PAD_LEFT);

    //9907	TOTAL CLAIM AMOUNT
    $_9907 = str_pad($totalclaim, 11, '0', STR_PAD_LEFT);

    //9908	INDICATION DEBIT/CREDIT
    $_9908 = 'D';

    // 9980	RESERVE
    $_9980 = str_repeat(' ', 265); // 265 chars empty string 

    // concatenate the big string
    $bigline = '';
    for ( $i = 1 ; $i <= 8 ; $i++ ) {
        $avar = sprintf('_99%02s', $i);
        $bigline .= ${$avar};
    }

    $bigline .= $_9980. "\r\n";
    vk_write_file($bigline); // this is written just once per file
}




//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
/**
 * FIND UZOVI CODE / POLICY NUMBER FOR THIS DBC
 * 
 * @param int $uzflag = 1 returns uzovi code; $uzflag != 1 returns policy code
 * @return string
 */
function vk_whatinsurance($uzflag = 1) {
    global $patientglobal;
    $insurer = get_insurers_nl($patientglobal, 1);
    $uzovi   = $insurer['pin_provider'];
    $policy  = $insurer['pin_policy'];

    if ( !$uzovi ) {
        global $_407global;
        $_407global = 2;
        $policy = '';
    }

    $retval   = ( $uzflag == 1 ) ? str_pad($uzovi, 4, '0', STR_PAD_LEFT) : str_pad($policy, 15, ' ', STR_PAD_LEFT);
    return $retval;
}


//-----------------------------------------------------------------------------
/**
 * GENERATE INVOICE NUMBER
 * 
 * unique invoice number format YYYYMMDD0001
 * It permits max 9999 invoice / day !
 * 
 * @param none
 * @return string
 */
function vk_invoice_number() {
    $date = date('Ymd');

    // retrieve auxiliary values
    $rd = mysql_query("SELECT * FROM cl_aux WHERE aux_id = 'vk_0116_invoice'") or die(mysql_error());
    $rez = mysql_fetch_array($rd);

    // if date strings are NOT equal, then we must update the date field with today value
    // and reset the counter (aux_varn) to 0
    if ( $date !== trim($rez['aux_varc']) ) {
        $current_number = 1;
    } else {
        // we are in the same day, so we'll use the value from aux_varn (numeric)
        $current_number = $rez['aux_varn'] + 1;
    } // if else

    // update the auxiliary tables with the new values
    $qu = sprintf("UPDATE cl_aux SET aux_varc = '%s', aux_varn = %d WHERE aux_id = 'vk_0116_invoice'", $date, $current_number);
    mysql_query($qu) or die(mysql_error());

    $uniqID = $date . str_pad($current_number, 4, '0', STR_PAD_LEFT);
    return $uniqID;
}

//-----------------------------------------------------------------------------
/**
 * GENERATE INVOICE NUMBER FOR EVERY DBC (SERVICE)
 * 
 * unique invoice number format 253-0001 (patient id from openemr + unique number)
 * the last number is kept into auxiliary table
 * 
 * @param none
 * @return string
 */
function vk_invoice_service() {
    global $patientglobal;

    // retrieve auxiliary values
    $rd = mysql_query("SELECT * FROM cl_aux WHERE aux_id = 'vk_0426_invoice'") or die(mysql_error());
    $rez = mysql_fetch_array($rd);

    $uninum = $rez['aux_varn'] + 1;

    // update the auxiliary tables with the new values
    $qu = sprintf("UPDATE cl_aux SET aux_varn = %d WHERE aux_id = 'vk_0426_invoice'", $uninum);
    mysql_query($qu) or die(mysql_error());

    $invoice_service = sprintf("%s-%04s", $patientglobal, $uninum);

    // padding as required
    return str_pad($invoice_service, 20, ' ', STR_PAD_LEFT);
}

//-----------------------------------------------------------------------------
/**
 * GENERATE LINE IDENTIFICATION RECORD
 * 
 * unique number for every generated line
 * begins with 1 and the add 1 unit at a time
 * 
 * @param none
 * @return string
 */
function vk_unique_line_record() {
    global $uniqueid;

    // add one unit to the global var
    $uninum = $uniqueid + 1;

    // update the global var with the new value
    $uniqueid = $uninum;

    // padding as required
    $id = str_pad($uninum, 12, '0', STR_PAD_LEFT);
    
    return $id;
}


//-----------------------------------------------------------------------------
/**
 * GET PATIENT DATA
 * 
 * returns different informations about patient
 * it works with multibyte strings, so mb extension must be enabled!
 * 
 * @param string $param
 * @return string
 */
function vk_patientdata($param = 'pid') {
    mb_internal_encoding('UTF-8');

    global $patientglobal;
    $pat    = getPatientData($patientglobal);
    $patnl  = getPatientDataNL($patientglobal);

    switch ( $param ) {
        case 'pid': $retval = str_pad($patientglobal, 11, ' ', STR_PAD_RIGHT); break;
        case 'ssn': $retval = ( $pat['ss'] ) ? str_pad($pat['ss'], 9, '0', STR_PAD_LEFT) : '999999999'; break; 
        case 'dob': $retval = str_replace('-', '', $pat['DOB']); break;

        case 'sex': if ( $pat['sex'] == 'Male' ) $retval = 1; else if ( $pat['sex'] == 'Female' ) $retval = 2; 
                    else $retval = 1; break;

        case 'namecode_patient':
            // there are four codes but we use only 1-3. no 4
            /*$pname      = $patnl['pdn_pxlast'] .' '. $pat['lname']; // patient's name
            $parname    = $patnl['pdn_pxlastpar'] .' '. $patnl['pdn_lastpar']; // partner's name
            $pname = trim($pname); $parname = trim($parname);

            if ( $pname && $parname) $code = 3;
            else if ( $pname && !$parname) $code = 1;
            else if ( !$pname && $parname ) $code = 2; 
            $retval = $code;*/
            $retval = 1;
        break;

        case 'namepat': 
            $inival = mb_strpad($pat['lname'], 25, ' ', STR_PAD_RIGHT); 
            $retval = mb_strtoupper($inival, 'UTF-8');
        break;
        case 'pxnamepat': 
            $inival = str_pad($patnl['pdn_pxlast'], 10, ' ', STR_PAD_RIGHT); 
            $retval = mb_strtoupper($inival, 'UTF-8');
        break;

        case 'namecode_partner':
            $par = trim($patnl['pdn_lastpar']);
            return ( $par ? 2 : 0 );
        break;

        case 'nameusage':
            $_209 = 1 ; // for us always 1 otherwise vk_patientdata('namecode_patient');
            $_212 = vk_patientdata('namecode_partner');
            $retval = ( $_212 ) ? 3 : 0;
        break;

        case 'namepar': 
            $inival = mb_strpad($patnl['pdn_lastpar'], 25, ' ', STR_PAD_RIGHT); 
            $retval = mb_strtoupper($inival, 'UTF-8');
        break;
        case 'pxnamepar': 
            $inival = str_pad($patnl['pdn_pxlastpar'], 10, ' ', STR_PAD_RIGHT); 
            $retval = mb_strtoupper($inival, 'UTF-8');
        break;

        case 'initials': 
            $ini     = trim($patnl['pdn_initials']);
            $ini_new = ( $ini ) ? preg_replace('/[^a-zA-Z]*/','', $ini) : ''; 
            $retval  = str_pad($ini_new, 6, ' ', STR_PAD_RIGHT);
        break;

        case 'postalcode':
            // check for country code
            $pcode = ( $pat['country_code'] == CNLCODE ) ? $pat['postal_code'] : '' ;
            $retval = str_pad($pcode, 6, ' ', STR_PAD_LEFT);
        break;
        case 'foreignpostalcode':
            // check for country code
            $pcode = ( $pat['country_code'] != CNLCODE ) ? $pat['postal_code'] : '' ;
            $retval = str_pad($pcode, 9, ' ', STR_PAD_LEFT);
        break;

        case 'stnumber': $retval = str_pad($patnl['pdn_number'], 5, '0', STR_PAD_LEFT); break;
        case 'stnumberaffix': $retval = str_pad($patnl['pdn_addition'], 6, ' ', STR_PAD_LEFT); break;

        case 'countrycode': 
            $ccode = ( $pat['country_code'] != CNLCODE ) ? $pat['country_code'] : '';
            $twocode =  ( $ccode ) ? vk_countrycode($ccode) : '';
            $retval = str_pad($twocode, 2, ' ', STR_PAD_LEFT); 
        break;
    }

    return $retval;

}

//-----------------------------------------------------------------------------
/**
 * FIND THE ISOCODE FOR A COUNTRY CODE
 * 
 * the two digits code
 * 
 * @param int $cid - country id
 * @return string
 */
function vk_countrycode($cid) {
    $q = sprintf("SELECT * FROM geo_country_reference WHERE countries_id = %d ", $cid);
    $r = mysql_query($q);
    $row = mysql_fetch_array($r);
    return $row['countries_iso_code_2'];
}

//-----------------------------------------------------------------------------
/**
 * PRESTATIE CODE TABLE
 * 
 * get values from it
 * 
 * @param string $flag prestatie - cl_dbc_prestatiecode | declaratie for cl_declaratiecode
 * @return string
 */
function vk_claimcode($flag = 'prestatie') {
    global $dbcidglobal;
    $cdbc = content_diagnose($dbcidglobal);
    $pcode = $cdbc['ax_pcode'];
    $cdate = $cdbc['ax_cdate'];

    switch ( $flag ) {
        case 'prestatie': $retval = str_pad($pcode, 12, ' ', STR_PAD_LEFT); break; 
        case 'declaratie': 
            $q = sprintf("SELECT cl_declaratiecode AS cd FROM cl_prestatiecode WHERE cl_dbc_prestatiecode = '%s'
            AND cl_prestatiecode_begindatum <= '%s' AND cl_prestatiecode_einddatum >= '%s' ", $pcode, $cdate, $cdate);
            $r = mysql_query($q) or die(mysql_error()); 

            if ( $row = mysql_fetch_array($r) ) $retval = str_pad($row['cd'], 6, ' ', STR_PAD_LEFT);
            else $retval = str_pad('', 6, ' ', STR_PAD_LEFT);
        break;
    }

    return $retval;   
}

//-----------------------------------------------------------------------------
/**
 * DBC DATES
 * 
 * return the start/end dates for a DBC
 * 
 * @param int $flag 1-opening date 2-closing date
 * @return string
 */
function vk_dbcdates($flag = 1) {
    global $dbcidglobal;
    $cdbc = content_diagnose($dbcidglobal);
    $odate = $cdbc['ax_odate'];
    $cdate = $cdbc['ax_cdate'];

    // put them in vektis form YYYYMMDD
    $od = str_replace('-','', $odate);
    $cd = str_replace('-','', $cdate);

    return (( $flag == 1 ) ? $od : $cd);

}

//-----------------------------------------------------------------------------
/**
 * DBC INFO
 * 
 * return different informations about a DBC
 * 
 * @param string $info
 * @return
 */
function vk_dbcinfo($info = 'ztn') {
    global $dbcidglobal;
    $cdbc = content_diagnose($dbcidglobal);

    switch ( $info ) {
        case 'ztn' : $ztn = $cdbc['ax_ztn']; $retval = str_pad($ztn, 20, ' ', STR_PAD_LEFT); break;
        case 'tariff': 
            $decode = trim(vk_claimcode('declaratie'));
            $date   = trim(vk_dbcdates(2)); // closing date?

            $q = sprintf("SELECT cl_dbc_tarief FROM cl_dbc_tarief WHERE cl_declaratiecode = '%s' 
            AND cl_dbc_tarief_begindatum <= '%s' AND cl_dbc_tarief_einddatum >= '%s' ", $decode, $date, $date);
            $r = mysql_query($q) or die(mysql_error()); 
            if ( mysql_num_rows($r) ) {
                $row = mysql_fetch_array($r); $val = $row['cl_dbc_tarief'];
            } else {
                $val = 0;
            }
            $retval = str_pad($val, 8, '0', STR_PAD_LEFT);
        break;
    }

    return $retval;
}

//-----------------------------------------------------------------------------
/**
 * WRITE THE DATA INTO THE FILE
 *
 * write the provided date into the file
 *
 * @param string $data
 * @return
 */
function vk_write_file($data) {
    global $filepointer;
    if ( !$filepointer ) return FALSE;

    flock($filepointer, LOCK_EX);
    fwrite($filepointer, $data);
    flock($filepointer, LOCK_UN);

}

//-----------------------------------------------------------------------------
/**
 * COUNT THE TOTAL NUMBER OF DBC READY FOR VEKTIS GENERATION
 *
 * @param int $type - the vektis records type (new; old - to be resubmited)
 * @return int
 */
function vk_number_ready($type = 1) {
    switch ( $type ) {
        case '1': $type = 1; break;
        case '3': $type = 3; break;
        default: $type = 1;
    }

    $q = sprintf("SELECT COUNT(*) AS c FROM cl_axes WHERE ax_vkstatus = $type ");

    $r = mysql_query($q) or die(mysql_error());
    $row = mysql_fetch_array($r);
    return (int)$row['c'];
}

//-----------------------------------------------------------------------------
/**
 * RETURN DBCs READY FOR VEKTIS GENERATION
 *
 * statuses 0 - DBC open, nothing to do
 *          1 - DBC closed; to be sent to VK
 *          2 - DBC closed; sent to VK but w/out returning codes
 *          3 - DBC closed; sent to VK and invalid; to be resubmited
 *          4 - DBC closed; sent to VK and validated.
 * @param int $status - what status do you want
 * @return array
 */
function vk_vektis_ready($status = 1) {
    $q = sprintf("SELECT ax_id FROM cl_axes WHERE ax_vkstatus = %d", $status);
    $r = mysql_query($q) or die(mysql_error());

    $result = array();
    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) )  {
            $result[] = content_diagnose($row['ax_id']);
        } // while
    } else {
        return FALSE;
    }

    return $result;
}


//-----------------------------------------------------------------------------
/**
 * GENERATE AN UNIQUE SESSION ID
 * 
 * it's about vektis session - the generating for file stage
 * an unique number for a moment in time
 * 
 * @param none
 * @return string
 */
function vk_vektis_session() {
    return date('dmYHis');
}

//-----------------------------------------------------------------------------
/**
 * DISPLAY THE GENERATED FILE
 * 
 * 
 * @param none
 * @return none
 */
function vk_display_file() {
    global $filename;

    $link_filename = basename($filename);
    echo "<p>The file <a href='" .VK_WORKINGLNK. "$link_filename'>$filename</a> was generated.</p>";

    $filepointer = @fopen($filename, "rb");
    $buffer      = '';

    if ( $filepointer ) {
        while ( !feof($filepointer) ) {
            $buffer .= fgets($filepointer, 1024);
        }
    }

    echo "<textarea rows='20' cols='150' wrap='off' readonly>$buffer</textarea>";
}

//-----------------------------------------------------------------------------
/**
 * COMMIT TO DB THE VEKTIS
 * 
 * save to db the content of the newly generates Vektis file.
 * 
 * @param none
 * @return none
 */
function vk_db_commit() {
    $arr_database   = $_SESSION['arrdb'];
    mysql_query("START TRANSACTION");
    mysql_query("BEGIN");

    if ( $arr_database ) {
        $data = date('Y-m-d', strtotime($arr_database['cvd_date']));
        // prepate the sql insert statements
        foreach ( $arr_database['cvd_pid'] as $ak => $av ) {
            $query = sprintf("INSERT INTO cl_vektis_data
                (cvd_session, 
                cvd_pid, 
                cvd_date, 
                cvd_116, 
                cvd_uzovi, 
                cvd_ztn, 
                cvd_tariff, 
                cvd_426,
                cvd_dbcid) 
            VALUES ('%s', %d, '%s', '%s', '%s', '%s', %d, '%s', %d)",
                $arr_database['cvd_session'],
                $av,
                $data,
                $arr_database['cvd_116'],
                $arr_database['cvd_uzovi'][$ak],
                trim($arr_database['cvd_ztn'][$ak]),
                $arr_database['cvd_tariff'][$ak],
                trim($arr_database['cvd_426'][$ak]),
                $arr_database['cvd_dbcid'][$ak]
            ); // $query
    
            // saving to the table
            mysql_query($query) or die(mysql_error());

            //echo '<pre>'.print_r($query, true).'</pre>'; // debug
        } // foreach

        // marked the DBC's as sent to vektis
        foreach ( $arr_database['cvd_dbcid'] as $dbcid) {
            $queryupdate = sprintf("UPDATE cl_axes SET ax_vkstatus = 2 WHERE ax_id = %d ", $dbcid);
            mysql_query($queryupdate) or die(mysql_error());
        }
    }

    mysql_query("COMMIT");
}

//-----------------------------------------------------------------------------
/**
 * PARSE THE UPLOADED FILE
 * 
 *  
 * @param string $file
 * @param int $state - 1 for simulation, 2 for real situation
 * @return none
 */
function vk_parse_returning_file($file = '', $state = 1) {
    if ( !$file ) return FALSE;

    $filepath = realpath($file);
    $fp = @fopen($filepath, "rb");
    $buffer      = '';
    $_SESSION['vk_retfile'] = $filepath;

    if ( $fp ) {
        while ( !feof($fp) ) {
            $buffer = fgets($fp);

            // we get the type of line from the first two digits
            $f2     = substr($buffer, 0, 2);
            switch ( $f2 ) {
                case '01': 
                    $extarr = vk_process_extra($buffer, 1); $com = vk_process_common($buffer, 1);
                    vk_shows_line($buffer, $extarr, $com, 1, $state);
                break;
                case '02': 
                    $extarr = vk_process_extra($buffer, 2); $com = vk_process_common($buffer, 2);
                    vk_shows_line($buffer, $extarr, $com, 2, $state);
                break;
                case '04': 
                    $extarr = vk_process_extra($buffer, 4); $com = vk_process_common($buffer, 4);
                    vk_shows_line($buffer, $extarr, $com, 4, $state);
                break;
                case '99': 
                    $extarr = vk_process_extra($buffer, 99); $com = vk_process_common($buffer, 99);
                    vk_shows_line($buffer, $extarr, $com, 99);
                break;
            } 

        }
    } // if $fp

}

//-----------------------------------------------------------------------------
/**
 * PROCESS EXTRA INFORMATIONS
 * 
 * process extra informations from every line in returning file
 *  
 * @param string $line - whole line (sent line + returning info)
 * @param int $case - the line type
 * @return array
 */
function vk_process_extra($line = '', $case = 1) {
    if ( !$line ) return FALSE;
    
    // the start and len values are taken from the vektis documentation
    switch ( $case ) {
        case 1: 
            $start = 310; $len = 44; 
            $extrastr = mb_substr($line, $start, $len); 
            $_181 = mb_substr($extrastr, 0, 24);
            $_182 = mb_substr($extrastr, 24, 8);
            $_196 = mb_substr($extrastr, 32, 4);
            $_197 = mb_substr($extrastr, 36, 4);
            $_198 = mb_substr($extrastr, 40, 4);
            $retarr = array('s181' => $_181, 's182' => $_182, 's196' => $_196, 's197' => $_197, 's198' => $_198);
        break;
        case 2: 
            $start = 310; $len = 12; 
            $extrastr = mb_substr($line, $start, $len); 
            $_296 = mb_substr($extrastr, 0, 4);
            $_297 = mb_substr($extrastr, 4, 4);
            $_298 = mb_substr($extrastr, 8, 4);
            $retarr = array('s296' => $_296, 's297' => $_297, 's298' => $_298);
        break;
        case 4: 
            $start = 310; $len = 30; 
            $extrastr = mb_substr($line, $start, $len); 
            $_481 = mb_substr($extrastr, 0, 8);
            $_482 = mb_substr($extrastr, 8, 1);
            $_483 = mb_substr($extrastr, 9, 8);
            $_484 = mb_substr($extrastr, 17, 1);
            $_496 = mb_substr($extrastr, 18, 4);
            $_497 = mb_substr($extrastr, 22, 4);
            $_498 = mb_substr($extrastr, 26, 4);
            $retarr = array('s481' => $_481, 's482' => $_482, 's483' => $_483, 's484' => $_484, 's496' => $_496, 's497' => $_497, 's498' => $_498);
        break;
        case 99: 
            $start = 45; $len = 12; 
            $extrastr = mb_substr($line, $start, $len); 
            $_9909 = mb_substr($extrastr, 0, 11);
            $_9910 = mb_substr($extrastr, 11, 1);
            $retarr = array('s9909' => $_9909, 's9910' => $_9910);
        break;
        default : return FALSE;
    } // switch

    return $retarr;

}


//-----------------------------------------------------------------------------
/**
 * PROCESS COMMON INFORMATIONS
 * 
 * common informations are the common elements from the file and already saved database
 * there are the links between db saved info and returning file
 *  
 * @param string $line - whole line (sent line + returning info)
 * @param int $case - the line type
 * @return none
 */
function vk_process_common($line = '', $case = 1) {
    if ( !$line ) return FALSE;

    // the start and len values are taken from the vektis documentation
    switch ( $case ) {
        case 1: 
            $_116 = mb_substr($line, 80, 12);
            $_117 = mb_substr($line, 92, 8);
            $retarr = array('s116' => $_116, 's117' => $_117);
        break;
        case 2: 
            $_204 = mb_substr($line, 23, 4);
            $_206 = mb_substr($line, 42, 11);
            $retarr = array('s204' => $_204, 's206' => $_206);
        break;
        case 4: 
            $_414 = mb_substr($line, 99, 20);
            $_418 = mb_substr($line, 136, 8);
            $_426 = mb_substr($line, 176, 20);
            $retarr = array('s414' => $_414, 's418' => $_418, 's426' => $_426);
        break;
    } // switch

    return $retarr;

}

//-----------------------------------------------------------------------------
/**
 * VALIDATE THE RETURNING FILE
 * 
 * 
 * 
 *  
 * @param string $errcode
 * @return bool|array('valid' => true|false, 'letter' => 'X|T|D|space', 'expl' => explanation, 'code' => code)
 */
function vk_validate_vektis($errcode) {
    // if we don't have a retcode, then return valid (aka do nothing)
    if ( $errcode == '0000') return array('valid' => TRUE, 'letter' => '', 'expl' => '', 'code' => '');

    $earr = vk_error_code($errcode);
    $retarr = array();

    if ( $earr ) {
        switch ( $earr['cvr_content'] ) {
            case 'X':
            case 'T': 
            case 'D': $retarr['valid'] = FALSE; break;
            default: $retarr['valid'] = TRUE;
        }
        $retarr['letter'] = $earr['cvr_content'];
        $retarr['expl']   = $earr['cvr_expl'];
        $retarr['code']   = $earr['cvr_code'];
    } 

    return $retarr;
}

//-----------------------------------------------------------------------------
/**
 * RETURN THE ERROR DESCRIPTION FOR A CODE
 * 
 * works with cl_vektis_retcodes table
 * 
 * @param string $retcode
 * @return array
 */
function vk_error_code($retcode) {
    $q = sprintf("SELECT * FROM cl_vektis_retcodes WHERE cvr_code = '%s' ", $retcode);
    $r = mysql_query($q) or die(mysql_error());
    return mysql_fetch_array($r);
}

//-----------------------------------------------------------------------------
/**
 * SHOWS A LINE FROM THE VALIDATION FILE
 * 
 * shows the validation result 
 * if in 'real' mode it saves also into db
 * 
 * @param string $buffer - whole line
 * @param array $extarr - extra fields
 * @param array $com - common fields
 * @param int $line - line type (e.g. 01, 04, 99)
 * @param int $state - 1 simulation, 2 real
 * @return 
 */
function vk_shows_line($buffer, $extarr, $com, $line, $state = 1) {

   switch ( $line ) {
        case 1: 
            $e1 = vk_validate_vektis($extarr['s196']);
            $e2 = vk_validate_vektis($extarr['s197']);
            $e3 = vk_validate_vektis($extarr['s198']);
        break;
        case 2: 
            $e1 = vk_validate_vektis($extarr['s296']);
            $e2 = vk_validate_vektis($extarr['s297']);
            $e3 = vk_validate_vektis($extarr['s298']);
        break;
        case 4: 
            $e1 = vk_validate_vektis($extarr['s496']);
            $e2 = vk_validate_vektis($extarr['s497']);
            $e3 = vk_validate_vektis($extarr['s498']);
        break;
        case 99: 
            $lastline = vk_format_msg($extarr, 1);
        break;
    }

    // common part
    if ( $line != 99 ) {
        if ( !$e1['valid'] || !$e2['valid'] || !$e3['valid'] ) {
            $class = 'vk_error'; 
            $e = array($e1, $e2, $e3);
            if ( $state == 2 ) vk_mod_vkstatus($line, $com, $e);
        } else {
            $class = 'vk_expl';
        }
    }

    $e1str = ( $e1['expl'] ) ? vk_format_msg($e1) : '';
    $e2str = ( $e2['expl'] ) ? vk_format_msg($e2) : '';
    $e3str = ( $e3['expl'] ) ? vk_format_msg($e3) : '';

    $msgline = ( $lastline ) ? $lastline : $e1str . $e2str . $e3str;
    if ( $lastline ) $class = 'vk_expl';
    $shstr = "<div class='vk_nline'>$buffer</div><div class='$class'>$msgline</div>";

    // display the built string
    echo $shstr;
}

//-----------------------------------------------------------------------------
/**
 * FORMATS THE MESSAGE FOR RETURNING CODES
 * 
 * for the last line there is a special format
 * 
 * @param array $e - all infos about a retcode
 * @param bool $lastline - is the last line? (default:false)
 * @return string
 */
function vk_format_msg($e, $lastline = 0) {
    if ( $lastline ) {
        $str = "TOTAL ASSIGNED AMOUNT: {$e['s9909']} D/C: {$e['s9910']} <br />";
    } else {
        // common line format      
        $content = ( $e['letter'] != 'O') ? $e['letter'] : 'OK';
        $str = "CODE: {$e['code']} - {$e['expl']} ($content) <br />";
    } 

    return $str;
}

//-----------------------------------------------------------------------------
/**
 * MODIFY THE VEKTIS STATUS
 * 
 * 
 * @param int $line - in what line the error appeared
 * @param array $com - common part (sent/returned files)
 * @param array $ee - those three array errors
 * @return
 */
function vk_mod_vkstatus($line, $com, $ee) {
    // function of the line, we treat different the errors  
    switch ( $line ) {
        case 1: 
            foreach ( $ee as $e) {
                // for X-T letters on the first level we invalidate all the file
                if ( $e['letter'] == 'X' || $e['letter'] == 'T' ) {
                    vk_file_invalidatevektis();
                } //if
            }
        break;
        case 2: 
            $pid = $com['s206'];
            foreach ( $ee as $e) {
                // for X-T letters on the first level we invalidate all the file ??
                if ( $e['letter'] == 'D' ) {
                    vk_file_invalidatedbc($pid, 2);
                } else if ( $e['letter'] == 'O' ){
                    // make it valid
                }
            }
        break;
        case 4: 
            foreach ( $ee as $e) {
                // for X-T letters on the first level we invalidate all the file ??
                if ( $e['letter'] == 'D' ) {
                    vk_file_invalidatedbc($com, 4); // we send as pid the common elements
                } else if ( $e['letter'] == 'O' ){
                    // make it valid
                }
            }
        break;
        default: return FALSE;
    } // switch
}


//-----------------------------------------------------------------------------
/**
 * RETURNS SOME FIELDS FROM THE FILE
 * 
 * 116 - vektis invoice number for all the file
 * 
 * 
 * @param string $var - what field
 * @param string $ztn - what ztn to look for (needed only for 426 or so, because there are many lines)
 * @return string|bool - the invoice number
 */
function vk_file_getvar($var = '116', $ztn = 0) {

    if ( !isset($_SESSION['vk_retfile']) || empty($_SESSION['vk_retfile']) ) {
        return FALSE;
    }

    // array that containts lines where are the requested var
    $elemarr = array('116' => '01', '426' => '04');

    $fp = @fopen($_SESSION['vk_retfile'], "rb");
    if ( $fp ) {
        while ( !feof($fp) ) {
            $buffer = fgets($fp);
            // we get the type of line from the first two digits
            $f2     = substr($buffer, 0, 2);

            // we parse all the lines looking for our line type
            if ( $f2 == $elemarr[$var] ) { 
                // different cases
                if ( $var = '116' ) {
                    $varfield = mb_substr($buffer, 80, 12);
                }

                if ( $var = '426' ) {
                    // not needed in this moment
                }
            }
        } 
    } else return FALSE;

    return trim($varfield);
}


//-----------------------------------------------------------------------------
/**
 * INVALIDATE ALL VEKTIS FILE
 * 
 * look for dbcs after unique vektis invoice number
 * 
 * @param none
 * @return void
 */
function vk_file_invalidatevektis() {
    $inv = vk_file_getvar('116');

    $q = sprintf("SELECT * FROM cl_vektis_data WHERE cvd_116 = '%s' ", $inv);
    $r = mysql_query($q) or die(mysql_error());
    while ( $row = mysql_fetch_array($r) ) {
        // 3 - means 'to be resubmitted'
        $q2 = sprintf("UPDATE cl_axes SET ax_vkstatus = 3 WHERE ax_id = %d ", $row['cvd_dbcid']);
        $r2 = mysql_query($q2) or die(mysql_error());
    }
}

//-----------------------------------------------------------------------------
/**
 * INVALIDATE ALL DBC FROM VEKTIS FILE
 * 
 * for a specified patient
 * 
 * @param int $pid - patient id
 * @param int $case - what situation? row type 02 or 04 ?
 * @return void
 */
function vk_file_invalidatedbc($pid, $case) {

    if ( $case == 2 ) {
        $inv = vk_file_getvar('116'); 
        $q = sprintf("SELECT * FROM cl_vektis_data WHERE cvd_116 = '%s' AND cvd_pid = %d", $inv, $pid);
    } else if ( $case == 4) {
        // here we don't need the patient id but we use this var to retrieve the common elements
        // so here, $pid means actually $com array
        $q = sprintf("SELECT * FROM cl_vektis_data WHERE cvd_426 = '%s' ", trim($pid['s426'])); 
    }

    $r = mysql_query($q) or die(mysql_error());
    if ( mysql_num_rows($r) ) {
        while ( $row = mysql_fetch_array($r) ) {
            // 3 - means 'to be resubmitted'
            $q2 = sprintf("UPDATE cl_axes SET ax_vkstatus = 3 WHERE ax_id = %d ", $row['cvd_dbcid']);
            $r2 = mysql_query($q2) or die(mysql_error());
        }
    } // if
}

//-----------------------------------------------------------------------------
/**
 * CHANGE DBC STATUS FROM 'WRONG TO RIGHT'
 * 
 * wrong means it's 3 (to be resubmitted), right means it's 1 (to be sent)
 * 
 * @param int $dbcid
 * @return void
 */
function vk_dbc_resubmit($dbcid = 0) {
    if ( !$dbcid ) return FALSE;

    // ax_vkstatus = 3 -> supplementary caution measure
    $q = sprintf("UPDATE cl_axes SET ax_vkstatus = 1 WHERE ax_id = %d AND ax_vkstatus = 3 ", $dbcid);
    mysql_query($q) or die(mysql_error());

}

//-----------------------------------------------------------------------------
/**
 * CUSTOM FUNCTION TO FIND SPECIAL DBC
 * 
 * condition: Overloop DBCs (all DBCs with encounters both in 2007 and 2008):
 * 
 * @param int $dbcid
 * @return void
 */
function vk_is_overloop_dbc($dbcid = 0) {
    if ( !$dbcid ) return FALSE;

    $dbc = content_diagnose($dbcid);
    $odate = $dbc['ax_odate'];
    $cdate = $dbc['ax_cdate'];

    global $patientglobal;
    $pid = $patientglobal;

    // 2007 encounters
    $q1 = mysql_query("SELECT COUNT(*) AS a FROM openemr_postcalendar_events WHERE pc_pid = $pid AND pc_apptstatus = '@'
    AND pc_eventDate >= '$odate' AND pc_eventDate <= '2007-12-31' ") or die(mysql_error());
    $r1 = mysql_fetch_array($q1);
    $enc2007 = $r1['a'];

    if ( $enc2007 ) {
    // 2008 encounters
        $q2 = mysql_query("SELECT COUNT(*) AS a FROM openemr_postcalendar_events WHERE pc_pid = $pid 
        AND pc_apptstatus = '@'
        AND pc_eventDate >= '2008-01-01' AND pc_eventDate <= '$cdate' ") or die(mysql_error());
        $r2 = mysql_fetch_array($q2); 
        $enc2008 = $r2['a'];
    } else {
        $enc2008 = 0;
    } //if $enc2007 

    return ($enc2007 && $enc2008);

}


//-----------------------------------------------------------------------------
/**
 * CUSTOM FUNCTION TO CALCULATE DEDUCTION FOR DBC's
 * 
 * function of F* codes in 2007 (not 2008!)
 * 
 * @param int $dbcid
 * @return int
 */
function vk_deduction($dbcid = 0) {
    global $patientglobal;
    $pid = $patientglobal;

    $enarr = array(  'F101' => '2090', 'F102' => '20040', 'F103' => '41610', 'F104' => '10160',
                     'F105' => '6840', 'F106' => '8550' , 'F221' => '4750',  'F151' => '2090',
                     'F152' => '16150','F153' => '44170', 'F154' => '15290', 'F155' => '9780',
                     'F156' => '9210', 'F224' => '4940');
    $total_sum = 0;
    foreach ( $enarr as $ek => $ev ) {
        $q = mysql_query("SELECT COUNT(*) AS a FROM openemr_postcalendar_events WHERE pc_pid = $pid AND pc_apptstatus = '@' AND pc_eventDate >= '2007-01-01' AND pc_eventDate <= '2007-12-31' AND pc_title LIKE '$ek%'");
        $r = mysql_fetch_array($q); 
        $total_sum += $r['a'] * $ev;
    }

    $total_sum = round($total_sum);
    
    return $total_sum;
}

//----------------------------------------------------------------------------- 
?>
