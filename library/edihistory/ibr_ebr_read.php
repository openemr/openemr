<?php
/**
 *   ibr_ebr_read.php
 *  
 * Copyright 2012 Kevin McCormick   Longview Texas  
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
 * 
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
 
// a security measure to prevent direct web access to this file
// must be accessed through the main calling script ibr_history.php 
// from admin at rune-city dot com;  found in php manual
//if (!defined('SITE_IN')) die('Direct access not allowed!');
 
// define constants
if (!defined("IBR_DELIMITER")) define("IBR_DELIMITER", "|");    // per Availity edi guide
if (!defined("DPR_MSG_DELIM")) define("DPR_MSG_DELIM", "^");
//if (!defined("IBR_ENCOUNTER_DIGIT_LENGTH")) define("IBR_ENCOUNTER_DIGIT_LENGTH",  "4");


/**
 * derive 'text' version name from ebr, ibr, and dpr files
 * 
 * specific to Availity LLC -- see their EDI Guide
 * also added 999 files, since availity practice is 99T for 'text' version
 * 
 * @param string    file name of related ebr, ibr, or dpr file
 * @return string
 */
function ibr_ebr_ebt_name ($ebr_fname) {
	 // based upon Availity conventions: see availity_edi_guide
	 // the EBR-*-<<seq>>.ebr --> EBT-*-<<seq>>.ebt is the change made here
	 // Availity gives the text version with a sequence number increased by 1
	 // examples:
	 // DPR-MULTIPAYER-201204250830-001-320101111-1821101111.dpr		
	 // DPT-MULTIPAYER-201204250830-002-320101111-1821101111.dpt	 
	 //
	 // EBR-MULTIPAYER-201204210230-001.ebr
	 // EBT-MULTIPAYER-201204210230-002.ebt
	 //
	 // not tested with more complex file names, but it should work
	 // this concept also applies to .997 files, but they are too simple
	 //
	 // get the sequence, relying on it being the only 3 digit part of the filename
	 $re_seq = '/-([0-9]{3})[\-|.]{1}/';
	 preg_match ($re_seq, $ebr_fname, $matches);
	 $m_seq = $matches[1];
	 //
	 // increment the sequence and reform as 3 byte count
	 $f_seq = ltrim ($m_seq, "0"); 
	 $f_seq += 1;
	 $f_seq = str_pad($f_seq, 3, "0", STR_PAD_LEFT);
	 // insert leading "-" to avoid substitution error
	 $m_seq = str_pad($m_seq, 4, "-", STR_PAD_LEFT); 
	 $f_seq = str_pad($f_seq, 4, "-", STR_PAD_LEFT);
	 //
	 // file names will begin with IBR- EBR- DPR-
	 $f_type = strtoupper(substr($ebr_fname, -4));
	 //
	 if ($f_type == ".EBR" ) {
		 // replace the EBR and .ebr parts
		 $f_txt_name = str_replace ("EBR-", "EBT-", $ebr_fname);
		 //echo "1: $f_txt_name" . PHP_EOL;
		 $f_txt_name = str_replace (".ebr", ".ebt", $f_txt_name);
		 $f_txt_name = str_replace ($m_seq, $f_seq, $f_txt_name);
	 } elseif ($f_type == ".IBR" ) { 
		 // replace the IBR and .ibr parts
		 $f_txt_name = str_replace ("IBR-", "IBT-", $ebr_fname);
		 //echo "1: $f_txt_name" . PHP_EOL;
		 $f_txt_name = str_replace (".ibr", ".ibt", $f_txt_name);
		 // Availity apparently does not change sequence numbers on IBR -- IBT files
	 } elseif ($f_type == ".DPR" ) {
		 // replace the DPR and .dpr parts 
		 // (if DPR files are added to this script)
		 $f_txt_name = str_replace ("DPR-", "DPT-", $ebr_fname);
		 //echo "1: $f_txt_name" . PHP_EOL;
		 $f_txt_name = str_replace (".dpr", ".dpt", $f_txt_name);
		 $f_txt_name = str_replace ($m_seq, $f_seq, $f_txt_name);
	 } elseif ($f_type == ".997" ) {
		 // also Availity 999 to 99T files
		 $f_txt_name = str_replace (".999", ".99T", $f_txt_name);
	 } 
	 //
	 // insert the incremented sequence number
	 //$f_txt_name = str_replace ($m_seq, $f_seq, $f_txt_name);	 
	 // return the text version file name
	 return $f_txt_name;
 }
 
 
/**
 * read the .ibr .ebr or .dpr file into an array of arrays 
 * 
 * Clearinghouse specific format, not x12
 * 
 * @deprecated
 * @param string   file path
 * @return array
 */
function ibr_ebr_filetoarray ($file_path ) {
	// 
	// since the file is multi-line, use fgets()
	//$delim = "|"; 
	$fh = fopen($file_path, 'r');
	if ($fh) {
		while (($buffer = fgets($fh, 4096)) !== false) {
			$ar_ibr[] = explode ( IBR_DELIMITER, trim($buffer) );
		}
	} else	{
		//	trigger_error("class.ibr_read.php :	failed to read $file_path"  );      
		csv_edihist_log( "ibr_ebr_filetoarray Error: failed to read " . $file_path  );
	}
	fclose($fh);
	return $ar_ibr;
 }

/* ************ DPR functions *************** */

/**
 * Find and retrieve the claim response message in the dpr file.
 * 
 * The message for given for a particular claim is returned in
 * html format.  The message is strangely formatted, so the
 * output may not be as clean as desired.
 * 
 * @uses csv_verify_file()
 * @uses csv_ebr_filetoarray(
 * @param string
 * @param string
 * @return string
 */
function ibr_dpr_message($dpr_filepath, $clm01) {
	//
	$str_html = '';
	$fp = csv_verify_file($dpr_filepath, 'dpr', false);
	if ($fp) {
		//$dpr_ar = ibr_ebr_filetoarray ($fp);
        $dpr_ar = csv_ebr_filetoarray($fp);
	} else {
		csv_edihist_log("ibr_dpr_message: file read error $dpr_filepath");
		$str_html = "Unable to read " . basename($dpr_filepath) . "<br />".PHP_EOL;
		return $str_html;
	}
	if (!is_array($dpr_ar) || !count($dpr_ar) ) {
		csv_edihist_log("ibr_dpr_message: file read error $dpr_filepath");
		$str_html = "Unable to read " . basename($dpr_filepath) . "<br />".PHP_EOL;
		return $str_html;
	}
	foreach($dpr_ar as $fld) {
		if ($fld[0] == 'CST' && $fld[2] == $clm01) {
			$msgstr = $fld[9];
			$msgstr = str_replace('\\', '', $msgstr);
			$msgstr = str_replace('^^^', '<br />', $msgstr);
			$msgstr = str_replace('A^^', '', $msgstr);
			$msgstr = wordwrap($msgstr, 46, '<br />'.PHP_EOL);
			$str_html .= "<p class='ibrmsg'>$msgstr</p>";
			//
			break;
		}
	}
	if (!$str_html) {
		$str_html .= "Did not find $clm01 in " . basename($fp) . "<br />".PHP_EOL;
	}
	return $str_html;
}

/**
 * order the claim data array for csv file
 * 
 * @param array individual claim array
 * @return array
 */
function ibr_dpr_csv_claims($claim_ar) {
	// 
	$errstr ='';
	//	['dpr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');	
	$f_claim_data[0] = $claim_ar['pt_name'];
	$f_claim_data[1] = $claim_ar['date'];
	$f_claim_data[2] = $claim_ar['clm01'];
	$f_claim_data[3] = $claim_ar['status'];
	$f_claim_data[4] = $claim_ar['batch_name'];
	$f_claim_data[5] = $claim_ar['dpr_name'];
	$f_claim_data[6] = $claim_ar['payer_name'];
	//
	return $f_claim_data;
}


/**
 * parse dpr file into an array
 * 
 * @uses ibr_ebr_ebt_name()
 * @param array  the array from csv_ebr_filetoarray()
 * @param string  the file path
 * @return array
 */
function ibr_dpr_values ( $ar_dpr_fields, $dpr_filepath) {
	// the .dpr file is one DPR line for each batch file
	// followed by a CST for each patient for that payer in the batch file
	//
	if ($dpr_filepath) {
		$fname = basename($dpr_filepath);
		$dpt_name = ibr_ebr_ebt_name ($fname);
	} else {
		csv_edihist_log("ibr_dpr_values: no file path provided");
		return FALSE;
	}
	if (!is_array($ar_dpr_fields) && count($ar_dpr_fields) > 0) {
		csv_edihist_log("ibr_dpr_values: no values array provided");
		return FALSE;
	}		
	//
	$ar_dpr_clm = array();
	//
	foreach ( $ar_dpr_fields as $fld ) {
		//
		if ( $fld[0] == "DPR" ) {
			$batch_ctrl = $fld[3];
			$batch_name = $fld[6];
            continue;
		}
		if ($fld[0] = "CST" ) {
			// CST line -- there may be one or more for each DPR line, but we just create a line entry for
			//  each CST line  
			//  -- only have a claims_dpr.csv file
			//
			$ar_pid = preg_split('/\D/', $fld[2], 2, PREG_SPLIT_NO_EMPTY);
			if ( count($ar_pid) == 2)  {	
				$pid = $ar_pid[0];
				$enctr = $ar_pid[1];
			} else {
				$enctr = substr($fld[2], -IBR_ENCOUNTER_DIGIT_LENGTH);
				$pid = substr($fld[2], 0, strlen($fld[2]) - IBR_ENCOUNTER_DIGIT_LENGTH);
			}
            //
            $clm01 = $fld[2];
            $payer_id = $fld[3];
            $pt_name = $fld[5];
            $frm_date = $fld[6]; 
            $payer_name = $fld[15];
            // take the fields out of order because the message precedes the status
            //
            $clm_status = $fld[11];    // Status|  "REJ" or "ACK"
            //
            $msg_txt = "";
            // DPR_MSG_DELIM is '^' and '^' is a PCRE metacharacter 
            // the DPR layout leaves a lot to be desired, there is no telling what you will get
            // but it appears to be a distillation of the 277
            $msg_ar = explode(DPR_MSG_DELIM, $fld[9]);
            if (is_array($msg_ar) && count($msg_ar)) {
				for($i=0; $i<count($msg_ar); $i++) {
					$statuspos = strpos($msg_ar[$i], 'Status:');
					if ($statuspos) {
						$msg_txt = substr($msg_ar[$i], $statuspos) . ' ' . substr($msg_ar[$i], 0, $statuspos-1);
					} else {
						$msg_txt .= (strpos($msg_ar[$i], '\\') === false) ? $msg_ar[$i] : str_replace('\\', ' ', $msg_ar[$i]);
					}
				}
			}	
            $msg_txt = trim($msg_txt); 
            
            $clm_ins_id = $fld[12];    // Payer Claim Number|
            $clm_ins_name = $fld[15];  // Payer Name|
            //
			//$csv_hd_ar['dpr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer')
            // add the values to the output array
            $ar_dpr_clm[] = array( 
			            'pt_name' => $pt_name,
			            'date' => $frm_date,
			            'clm01' => $clm01,
			            'status' => $clm_status,
			            'payer_name' => $payer_name,
			            'batch_name' => $batch_name,
			            'dpr_name' => $fname,
			            'dpr_text'=> $dpt_name,
			            'message' => $msg_txt 
						);			
         }
         // 'pt_name''date''pid''enctr''ext''status''batch_name''file_text''message'
	 } // end foreach
	 //
	 return $ar_dpr_clm; 
 } // end function  ibr_dpr_values   


/**
 * Create html table for processing of file
 * 
 * If all claims are accepted then only a message so indicating is shown
 * 
 * @param array  data array from ibr_dpr_values()
 * @param bool   only show rejected claims if true
 * @return string
 */ 
function ibr_dpr_html ($ar_dpr_data, $err_only=FALSE) {
	//
	$hasclm = FALSE;
	$str_html = "";
	$idx = 0;
	$rj_ct = 0;
	$ack_ct = 0;
	// the details table heading
	$clm_hdg = "<table cols=6 class=\"ibr_dpr\">
	 <thead>
		<tr>
		  <th>Patient Name</th><th>Date</th><th>Claim</th><th>Account</th>
		  <th>Status</th><th>DPR Name</th><th>BatchFile</th>
		</tr>
		<tr>
		  <th colspan=6 align=left>Message</th>
		</tr>
	 </thead>
	 <tbody>";
	//'pt_name''date''pid''enctr''ext''status''dpr_name''batch_name''file_text''message'
	
	if (is_array($ar_dpr_data) && count($ar_dpr_data) > 0) {
		// create html output for the claims
		foreach ($ar_dpr_data as $val) {
			//
			$idx++;
			// if $err_only, then only get information on rejected claims
			// rejected claims will have one or more 3e subarrays
			if ($val['status'] == 'ACK') { $ack_ct++; }
			if ($err_only && $val['status'] == 'ACK') { continue; }
			//
			if ($val['status'] == 'REJ') { $rj_ct++; }
			$hasclm = TRUE;
			// alternate row background colors
			$bgc = ($rj_ct % 2 == 1 ) ? 'odd' : 'even';
			//
			$clm_html .= "<tr class=\"{$bgc}\">
			   <td>{$val['pt_name']}</td>
			   <td>{$val['date']}</td>
			   <td><a class=\"btclm\" target=\"_blank\" href=\"edi_history_main.php?fvbatch={$val['batch_name']}&btpid={$val['clm01']}\">{$val['clm01']}</a></td>
			   <td><a class=\"clmstatus\" target=\"_blank\" href=\"edi_history_main.php?dprfile={$val['dpr_name']}&dprclm={$val['clm01']}\">{$val['status']}</a></td>
			   <td><a target=\"_blank\" href=\"edi_history_main.php?fvkey={$val['dpr_name']}\">{$val['dpr_name']}</a></td>
			   <td title={$val['payer_id']}>{$val['payer_name']}</td>
			  </tr>
			  <tr class=\"{$bgc}\">
			   <td  colspan=6>{$val['message']}</td>
			  </tr>";
		} // end foreach(($ar_cd as $val)
		//
		// if there were any claims detailed
		if ( $hasclm && strlen($clm_html) ) {
			$str_html .= $clm_hdg . $clm_html;	
			// finish the table and add a <p> 
			$str_html .= "</tbody></table>
				<p></p>";
			$str_html .="<p class=dpr_notice>Of $idx dpr claims, there were $rj_ct reported REJ and $ack_ct reported ACK </p>";
		} else {
			$str_html .="<p class=dpr_notice>All $idx dpr claims reported ACK (accepted)</p>";
		}
	} //
	//
	return $str_html;
}


/**
 * main function to process new dpr files 
 * 
 * @uses csv_newfile_list()
 * @uses csv_verify_file()
 * @uses csv_ebr_filetoarray()
 * @uses ibr_dpr_values()
 * @uses csv_write_record()
 * @uses ibr_dpr_html()
 * @param array  optional files array
 * @param bool   whether to create html output
 * @param bool   whether to only generate output for errors
 * @return string
 */
function ibr_dpr_process_new($files_ar=NULL, $html_out=TRUE, $err_only=TRUE ) {  	
	//
	$html_str = "";
	// get the new files in an array
	if ( is_array($files_ar) && count($files_ar) ) {	
		$f_list = $files_ar;
	} else {
		$f_list = csv_newfile_list('dpr');
	}
	//	
	// see if we got any
	if ( count($f_list) == 0 ) {
		if($html_out) { 
			$html_str .= "<p>No new DPR files found.</p>";
			return $html_str;
		} else {
			return false;
		}
	} else {
		$fdprcount = count($f_list);
	}
	// OK, so we have some files		
	$html_str = "";
	$ar_dpr = array();
	$chrc = 0;

	// sort ascending so latest files are last to be output
	$is_sort = asort($f_list);  // returns true on success
	//
	
	// Step 2: file data written to csv files
	//         also html string created if $html_out = TRUE
	foreach ($f_list as $f_name) {
		//
		$f_path = csv_verify_file($f_name, 'dpr');
		if ($f_path) {
			//$ar_dprfld = ibr_ebr_filetoarray($f_path);
            $ar_dprfld = csv_ebr_filetoarray($f_path);
			if ($ar_dprfld) {
				//
				$ar_dprval = ibr_dpr_values ($ar_dprfld, $f_path);
				// the $ar_dprfld array is array of arrays
				// one for each CST line, so we append them to our
				// array for csv and html output
				foreach($ar_dprval as $cst) {
					$ar_out[] = $cst;
					$ar_csv[] = ibr_dpr_csv_claims($cst);
				}
			} else {
			    $html_str .= "<p>Error: failed to parse $f_name </p>" .PHP_EOL;
				csv_edihist_log("ibr_dpr_process_new: failed to parse $f_name");
			}			 
		} else {
			$html_str .= "<p>Error: invalid path for $f_name </p>" .PHP_EOL;
			csv_edihist_log("ibr_dpr_process_new: invalid path for $f_name");
		}
	}
	//$ar_csv
	//$chrc += csv_write_record($ar_out, 'dpr', 'claim');
	$chrc += csv_write_record($ar_csv, 'dpr', 'claim');
	//
	if ($html_out) {
		$html_str .= ibr_dpr_html($ar_out, $err_only);
	} else {
		$html_str .= "DPR files: processed $fdprcount files <br />".PHP_EOL;
	}
	//
	return $html_str;
}


/* ********************  EBR/IBR functions ******************** */

/**
 * locate and retrieve the message for a claim in an ebr or ibr file
 * 
 * @uses csv_verify_file()
 * @uses csv_ebr_filetoarray()
 * @param string		filename
 * @param string        claim id (pid-encounter) or 'err' for all in file with errors
 * @return string       html formatted paragraph
 */
function ibr_ebr_message($ebrfile, $clm01, $batchnm = '') {
	// a particular encounter may appear more than once in an ibr or ebr file
	// if it is sent again in a new batch very quickly, depending on how the
	// clearinghouse aggregated its responses.
	// Therefore, we need to try and check for batch name match
	$str_html = '';
	//
	$ext = substr($ebrfile, -3);
	$fp = csv_verify_file($ebrfile, $ext, false);
	if ($fp) {
		$fname = basename($fp);
		$ext = strtolower(substr($fname, -4));
		//$ebr_ar = ibr_ebr_filetoarray ($fp);
        $ebr_ar = csv_ebr_filetoarray($fp);
	} else {
		csv_edihist_log("ibr_ebr_message: file read error $ebrfile");
		$str_html = "Unable to read file " . basename($ebrfile) . "<br />".PHP_EOL;
		return $str_html;
	}
	if (!is_array($ebr_ar) || !count($ebr_ar) ) {
		csv_edihist_log("ibr_ebr_message: file read error $ebrfile");
		$str_html = "Unable to read file " . basename($ebrfile) . "<br />".PHP_EOL;
		return $str_html;
	}
	$usebatch = ($batchnm) ? true : false;
	$isbatch = false;
	$isfound = false;
	$msgstr = '';
	foreach($ebr_ar as $fld) {
		// since true value can match '1'
		if (strval($fld[0]) === '1' && $usebatch) {
			$isbatch = ($ext == '.ibr') ? ($fld[15] == $batchnm) : ($fld[8] == $batchnm);
			$btnm = ($ext == '.ibr') ? $fld[15] : $fld[8];
			continue;
		}
		//
		if ($fld[0] == '3') {
			if ($usebatch & !$isbatch) { 
				$isfound = false;
				continue; 
			}
			$isfound = ($fld[4] == $clm01);
			if ($clm01 == 'any' || $isfound) {
				$nm = $fld[1];
				$pe = $fld[4];
				$sts = ($ext == '.ibr') ? $fld[11] : '';
				//
				if ($isfound && $ext == '.ibr' && $sts == 'A') {
					//
					$msgstr = "<p class='ibrmsg'>".PHP_EOL;
					$msgstr .= "$nm &nbsp;$pe <br />".PHP_EOL;
					$msgstr .= "$btnm <br />".PHP_EOL;
					$msgstr .= "Status: $sts<br />".PHP_EOL;
					$msgstr .= '</p>'.PHP_EOL;
				}
			}
			continue;
		}
		//
		if ($isfound) {
			// there should be only one of these three possibilities, but maybe more than one 3e
			if ($fld[0] == '3e') {
				//3e│Error Initiator│R│Error Code – if available, otherwise NA│Error Message | Loop│Segment ID│Element # ││││Version |
				// different for older 4010, one less field, so loop gives segment, segment gives element, element is blank 
				$sts = ($ext == '.ebr') ? $fld[2] :  $sts;
				$msgstr = "<p class='ibrmsg'>".PHP_EOL;
				$msgstr .= "$nm &nbsp;$pe <br />".PHP_EOL;
				$msgstr .= "$btnm <br />".PHP_EOL;
				$msgstr .= "Status: $sts<br />".PHP_EOL;
				$msgstr .= "Error Initiator: {$fld[1]} Code: {$fld[3]} <br />".PHP_EOL;
				$msgstr .= "Loop: {$fld[5]} Segment: {$fld[6]} Element: {$fld[7]} <br />".PHP_EOL;
				$msgstr .= wordwrap($fld[4], 46, "<br />".PHP_EOL);
				$msgstr .= "</p>".PHP_EOL;
			} elseif ($fld[0] == '3c') {
				$sts = ($ext == '.ebr') ? $fld[2] :  $sts;
				$msgstr = "<p class='ibrmsg'>".PHP_EOL;
				$msgstr .= "$nm &nbsp;$pe <br />".PHP_EOL;
				$msgstr .= "$btnm <br />".PHP_EOL;
				$msgstr .= "Status: $sts<br />".PHP_EOL;
				$msgstr .= wordwrap($fld[4], 46, '<br />'.PHP_EOL);
				$msgstr .= '</p>'.PHP_EOL;
			} elseif ($fld[0] == '3a') {
				//3a│Bill Type│Allowed Amount│Non-Covered Amount │Deductible Amount │Co-Pay Amount │Co-insurance Amount │Withhold
				//Amount │Estimated Payment Amount │Patient Liability│Message Code│Message Text││
				//
				$msgstr = "<p class='ibrmsg'>".PHP_EOL;
				$msgstr .= "$nm &nbsp;$pe <br />".PHP_EOL;
				$msgstr .= "$btnm <br />".PHP_EOL;
				$msgstr .= "Type: {$fld[1]} <br />".PHP_EOL;
				$msgstr .= ($fld[2] =='NA') ? "" : "Allowed: {$fld[2]}";
				$msgstr .= ($fld[8] =='NA') ? "" : " Payment: {$fld[8]}";
				$msgstr .= ($fld[9] =='NA') ? "<br />".PHP_EOL : " Pt Resp: {$fld[9]} <br />".PHP_EOL; 
				$msgstr .= ($fld[10] =='NA') ? "" : "Code: {$fld[10]} ";
				$msgstr .= ($fld[11] =='NA') ? "" : wordwrap($fld[11], 46, "<br />".PHP_EOL);
				$msgstr .= "</p>".PHP_EOL; 
			} 
		} elseif ($clm01 == 'any') {
			if ($usebatch & !$isbatch) { continue; }
			//
			if ($fld[0] == '3e') {
				// gather all errors
				$msgstr .= "<p class='ibrmsg'>".PHP_EOL;
				$msgstr .= "$nm &nbsp;$pe <br />".PHP_EOL;
				$msgstr .= "$btnm <br />".PHP_EOL;
				$msgstr .= "Error Initiator: {$fld[1]} Code: {$fld[3]} <br />".PHP_EOL;
				$msgstr .= "Loop: {$fld[5]} Segment: {$fld[6]} Element: {$fld[7]} <br />".PHP_EOL;
				$msgstr .= wordwrap($fld[4], 46, "<br />".PHP_EOL)."<br />".PHP_EOL;
				$msgstr .= "~~~~~~~~<br />".PHP_EOL;
				$msgstr .= "</p>".PHP_EOL;
			}
		}
		
	} // end foreach($ebr_ar as $fld)
	//
	$str_html .= $msgstr;
	//
	if (!$str_html) {
		$str_html .= "Did not find $clm01 in $fname <br />".PHP_EOL;
	}
	return $str_html;
}


/**
 * parse the ebr file format into an array
 * 
 * The array is indexed in different batch files, indicated by line '1'
 * So a new line will appear in the csv files table for each line '1' 
 * The structure is:
 * <pre>
 *   $ar_val[$b]['file'] 
 *      ['date']['f_name']['clrhsid']['batch']['clm_ct']['clm_rej']['chg_r']
 *
 *   $ar_val[$b]['claims'][$c]
 *      ['pt_name'] ['svcdate']['clm01']['status']['batch']['f_name']['payer'] 
 *      ['providerid']['prclmnum']['payerid'] 
 *       -- with  ['3c'] 
 *                  ['err_seg']['err_msg']
 *                ['3a']
 *                  ['pmt']['msg']
 *                ['3e'][i]
 *                  ['err_type']['err_code']['err_msg']['err_loop']['err_seg']['err_elem']
 * 
 * </pre>
 * 
 * @see ibr_ebr_process_new_files()
 * @uses ibr_ebr_ebt_name()
 * @uses csv_ebr_filetoarray()
 * @param string  path to ebr file
 * @return array
 */
function ibr_ebr_values($file_path) { 
	//
	// get file information
	if (is_readable($file_path)) {
		//  string setlocale ( int $category , array $locale ) // may be needed
		$path_parts = pathinfo($file_path);
	} else {
		// error, unable to read file
		csv_edihist_log("Error, unable to read file $file_path");
		return FALSE;
	}
	$path_parts = pathinfo($file_path);
	//$ebr_dir = $path_parts['dirname'];
	$ebr_fname = $path_parts['basename'];
	$ebr_ext = $path_parts['extension'];
	//
	if ($ebr_ext != 'ebr') {
		csv_edihist_log("ibr_ebr_values: incorrect file extension $ebr_ext $ebr_fname");
		return false;
	}
	//
	$ebr_mtime = date ("Ymd", filemtime($file_path));
	//
	$ar_val = array();
	//
	$clm_ct = 0;
	$rjct_ct = 0;
	$b = -1;
	$c = -1;
	// get file contents transformed to array
	//$ar_ebr = ibr_ebr_filetoarray ($file_path);
    $ar_ebr = csv_ebr_filetoarray($file_path);
	if (!is_array($ar_ebr) || !count($ar_ebr)) {
		csv_edihist_log("ibr_ibr_values: failed to read $ebr_fname");
		return false;
	}
	//		
	foreach($ar_ebr as $ln) {
		//
		if (strval($ln[0]) == '1') {
			//['ibr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch');
			$b++;
			$c = -1;
			//		
			if (preg_match('/\d{4}\D\d{2}\D\d{2}/', $ln[1])) {
				$fdate = preg_replace('/\D/', '', $ln[1]);
			} else {
				$fdate = $ebr_mtime;
			}
            $batch_name = $ln[8];
			//['date']['f_name']['clrhsid']['batch']['clm_ct']['clm_rej']['chg_r']
			$ar_val[$b]['file']['date'] = $fdate;			// ibr file date
			$ar_val[$b]['file']['f_name'] = $ebr_fname;
			$ar_val[$b]['file']['clrhsid'] = $ln[7]; 		// availity clearinghouse file id
			$ar_val[$b]['file']['batch'] = $batch_name;		// batch file name
			//
			$clm_ct = 0;
			$clm_acc = 0;
			$clm_acc_chg = 0;
			$clm_rej = 0;
			$clm_rej_chg = 0;
			//
			continue;
		}
		//
		if (strval($ln[0]) == '2') {
			$payer = $ln[1];
			//
			$clm_ct += intval($ln[2]);
			$clm_rej += intval($ln[6]);
			$clm_rej_chg += floatval($ln[7]);
			//
			$payerid = $ln[8];
			//
			$ar_val[$b]['file']['clm_ct'] = $clm_ct;		// claim count
			$ar_val[$b]['file']['clm_rej'] = $clm_rej;		// rejected claims count
			$ar_val[$b]['file']['chg_r'] = $clm_rej_chg; 	// rejected charges
			//
			continue;
		}
		//['pt_name'] ['svcdate']['clm01']['status']['batch']['f_name']['payer'] ['providerid']['prclmnum']['payerid']['3c']['3e']['3a']
		if (strval($ln[0]) == '3') {
			//['ibr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
			$c++;
			$err_ct = -1;
			//
			$ar_val[$b]['claims'][$c]['pt_name'] = $ln[1];
			$ar_val[$b]['claims'][$c]['svcdate'] = $ln[2];
			$ar_val[$b]['claims'][$c]['clm01'] = $ln[4];
			$ar_val[$b]['claims'][$c]['batch'] = $batch_name;
			$ar_val[$b]['claims'][$c]['f_name'] = $ebr_fname;
			$ar_val[$b]['claims'][$c]['payer'] = $payer;
			//
			$ar_val[$b]['claims'][$c]['providerid'] = $ln[6];
			$ar_val[$b]['claims'][$c]['prclmnum'] = $ln[8];
			$ar_val[$b]['claims'][$c]['payerid'] = $payerid;
			//
			continue;
		}
		//
		if (strval($ln[0]) == '3c') { 
			//
			$msg = ''; $err_seg = '';
			//
			if ($ln[2] != 'A' && $ln[3] == 'NA') { 
				$ar_val[$b]['claims'][$c]['status'] = 'A';
			} else {
				$ar_val[$b]['claims'][$c]['status'] = $ln[2];
			}
			//['err_seg']['err_msg']
			$err_seg .= (strlen($ln[5]) && $ln[5] != 'NA') ? $ln[5].' | ' : '';
			$err_seg.= (strlen($ln[6]) && $ln[6] != 'NA') ? $ln[6].' | ' : '';
			$err_seg .= (strlen($ln[7]) && $ln[7] != 'NA') ? $ln[7].' | ' : '';
			//
			$msg .= (strlen($ln[1]) && $ln[1] != 'NA') ? $ln[1].' ' : '';
			$msg .= (strlen($ln[2]) && $ln[2] != 'NA') ? 'Type: '.$ln[2].' ' : ''; 
			$msg .= (strlen($ln[4]) && $ln[4] != 'NA') ? $ln[4] : '';
			// 
			$ar_val[$b]['claims'][$c]['3c']['err_seg'] = $err_seg;
			$ar_val[$b]['claims'][$c]['3c']['err_msg'] = $msg;
			
			continue;
		}
		//
		if (strval($ln[0]) == '3e') {
			$err_ct++;
			//
			if ($ln[2] != 'R' && $ln[3] != 'NA') { 
				$ar_val[$b]['claims'][$c]['status'] = 'R';
			} else {
				$ar_val[$b]['claims'][$c]['status'] = $ln[2];
			}
			//['err_type']['err_code']['err_msg']['err_loop']['err_seg']['err_elem']	
			$ar_val[$b]['claims'][$c]['3e'][$err_ct]['err_type'] = $ln[1];           // Error Initiator
			$ar_val[$b]['claims'][$c]['3e'][$err_ct]['err_code'] = $ln[3]; 			// Error Code or NA
			$ar_val[$b]['claims'][$c]['3e'][$err_ct]['err_msg'] = $ln[4];	        // Error Message 
			$ar_val[$b]['claims'][$c]['3e'][$err_ct]['err_loop'] = $ln[5];           // Loop
			$ar_val[$b]['claims'][$c]['3e'][$err_ct]['err_seg'] = $ln[6];            // Segment ID
			$ar_val[$b]['claims'][$c]['3e'][$err_ct]['err_elem'] = $ln[7];           // Element #
			//
			continue;
		}
		//
		if (strval($ln[0]) == '3a') { 
			
			$ar_val[$b]['claims'][$c]['status'] = 'A';
			//				
			$msg = ''; $msg_txt = '';
			//['pmt']['msg']
			$msg .= (strlen($ln[1]) && $ln[1] != 'NA') ? 'Bill Type: '.$ln[1] : '';
			$msg .= (strlen($ln[2]) && $ln[2] != 'NA') ? 'Allowed: '.$ln[2] : '';
			$msg .= (strlen($ln[3]) && $ln[3] != 'NA') ? 'Non Covered: '.$ln[3] : '';
			$msg .= (strlen($ln[4]) && $ln[4] != 'NA') ? 'Deductible '.$ln[4] : '';
			$msg .= (strlen($ln[5]) && $ln[5] != 'NA') ? 'Co-Pay: '.$ln[5] : '';
			$msg .= (strlen($ln[6]) && $ln[6] != 'NA') ? 'Co-ins: '.$ln[6] : '';
			$msg .= (strlen($ln[7]) && $ln[7] != 'NA') ? 'Withhold: '.$ln[7] : '';
			$msg .= (strlen($ln[8]) && $ln[8] != 'NA') ? 'Est Pmt: '.$ln[8] : '';
			$msg .= (strlen($ln[9]) && $ln[9] != 'NA') ? 'Pt Rsp: '.$ln[9] : '';
			//
			$msg_txt .= (strlen($ln[10]) && $ln[10] != 'NA') ? 'Code: '.$ln[10] : '';
			$msg_txt .= (strlen($ln[11]) && $ln[11] != 'NA') ? ' '.$ln[11] : '';
			//
			$ar_val[$b]['claims'][$c]['3a']['pmt'] = $msg;
			$ar_val[$b]['claims'][$c]['3a']['msg'] = $msg_txt;
		}
	}
	//
	return $ar_val;
}


/**
 * parse the ibr file format into an array
 * 
 * Very similar to ibr_ebr_values(), with slight differences
 * The array is indexed in different batch files, indicated by line '1'.
 * So a new line will appear in the csv files table for each line '1'. 
 * The structure is:
 * <pre>
 *   $ar_val[$b]['file'] 
 *      ['date']['f_name']['clrhsid']['batch']['clm_ct']['clm_rej']['chg_r']
 *
 *   $ar_val[$b]['claims'][$c]
 *      ['pt_name'] ['svcdate']['clm01']['status']['batch']['f_name']['payer'] 
 *      ['providerid']['bht03']['payerid']
 *       -- with ['3e'][i]
 *                  ['err_type']['err_code']['err_msg']['err_loop']['err_seg']['err_elem']
 * 
 * </pre>
 * 
 * @see ibr_ebr_process_new_files()
 * @uses ibr_ebr_ebt_name()
 * @uses csv_ebr_filetoarray()
 * @param string  path to ibr file
 * @return array
 */			
function ibr_ibr_values($file_path) { 
	//
	// get file information
	if (is_readable($file_path)) {
		//  string setlocale ( int $category , array $locale ) // may be needed
		$path_parts = pathinfo($file_path);
	} else {
		// error, unable to read file
		csv_edihist_log("ibr_ibr_values: Error, unable to read file $file_path");
		return FALSE;
	}
	$path_parts = pathinfo($file_path);
	//$ibr_dir = $path_parts['dirname'];
	$ibr_fname = $path_parts['basename'];
	$ibr_ext = $path_parts['extension'];
	//
	if ($ibr_ext != 'ibr') {
		csv_edihist_log("ibr_ibr_values: incorrect file extension $ibr_ext $ibr_fname");
		return false;
	}
	//
	$ibr_mtime = date ("Ymd", filemtime($file_path));
	//
	$ar_ih = array();
	//
	//$clm_ct = 0;
	//$rjct_ct = 0;
	$b = -1;
	//$p = -1;
	$c = -1;
	// get file contents transformed to array
	//$ar_ibr = ibr_ebr_filetoarray ($file_path);
    $ar_ibr = csv_ebr_filetoarray($file_path);
	if (!is_array($ar_ibr) || !count($ar_ibr)) {
		csv_edihist_log("ibr_ibr_values: failed to read $ibr_fname");
		return false;
	}
	//		
	foreach($ar_ibr as $ln) {
		//
		if (strval($ln[0]) == '1') {
			//['ibr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch');
			$b++;
			$c = -1;
			//
			$batch_name = $ln[15];
			$batch_ctl = $ln[5];
			//
			if (preg_match('/\d{4}\D\d{2}\D\d{2}/', $ln[1])) {
				$fdate = preg_replace('/\D/', '', $ln[1]);
			} else {
				$fdate = $ibr_mtime;
			}
			$ar_ih[$b]['file']['date'] = $fdate;			// ibr file date
			$ar_ih[$b]['file']['f_name'] = $ibr_fname;
			$ar_ih[$b]['file']['btctln'] = $ln[5]; 			// batch control number [ISA13]
			$ar_ih[$b]['file']['clm_ct'] = $ln[6];			// claim count
			//$ar_ih[$b]['file']['chg_s'] = $ln[8]; 			// submitted charges total [CLM02 ?]
			$ar_ih[$b]['file']['clm_rej'] = $ln[10];			// rejected claims count
			$ar_ih[$b]['file']['chg_r'] = $ln[11]; 			// rejected charges
			$ar_ih[$b]['file']['clrhsid'] = $ln[14]; 		// availity clearinghouse file id
			$ar_ih[$b]['file']['batch'] = $ln[15];			// batch file name
			//
			continue;
		}
		//
		if (strval($ln[0]) == '2') {
			$payer = $ln[1];
			$payerid = $ln[8];
			//
			continue;
		}
		//['pt_name'] ['svcdate']['clm01']['status']['batch']['filename']['payer'] ['providerid']['bht03']['payerid']
		if (strval($ln[0]) == '3') {
			//['ibr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
			$c++;
			$err_ct = -1;
			//
			$ar_ih[$b]['claims'][$c]['pt_name'] = $ln[1];
			$ar_ih[$b]['claims'][$c]['svcdate'] = $ln[2];
			$ar_ih[$b]['claims'][$c]['clm01'] = $ln[4];
			$ar_ih[$b]['claims'][$c]['status'] = $ln[11];
			$ar_ih[$b]['claims'][$c]['batch'] = $batch_name;
			$ar_ih[$b]['claims'][$c]['f_name'] = $ibr_fname;
			$ar_ih[$b]['claims'][$c]['payer'] = $payer;
			//
			$ar_ih[$b]['claims'][$c]['providerid'] = $ln[6];
			$ar_ih[$b]['claims'][$c]['bht03'] = (strlen($ln[10]) >= 9) ? $ln[10] : $batch_ctl;
			$ar_ih[$b]['claims'][$c]['payerid'] = $payerid;
			//
			continue;
		}
		//
		if (strval($ln[0]) == "3e") { 
			// increment error count -- more than one error is possible
			// ibr files have a 3e only in error case, no 3a or 3c
			$err_ct++;
			//
			$ar_ih[$b]['claims'][$c]['3e'][$err_ct]['err_type'] = $ln[1];           // Error Initiator
			$ar_ih[$b]['claims'][$c]['3e'][$err_ct]['err_code'] = $ln[3]; 			// Error Code or NA
			$ar_ih[$b]['claims'][$c]['3e'][$err_ct]['err_msg'] = $ln[4];	        // Error Message 
			$ar_ih[$b]['claims'][$c]['3e'][$err_ct]['err_loop'] = $ln[5];           // Loop
			$ar_ih[$b]['claims'][$c]['3e'][$err_ct]['err_seg'] = $ln[6];            // Segment ID
			$ar_ih[$b]['claims'][$c]['3e'][$err_ct]['err_elem'] = $ln[7];           // Element #		
			//
			continue;
		}
	 } // end foreach
	 //
	 return $ar_ih;
}


/**
 * Create html table for displaying processing results
 * 
 * @param array $ar_data  array produced by function ibr_ebr_data_ar
 * @param $err_only boolean ignore claim information if no 3e subarray is in the claim data
 * @return string
 */
function ibr_ebr_html ($ar_data, $err_only=false) {
	// create an html string for a table to display in a web page
	//$ar_hd = $ar_data['head'];
	//$ar_cd = $ar_data['claims'];
	$idx = 0;
	$idf = 0;
	$has3 = false;
	$hasclm = false;
	$clm_html = "";
	$str_html = "";
	//
	$dtl = ($err_only) ? "Errors only" : "All included claims";
	//
	// the table heading for files
	$f_hdg = "<table cols=6 class=\"ibr_ebr\">
	   <caption>IBR-EBR Files Summary  {$dtl} </caption> 
	   <thead>
		   <tr>
			 <th>IBR-EBR File</th><th>Date</th>
			 <th>Batch</th><th>Claims</th><th>Rej</th><th>&nbsp;</th>
		   </tr>
		</thead>
		<tbody>".PHP_EOL;
	//
	// the details table heading
	$clm_hdg = "<table cols=6 class=\"ibr_ebr\">
	 <thead>
		<tr>
		  <th>Patient Name</th><th>Date</th><th>CtlNum</th>
		  <th>Status</th><th>Payer Name</th><th>Type Code Loop Segment Field</th>
		</tr>
		<tr>
		  <th colspan=6 align=left>Message</th>
		</tr>
	 </thead>
	 <tbody>".PHP_EOL;
	//
	// start with the table heading
	$str_html .= $f_hdg;
	//	
	foreach ($ar_data as $ardt) {
		// alternate colors
		$bgf = ($idf % 2 == 1 ) ? 'fodd' : 'feven';
		$idf++;
		//
		$ar_hd = isset($ardt['file']) ? $ardt['file'] : NULL ;
		$ar_cd = isset($ardt['claims']) ? $ardt['claims'] : NULL ;
		//
		if (!$ar_hd && !$ar_cd) {
			$str_html .= "ibr_ebr_html: empty array or wrong keys <br />" . PHP_EOL;
			continue;
		}
		// if we had a claim detail, we need to append the files heading
		if ($hasclm) { $str_html .= $f_hdg; }
		//
		// if any individual claims detail is to be output this will be set true
		$clm_html = "";	
		$has3 = FALSE;
		$hasclm = FALSE;
		//['date']['f_name']['availid']['batch']['clm_acc']['chg_s']['clm_rej']['chg_r']
		$str_html .= "<tr class=\"{$bgf}\">" .PHP_EOL;
		$str_html .= "<td>{$ar_hd['date']}</td>
			 <td><a target=\"_blank\" href=\"edi_history_main.php?fvkey={$ar_hd['f_name']}\">{$ar_hd['f_name']}</a> <a target=\"_blank\" href=\"edi_history_main.php?fvkey={$ar_hd['f_name']}&readable=yes\">Text</a></td>
			 <td><a target=\"_blank\" href=\"edi_history_main.php?fvkey={$ar_hd['batch']}\">{$ar_hd['batch']}</a></td>
			 <td>{$ar_hd['clm_ct']}</td>
			 <td>{$ar_hd['clm_rej']}</td>
			 <td>&nbsp;</td>" .PHP_EOL;
		$str_html .= "</tr>" .PHP_EOL;
		   //{$ar_hd['ft_name']}
	
		// now the individual claims details
		//['pt_name'] ['svcdate']['clm01']['status']['batch']['filename']['payer'] ['providerid']['bht03']['payerid']
		if ($ar_cd) {
			// create html output for the claims
			foreach ($ar_cd as $val) {
				// if $err_only, then only get information on claims with 3e 
				if ($err_only && !array_key_exists("3e", $val) ) { continue; }
				//
				// alternate row background colors
				$bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
				$idx++;
				// since we are here, we have claim details in the output
				$hasclm = TRUE;
				//
				$clm_html .= "<tr class=\"{$bgc}\">
				   <td>{$val['pt_name']}</td>
				   <td>{$val['svcdate']}</td>
				   <td><a class=\"btclm\" target=\"_blank\" href=\"edi_history_main.php?fvbatch={$val['batch']}&btpid={$val['clm01']}\">{$val['clm01']}</a></td>
				   <td><a class=\"clmstatus\" target=\"_blank\" href=\"edi_history_main.php?ebrfile={$val['f_name']}&ebrclm={$val['clm01']}\">{$val['status']}</a></td>
				   <td title=\"{$val['payerid']}\">{$val['payer_name']}</td>";
				   
				   // do not finish the row here, test for 3e, 3a, or 3c
		
				if (array_key_exists("3e", $val)) {
					// there may be more than one error reported
                    $clm_html .= "<td>&nbsp;</td>".PHP_EOL."</tr>".PHP_EOL;
					foreach ($val['3e'] as $er) {
                        $clm_html .= "<tr class=\"{$bgc}\">".PHP_EOL;
						$clm_html .= "<td>{$er['err_type']}&nbsp;{$er['err_code']}&nbsp;{$er['err_loop']}&nbsp;{$er['err_seg']}&nbsp;{$er['err_elem']}</td>
						    <td  colspan=5>{$er['err_msg']}</td>
						 </tr>".PHP_EOL;
					} // end foreach ($val['3e'] as $er)
				} elseif (array_key_exists("3a", $val)) { 
					$clm_html .= "<td>payment</td>
					  </tr>
					  <tr class=\"{$bgc}\">
					    <td colspan=6>{$val['3a']['msg']}</td>
					  </tr>
					  <tr class=\"{$bgc}\">
						<td colspan=6>{$val['3a']['msg_txt']}</td>
					  </tr>".PHP_EOL;
				} elseif (array_key_exists("3c", $val)) { 
					$clm_html .= "<td>{$val['3c']['err_seg']}</td>
		               </tr>
		               <tr class=\"{$bgc}\"> 
		                 <td colspan=6>{$val['3c']['err_msg']}</td>
		               </tr>".PHP_EOL;
				} else {
					// ibr files only report 3e
					$clm_html .= "<td> &nbsp;</td>".PHP_EOL."</tr>".PHP_EOL;
				}

			} // end foreach(($ar_cd as $val)
			//
			// if there were any claims detailed
			if ( $hasclm && strlen($clm_html) ) {
				$str_html .= "</tbody>".PHP_EOL;
				$str_html .= $clm_hdg . $clm_html;
			}
		} // end if ($ar_cd)
	} // end foreach ($ar_data as $ardt)
	//
	// finish the table and add a <p> 
	$str_html .= "</tbody></table>
	  <p></p>";
	//
	return $str_html;
}

/**
 * order array of file information for csv table
 * 
 * @param array  file array created elswhere
 * @return array
 */
function ibr_ebr_csv_files($head_ar) {
	// the file record csv file
	//['ebr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch'); 
    $f_file_data = array();
	$f_file_data[0] = $head_ar['date'];
	$f_file_data[1] = $head_ar['f_name'];
	// put file id as column 2 $f_file_data[2] = $head_ar['availid'] 
	$f_file_data[2] = $head_ar['clrhsid'];
	$f_file_data[3] = $head_ar['clm_ct'];	
	$f_file_data[4] = $head_ar['clm_rej'];
	$f_file_data[5] = $head_ar['batch'];
	//	
	return $f_file_data; 
}


/**
 * order the claim data array for csv file
 * 
 * @param array individual claim array
 * @return array
 */
function ibr_ebr_csv_claims($claim_ar) {
	// 
	$f_claim_data = array();
	//['ebr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer')
	$f_claim_data[0] = $claim_ar['pt_name'];
	$f_claim_data[1] = $claim_ar['svcdate'];
	$f_claim_data[2] = $claim_ar['clm01'];
	$f_claim_data[3] = $claim_ar['status'];
	$f_claim_data[5] = $claim_ar['batch'];
	$f_claim_data[6] = $claim_ar['f_name'];
	$f_claim_data[4] = $claim_ar['payer'];
	//	
	return $f_claim_data;
}
	
	
/**
 * the main function for ebr and ibr files in this script
 * 
 * @uses csv_newfile_list()
 * @uses csv_verify_file()
 * @uses csv_write_record()
 * @uses ibr_ebr_values()
 * @uses ibr_ibr_values()
 * @uses ibr_ebr_csv_files()
 * @uses ibr_ebr_csv_claims()
 * @param array              optional array of filenames or null
 * @param string $extension  one of ibr, or ebr; default is ibr
 * @param bool $html_out  true or false whether html output string should be created and returned
 * @param bool $err_only  true or false whether html output shows files and only errors or all claims
 * @return string|bool       html output string or boolean
 */		
function ibr_ebr_process_new_files($files_ar=NULL, $extension='ibr', $html_out=TRUE, $err_only=TRUE ) { 

	// process new ebr or ibr files by calling the functions in this script
	// three optional arguments to be passed to functions and used within
	//
	$html_str = "";
	$f_list = array();
	// patterned from ibr_batch_read.php
	if ( $files_ar === NULL || !is_array($files_ar) || count($files_ar) == 0) {
		$f_new = csv_newfile_list($extension);
	} elseif ( is_array($files_ar) && count($files_ar) ) {
		$f_new = $files_ar;
	}
	//
	if ( count($f_new) == 0 ) {
		if($html_out) { 
			$html_str .= "<p>ibr_ebr_process_new: no new $extension files <br />";
			return $html_str;
		} else {
			return false;
		}
	}
	// we have some new files
	// verify and get complete path
	foreach($f_new as $fbt) { 
		$fp = csv_verify_file($fbt, $extension, false);
		if ($fp) { $f_list[] = $fp; }
	}
	$fibrcount = count($f_list);	
	//		
	// initialize variables		
	$ar_htm = array();
	$data_ar = array();
	$wf = array();
	$wc = array();
	$chrf = 0;
	$chrc = 0;
	//
	// sort ascending so latest files are last to be output
	$is_sort = asort($f_list);  // returns true on success
	//
	// Step 2: file data written to csv files
	//         also html string created if $html_out = TRUE
	foreach ($f_list as $f_name) {
		// get the data array for the file
		// 
		// verify extension
		if (substr($f_name, -3) != $extension) {
			csv_edihist_log("ibr_ebr_process_new_files: type mismatch $extension " . basename($f_name));
			continue;
		}
		//
		if ($extension == 'ibr') {
			$data_vals = ibr_ibr_values($f_name);
		} elseif ($extension == 'ebr') {
			$data_vals = ibr_ebr_values($f_name);
		} else {
			csv_edihist_log("ibr_ebr_process_new_files: incorrect extension $ext " . basename($f_name));
			continue;
		}
		//
		if (is_array($data_vals) && count($data_vals)) {
			foreach($data_vals as $dm) {
				$wf[] = ibr_ebr_csv_files($dm['file']);
				foreach($dm['claims'] as $cl) {
					// array for csv
					$wc[] = ibr_ebr_csv_claims($cl);
				}
				//$data_ar[] = $dm;
			}
		}
	}
	//
	$chrf += csv_write_record($wf, $extension, "file");
	$chrc += csv_write_record($wc, $extension, "claim");
	//
	if ($html_out) { 
		//$html_str .= ibr_ebr_html ($data_ar, $err_only);
        $html_str .= ibr_ebr_html ($data_vals, $err_only);
	} else {
		$html_str .= "IBR/EBR files: processed $fibrcount $extension files <br />".PHP_EOL;
	}
	return $html_str;
}	


/**
 * generate output as if file is being processed
 * 
 * @param string  filename
 * @param bool    display errors only 
 * @return string
 */
function ibr_ebr_filetohtml($filepath, $err_only=false) {
	// simply create an html output for the file
	$html_str = "";
	$data_ar = array();
	$fp = false;
	$ext = substr($filepath, -3);
	if ( strpos('|ibr|ebr', $ext) ) {
		$fp = csv_verify_file( $filepath, "ebr");
	} 

	if ($fp) {
		if ($ext == 'ebr') {
			$data_ar = ibr_ebr_values($fp);
		} elseif ($ext == 'ibr') {
			$data_ar = ibr_ibr_values($fp);
		} else {
			csv_edihist_log ("ibr_ebr_filetohtml: invalid extension $ext " . basename($fp) );
			return "<p>invalid extension $ext </p>".PHP_EOL;
		}
		//
		$html_str .= ibr_ebr_html ($data_ar, $err_only);
	} else {	
		csv_edihist_log ("ibr_ebr_filetohtml: verification failed $filepath");
		$html_str .= "Error, validation failed $filepath <br />";
	} 
	//
	return $html_str;
}

?>
