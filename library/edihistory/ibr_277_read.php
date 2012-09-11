<?php
/**
 * ibr_277_read.php
 * 
 * read x12 277 claim status responses, especially the 277CA variety
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 or later.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.  You should have received 
 * a copy of the GNU General Public License along with this program; 
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, 
 * Boston, MA 02110-1301, USA. 
 * <http://opensource.org/licenses/gpl-license.php>
 * 
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
 
// /**
//  * a security measure to prevent direct web access to this file
//  */
// if (!defined('SITE_IN')) die('Direct access not allowed!'); 



/**
 * get the segments or array_slice parameters for ISA...IEA in a 277 file
 * 
 * @todo    adapt this to general purpose x12 section getter
 * 
 * @uses csv_verify_file()
 * @uses csv_x12_segments()
 * @param string $filename   name of file
 * @param string $isa13  the ISA control number
 * @param bool  $slice_params  whether to return array_slice() parameters or the segments
 * @return array
 */ 
function ibr_277_isa_block($filename, $isa13, $slice_params=false) {
	//
	$isa_str = '';
	$srchval = strval($isa13);
	// get segments
	$x12seg = csv_x12_segments($filename, 'f277', false);
	if ( !$x12seg || ! isset($x12seg['segments']) ) {
		$isa_str .= "ibr_277_isa_block: failed to get segments for ".basename($filename).PHP_EOL;
		csv_edihist_log("ibr_277_isa_block: failed to get segments for $filename");
		return $isa_str;
	}
	
	$elem_d = $x12seg['delimiters']['e'];
	$isa277 = array();
	$isa_slice = array();
	$isa_pos = 0;	
	$idx = -1;
	foreach($x12seg['segments'] as $segstr) {
		$idx++;
		if (substr($segstr, 0, 4) == 'ISA'.$elem_d) {
			$seg = explode($elem_d, $segstr);
			$isfound = (strval($seg[13]) == $srchval);
			$isa_pos = $idx;
		}
		//
		if ($isfound && !$slice_params) { $isa277[] = $segstr; }
		//
		if (substr($segstr, 0, 4) == 'IEA'.$elem_d) {
			$seg = explode($elem_d, $segstr);
			if (strval($seg[2]) == $srchval) { 
				$isa_slice['start'] = $isa_pos;
				$isa_slice['count'] = $idx - $isa_pos + 1;
				//$isfound = false; 
				break;
			}
		}
	}
	//
	if ( !count($isa277) && !count($isa_slice) ) {
		$isa_str .= "ibr_277_isa_block: did not find $isa13 in " . basename($filename).PHP_EOL;
		csv_edihist_log("ibr_277_isa_block: did not find $isa13 in $filename");
		return $isa_str;
	}
	//
	if ($slice_params) {
		return $isa_slice;
	} else {
		return $isa277;
	}
}
	

/**
 * Selected values from $ar277['claim'] array for claims_277.csv
 * 
 * these are for initial batch return files, basically saying the claims are accepted
 * for "adjudication" or rejected due to some error or omission 
 * close match to Availity ebr|ibr files, which are derived from these
 * This uses an edit to the OpenEMR billing_process.php script to create
 * a unique BHT03 id number, since that is how the claims are identified
 * 
 * @uses ibr_batch_by_ctln()
 * @param array $ar_277  the array from {@see ibr_277_parse()}
 * @return array
 */ 
function ibr_277_csv_claim_data($ar_277_clm) {
	//
	// the $ar_277_vals [ $ar277['claim'] ] array:
	// <pre>
	// $arRSP[$isa_ct]['key']
	//   ['BHT'] ['isa_id']['gs06']['iea02']
	//
	// $arRSP["claim"][$isa_ct]=>["ISA13"]["GS04"]["GS06"]["IEA02"]
	//                         =>['BHT']['bht_ct']['key'] 
	// 		                         =>['ENV"]=>['BHT01']['BHT02']['BHT03']['BHT04']
	//                                          ['BHT06']["FILE"]["ISA13"]["ST02"]["SE01"]
	//                               =>['A']=>['NM101']["NM102"]["NM103"]["NM104"]["NM105"]
	//                                        ["NM107"]["NM108"]["NM109"]["TRN01"]["TRN02"]
	//                                        ["DTP03050"]["DTP03009"]
	//                               =>['B']=>['NM101']["NM102"]["NM103"]["NM104"]["NM105"]
	//                                        ["NM107"]["NM108"]["NM109"]["TRN01"]["TRN02"]
	//                                        ["STC011"]["STC012"]["STC013"]["STC014"]
	//                                        ["STC02"]["STC03"]["STC04"]["QTY01"]["QTY02"]
	//                                        ["AMT01"]["AMT02"]
	//                               =>['C']=>['NM101']["NM102"]["NM103"]["NM104"]["NM105"]
	//                                        ["NM107"]["NM108"]["NM109"]["TRN01"]["TRN02"]
	//                                        ["REFTJ"]
	//                               =>['D']=>['NM101']["NM102"]["NM103"]["NM104"]["NM105"]
	//                                        ["NM107"]["NM108"]["NM109"]["TRN01"]["TRN02"]
	//                                        ["REF1K"]["REFD9"]["DTP03FROM"]["DTP03TO"]
	//                                      =>["STC"][i]=>["STC011"]["STC012"]["STC013"]
	//                                          ["STC014"]["STC02"]["STC03"]["STC04"]
	// </pre>
	if (!is_array($ar_277_clm) || count($ar_277_clm) == 0 ) {
		return FALSE;
	}
	//
	$csv_ar = array();
	//
	foreach($ar_277_clm['BHT'] as $bht) {
		$status = ""; $message = ""; $bfl = ""; $ctlnum = ""; $stnum = "";
		// this is the BHT03 value from the batch file 
		$bt_bht03 = strval($bht['B']['TRN02']);
		//
		$st_277 = $bht['ENV']['ISA13'].'_'.$bht['ENV']['ST02'];   // this is to find the response later
		$file_277 = $bht['ENV']['FILE'];
		$payer_name = isset($bht['A']['NM103'] ) ? $bht['A']['NM103'] : "Unknown Payer";
		// if no claim_id, but ['TRN02'], find the pid-encounter with batch file and st02
		if ( isset($bht['D']['REF1K']) ) {
			 $claim_id = $bht['D']['REF1K'];
		} elseif (isset($bht['D']['REFD9'])) {
			$claim_id = $bht['D']['REFD9'];
		} elseif (isset($bht['D']['REFEA'])) {
			$claim_id = $bht['D']['REFEA'];
		} elseif (isset($bht['D']['REFBLT'])) {
			$claim_id = $bht['D']['REFBLT'];
		} elseif (isset($bht['D']['REFEJ'])) {
			$claim_id = $bht['D']['REFEJ'];
		} elseif (isset($bht['D']['REFXZ'])) {
			$claim_id = $bht['D']['REFXZ'];
		} elseif (isset($bht['D']['REFVV'])) {
			$claim_id = $bht['D']['REFVV'];
		} else {
			$claim_id = "NF";
		}
		$pt_name = $bht['D']['NM103'] . ", " . $bht['D']['NM104'];
		$date = $bht['D']['DTP03FROM'];
		$pid = $bht['D']['TRN02'];                        //$pidenc[0]
		foreach ($bht['D']['STC'] as $stc) { 
			//
			$status = $stc['STC011'];
			//
			if ($stc['STC011'] == "A1" ) { 
				$status = "A1 Ack"; 
			} elseif ($stc['STC011'] == "A2" ) { 
				$status = "A2 Acpt"; 
			} elseif ($stc['STC011'] == "A3" ) { 
				$status = "A3 Rej"; 
			} elseif ($stc['STC011'] == "A4" ) { 
				$status = "A4 Nfnd"; 
			} elseif ($stc['STC011'] == "A5" ) { 
				$status = "A5 Split"; 
			} elseif ($stc['STC011'] == "A6" ) { 
				$status = "A6 Rej"; 
			} elseif ($stc['STC011'] == "A7" ) { 
				$status = "A7 Rej"; 
			} elseif ($stc['STC011'] == "A8" ) { 
				$status = "A8 Rej"; 
			} elseif ($stc['STC011'] == "DO" ) { 
				$status = "DO Fail"; 
			}
			if ($stc['STC03'] == "15") { $status .= " Resubmit"; }

			$message .= isset($stc['STC12']) ? trim($stc['STC12']) . " " : "";
		}
		// revised csv layout
		//['f277']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'st_277', 'File_277', 'payer_name', 'claim_id', 'bht03_837');
		$csv_ar[] = array('pt_name'=>$pt_name, 'date'=>$date, 'pid'=>$pid, 'status'=>$status, 
		                  'st_277'=>$st_277, 'file_277'=>$file_277, 'payer_name'=>$payer_name, 
		                  'claim_id'=>$claim_id, 'bht03_837'=>$bt_bht03, 'message'=>$message);
		
	}// end foreach ($ar_277_vals as $isa)
	//
	return $csv_ar;
}	


/**
 * write data to csv file
 * 
 * @deprecated
 * @uses csv_write_record()
 * @param array $ar_claim_data the data array, either file data or claim data
 * @param string $type either claim or file, claim if omitted
 * @return int  character count from fputcsv()
 */
function ibr_277_write_csv($ar_data, $type = "claim") {
	//
	$ct = ($type == "claim") ? 'claim' : 'file';
	$rslt = csv_write_record($ar_data, "f277", $ct);
	return $rslt;
}

/**
 * create an html string for a table to display in a web page
 * 
 * @param $ar_data  array produced by function ibr_ebr_data_ar
 * @param $err_only boolean ignore claim information if no 3e subarray is in the claim data
 * @return string
 */
function ibr_277_html ($ar_data, $err_only=FALSE) {
	//$ar_hd = $ar_data['head'];
	//$ar_cd = $ar_data['claims'];
	$idx = 0;
	$idf = 0;
	$has3 = FALSE;
	$hasclm = FALSE;
	$clm_html = "";
	$str_html = "";
	//
	$dtl = ($err_only) ? "Errors only" : "All included claims";
	//$ar_col_hdr = array('mtime', 'directory', 'file_name', 'text_name', 'claim_ct', 'reject');
	// the table heading for files'mtime', 'directory', 'file_name',  'claim_ct', 'amt_accpt','reject', 'amt_rej'
	$f_hdg = "<table cols=6 class=\"f277\">
	   <caption>277 Files Summary  {$dtl} </caption> 
	   <thead>
		   <tr>
			 <th>Date</th><th>277 File</th><th>Claims</th><th>Amt Total</th><th>Rejects</th><th>Amt Rej</th>
		   </tr>
		</thead>
		<tbody>";
	//
	// the details table heading'pt_name''date''pid''status''st_277''claim_id''payer_name''message'
	$clm_hdg = "<table cols=6 class=\"f277\">
	 <thead>
		<tr>
		  <th>Patient Name</th><th>Date</th><th>PtCtln</th><th>Status</th><th>Payer Name</th><th>Claim No</th>
		</tr>
		<tr>
		  <th colspan=6 align=left>Message</th>
		</tr>
	 </thead>
	 <tbody>";
	//
	// start with the table heading
	$str_html .= $f_hdg;
	//	
	foreach ($ar_data as $ardt) {
		// alternate colors
		$bgf = ($idf % 2 == 1 ) ? 'fodd' : 'feven';
		$idf++;
		//
		$ar_hd = $ardt['file'];
		$ar_cd = $ardt['claim'];
		// if any individual claims detail is to be output
		// a claims table was inserted, so put the files heading in
		if ($hasclm) { $str_html .= $f_hdg; }
		// reset variables for whether there are claims
		$clm_html = "";	
		$has3 = FALSE;
		$hasclm = FALSE;
		//
		if (isset($ardt['file'])) {
			// 'filetime''filename''isa13_277''claim_ct''amt_accpt''claim_rct''amt_rej'
            //
			$str_html .= "
			   <tr class=\"{$bgf}\">
                 <td>{$ardt['file']['filetime']};</td>
				 <td><a href=\"edi_history_main.php?fvkey={$ardt['file']['filename']}\" target=\"_blank\">{$ardt['file']['filename']}</a></td>
				 <td>{$ardt['file']['claim_ct']}</td>
				 <td>{$ardt['file']['amt_accpt']}</td>
				 <td>{$ardt['file']['claim_rct']}</td>
				 <td>{$ardt['file']['amt_rej']}</td>
			   </tr>";
		}			
		if (isset($ardt['claim']) ) {	
			foreach ($ardt['claim'] as $val) {
				if ($err_only && $val['message'] == "" ) { continue; }
				$bgc = ($idx % 2 == 1 ) ? 'odd' : 'even';
				$hasclm = TRUE;
				$clm_html .= "<tr class=\"{$bgc}\">
				   <td>{$val['pt_name']}</td>
				   <td>{$val['date']}</td>
				   <td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch={$val['bht03_837']}&btpid={$val['pid']}'>{$val['pid']}</td>
				   <td><a class='clmstatus' target='_blank' href='edi_history_main.php?rspfile={$val['file_277']}&pidenc={$val['pid']}&rspstnum={$val['st_277']}'>{$val['status']}</td>
				   <td>{$val['payer_name']}</td>
				   <td>{$val['claim_id']}</td>
				  </tr>
				  <tr class=\"{$bgc}\">
				    <td colspan = 7>{$val['message']}</td>
				  </tr>";
				$idx++;
			}
		}
		// if there were any claims detailed
		if ( $hasclm && strlen($clm_html) ) {
			$str_html .= $clm_hdg.$clm_html.PHP_EOL."</tbody></table>".PHP_EOL;
		}
	}
	// finish the table 
	$str_html .= "</tbody></table>";
	//
	return $str_html;
}


/**
 * Parse x12 277 file into array
 * 
 * The x12 277 claim status response file contains many fields that are useful for 
 * different purposes, so there is a lot of surplus information depending on your 
 * reason for viewing the file.
 * 
 * <pre>
 * Return array has keys 'file' and 'claim'
 * The 'claim' array is detailed here:
 *  $arRSP[$isa_ct]['key']
 *    ['BHT'] ['isa_id']['gs06']['iea02']
 * 
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['key'] 
 *    ['ENV"] ['A'] ['B'] ['C'] ['D']['BHT01']['BHT02']['BHT03']['BHT04']['BHT06']
 * 
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['ENV"] 
 *   	  ['ISA13'] ['ST02'] ['SE01'] ['FILE']
 * 
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['A']  (sender -- insurance company or clearinghouse)
 *         ['NM103']['NM109']['TRN02']['DTP03050']['DTP03009']['PER01']['PER02']['PER03']['PER04']
 * 
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['B']  (receiver -- practice or biller)
 *         ['STC011']['STC012']['STC013']['STC014']['STC02']['STC03']['STC04']['STC05']['STC06']
 *         ['STC07']['STC08'] ['STC09']['QTY01']['QTY02']['AMT01']['AMT02']
 *            
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['C'] (provider -- practice or individual)
 *         ['NM103']['NM104']['NM105']['NM107']['NM108']['NM109']['TRN01'] ['TRN02']
 *         ['REF01']['REF02']['QTY01']['QTY02']['AMT01']['AMT02']	
 * 		
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['D'] (patient -- individual)
 *   	  ['NM102']['NM103']['NM104']['NM105']['NM107']['NM108']['NM109']['TRN01']['TRN02']
 *   	  ['REF1K']['REFD9']['REFEA']['REFBLT']['REFEJ']['REFXZ']['REFVV']
 *        ['DTP03FROM'] ['DTP03TO']
 * 
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['D']['STC'][stccount]['key']
 * 		  ['STC011']['STC012']['STC013']['STC014']['STC011']['STC012']
 *  	  ['STC02']['STC03']['STC04']['STC05']['STC06']['STC07']['STC08']['STC09']
 *     	  ['STC101']['STC102']['STC103']['STC104']
 *   	  ['STC111']['STC112']['STC113']['STC114']
 * 
 *  $arRSP[$isa_ct]['BHT']['bht_ct']['D']['SVC'][svccount]['key']
 *    	  ['SVC011']['SVC012']['SVC013']['SVC014']['SVC015']['SVC016']['SVC017']
 *  	  ['SVC02']['SVC03']['SVC04']['SVC05']['SVC06']['SVC07']     
 *  	  ['STC011']['STC012']['STC013']['STC014']
 * 		  ['STC02']['STC03']['STC04']['STC05']['STC06']['STC07']['STC08']['STC09']
 * 		  ['STC101']['STC102']['STC103']['STC104']
 * 		  ['STC111']['STC112']['STC113']['STC114']
 * 		  ['STC12']
 * </pre>
 * 
 * @todo   refactor array design -- does not account well enough for x12 controls or references
 * @param array $ar_segments -- from ibr_277_process_new and csv_record_include.php
 * @return array
 */
function ibr_277_parse($ar_segments) {
	// 

	// read each segment and parse for desired data
	//
	// $ar_vals['hl_prv']
	//   ['prv_name']['prv_id']
	// $ar_vals['hl_prv']['clm_ct']
	// 
	//             ISA*00
	//			   GS*HN
	//             - ST*277
	// 0085 expect - BHT*0085
	//			      HL*1 NM1*PR TRN*1 DTP*050 DTP*009          // HL03 NM103 NM109
	//                HL*2 NM1*41 TRN*2 STC*A1 QTY*90*1 AMT*YU   // 
	//                HL*3 NM1*85 TRN*1 REF*TJ QTY*QA*1 AMT*YU
	//                HL*4 NM1*QC TRN*2 STC*A1 REF*1K REF*D9 DTP*472
	//             - repeat BHT series
	//             - SE*27
	//             GE*26
	//             IEA*1
	//
	if (is_array($ar_segments) && count($ar_segments['segments']) ) {
		$fdir = dirname($ar_segments['path']);
		$fname = basename($ar_segments['path']);
		$fmtime = date('Ymd', filemtime($ar_segments['path']) );
		//
		$ar_277_segments = $ar_segments['segments'];
		//
		$elem_d = $ar_segments['delimiters']['e'];
		$rep_d = $ar_segments['delimiters']['r'];
		$sub_d = $ar_segments['delimiters']['s'];
	} else {
		csv_edihist_log("ibr_277_parse: error invalid segments array");
		return FALSE;
	}
	//
	//$arRSP = array();
	$ar277 = array();
	//
	$bct = -1;
	$ict = -1;
	$st_ct = 0;	
	$svc_ct = 0;
	$clm_ct = 0;
	$rej_ct = 0;
	$amt_accpt = 0;
	$amt_rej = 0;
	// $clm_ct = 0;
	$hl_id = "";
	$hl_pyr = "0";
	$hl_parent = "0";
	$hl_code = "";
	$stchlct = ""; // keep track of which HL is operating on the loop 2000D STC
	//
	$loopid = "0";
	//
	foreach($ar_277_segments as $segline) { 
		// explode the segment into an array of elements
		//
		$seg = explode($elem_d, $segline);
		// set counters, loops, etc. here
		// count segments to verify ST--SE blocks
		// $st_seg_ct is set to 1 in "ST" 
		$st_seg_ct = isset($st_seg_ct) ? $st_seg_ct+1 : 0;
		//
		if ($seg[0] == "ISA") {
			// I have x12-277 files from Availity with multiple ISA--IEA segment blocks
			$ict++;
			//
			$isa13 = $seg[13];
			$fmtime = '20'.strval($seg[9]);
			//
			//$ar277[$ict]['claim']['ISA13'] = $seg[13];
			// reset the ST count and BHT count
			$st_ct = 0;
			$st_seg_ct = 0;
			$bct = -1;
			//
			$loopid = "0";
			// paranoia check
			if ($rep_d != $seg[11] ) {
				$rep_d = $seg[11];
			}
			if ($sub_d != $seg[16] ) {
				$sub_d = $seg[16];
			}	
			//
			continue;					
		}
		
		if ($seg[0] == "IEA") { 
			//$ar277[$ict]['claim']['IEA02'] = $seg[2];
			if ($seg[2] != $isa13) {
				echo "ibr_277_read: ISA Mismatch IEA {$seg[2]} vs ISA $isa13 <br />" . PHP_EOL;
			}
			// consider indexing file array on ISA -- will increase csv table x2 or x3
			//$ar_col_hdr = array('mtime', 'directory', 'file_name',  'claim_ct', 'amt_accpt','reject', 'amt_rej');
			$ar277[$ict]['file']['filetime'] = $fmtime;
			$ar277[$ict]['file']['filename'] = $fname;
			$ar277[$ict]['file']['isa13_277'] = $isa13;
			$ar277[$ict]['file']['claim_ct'] = $clm_ct;
			$ar277[$ict]['file']['amt_accpt'] = $amt_accpt;
			$ar277[$ict]['file']['claim_rct'] = $rej_ct;
			$ar277[$ict]['file']['amt_rej'] = $amt_rej;
			//
			$amt_accpt = 0; $amt_rej = 0; $clm_ct = 0; $rej_ct =0;
			$isa13 = ''; $fmtime = '';
			continue;			
		}
		
		if ($seg[0] == "GS") {
			$fmtime = strval($seg[4]);
			//$ar277[$ict]['claim']['GS04'] = $seg[4];   // File date
			//$ar277[$ict]['claim']['GS06'] = $seg[6];   // Group Control Number
			$gs04 = strval($seg[4]);
			$gs06 = strval($seg[6]); 		
			//
			continue;
		}

		if ($seg[0] == "ST") { 
			//
			$st01 = $seg[1]; 	//R 277 Transaction Set Identifier Code
			$st02 = $seg[2]; 	//R     Transaction Set  Control Number
			$st03 = $seg[3]; 	//R 005010X214   Implementation Convention Reference
			//
			$st_seg_ct = 1;	  // the ST segment is included in the segment count
			$st_ct++;
			$stc_ct = 0;      // STC segments (status) at claim level may repear
			//
			continue;
		}
		
		if ($seg[0] == "SE") { 	
			// check segment count
			$se01 = $seg[1];
			$se02 = $seg[2];
			if ($se01 != $st_seg_ct) {
				echo "ibr_277_read: SE segment count mismatch $se01 $st_seg_ct <br />" . PHP_EOL;
			}
			if ($se02 != $st02) {
				echo "ibr_277_read: SE ST id mismatch $se02 $st02 <br />" . PHP_EOL;
			}
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['SE01'] = $se01;
			//
			continue;
		}
		
		if ($seg[0] == "BHT") {
			// There may be many BHT's Begin Hierarchical Transaction

			$bct++;
			//  define the HL structure of the transaction set
			//  0010  Information Source, Information Receiver, Provider of Service, Subscriber, Dependent
			//  0085  (Guess ?? ) Information Source, Information Receiver, Provider of Service, Patient
			//['BHT02']['BHT03']['BHT04']['BHT06']
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['BHT01'] = $seg[1];   //R 0010  0085   Hierarchical Structure Code
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['BHT02'] = $seg[2];   //R 08 (status) TRANSACTION SET PURPOSE CODE
			// issue with the Originator reference is that it apparently is created by Availity
			// and is not our batch file control number
			// relating back to our claims will require the encounter number (2000D TRN03)
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['BHT03'] = $seg[3];   //R    Originater REFERENCE IDENTIFICATION
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['BHT04'] = $seg[4];   //R CCYYMMDD Transaction Set Creation Date
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['BHT06'] = $seg[6];   //R DG CH TH     Transaction Type Code
			//['ISA13'] ['ST02'] ['SE01'] ['FILE']
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['FILE'] = $fname;		//basename($fp);
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['ISA13'] = $isa13;
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['GS04'] = $gs04;
			$ar277[$ict]['claim']['BHT'][$bct]['ENV']['ST02'] = $st02;
			//
			continue;
		}
		
		if ($seg[0] == "HL") {
			// loop 2000A, 2000B, 2000C, 2000D ??
			// set the $ky variable acording to HL level
			//
			$hl01 = isset($seg[1]) ? $seg[1] : "";;  //  Hierarchical ID Number
			$hl02 = isset($seg[2]) ? $seg[2] : "";;  //  Hierarchical Parent ID Number
			// 20 Information source Payer loop 2000A
			$hl03 = isset($seg[3]) ? $seg[3] : "";;  //    Hierarchical Level Code
			$hl04 = isset($seg[4]) ? $seg[4] : "";  //  1  Hierarchical Child Code
			
			// HL*1**20*1~   HL*1 NM1*PR TRN*1 DTP*050 DTP*009 
			if ($hl03 == "20") {  					 
				$loopid = "2000A";
				$ky = 'A'; 
			}
			// HL*2*1*21*1~				
			if ($hl03 == "21") { 
				$loopid = "2000B";
				$ky = 'B';
			}
			// HL*3*2*19*1~					
			if ($hl03 == "19") { 
				$loopid = "2000C";
				$ky = 'C';
			}					
			// varying levels in loop 2000D	
			// HL*4*3*PT~    patient
			// HL*4*3*22*1~	 subscriber	
			// HL*5*4*23*0~  dependent				
			if ($hl03 == "PT" || $hl03 == "22" || ($hl03 == "23" && $loopid != "2000D") ) { 
				// expect 277CA, but for 277, there is possibility of loop 2000E	
				if ($hl03 == "23" && $loopid == "2000D") {
					$loopid = "2000E";
					$ky = 'E';
				}
				$loopid = "2000D"; 
				$ky = 'D';
				//										    	
				$lp2100D = FALSE; 										
				$lp2200D = FALSE;
				$lp2220D = FALSE;
				//
				$stc_ct = 0;	   // multiple STC segments are possible at the patient level
				$stchlct = $hl01;  // keep track of which HL is operating on the loop 2000D STC
				$svc_ct = 0; 	   // this is for the unlikely event that multiple 2220D loops appear	
			}
			
			// debug
			// check for some things in HL segment
			if (strpos("|20|21|19|PT|22|23", $hl03) == 0) {
				echo "ibr_277: unexpected HL03 {$seg[3]} in $segline <br />" . PHP_EOL;
			}
			if ( strpos("|PT|22|23", $hl03) && $hl04 > 0 ) {
				echo "ibr_277: child HL segment follows HL level $hl03 in $segline " . basename($fp) . PHP_EOL;
			}
			//
			// true if the parent matches the preceeding HL ID
			$same_prv = ($seg[2] == $hl_id);
			// if HL02 is blank, then we are with a new provider
			$hl_id = $seg[1];
			$hl_parent = $seg[2];
			$hl_code = $seg[3];
			//
			continue;
		}
		// testing				
		if ($loopid == "2000A" && $ky != 'A') { echo "HL loop mismatch, $hl_code $loopid $ky $segline $fname<br />" . PHP_EOL; }
		if ($loopid == "2000B" && $ky != 'B') { echo "HL loop mismatch, $hl_code $loopid $ky $segline $fname<br />" . PHP_EOL; }
		if ($loopid == "2000C" && $ky != 'C') { echo "HL loop mismatch, $hl_code $loopid $ky $segline $fname<br />" . PHP_EOL; }
		if ($loopid == "2000D" && $ky != 'D') { echo "HL loop mismatch, $hl_code $loopid $ky $segline $fname<br />" . PHP_EOL; }
		//
		//
		// payer or clearinghouse level HL*1**20*1~ 
		if ($seg[0] == "NM1") {
			// PR payer name  AY clearinghouse 
			// expect payer, but clearinghouse may respond here
			$q = ($seg[1] == "PR") ? $seg[1] : "unexpected qualifier in NM1 2000A: {$seg[1]}";
			//
			// if ($loopid == "2000A") { $loopid = "2100A" }
			// if ($loopid == "2000B") { $loopid = "2100B" }
			// if ($loopid == "2000C") { $loopid = "2100C" }
			// if ($loopid == "2000D") { $loopid = "2100D" }
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM101'] = $seg[1];
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM102'] = $seg[2];
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM103'] = $seg[3]; 	// Last name or company name
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM104'] = $seg[4];   // first name or blank
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM105'] = $seg[5];   // middle name or blank
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM107'] = $seg[7];   // name suffix
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM108'] = $seg[8];   // "FI" or "XX"
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['NM109'] = $seg[9];   // tax id, NPI, payerID
			//
			continue;
		}			
		
		if ($seg[0] == "TRN") {
			// if ($loopid == "2100B") { $loopid = "2200B" }
			// if ($loopid == "2100C") { $loopid = "2200C" }
			// if ($loopid == "2100D") { $loopid = "2200D" }
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['TRN01'] = $seg[1];
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['TRN02'] = $seg[2];  // at HL 4, TRN02 is pid-encounter  ['D']['TRN02']
			//
			continue;
		}
		
		if ($seg[0] == "DTP") {
			if ($seg[1] == "050") {	$ar277[$ict]['claim']['BHT'][$bct][$ky]['DTP03050'] = $seg[3]; }
			if ($seg[1] == "009") {	$ar277[$ict]['claim']['BHT'][$bct][$ky]['DTP03009'] = $seg[3]; }
			if ($seg[1] == "472") {
				if ($seg[2] == "RD8") {
					// service dates
					$sp =  strpos($seg[3], "-");
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['DTP03FROM'] = ($sp) ? substr($seg[3], 0, $sp) : $seg[3];
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['DTP03TO'] =  ($sp) ? substr($seg[3], $sp+1) : $seg[3];	
				}
				if ($seg[2] == "D8") {
					$ar277[$ict]['claim']['BHT'][$bct]['D']['DTP03FROM'] = $seg[3];
				}
			}
			//
			continue;
		}
		
		if ($seg[0] == "PER") {
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['PER01'] = $seg[1];	//R  IC  Contact Function Code R
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['PER02'] = $seg[2];	//S  Payer Contact Name
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['PER03'] = $seg[3];	//R  ED, EM, TE, FX Communication Number Qualifier
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['PER04'] = $seg[4]; 	//R  Communication Number 
			//
			continue;
		} // "PER"
		
		if ($seg[0] == "QTY" && $loopid == "2000B") {	 
			$ar277[$ict]['claim']['BHT'][$bct]['B']['QTY01'] = $seg[1];  // QTY TOTAL ACCEPTED QUANTITY "90" 2200B  QTY TOTAL REJECTED QUANTITY "AA" 2200B
			$ar277[$ict]['claim']['BHT'][$bct]['B']['QTY02'] = $seg[2];  // count of accepted or rejected items
			//
			continue;
		}
		if ($seg[0] == "AMT" && $loopid == "2000B") {	 
			$ar277[$ict]['claim']['BHT'][$bct]['B']['AMT01'] = $seg[1];  // AMT TOTAL ACCEPTED AMOUNT "YU" 2200B  AMT TOTAL REJECTED AMOUNT "YY"
			$ar277[$ict]['claim']['BHT'][$bct]['B']['AMT02'] = $seg[2];  // quantity, i.e. dollars, accepted or rejected
			//
			continue;
		}	
		
		if ($seg[0] == "STC" && $loopid == "2000B") {	
			//PROVIDER STATUS INFORMATION
			//STC*A1:20*20120217*WQ*65~
			if ( strpos($seg[1], $sub_d) ) {
				$sp = strpos($seg[1], $sub_d);
				$stc01 = explode($sub_d, $seg[1]);   			// A1:20 is expected here
				if ( is_array($stc01) ) {
					$ar277[$ict]['claim']['BHT'][$bct]['B']['STC011'] = isset($stc01[0]) ? $stc01[0] : "";	//STC01-1	Health Care Claim Status Category Code	AN	01/30/12	R	D0, E
					$ar277[$ict]['claim']['BHT'][$bct]['B']['STC012'] = isset($stc01[1]) ? $stc01[1] : ""; //STC01-2	Health Care Claim Status Code	AN	01/30/12	R
					$ar277[$ict]['claim']['BHT'][$bct]['B']['STC013'] = isset($stc01[2]) ? $stc01[2] : ""; //STC01-3	Entity Identifier Code	ID	02/03/12	S	 	 	1P
					$ar277[$ict]['claim']['BHT'][$bct]['B']['STC014'] = isset($stc01[3]) ? $stc01[3] : "";	//STC01-4	Code List Qualifier Code	ID	01/03/12	N/U	 
				} else {
					$ar277[$ict]['claim']['BHT'][$bct]['prv_STC011'] = isset($seg[1]) ? $seg[1] : "";
				}
			}
			//
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC02'] = isset($seg[2]) ? $seg[2] : "";	 	//STC02	Status Information Effective Date	DT	08/08/12	R	 	 	CCYYMMDD
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC03'] = isset($seg[3]) ? $seg[3] : "";	 	//STC03	Action Code	ID	01/02/12	N/U
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC04'] = isset($seg[4]) ? $seg[4] : "";	 	//STC04	Monetary Amount	R 	01/18/12	N/U
			// no segments beyond STC04 are expected in loop 2200B STC
			if ( !isset($seg[5]) ) { continue; }
			//
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC05'] = isset($seg[5]) ? $seg[5] : "";	 	//STC05	Monetary Amount	R 	01/18/12	N/U
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC06'] = isset($seg[6]) ? $seg[6] : "";	 	//STC06	Date	DT	08/08/12	N/U		 
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC07'] = isset($seg[7]) ? $seg[7] : "";	 	//STC07	Payment Method Code	ID	03/03/12	N/U
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC08'] = isset($seg[8]) ? $seg[8] : "";	 	//STC08	Date	DT	08/08/12	N/U	 
			$ar277[$ict]['claim']['BHT'][$bct]['B']['STC09'] = isset($seg[9]) ? $seg[9] : "";	 	//STC09	Check Number	AN	01/16/12	N/U	
			//
			continue;
		} // end if ($seg[0] == "STC") in loop 2200B

		if ($seg[0] == "STC" && $loopid == "2000D") {	
			// loop 2200D Subscriber / Patient
			if ( strpos($seg[1], $sub_d) ) {
				$sp = strpos($seg[1], $sub_d);
				$stc01 = explode($sub_d, $seg[1]);           // 2200D STC01-1 Health Care Claim Status Category Code 
				//
				if ( is_array($stc01) ) {                           // "A2" Accept , "A3" "A7" Reject, or “R3” Warning 1/30
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC011'] = isset($stc01[0]) ? $stc01[0] : "";	//STC01-1	Health Care Claim Status Category Code	AN	01/30/12	R	D0, E
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC012'] = isset($stc01[1]) ? $stc01[1] : "";	//STC01-2	Health Care Claim Status Code	AN	01/30/12	R
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC013'] = isset($stc01[2]) ? $stc01[2] : "";	//STC01-3	Entity Identifier Code	ID	02/03/12	S	 	 	1P
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC014'] = isset($stc01[3]) ? $stc01[3] : "";	//STC01-4	Code List Qualifier Code	ID	01/03/12	N/U	 
				} else {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC011'] = isset($seg[1]) ? $seg[1] : "";
				}
			}
			//
			// interject some tallys here, for the files array 
			if ($loopid == "2000D" && $stchlct == $hl01) {
				$clm_ct++;
				if (strpos("|A1|A2|A5|", $stc01[0]) === FALSE) { $rej_ct++; }
				$amt_accpt += $seg[4]; 
				if ($seg[3] == "U") { $amt_rej += $seg[4]; }
				$stchlct = "yes";	
				// 
			}
			//
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC02'] = isset($seg[2]) ? $seg[2] : "";	//STC02	Status Information Effective Date DT 08/08/12 R CCYYMMDD
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC03'] = isset($seg[3]) ? $seg[3] : "";	//STC03	Action Code ID 01/02/12 N/U   15 Correct and Resubmit Claim
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC04'] = isset($seg[4]) ? $seg[4] : "";	//STC04	Monetary Amount R 01/18/12 N/U
			//
			// cut this off if there are no more elements, often only 5
			if ( !isset($seg[5]) ) { continue; }
			//
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC05'] = isset($seg[5]) ? $seg[5] : "";	//STC05	Monetary Amount R 01/18/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC06'] = isset($seg[6]) ? $seg[6] : "";	//STC06	Date DT 08/08/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC07'] = isset($seg[7]) ? $seg[7] : "";	//STC07	Payment Method Code ID 03/03/12 N/U	 
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC08'] = isset($seg[8]) ? $seg[8] : "";	//STC08	Date DT 08/08/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC09'] = isset($seg[9]) ? $seg[9] : ""; 	//STC09	Check Number AN 01/16/12 N/U
			//
			//STC10	HEALTH CARE CLAIM STATUS	 	 	S				
			if ( isset($seg[10]) && strpos($seg[10], $sub_d) ) {	
				$stc10 = explode($sub_d, $seg[10]);
				if ( is_array($stc01) ) {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC101'] = isset($stc10[0]) ? $stc10[0] : ""; 	//STC10-1	Health Care Claim Status Category Code	AN	01/30/12	R	 	 	D0, E
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC102'] = isset($stc10[1]) ? $stc10[1] : ""; 	//STC10-2	Health Care Claim Status Code	AN	01/30/12	R
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC103'] = isset($stc10[2]) ? $stc10[2] : ""; 	//STC10-3	Entity Identifier Code	ID	02/03/12	S	 	 	1P
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC104'] = isset($stc10[3]) ? $stc10[3] : ""; 	//STC10-4	Code List Qualifier Code	ID	01/03/12	N/U
				} else {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC101'] = isset($seg[10]) ? $seg[10] : "";
				}
			} 
			// 
			//STC11	HEALTH CARE CLAIM STATUS	 	 	S				
			if ( isset($seg[11]) && strpos($seg[11], $sub_d) ) {	
				$stc11 = explode($sub_d, $seg[10]);
				if ( is_array($stc11) ) {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC111'] = isset($stc11[0]) ? $stc11[0] : ""; 	//STC11-1	Health Care Claim Status Category Code	AN	01/30/12	R	 	 	D0, E
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC112'] = isset($stc11[1]) ? $stc11[1] : ""; 	//STC11-2	Health Care Claim Status Code	AN	01/30/12	R
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC113'] = isset($stc11[2]) ? $stc11[2] : ""; 	//STC11-3	Entity Identifier Code	ID	02/03/12	S	 	 	1P
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC114'] = isset($stc11[3]) ? $stc11[3] : ""; 	//STC11-4	Code List Qualifier Code	ID	01/03/12	N/U
				} else {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC111'] = isset($seg[11]) ? $seg[11] : "";
				}
			}
			//STC12	Free-Form Message Text	AN	01/01/64 N/U	 	 	 
			if ( isset($seg[12]) ) { $ar277[$ict]['claim']['BHT'][$bct][$ky]['STC'][$stc_ct]['STC12'] = $seg[12]; }
			//
			if( $loopid == "2000D" || $loopid == "2000E") { $stc_ct++; }
			//$lp2220D = TRUE;
			
			//
			continue;
		} // end if ($seg[0] == "STC") in loop 2200C

		if ($seg[0] == "REF") {  
			if ($ky == "C") {
				if ($seg[1] == "TJ") { $ar277[$ict]['claim']['BHT'][$bct][$ky]['REFTJ'] = $seg[2]; }
			} elseif ($ky == "D") {		
				// ref,  1K, EJ, D9 will be expected
				if ($seg[1] == "1K") { 	
					// REF*1K*<<subscriber number >>~REF02 Payer Claim Control Number AN 1-50 R
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['REF1K'] = $seg[2]; 
				} elseif ($seg[1] == "D9") { 
					// REF*D9*NA~ clearinghouse ID
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['REFD9'] = $seg[2]; 
				} elseif ($seg[1] == "EA") { 
					// record ID
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['REFEA'] = $seg[2]; 
				} elseif ($seg[1] == "BLT") { 
					// institutional ID
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['REFBLT'] = $seg[2]; 
				} elseif ($seg[1] == "EJ") { 
					// control ID
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['REFEJ'] = $seg[2]; 
				} elseif ($seg[1] == "XZ") { 
					// prescripton ID
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['REFXZ'] = $seg[2]; 
				} elseif ($seg[1] == "VV") { 
					// voucher ID
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['REFVV'] = $seg[2]; 
				} 	

			} else {
				$refkey = 'REF'.$seg[1];
				$ar277[$ict]['claim']['BHT'][$bct][$ky][$refkey] = $seg[1] . ":" . $seg[2];   // qualifier:value
			}
			//
			continue;			
		}
		
		if ($seg[0] == "SVC") { 
			// loop 2220D
			// set another loop id
			$lp2200D = FALSE;
			$lp2220D = TRUE;
			// 
			// SVC	SERVICE LINE INFORMATION	 	1	S	2220D	>1	 
			$sbr_svc01 = $seg[1]; 		// SVC01	COMPOSITE MEDICAL PROCEDURE INDENTIFIER	 R
			$svc01 = explode($sub_d, $seg[1]);	
			// 	 	 	AD, ER, HC, HP, IV, N4, NU, WK
			
			if ( is_array($stc01) ) {
				$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC011'] = isset($svc01[0]) ? $svc01[0] : "";  //SVC01-1	Product/Service ID Qualifier	ID	02/02/12	R
				$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC012'] = isset($svc01[1]) ? $svc01[1] : "";  //SVC01-2	Service Identification Code	AN	01/01/48	R
				$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC013'] = isset($svc01[2]) ? $svc01[2] : "";  //SVC01-3	Procedure Modifier	AN	02/02/12	S	
				$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC014'] = isset($svc01[3]) ? $svc01[3] : "";  //SVC01-4	Procedure Modifier	AN	02/02/12	S
				$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC015'] = isset($svc01[4]) ? $svc01[4] : "";  //SVC01-5	Procedure Modifier	AN	02/02/12	S
				$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC016'] = isset($svc01[5]) ? $svc01[5] : "";  //SVC01-6	Procedure Modifier	AN	02/02/12	S	
				$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC017'] = isset($svc01[6]) ? $svc01[6] : "";  //SVC01-7	Description	AN	01/01/80	N/U	 
			}
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC02'] = isset($seg[2]) ? $seg[2] : "";  //SVC02	Line Item Charge Amount S9(7)V99	R	01/18/12	R	
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC03'] = isset($seg[2]) ? $seg[3] : "";  //SVC03	Line Item Payment Amount S9(7)V99	R	01/18/12	R	
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC04'] = isset($seg[2]) ? $seg[4] : "";  //SVC04	Revenue Code	AN	01/01/48	S	
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC05'] = isset($seg[2]) ? $seg[5] : "";  //SVC05	Quantity	R	01/15/12	N/U	 
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC06'] = isset($seg[2]) ? $seg[6] : "";  //SVC06	COMPOSITE MEDICAL PROCEDURE INDENTIFIER	 	 	N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['SVC07'] = isset($seg[2]) ? $seg[7] : "";  //SVC07	Units of Service Count S9(3)V9	R	01/15/12	S
			//
			$svc_ct++;
			continue;
		}
		
		if ($seg[0] == "STC" && $lp2220D ) {	
			// loop 2220D
			//SUBSCRIBER STATUS INFORMATION
			//STC*A1:20*20120217*WQ*65~
			if ( strpos($seg[1], $sub_d) ) {
				$sp = strpos($sub_d, $seg[1]);
				$stc_svc_01 = explode($sub_d, $seg[1]);           // 2200D STC01-1 Health Care Claim Status Category Code 
				if ( is_array($stc01) ) {                           // "A2" Accept , "A3" Reject, or “R3” Warning 1/30
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC011'] = isset($stc01[0]) ? $stc01[0] : "";	//STC01-1	Health Care Claim Status Category Code	AN	01/30/12	R	D0, E
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC012'] = isset($stc01[1]) ? $stc01[1] : "";	//STC01-2	Health Care Claim Status Code	AN	01/30/12	R
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC013'] = isset($stc01[1]) ? $stc01[1] : "";	//STC01-3	Entity Identifier Code	ID	02/03/12	S	 	 	1P
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC014'] = isset($stc01[1]) ? $stc01[1] : "";	//STC01-4	Code List Qualifier Code	ID	01/03/12	N/U	 
				} else {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC011'] = isset($seg[1]) ? $seg[1] : "";
				}
			}

			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC02'] = isset($seg[2]) ? $seg[2] : "";	//STC02	Status Information Effective Date DT 08/08/12 R CCYYMMDD
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC03'] = isset($seg[3]) ? $seg[3] : "";	//STC03	Action Code ID 01/02/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC04'] = isset($seg[4]) ? $seg[4] : "";	//STC04	Monetary Amount R 01/18/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC05'] = isset($seg[5]) ? $seg[5] : "";	//STC05	Monetary Amount R 01/18/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC06'] = isset($seg[6]) ? $seg[6] : "";	//STC06	Date DT 08/08/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC07'] = isset($seg[7]) ? $seg[7] : "";	//STC07	Payment Method Code ID 03/03/12 N/U	 
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC08'] = isset($seg[8]) ? $seg[8] : "";	//STC08	Date DT 08/08/12 N/U
			$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC09'] = isset($seg[9]) ? $seg[9] : ""; 	//STC09	Check Number AN 01/16/12 N/U
			//STC10	HEALTH CARE CLAIM STATUS	 	 	S				
			if ( isset($seg[10]) && strpos($seg[10], $sub_d) ) {	
				$stc10 = explode($sub_d, $seg[10]);
				if ( is_array($stc01) ) {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC101'] = isset($stc10[0]) ? $stc10[0] : ""; 	//STC10-1	Health Care Claim Status Category Code	AN	01/30/12	R	 	 	D0, E
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC102'] = isset($stc10[1]) ? $stc10[1] : ""; 	//STC10-2	Health Care Claim Status Code	AN	01/30/12	R
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC103'] = isset($stc10[2]) ? $stc10[2] : ""; 	//STC10-3	Entity Identifier Code	ID	02/03/12	S	 	 	1P
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC104'] = isset($stc10[3]) ? $stc10[3] : ""; 	//STC10-4	Code List Qualifier Code	ID	01/03/12	N/U
				} else {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC101'] = isset($seg[10]) ? $seg[10] : "";
				}
			}
			//
			//STC11	HEALTH CARE CLAIM STATUS	 	 	S				
			if ( isset($seg[11]) && strpos($seg[11], $sub_d) ) {	
				$stc11 = explode($sub_d, $seg[10]);
				if ( is_array($stc11) ) {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC111'] = isset($stc11[0]) ? $stc11[0] : ""; 	//STC11-1	Health Care Claim Status Category Code	AN	01/30/12	R	 	 	D0, E
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC112'] = isset($stc11[1]) ? $stc11[1] : ""; 	//STC11-2	Health Care Claim Status Code	AN	01/30/12	R
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC113'] = isset($stc11[2]) ? $stc11[2] : ""; 	//STC11-3	Entity Identifier Code	ID	02/03/12	S	 	 	1P
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC114'] = isset($stc11[3]) ? $stc11[3] : ""; 	//STC11-4	Code List Qualifier Code	ID	01/03/12	N/U
				} else {
					$ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC111'] = isset($seg[11]) ? $seg[11] : "";
				}
			}
			//STC12	Free-Form Message Text	AN	01/01/64 N/U	 	 	 
			if ( isset($seg[12]) ) { $ar277[$ict]['claim']['BHT'][$bct][$ky]['SVC'][$svc_ct]['STC12'] = $seg[12]; }
			//
			if( $loopid == "2000D" || $loopid == "2000E") { $svc_ct++; }
			//
			continue;
			//
		} // end if ($seg[0] == "STC" && $lp2220D ) in loop 2200D			
		//
	} // end foreach ( )
	// array('mtime', 'file_name',  'claim_ct', 'amt_accpt', 'reject_ct', 'amt_rej');
	//$ar_col_hdr = array('mtime', 'directory', 'file_name',  'claim_ct', 'amt_accpt','reject', 'amt_rej');
	
	//
	//$ar277['file'] = array('filetime' => $fmtime, 'filename' => $fname, 'claim_ct' => $clm_ct, 
	//                        'amount' => $amt_accpt, 'reject_ct' => $rej_ct, 'reject_amt' => $amt_rej);
	// new csv layout                       
	//$csv_hd_ar['f277']['file'] =  array('date', 'file_name', 'isa13_277', 'claim_ct', 'claim_rct'); 
	//$ar277[$ict]['file'] = array('filetime'=>$fmtime, 'filename'=>$fname, 'isa13_277'=>$isa13, 'claim_ct'=>$clm_ct, 'claim_rct' => $rej_ct);
	//
	//$ar277['claim'] = $arRSP;
	return $ar277;
}


/**
 * Process new files for csv table and html output
 * 
 * @uses csv_newfile_list()
 * @uses csv_parameters()
 * @uses csv_x12_segments()
 * @uses ibr_277_parse()
 * @uses ibr_277_csv_claim_data()
 * @uses csv_write_record()
 * @param bool $html_out -- whether to return html output
 * @param bool $err_only -- only list claims with errors in output
 * @param array $files_ar -- list of new files from upload script
 * @return string
 */	
function ibr_277_process_new($files_ar = NULL, $html_out = FALSE, $err_only = TRUE) {
	// 
	if ( $files_ar === NULL || !is_array($files_ar) || count($files_ar) == 0) {
		$ar_files = csv_newfile_list("f277");
	} else {
		$ar_files = $files_ar;
		//$need_dir = FALSE;
	} 	
	//
	if ( count($ar_files) == 0 ) {
		if($html_out) { 
			$html_str .= "<p>ibr_277_process_new: no new f277 files <br />";
			return $html_str;
		}
	}
	// we have some new ones, verify and get complete path
	foreach($ar_files as $fbt) { 
		$fp = csv_verify_file($fbt, 'f277', false);
		if ($fp) { $ar_277files[] = $fp; }
	}
	//
	if (!is_array($ar_277files) || count($ar_277files) == 0 ) {
		$html_str = "ibr_277_process_new: no new f277 files found <br />" . PHP_EOL;
		return $html_str;
	} else {
		$f277count = count($ar_277files);
	}		
	// OK, we have some new files
	$html_str = "";
	$idx = 0;
	$chr_c = 0;
	$chr_f = 0;
	//
	foreach ($ar_277files as $file_277) {
		// since the newfiles routine is updated, the need_dir test is not necessary
		//$path_277 = ($need_dir) ? $dir.DIRECTORY_SEPARATOR.$file_277 : $file_277;
		$path_277 = $file_277;
		//
		$ar_277seg = csv_x12_segments($path_277, "f277", FALSE);
		//
		if (is_array($ar_277seg) && count($ar_277seg['segments']) ) {
			$ar_277_vals = ibr_277_parse($ar_277seg);
			if (!$ar_277_vals) {
				$html_str .= "failed to get segments for $file_277 <br />" .PHP_EOL;
				continue;
			}		
		} else {
			$html_str .= "failed to get segments for $file_277 <br />" .PHP_EOL;
			continue;
		}
		// since main array is indexed on isa segment and there may be more
		// than one per file, the 'file' csv table has a row for each ISA segment
		// and the 'claim' csv table has a row for each BHT segment
		
		foreach($ar_277_vals as $isa) { 
			$ar_csvf = $isa['file']; 
			$ar_csvc = ibr_277_csv_claim_data($isa['claim']);
			//
			if ($html_out) {
				$ar_h[$idx]['file'] = $ar_csvf;
				$ar_h[$idx]['claim'] = $ar_csvc;
				$idx++;
			}
			// still too much in claim csv record, drop the message
			//['f277']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'st_277', 'File_277', 'payer_name', 'claim_id', 'bht03_837');
			$ar_csvclm = array();
			foreach($ar_csvc as $clm) {
				$ar_csvclm[] = array_slice($clm, 0, 8);
			}			
			$chr_f += csv_write_record($ar_csvf, "f277", "file");
			$chr_c += csv_write_record($ar_csvclm, "f277", "claim");
			//
		}
	} // end foreach ($ar_files as $file_277)
	//
	if ($html_out) {
		$html_str .= ibr_277_html($ar_h, $err_only);
	} else {
		$html_str .= "x12_277 files: processed $f277count x12-277 files <br />". PHP_EOL;
	} 
	csv_edihist_log("ibr_277_process_new: $chr_f characters written to files_277.csv");
	csv_edihist_log("ibr_277_process_new: $chr_c characters written to claims_277.csv");		
	//
	
	return $html_str;
}


/**
 * Create html output for a 277 file, same as the process new output
 * 
 * @uses csv_verify_file()
 * @uses csv_x12_segments()
 * @uses ibr_277_parse()
 * @uses ibr_277_csv_claim_data()
 * @uses ibr_277_html()
 * @param string $filepath -- filename or full path to file
 * @return string
 */
function ibr_277_filetohtml($filepath, $err_only = false) {
	// 
	// simply create an html output for the file like processing new
	//
	$html_str = "";
	//
	$fp = csv_verify_file($filepath, "f277");
	if ($fp) {
		$ar_277_seg = csv_x12_segments($fp, "f277", false);
		if (is_array($ar_277_seg) && count($ar_277_seg['segments']) ) {
			$ar_277_vals = ibr_277_parse($ar_277_segs);
		} else {
			$html_str .= "failed to get segments for $fp <br />" .PHP_EOL;
			continue;
		}
        $idx = 0;
        foreach($ar_277_vals as $isa) { 
			$ar_csvf = $isa['file']; 
			$ar_csvc = ibr_277_csv_claim_data($isa['claim']);
            //
            $ar_h[$idx]['file'] = $ar_csvf;
            $ar_h[$idx]['claim'] = $ar_csvc;
            $idx++;
        }
		$html_str .= ibr_277_html ($ar_h, $err_only);
	} else {	
		csv_edihist_log ("ibr_277_filetohtml: verification failed $filepath");
		$html_str .= "ibr_277_filetohtml: Error, validation failed $filepath <br />";
	} 	
	//
	return $html_str;
}


////////////////////////////////
// html for claim status -- ties into revision of csv files becasue they 
//                          are too hard to work with with error messages
//                          csv claims files will be simplified -- no messages
//                          just status and link to this function
//                          also x12 csv file record to be modified
////////////////////////////////

/**
 * parse a bht segment group into a multi-dimensonal array
 * 
 * @param array $segments the ST...SE segments of the response
 * @param array $delimiters the delimiters array
 * @return array
 */
function ibr_277_bht_array($segments, $delimiters) {
	// parse a bht segment group into html output
	if (is_array($delimiters) && array_keys($delimiters) == array('t', 'e', 's', 'r')) {
		$seg_d = $delimiters['t'];
		$elem_d = $delimiters['e'];
		$sub_d = $delimiters['s'];
		$rep_d = $delimiters['r'];
	} else {
		csv_edihist_log ("ibr_277_bht_html: invalid delimiters");
		return false;
	}
	
	if (!is_array($segments) || count($segments) == 0) {
		csv_edihist_log ("ibr_277_bht_html: invalid segments ");
		return false;
	}
	
	// open the codes lookup class
	$codes277 = new status_code_arrays();
	//
	// initialize variables
	$bht277_ar = array();
	$x12var = '';
	$idx = -1;
	foreach($segments as $segstr) {
		//
		$seg = explode($elem_d, $segstr);
		//
		// determine loops
		if ($seg[0] == 'ST') { $loopid = '0'; continue; }
		if ($seg[0] == 'BHT') { $loopid = '0'; }
		if ($seg[0] == 'HL')	{
			//echo "ibr_277_bht_array HL segment".HP_EOL;
			
			if ($seg[3] == '20') { $loopid = '2000A'; $ky = 'A'; }
			if ($seg[3] == '21') { $loopid = '2000B'; $ky = 'B'; }			
			if ($seg[3] == '19') { $loopid = '2000C'; $ky = 'C'; }					
			if ($seg[3] == '22') { $loopid = '2000D'; $ky = 'D'; }
			if ($seg[3] == 'PT') { $loopid = '2000D'; $ky = 'D'; }	
			if ($seg[3] == '23') { $loopid = '2000E'; $ky = 'E'; }	
		}
		if ($seg[0] == 'NM1'){
			if ( $loopid == '2000A') { $loopid = '2100A'; $level = 'Source'; }
			if ( $loopid == '2000B') { $loopid = '2100B'; $level = 'Receiver';  }	
			if ( $loopid == '2000C') { $loopid = '2100C'; $level = 'Provider';  }				
			if ( $loopid == '2000D') { $loopid = '2100D'; $level = 'Subscriber'; }	
			if ( $loopid == '2000E') { $loopid = '2100E'; $level = 'Dependent'; }
		}
		if ($seg[0] == 'TRN'){
			if ( $loopid == '2100A') { $loopid = '2200A'; $x12var = 'CA'; }
			if ( $loopid == '2100B') { $loopid = '2200B'; $x12var = ''; }	
			if ( $loopid == '2100C') { $loopid = '2200C'; $x12var = ''; }				
			if ( $loopid == '2100D') { $loopid = '2200D'; $x12var = ''; }	
		}		
		if ($seg[0] == 'SVC'){
			if ( $loopid == '2200C') { $loopid = '2220C'; }	
			if ( $loopid == '2200D') { $loopid = '2220D'; }	
			if ( $loopid == '2200E') { $loopid = '2220E'; }	
		}
		//
		// create $bht277_ar array so values can be organized for output
		// this design can work because the segment order is 
		// NM1* ~TRN* ~STC* ~REF* ~DTP* ~SVC* ~DTP	
		if ($loopid == '2000A') {
			// $ky should be 'A'
			$bht277_ar['A'] = array('loop'=>'', 'level'=>'', 'entity'=>'', 'name'=>'', 'id'=>'', 
									'sourcerefid'=>'', 'pername'=>'', 'percontact'=>'', 
			                        'pernumtype'=>'', 'pernumber'=>'', 'perextra'=>'', 
			                        'trace'=>'', 'dtrec'=>'', 'dtproc'=>'',
			                        'STC'=>array());
		}
		if ($loopid == '2000B') {
			$bht277_ar['B'] = array('loop'=>'', 'level'=>'', 'entity'=>'', 'name'=>'', 'id'=>'', 'trace'=>'', 
									'qtyacc'=>'', 'amtacc'=>'', 'qtyrej'=>'', 'amtrej'=>'',
									'STC'=>array(), 'dtsvc'=>'', );
		}
		if ($loopid == '2000C') {
			$bht277_ar['C'] = array('loop'=>'', 'level'=>'', 'entity'=>'', 'name'=>'', 'id'=>'', 'trace'=>'', 
									'qtyacc'=>'', 'amtacc'=>'', 'qtyrej'=>'', 'amtrej'=>'',
									'STC'=>array(), 'dtsvc'=>'' );
									//'stcat'=>'', 'ststat'=>'', 'stentity'=>'', 'stclqual'=>'',
									//'stdate'=>'','staction'=>'','stamount'=>'', 'stmessage'=>''
									//'stcat2'=>'','ststat2'=>'','stentity2'=>'','stclqual2'
									//'stcat3'=>'','ststat3'=>'','stentity3'=>'','stclqual3' 
		}	
		if ($loopid == '2000D') {
			$bht277_ar['D'] = array('loop'=>'', 'level'=>'', 'entity'=>'', 'name'=>'', 'id'=>'', 'trace'=>'', 
									'qtyacc'=>'', 'amtacc'=>'', 'qtyrej'=>'', 'amtrej'=>'',
									'STC'=>array(), 'dtsvc'=>'', 'SVC'=>array());
									//'stccat'=>'', 'stcstat'=>'', 'stcentity'=>'', 'stcclqual'=>'',
									//'stcdate'=>'','stcaction'=>'','stcamount'=>'', 'stcmessage'=>''
									//'stccat2'=>'','stcstat2'=>'','stcentity2'=>'','stcclqual2'
									//'stccat3'=>'','stcstat3'=>'','stcentity3'=>'','stcclqual3' 
			
		}			
		if ($loopid == '2000E') {
			$bht277_ar['E'] = array('loop'=>'', 'level'=>'', 'entity'=>'', 'name'=>'', 'id'=>'', 'trace'=>'', 
									'qtyacc'=>'', 'amtacc'=>'', 'qtyrej'=>'', 'amtrej'=>'',
									'STC'=>array(), 'dtsvc'=>'', 'SVC'=>array());
									//'stccat'=>'', 'stcstat'=>'', 'stcentity'=>'', 'stcclqual'=>'',
									//'stcdate'=>'','stcaction'=>'','stcamount'=>'', 'stcmessage'=>''
									//'stccat2'=>'','stcstat2'=>'','stcentity2'=>'','stcclqual2'
									//'stccat3'=>'','stcstat3'=>'','stcentity3'=>'','stcclqual3' 
			
		}						
		// 
		if ($seg[0] == 'BHT') {
			if (isset($seg[3])) { $sourcerefid = $seg[3]; }
		}
		
		if ($seg[0] == 'NM1') {	
			// get entity type
			$entity = $codes277->get_STC_Entity_Code($seg[1]);
			//$entity = ($seg[1]) ?  : '&nbsp;'; 
			// assemble name  last:$seg[3] first:$seg[4] middle:$seg[5]  
			$name = ($seg[2] == '1') ? $seg[3].', '.$seg[4].' '.$seg[5] : $seg[3];
			//
			$id = ($seg[8] == 'PI') ? 'EDI: '.$seg[9] : '';
			$id = ($seg[8] == 'XV') ? 'CMS: '.$seg[9] : $id;
			$id = ($seg[8] == '46') ? 'ETIN: '.$seg[9] : $id;
			$id = ($seg[8] == '24') ? 'EIN: '.$seg[9] : $id;
			$id = ($seg[8] == 'II') ? 'UHI: '.$seg[9] : $id;
			$id = ($seg[8] == 'MI') ? 'MbrID: '.$seg[9] : $id;
			$id = ($seg[8] == 'FI') ? 'TIN: '.$seg[9] : $id;
			$id = ($seg[8] == 'SV') ? 'SPN: '.$seg[9] : $id;
			$id = ($seg[8] == 'XX') ? 'NPI: '.$seg[9] : $id;
			if ($id == '' && isset($seg[8])) { $id = $seg[8].' '.$seg[9]; }
			// 
			$bht277_ar[$ky]['loop'] = $loopid;
			$bht277_ar[$ky]['level'] = $level;
			$bht277_ar[$ky]['entity'] = $entity[1];
			$bht277_ar[$ky]['name'] = $name;
			$bht277_ar[$ky]['id'] = $id;
			//
		}
		
		if ($seg[0] == 'PER') {
			// contact information is not sent in the 277CA variety
			$bht277_ar[$ky]['percontact'] = $seg[1];
			$bht277_ar[$ky]['pernumtype'] = $seg[2];
			$bht277_ar[$ky]['pernumber'] = $seg[3];
			if ( isset($seg[5]) && isset($seg[6]) ) {
				$bht277_ar[$ky]['perextra'] = $seg[5] . ' ' . $seg[6];
			}
		}
		
		if ($seg[0] == 'TRN') {	
			// transaction reference, but which one?
			$trtype = ($seg[1] == '1') ? 'This transaction ' : 'Referenced transaction ' ;
			// trace in loop
			$trace = ($loopid == '2200A') ? 'Xmit RefID: '.$seg[2] : ''; // ''837 BHT03: '.$seg[2] : '';
			// according to CMS Versions 5010 and D.0 & 3.0
			// 837 BHT03 is mapped to the 277CA response in the 2200B.TRN02 data element
			$trace = ($loopid == '2200B') ? (($x12var == 'CA') ? '276 BHT03: '.$seg[2] : '837 BHT03: '.$seg[2]) : $trace;
			$trace = ($loopid == '2200C' && $seg[2]) ? '837 BHT03: '.$seg[2] : $trace;
			$trace = ($loopid == '2200D') ? '837 CLM01: '.$seg[2] : $trace;
			$trace = ($loopid == '2200E') ? '837 CLM01: '.$seg[2] : $trace;
			if ($trace == '' && isset($seg[9])) { $trace = $loopid.' trace '.$seg[9]; }
			//
			$bht277_ar[$ky]['trace'] = ($trace) ? $trtype . ' ' . $trace : '';
			// from BHT segment
			if ($loopid == '2200A') { $bht277_ar[$ky]['sourcerefid'] = 'Reference: ' .$sourcerefid; }
		}
		
		if ($seg[0] == 'DTP') {	
			if ($seg[2] == 'D8') {
				$dt = substr($seg[3],0,4) .'-'. substr($seg[3],4,2) .'-'. substr($seg[3],6,2);
			}
			if ($seg[2] == 'D8') {
				if ($seg[1] == '050') { $bht277_ar[$ky]['dtrec'] = $dt; }
				if ($seg[1] == '009') { $bht277_ar[$ky]['dtproc'] = $dt; }
				if ($seg[1] == '472') { $bht277_ar[$ky]['dtsvc'] = $dt; }
			}
			if ($seg[2] == 'RD8') {
				$dt_ar = preg_split('/\D/', $seg[3]);
				$dt1 = substr($dt_ar[0],0,4) .'-'. substr($dt_ar[0],4,2) .'-'. substr($dt_ar[0],6,2);
				$dt2 = substr($dt_ar[1],0,4) .'-'. substr($dt_ar[1],4,2) .'-'. substr($dt_ar[1],6,2);
				if ($seg[1] == '472') { $bht277_ar[$ky]['dtsvc'] = $dt1 .' to '. $dt2; }
				if ($seg[1] == '434') { $bht277_ar[$ky]['dtstmt'] = $dt1 .' to '. $dt2; }
			}
		}
		
		if ($seg[0] == 'QTY') {
			// $ky should be B
			// debug
			//echo "QTY Segment in $loopid $segstr" .PHP_EOL;
			//
			if ($seg[1] == '90') { $bht277_ar[$ky]['qtyacc'] = $seg[2]; }
			if ($seg[1] == 'AA') { $bht277_ar[$ky]['qtyrej'] = $seg[2]; }
		}
		if ($seg[0] == 'AMT') {	
			// debug
			//echo "AMT Segment in $loopid $segstr" .PHP_EOL;
			//
			if ($seg[1] == 'YU') { $bht277_ar[$ky]['amtacc'] = sprintf("%0.02f", $seg[2]); }
			if ($seg[1] == 'YY') { $bht277_ar[$ky]['amtrej'] = sprintf("%0.02f", $seg[2]); }	
		}
		//'stcat''ststat''stentity''stclqual''stdate''staction''stamount'
		if ($seg[0] == 'STC') {
			// since there can be multiple STC segments in a loop
			$idx = count($bht277_ar[$ky]['STC']);
			// loop identification
			$bht277_ar[$ky]['STC'][$idx]['loop'] = $loopid;
			//
			if ( strpos($seg[1], $sub_d) ) {
				$stc01 = explode($sub_d, $seg[1]);
				if ( isset($stc01[0]) ) {
					$cat = $codes277->get_STC_Category_Code($stc01[0]);
					$bht277_ar[$ky]['STC'][$idx]['stccat'] = $cat[1];
				}
				if ( isset($stc01[1]) ) {
					$stat = $codes277->get_STC_Status_Code($stc01[1]);
					$bht277_ar[$ky]['STC'][$idx]['stcstat'] = $stat[1];	
				}
				if ( isset($stc01[2]) ) {
					$entity = $codes277->get_STC_Entity_Code($stc01[2]);
					$bht277_ar[$ky]['STC'][$idx]['stcentity'] = $entity[1];
				}
				if ( isset($stc01[3]) ) {
					// this seems to be used only in special situations, like a pharmacy claim
					$bht277_ar[$ky]['STC'][$idx]['stcclqual'] = $stc01[3];
				}
			}
			//
			$bht277_ar[$ky]['STC'][$idx]['stcdate'] = $seg[2];
			//
			if ( isset($seg[3]) ) {
				if ($seg[3]== 'WQ') { $bht277_ar[$ky]['STC'][$idx]['stcaction'] = $seg[3] . ' Accepted'; }
				if ($seg[3]== 'U') { $bht277_ar[$ky]['STC'][$idx]['stcaction'] = $seg[3] . ' Rejected'; }	
			}		
			if ( isset($seg[4]) ) {	
				$bht277_ar[$ky]['STC'][$idx]['stcamount'] = sprintf("%0.02f", $seg[4]);
			}
			if ( isset($seg[10]) ) {
				if ( strpos($seg[10], $sub_d) ) {
					$stc10 = explode($sub_d, $seg[10]);
					if ( isset($stc10[0]) ) {
						$cat = $codes277->get_STC_Category_Code($stc10[0]);
						$bht277_ar[$ky]['STC'][$idx]['stccat2'] = $cat[1];
					}
					if ( isset($stc10[1]) ) {
						$stat = $codes277->get_STC_Status_Code($stc10[1]);
						$bht277_ar[$ky]['STC'][$idx]['stcstat2'] = $stat[1];	
					}
					if ( isset($stc10[2]) ) {
						$entity = $codes277->get_STC_Entity_Code($stc10[2]);
						$bht277_ar[$ky]['STC'][$idx]['stcentity2'] = $entity[1];
					}
					if ( isset($stc10[3]) ) {
						// this seems to be used only in special situations, like a pharmacy claim
						$bht277_ar[$ky]['STC'][$idx]['stcclqual2'] = $stc10[3];
					}
				}
			}
			if ( isset($seg[11]) ) {
				if ( strpos($seg[11], $sub_d) ) {
					$stc11 = explode($sub_d, $seg[10]);
					if ( isset($stc11[0]) ) {
						$cat = $codes277->get_STC_Category_Code($stc11[0]);
						$bht277_ar[$ky]['STC'][$idx]['stccat3'] = $cat[1];
					}
					if ( isset($stc11[1]) ) {
						$stat = $codes277->get_STC_Status_Code($stc11[1]);
						$bht277_ar[$ky]['STC'][$idx]['stcstat3'] = $stat[1];	
					}
					if ( isset($stc11[2]) ) {
						$entity = $codes277->get_STC_Entity_Code($stc11[2]);
						$bht277_ar[$ky]['STC'][$idx]['stcentity3'] = $entity[1];
					}
					if ( isset($stc11[3]) ) {
						// this seems to be used only in special situations, like a pharmacy claim
						$bht277_ar[$ky]['STC'][$idx]['stcclqual3'] = $stc11[3];
					}
				}
			}													
			if ( isset($seg[12]) ) {
				$bht277_ar[$ky]['STC'][$idx]['stcmessage'] = $seg[12];
			}
		}
		
		if ($seg[0] == 'REF') {
			//'ref1K''refD9''refEA''refBLT''refEJ''refXZ''refVV''refFJ'
			$refkey = 'ref' . strtolower($seg[1]);
			$refstr = '';
			if ($seg[1] == '1K') { $refstr = 'Payer Ctl No '; }
			if ($seg[1] == 'BLT') { $refstr = 'Billing Type '; }
			if ($seg[1] == 'EJ') { $refstr = 'Pt Acct No '; }
			if ($seg[1] == 'XZ') { $refstr = 'Rx No '; }
			if ($seg[1] == 'VV') { $refstr = 'Voucher No '; }
			if ($seg[1] == 'FJ') { $refstr = 'Svc Item Info '; }
			if ($seg[1] == 'D9') { $refstr = 'Clearinghouse ID No '; }
			if ($seg[1] == 'TJ') { $refstr = 'Fed Tax ID No '; }
			//
			$bht277_ar[$ky][$refkey] = $refstr . $seg[2];
		}
		
		if ($seg[0] == 'SVC') {
			// SVC segment only occurs in 2200D or 2200E
			if ($ky == 'D' || $ky == 'E') {
				$idx = count($bht277_ar[$ky]['SVC']);
				// loop identification
				$bht277_ar[$ky]['SVC'][$idx]['loop'] = $loopid;
				// required elements
				$bht277_ar[$ky]['SVC'][$idx]['svccode'] = $seg[1];
				$bht277_ar[$ky]['SVC'][$idx]['svcfee'] = $seg[2];
				$bht277_ar[$ky]['SVC'][$idx]['svcpmt'] = $seg[3];
				// situational elements
				if (isset($seg[4])) { $bht277_ar[$ky]['SVC'][$idx]['svcnub'] = $seg[4]; }
				if (isset($seg[5])) { $bht277_ar[$ky]['SVC'][$idx]['svcqty'] = $seg[5]; }
				if (isset($seg[5])) { $bht277_ar[$ky]['SVC'][$idx]['svcqty'] = $seg[5]; }
				if (isset($seg[6])) { $bht277_ar[$ky]['SVC'][$idx]['svccompid'] = $seg[6]; }
				// required, but test anyway
				if (isset($seg[7])) { $bht277_ar[$ky]['SVC'][$idx]['svcqty'] = $seg[7]; } 
			}
		}
	}
	// return the array
	return $bht277_ar;
}				
				
/**
 * create an html table to display the claim status response
 * 
 * @param array $bhtarray the multidimesional array from {@see ibr_277_bht_array()}
 * @return string
 */				
function ibr_277_bhthtml($bhtarray) { 
	//
	if (is_array($bhtarray)) { 
		$bar = $bhtarray; 
	} else {
		csv_edihist_log("ibr_277_bhthtml: error, argument not array");
		return false;
	}
	// derive the caption
	$capstr = 'Claim Status: ';
	if ($bar['D']['trace']) { $capstr .= $bar['D']['trace']; }
	if (isset($bar['D']['ref1k'])) { $capstr .= ' ' . $bar['D']['ref1k']; }
	//
	$str_html = "<table class='bht277' cols=5 caption='$capstr'>
	<thead>
		<tr>
			<th>Level</th>
			<th colspan='4'>Information</th>
		</tr>
	</thead>
	<tbody>".PHP_EOL;
	//
	if (isset($bar['A'])) {
		// Source level
		$str_html .= "<tr class='leva'>
		    <td>{$bar['A']['level']}</td>
		    <td colspan='2'>{$bar['A']['entity']} {$bar['A']['name']}</td>
		    <td colspan='2'>{$bar['A']['id']}</td>
		</tr>
		<tr class='leva'>
		    <td>&nbsp;</td>
		    <td colspan='2'>{$bar['A']['sourcerefid']}</td>
		    <td colspan='2'>{$bar['A']['trace']} </td>
		</tr>
		<tr class='leva'>		
		    <td>&nbsp;</td>
		    <td colspan='4'>Recieved {$bar['A']['dtrec']}  Processed {$bar['A']['dtproc']}</td>
		 </tr>".PHP_EOL;
		 if ($bar['A']['percontact']) {
			 $str_html .= "<tr class='leva'>
			 <td>&nbsp;</td>
			   <td colspan='4'>{$bar['A']['pername']} {$bar['A']['percontact']} {$bar['A']['pernumtype']} {$bar['A']['pernumber']} </td>
			 </tr>".PHP_EOL;
		}
	}
	
	if (isset($bar['B'])) {	
		// Reciever level
		$acp = '';
		if ($bar['B']['qtyacc']) { $acp .= ' Accepted ' . $bar['B']['qtyacc'] . ': ' . $bar['B']['amtacc']; }
		if ($bar['B']['qtyrej']) { $acp .= ' Rejected ' . $bar['B']['qtyrej'] . ': ' . $bar['B']['amtrej']; }
		$str_html .= "<tr class='levb'>
		    <td>{$bar['B']['level']}</td>
		    <td colspan='4'>{$bar['B']['entity']} {$bar['B']['name']} {$bar['B']['id']}</td>
		</tr>
		<tr class='levb'>
		    <td>&nbsp;</td>
		    <td colspan='4'>$acp {$bar['B']['trace']} </td>
		</tr>".PHP_EOL;
		if (count($bar['B']['STC'])) {
			foreach($bar['B']['STC'] as $stc) {
				$str_html .= "<tr class='levb'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$stc['stcamount']} {$stc['stcaction']} {$stc['stccat']} </td>
		        </tr>".PHP_EOL;
			}
		}
	}
	
	if (isset($bar['C'])) {	
		// Reciever level
		$acp = ''; $ref = '';
		if ($bar['C']['qtyacc']) { $acp .= ' Accepted ' . $bar['C']['qtyacc'] . ': ' . $bar['C']['amtacc']; }
		if ($bar['C']['qtyrej']) { $acp .= ' Rejected ' . $bar['C']['qtyrej'] . ': ' . $bar['C']['amtrej']; }
		$str_html .= "<tr class='levc'>
		    <td>{$bar['C']['level']}</td>
		    <td colspan='4'>{$bar['C']['entity']} {$bar['C']['name']} {$bar['C']['id']}</td>
		</tr>".PHP_EOL;
		// trace is not really expected in 2000C
		if ($bar['C']['trace']) { $ref = $bar['C']['trace']; }
		// we expect a reftj (tax id) in 2000C
		if (isset($bar['C']['reftj'])) { $ref .=  ' ' .$bar['C']['reftj']; }
		if ($ref) {
			$str_html .= "<tr class='levc'>
				<td>&nbsp;</td>
				<td colspan='4'>$ref</td>
			</tr>".PHP_EOL;
		}
		if (count($bar['C']['STC'])) {
			foreach($bar['C']['STC'] as $stc) {
				$str_html .= "<tr class='levc'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$stc['stcamount']} {$stc['stcaction']} {$stc['stccat']} </td>
		        </tr>".PHP_EOL;
			}
		}
	}
	
	if (isset($bar['D'])) {	
		// Subscriber level
		// do not expect amounts or quantities in D
		$acp = ''; $ref = '';
		if ($bar['D']['qtyacc']) { $acp .= ' Accepted ' . $bar['D']['qtyacc'] . ': ' . $bar['D']['amtacc']; }
		if ($bar['D']['qtyrej']) { $acp .= ' Rejected ' . $bar['D']['qtyrej'] . ': ' . $bar['D']['amtrej']; }
		$str_html .= "<tr class='levd'>
		    <td>{$bar['D']['level']}</td>
		    <td colspan='4'>{$bar['D']['entity']} {$bar['D']['name']} {$bar['D']['id']}</td>
		</tr>".PHP_EOL;
		if ($bar['D']['dtsvc']) {
			$str_html .= "<tr class='levd'>
			    <td>&nbsp;</td>
			    <td colspan='4'>Service Date: {$bar['D']['dtsvc']}</td>
			</tr>".PHP_EOL;
		}			
		// our pid-encounter and possibly payer claim number and intermediary trace
		if (isset($bar['D']['trace'])) { $ref .= $bar['D']['trace']; }
		if (isset($bar['D']['ref1k'])) { $ref .= ' ' . $bar['D']['ref1k']; }
		if (isset($bar['D']['refvv'])) { $ref .= ' ' . $bar['D']['refvv']; }
		if (isset($bar['D']['refd9'])) { $ref .= ' ' . $bar['D']['refd9']; }
		if ($ref) {	
			$str_html .= "<tr class='levd'>
			    <td>&nbsp;</td>
			    <td colspan='4'>$ref</td>
			</tr>".PHP_EOL;
		}

		if (count($bar['D']['STC'])) {
			foreach($bar['D']['STC'] as $stc) {
				$str_html .= "<tr class='levd'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$stc['stcamount']} {$stc['stcaction']} {$stc['stccat']} </td>
		        </tr>
		        <tr class='levd'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$stc['stcmessage']}</td>
		        </tr>".PHP_EOL;
			}
		}
		
		if (count($bar['D']['SVC'])) {
			foreach($bar['D']['SVC'] as $svc) {
				$str_html .= "<tr class='levd'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$svc['svccode']} {$svc['svcfee']} {$svc['svcpmt']} {$svc['svcqty']} </td>
		        </tr>".PHP_EOL;
			}
		}		
	}
		
	if (isset($bar['E'])) {	
		// Subscriber level
		// do not expect amounts or quantities
		$acp = ''; $ref = '';
		if ($bar['E']['qtyacc']) { $acp .= ' Accepted ' . $bar['E']['qtyacc'] . ': ' . $bar['E']['amtacc']; }
		if ($bar['E']['qtyrej']) { $rej .= ' Rejected ' . $bar['E']['qtyrej'] . ': ' . $bar['E']['amtrej']; }
		$str_html .= "<tr class='leve'>
		    <td>{$bar['E']['level']}</td>
		    <td colspan='4'>{$bar['E']['entity']} {$bar['E']['name']} {$bar['E']['id']}</td>
		</tr>".PHP_EOL;
		if ($bar['E']['dtsvc']) {
			$str_html .= "<tr class='leve'>
			    <td>&nbsp;</td>
			    <td colspan='4'>Service Date: {$bar['E']['dtsvc']}</td>
			</tr>".PHP_EOL;
		}			
		// our pid-encounter and possibly payer claim number and intermediary trace
		if (isset($bar['E']['trace'])) { $ref .= $bar['E']['trace']; }
		if (isset($bar['E']['ref1k'])) { $ref .= ' ' . $bar['E']['ref1k']; }
		if (isset($bar['D']['refvv'])) { $ref .= ' ' . $bar['D']['refvv']; }
		if (isset($bar['E']['refd9'])) { $ref .= ' ' . $bar['E']['refd9']; }
		if ($ref) {	
			$str_html .= "<tr class='leve'>
			    <td>&nbsp;</td>
			    <td colspan='4'>$ref</td>
			</tr>".PHP_EOL;
		}

		if (count($bar['E']['STC'])) {
			foreach($bar['E']['STC'] as $stc) {
				$str_html .= "<tr class='leve'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$stc['stcamount']} {$stc['stcaction']} {$stc['stccat']} </td>
		        </tr>
		        <tr class='leve'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$stc['stcmessage']}</td>
		        </tr>".PHP_EOL;
			}
		}
		
		if (count($bar['E']['SVC'])) {
			foreach($bar['E']['SVC'] as $svc) {
				$str_html .= "<tr class='leve'>
		          <td>&nbsp;</td>
		          <td colspan='4'>{$svc['svccode']} Fee: {$svc['svcfee']} Pmt: {$svc['svcpmt']} Qty: {$svc['svcqty']} </td>
		        </tr>".PHP_EOL;
			}
		}		
	}
	$str_html .= "</tbody>
	</table>".PHP_EOL;
	//
	return $str_html;
}
			


/**
 * determine the array_slice parameters for the particular claim status transaction
 * 
 * parameters to slice ST...SE transaction segments out of the the file segments array 
 * 
 * @param array  the segments array of the 277 file
 * @param array  the delimiters array
 * @param string  the bht03 value we are looking for in loop 2000C TRN
 * @param string  the patient control (pid-enc or encounter) from 837 CLM01
 * @param string the ST02 number from claims_f277.csv, from 277 file initial processing
 * @param string  the ISA control number from the envelope wrapping the ST02 number
 * @return array the parameters for array_slice (start, count, searchval)
 */	 	
function ibr_277_bhtblock($segments, $delimiters, $clm01 = '', $bht03 = '', $st02 = '') {
	// derive the array_slice parameters for the bht segments block
	// need to add the isa13 parameter, multiple ISA--IEA in 277 files
	$useclm = false;
	$useenc = false;
	$usebht = false;
	$usest = false;
	$isfound = false;
	$slice_ar = array();
	$isanum = '';
	//
	$elem_d = $delimiters['e'];
	//
	if (!is_array($segments)) {
		csv_edihist_log ("ibr_277_bhtblock: segment array error");
		// debug
		//echo "ibr_277_bhtblock: segment array error".PHP_EOL;
		return false;
	}
	if (!$clm01 && !$bht03 && !$st02) {
		csv_edihist_log ("ibr_277_bhtblock: no specifier arguments given");
		return false;
	} elseif (($bht03 == '0123' || strlen($bht03) != 13) && !$clm01 && !$st02) { 
		// OpenEMR presently gives all BHT03 value of '0123' in batch files
		//echo "ibr_277_bhtblock: bht03 useless $bht03 with no clm01 or st02".PHP_EOL;
		csv_edihist_log ("bht03 useless $bht03 with no isa13 or st02");
		return false;
	} elseif (strpos($st02, '_')) {
		$dpos = strpos($st02, '_');
		$srchval = substr($st02, $dpos+1);
		$isanum = substr($st02, 0, $dpos);
		//$srchval = (strlen($st02) < 4) ? str_pad ($st02, 4, "0", STR_PAD_LEFT) : strval($st02);
		$usest = true;
	} elseif (strlen($bht03) == 13) {
		$srchval = strval($bht03);
		$usebht = true;
	} elseif (strlen($clm01) && strpos($clm01, '-')) { 
		$useclm = true;
		$srchval = strval($clm01);
	} elseif (strlen($clm01) && !strpos($clm01, '-')) {
		$useenc = true;
		$srchval = strval($clm01);
	}
	//
	$isastr = 'ISA'.$elem_d;
	$ststr = 'ST'.$elem_d;
	$trnstr = 'TRN'.$elem_d.'2'.$elem_d;
	$sestr = 'SE'.$elem_d;
	$idx = -1;
	//
	foreach($segments as $segstr) {
		// increment index
		$idx++;
		// check ISA envelope
		if (substr($segstr, 0, 4) == $isastr) { 
			$seg = explode($elem_d, $segstr); 
			$isisa = ($seg[13] == $isanum) ? true : false; 
			continue;
		}
		if (substr($segstr, 0, 4) == 'IEA'.$elem_d) { 
			$isisa = false; 
			continue;
		}
		// get position of ST segment
		if (substr($segstr, 0, 3) ==  $ststr) { 
			$stpos = $idx;
			$isfound = false;
			$seg = explode($elem_d, $segstr); 
			$stnum = strval($seg[2]);
			if ($usest && $isisa && ($stnum == $srchval)) {
				$isfound = true;
			}
			continue;
		}
		// 
		if (substr($segstr, 0, 6) ==  $trnstr) {
			$seg = explode($elem_d, $segstr); 
			if ($seg[2] == $srchval) {
				$isfound = true;
				$slice_ar[0] = $stpos;
			}
						
			if ($useenc) {
				if (substr($seg[2], -strlen($srchval)) == $srchval) {
					$isfound = true;
					$slice_ar[0] = $stpos;
				}
			}
			continue;
		}
		//
		if ($isfound && (substr($segstr, 0, 3) ==  $sestr) )  { 							
			$seg = explode($elem_d, $segstr); 
			if ($stnum == $seg[2]) {
				$slice_ar[1] = $seg[1];	
				$slice_ar[2] = $srchval;
			}
			//
			if ($idx - $stpos + 1 != $seg[1]) {
				$ct = $idx - $stpos + 1;
				csv_edihist_log ("ibr_277_bhtblock: $srchval st count error se02 {$seg[1]} count $ct ");
			}
			//
			// we expect only one match in a file
			break;
		}
	}
	//
	// return the slice or the slice parameters?  slice parameters
	return $slice_ar;
}

/**
 * create a display for an individual claim status response	
 * 
 * @uses csv_file_by_controlnum()
 * @uses csv_x12_segments()
 * @uses ibr_277_bhtblock()
 * @uses ibr_277_bht_array()
 * @uses ibr_277_bhthtml()
 * @param string  $filename the filename
 * @param string  $isa13 the isa13 control number for the file
 * @param string  $bht03 the identifier from the 837 bht segment
 * @param string  $clm01 the pid-encounter from the 837 clm segment
 * @param string  $st02 the st number from the 277 file
 * @return string  either an error message or a table with the information from the response
 */			
function ibr_277_response_html($filename = '', $isa13 = '', $bht03 = '', $clm01 = '', $st02 = '' ) {
	// create a display for an individual 277 response
	$html_str = '';
	//
	if (!$filename && !$isa13) {
		csv_edihist_log ("ibr_277_response_html: called with no file arguments");
		$html_str .= "Error, no file given<br />".PHP_EOL;
		return $html_str;
	} elseif (!$filename && $isa13) {	
		$fn = csv_file_by_controlnum('f277', $isa13);
	} elseif ($filename) { 
		$fn = basename($filename);
	}		
	
	if ($fn) {
		//
		$ar_277_seg = csv_x12_segments($fn, "f277", false);
	}
	//
	if ($bht03 || $clm01 || $st02) {
		//
		$sliceparams = ibr_277_bhtblock($ar_277_seg['segments'], $ar_277_seg['delimiters'], $clm01, $bht03, $st02);
		if ($sliceparams) {
			$bhtsegs = array_slice($ar_277_seg['segments'], $sliceparams[0], $sliceparams[1]);
			$bht_ar = ibr_277_bht_array($bhtsegs, $ar_277_seg['delimiters']);
			$bht_html = ibr_277_bhthtml($bht_ar);
			if ($bht_html) { 
				$html_str .= $bht_html;
			} else {
				$html_str .= "Error encountered in generating display <br />".PHP_EOL;
			}
				
		} else {
			$html_str .= "Did not find status for $bht03 $clm01 $st02 in $fn <br />".PHP_EOL;
		}
	} else {
		csv_edihist_log ("ibr_277_response_html: called with no claim identifying arguments");
		$html_str .= "Error, no claim identification given<br />".PHP_EOL;
	}
	//
	return $html_str;				
}	
	
	
?>