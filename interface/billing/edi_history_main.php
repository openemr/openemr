<?php
/*
 * edi_history_main.php
 * 
 * Copyright 2012 Kevin McCormick Longview, Texas
 * 
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 or later.  You should have 
 * received a copy of the GNU General Public License along with this program; 
 * if not, write to the Free Software Foundation, Inc., 
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *  <http://opensource.org/licenses/gpl-license.php>
 * 
 * 
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */

/* these lines for OpenEMR
 */  
$sanitize_all_escapes=true; 
$fake_register_globals=false; 
require_once(dirname(__FILE__) . "/../globals.php");

/**
 * this define is used to prevent direct access to the included scripts 
 * which have the corresponding definition commented for now
 */
define('SITE_IN', 1);

// define constants
// since enounter digits are sequential, digit length should rarely change
// however for a startup they may, or a "mask" value of 1000 or 10000
// would be a good idea if there are problems with deciphering the pid-encounter
// same idea for pid value, but since encounter is unique and always last, it is essential
// possibly check the mask value in OpenEMR globals to set this
/**
 * Try and prevent panic if patient invoice number is mangled by treating the last
 * digits as the encounter number
 */
if (!defined("IBR_ENCOUNTER_DIGIT_LENGTH")) define("IBR_ENCOUNTER_DIGIT_LENGTH",  "5");
/**
 * these delimiters are hardcoded into OpenEMR batch files
 */   
if (!defined("SEG_ELEM_DELIM")) define( "SEG_ELEM_DELIM" , "*");
if (!defined("SEG_TERM_DELIM")) define( "SEG_TERM_DELIM" , "~");
/**
 * clearinghouse practice
 */
if (!defined("IBR_DELIMITER")) define("IBR_DELIMITER", "|"); 
//
// path will be "$srcdir/edihistory/filename.php"
require_once("$srcdir/edihistory/csv_record_include.php");    //dirname(__FILE__) . "/edihist/csv_record_include.php"); 
require_once("$srcdir/edihistory/ibr_era_read.php");          //dirname(__FILE__) . "/edihist/ibr_era_read.php"); 
require_once("$srcdir/edihistory/ibr_code_arrays.php");       //dirname(__FILE__) . "/edihist/ibr_code_arrays.php");
require_once("$srcdir/edihistory/ibr_ebr_read.php");          //dirname(__FILE__) . "/edihist/ibr_ebr_read.php"); 
require_once("$srcdir/edihistory/ibr_997_read.php");          //dirname(__FILE__) . "/edihist/ibr_997_read.php"); 
require_once("$srcdir/edihistory/ibr_277_read.php");          //dirname(__FILE__) . "/edihist/ibr_277_read.php"); 
require_once("$srcdir/edihistory/ibr_status_code_arrays.php"); //dirname(__FILE__) . "/edihist/ibr_status_code_arrays.php"); 
require_once("$srcdir/edihistory/ibr_batch_read.php");        //dirname(__FILE__) . "/edihist/ibr_batch_read.php");
require_once("$srcdir/edihistory/ibr_ack_read.php");          //dirname(__FILE__) . "/edihist/ibr_ack_read.php");
require_once("$srcdir/edihistory/ibr_uploads.php");           //dirname(__FILE__) . "/edihist/ibr_uploads.php");
require_once("$srcdir/edihistory/ibr_io.php");                //dirname(__FILE__) . "/edihist/ibr_io.php");
//
// php may output line endings if include files are utf-8
ob_clean();

if (isset($GLOBALS['OE_SITE_DIR'])) {
    $ibr_upldir = csv_edih_basedir();
    $ibr_tmp = csv_edih_tmpdir();
} else {
    die("EDI History: Did not get directory path information!");
}

// if we are not set up, create directories and csv files
//if (!is_dir(dirname(__FILE__) . '/edihist' . IBR_HISTORY_DIR) ) {
if (!is_dir($ibr_upldir)) {
	//
	echo "setup with base directory: $ibr_upldir <br />" .PHP_EOL;
	$is_set = csv_setup($html_str );
	if (!$is_set) {
		print $html_str;
		csv_clear_tmpdir();
		exit;
	}
}

/* ******* remove functions to separate file ******* */
/*  
 * functions called in the if stanzas are now in ibr_io.php
 */

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
	//
	if ( isset($_POST['NewFiles']) ) {
		// process new files button clicked
		$html_str = ibr_disp_newfiles();
		
	} elseif ( isset($_POST['Batch-enctr']) && isset($_POST['enctrbatch']) ) {
		// may be ajax for dialog or straight for new window
		$html_str = is_xhr() ? '' : ibr_html_heading('textdisplay');
		$html_str .= ibr_disp_claimst();
		
	} elseif (isset($_POST['eraText']) && $_POST['enctrEra'] ) {	
		// get the text of an era remittance from the file
		$html_str = is_xhr() ? '' : ibr_html_heading('textdisplay');
		$html_str .= ibr_disp_eraClp();
		
	} elseif (isset($_POST['csvshowtable'])) {
		// should be ajax
		$html_str = is_xhr() ? '' : ibr_html_heading('csvtable');
		$html_str .= ibr_disp_csvtable();
		
	} elseif (isset($_POST['subpid835']) ) {
		// era table display for patient id
		$html_str = is_xhr() ? '' : ibr_html_heading('eradisplay');
		$html_str .= ibr_disp_era_post();
		
	} elseif (isset($_POST['subenctr835']) ) {
		// era table display for encounter ibr_disp_era_get()
		$html_str = is_xhr() ? '' : ibr_html_heading('eradisplay');
		$html_str .= ibr_disp_era_post();
		
	} elseif (isset($_POST['subtrace835']) ) {
		//	era table display be trace number
		$html_str = is_xhr() ? '' : ibr_html_heading('eradisplay');	
		$html_str .= ibr_disp_era_post();
		
	} elseif (isset($_POST['fileERA']) && isset($_FILES['fileUplEra'])) {	
		// upload local files for display  _FILES accessed in called functions
		$html_str = is_xhr() ? 'XHR XMLHttpRequest' : ibr_html_heading('eradisplay');
		$html_str .= ibr_disp_eraFileUpl();
	
	} elseif (isset($_POST['fileX12']) ) {	
		// upload local x12 file
		$html_str = is_xhr() ? '' : ibr_html_heading('x12display');
		//$html_str .= ibr_disp_fileUpl();	
		$html_str .= ibr_disp_fileText();
		
	} elseif (isset($_POST['uplsubmt']) && isset($_FILES['fileUplMulti']) ) { 
		// upload multiple files for sorting and storage
		// output is a popup window and full html is required
		$html_str = ibr_html_heading('newfiles');
		$html_str .= ibr_disp_fileMulti();
		
	} elseif (isset($_POST['putnotes']) ) {
		$html_str = ibr_history_notes();
		
	} else {
		$html_str = ibr_html_heading('error');
		$html_str .= "<p>Error: unrecognized value in POST array</p>".PHP_EOL;
		foreach($_POST as $ky => $val) {
			$html_str .= "$ky : $val <br />".PHP_EOL;
		}
    }  // end if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
    //
} elseif (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
	//
	if (isset($_GET['srvinfo']) && $_GET['srvinfo'] == 'yes') { 
		// initial ajax request
		$html_str = ibr_inivals();
		
	} elseif (isset($_GET['csvtbllist']) && $_GET['csvtbllist'] == 'yes') { 
		// initial ajax request
		$html_str = csv_table_select_list();
        
	} elseif (isset($_GET['ckprocessed']) && $_GET['ckprocessed'] == 'yes') { 
		// initial ajax request
		$html_str = ibr_disp_is_era_processed();
        
	} elseif (isset($_GET['fvkey']) ) { 
		// this will output to a new window (target=_blank)
		$html_str .= ibr_disp_fileText();
		
	} elseif (isset($_GET['btctln'])) { 
		$html_str .= ibr_disp_fileText();
					
	} elseif (isset($_GET['erafn']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('eradisplay');
		$html_str .= ibr_disp_era_get();
		 
	} elseif ( isset($_GET['fvbatch']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('textdisplay');
		$html_str .= ibr_disp_claimst();
		
	} elseif ( isset($_GET['chenctr']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('claimstatus');
		$html_str .= ibr_disp_clmhist();		
		
	} elseif ( isset($_GET['rspfile']) ) { 
		$html_str = is_xhr() ? '' : ibr_html_heading('claimstatus');
		$html_str .= ibr_disp_status_resp();
		
	} elseif ( isset($_GET['dprfile']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('claimstatus');
		$html_str .= ibr_disp_dpr_message();
		
	} elseif ( isset($_GET['ebrfile']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('claimstatus');
		$html_str .= ibr_disp_ebr_message();
				
	} elseif ( isset($_GET['fv997']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('claimstatus');
		$html_str .= ibr_disp_997_message();
		
	} elseif ( isset($_GET['ackfile']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('claimstatus');
		$html_str .= ibr_disp_ta1_message();
        
    } elseif ( isset($_GET['batchicn']) ) {
		$html_str = is_xhr() ? '' : ibr_html_heading('claimstatus');        
        $html_str .= ibr_disp_997_for_batch();
				
	} elseif (array_key_exists('showlog', $_GET)) { 
		$la = filter_input(INPUT_GET, 'showlog', FILTER_SANITIZE_STRING);
		$html_str = ($la) ? csv_log_html() : "input parameter error<br />" ;
		
	} elseif (array_key_exists('archivelog', $_GET)) { 
		$la = filter_input(INPUT_GET, 'archivelog', FILTER_SANITIZE_STRING);
		$html_str = ($la) ? csv_log_archive() : "input parameter error<br />" ;
		
	} elseif (array_key_exists('getnotes', $_GET) ) {
		$la = filter_input(INPUT_GET, 'getnotes', FILTER_SANITIZE_STRING);
		$html_str = ($la) ? ibr_history_notes() : "input parameter error<br />"; 
		
	} else {
		$html_str = "EDI History: unknown parameter<br />" .PHP_EOL;
		//$html_str .= var_dump($_GET) . PHP_EOL;
	}
	
} else {
	die("EDI History: invalid input method <br />");
}

//
$isclear = csv_clear_tmpdir();
if (!$isclear) { 
	echo "file contents remain in $ibr_tmp <br />".PHP_EOL; 
	csv_edihist_log("file contents remain in $ibr_tmp");
}
//
if (!$html_str) {
	csv_edihist_log("no html output!");
	die("No content in response <br />" . PHP_EOL);
}
//
print $html_str;
		
?>
