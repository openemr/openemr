<?PHP
/**
 * ibr_ack_read.php, read and process ack files
 * 
 * Copyright 2012 Kevin McCormick
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
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
 
 
// a security measure to prevent direct web access to this file
// must be accessed through the main calling script ibr_history.php 
// from admin at rune-city dot com;  found in php manual
// if (!defined('SITE_IN')) die('Direct access not allowed!');


/**
 * get error code test for TA1 error
 * 
 * @param string $code  - the error code
 * @return string   error test
 */
function ibr_ta1_code($code) {
	$ar_ta1code = array(
				'A' => 'Interchange accepted with no errors.',
				'R' => 'Interchange rejected because of errors. Sender must resubmit file.',
				'E' => 'Interchange accepted, but errors are noted. Sender must not resubmit file.',
				'000' => 'No error',
				'001' => 'The Interchange Control Number in the header and trailer do not match.',
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
	if (array_key_exists($code, $ar_ta1code)) {
		$ta1_msg = $ar_ta1code[$code];
	} else {
		$ta1_msg = "Code $code not found";
	}
	return $ta1_msg;
}


function ibr_ack_error($filename, $errcode) {
	//
	$errval = '<p>';
	$ext = strtolower(substr($filename, -3));
	if ($ext == 'ack') {
		if (!$errcode) { $errcode = '1E'; }
		$fp = csv_verify_file($filename, $ext);
		if ($fp) {
			$fh = fopen($fp, 'r');
			if ($fh) {
				while (($buffer = fgets($fh, 4096)) !== false) {
					if (substr($buffer, 0, 2) == $errcode) {
						$errval = substr($buffer, 3) . "</p>".PHP_EOL;
						break;
					}
				}
			} else {
				$errval .= "Failed to read file $filename <p>".PHP_EOL;
			}
		} else {
			$errval .= "Verification failed for file $filename <p>".PHP_EOL;
			csv_edihist_log ( "ibr_ack_error Error: failed to read " . $filename);
		}
	} elseif ($ext == 'ta1') {
		$seg_ar = csv_x12_segments($filename, 'ta1', false);
		if (is_array($seg_ar) && isset($seg_ar['segments']) ) {
			$elem_d = $seg_ar['delimiters']['e'];
			foreach($seg_ar['segments'] as $segstr) {
				$seg = explode($elem_d, $segstr);
				if ($seg[0] =='TA1') {
					$ack_code = isset($seg[5]) ? strval($seg[5]) : '';
					if (strlen($errcode) && $errcode != $ack_code) {
						$errval .= "Given code: $errcode <br />".PHP_EOL;
						$errval .= "Message: " . ibr_ta1_code($errcode) . "</p>".PHP_EOL;
						$errval .= "Found Code: <br />".PHP_EOL;
					}
					$errstr = ($ack_code) ? $seg[5].' '. ibr_ta1_code($seg[5]) : '';
					$errval .= "$errstr</p>".PHP_EOL;
					break;
				}
			}
		} else {
			$errval = "Failed to read file $filename <br />".PHP_EOL;
			csv_edihist_log ( "ibr_ack_error Error: failed to read " . $filename);
		}
	}
	//
	return $errval;
}


/**
 * to parse ACK files -- usually a batch reject due to errors in ISA segment
 * 
 * @uses ibr_batch_find_file_with_controlnum()
 * @param string $filepath -- full path to .ack file
 * @return array 
 */
function ibr_process_ack($filepath) {
	// to parse ACK files -- usually a batch reject due to errors in ISA segment
	//
	if (is_readable($filepath)) {
		//  string setlocale ( int $category , array $locale ) // may be needed
		$path_parts = pathinfo($filepath);	
		$ack_dir = $path_parts['dirname'];
		$ack_fname = $path_parts['basename'];
		$ack_ext = $path_parts['extension'];
		$ack_mtime = date ("Ymd", filemtime($filepath));
		$act_txt = str_replace ( ".ACK", ".ACT", $ack_fname);
	} else {
		// error, unable to read file
		csv_edihist_log ("ibr_process_ack: Error, unable to read file $filepath");
		return FALSE;
	}
	// read the file
	$fh = fopen($filepath, 'r');
	if ($fh) {
		while (($buffer = fgets($fh, 4096)) !== false) {
			$ar_ack[] = trim($buffer);
		}
	} else	{     
		csv_edihist_log ( "ibr_process_ack Error: failed to read " . $file_path  );
	}
	fclose($fh);
	//
	$ar_ackfile = array();
	$batch_file ="";
	$ack_msg = "";
	//
	foreach($ar_ack as $strln) {
		if (substr($strln, 0, 2) == '1'.IBR_DELIMITER) {
			$ln = explode(IBR_DELIMITER, $strln);
			// use the given date for the file date, otherwise filemtime
			$dtstr = str_replace('-', '', $ln[1]);
			if (strlen($dtstr) == 8 && substr($dtstr, 0, 4) == substr($ln[1], 0, 4) ) {
				$ack_mtime = $dtstr; 
			} 
			$ack_date = trim($ln[1]); 
			$ack_ip = trim($ln[2]);
			$ack_isa13 = strval($ln[4]);
			$ack_ctlnum = strval($ln[5]);
			continue;
		}
		if (substr($strln, 0 ,2) == '1E') {	
			$ln = explode(IBR_DELIMITER, $strln);
			$ack_msg .= '1E: '.trim(substr($ln[1],0, 80)) . "... ";
			$ack_code = '1E';
			continue;
		}
	}
    if ( isset($ack_ctlnum) && strlen($ack_ctlnum) ) {
		$batch_file = csv_file_by_controlnum('batch', $ack_ctlnum);
	}
	//
	$ar_ackfile["ack_time"] = isset($ack_mtime) ? trim($ack_mtime) : ""; 		//"date"
	$ar_ackfile["ack_file"] = isset($ack_fname) ? trim($ack_fname) : "";  	//"filename"
	$ar_ackfile["ack_isa13"] = isset($ack_isa13) ? trim($ack_isa13) : "";   // file id?
	$ar_ackfile["ack_ctrl"] = isset($ack_ctlnum) ? trim($ack_ctlnum) : "";	//"batch icn "
	$ar_ackfile["ack_code"] = isset($ack_code) ? trim($ack_code) : "";	    //"error code ? 1E "
	$ar_ackfile["ack_msg"] = isset($ack_msg) ? trim($ack_msg) : "";
	$ar_ackfile["ack_batch"] = ($batch_file) ? trim($batch_file) : "batch not identified";		//"batch"
    $ar_ackfile["ack_ftxt"] = ($ack_txt) ? $ack_txt : ""; 	              //"readable filename"
    //
	return $ar_ackfile;
}

/**
 * process TA1 acknowledgment files
 * 
 * @return array
 */
function ibr_process_ta1($filepath) {
	//
	$ar_ackfile = array();
	//
	$seg_ar = csv_x12_segments($filepath, 'ta1', false);
	if (is_array($seg_ar) && isset($seg_ar['segments']) ) {
		$ar_ta1_segments = $seg_ar['segments'];
		$ack_mtime = date('Ymd', filemtime($seg_ar['path']));
		$elem_d = $seg_ar['delimiters']['e'];
		$sub_d = $seg_ar['delimiters']['s'];
		$rep_d = $seg_ar['delimiters']['r'];
		//
		$ack_fname = basename($seg_ar['path']);
		$ack_txt = str_replace('.TA1', '.TAT', $ack_fname);
	} else {
		// error getting segments
		csv_edihist_log("ibr_process_ta1: error getting segments for $filepath");
		return false;
	}
	//
	$batch_file = '';
	$ack_msg = '';
	//
	foreach($ar_ta1_segments as $segstr) {
		$seg = explode($elem_d, $segstr);
		if ($seg[0] == 'ISA') {
			$ack_mtime = $seg[9];
			if (strlen($seg[9]) == 6) { $ack_mtime = '20' . $seg[9]; }
			$ack_isa13 = strval($seg[13]);
		}
		if ($seg[0] == 'TA1') {	
			$ack_ctlnum = strval($seg[1]);
			$ack_date = '20' . $seg[2];  // date is 6 digits, prepend century
			$ack_status = $seg[4];
			$ack_code = $seg[5];
			$ack_msg = ($ack_code) ? $seg[5].": ". ibr_ta1_code($seg[5]) : '';
		}
	}
    if ( isset($ack_ctlnum) && strlen($ack_ctlnum) ) {
		$batch_file = csv_file_by_controlnum('batch', $ack_ctlnum);
	}
    //
	$ar_ackfile["ack_time"] = isset($ack_mtime) ? $ack_mtime : ""; 		//"date"
	$ar_ackfile["ack_file"] = isset($ack_fname) ?  $ack_fname : "";  	//"file"
	$ar_ackfile["ack_isa13"] = isset($ack_isa13) ? $ack_isa13 : "";     // $ta1 isa13
	$ar_ackfile["ack_ctrl"] = isset($ack_ctlnum) ? $ack_ctlnum : "";	//"batch_ctrl"
	$ar_ackfile["ack_code"] = isset($ack_code) ? $ack_code : "";        // error code
	$ar_ackfile["ack_msg"] = isset($ack_msg) ? $ack_msg : "";
    $ar_ackfile["ack_batch"] = ($batch_file) ? $batch_file : "batch not identified"; 
    $ar_ackfile["ack_ftxt"] = ($ack_txt) ? $ack_txt : "";
	//
	return $ar_ackfile;
}	

/**
 * creates an html table listing .ack files and rejected claims
 * 
 * @param array -- the data array from ibr_process_ack()
 * @return string -- tml table
 */
function ibr_ack_html($ar_data) {
	//
	if (!count($ar_data) ) { return ""; }
	//
	$idx = 0;
	$idf = 0;
	//
	// array('Date', 'FileName', 'isa13', 'ta1ctrl', 'code');
	$str_html = "<table class=\"f997\" cols=5><caption>ACK/TA1 File Report</caption>
	   <thead>
	   <tr>
		 <th>Date</th><th>File Name</th><th>Batch ICN</th><th>Batch</th><th>Code</th>
	   </tr>
	   </thead>
	   <tbody>";
	// file information row ack_time ack_file ack_batch ack_msg
	foreach ($ar_data as $ack) {
		$bgf = ($idf % 2 == 1) ? 'odd' : 'even';
		$idf++;
		//
	    $str_html .= "
	       <tr class=\"{$bgf}\">
	        <td>{$ack['ack_time']}</td>
			<td><a target='_blank' href='edi_history_main.php?fvkey={$ack['ack_file']}'>{$ack['ack_file']}</a>&nbsp;&nbsp; <a target='_blank' href='edi_history_main.php?fvkey={$ack['ack_ftxt']}&readable=yes'>R</a></td>
			<td><a href='edi_history_main.php?btctln={$ack['ack_ctrl']}' target='_blank'>{$ack['ack_ctrl']}</a></td>
            <td><a href='edi_history_main.php?fvkey={$ack['ack_batch']}' target='_blank'>{$ack['ack_batch']}</a></td>
			<td>{$ack['ack_code']}</td>
		  </tr>
		  <tr>
		    <td span=4>{$ack['ack_msg']}</td>
		  </tr>";
	    //
	}
    $str_html .= "</tbody>".PHP_EOL."</table>".PHP_EOL;
	return $str_html;
}

/**
 * process new .ack files
 * 
 * This is the main function in this script
 * 
 * @uses ibr_process_ack()
 * @uses ibr_ack_html()
 * @uses csv_newfile_list()
 * @uses csv_verify_file()
 * @param array|string -- optional array of filenames or filename
 * @param bool --whether to produce html table
 * @return string
 */
function ibr_ack_process_new($ack_files, $html_out = TRUE ) {
	//
	$str_html = "";
	//
    if ( is_array($ack_files) ) {
		$new_ack = $ack_files;
    } elseif (is_string($ack_files) && strlen($ack_files) ) {
        $new_ack[] = $ack_files;
    } else {
        $new_ack = csv_newfile_list("ack");
    }
    //
    if ( is_array($new_ack) && count($new_ack) > 0 ) {
        $fcount = count($new_ack);
		foreach($new_ack as $ack) {		
			$fp = csv_verify_file($ack, 'ack'); 
			if ($fp) {
				$ar_d[] = ibr_process_ack($fp);
			} else {
				$str_html .= "ACK failed to verify file: $ack <br />" .PHP_EOL;
				csv_edihist_log("ibr_ack_process_new: failed to verify file: $ack");
			}		
		}
	}    

	// write a line to csv file -- put in file_997.csv
	if ( is_array($ar_d) && count($ar_d) ) {
		foreach($ar_d as $arec) {
			// do not put message, batch file, or text file in csv record
			$ar_csv[] = array_slice($arec, 0, count($arec)-3);
		}
		$rslt = csv_write_record($ar_csv, "ack", "file");
		csv_edihist_log("ibr_ack_process_new: $rslt characters written to files_997.csv");
		if ($html_out) { 
			$str_html .= ibr_ack_html($ar_d); 
		} else {
			$str_html .= "ACK files: processed $fcount ACK files <br />".PHP_EOL;
		}
	} else {
		$str_html .= "No new ACK files found. <br />" .PHP_EOL;
	}
	//
	return $str_html;
}

/**
 * process new .ta1 files
 * 
 * 
 * @uses ibr_process_ta1()
 * @uses ibr_ack_html()
 * @uses csv_newfile_list()
 * @uses csv_verify_file()
 * @param array|string -- optional array of filenames or filename
 * @param bool --whether to produce html table
 * @return string
 */
function ibr_ta1_process_new($ta1_files, $html_out = TRUE ) {
    //
	$str_html = "";
	//
	if ( is_array($ta1_files) ) {
		$new_files = $ta1_files; 
    } elseif (is_string($ta1_files) && strlen($ta1_files)) {
        $new_files[] = $ta1_files;
    } else {
        $new_files = csv_newfile_list("ta1");
    }
    //
    if ( is_array($new_files) && count($new_files) > 0 ) {
        $fcount = count($new_files);
        foreach($new_files as $ta) {	
            $fp = csv_verify_file($ta, 'ta1'); 
            if ($fp) {
				$ar_d[] = ibr_process_ta1($fp);
			} else {
				$str_html .= "TA1 failed to verify file: $ta <br />" .PHP_EOL;
				csv_edihist_log("ibr_ta1_process_new: failed to verify file: $ta");
			}		
		}
	} 
 	// write a line to csv file -- put in file_997.csv
	if ( is_array($ar_d) && count($ar_d) ) {
		foreach($ar_d as $arec) {
			// do not put message, batch, or text file in csv record
			$ar_csv[] = array_slice($arec, 0, count($arec)-3);
		}
		$rslt = csv_write_record($ar_csv, "ta1", "file");
		csv_edihist_log("ibr_ta1_process_new: $rslt characters written to files_997.csv");
		if ($html_out) { 
			$str_html .= ibr_ack_html($ar_d); 
		} else {
			$str_html .= "TA1 files: processed $fcount TA1 files <br />".PHP_EOL;
		}
	} else {
		$str_html .= "No new TA1 files found. <br />" .PHP_EOL;
	}
	//
	return $str_html;   
}


?>