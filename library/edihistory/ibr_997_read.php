<?php
/**
 * ibr_997_read.php 
 * 
 * Copyright 2012 Kevin McCormick, Longview, Texas
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA. 
 *   <http://opensource.org/licenses/gpl-license.php>
 * 
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
 
//
////////////////////////////////////////////////////////////////
// a security measure to prevent direct web access to this file
// must be accessed through the main calling script ibr_history.php 
// from admin at rune-city dot com;  found in php manual
// if (!defined('SITE_IN')) die('Direct access not allowed!');
//
///////////////////////////////////////////////////////////////

/**
 * error code values in AK or IK segments
 * 
 * @param string  the segment field ak304, ak403, ak501
 * @param string  the code
 * @return string
 */
function ibr_997_code_text ( $ak_seg_field, $ak_code ) {
	// the Availity 997 file has codes with certain errors
	// which correspond to the messages in these arrays
	//
	$aktext['ak304'] = array(
	        '1' => 'Unrecognized segment ID',
			'2' => 'Unexpected segment',
			'3' => 'Mandatory segment missing',
			'4' => 'Loop occurs over maximum times',
			'5' => 'Segment exceeds maximum use',
			'6' => 'Segment not in defined transaction set',
			'7' => 'Segment not in proper sequence',
			'8' => 'Segment has field errors',
			'I4' => 'Segment not used in implementation',
			'I6' => 'Implementation dependent segment missing',
			'I7' => 'Implementation loop occurs less than minimum times',
			'I8' => 'Implementation segment below minimum use',
			'I9' => 'Implementation dependent not used segment present'
			);
	
	$aktext['ak403'] = array(
	       '1' => 'Mandatory data element missing',
		   '2' => 'Conditional required data element missing',
		   '3' => 'Too many data elements',
		   '4' => 'Data element too short',
		   '5' => 'Data element too long',
		   '6' => 'Invalid character in data element',
		   '7' => 'Invalid code value',
		   '8' => 'Invalid date',
		   '9' => 'Invalid time',
		   '10' => 'Exclusion condition violated - segment includes two values that should not occur together',
		   '12' => 'Too many repetitions', 
		   '13' => 'Too many components',
		   'I10' => 'Implementation not used',
		   'I11' => 'Implementation too few repetitions',
		   'I12' => 'Implementation pattern match failure',
		   'I13' => 'Implementation dependent not used data element present',
		   'I6' => 'Code value not used in implimentation',
		   'I9' => 'Implementation dependent data element missing'	
		   );

	$aktext['ak501'] = array(
	       'A' => 'Accepted advised',
		   'E' => 'Accepted, but errors were noted',
		   'M' => 'Rejected, message authentication code (MAC) failed',
		   'P' => 'Partially Accepted',
		   'R' => 'Rejected advised',
		   'W' => 'Rejected, assurance failed validity tests',
		   'X' => 'Rejected, content after decryption could not be analyzed'
		   );
				   
	 $aktext['ak502'] = array(
	     '1' => 'Functional Group not supported',
         '2' => 'Functional Group Version not supported',
         '3' => 'Functional Group Trailer missing',
         '4' => 'Group Control Number in the Functional Group Header and Trailer do not agree',
         '5' => 'Number of included Transaction Sets does not match actual count',
         '6' => 'Group Control Number violates syntax',
         '10' => 'Authentication Key Name unknown',
         '11' => 'Encryption Key Name unknown',
         '12' => 'Requested Service (Authentication or Encryption) not available',
         '13' => 'Unknown security recipient',
         '14' => 'Unknown security originator',
         '15' => 'Syntax error in decrypted text',
         '16' => 'Security not supported',
         '17' => 'Incorrect message length (Encryption only)',
         '18' => 'Message authentication code failed',
         '19' => 'Functional Group Control Number not unique within Interchange',
         '23' => 'S3E Security End Segment missing for S3S Security Start Segment',
         '24' => 'S3S Security Start Segment missing for S3E Security End Segment',
         '25' => 'S4E Security End Segment missing for S4S Security Start Segment', 
         '26' => 'S4S Security Start Segment missing for S4E Security End Segment',
         'I6' => 'Implementation dependent segment missing',
         );
				   
	if ( array_key_exists($ak_seg_field, $aktext) && array_key_exists($ak_code, $aktext[$ak_seg_field]) ){
		return $aktext[$ak_seg_field][$ak_code];
	} else {
		return "";
	}
} // end function ibr_997_code_text


/**
 * code values for TA1 segment
 * 
 * @param string  the code
 * @return string
 */
function ibr_997_ta1_code($code) {
	// codes in TA1 segment elements 4 and 5, since codes are distinct form, all values in one array
		
	$ta1code = array('A' => 'Interchange accepted with no errors.',
		'R' => 'Interchange rejected because of errors. Sender must resubmit file.',
		'E' => 'Interchange accepted, but errors are noted. Sender must not resubmit file.',
		'000' => 'No error',
		'001' => 'The Interchange Control Number in the header and trailer do not match. Use the value from the header in the acknowledgment.',
		'002' => 'This Standard as noted in the Control Standards Identifier is not supported.',
		'003' => 'This Version of the controls is not supported',
		'004' => 'The Segment Terminator is invalid',
		'005' => 'Invalid Interchange ID Qualifier for sender',
		'006' => 'Invalid Interchange Sender ID',
		'007' => 'Invalid Interchange ID Qualifier for receiver',
		'008' => 'Invalid Interchange Receiver ID',
		'009' => 'Unknown Interchange Receiver ID',
		'010' => 'Invalid Authorization Information Qualifier value',
		'011' => 'Invalid Authorization Information value',
		'012' => 'Invalid Security Information Qualifier value',
		'013' => 'Invalid Security Information value',
		'014' => 'Invalid Interchange Date value',
		'015' => 'Invalid Interchange Time value',
		'016' => 'Invalid Interchange Standards Identifier value',
		'017' => 'Invalid Interchange Version ID value',
		'018' => 'Invalid Interchange Control Number',
		'019' => 'Invalid Acknowledgment Requested value',
		'020' => 'Invalid Test Indicator value',
		'021' => 'Invalid Number of Included Group value',
		'022' => 'Invalid control structure',
		'023' => 'Improper (Premature) end-of-file (Transmission)',
		'024' => 'Invalid Interchange Content (e.g., invalid GS Segment)',
		'025' => 'Duplicate Interchange Control Number',
		'026' => 'Invalid Data Element Separator',
		'027' => 'Invalid Component Element Separator',
		'028' => 'Invalid delivery date in Deferred Delivery Request',
		'029' => 'Invalid delivery time in Deferred Delivery Request',
		'030' => 'Invalid delivery time Code in Deferred Delivery Request',
		'031' => 'Invalid grade of Service Code'
		);
	if ( array_key_exists($code, $ta1code) ) {
		return 	$ta1code[$code];
	} else {
		return "Code $code not found in TA1 codes table. <br />";
	}
}	
	

/**
 * parse x12 997/999 file into an array
 * 
 * <pre>
 * return array['file']['key']
 *     ['f997_time']['f997_file']['f997_97t']['f997_ta1ctrl']
 *     ['f997_initver']['f997_clm_reject']['f997_clm_count']
 *     ['f997_clm_indc']['f997_batch']
 * return array['claims']['key']
 *     ['clmr_status']['clmr_ftime']['clmr_st_num']['clmr_control_num']
 *     ['clmr_997file']['clmr_97T']['batch_file']['clmr_gs_num']
 *     ['clmr_errseg']['clmr_errtxt']['clmr_busunit']['clmr_errcopy']
 * </pre>
 * 
 * @todo refine data collection -- need experience to know what to keep
 * @uses csv_file_by_controlnum()
 * @param array $seg_ar -- the segment array produced by csv_x12_segments
 * @return array
 */
function ibr_997_parse($seg_ar) {
	 //
	 $ar_997_segments = $seg_ar['segments'];
	 $fname = basename($seg_ar['path']);
	 $elem_d = $seg_ar['delimiters']['e'];
	 $sub_d = $seg_ar['delimiters']['s'];
	 $rep_d = $seg_ar['delimiters']['r'];
	 //
	 $ar_var = array();
	 $ar_reject = array();
	 $trans_id = "";
	 $loopid = "";
	 //
	 $bt_file = "";
	 $ta1ctlnum = "";    // IMHO, 997/999 files are pretty worthless without TA1, but just in case
	 //
	 $ta1date = "";
	 $ta1time = "";
	 $ta1ack = "";
	 $ta1note = "";
	 //
	 $st_ct = 0; $idx = 0;
	 //
	 $ar_997file = array();
	 //$f97T_name = $fname;
	 if (substr($fname, -4) == ".997") { $f97T_name = str_replace ( ".997", ".97T", $fname); }
	 if (substr($fname, -4) == ".999") { $f97T_name = str_replace ( ".999", ".99T", $fname); }
	 //		  
	 foreach($ar_997_segments as $seg_997 ) {
		 //
		 $idx++;
		 $ar_seg = explode($elem_d,	$seg_997);
		 //
		 // evaluate particular segments
		if ( $ar_seg[0] == "ISA" ) { 
		    // ISA segment -- get submitter ID, sender ID
			$isasender = $ar_seg[6];        // AV09311993
			$isareciever = $ar_seg[8];      // 030240928 
			$isadate = $ar_seg[9];
			$isatime = $ar_seg[10];
			$repseparator = ($ar_seg[11] == "U") ? "" : $ar_seg[11];   // 999 expect "^"
			$isactlver = $ar_seg[12];
			$isactlnum = $ar_seg[13];      // Must match IEA02
			$sub_elem = $ar_seg[16];
			//
			continue;									 
		}
		
		if ( $ar_seg[0] == "TA1" ) { 
			// TA1 segment -- get the control number$ta1ctlnum$ta1date$ta1time$ta1ack$ta1note$bt_file
			//$ta1ctlnum = strval($ar_seg[1]);      // Interchange Control Number --> batch file ISA13
			// all digits, possible leading zero, must be a string
			$ta1ctlnum = strval($ar_seg[1]); 
			$ta1date = $ar_seg[2];  
			$ta1time = $ar_seg[3]; 
			$ta1ack = $ar_seg[4];
			if ($ar_seg[4] == "E") { $ta104msg = "Errors - correct individually"; }
			if ($ar_seg[4] == "R") { $ta104msg = "Errors - correct and resubmit file"; }
			$ta1note = $ar_seg[5];
			if ($ar_seg[5] != "000") { $ta105msg = ibr_997_ta1_code($ar_seg[5]); }
			//$ta1ack $ta1note$ta104msg$ta105msg
			// get the batch file from function in batch_file_csv.php
			//$bt_file = ibr_batch_find_file_with_controlnum($ta1ctlnum);
            $bt_file = csv_file_by_controlnum('batch', $ta1ctlnum);
			if (!$bt_file) { $bt_file = $ta1ctlnum; }
			//
			continue;
		}
		
		if ( $ar_seg[0] == "GS" ) { 
			$gsid = $ar_seg[1];          // FA or 999 Implementation Acknowledgement (5010A1)
			$gssender = $ar_seg[2];      // 
			$gsreciever = $ar_seg[3];    //
			$gsdate = $ar_seg[4]; 
			$gstime = $ar_seg[5]; 
			//
			continue;
		}
		
		if ( $ar_seg[0] == "GE" ) { 
			$gecount = $ar_seg[1];
			$gectlnum = $ar_seg[2];
			if ($gecount != $st_ct) {
				csv_edihist_log("ibr_997_parse: ST count mismatch $gecount $st_ct in $fname");
				echo "ibr_997_parse: ST count mismatch $gecount $st_ct in $fname".PHP_EOL;
			}
			//
			continue;
		}
		
		if ( $ar_seg[0] == "ST" ) { 
			$stversion = $ar_seg[1];		// 997 or 999
			$stnum = $ar_seg[2]; 			// count of ST segments in this file 
			$st_ct++;
			$idx = 1;
			//
			continue;
		};
		
		if ( $ar_seg[0] == "SE" ) { 
			$secount = $ar_seg[1]; 			// count of segments in the ST block
			$senum = $ar_seg[2]; 			// should match $stnum or ST02
			if ($secount != $idx) {
				csv_edihist_log("ibr_997_parse: Segment count mismatch $idx $secount in $fname");
				echo "ibr_997_parse: Segment count mismatch $idx $secount in $fname".PHP_EOL;
			}
			//
			continue;
		}
		
		if ( $ar_seg[0] == "AK1" ) { 
			// beginning of acknowledgments
			$ak1ver = $ar_seg[1];         // GS01 from batch, expect HC
			$ak1gs06 = $ar_seg[2];        // AK102 = GS06 from batch, expect 1
			//
			$loopid = "0";
			continue;
		}
		
		if ( $ar_seg[0] == "AK9" ) { 
			// end of acknowledgments
			$ak9status = $ar_seg[1];      // A, R, or P, or E
			$ak9batchct = $ar_seg[2];     // transaction count in in batch file
			$ak9processed = $ar_seg[3];   // transaction count by Availity
			$ak9acceptct = $ar_seg[4];    // transactions accepted by Availity
			// ignore AK905 -- do not check for header errors
			// since Availity says this is rarely reported
			// and will probably produce an ACK response anyway
			//
			//echo "AK9: {$ar_seg[1]} {$ar_seg[2]} {$ar_seg[3]} {$ar_seg[4]} " . PHP_EOL;
			continue;
		}	
			
		if ( $ar_seg[0] == "AK2" ) { 
			// this segment describes accept/reject status of each claim
			// if 276 claim status is sent by OpenEMR, 
			// then need to account for that possibility here
			//   it is probably to sent results to a different csv file
			//
			$ak2version = $ar_seg[1];   // 837 or 276 assume 837 here
			$ak2st02 = sprintf("%04d", $ar_seg[2]);      // AK202 = ST02 in batch file ST segment
			//
			// reset values for new entry
			$ak5statustxt = "";
			$ak3seg = "";     // error segment
			$ak3line = "";    // error segment position (segments from ST = 1)
			$ak3loop = "";    // loop identifier code e.g. 2300B
			$ak3type = "";    // error code
			$ak4pos = "";
			$ak3typetxt = "";
			$ak3msg = "";
			//
			$ctx301 = "";  // business unit (CLM01, NM1, etc.)
			$ctx302 = "";  // business unit value  pid-enctr, name, etc.
			$ctx303 = "";  // probably not used
			$ctx401 = "";  // business unit (CLM01, NM1, etc.)
			$ctx402 = "";  // business unit value  pid-enctr
			$ctx403 = "";  // probably not used	
			$ctx_ct = 0;
			$ctx01 = "";		
			//
			$ak4pos = "";        // IK401-1 offending element position in segment
			$ak4comppos = "";    // IK401-2 offending component position in element
			$ak4rep = "";        // IK401-3 offending component repetition position
			$ak4elem = "";       // IK402 offending component repetition position      
			$ak4cause = "";      // IK403 code for error
			$ak4causetxt = "";   // text explanation from ibr_997_code_txt array
			$ak4data = "";       // IK404 copy of bad data element
			$ak4msg = "";
			//
			$ak5status = "";     // status of transaction set
			//
			// these are just left here in case it turns out that the IK5 segment
			// has more useful information than just status--like testing new batch generators 
			//$ak501 = "";         // status of transaction set
			//$ak502 = "";		 // implementation error found
			//$ak503 = ""; 		 // transaction set syntax error code
			//$ak504 = "";		 // transaction set syntax error code
			//$ak505 = "";		 // transaction set syntax error code
			//$ak506 = "";		 // transaction set syntax error code
			//
			$loopid = "2000";
			//			
			continue;
		}
		
		// Segment IK3 in the 5010 999 replaces AK3 in 4010 997
		// If IK3, then new segment CTX may appear		
		if ( $ar_seg[0] == "AK3" || $ar_seg[0] == "IK3" ) { 
			$ak3seg = $ar_seg[1];                     // segment name with error in batch file
			$ak3line= $ar_seg[2];                     // segment line in ST...SE in batch file
			$ak3loop = $ar_seg[3];					  // loop identifier code e.g. 2300B
			$ak3type = $ar_seg[4];		              // error code
			// call $ak304($ak3type); for text
			$ak3typetxt = ibr_997_code_text('ak304', $ak3type);
			// 
			$ak3msg .= $ak3loop . ' ' . $ak3seg . ' ' . $ak3line;
			// set loopid and reset ctx segment count
			$loopid = "2100";
			$ctx_ct = 0;
			continue;
		}

		// If IK4, then no AK4, IK4 in 999 replaces AK4 997
		// If IK4, then segment CTX may appear	
		if ( $ar_seg[0] == "AK4" || $ar_seg[0] == "IK4") { 
			// IK401 is possibly a composite element $ik4pos:$ik4comppos:$ik4rep
			// for now, just get the first part, the element, such as '2' for error in N302
			$sub = strpos($ar_seg[1], $sub_d);
			if ($sub) {
				$ak4pos = explode($sub_d, $ar_seg[1]);
				$ak4msg .= ($ak4msg) ? ' pos '.$ak4pos[0] : 'pos '.$ak4pos[0];
				$ak4msg .= (isset($ak4pos[1])) ? ' sub '.$ak4pos[1] : '';
				$ak4msg .= (isset($ak4pos[2])) ? ' rep '.$ak4pos[2] : '';
			} else {
				$ak4msg .= ($ak4msg) ? ' pos '.$ar_seg[1] : 'pos '.$ar_seg[1];
			}
			$ak4elem = $ar_seg[2];                   // Data element numbers defined in X12N Data Element Dictionary
			$ak4cause = $ar_seg[3];                  // error code
			// call $ak403($ak4cause); for text
			$ak4causetxt = ibr_997_code_text ("ak403", $ak4cause );
			if ( array_key_exists(4, $ar_seg) ) { $ak4data = $ar_seg[4]; }
			//
			
			$loopid = "2110";
			continue;
		}		
		
		if ($ar_seg[0] == "CTX" ) {
			// SITUATIONAL TRIGGER 
			//   CTX02 is segment ID CTX03 is segment count 
			// Business Unit Identifier i.e. element ID
			//  CTX01 is components ELEM:VALUE, ex. CLM01:pid-encounter
			//  response to 269 270 271 274 276 277 835 then CTX01 = TRN02
			//  response to 274 275 278 then CTX01 = NM109
			//  response to 837 then CTX01 = CLM01 
			if ($loopid == "2100") {
				if ( strpos($ar_seg[1], 'SITUATIONAL') ) {
					// try and get the segment ID and segment number
					// there is more, but it is overkill for us
					$ctx301 .= isset($ar_seg[2]) ? $ar_seg[2] : '';
					$ctx301 .= isset($ar_seg[3]) ? ' ' . $ar_seg[3] . ' ': '';
					continue;
				}
				$sub = strpos($ar_seg[1], $sub_d);
				if ($sub) { 
					// Business Unit Identifier
					$ctx301 .= substr($ar_seg[1], 0, $sub);    // business unit (CLM01, NM1, etc.)
					$ctx302 .= substr($ar_seg[1], $sub+1);     // business unit value  pid-enctr
					
				} else {
					$ctx301 .= $ar_seg[1];
				}
				continue;
			} elseif ($loopid == "2110") {
				if ( strpos($ar_seg[1], 'SITUATIONAL') ) {
					// try and get the segment ID and segment number
					// there is more, but it is overkill for us
					$ctx401 .= isset($ar_seg[2]) ? $ar_seg[2] : '';
					$ctx401 .= isset($ar_seg[3]) ? ' ' . $ar_seg[3] . ' ': '';
				} else {
					$sub = strpos($ar_seg[1], $sub_d);
					if ($sub) {
						$ctx401 .= substr($ar_seg[1], 0, $sub);  // business unit (CLM01, NM1, etc.)
						$ctx402 .= substr($ar_seg[1], $sub+1);     // business unit value  pid-enctr
					} else {
						$ctx401 .= $ar_seg[1];
					}
				}
				continue;
			} else {
				$ctx01 = $ar_seg[1];
				if ( array_key_exists(2, $ar_seg) ) {$ctx02 = $ar_seg[2]; } 
				
			}
			// increment ctx count, not used, but maybe in future revisions
			$ctx_ct++;
		    //
		    continue;
		}

		if ( $ar_seg[0] == "IK5" || $ar_seg[0] == "AK5" ) { 
			$ak5status = $ar_seg[1];      // A, E, or R
			$ak5statustxt = ibr_997_code_text ("ak501", $ak5status);
			// Only report information on errors
			// since AK5 gives the status of each claim
			// we take this as the sign to gather error information
			// Problems with accepted claims show up as .ebr rejects or claim denials
			if ($ak5status != "A" ) {
				// not perfect: claim rejected or issue noted 
				// index array
				$rct = count($ar_reject);
				// get more information on the claim
				// possibly add check for $isadate, since 999 date must be >= batch date
                $cml_info = ibr_batch_get_st_info($ak2st02, $bt_file);
				if (is_array($cml_info)) {
					$pt_name = $cml_info[2].', '.$cml_info[3];
					if (!$bt_file) { $bt_file = $cml_info[4]; }
					$bt_svcd = $cml_info[5];
					if (!$ctx302) { $ctx302 = $cml_info[1]; }			
				} else {
					if (!$ctx302) { $ctx302 = $ta1ctlnum.$ak2st02; } 
				} 
				//array('PtName', 'SvcDate', 'clm01', 'Status', 'ak_num', 'err_seg', 'File_997', 'Ctn_837', 'err_copy', 'FileTxt'); 
				$ar_reject[$rct]['pt_name'] = isset($pt_name) ? $pt_name : "NF";
				$ar_reject[$rct]['svc_date'] = isset($bt_svcd) ? $bt_svcd : "NF";
				$ar_reject[$rct]['pid_enctr'] = isset($ctx302) ? $ctx302 : "NF";
				$ar_reject[$rct]['clm_status'] = $ak5status;
				$ar_reject[$rct]['ak_num'] = $ak2st02;
				$ar_reject[$rct]['err_seg'] = $ak3msg.' '.$ak4msg;
				$ar_reject[$rct]['file_997'] = $fname;
				$ar_reject[$rct]['btcntrl'] = $ta1ctlnum;
				$ar_reject[$rct]['err_copy'] = $ak3typetxt . ' | ' . $ak4causetxt;
				$ar_reject[$rct]['file_97T'] = $f97T_name;
				//
			}
			//
			continue;
		} //
							
		if ( $ar_seg[0] == "IEA" ) { 
			$ieacount = $ar_seg[1];
			$ieactlnum = $ar_seg[2];
			if ($ieactlnum == $isactlnum) {
				// we are done
				$isa_match = TRUE;
			} else {
				echo "ibr_997_parse Error: IEA Segment did not match to ISA segment.";
			}
			//
			continue;
		}		
		
	}  // end foreach($ar_997 as $seg )
	//
	$rjct_ct = $ak9processed - $ak9acceptct;
	//
	$ar_997file['f997_time'] = isset($gsdate) ? $gsdate : $ftime; 
	$ar_997file['f997_file'] = isset($fname) ?  $fname  : '';                
	$ar_997file['f997_isactl'] = isset($isactlnum) ? $isactlnum : '';
	$ar_997file['f997_ta1ctrl'] =  isset($ta1ctlnum) ? $ta1ctlnum : '';
	$ar_997file['f997_initver'] = isset($ak2version ) ? $ak2version : $ta1ack;
	$ar_997file['f997_clm_reject'] = isset($rjct_ct) ? $rjct_ct : '';
	$ar_997file['f997_clm_count'] = isset($ak9processed) ? $ak9processed : '';
	$ar_997file['f997_clm_indc'] = isset($ak9batchct) ? $ak9batchct : '';
	$ar_997file['f997_batch'] = isset($bt_file) ? $bt_file  : ''; 
    //
	return array('file' => $ar_997file, 'claims' => $ar_reject);
 }  // end function ibr_997_parse($path_997)

/**
 * trim the claims and files array down for csv file 
 * 
 * @param array
 * @param string
 * @return array
 */
function ibr_997_data_csv($ar_claims, $type='claim') {
	// trim the claims array down for csv file 
	$ar_csv = array();
	//
	if (!is_array($ar_claims) || ! count($ar_claims) ) {
		return false;
	}
	$idx=0;
	if ($type == 'claim') {
		foreach($ar_claims as $rjc) {
			$ar_csv[$idx]['pt_name'] = $rjc['pt_name'];
			$ar_csv[$idx]['svc_date'] = $rjc['svc_date'];
			$ar_csv[$idx]['pid_enctr'] = $rjc['pid_enctr'];
			$ar_csv[$idx]['clm_status'] = $rjc['clm_status'];
			$ar_csv[$idx]['ak_num'] = $rjc['ak_num'];
			$ar_csv[$idx]['file_997'] = $rjc['file_997'];
			$ar_csv[$idx]['btcntrl'] = $rjc['btcntrl'];
			$ar_csv[$idx]['err_seg'] = $rjc['err_seg'];
			//
			$idx++;
		}
	} else {
		// files -- once for each file
		$ar_csv['f997_time'] = $ar_claims['f997_time']; 
		$ar_csv['f997_file'] = $ar_claims['f997_file'];                
		$ar_csv['f997_isactl'] = $ar_claims['f997_isactl'];
		$ar_csv['f997_ta1ctrl'] = $ar_claims['f997_ta1ctrl'];
		$ar_csv['f997_clm_reject'] = $ar_claims['f997_clm_reject'];
	}
	//		
	return $ar_csv;
}


/**
 * create html table summarizing the 997/999 file
 * 
 * @param array $ar_data
 * @param bool $err_only
 * @return string
 */
function ibr_997_file_data_html($ar_data, $err_only=TRUE) {
	//
	$idx = 0;
	$idf = 0;
	$clm_html = "";
	//
	//  File Name  97T Name  Control Num  Claims  Rejected
	$str_html = "<table class=\"f997\" cols=6><caption>997 Files Summary</caption>
	   <thead>
	   <tr>
		 <th>File Name</th><th>Batch Ctl Num</th>
		 <th>Claims</th><th>Rejected</th><th>Batch</th>
	   </tr>
	   </thead>";
    //
    foreach ($ar_data as $arh ) { 
		// alternate
		$bgf = ($idf % 2 == 1) ? 'odd' : 'even';
		$idf++;
		// do the files table first, then add reject claims, if any
		$ar_df = $arh['file'];
		$fname = $ar_df['f997_file'];
	    //
	    // file information row<a href=\"edi_history_main.php?fvkey={$ar_hd['filename']}\" target=\"_blank\">{$ar_hd['filename']}</a></td>
	    $str_html .= "<tbody>
	       <tr class=\"{$bgf}\">
			<td><a target=\"_blank\" href=\"edi_history_main.php?fvkey={$ar_df['f997_file']}\">{$ar_df['f997_file']}</a> &nbsp; <a target=\"_blank\" href=\"edi_history_main.php?fvkey={$ar_df['f997_file']}&readable=yes\">Text</a></td>
			<td><a target=\"_blank\" href=\"edi_history_main.php?btctln={$ar_df['f997_ta1ctrl']}\">{$ar_df['f997_ta1ctrl']}</a></td>
			<td>{$ar_df['f997_clm_count']}</td>
			<td><a  class=\"clmstatus\" target=\"_blank\" href=\"edi_history_main.php?fv997={$ar_df['f997_file']}&err997=yes\">{$ar_df['f997_clm_reject']}</a></td>
			<td>{$ar_df['f997_batch']}</td>
		  </tr>";
	    //
	    // rejected claims information row
	    if ( isset($arh['claims']) && count($arh['claims']) ) {
			//array('PtName', 'SvcDate', 'clm01', 'Status', 'ak_num', 'err_seg', 'File_997', 'Ctn_837', 'err_copy', 'FileTxt'); 
			//['pt_name']['svc_date']['pid_enctr'] ['clm_status']['ak_num']['err_seg']['file_997']['cntrl_num']['err_copy']['file_97T']			 
			foreach ($arh['claims'] as $clmr) { 
				if ($err_only) { if ($clmr['clm_status'] != "R") continue; }
				// alternate
				$bgc = ($idx % 2 == 1) ? 'odd' : 'even';
				$idx++;
				//'pt_name''date''batch_file''cntrl_num''st_num''clm_status''pid_enctr''err_elem''err_seg''err_copy''file_text'
				$batchctln = isset($clmr['btcntrl']) ? $clmr['btcntrl'] : '';
				//
				$clm_html .= "<tr class=\"{$bgc}\">";
	            $clm_html .= isset($clmr['pt_name']) ? "<td>{$clmr['pt_name']}</td>" : "<td>&nbsp;</td>";
	            $clm_html .= isset($clmr['pid_enctr']) ? "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch=$batchctln&btpid={$clmr['pid_enctr']}&stnum={$clmr['ak_num']}'>{$clmr['pid_enctr']}</a></td>" : "<td>&nbsp;</td>";
	            $clm_html .= isset($clmr['clm_status']) ? "<td>{$clmr['clm_status']}</td>" : "<td>&nbsp;</td>";
	            $clm_html .= isset($clmr['ak_num']) ? "<td><a class='clmstatus' href='edi_history_main.php?fv997={$clmr['file_997']}&aknum={$clmr['ak_num']}' target='_blank'>{$clmr['ak_num']}</a></td>" : "<td>&nbsp;</td>";
	            $clm_html .= isset($clmr['batch_file']) ? "<td><a target='_blank' href='edi_history_main.php?fvkey={$clmr['batch_file']}'>{$clmr['batch_file']}</a></td>" : "<td>&nbsp;</td>";
	            $clm_html .= isset($clmr['err_seg']) ? "<td>{$clmr['err_seg']}</td> </tr>" : "<td>&nbsp;</td> </tr>";
	            //
	            $clm_html .= isset($clmr['err_copy']) ? "   <tr class=\"{$bgc}\"><td colspan=6> &nbsp {$clmr['err_copy']}</td></tr>"	: "<td>&nbsp;</td>";
			} // end foreach($arh['claims'] as $clmr) 
		}  // end if ( isset($ar_data['claims']) && count($ar_data['claims']) ) 
	} // end foreach ($ar_data as $arh ) 
		
	if ( $clm_html ) {
		// we have some rejected claims to report on 
		// make a header row, but no caption
		$str_html .= "<table class=\"f997\" cols=6>
		   <thead>
		   <tr>
			 <th>Name</th><th>Account</th><th>Status</th>
			 <th>ST Num</th><th>Batch</th><th>Segment</th>
		   </tr>
		   <tr>
			 <th colspan=6>Message</th>
		   </tr>
		   </thead>
		   <tbody>";
		   
		 $str_html .= $clm_html;
	}
	//
	$str_html .= "</tbody>
	  </table>
	  <p></p>";
	//
	return $str_html;
}

/**
 * Html output for errors in 997/999 files
 * 
 * @uses csv_file_by_controlnum()
 * @uses ibr_batch_find_claim_enctr()
 * @uses ibr_997_code_text()
 * 
 * @param array $aksegments
 * @param array $delims
 * @param string $btisa13
 * @param bool $html
 * @return string 
 */
function ibr_997_akhtml($aksegments, $delims, $btisa13='', $html=true) {
	//
	$str_html = '';
	if ( !is_array($aksegments) || !count($aksegments)) {
		$str_html .= "<p class='ak999stat'>No rejected claims found in file</p>".PHP_EOL;
		csv_edihist_log("ibr_997_akhtml: No rejected claims or invalid segments array");
		return $str_html;
	}
	
	$btctlnum = ($btisa13) ? $btisa13 : 'unknown';
    $bt_file = ($btisa13) ? csv_file_by_controlnum('batch', $btisa13) : 'unknown';
	//
	$elem_d = $delims['e'];
	$sub_d = $delims['s'];
	$isfound = false;
	$ak997 = array();
	$ak_pos = 0;
	$idx = -1;
	foreach($aksegments as $segstr) {
		$idx++;
		$seg = explode($elem_d, $segstr);
		if ($seg[0] == 'AK2') {
			$batchst = sprintf("%04d", $seg[2]); 
			$str_html .= "<p class='ak999stat'>".PHP_EOL;
            $str_html .= "ST: $batchst <br /> (837 ICN: $btctlnum $bt_file)<br /><br />";
			if ($bt_file && $bt_file != 'unknown') {
				$stinfo = ibr_batch_get_st_info($batchst, $bt_file);
				if (count($stinfo) > 0) {
					$str_html .= "Name: {$stinfo[2]}, {$stinfo[3]} Ctl: {$stinfo[1]}<br /><br />".PHP_EOL;
				}
			}
			continue;
		}
		// format IK3, CTX, IK4 segments
		if ( $seg[0] == 'AK3' ||$seg[0] == 'IK3') {
			$str_html .= "Loop: {$seg[3]}, Line: {$seg[2]}, Segment: {$seg[1]} <br />".PHP_EOL;
			$ak3err = ibr_997_code_text('ak304', $seg[4]);
			$str_html .= "Code: {$seg[4]} $ak3err <br />".PHP_EOL;
		}
		if ( $seg[0] == "AK4" || $seg[0] == "IK4") { 
			// IK401 is possibly a composite element $ik4pos:$ik4comppos:$ik4rep
			// for now, just get the first part, the element, such as '2' for error in N302
			$ak4msg = ''; $ak4data = ''; $ak4err = '';
            $sub = strpos($seg[1], $sub_d);
			if ($sub) {
				$ak4pos = explode($sub_d, $seg[1]);
				$ak4msg .= ($ak4msg) ? ' pos '.$ak4pos[0] : 'pos '.$ak4pos[0];
				$ak4msg .= (isset($ak4pos[1])) ? ' sub '.$ak4pos[1] : '';
				$ak4msg .= (isset($ak4pos[2])) ? ' rep '.$ak4pos[2] : '';
			} else {
				$ak4msg .= ($ak4msg) ? ' pos '.$seg[1] : 'pos '.$seg[1];
			}
			$ak4elem = $ar_seg[2];                   // Data element numbers defined in X12N Data Element Dictionary
			$ak4cause = $ar_seg[3];                  // error code
			// 
			$ak4err = ibr_997_code_text ("ak403", $seg[3] );
			if ( array_key_exists(4, $seg) ) { $ak4data = $seg[4]; }
			$str_html .= "Data error: $ak4msg <br />".PHP_EOL;
			$str_html .= "Code: {$seg[3]} $ak4err <br />".PHP_EOL;
            $str_html .= ($ak4data) ? "Data: $ak4data  <br />".PHP_EOL : '';
		}
		if ( $seg[0] == "CTX") {
			if (strpos($seg[1], 'SITUATIONAL')) {
				$str_html .= "{$seg[1]} -- Line: {$seg[3]} Position: {$seg[5]} <br />".PHP_EOL;
			} else {
				if (strpos($seg[1], $sub_d)) {
					$busid = explode($sub_d, $seg[1]);
					if ($busid[0] == 'CLM01') {
						$str_html .= "Patient Ctl Num: {$busid[1]} <br />".PHP_EOL;
					} else {
						$str_html .= "Identifier: {$busid[0]} Value: {$busid[1]} <br />".PHP_EOL;
					}
				} else {
					$str_html .= "Identifier: {$seg[1]} <br />".PHP_EOL;
				}
			}		
		}
		if ( $seg[0] == "AK5" || $seg[0] == "IK5" ) {
			$ak5txt = ibr_997_code_text ("ak501", $seg[1]);
			$str_html .= "Status: {$seg[1]}  $ak5txt <br />".PHP_EOL;
			$str_html .= "</p>".PHP_EOL;
		}
	}
	return $str_html;
}

/**
 * Scan through the 997/999 file and report on errors
 * 
 * @uses csv_x12_segments()
 * @uses ibr_997_akhtml()
 * @param string $filename
 * @param string $ak2num  optional
 * @param bool $html_out
 * @return string 
 */		
function ibr_997_errscan($filename, $ak2num='', $html_out=true) {
	//
	$x12seg = csv_x12_segments($filename, 'f997', false);
	if ( !$x12seg || ! isset($x12seg['segments']) ) {
		$str_html = "failed to get segments for " . basename($filename).PHP_EOL;
		csv_edihist_log("ibr_997_get_akblock: failed to get segments for $filename");
		return $str_html;
	}
	//
	$str_html = '';
	$akst = ($ak2num) ? sprintf("%04d", $ak2num) : false;
	//
	$elem_d = $x12seg['delimiters']['e'];
	$sub_d = $x12seg['delimiters']['s'];
	$isfound = false;
	$errslice = array();
	$btctlnum = '';
	$ak_pos = 0;	
	$idx = -1;
	//
	foreach($x12seg['segments'] as $segstr) {
		$idx++;
		$segid = substr($segstr, 0, 4);
		//
		if ($segid == 'TA1'.$elem_d) {
			$seg = explode($elem_d, $segstr);
			$btctlnum = strval($seg[1]);
			continue;
		}
		if ($segid == 'AK2'.$elem_d) {
			$ak2pos = $idx;
			if ($akst) {
				$seg = explode($elem_d, $segstr);
				if ($seg[2] == $akst) { $isfound = true; }
			} 
			continue;
		}
		if ($segid == 'AK5'.$elem_d || $segid == 'IK5'.$elem_d) {
			$seg_count = $idx - $ak2pos + 1;
			if ($isfound) { 
				$errslice[] = array($ak2pos, $seg_count);
				break; 
			} elseif (!$akst && substr($segstr, 4, 1) != 'A') {
				$errslice[] = array($ak2pos, $seg_count);
			}
		}
	}
	if (count($errslice)) {
		foreach($errslice as $er) {
			$aksegs = array_slice($x12seg['segments'], $er[0], $er[1]);
			$str_html .= ibr_997_akhtml($aksegs, $x12seg['delimiters'], $btctlnum, $html_out);
		}
	} else {
		$fn = basename($filename);
		$str_html .= "<p>No rejected claims indicated in $fn</p>".PHP_EOL;
	}
	
	return $str_html;
}
			
		
			
/**
 * process new 997/999 files
 * 
 * @uses csv_newfile_list()
 * @uses csv_verify_file()
 * @uses csv_parameters()
 * @param array $file_array -- optional, this array is sent from the ibr_io.php script
 * @param bool $html_out -- whether to produce and return html output
 * @param bool $err_only -- whether to generate claim information only for errors (ignored)
 * @return string
 */				
function ibr_997_process_new($file_array = NULL, $html_out = TRUE, $err_only = TRUE) {
	//
	$ret_str = "";
	$chr_ct1 = 0;
	$chr_ct2 = 0;
	$new_997 = array();
	$need_dir = TRUE;
	//
	if (is_null($file_array) || empty($file_array) ) {
		// directory will need to be prepended to name
		$new_997 = csv_newfile_list('f997');		
	} elseif (is_array($file_array) && count($file_array) ) {
		// files that are not verified will just be ignored
		foreach ($file_array as $finp) {
			$fp = csv_verify_file($finp, 'f997');
			if ($fp) { $new_997[] = $fp; }
		}
		$need_dir = FALSE;
	}
	//
	if (count($new_997) == 0 ) {
		$ret_str = "<p>ibr_997_process_new: no new 997/999 files. </p>";
		return $ret_str;
	} else {
		$f997count = count($new_997);
	}
	//
	$ar_htm = array();
	//	
	$params = csv_parameters("f997");
	$tdir = dirname(__FILE__).$params['directory'];
	//
	// get batch files parameters, we need the batch_dir 
	$bp = csv_parameters("batch");
	$batch_dir = dirname(__FILE__).$bp['directory'];
	// 
	foreach ($new_997 as $f997) { 
		// make the file path
		$fpath = ($need_dir) ? $tdir . DIRECTORY_SEPARATOR . $f997 : $f997;
		// get file m-time
		//$ftime = date('Ymd:His', filemtime($fpath));
		// read file into string
		$f_str = file_get_contents($fpath);
		if ($f_str) {
			// transform file contents into segment arrays
			$ar_seg = csv_x12_segments($fpath, "f997", FALSE );
			//
			if (!$ar_seg) {
				// file was rejected
				csv_edihist_log("ibr_997_process_new: failed to get segments for $fpath");
				$ret_str .= "ibr_997_process_new: failed to get segments for $fpath</p>" .PHP_EOL;
				continue;
			}
			// parse arrays into data arrays
			$ar_data = ibr_997_parse($ar_seg);
			// $ar_data = array("file" => $ar_997file, "rejects" => $ar_reject);
			//
			$csv_claims = ibr_997_data_csv($ar_data['file'], 'file');
			if ($csv_claims) {
				$chr_ct1 += csv_write_record($csv_claims, "f997", "file");
			} else {
				csv_edihist_log("ibr_997_process_new: error with files csv array");
			}	
			//
			if ( isset($ar_data['claims']) && count($ar_data['claims']) ) { 
				// only add to claims_997.csv if there are rejected claims
				$csv_claims = ibr_997_data_csv($ar_data['claims'], 'claim');
				if ($csv_claims) {
					$chr_ct2 += csv_write_record($csv_claims, "f997", "claim");
				} else {
					csv_edihist_log("ibr_997_process_new: error with claims csv array");
				}
				//
			}
			//
			// save all the ar_datas for html output 
			if ($html_out) { $ar_htm[] = $ar_data; }
			//
		} else {
			$ret_str .= "<p>ibr_997_process_new: failed to read $fpath </p>" .PHP_EOL;
		}
	}
	//
	csv_edihist_log("ibr_997_process_new: $chr_ct1 characters written to files_997.csv");
	csv_edihist_log("ibr_997_process_new: $chr_ct2 characters written to claims_997.csv");
	//
	if ($html_out) {
		// generate html output and return that
		$ret_str .= ibr_997_file_data_html($ar_htm, $err_only);
	} else {
		$ret_str .= "x12_999 files: processed $f997count x12-999 files <br />";
	}

	//
	return $ret_str;
}


                      
?>
