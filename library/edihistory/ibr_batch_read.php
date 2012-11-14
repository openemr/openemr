<?php
/**
 *  ibr_batch_read.php
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 or later.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *  <http://opensource.org/licenses/gpl-license.php>
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *  
 * 
 * This file has functions to deal with OpenEMR batch files when identifying
 * rejected claims in .997 files, from the Availity LLC clearinghouse.  
 * The concept is to use a .csv to store batch file names, control id numbers, and claim counts.
 * When the Availity .997 file is read, we can get a control number and claim number for
 * each claim rejected at the initial level -- these are never submitted to payers.
 * We use the control number to find the claim file and the claim number to find the 
 * encounter and patient id.  
 * Then we can manually review the claim and hopefully resolve the error.
 * 
 * Also a csv file of claim information is created.  This allows one to see a list
 * of all claims created and also allows other scripts to access claim information
 * more easily.
 * 
 * Also functions to output the text of a particular claim
 * 
 * @link: http://www.open-emr.org
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
  
// a security measure to prevent direct web access to this file
// must be accessed through the main calling script ibr_history.php 
// from admin at rune-city dot com;  found in php manual
// if (!defined('SITE_IN')) die('Direct access not allowed!');

// these are hardcoded into OpenEMR batch files   
if (!defined("SEG_ELEM_DELIM")) define( "SEG_ELEM_DELIM" , "*");
if (!defined("SEG_TERM_DELIM")) define( "SEG_TERM_DELIM" , "~");  	
//					  

/**
 * Derives the batch file date from the file name
 * 
 * @param string $file_name - batch file name
 * @return string      date in YYYMMDD format
 */
function ibr_batch_datefromfilename($file_name) {
	//
	// this function relies on OpenEMR batch file name convention
	//
	$fn = basename($file_name);
	//
	// try to get file date by file name
	$isdt = preg_match('/201[0-9]-[01]{1}[0-9]{1}-[0-3]{1}[0-9]{1}/', $fn, $match);
	//
	$dtstr = str_replace('-', '', $match[0]);
	//
	if ($isdt && strlen($dtstr) == 8) {
		$f_date = $dtstr;
	} else {
		$f_date = FALSE;
	}
	//
	return $f_date;
}


/**
 * creates an array of controlnum => file name
 * 
 * @param int $length=20  the number of most recent files to include
 * @return array          key => value is control_num => file name
 */
function ibr_batch_by_ctln($length=20) {
	// 
	// read the .csv file into an array 
	// 
	// hopefully OpenEMR does not repeat the control_num
	$p = csv_parameters('batch');
	//$batch_csv_path = dirname(__FILE__).$p['files_csv'];
    $batch_csv_path = $p['files_csv'];
	//array('Date', 'FileName', 'Ctn_837', 'claim_ct', 'x12_partner');
	$ar_ctlid_batch = array();
	$idx = 0;
	if (($fh1 = fopen($batch_csv_path, "r")) !== FALSE) {
	    while (($data = fgetcsv($fh1, 1000, ",")) !== FALSE) {
			$idx++;
	        $ar_ctlid_batch[$data[2]] = $data[1];
	        if ($idx >= $length) { break; }
		}
		fclose($fh1);
	} else {
		return false;
	}
	//
	return $ar_ctlid_batch;
} // end function ibr_batch_by_ctln


/**
 * look in the csv table claims_batch.csv and find the pid-encounter 
 *
 * @deprecated
 * @uses csv_search_record() 
 * @param string $bht837     the icn and st numbers concatenated 
 * @return string          the pid-encounter, expect only one match
 */
function ibr_batch_find_pid_with_ctl_st ($bht837) { 
	//
	$bval = ($bht837 ) ? trim($bht837) : '';
	if (strlen($bval) == 13) {
		$b = $bval;
	} else {
		csv_edihist_log("ibr_batch_find_pid_with_ctl_st: invalid argument $bht837");
		return false;
	}
	//$search = array('s_val'=>$b, 's_col'=>7, 'r_cols'=>array(2));
    $search = array('s_val'=>$b, 's_col'=>4, 'r_cols'=>array(2));
	$finfo = csv_search_record('batch', 'claim', $search, "1");	
	if (is_array($finfo) && count($finfo) ) {
		// we seem to have found something
		return $finfo[0][0];
	} else {
		// nothing found
		return false;
	}
 }	


/**
 * get the pid-encounter from the batch file using the ST number.
 * 
 * ST number is is how claims are identified in 999 files
 * 
 * @uses csv_parameters()
 * @uses csv_verify_file() 
 * @param string $st_clm_num   the ST02 number in the batch file
 * @param string $batch_file   the batch file name
 * @return string              the pid-encounter
 */
function ibr_batch_get_pid ( $st_clm_num, $batch_file ) {
	//
	// get the pid-encounter from the batch file
	//
    if (strlen($st_clm_num) == 13) { 
		$st_num = substr($st_clm_num, -4); 
        if (!$batch_file) {
            $btctln = substr($st_clm_num, 0, 9);
            $batch_file = csv_file_by_controlnum('batch', $btctln);
        }
	} elseif ( strlen($st_clm_num) < 4 ) { 
		$st_num = str_pad ($st_clm_num, 4, "0", STR_PAD_LEFT); 
	} else {
		$st_num = trim($st_clm_num);
	}
    //
	$bfullpath = csv_verify_file($batch_file, 'batch');
	if (!$bfullpath) {
		$str_st = "Error: failed to read $batch_file" . PHP_EOL;
		return FALSE;
	} else {
		$bstr = file_get_contents($bfullpath);	
	}
	if (!$bstr) { 
		$str_st = "Error: failed to read $batch_file" . PHP_EOL; 
		return FALSE; 
	}
	//
	
	$seg_st = "ST*837*" . $st_num;  // particular ST block
	//
	$seg_clm = "CLM*";
	//
	$st_pos = strpos($bstr, $seg_st, 0);
	if ( $st_pos == FALSE ) { 
		csv_edihist_log("ibr_batch_get_pid: $st_num not found in $batch_file" ); 
		return FALSE;
	}		
	$clm_pos = strpos($bstr, $seg_clm, $st_pos);
	// pid-encounter is first element of CLM segment
	$epos1 = strpos($bstr, "*", $clm_pos);
	$epos2 = strpos($bstr, "*", $epos1+1);
	//
	$pe = substr($bstr, $epos1+1, $epos2-$epos1-1);
	//
	return $pe;
}

/**
 * increment loop values ($lpval is a reference)
 * 
 * @param $lptest   the prospective loop value 
 * @param &$lpval    the present loop value -- reassigned here
 * @return integer  value from strcmp()
 */  
function ibr_batch_change_loop($lptest, &$lpval) {

	// strcmp($str1,$str2) Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
	if ( strcmp($lptest, $lpval) > 0) { 
		//echo "$lptest greater than $lpval" .PHP_EOL;
		$lpval = $lptest;
	} 
	return strcmp($lptest, $lpval);
}

/**
 * Create an html table of Loop | Segment for viewing x12 v5010A1 claims 
 * 
 * @uses ibr_batch_change_loop()
 * @param string $st_block  substring of batch file ST...SE segments making up a claim 
 * @return string            html table
 */     
function ibr_batch_st_html($st_block, $batchname='', $claimid='', $message='') {
	//
	// count the segment terminators
	$seg_ct = substr_count ($st_block, SEG_TERM_DELIM);
	if (!$seg_ct) {
		$st_html = "segment terminator error <br />" .PHP_EOL;
		return $st_html;
	}
	$capstr = 'Batch Claim';
	if ($batchname) { $capstr = $batchname; }
	if ($claimid) { $capstr .= " Claim: $claimid"; }
	$st_html = "<div class='filetext'>".PHP_EOL;            //."<pre><code>".PHP_EOL;
	$st_html .= ($message) ? "<p>$message</p>".PHP_EOL : '';
	$st_html .= "<table id='$claimid' cols=3 class='batchst'><caption>$capstr</caption>".PHP_EOL;
	$st_html .= "<thead>".PHP_EOL."<tr>".PHP_EOL."<th>Loop</th><th>Num</th><th>Segment</th>".PHP_EOL."</tr>".PHP_EOL;
	$st_html .= "</thead>".PHP_EOL."<tbody>".PHP_EOL;
	$loop = "";
	//$hasSBR = FALSE;
	//$hasCTP = FALSE;
	//$hasCLM = FALSE; 
	$lx_ct = 0;
	$idx = 0;
	
	//$st_segs = explode("~",$st_block);
	$st_segs = explode(SEG_TERM_DELIM, $st_block);
	foreach($st_segs as $sts) {
		$idx++;
		if ($idx >= $seg_ct && !$sts) { break; }   // the last element of $st_segs may be just \n or empty 
		$segstr = trim($sts);
		//$seg = explode("*", $segstr );
		$seg = explode(SEG_ELEM_DELIM, $segstr );
		if ($seg[0] == "ST") {
			$loop = '0';
			//$hasCLM = FALSE;
			$st_html .= "<tr><td class='btloop'>Header</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;
		}
		if ($seg[0] == "BHT") {
			$loop = '0';
			$st_html .= "<tr><td class='btloop'>Begin</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;
		}
		// organize this by loops -- first determine the loop
		// we do assume the $loop is defined for some choices further down the list
		//   many loops begin with the NM1 segment
		if ($seg[0] == "NM1") {
			// SUBMITTER NAME
			if ($seg[1] == "41") { ibr_batch_change_loop('1000A', $loop); }
			// RECEIVER NAME
			if ($seg[1] == "40") { ibr_batch_change_loop('1000B', $loop) ; }							
			// Billing Provider Name 
			if ($seg[1] == "85") { 
				ibr_batch_change_loop('2010AA', $loop);
				// OTHER PAYER BILLING PROVIDER 
				if (strcmp($loop, '2300') > 0) { ibr_batch_change_loop('2330G', $loop); }	
			}
			// PAY-TO ADDRESS NAME 			
			if ($seg[1] == "87") { 
				ibr_batch_change_loop('2010AB', $loop); 
				if (strcmp($loop, '2300') > 0) { ibr_batch_change_loop('2330G', $loop); }	
			}
			// PAY TO PLAN NAME		
			if ($seg[1] == "PE") { 
				ibr_batch_change_loop('2010AC', $loop); 
				if (strcmp($loop, '2300') > 0) { ibr_batch_change_loop('2330B', $loop); }
			}			
			// SUBSCRIBER NAME
			if ($seg[1] == "IL") { 
				ibr_batch_change_loop('2010BA', $loop);
				//OTHER SUBSCRIBER NAME
				if (strcmp($loop, '2300') > 0) { ibr_batch_change_loop('2330A', $loop); }	
				
			}
			// PAYER NAME
			if ($seg[1] == "PR") { 
				ibr_batch_change_loop('2010BB', $loop);
				//OTHER PAYER NAME
				if (strcmp($loop, '2300') > 0) { ibr_batch_change_loop('2330B', $loop); }
			}
			//	
			// PATIENT NAME			
			if ($seg[1] == "QC") { 
				ibr_batch_change_loop('2010CA', $loop);
				// patient name is only in 2010CA loop -- applies to all segments in 2300
				//if (strcmp($loop, '2300') > 0) { ibr_batch_change_loop('2330B', $loop); }
			}
			// REFERRING PROVIDER NAME
			if ($seg[1] == "DN" || $seg[1] == "P3" )  {  
				ibr_batch_change_loop('2310A', $loop);
				// OTHER PAYER REFERRING PROVIDER 
				if (strcmp($loop, '2310A') > 0) { ibr_batch_change_loop('2330C', $loop); }
			}	 
			//
			// RENDERING PROVIDER NAME                         
			if ($seg[1] == "82") { 
				ibr_batch_change_loop('2310B', $loop);
				// OTHER PAYER RENDERING PROVIDER 
				if (strcmp($loop, '2310B') > 0) { ibr_batch_change_loop('2330D', $loop); }
				// RENDERING PROVIDER NAME  
				if (strcmp(substr($loop,0, 4), '2400') > 0) { ibr_batch_change_loop('2420A', $loop); }
			}
			// SERVICE FACILITY LOCATION
			if ($seg[1] == "77") { 
				ibr_batch_change_loop('2310C', $loop); 
				// OTHER PAYER SERVICE FACILITY LOCATION
				if (strcmp($loop, '2310C') > 0) { ibr_batch_change_loop('2330E', $loop); }
			}
			// SUPERVISING PROVIDER NAME  
			if ($seg[1] == "DQ") { 
				ibr_batch_change_loop('2310D', $loop);
				// OTHER PAYER SUPERVISING PROVIDER 
				if (strcmp($loop, '2310D') > 0) { ibr_batch_change_loop('2330F', $loop); }
			}
			// AMBULANCE PICK UP LOCATION
			if ($seg[1] == "PW") { 
				ibr_batch_change_loop('2310E', $loop);
				if (strcmp($loop, '2310E') > 0) { ibr_batch_change_loop('2420G', $loop); }
			}
			// AMBULANCE DROP OFF LOCATION
			if ($seg[1] == "45") { 
				ibr_batch_change_loop('2310F', $loop);
				if (strcmp($loop, '2310F') > 0) { ibr_batch_change_loop('2420H', $loop); }
			}
			// PURCHASED SERVICE PROVIDER NAME
			if ($seg[1] == "QB") { $loop = '2420B' ; }
			// ORDERING PROVIDER NAME
			if ($seg[1] == "DK") { $loop = '2420E' ; }
			
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;			
		}
		
		if ($seg[0] == "HL") {	
			if ($seg[1] == "1") { $loop = '2000A'; }
			if ($seg[1] > "1") { 
				if ($seg[3] == "22") { $loop = '2000B'; }
				if ($seg[3] == "23") { $loop = '2000C'; }
				$haschild = ($seg[4] == "1");
			}
			$hl_lev = $seg[3];
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;	
		}
		
		if ($seg[0] == "CLM") { 
			$loop = '2300'; 
			//$hasCLM = TRUE; 
			$lx_ct = 0;
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;			
		}
		//	Subsriber or 
		if ($seg[0] == "SBR") {
			ibr_batch_change_loop('2000B', $loop);
			//$loop = ($hl_lev == "22") ? '2000B' : '2320';  
			// OTHER SUBSCRIBER INFORMATION
			//   do not test for loop value, just restart the loop 2320
			//if (strcmp(substr($loop, 0, 4), '2300') > 0) { ibr_batch_change_loop('2320', $loop); }
			if (strcmp(substr($loop, 0, 4), '2300') > 0) { $loop = '2320'; }
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;	
		}

		
		if ($seg[0] == "CAS") { 
			$loop = ($lx_ct) ? '2430' : '2320'; 
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;				
		}
		
		if ($seg[0] == "LX") { 
			$loop = '2400'; 
			$lx_ct++; 
			$newLX = ($seg[1] == $lx_ct) ? TRUE : FALSE;
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;	
		}	
		if ($seg[0] == "SV1") { 
			$loop = '2400';	
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;			
		}	
		
		if ($seg[0] == "CTP") { 
			$loop = '2410'; 
			//$hasCTP = TRUE;	
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;			
		}	
		
		if ($seg[0] == "LQ") { 
			$loop = '2440';	
			$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;				
		}
		if ($seg[0] == "SE") { 
			$loop = '0';	
			$st_html .= "<tr><td class='btloop'>Trailer</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;
			continue;				
		}
		//
		// for all the segments that do not begin loops										
		$st_html .= "<tr><td class='btloop'>$loop</td><td class='btloop'>$idx</td><td class='btseg'>$segstr</td></tr>" .PHP_EOL;	
		//
	}
	//
	$st_html .= "</tbody></table>".PHP_EOL;
	$st_html .= "<p></p>".PHP_EOL."</div>".PHP_EOL;
	//
	return $st_html;
}


/**
 * Select the substring of ST...SE segments comprising a claim in a batch file.
 * 
 * one of $st_clm_num or $pid_enctr must be provided
 * the batch file, if unknown and given as '', may be found if a $pid_enctr is supplied
 * 
 * @uses csv_parameters()
 * @uses csv_verify_file()
 * @uses ibr_batch_st_html()
 * @uses csv_file_with_pid_enctr()
 * @param string $batch_file     the batch file name
 * @param string $st_clm_num     the ST02 number in the batch file
 * @param string $pid_enctr      the pid-encounter identifying the claim
 * @param boolean $plain_text    just return the ST...SE segments substring
 * @return string                html table from ibr_batch_st_html()
 */
function ibr_batch_get_st_block ($batch_file, $st_clm_num=NULL, $pid_enctr=NULL, $plain_text=FALSE) {
	//
	// get the substring of the batch file containing the ST number
	//
	if ( is_null($st_clm_num) && is_null($pid_enctr) ) {
		csv_edihist_log("ibr_batch_get_st_block: both st_clm_num and pid_enctr were NULL");
		$out_str .= "ibr_batch_get_st_block: you must provide either ST number or pid-encounter <br />" . PHP_EOL;
		return $out_str;
	}
	//
	$out_str = "";
	$msg_str = '';
	$btchname = basename($batch_file);
	//
	$bfullpath = ($batch_file) ? csv_verify_file($batch_file, 'batch') : '';	
	if ($bfullpath) {	
		$out_str .=	$batch_file .PHP_EOL;
		$btchname = basename($bfullpath);
	} else {
		// possibly batch file was not determined when parsing a response file
		if ($batch_file && !$bfullpath) {
            if ( strlen($batch_file) >= 9 ) {
                // try control number search
                $btchname = csv_file_by_controlnum('batch', $batch_file);
                $bfullpath = csv_verify_file($btchname, 'batch');
                if (!$bfullpath) {
                    $msg_str .=	"batch file not found: $batch_file". PHP_EOL;
                }
            } else {
                $msg_str .=	"batch file not found: $batch_file". PHP_EOL;
            }
		} elseif (!$batch_file) {
			$msg_str .=	"batch file name not supplied";
		}
		if (!$bfullpath && $pid_enctr) {
			$pe1 = trim($pid_enctr);
            $btnm1 = csv_file_with_pid_enctr($pe1, 'batch', 'ptctln' );
			// select the last file found
			$btnm2 = count($btnm1) ? $btnm1[count($btnm1)-1][1] : FALSE;
			if ($btnm2) {
				$bfullpath = csv_verify_file($btnm2, 'batch');
				for($i=0; $i<count($btnm1); $i++) { 
					$msg_str .=	"found {$btnm1[$i][1]}". PHP_EOL;
				}
				$msg_str .=	"using $btnm2".PHP_EOL;
			} else {
				$bfullpath = FALSE;
			}
			//
		} elseif ( !$bfullpath && strlen($st_clm_num) == 13 ) {
			$ctln = substr($st_clm_num, 0, 9);
			$stnum = substr($st_clm_num, -4);
            $btnm1 = csv_file_by_controlnum('batch', $ctln);
			$bfullpath = ($btnm1) ? csv_verify_file($btnm1, 'batch') : FALSE;
		} else {
			csv_edihist_log("ibr_batch_get_st_block: batch file not found"); 
			return false; 
		}
	}
	// Now, if we determined the file name		
	$bstr = ($bfullpath) ? file_get_contents($bfullpath) : FALSE;	
	if (!$bstr) { 
		csv_edihist_log("ibr_batch_get_st_block: failed to read file $btchname");
		return false; 
	}
	//
	if ($pid_enctr) { 
		$pe = trim($pid_enctr);
		$seg_clm="CLM*$pe*"; 
		$seg_st = "ST*837*";
		$clm_pos = strpos($bstr, $seg_clm, 0);
		// php directions for strrpos are wrong
		$st_pos = strrpos(substr($bstr, 0, $clm_pos), $seg_st);		
		$seg_st = substr($bstr, $st_pos, 11);
		$st_num = substr($seg_st, -4);
	} elseif ($st_clm_num) {
		$st_num = substr($st_clm_num, -4) ? substr($st_clm_num, -4) : trim($st_clm_num);
		if ( strlen($st_num) < 4 ) { $st_num = str_pad ($st_num, 4, "0", STR_PAD_LEFT); }
		$seg_st = 'ST'.SEG_ELEM_DELIM.'837'.SEG_ELEM_DELIM.$st_num;  // particular ST block
		$st_pos = strpos($bstr, $seg_st, 0);	
	} else {
		csv_edihist_log("ibr_batch_get_st_block: ST number and Encounter missing");
		return FALSE;
	}		
	// break it off if $st_pos is not found
	if ($st_pos === FALSE ) { 
		csv_edihist_log("ibr_batch_get_st_block: ST $st_num not found in $btchname");
		return FALSE;
	}	
	//
	$seg_se = SEG_TERM_DELIM.'SE'.SEG_ELEM_DELIM;
	//
	$se1 = strpos($bstr, $seg_se, $st_pos);
	$se2 = strpos($bstr, SEG_TERM_DELIM, $se1+1 ) + 1;
	//
	$str_st = substr($bstr, $st_pos, $se2-$st_pos);
	//
	if ($plain_text) {
		$out_str = $str_st;
	} else {
		$bnm = basename($bfullpath);
		$clmid = ($pe) ? $pe : $st_num;
		$out_str = ibr_batch_st_html($str_st, $bnm, $clmid, $msg_str);
	}
	//
	return $out_str;
}

/**
 * Search a batch file to identify a claim by the ST number
 * 
 * Claims identified in .997/999 files must be located in batch files
 * The .997 file is per a transaction control number in the TA1 segment, 
 * which is used to identify the batch file.  However, the TA1 segment may
 * not be provided in the .999 file, so the batch file may be unknown. 
 * The ST02 and CLM01 values may also be obtrained fromt the .999 file and
 * we can use those to try and identify the batch file, but we may
 * identify the wrong file.  The .997 file gives the limited information
 * for the particular claim, so this function is to get more information on the claim.
 * array($st_num, $enc_pid, $enc_enctr, $enc_pt_lname, $enc_pt_fname, $enc_pt_dob, $batch_file);
 * 
 * @see ibr_997_rejects() in ibr_997_read.php
 * @todo accept an array of $st_clm_num so batch file needs only one read 
 * @uses csv_file_by_controlnum()
 * @uses csv_verify_file()
 * @param string $stnum      the ST number of a claim
 * @param string $batchnam   the batch file contents which contain the st_num
 * @param string $pidenctr   the pid-encounter for the desired claim	
 * @return array
 */
function ibr_batch_get_st_info ($st_num, $batchname='', $pidenctr='') { 
	//ibr_batch_get_st_info($ak2st02, $bt_file, $ctx302)
	$stnum = ($st_num) ? strval($st_num) : '';
	$pe = ($pidenctr) ? strval($pidenctr) : '';
	$btfname = '';
	//
	if (!$stnum) {
		// error - st number not supplied
		csv_edihist_log("ibr_batch_get_st_info: ST number not supplied");
		return FALSE;
	} 
	if (strlen($stnum) == 13) {
		// assume concatenation of isa13 and st02
		$ctln = substr($stnum, 0, 9);
		$stnum = substr($stnum, -4);
        $btfname = csv_file_by_controlnum('batch', $ctln );
	} elseif (strlen($stnum) < 4 ) { 
		$stnum = str_pad ($stnum, 4, "0", STR_PAD_LEFT); 
	}
	
	if (strlen($batchname) == 9 && !$btfname) {
		// assume isa13 control number
        $btfname = csv_file_by_controlnum('batch', $batchname );
	} else {
		$btfname = $batchname;
	}
	//
	$fp = ($btfname) ? csv_verify_file($btfname, 'batch') : '';
	//
	if ($fp) { 
		$bstr = file_get_contents($fp);
		$batch_name = basename($fp);
	} else {
		// unable to get the file, quit here
		csv_edihist_log("ibr_batch_get_st_info: could not read batch file");
		return FALSE;
	}

	// $st_clm_num must be in format "000N" like "0004" or "0012"
	// we are looking for, e.g. ST*837*0017~
	$seg_st = 'ST'.SEG_ELEM_DELIM.'837'.SEG_ELEM_DELIM.$stnum;  // particular ST block
	//
	$seg_se = SEG_TERM_DELIM."SE".SEG_ELEM_DELIM;      // prepend the "~" to avoid SE* as substring in a segment
	$seg_st2 = SEG_ELEM_DELIM.$st_num.SEG_TERM_DELIM;  //second search string *0021*  e.g. SE*47*0021* 
	//
	// get segment positions	
	$st_pos = strpos($bstr, $seg_st, 0);
	// break it off if $st_pos is not found
	if ( $st_pos == FALSE ) { 
		//echo "ibr_find_claim_enctr $st_num $seg_st not found in file $batch_file" . PHP_EOL; 
		return FALSE;
	}
	//
	$se_pos = strpos($bstr, $seg_se, $st_pos);
	$se_pos2 = strpos($bstr, $seg_st2, $se_pos);
	$se_pos3 = strpos($bstr, SEG_TERM_DELIM, $se_pos2);
	//
	$seg_block = substr($bstr, $st_pos, $se_pos3-$st_pos+1); 
	$segs_ar = explode(SEG_TERM_DELIM, $seg_block);
	//
	$has_sbr = FALSE; 
	$has_dep = FALSE;
	$enc_pt_lname = ''; $enc_pt_fname = ''; $pid_enctr = ''; $svcdate = '';
	//
	foreach($segs_ar as $seg_str) { 
		// a 'trim($seg_str)' is needed if newlines are present
		$seg = explode(SEG_ELEM_DELIM, $seg_str);
		//
		if ($seg[0] == "HL") {
			if ($seg[3] == "22") { $has_sbr = TRUE; }
			if ($seg[3] == "23") { $has_dep = TRUE; }
			continue;
		}
			
		if ($seg[0] == "NM1" && ($seg[1] == "IL" || $seg[1] == "QC")) {
			// name can be in first NM1*IL and blank in second	
			if ($seg[1] == "IL" && !$enc_pt_lname) {
				$enc_pt_lname = $seg[3];
				$enc_pt_fname = $seg[4];
			}
			if ($seg[1] == "QC" && $has_dep) {
				if ($enc_pt_lname && strlen($seg[3])) {
					$enc_pt_lname = $seg[3];
					$enc_pt_fname = $seg[4];
				} elseif(!$enc_pt_lname) {
					$enc_pt_lname = $seg[3];
					$enc_pt_fname = $seg[4];
				}					
			}
			//
			continue;
		}
		
		if ($seg[0] == "CLM") {
			$pid_enctr = $seg[1];
		}
		
		if ($seg[0] == 'DTP' && $seg[1] == '472') {
			$svcdate = $seg[3];	
			// we are done, since DTP segment comes after SVC, near end
			break;
		}
	}
	//
	return array($st_num, $pid_enctr, $enc_pt_lname, $enc_pt_fname, $batch_name, $svcdate);
}


/**
 * Parse a batch file for data for the csv record
 * 
 * The array returned has a key 'file' which is for the files_batch.csv
 * and a key 'claim' with a subarray for each claim
 * 
 * @see csv_files_header() for values
 * @uses csv_x12_segments()
 * @param string $batch_file   name of x12-837 batch file
 * @return array
 */
function ibr_batch_csv_data($batch_file_path) {
	// 
	// read the file and transform it into an array of segment arrays
	// then loop through the segment arrays and copy desired items
	// to a data array for writing to the csv_file and csv_claims files
	//
	// get the segments from csv_record_include function csv_x12_segments
	$ar_batch = csv_x12_segments($batch_file_path, 'batch', $seg_array = FALSE);
	// debug
	//var_dump(array_keys($ar_batch) ) .PHP_EOL;
	//
	if ( is_array($ar_batch) ) {
		$batch_file = basename($ar_batch['path']);
		$elem_d = $ar_batch['delimiters']['e'];
		//
	} else {
		csv_edihist_log("ibr_batch_csv_data did not get segments for $batch_file");
		return FALSE;
	}
	//
    $ar_data = array();
	$st_ct = -1;
	$seg_ct = 0;
	//
	foreach ( $ar_batch['segments'] as $segtxt) {
		// debug
		//echo "$segtxt  <br />" .PHP_EOL;
		//
		$seg = explode($elem_d, $segtxt);	 
		// increment segment count for testing
		$seg_ct++;

		// these values will occur once per file
		// $fname = $batch_file; 			// $ar_data[$st_ct][8] = $batch_file
		if ($seg[0] == "ISA") { 
			$x12partner = $seg[6];
			$f_mtime = '20'.$seg[9];
			$x12version = $seg[12];
			$ctrl_num = strval($seg[13]); 		    // $ar_data[$st_ct][10] = $ctrl_num
			//
			continue;
		}
		if ($seg[0] == 'GS') {
			$f_mtime = $seg[4];
			continue;
		}
		// get GE segment for files data claim count
		if ($seg[0] == "GE") { 
			$clm_ct = $seg[1];
			continue;
		}
		// now we need to create a sub array for each ST block
		if ($seg[0] == "ST" ) { 
			$st_ct++; 					// OpenEMR 837 places each claim in an ST block
			$ln_st = $seg_ct;
			$st_num = strval($seg[2]); 		    // $ar_data[$st_ct][9] = $st_num;
			continue;
		}
		if ($seg[0] == "BHT" ) { 
			$bht03 = $seg[3];
			$ins_pos = '';
			// since this is '0123' and not useful, we will construct it
			// below with $ctrl_num.$st_num
			continue;
		}
		if ($seg[0] == "HL") {
			$hlevel = strval($seg[3]);
			continue;
		}
		if ($seg[0] == "SBR") {
			if (($hlevel == '22' || $hlevel == '23') && !$ins_pos) { $ins_pos = $seg[1]; }
			//$ins_pos = $seg[1];
			continue;
		}
		if ($seg[0] == "CAS") {
			// on theory that CAS will only appear on secondary claims
			if ($ins_pos == 'P') { $ins_pos = 'S'; }
		}
		if ($seg[0] == "NM1" && $seg[1] == "PR" ) { 
			if ($ins_pos == "P") {
				$ins_primary = $seg[3];  		// $ar_data[$st_ct][7] = $ins_primary;
			}
			if ($ins_pos == "S") {
				$ins_secondary = $seg[3]; 		// $ar_data[$st_ct][8] = $ins_secondary;
			}
			continue;
		}
		if ( $seg[0] == "NM1" && strpos("|IL|QC", $seg[1]) ) { 
			// The NM1*QC segment is in the batch file if the patient is not
			// the subscriber, and it comes after the NM1*IL segment,
			// so get something in either case, but errors can leave blanks
			$pt_lname = ($pt_lname) ? $pt_lname : $seg[3]; 		    // $ar_data[$st_ct][0] = $pt_lname
			$pt_fname = ($pt_fname) ? $pt_fname : $seg[4]; 		    // $ar_data[$st_ct][1] = $pt_fname 
			continue;
		}
		if ( $seg[0] == "NM1" && $seg[1] == '82') { 
			$providerid = $seg[9];
			continue;
		}	
		if ($seg[0] == "CLM") {
			$inv_split = preg_split('/\D/', $seg[1], 2, PREG_SPLIT_NO_EMPTY);
			$pid = $inv_split[0];		    // $ar_data[$st_ct][2] = $pid 
			$enctr = $inv_split[1];		    // $ar_data[$st_ct][3] = $enctr
			$clm01 = $seg[1];
			$fee = $seg[2];		    		// $ar_data[$st_ct][5] = $fee
			continue;
		}
		if ($seg[0] == "DTP" && $seg[1] == "472") {
			$svc_date = $seg[3];		    // $ar_data[$st_ct][4] = $svc_date
			continue;
		}
		if ($seg[0] == "AMT" && $seg[1] == "F5") {
			$pt_paid = $seg[2];		    	// $ar_data[$st_ct][6] = $pt_paid
			continue;
		}
		if ($seg[0] == "SE" ) {
			// end of ST block, get claim array and go back
			// debug  count lines  
			$ln_ct = $seg[1];
			//
			$ln_test = $seg_ct - $ln_st + 1;
			if ($ln_test != $ln_ct) {
				csv_edihist_log("ibr_batch_csv_data: ST segment count error $ln_test $ln_ct");
			}
			//['batch']['claim'] = array('PtName', 'SvcDate', 'clm01', 'InsLevel', 'Ctn_837', 'File_837', 'Fee', 'PtPaid', 'Provider' );
			// now put ar_data in order
			//
			$ar_data['claim'][$st_ct][0] = $pt_lname . ", " . $pt_fname;
			$ar_data['claim'][$st_ct][1] = $svc_date;
			$ar_data['claim'][$st_ct][2] = $clm01; 
			$ar_data['claim'][$st_ct][3] = $ins_pos;
			$ar_data['claim'][$st_ct][4] = $ctrl_num.$st_num;
			$ar_data['claim'][$st_ct][5] = $batch_file;	
			$ar_data['claim'][$st_ct][6] = $fee;
			$ar_data['claim'][$st_ct][7] = isset($pt_paid) ? $pt_paid : "0";
			$ar_data['claim'][$st_ct][8] = $providerid;
			
			// reset variables so there is no carryover
			// do not reset $batch_file or $ctrl_num
			$pt_lname = "";
			$pt_fname = "";
			$pid = "";
			$enctr = "";
			$svc_date = "";
			$fee = "";
			$pt_paid = "";
			$ins_primary = "";
			$ins_secondary = "";
			$st_num = "";
			$bht03 = "";
			$ins_pos = "";
			$providerid = "";
			$clm01 = "";
			//
			continue;
		}
		//
	}   // end foreach( $ar_batch as $seg)
	//
	// put the file data array together
	//$csv_hd_ar['batch']['file'] = array('Date', 'FileName', 'Ctn_837', 'claim_ct', 'x12_partner');
	$ar_data['file'][0] = $f_mtime;
	$ar_data['file'][1] = $batch_file;
	$ar_data['file'][2] = $ctrl_num;
	$ar_data['file'][3] = $clm_ct;
	$ar_data['file'][4] = $x12partner;
	//$ar_data['file'][5] = $x12version;

	return $ar_data;
}


/**
 * Process new batch files for csv data and html output
 * 
 * The html output is only a file summary, no claim detail is created
 * 
 * 
 * @uses csv_verify_file()
 * @uses ibr_batch_csv_data()
 * @uses csv_write_record() 
 * @uses csv_newfile_list()
 * @param array $file_array   optional default is NULL 
 * @param boolean $html_out   whether to generate html files summary table
 * @return string             html output only for files table
 */
function ibr_batch_process_new ($file_array=NULL, $html_out=TRUE) { 
	//
	// 'mtime', 'dirname', 'fname', 'trace' 'payer' 'claims'
	$html_str = "";
	$chars1 = 0;
	$chars2 = 0;
	//$chars2 = 0;
	$idx = 0;
	$ar_batchf = array();
	//$need_dir = TRUE;
	// get the list of new files   
	if ( $file_array === NULL || !is_array($file_array) || count($file_array) == 0) {
		$ar_newfiles = csv_newfile_list("batch");
	} else {
		$ar_newfiles = $file_array;
		//$need_dir = FALSE;
	}
	//
	if ( count($ar_newfiles) == 0 ) {
		if($html_out) { 
			$html_str .= "<p>ibr_batch_process_new: no new batch files <br />";
			return $html_str;
		} else {
			return false;
		}
	} else {
		$btfcount = count($ar_newfiles);
	}
	// we have some new ones
	// verify and get complete path
	foreach($ar_newfiles as $fbt) { 
		$fp = csv_verify_file($fbt, 'batch', false);
		if ($fp) { $ar_batchf[] = $fp; }
	}
	
	$p = csv_parameters("batch");
	$b_dir = $p['directory'];	
	//
	if($html_out) {
		$html_str .= "<table cols=5 class=\"batch\">
		  <caption>Batch Files CSV</caption>
		  <thead>
			<tr>
			  <th>File Time</th><th>File Name</th>
			  <th>Control</th><th>Claims</th><th>x12_Partner</th>
			</tr>
		  </thead>
		  <tbody>";
	}

	foreach ($ar_batchf as $f_batch) {
		 // 
		 // increment counter for alternating html backgrounds
		 $bgc = ($idx % 2 == 1 ) ? 'odd' : 'even'; 
		 $idx++;
		 //
		 //$full_path = ($need_dir) ? $b_dir.DIRECTORY_SEPARATOR.$f_batch : $f_batch;
		 // get the file data for csv output
		 //$ar_csv_data = ibr_batch_csv_data($full_path);
		 $ar_csv_data = ibr_batch_csv_data($f_batch);
		 //
		 // write to the files_batch csv record 	 	 
		 $chars1 += csv_write_record($ar_csv_data['file'], 'batch', 'file' );
		 $chars2 += csv_write_record($ar_csv_data['claim'], 'batch', 'claim');
		 //
		 // link for viewing file <a href=\"edi_view_file.php?\'fvkey\'=$dta\" target=\"_blank\">$dta</a></td>";
		 if($html_out) { 
			 $html_str .= "<tr class=\"$bgc\">
                 <td>{$ar_csv_data['file'][0]}</td>
                 <td><a target='_blank' href='edi_history_main.php?fvkey={$ar_csv_data['file'][1]}'>{$ar_csv_data['file'][1]}</a></td>
                 <td>{$ar_csv_data['file'][2]}</td>
                 <td>{$ar_csv_data['file'][3]}</td>
                 <td>{$ar_csv_data['file'][4]}</td>
               </tr>";
		 }
	 }
	 if($html_out) { 
		 $html_str .= "</tbody>
		   </table>
		 <p></p>"; 
	 }
	 //
	 csv_edihist_log("ibr_batch_process_new: $chars1 characters written to files_batch.csv");
	 csv_edihist_log("ibr_batch_process_new: $chars2 characters written to claims_batch.csv");	 
	 //
	 if($html_out) { 
		 return $html_str;
	 } else {
		 return "<p>Batch files: processed $btfcount files </p>";
	 }
 }


?>
