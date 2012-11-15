<?php
/**
 * csv_record_include.php
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
 
/*
 * The purpose of this file is to hold functions of general utility for
 * my edi_claim_history project.  It began as a php "class" but I am now
 * thinking that instantiating the class is too much bother and probably
 * a waste of memory, since the contents of the file have to be read into
 * memory anyway.
 * 
 * <pre>
 * ******* important *********
 * function csv_parameters($type="ALL")
 *   This function must have the correct values or nothing will work
 * function csv_verify_file( $file_path, $type, $val_array=FALSE )  
 *   critical for file verification and x12 parsing
 * function (in ibr_uploads.php) ibr_upload_match_file($param_ar, $fidx, &$html_str) 
 *   contains a regular expression that must be correct
 * 
 * **************************
 * </pre>
 * 
 * The claim_history x12 files are batch (837) claim status (277 also TA1) and era (835) 
 *  (Eligibility status files (271) would probably work in this scheme as well.)
 * There are also Availity clearinghouse specific files .ibr, .ebr, .dpr (nothing for .dpr files)
 * 
 * <pre>
 * Basic workflow:
 *  Each file type has a row in the array from csv_paramaters() 
 *     type  directory files_csv  claims_csv  column  regex
 *  
 *  1. Read the parameters array and choose the parameters using 'type'
 *  2. Search the 'directory' for files matching the 'regex' regular expressions and 
 *     compare the results to the files listed in the 'files_csv' files.csv record -- unmatched files are "new"
 *  3. Each "new" x12 file should be read by csv_x12_segments -- returns array('path', 'delimiters', 'segments')
 *      ibr, ebr, ack -- basically Availity formats have their own read functions
 *  4. Pass the array to various functions which parse for claims information
 *  5. Write the results to files.csv or claims.csv and create html output for display
 * 
 *  6. Other outputs as called for in ibr_history.php -- from user input from claim_history.html
 * </pre> 
 * 
 *  Key usability issue is the "new" files are in the users home directory -- downloaded there
 *   while the OpenEMR is on the server -- so there is a basic issue of access to the files
 * 
 *  The ibr_uploads.php script handles uploads of zip archives or multiple file uploads
 * 
 * The csv data files are just php written .csv files, so anything different may cause errors
 * You can open and edit them in OpenOffice, but you must save them in "original format"
 * 
 * TO DO script to read 271 eligibility response files -- add type to csv_verfy_file, csv_parameters, csv_files_header, csv_setup
 * 
 * TO_DO Some type of "find in files" search would be helpful for locating all references to a claim, patient, etc.
 *    [ grep -nHIrF 'findtext']
 * 
 * TO_DO functions to zip old files, put them aside, and remove them from csv tables
 */ 
 
///**
// *  a security measure to prevent direct web access to this file
// */
// if (!defined('SITE_IN')) die('Direct access not allowed!'); 
 
/**
 * Log messages to the log file
 * 
 * @param string $msg_str  the log message
 * @return int             number of characters written
 */
function csv_edihist_log ( $msg_str ) {
	//
    $logfile = csv_edih_basedir();
    //$logfile = ($logfile) ? $logfile."/edi_history_log.txt" : $GLOBALS['OE_SITE_DIR']."/edi_history_log.txt";
    $logfile = ($logfile) ? $logfile."/edi_history_log.txt" : false;
    if (!$logfile) { 
        echo "EDI History log file not available <br />"; 
        return false;
    }
	$rslt = FALSE;
	if ( is_string($msg_str) ) { 
		$tm = date( 'Ymd:Hms' );
		$tm .= " " . $msg_str . PHP_EOL;
		$fh = fopen( $logfile, 'a');
		if ($fh !== FALSE) {
			$rslt = fwrite($fh, $tm);  // number of characters written
		}
		fclose($fh);
	}
	return $rslt;
}


/**
 * read the edi_history_log.txt file into an
 * html formatted ordered list
 * 
 * @return string
 */
function csv_log_html() {
	$html_str = "<div class=\"filetext\">".PHP_EOL."<ol class='logview'>".PHP_EOL; 
    $fp = csv_edih_basedir();
    $fp = $fp."/edi_history_log.txt";
	$fh = fopen( $fp, 'r');
	if ($fh !== FALSE) {
		while (($buffer = fgets($fh)) !== false) {
			$html_str .= "<li>$buffer</li>".PHP_EOL;
		}
		$html_str .= "</ol>".PHP_EOL."</div>".PHP_EOL;
		if (!feof($fh)) {
			$html_str .= "<p>Error: unexpected file ending error</p>".PHP_EOL;
		}
		fclose($fh);
	} else {
		$html_str = "<p>Error: unable to open log file</p>".PHP_EOL;
	}
	return $html_str;
}

function csv_log_archive() {
	$dte = date('Ymds');
	//
	$str_out = '';
    $bdir = csv_edih_basedir();
    $zname = $bdir.DIRECTORY_SEPARATOR.$dte.'_edihistory_log.txt';
    $logname = $bdir.DIRECTORY_SEPARATOR.'edi_history_log.txt';
    $archname = $bdir.DIRECTORY_SEPARATOR.'edihistory_archive.zip';
	//
	$fs = filesize($logname);
	if ($fs == false && $fs < 512) {
		$str_out = "$tm error accessng log file.<br />".PHP_EOL;
		return $str_out;
	} elseif ($fs < 512) {
		$str_out = "$tm log too small to archive.<br />".PHP_EOL;
		return $str_out;
	}
	
	$zip = new ZipArchive;
	if ($zip->open($archname, ZIPARCHIVE::CREATE | ZIPARCHIVE::CHECKCONS)!==TRUE) {
	    exit("cannot open <$archname>\n");
	} else {
		$a = $zip->addFile($logname, $zname);
		$c = $zip->close();
		if ($c) {
			$fh = fopen($logname, "w+b");
			if ($fh !== FALSE) {
				$tm = date('Ymd:Hms');
				$rslt = fwrite($fh, "$tm log file archive created.".PHP_EOL);
			}
			fclose($fh);
			$str_out = "$tm log file archive created.<br />".PHP_EOL;
		} else {
			$str_out = "$tm error in archive of log file.<br />".PHP_EOL;
		}
	}
    return $str_out;
}

/**
 * open or save a user notes file
 * 
 * @param string
 * @param bool
 * @return string
 */
function csv_notes_file($content='', $open=true) {
	//
	$str_html = '';
	//$fp = dirname(__FILE__) . "/edi_notes.txt";	
    $fp = csv_edih_basedir();
    $fp = $fp."/edi_notes.txt";	
	if (! is_writable($fp) ) {
		$fh = fopen( $fp, 'a+b');
		fclose($fh);
	}
	if ($open) {
		// if contents were previously deleted by user and file is empty,
		// the text 'empty' is put in content in save operation
		$ftxt = file_get_contents($fp);
		if ($ftxt === false) { echo "csv_notes_file: file error<br />".PHP_EOL; }
		if (substr($ftxt, 0, 5) == 'empty' && strlen($ftxt) == 5) {
			$ftxt = '## '. date("F j, Y, g:i a");
		} elseif (!$ftxt) { 
			$ftxt = '## '. date("F j, Y, g:i a"); 
		}
		$str_html .= $ftxt;
	} elseif (strlen($content)) {
		//echo "csv_notes_file: we have content<br />".PHP_EOL;
		if (preg_match('/[^\x20-\x7E\x0A\x0D]|(<\?)|(<%)|(<asp)|(<ASP)|(#!)|(\$\{)|(<scr)|(<SCR)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
			$str_html .= "Filtered character in file content not accepted <br />" . PHP_EOL;
			$str_html .= " character: " . $matches[0][0] . "  position: " . $matches[0][1] . "<br />" . PHP_EOL;
			$saved = false;
		} else {
			//echo "csv_notes_file: we are trying to save in $fp<br />".PHP_EOL;
			$saved = file_put_contents($fp, $content);
		}
		$str_html .= ($saved) ? "Notes saved<br />" : "Save Error<br />";
	} else {
		$ftxt = 'empty';
		$saved = file_put_contents($fp, $ftxt);
		$str_html .= ($saved) ? "No content in notes.<br />" : "Save Error with empty file<br />";
	}
	//
	return $str_html;
}

/**
 * set the base path for most file operations
 * 
 * @return string|boolean 
 */
function csv_edih_basedir() {
	//$GLOBALS['OE_SITE_DIR'] 
    // should be something like /var/www/htdocs/openemr/sites/default
    if (isset($GLOBALS['OE_SITE_DIR'])) {
        return $GLOBALS['OE_SITE_DIR'].'/edi/history';
    } else {
        csv_edihist_log("csv_edih_basedir: failed to obtain OpenEMR Site directory"); 
        return false;
    }
}

/**
 * set the temporary directory used for uploads, etc.
 * 
 * @return string
 */
function csv_edih_tmpdir() {
    //define("IBR_UPLOAD_DIR", "/tmp/edihist"); 
    $systmp = sys_get_temp_dir();
    $systmp = stripcslashes($systmp);
    return $systmp."/edihist";
}
   

/**
 * Initial setup function
 * 
 * Create the directory tree and write the column headers into the csv files
 * This function will accept a directory argument and it appends the value
 * from IBR_HISTORY_DIR to the path.  Then a directory for each type of file
 * and the csv files are created under that.
 * 
 * @uses csv_parameters()
 * @uses csv_files_header()
 * @param string $dir
 * @param string &$out_str  referenced, should be created in calling function
 * @return boolean
 */
function csv_setup(&$out_str) {
	//
	$isOK = FALSE;
	$chr = 0;
	//$basedir = dirname(__FILE__);
    $edihist_dir = csv_edih_basedir();
    if ($edihist_dir) {
        $basedir = $GLOBALS['OE_SITE_DIR'].DIRECTORY_SEPARATOR.'edi';
        $csv_dir = $edihist_dir.DIRECTORY_SEPARATOR.'csv';
        $archive_dir = $edihist_dir.DIRECTORY_SEPARATOR.'archive';
    } else {
       //csv_edihist_log("setup: failed to obtain OpenEMR Site directory"); 
       $out_str .= "setup: failed to obtain OpenEMR Site directory <br />";
       return false;
    }
	//
	if (is_writable($basedir) ) { 
		$isOK = TRUE;
        $out_str .= "setup: directory $basedir <br />";
		//csv_edihist_log("setup: directory $basedir");
	}
	//
	if ($isOK) { 
		//
		if (!mkdir($edihist_dir, 0755, true)) {
			//csv_edihist_log("Setup: Failed to create folder... $edihist_dir");
			$out_str .= "Setup: Failed to create folder... $edihist_dir<br />".PHP_EOL;
			$isOK = FALSE;
			return false;
		} else { 
			$p_ar = csv_parameters("ALL");
			//
			if (!mkdir($csv_dir, 0755, true) ) {
				$isOK = FALSE;
				//csv_edihist_log("Setup: Failed to create csv folder...$csv_dir");
				return false;
			}
			if (!mkdir($archive_dir, 0755, true) ) {
				$isOK = FALSE;
				//csv_edihist_log("Setup: Failed to create archive folder...$archive_dir");
				return false;
			}
			//
			foreach ($p_ar as $key=>$val) {
				// make the file storage subdirs; like /history/era /history/f997, etc.
                $type_dir = $p_ar[$key]['directory'];
				//
				if (!is_dir($type_dir) && !mkdir($type_dir, 0755, false) ) {
					//csv_edihist_log("Setup: failed to create storage directory $key");
					$out_str .= "Setup: failed to create storage directory $key<br />".PHP_EOL;
					return false;
				}
				$out_str .= "created directory for $key<br />" .PHP_EOL;
				$chr = 0;				
				//
				$hdr_f = csv_files_header($p_ar[$key]['type'], 'file');
				$hdr_c = csv_files_header($p_ar[$key]['type'], 'claim');
				//
				$fpath = $p_ar[$key]['files_csv'];
				$cpath = $p_ar[$key]['claims_csv'];
				//
				if (is_array($hdr_f) ) {
                    // create the files_type.csv files and insert header row
					if ($fpath) {
						//csv_edihist_log("Creating file $fpath for $key");	
                        $fh = fopen($fpath, 'x');
						if ($fh !== FALSE) {
							$chr = fputcsv($fh, $hdr_f);
							$out_str .= ($chr) ? "created $fpath <br />" .PHP_EOL : "failed to create $fpath<br />" .PHP_EOL;
							$isOK = ($chr) ? TRUE : FALSE;
							$chr = 0;
						} else {
							$isOK = FALSE;
							//csv_edihist_log("Creating file failed for $key");
							$out_str .= "Creating file failed for $key<br />" .PHP_EOL;
						}
						fclose($fh);
					}
				} else {
					//csv_edihist_log("Did not get header row for $key file");
                    $out_str .= "Did not get header row for $key file<br />".PHP_EOL;
				}
					
				if (is_array($hdr_c) ) {
                    // // create the claims_type.csv files and insert header row
					if ($cpath) {
						//csv_edihist_log("Creating file $cpath for $key");
                        $fh = fopen($cpath, 'x');
						if ($fh !== FALSE) {
							$chr = fputcsv($fh, $hdr_c);
							$out_str .= ($chr) ? "created $cpath <br />" .PHP_EOL : "failed to write heading row for $cpath<br />" .PHP_EOL;
							$isOK = ($chr) ? TRUE : FALSE;
							$chr = 0;							
						} else {
							$isOK = FALSE;
							//csv_edihist_log("Creating file failed for $key");
							$out_str .= "Creating file failed for $key<br />" .PHP_EOL;
						}
						fclose($fh);
					}
				} else {
					$isOK = FALSE;
					//csv_edihist_log("Did not get header row for $key claims table");
                    $out_str .= "Did not get header row for $key claims table<br />".PHP_EOL;
				}
				// 	
			}
            //$GLOBALS['OE_SITE_DIR']."/edi_history_log.txt";	
            //if (is_file($GLOBALS['OE_SITE_DIR']."/edi_history_log.txt")) {
            //     rename ($GLOBALS['OE_SITE_DIR']."/edi_history_log.txt", $edihist_dir."/edi_history_log.txt" );
            //}
		}
	} else {
		$out_str .= "Setup failed: Can not create directories <br />" . PHP_EOL;
	}
	return $isOK;
	//return $out_str;
}				


/**
 * Empty all contents of tmp dir IBR_UPLOAD_DIR
 * 
 * @param  none
 * @return bool
 */
function csv_clear_tmpdir() {
	//
    $edih_tmpdir = csv_edih_tmpdir();
	$tmp_files = scandir($edih_tmpdir);
	if (count($tmp_files)) {
		foreach($tmp_files as $idx=>$tmpf) { 
			if ($tmpf == "." || $tmpf == "..") {
				// can't delete . and ..
				continue;
			}		
			if (is_file($edih_tmpdir.DIRECTORY_SEPARATOR.$tmpf) ) {
				unlink($edih_tmpdir.DIRECTORY_SEPARATOR.$tmpf); 
				unset($tmp_files[$idx]);
			}
		}
	}
	if (count($tmp_files) > 2) {
		//
		csv_edihist_log ( "tmp dir contents remain in $edih_tmpdir");
		return FALSE;
	} else {
		return TRUE;
	}
}

/**
 * The array that holds the various parameters used in dealing with files
 * 
 * A key function since it holds the paths, columns, etc.  This function relies on
 * the IBR_HISTORY_DIR constant.  Unfortunately, there is an issue with matching the type in
 * the case of the values '997', '277', '999', etc, becasue these strings may be recast
 * from strings to integers, so the 'type' originally supplied is lost.  
 * This introduces an inconsistency when the 'type' is used in comparison tests.
 * The workaround is to say the "type" should have an 'f' prepended to the x12 type number.
 * The 'datecolumn' and 'fncolumn' entries are used in csv_to_html() to filter by date 
 * or place links to files.
 * 
 * @param string $type -- default = ALL or one of batch, ibr, ebr, dpr, f997, f277, era, ack, ta1, text
 * @return array
 */
function csv_parameters($type="ALL") {
	//
    $edihist_dir = csv_edih_basedir();  // $GLOBALS['OE_SITES_BASE'].'/edi/history'
	$p_ar = array();
	// the batch file directory is a special case - decision is to use OpenEMR batch files so users will not have to
    // upload these.  If they are accidentally uploaded, they will be matched and the extra copy will be discarded
	// OpenEMR copies each batch file to sites/default/edi and this project never reads from or writes to that directory
	// batch reg ex -- '/20[01][0-9]-[01][0-9]-[0-3][0-9]-[0-9]{4}-batch*\.txt/' '/\d{4}-\d{2}-\d{2}-batch*\.txt$/'
    //
 	//$p_ar['csv'] = array("type"=>'csv', "directory"=>$edihist_dir.'/csv', "claims_csv"=>'ibr_parameters.csv',  
	//					"files_csv"=>'', "column"=>'', "regex"=>'/\.csv$/');    
    
    $p_ar['batch'] = array("type"=>'batch', "directory"=>$GLOBALS['OE_SITE_DIR'].'/edi', "claims_csv"=>$edihist_dir."/csv/claims_batch.csv", 
						"files_csv"=>$edihist_dir."/csv/files_batch.csv", "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/\-batch(.*)\.txt$/');	
    $p_ar['ta1'] = array("type"=>'ta1', "directory"=>$edihist_dir.'/f997', "claims_csv"=>'', 
						"files_csv"=>$edihist_dir.'/csv/files_997.csv', "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/\.ta1$/i');
    $p_ar['ack'] = array("type"=>'ack', "directory"=>$edihist_dir.'/f997', "claims_csv"=>'', 
						"files_csv"=>$edihist_dir.'/csv/files_997.csv', "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/\.ack$/i');    	
	$p_ar['f997'] = array("type"=>'f997', "directory"=>$edihist_dir.'/f997', "claims_csv"=>$edihist_dir.'/csv/claims_997.csv', 
						"files_csv"=>$edihist_dir.'/csv/files_997.csv', "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/\.99[79]$/');
    
	$p_ar['ibr'] = array("type"=>'ibr', "directory"=>$edihist_dir.'/ibr', "claims_csv"=>$edihist_dir.'/csv/claims_ibr.csv', 
						"files_csv"=>$edihist_dir.'/csv/files_ibr.csv', "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/\.ibr$/');
	$p_ar['ebr'] = array("type"=>'ebr', "directory"=>$edihist_dir.'/ebr', "claims_csv"=>$edihist_dir.'/csv/claims_ebr.csv', 
						"files_csv"=>$edihist_dir.'/csv/files_ebr.csv', "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/\.ebr$/');
	$p_ar['dpr'] = array("type"=>'dpr', "directory"=>$edihist_dir.'/dpr', "claims_csv"=>$edihist_dir.'/csv/claims_dpr.csv', 
						"files_csv"=>'', "datecolumn"=>'1', "fncolumn"=>'5', "regex"=>'/\.dpr$/');						
	$p_ar['f277'] = array("type"=>'f277', "directory"=>$edihist_dir.'/f277', "claims_csv"=>$edihist_dir.'/csv/claims_277.csv', 
						"files_csv"=>$edihist_dir.'/csv/files_277.csv', "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/\.277([ei]br)?$/');
	// OpenEMR stores era files, but the naming scheme is confusing, so we will just use our own directory for them 
	$p_ar['era'] = array("type"=>'era', "directory"=>$edihist_dir.'/era', "claims_csv"=>$edihist_dir.'/csv/claims_era.csv', 
						"files_csv"=>$edihist_dir.'/csv/files_era.csv', "datecolumn"=>'0', "fncolumn"=>'1', "regex"=>'/835[0-9]{5}\.835*|\.(era|ERA)$/');
	$p_ar['text'] = array("type"=>'text', "directory"=>$edihist_dir.'/text', "claims_csv"=>'',
						"files_csv"=>'', "column"=>'', "regex"=>'/\.(EB)|(IB)|(DP)|(AC)|(TA)|(99)|(97)T$/i');
    
	$tp = strpos('|f837', (string)$type) ? 'batch' : $type;
	$tp = strpos('|f999', (string)$type) ? 'f997' : $tp;
	$tp = strpos('|f997', (string)$type) ? 'f997' : $tp;
	$tp = strpos('|f835', (string)$type) ? 'era' : $tp;
	$tp = strpos('|f277', (string)$type) ? 'f277' : $tp;
	//
	if ( array_key_exists($tp, $p_ar) ) {
		return $p_ar[$tp];
	} else {
		return $p_ar;
	} 	
}

/**
 * determine if a csv table has data for select dropdown
 * 
 * @param string   default 'json'
 * @return array   json if argument is 'json'
 */
function csv_table_select_list($outtp='json') {
	$optlist = array();
	//
    $edihist_dir = csv_edih_basedir();  // $GLOBALS['OE_SITE_DIR'].'/edi/history'
    $thisdir = $edihist_dir.'/csv';
	$tbllist = scandir($thisdir);
	$idx = 0;
	foreach($tbllist as $csvf) {
		if ($csvf == "." || $csvf == ".." ) { continue; }
		if (filesize($thisdir.DIRECTORY_SEPARATOR.$csvf) < 90) { continue; }
		if (substr($csvf, -1) == '~') { continue; }
		$finfo = pathinfo($thisdir.DIRECTORY_SEPARATOR.$csvf);
		$fn = $finfo['filename']; 
		$tp = explode('_', $fn);
		$optlist[$idx]['fname'] = $fn;
		$optlist[$idx]['desc'] = $tp[1] .' '.$tp[0];
		$idx++;
	}
	if ($outtp == 'json') {
		return json_encode($optlist);
	} else {
		return $optlist;
	}	
}


/**
 * List files in the directory for the given type
 * 
 * Write an entry in the log if an file is in the directory
 * that does not match the type.
 * 
 * @uses csv_parameters()
 * @param string $type    a type from our list
 * @return array
 */
function csv_dirfile_list ($type) {
	// return false if location is not appropriate
	// use regular expressions to select desired files from directory
  	if (! strpos("|era|f997|ibr|ebr|dpr|f277|batch|ack|ta1", $type) ) {	
        if ($type != 'text') {
            // do not log text type, but do not search either
            csv_edihist_log("csv_dirfile_list error: incorrect type $type");
        }
		return FALSE;
	}
	$params = csv_parameters($type);
    $search_dir = $params['directory'].DIRECTORY_SEPARATOR;
    $typedir = basename($params['directory']);
	$ext_re = $params['regex'];
	$dirfiles = array();
	//
	if (is_dir($search_dir)) {
	    if ($dh = opendir($search_dir)) {
	        while (($file = readdir($dh)) !== false) {
	            if (is_file($search_dir.$file) ) {
					if (preg_match($ext_re, $file) ) { 
						$dirfiles[] = $file; 
					} elseif ($typedir == 'f997') {
                        $ext = substr($file, -3);
                        // no error, since ack|ta1 files are put there
                        if ($type == 'f997') { 
                            if ($ext == 'ack' || $ext == 'ta1') { continue; }
                        } elseif ($type == 'ack') {
                            if (ext == '999' || ext == '997' || $ext == 'ta1') { continue; }
                        } elseif ($type == 'ta1') {
                            if (ext == '999' || ext == '997' || $ext == 'ack') { continue; }
                        } 
                    } else {
                        //if ($file == '.' || $file == '..') { continue; }  // . and .. are not files
						csv_edihist_log("csv_dirfile_list: $type wrong type $file");
					}
				}
	        }
	        closedir($dh);
	    }
	} else {
        csv_edihist_log("csv_dirfile_list: Error: $typedir directory seems to be missing!");
    }
	//   
	return $dirfiles;
} // end function


/**
 * List files that are in the csv record
 * 
 * @uses csv_parameters()
 * @param string $type -- one of our types
 * @return array
 */
function csv_processed_files_list ($type) {
	// 
	//
	if (! strpos("|era|f997|ibr|ebr|dpr|f277|batch|ack|ta1", $type) ) {	
        if ($type != 'text') {
            csv_edihist_log("csv_processed_files_list error: incorrect type $type");
        }
		return FALSE;
	}
	$processed_files = array();
	$param = csv_parameters($type);
	$csv_col = $param['fncolumn'];
	if ($type == 'dpr') {
        $csv_file = $param['claims_csv'];
		//$csv_col = '5';
	} else {
        $csv_file = $param['files_csv'];
	}
	//
	$idx = 0;
	if (($fh1 = fopen( $csv_file, "r" )) !== FALSE) {
	    while (($data = fgetcsv($fh1, 1024, ",")) !== FALSE) {
	        if ($idx) { $processed_files[] = $data[$csv_col]; }
	        // skip the header row
	        $idx++;
		}
		fclose($fh1);   
	} else {
		csv_edihist_log ("csv_list_processed_files: failed to access $csv_file" ); 
		return false;
	}	
	// consider array_shift($processed_files) to drop the header row (too slow)
    // consider array_unique($processed_files) becasue files may be listed several times
	return $processed_files;
} // end function


/**
 * Give an array of files in the storage directories that are not in the csv record
 * 
 * @param string $type -- one of our types
 * @return array
 */
function csv_newfile_list($type) {	
	// 
	//f277  f997  ack  batch  csv  ebr  era  ibr  text
	if (! strpos("|era|f997|ibr|ebr|dpr|f277|batch|ack|ta1", $type) ) {	
        if ($type != 'text') { 
            csv_edihist_log("csv_newfile_list: incorrect type $type");
        }
		return FALSE;
	}
	//
	$dir_files = csv_dirfile_list ($type);
	$csv_files = csv_processed_files_list ($type);
	// $dir_files should come first in array_diff()
	if (empty($dir_files)) { 
        // logic error -- fixed; if dir_files is empty, there are no files of that type
		//$ar_new = $csv_files;
        $ar_new = $dir_files;
	} else {
		$ar_new = array_diff($dir_files, $csv_files);
	}
	//   
	return $ar_new;
}


/**
 * Give the column headings for the csv files
 * 
 * @param string $file_type -- one of our types batch|era|ibr|ebr|dpr|f277|f997, etc.
 * @param string $csv_type -- one of 'file' or 'claim'	
 * @return array
 */	
function csv_files_header($file_type, $csv_type) {
	// 
	if (! strpos("|era|835|f997|999|ibr|ebr|dpr|f277|batch|837|ta1|ack", $file_type) ) {	
		csv_edihist_log("csv_files_header error: incorrect file type $file_type");
		return FALSE;
	}
	if (!strpos('|file|claim', $csv_type) ) { 
		csv_edihist_log("csv_files_header error: incorrect csv type $csv_type");
		return FALSE;
	}		
	//
	$ft = strpos('|277', $file_type) ? 'f277' : $file_type;
	$ft = strpos('|835', $file_type) ? 'era' : $ft;
	$ft = strpos('|837', $file_type) ? 'batch' : $ft;
	$ft = strpos('|999|997|ack|ta1', $file_type) ? 'f997' : $ft;
	//
	$csv_hd_ar = array();
	// actually, 'ack' and 'ta1' are probably redundant, since they are interpreted as 'f997'
	$csv_hd_ar['ack']['file'] = array('Date', 'FileName', 'isa13', 'ta1ctrl', 'Code');
	$csv_hd_ar['ebr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch'); 
	$csv_hd_ar['ibr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch');
	//
	$csv_hd_ar['batch']['file'] = array('Date', 'FileName', 'Ctn_837', 'claim_ct', 'x12_partner');
	$csv_hd_ar['ta1']['file'] =   array('Date', 'FileName', 'Ctn_ta1', 'ta1ctrl', 'Code');
	$csv_hd_ar['f997']['file'] =  array('Date', 'FileName', 'Ctn_999', 'ta1ctrl', 'RejCt');
	$csv_hd_ar['f277']['file'] =  array('Date', 'FileName', 'Ctn_277', 'Accept', 'AccAmt', 'Reject', 'RejAmt');
	$csv_hd_ar['era']['file'] =   array('Date', 'FileName', 'Trace', 'claim_ct', 'Denied', 'Payer');
	//
	$csv_hd_ar['ebr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');				
	$csv_hd_ar['ibr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
	$csv_hd_ar['dpr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
	//
	$csv_hd_ar['batch']['claim'] = array('PtName', 'SvcDate', 'clm01', 'InsLevel', 'Ctn_837', 'File_837', 'Fee', 'PtPaid', 'Provider' );
	$csv_hd_ar['f997']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'ak_num', 'File_997', 'Ctn_837', 'err_seg'); 
	$csv_hd_ar['f277']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'st_277', 'File_277', 'payer_name', 'claim_id', 'bht03_837');
	$csv_hd_ar['era']['claim'] =   array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'File_835', 'claimID', 'Pmt', 'PtResp', 'Payer');
	//
	return $csv_hd_ar[$ft][$csv_type];
}


/**
 * Determine whether an array is multidimensional
 * 
 * @param array
 * @return bool   false if arrayis multidimensional
 */
function csv_singlerecord_test ( $array ) { 
	// the two versions of count() are compared
	// if the array has a sub-array, count recursive is greater
	$is_sngl = count($array, COUNT_RECURSIVE ) == count( $array, COUNT_NORMAL);
	//
	return $is_sngl;
} 

/**
 * A multidimensional array will be flattened to a single row.
 * 
 * @param array $array array to be flattened
 * @return array
 */
function csv_array_flatten($array) {
	//
	if (!is_array($array)) {return FALSE;}
	$result = array();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$result = array_merge($result, csv_array_flatten($value));
		} else {
			$result[$key] = $value;
		}
	}
	return $result;
}

/**
 * Append rows to one of the csv record files.
 * 
 * @uses csv_singlerecord_test()
 * @uses csv_parameters()
 * @uses csv_files_header()
 * @param array $csv_data    the data array, either file data or claim data
 * @param string $file_type  which of our file types to use
 * @param string $csv_type   either 'claim' or 'file'
 * @return int               number of characters written per fputcsv()
 */
function csv_write_record($csv_data, $file_type, $csv_type) {
	//
	if (!is_array($csv_data)) { return FALSE;}
	// use CSV_RECORD class to write ibr or ebr claims data to the csv file 
	//  csv, batch, ibr, ebr, f997, or era
	if (! strpos("|era|f997|ibr|ebr|dpr|f277|batch|ta1|ack", $file_type) ) {	
		csv_edihist_log("csv_write_record error: incorrect file type $file_type");
		return FALSE;
	}

	$ft = $file_type;
	$ft = strpos("|835", $file_type) ? 'era' : $ft;
	$ft = strpos("|837", $file_type) ? 'batch' : $ft;
	$ft = strpos("|999|ack|ta1", $file_type) ? 'f997' : $ft;	
		
	$params = csv_parameters($ft);
	//
	if ($csv_type == "claim") { 
        $fp = $params['claims_csv'];
	} elseif ($csv_type == "file") {
        $fp = $params['files_csv'];
	} else {
		csv_edihist_log("csv_writedata_csv error: incorrect csv type $csv_type");
		return FALSE;
	}
	//
	$fh = fopen( $fp, 'a');	
	// count characters written -- returned by fputcsv
	$indc = 0;
	// if we fail to open the file, return the result, expect FALSE
	if (!$fh) { return FALSE; }
	// test for a new file
	if ( filesize($fp) === 0 ) { 
		$ar_h = csv_files_header($file_type, $csv_type);
		$td = fgetcsv($fh);
		if ($td === FALSE || $td === NULL ) {
			// assume we have an empty file
			// write header row if this is a new csv file
			if (count($ar_h) ) { 
				$indc += fputcsv ( $fh, $ar_h ); 
			} 
		}
	}

	// test array for dimension counts
	$is_sngl = csv_singlerecord_test($csv_data) ;
	if ( $is_sngl ) {	 
		$indc += fputcsv ( $fh, $csv_data );
	} else {
		// multi-dimensional array -- we rely on array_flatten to 
		// assure us that the array depth is 1 
		foreach ($csv_data as $row) {
			$wr = csv_array_flatten($row);
			// $wr is false if $row is not an array
			if ($wr) { 
				$indc += fputcsv ( $fh , $wr ); 
			} else {
				continue;
			}
		}
	}
	fclose($fh); 
	//
	return $indc;	
}

/**
 * Search a csv record file and return the row or values from selected columns
 * 
 * This function requires that the $search_ar parameter be an array
 * with keys ['s_val']['s_col']['r_cols'], and 'r_cols' is an array
 * 's_val' is the search value, s_col is the column to check, r_cols is an array
 * of column numbers from which values are returned.  If r_cols is not an array,
 * then the entire row will be returned.  If the 'expect' parameter is 1, then
 * the search will stop after the first success and return the result. Otherwise, the
 * entire file will be searched.
 * ex: csv_search_record('batch', 'claim', array('s_val'=>'20120115', 's_col'=>1, 'r_cols'=>array(0, 1, 2, 8)), "2" )
 * 
 * @uses csv_parameters()
 * @param string $file_type
 * @param string $csv_type
 * @param array $search_ar
 * @param mixed $expect
 * @return array
 */
function csv_search_record($file_type, $csv_type, $search_ar, $expect="1") {
	//
	if (! strpos("|era|f997|ibr|ebr|dpr|f277|batch|ack", $file_type) ) {	
		csv_edihist_log("csv_search_record: incorrect file type $file_type");
		return FALSE;
	}
	//
	$params = csv_parameters($file_type);
	//
	if ($csv_type == "claim") { 
        $fp = $params['claims_csv'];
	} elseif ($csv_type == "file") {
        $fp = $params['files_csv'];
	} else {
		csv_edihist_log("csv_search_record: incorrect csv type $csv_type");
		return FALSE;
	}
	//
	if (!is_array($search_ar) || array_keys($search_ar) != array('s_val', 's_col', 'r_cols')) { 
		csv_edihist_log("csv_search_record: invalid search criteria");
		return FALSE;
	} 
	$sv = $search_ar['s_val'];
	$sc = $search_ar['s_col'];
	$rv = (is_array($search_ar['r_cols']) && count($search_ar['r_cols'])) ? $search_ar['r_cols'] : 'all';
	$ret_ar = array();
	$idx = 0;
    //
	if (($fh1 = fopen($fp, "r")) !== FALSE) {
	    while (($data = fgetcsv($fh1)) !== FALSE) {
			// check for a match
			if ($data[$sc] == $sv) { 
				if ($rv == 'all') {
					$ret_ar[$idx] = $data;
				} else {
					// now loop through the 'r_cols' array for data index
					$dct = count($data);	
					foreach($rv as $c) {
						// make sure we don't access a non-existing index
						if ($c >= $dct) { continue; }
						//
						$ret_ar[$idx][] = $data[$c];
					}
				}
				$idx++;
				if ($expect == '1') { break; }
			}
		}
		fclose($fh1);
	} else {
		csv_edihist_log("csv_search_record: failed to open $fp");
		return false;
	}
	if (empty($ret_ar) ) {
		return false;
	} else {
		return $ret_ar;	
	}
}	

/**
 * Search the 'claims' csv table for the patient control and find the associated file name
 * 
 * In 'claims' csv tables, clm01 is position 2 number is pos 4, and filename is pos 5;
 * except in ebr, ibr, and dpr files which have batch name in pos 4.  See the
 * csv files column headings for more information.
 * 
 * @uses csv_parameters()
 * @uses csv_pid_enctr_parse()
 * @see csv_files_header()
 * @param string                     patient control-- pid-encounter, pid, or encounter
 * @param string                     filetype batch, era, f277, f997, ibr, ebr, dpr
 * @param string                     search type encounter, pid, or ptctln
 * @return array|bool				 [i](pid_encounter, number, filename) or false on error
 */
function csv_file_with_pid_enctr ($ptctln, $filetype='batch', $srchtype='encounter' ) { 
	// 
	// return array of [i](pid_encounter, filename), there may be more than one file
	//	
	if (!$ptctln) {
        csv_edihist_log("csv_file_with_pid_enctr: missing encounter data");
		//return "invalid encounter data<br />" . PHP_EOL;
        return false;
	}
	// IBR_FTYPES
	if (! strpos('|era|835|f997|999|ibr|ebr|dpr|f277|batch|837|ta1|ack', $filetype) ) {	
		csv_edihist_log("csv_file_with_pid_enctr: incorrect file type $filetype");
		return false;
	} else {
		$params = csv_parameters($filetype);
		//$fp = isset($params['claims_csv']) ? dirname(__FILE__).$params['claims_csv'] : false;
        $fp = isset($params['claims_csv']) ? $params['claims_csv'] : false;
		if (!$fp) {
			csv_edihist_log("csv_file_with_pid_enctr: incorrect file type $filetype");
			return false;
		}
	}
	//	
	$enctr = trim($ptctln);
	//
	preg_match('/\D/', $enctr, $match2, PREG_OFFSET_CAPTURE);
	//
	if (count($match2)) {
		//
		if ($srchtype != 'ptctln') {
			$idar = csv_pid_enctr_parse($enctr);
			if (is_array($idar) && count($idar)) {
				$p = strval($idar['pid']);
				$plen = strlen($p);
				$e = strval($idar['enctr']);
				$elen = strlen($e);
			} else {	 
				csv_edihist_log("csv_file_with_pid_enctr: error parsing pid_encounter $pid_enctr");
				return false;
			}
		} else {
			$pe = $enctr;
		}
	} else {
		// no match from preg_match, so $enctr has no non-digit characer like '-'
		if ($srchtype == 'ptctln') {
			if (strlen($enctr) > IBR_ENCOUNTER_DIGIT_LENGTH) {
				$pe = substr($enctr, 0, strlen($enctr)-IBR_ENCOUNTER_DIGIT_LENGTH) .'-'.substr($enctr, -IBR_ENCOUNTER_DIGIT_LENGTH);
			} else {
				// no pid, so change search type to encounter only
				$srchtype = 'encounter';
			}
		} 
		$p = strval($enctr);
		$e = strval($enctr); 
		$plen = strlen($p);
		$elen = strlen($e);
	}
	//
	$ret_ar = array();
	// in 'claims' csv tables, clm01 is position 2 and filename is position 5
	if (($fh1 = fopen($fp, "r")) !== FALSE) {
		if ($srchtype == 'encounter') {
			while (($data = fgetcsv($fh1, 1024, ",")) !== FALSE) {
				// check for a match
				if (substr($data[2], -$elen) == $e) { 
					// since e=123 will match 1123 and 123
					$peval = csv_pid_enctr_parse($data[2]);
					if (is_array($peval) && count($peval)) {
						if ($peval['enctr'] == $e) {
							$ret_ar[] = array($data[2], $data[4], $data[5]);
						}
					}
				}
			}
		} elseif ($srchtype == 'pid') { 
			while (($data = fgetcsv($fh1, 1024, ",")) !== FALSE) {
				// check for a match
				if (substr($data[2], 0, $plen) == $p) { 
					// since p=123 will match 1123 and 123
					$peval = csv_pid_enctr_parse($data[2]);
					if (is_array($peval) && count($peval)) {
						if ($peval['pid'] == $p) {
							$ret_ar[] = array($data[2], $data[4], $data[5]);
						}
					}
				}
			}
		} else {
			while (($data = fgetcsv($fh1, 1024, ",")) !== FALSE) {
				// check for a match
				if ($data[2] == $pe) { 
					$ret_ar[] = array($data[2], $data[4], $data[5]);
				}
			}
		}						 
		fclose($fh1);
	} else {
		csv_edihist_log("csv_file_with_pid_enctr: failed to open csv file ");
		return false;
	}
	return $ret_ar;
}	 

/**
 * get the x12 file containing the control_num ISA13
 * 
 * The csv for x12 files 999, 277, 835, 837 has the control number in pos 2
 * and the filename in pos 1. This is a convenience function, since the actual 
 * work is done by csv_search_record()
 * 
 * @uses csv_search_record()
 * @param string $control_num   the interchange control number, isa13
 * @return string               the file name
 */
function csv_file_by_controlnum($type, $control_num) {
	// get the batch file containing the control_num
	//	
	if (! strpos("|era|f997|f277|batch|ta1", $type) ) {	
		csv_edihist_log("csv_file_by_controlnum: incorrect file type $type");
		return FALSE;
	} 
	// $search_ar should have keys ['s_val']['s_col'] array(['r_cols'][])
	//    like "batch', 'claim, array(9, '0024', array(1, 2, 7))
	$fn = '';
    if ($type == 'era') {
       $ctln = trim(strval($control_num)); 
    } else {
       $ctln = (strlen($control_num) >= 9) ? substr($control_num, 0, 9) : trim(strval($control_num)); 
    }
	$search = array('s_val'=>$ctln, 's_col'=>2, 'r_cols'=>array(1));
	$result = csv_search_record($type, 'file', $search, "1");
	if (is_array($result) && count($result[0]) == 1) {
		$fn = $result[0][0];
	}
	return $fn;
}

/**
* A function to try and assure the pid-encounter is correctly parsed
* 
* assume a format of pid-encounter, since that is sent in the OpenEMR x12 837
* however, in case payer mangles the pid-encounter by dropping the separator,
* check value and use IBR_ENCOUNTER_DIGIT_LENGTH constant 
* 
* @param string $pid_enctr   the value from element CPL01
* return array               array('pid' => $pid, 'enctr' => $enc)  
*/
function csv_pid_enctr_parse( $pid_enctr ) {
	// evaluate the patient account field
	// 
	if (!$pid_enctr || !is_string($pid_enctr) ) {
		csv_edihist_log("csv_pid_enctr_parse: invalid argument");
		return false;
	}
	$pval = trim($pid_enctr);
	preg_match('/\D/', $pval, $match2, PREG_OFFSET_CAPTURE);
	$inv_split = (count($match2)) ? preg_split('/\D/', $pval, 2, PREG_SPLIT_NO_EMPTY) : false;
	if ($inv_split) {
		$pid = $inv_split[0];
		$enc = $inv_split[1];
	} elseif ( preg_match('/20[01]{1}[0-9]{1}(0[0-9]{1}|1[0-2]{1})[0-3]{1}[0-9]{1}/', $pval) ) {
        // encounter numbers can also be Ymd like 20110412
        $enc = $pval;
        $pid = '';
    } else {
		$enc = (strlen($pval) >= IBR_ENCOUNTER_DIGIT_LENGTH) ? substr($pval, -IBR_ENCOUNTER_DIGIT_LENGTH) : $pval;
		$pid = (strlen($pval) > IBR_ENCOUNTER_DIGIT_LENGTH) ? substr($pval, 0, (strlen($pval)-IBR_ENCOUNTER_DIGIT_LENGTH)) : '';
	}
	return array('pid' => $pid, 'enctr' => $enc);
}   
  
	
/**
 * This function is supposed to allow the downloading of a file.
 * 
 * Not used or tested -- do not use.  Since the users cannot scan the directories,
 * the file to be downloaded would have to be selected from a csv table display
 * or some other listing produced by reading the directories.
 * 
 * @todo implement this function
 * @param string $filename
 * @return void   --save file dialogue
 */	
function csv_download_file( $filename ){
	// adapted from http://php.net/manual/en/function.header.php
	// phpnet at holodyn dot com 31-Jan-2011 01:01
	// Must be fresh start  
	// /////////////////////  this function not used as of now and probably doesn't work
	//////////////////////////////////////////////////////////////////////////////////////
	// but a "view file" function will be made, probably as a separate page
	// links in csv file 
	// <a href='edi_view_file.php?key=filename' target='_blank'>filename</a>
	// OpenEMR open log link: <a href='../../library/freeb/process_bills.log' target='_blank' class='link_submit' title=''>[View Log]</a>

	if( headers_sent() ) {
	  csv_edihist_log("csv_download_file: error headers already sent");
	  return FALSE;
	}
	//FILTER_SANITIZE_URL
 	//$filename = $_GET['dlkey'];
	//
	$filename =  filter_input(INPUT_GET,'dlkey',FILTER_SANITIZE_STRING);
	$fp = csv_check_filepath($filename);
	if (!fp) {
		csv_edihist_log("csv_download_file: invalid filename for download $filename");
		//echo "csv_download_file: invalid filename for download $filename <br />" . PHP_EOL;
		return FALSE; // no -- httpd error code 504	
	}
	//	
	$file_html = csv_filetohtml($fp);
	//
	if ($file_html) { 
		$bn = basename($filename) . '.html';	
		$ctype="text/html";
		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		header("Location: http://$host$uri/$extra"); 
		file_put_contents($host . $uri .DIRECTROY_SEPARATOR. $bn, $file_html);
		$fsize = filesize($host . $uri .DIRECTROY_SEPARATOR. $bn);
		//
		
	} else {
		csv_edihist_log("csv_download_file: file was not converted to html $filename");
		//echo "csv_download_file: file was not converted to html $filename <br />" . PHP_EOL;
		return FALSE;
	}
	
	// Required for some browsers
	if(ini_get('zlib.output_compression'))
	ini_set('zlib.output_compression', 'Off');
	

	$ctype="application/pdf";
	//
	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false); // required for certain browsers
	header("Content-Type: $ctype");
	header("Content-Disposition: attachment; filename=\"".basename($fp)."\";" );
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$fsize);
	ob_clean();
	flush();
	readfile( $fp );
	//
	exit();
	
}
	

/**
 * check the csv claims tables and return rows for a particular encounter
 * 
 * @uses csv_pid_enctr_parse()
 * @uses csv_file_with_pid_enctr()
 * @uses csv_table_select_list()
 * @uses csv_search_record()
 * @param string       encounter number
 * @return string
 */
function csv_claim_history($encounter) {
	//
	if ($encounter) {
		$enct = csv_pid_enctr_parse(strval($encounter));
		$e = ($enct) ? $enct['enctr'] : false;
	}
	//
	if (!$e) {   
		return "invalid encounter value $encounter <br />".PHP_EOL;
	}
	// get complete pid-encounter from the batch claims record
	$efp = csv_file_with_pid_enctr($e);
	if (is_array($efp) && count($efp)) {
		$pe = $efp[0][0];
	} else {
        csv_edihist_log("csv_claim_history: failed to locate $e in batch claims record");
		return "failed to locate $e in batch claims record";
	}
	// use function csv_table_select_list() so that only
	// existing csv tables are queried
	$tbl2 = csv_table_select_list('array');
	$rtypes = array();
	if (is_array($tbl2) && count($tbl2) ) {
		foreach($tbl2 as $tbl) {
			$tp1 = explode(' ', $tbl['desc']);
			if ($tp1[1] == 'files') { continue; }
			if ($tp1[0] == '999' || $tp1[0] == '997' || $tp1[0] == '277') {
				$k = 'f'.$tp1[0];
				$rtypes[$k] = $k;
			} elseif ($tp1[0] == 'ibr' || $tp1[0] == 'ebr' || $tp1[0] == 'dpr') {
				$k = $tp1[0];
				$rtypes['prop'][] = $k;
			} else {
				$k = $tp1[0];
				$rtypes[$k] = $k;
			}
		}
	} else {
		csv_edihist_log("csv_claim_history: failed to get csv table names");
		return "failed to get csv table names";
	}
	//
	$ch_html .= "<table class='clmhist' columns=4><caption>Encounter Record for $pe</caption>";
	$ch_html .= "<tbody>".PHP_EOL;
	//
	if (isset($rtypes['batch'])) {
		$tp = 'batch';
		$srchar = array('s_val'=>$pe, 's_col'=>2, 'r_cols'=>'all');
		$btar = csv_search_record($tp, 'claim', $srchar, '2');
		//
		$ch_html .= "<tr class='chhead'>".PHP_EOL;
		$ch_html .= "<td>Name</td><td>SvcDate</td><td>CLM01</td><td>File</td>".PHP_EOL;
		$ch_html .= "</tr>".PHP_EOL;
		if (is_array($btar) && count($btar)) {
			foreach($btar as $ch) {
				$dt = substr($ch[1], 0, 4).'-'.substr($ch[1], 4, 2).'-'.substr($ch[1], 6, 2);
				//array('PtName', 'SvcDate', 'clm01', 'InsLevel', 'Ctn_837', 'File_837', 'Fee', 'PtPaid', 'Provider' );
				$ch_html .= "<tr class='chbatch'>".PHP_EOL;
				//
				$ch_html .= "<td>{$ch[0]}</td>".PHP_EOL;
				$ch_html .= "<td>$dt</td>".PHP_EOL;
				$ch_html .= "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch={$ch[5]}&btpid={$ch[2]}'>{$ch[2]}</a></td>".PHP_EOL;
				$ch_html .= "<td title='{$ch[4]}'><a target='_blank' href='edi_history_main.php?fvkey={$ch[5]}'>{$ch[5]}</a></td>".PHP_EOL;
				//
				$ch_html .= "</tr>".PHP_EOL;
			}
		} else {
			$ch_html .= "<tr class='chbatch'>".PHP_EOL;
			$ch_html .= "<td colspan=4>Batch -- Nothing found for $pe in $tp record</td>".PHP_EOL;
			$ch_html .= "</tr>".PHP_EOL;
		}
	}
	//
	if (isset($rtypes['f997'])) {
		$tp = 'f997';
		$srchar = array('s_val'=>$pe, 's_col'=>2, 'r_cols'=>'all');
		$f997ar = csv_search_record($tp, 'claim', $srchar, '2');
		//
		$ch_html .= "<tr class='chhead'>".PHP_EOL;
		$ch_html .= "<td>Response</td><td>Status</td><td>File</td><td>Notes</td>".PHP_EOL;
		$ch_html .= "</tr>".PHP_EOL;
		if (is_array($f997ar) && count($f997ar)) { 
			foreach($f997ar as $ch)	{
				//
				$msg = strlen($ch[7]) ? $ch[7] : 'ST Number';
				//array('PtName', 'SvcDate', 'clm01', 'Status', 'ak_num', 'File_997', 'Ctn_837', 'err_seg'); 
				$ch_html .= "<tr class='chf997'>";
				$ch_html .= "<td>997/999</td>".PHP_EOL;
				$ch_html .= "<td><a class='clmstatus' target='_blank' href='edi_history_main.php?fv997={$ch[5]}&aknum={$ch[4]}'>{$ch[3]}</a></td>".PHP_EOL;
				$ch_html .= "<td><a target='_blank' href='edi_history_main.php?fvkey={$ch[5]}'>{$ch[5]}</a></td>".PHP_EOL;
				$ch_html .= "<td title='$msg'>{$ch[6]} {$ch[4]}</td>".PHP_EOL;
				$ch_html .= "</tr>".PHP_EOL;
			}
		} else {
			$ch_html .= "<tr class='chf997'>";
			$ch_html .= "<td colspan=4>x12 999 -- Nothing found for $pe</td>".PHP_EOL;
			$ch_html .= "</tr>".PHP_EOL;
		}
	}
	//
	if (isset($rtypes['f277'])) {
		$tp = 'f277';	
		//
		$srchar = array('s_val'=>$pe, 's_col'=>2, 'r_cols'=>'all');
		$f277ar = csv_search_record($tp, 'claim', $srchar, '2');
		//
		$ch_html .= "<tr class='chhead'>".PHP_EOL;
		$ch_html .= "<td>Response</td><td>Status</td><td>File</td><td>ClaimID</td>".PHP_EOL;
		$ch_html .= "</tr>".PHP_EOL;
		if (is_array($f277ar) && count($f277ar)) {
			foreach($f277ar as $ch) {
				// array('PtName', 'SvcDate', 'clm01', 'Status', 'st_277', 'File_277', 'payer_name', 'claim_id', 'bht03_837');
				$ch_html .= "<tr class='chf277'>";
				//
				$ch_html .= "<td>x12 277</td>".PHP_EOL;
				$ch_html .= "<td><a class='clmstatus' target='_blank' href='edi_history_main.php?rspfile={$ch[5]}&pidenc={$ch[2]}&rspstnum={$ch[4]}'>{$ch[3]}</a></td>".PHP_EOL;
				$ch_html .= "<td title='{$ch[5]}'><a target='_blank' href='edi_history_main.php?fvkey={$ch[5]}'>File</a></td>".PHP_EOL;
				$ch_html .= "<td title='{$ch[6]}'>{$ch[7]}</td>".PHP_EOL;
				//
				$ch_html .= "</tr>".PHP_EOL;
			}
		} else {
			$ch_html .= "<tr class='chf277'>";
			$ch_html .= "<td colspan=4>x12 277 -- Nothing found for $pe</td>".PHP_EOL;
			$ch_html .= "</tr>".PHP_EOL;
		}
	}
	//
	if (is_array($rtypes['prop']) && count($rtypes['prop']) ) {
		foreach($rtypes['prop'] as $tp) {
			//
			$rspnm = strtoupper($tp);
			$srchar = array('s_val'=>$pe, 's_col'=>2, 'r_cols'=>'all');
			$ibrar = csv_search_record($tp, 'claim', $srchar, '2');
			//
			$ch_html .= "<tr class='chhead'>".PHP_EOL;
			$ch_html .= "<td>Response</td><td>Status</td><td>File</td><td>Payer</td>".PHP_EOL;
			$ch_html .= "</tr>".PHP_EOL;
			if (is_array($ibrar) && count($ibrar)) {
				foreach($ibrar as $ch) {
					//array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
					$ch_html .= "<tr class='ch$tp'>";
					//
					$ch_html .= "<td>$rspnm</td>".PHP_EOL;
					if ($tp == 'dpr') {
						$ch_html .= "<td><a class='clmstatus' target='_blank' href='edi_history_main.php?dprfile={$ch[5]}&dprclm={$ch[2]}'>{$ch[3]}</a></td>".PHP_EOL;
					} else {
						$ch_html .= "<td><a class='clmstatus' target='_blank' href='edi_history_main.php?ebrfile={$ch[5]}&ebrclm={$ch[2]}&batchfile={$ch[4]}'>{$ch[3]}</a></td>".PHP_EOL;
					}
					$ch_html .= "<td title='{$ch[5]}'><a target='_blank' href='edi_history_main.php?fvkey={$ch[5]}'>File</a></td>".PHP_EOL;
					$ch_html .= "<td>{$ch[6]}</td>".PHP_EOL;
					//
					$ch_html .= "</tr>".PHP_EOL;
				}
			} else {
				$ch_html .= "<tr class='ch$tp'>";
				$ch_html .= "<td colspan=4>$rspnm -- Nothing found for $pe</td>".PHP_EOL;
				$ch_html .= "</tr>".PHP_EOL;
			}
		}
	}
	// 
	if (isset($rtypes['era'])) {
		$tp = 'era';
		//
		$srchar = array('s_val'=>$pe, 's_col'=>2, 'r_cols'=>'all');
		$eraar = csv_search_record($tp, 'claim', $srchar, '2');
		//
		$ch_html .= "<tr class='chhead'>".PHP_EOL;
		$ch_html .= "<td>Response</td><td>Status</td><td>Trace</td><td>Payer</td>".PHP_EOL;
		$ch_html .= "</tr>".PHP_EOL;			
		if (is_array($eraar) && count($eraar)) {
			foreach($eraar as $ch) {
				//
				$msg = $ch[6] .' '.$ch[7].' '.$ch[8];
				// array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'File_835', 'claimID', 'Pmt', 'PtResp', 'Payer');
				$ch_html .= "<tr class='ch835'>";
				//
				$ch_html .= "<td>x12 ERA</td>".PHP_EOL;
				$ch_html .= "<td>{$ch[3]} <a class='clmstatus' target='_blank' href='edi_history_main.php?erafn={$ch[5]}&pidenc={$ch[2]}&summary=yes'>S</a>&nbsp;&nbsp;<a target='_blank' href='edi_history_main.php?erafn={$ch[5]}&pidenc={$ch[2]}&srchtp=encounter'>RA</a></td>".PHP_EOL;
				$ch_html .= "<td><a target='_blank' href='edi_history_main.php?erafn={$ch[5]}&trace={$ch[4]}&srchtp=trace'>{$ch[4]}</a>&nbsp;&nbsp;<a target='_blank' href='edi_history_main.php?fvkey={$ch[5]}'>x12</a></td>".PHP_EOL;
				$ch_html .= "<td title=$msg>{$ch[9]}</td>".PHP_EOL;
				//
				$ch_html .= "</tr>".PHP_EOL;
			}
		} else {
			$ch_html .= "<tr class='ch835'>";
			$ch_html .= "<td colspan=4>x12 835 ERA -- Nothing found for $pe</td>".PHP_EOL;
			$ch_html .= "</tr>".PHP_EOL;
		}
		//
	} // end if($tp ...
	// -- this is where a query on the payments datatable could be used to show if payment
	//    has been received, even if no era file shows the payment.
    //
    $ch_html .= "</tbody>".PHP_EOL;
    $ch_html .= "</table>".PHP_EOL;
    //
    return $ch_html;		
}


/**
 * Render one of our csv record files as an html table
 * 
 * This function determines the actual csv file from the file_type and the
 * csv_type.  A percentage (default=100) of the csv file rows is selected 
 * or the date field of each row is checked against the optional date parameters.
 * 
 * @uses csv_parameters()
 * @param string $file_type -- one of "|era|f997|ibr|ebr|dpr|f277|batch"
 * @param string $csv_type -- either "file" or "claim"
 * @param float $row_pct the percentage of the table to return (most recent)
 * @param string $datestart
 * @param string $dateend
 * @return string
 */
function csv_to_html($file_type, $csv_type, $row_pct = 1, $datestart='', $dateend='') { 
	//
	// read a csv file into an html table, using predefined stylesheet and javascript
	//	
	$csv_html = "";
	$is_date = FALSE;
	//				  
	if (! strpos("|era|f997|ibr|ebr|dpr|f277|batch|ack|ta1", $file_type) ) {	
		csv_edihist_log("csv_to_html error: incorrect file type $file_type");
		$csv_html .= "csv_to_html error: incorrect file type $file_type <br />".PHP_EOL;
		return FALSE;
	}
	//
	$params = csv_parameters($file_type);
	//
	// csv tables date is given date or mtime in col 0 for file, date is service date in col 1 for claim
	$dtcol = ($csv_type == "file") ? $params['datecolumn'] : '1';
	$fncol = ($csv_type == "file") ? $params['fncolumn'] : '1';
	// but dpr files are only in the claims table, a special case
	if ($file_type == "dpr") { $fncol = $params['fncolumn']; }
	//
	if ($csv_type == "claim") { 
        $fp = $params['claims_csv'];
	} elseif ($csv_type == "file") {
        $fp = $params['files_csv'];
	} else {
		csv_edihist_log("csv_to_html error: incorrect csv type $csv_type");
		$csv_html .= "csv_to_html error: incorrect csv type $csv_type <br />".PHP_EOL;
		return FALSE;
	}
    // for using OpenEMR dynarch calendar, assume format of CCYY-MM-DD
    if (preg_match('/\d{4}-\d{2}-\d{2}/', $datestart) && preg_match('/\d{4}-\d{2}-\d{2}/', $dateend) ) {
        $ds = str_replace('-', '', $datestart);
        $de = str_replace('-', '', $dateend);
        if ( $de <= $ds) { $de = date("Ymd", time()); }
        $is_date = TRUE;
		$row_pct = 1;
    }
	//
	$f_name	= basename($fp);
	// open the file for read and read it into an array
	$fh = fopen($fp, "r");
	if ($is_date) {
		$isok = FALSE;
		$idx = 0;
		//
		if ($fh !== FALSE) {
			while (($data = fgetcsv($fh, 1024, ",")) !== FALSE) {
				//
				if ($idx == 0) { 
					$csv_d[] = $data; 
				} else {	
					$isok = (substr($data[$dtcol], 0, 8) >= $ds) ? TRUE : FALSE; 
					$isok = (substr($data[$dtcol], 0, 8) > $de) ? FALSE : $isok; 
					//
					if ($isok) { $csv_d[] = $data; }	
				}
				$idx++;
			}
			fclose($fh);
		} else {
			$csv_html .= "csv_to_html: failed to open $fp <br />".PHP_EOL;
			return $csv_html;
		}
	} else {
		// get the entire table		
		if ($fh !== FALSE) {
			while (($data = fgetcsv($fh)) !== FALSE) { 
				$csv_d[] = $data;
			}
			fclose($fh);
		} else {
			$csv_html .= "csv_to_html: failed to open $fp <br />".PHP_EOL;
			return $csv_html;
		}
	}
	//
	$ln_ct = count($csv_d);
	// make sure row_pct is between 0 and 1
	if ($row_pct > 1 || $row_pct <= 0) { $row_pct = 1; }
	// only return the number of desired rows
	$rwct = (int)($ln_ct * $row_pct) + 1;
	$rwst = $ln_ct - $rwct;
	if ($rwst < 1) { $rwst = 1; $rwct = $ln_ct; }
	// 
	if ($is_date) {
		$csv_html .= "<div id='dttl'>Table: $f_name &nbsp;&nbsp; Start Date: $datestart &nbsp; End Date: $dateend &nbsp;Rows: $rwct</div>".PHP_EOL;
	} else {
		$csv_html .= "<div id='dttl'>Table: $f_name &nbsp;&nbsp; Rows: $ln_ct &nbsp;&nbsp; Shown: $rwct</div>".PHP_EOL;
	}
	//
	 $csv_html .= "<table id=\"csvTable\" class=\"csvDisplay\">".PHP_EOL;
	 // this is the body of the table
	 // 
	 if ($csv_type == 'file') {
		 //['era']['file'] =   array('Date', 'FileName', 'Trace', 'claim_ct', 'Denied', 'Payer');
		 //
		 $csv_html .= '<thead>'.PHP_EOL.'<tr>'.PHP_EOL;
		 foreach ($csv_d[0] as $h) { $csv_html .= "<th>$h</th>"; }
		 $csv_html .= PHP_EOL.'</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL; 
		 //
		 if ($file_type == 'era') {
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $csv_html .= "<tr class='{$bgc}'>".PHP_EOL;
				 foreach($csv_d[$i] as $idx=>$dta) {
					 if ($idx == 1) {
						 $csv_html .= "<td><a href='edi_history_main.php?fvkey=$dta' target='_blank'>$dta</a></td>".PHP_EOL;
					 } elseif ($idx == 2) {
						 $fnm = $csv_d[$i][1];
						 $csv_html .= "<td><a href='edi_history_main.php?erafn=$fnm&trace=$dta' target='_blank'>$dta</a> &nbsp;&nbsp;<a class=\"clmstatus\" target='_blank' href='edi_history_main.php?tracecheck=$dta&ckprocessed=yes'>(a)</td>".PHP_EOL;
					 } else {
						 $csv_html .= "<td>$dta</td>".PHP_EOL;
					 }
				 }
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 } elseif ($file_type == 'f997') {
			 //
			 // array('Date', 'FileName', 'Ctn_999', 'ta1ctrl', 'RejCt');
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $csv_html .= "<tr class=\"$bgc\">".PHP_EOL;
				 //
				 foreach($csv_d[$i] as $idx => $dta) {
					 if ($idx == 1) {
						 $fnm = $dta;
						 $ext = strtolower(substr($dta, -3));
						 $csv_html .= "<td><a target=\"_blank\" href=\"edi_history_main.php?fvkey=$dta\">$dta</a>&nbsp;&nbsp;<a target=\"_blank\" href=\"edi_history_main.php?fvkey=$dta&readable=yes\">Text</a></td>".PHP_EOL;
					 } elseif ($idx == 3) {
						 $csv_html .= "<td><a target=\"_blank\" href=\"edi_history_main.php?btctln=$dta\">$dta</a></td>".PHP_EOL;
					 } elseif ($idx == 4) {
						 if ($ext == '999' || $ext == '997') {
							$csv_html .= "<td><a class=\"codeval\" target=\"_blank\" href=\"edi_history_main.php?fv997=$fnm&err997=$dta\">$dta</a></td>".PHP_EOL;
						 } elseif ($ext == 'ta1' || $ext == 'ack') {
							$csv_html .= "<td><a class=\"codeval\" target=\"_blank\" href=\"edi_history_main.php?ackfile=$fnm&ackcode=$dta\">$dta</a></td>".PHP_EOL;
						 } else {
							$csv_html .= "<td>$dta</td>".PHP_EOL;
						 }
					 } else {
						 $csv_html .= "<td>$dta</td>".PHP_EOL;
					 } 
				 }
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 } elseif ($file_type == 'ebr' || $file_type == 'ibr' ) {
			 //['ibr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch');
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $fnm = $csv_d[$i][1];
				 $btfile = $csv_d[$i][5];
				 if (intval($csv_d[$i][4]) > 0) {
					 $rejlink = "<td><a class=\"clmstatus\" target=\"_blank\" href=\"edi_history_main.php?ebrfile=$fnm&ebrclm=any\">{$csv_d[$i][4]}</a></td>";
				 } else {
					 $rejlink = "<td>{$csv_d[$i][4]}</td>";
				 }
				 //
				 $csv_html .= "<td>{$csv_d[$i][0]}</td>".PHP_EOL;
				 $csv_html .= "<td><a target=\"_blank\" href=\"edi_history_main.php?fvkey={$csv_d[$i][1]}\">{$csv_d[$i][1]}</a>&nbsp;&nbsp;<a target=\"_blank\" href=\"edi_history_main.php?fvkey={$csv_d[$i][1]}&readable=yes\">Text</a></td>".PHP_EOL;
				 $csv_html .= "<td>{$csv_d[$i][2]}</td>".PHP_EOL;
				 $csv_html .= "<td>{$csv_d[$i][3]}</td>".PHP_EOL;
				 $csv_html .= $rejlink.PHP_EOL;
				 $csv_html .= "<td><a target=\"_blank\" href=\"edi_history_main.php?fvkey=$btfile\">$btfile</a></td>".PHP_EOL;
				 //
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 } elseif ($file_type == 'batch') {
             //['batch']['file'] = array('Date', 'FileName', 'Ctn_837', 'claim_ct', 'x12_partner');
             for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
                 $csv_html .= "<tr class='{$bgc}'>";
				 foreach($csv_d[$i] as $idx=>$dta) {
                     $fnm = $csv_d[$i][$fncol];
                     if ($idx == $fncol) {
						 $csv_html .= "<td><a href='edi_history_main.php?fvkey=$dta' target='_blank'>$dta</a></td>".PHP_EOL;
                     } elseif ($idx == 2) {
                         // batch control number
                         $csv_html .= "<td>$dta &nbsp;&nbsp;<a class=\"clmstatus\" target=\"_blank\" href=\"edi_history_main.php?batchicn=$dta\">(r)</a></td>"; 
                     } else {
						 $csv_html .= "<td>$dta</td>".PHP_EOL;
					 }
				 }
				 $csv_html .= "</tr>".PHP_EOL;
             }
         } else {
			 // the generic case -- for 'file' type tables, the filename is in column 1, as set in the parameters array
			 // see csv_parameters()
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $csv_html .= "<tr class='{$bgc}'>";
				 foreach($csv_d[$i] as $idx=>$dta) {
					 if ($idx == $fncol) {
						 $csv_html .= "<td><a href='edi_history_main.php?fvkey=$dta' target='_blank'>$dta</a></td>".PHP_EOL;				 
					 } else {
						 $csv_html .= "<td>$dta</td>".PHP_EOL;
					 }
				 }
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 }
	 } elseif ($csv_type == 'claim') {
		 // a 'claim' type table  $csv_type == 'claim'  there is more variation
		 if ($file_type == 'era') {
			 // era csv_type is claim  col 2 is pid, 3 encounter, 8 is trace
			 //['era']['claim'] = array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'File_835', 'claimID', 'Pmt', 'PtResp', 'Payer');
			 $csv_html .= '<thead>'.PHP_EOL.'<tr>'.PHP_EOL;
			 $csv_html .= '<th>Name</th><th>SvcDate</th><th>CLM01</th><th>Status</th><th>Trace</th><th>File</th><th>Payer</th>'.PHP_EOL;
			 $csv_html .= '</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $nm = $csv_d[$i][0];
				 $dt = substr($csv_d[$i][1], 0, 4).'-'.substr($csv_d[$i][1], 4, 2).'-'.substr($csv_d[$i][1], 6, 2);
				 $clm = $csv_d[$i][2];
				 $sts = $csv_d[$i][3];
				 $trc = $csv_d[$i][4];
				 $fnm = $csv_d[$i][5];
				 $clmid = $csv_d[$i][6];
				 $msg = $csv_d[$i][6] .' '.$csv_d[$i][7].' '.$csv_d[$i][8];
				 $pr = $csv_d[$i][9];
				 $csv_html .= "<tr class='{$bgc}'>";
				 //Name
				 $csv_html .= "<td>$nm</td>".PHP_EOL;
				 //SvcDate
				 $csv_html .= "<td>$dt</td>".PHP_EOL;
				 //CLM01
				 $csv_html .= "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch=&btpid=$clm'>$clm</a></td>".PHP_EOL;
				 //Status
				 $csv_html .= "<td>$sts <a class='clmstatus' target='_blank' href='edi_history_main.php?erafn=$fnm&pidenc=$clm&summary=yes'>S</a> &nbsp;&nbsp;<a target='_blank' href='edi_history_main.php?erafn=$fnm&pidenc=$clm&srchtp=encounter'>RA</a></td>".PHP_EOL;
				 //Trace
				 $csv_html .= "<td><a target='_blank' href='edi_history_main.php?erafn=$fnm&trace=$trc&srchtp=trace'>$trc</a></td>".PHP_EOL;
				 //File_835
				 $csv_html .= "<td title=$fnm><a target='_blank' href='edi_history_main.php?fvkey=$fnm'>x12</a></td>".PHP_EOL;
				 //ClaimID
				 $csv_html .= "<td title=$msg>$pr</td>".PHP_EOL;
				 //
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 } elseif ($file_type == 'dpr') {
			 // dpr case, only file type is claim, 
			 //['dpr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
			 $csv_html .= '<thead>'.PHP_EOL.'<tr>'.PHP_EOL;
			 $csv_html .= '<th>Name</th><th>SvcDate</th><th>CLM01</th><th>Status</th><th>Batch</th><th>File</th><th>Payer</th>'.PHP_EOL;
			 $csv_html .= '</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $dt = substr($csv_d[$i][1], 0, 4).'-'.substr($csv_d[$i][1], 4, 2).'-'.substr($csv_d[$i][1], 6, 2);
				 $pidenc = $csv_d[$i][2];
				 $btfile = $csv_d[$i][4];
				 $fnm = $csv_d[$i][5];
				 //$msg = $csv_d[$i][8];
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $csv_html .= "<tr class='{$bgc}'>"; 
				 //Name
				 $csv_html .= "<td>{$csv_d[$i][0]}</td>".PHP_EOL;
				 //SvcDate
				 $csv_html .= "<td>$dt</td>".PHP_EOL;
				 //CLM01
				 $csv_html .= "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch=$btfile&btpid=$pidenc'>$pidenc</a></td>".PHP_EOL;
				 //Status
				 $csv_html .= "<td><a  class='clmstatus' target='_blank' href='edi_history_main.php?dprfile=$fnm&dprclm=$pidenc'>{$csv_d[$i][3]}</a></td>".PHP_EOL;
				 //Batch
				 $csv_html .= "<td title=$btfile><a target='_blank'href='edi_history_main.php?fvkey=$btfile'>Batch</a></td>".PHP_EOL;
				 //File
				 $csv_html .= "<td title=$fnm><a target='_blank'href='edi_history_main.php?fvkey=$fnm'>dpr File</a> <a target='_blank'href='edi_history_main.php?fvkey=$fnm&readable=yes'>Text</a></td>".PHP_EOL; 
				 //Payer
				 $csv_html .= "<td>{$csv_d[$i][6]}</td>".PHP_EOL;
				 //
				 $csv_html .= "</tr>".PHP_EOL;
			 }
			 //
		 } elseif ($file_type == 'ebr' || $file_type == 'ibr')  {
			 //array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');	
			 //
			 $csv_html .= '<thead>'.PHP_EOL.'<tr>'.PHP_EOL;
			 $csv_html .= '<th>Name</th><th>SvcDate</th><th>CLM01</th><th>Status</th><th>Batch</th><th>File</th><th>Payer</th>'.PHP_EOL;
			 $csv_html .= '</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 //
				 $dt = substr($csv_d[$i][1], 0, 4).'-'.substr($csv_d[$i][1], 4, 2).'-'.substr($csv_d[$i][1], 6, 2);
				 $pidenc = $csv_d[$i][2];
				 $btfile = $csv_d[$i][4];
				 $fnm = $csv_d[$i][5];
				 //$msg = $csv_d[$i][8];
				 $csv_html .= "<tr class='{$bgc}'>";
				 //Name
				 $csv_html .= "<td>{$csv_d[$i][0]}</td>".PHP_EOL;
				 //SvcDate
				 $csv_html .= "<td>$dt</td>".PHP_EOL;
				 //CLM01
				 $csv_html .= "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch=$btfile&btpid=$pidenc'>$pidenc</a></td>".PHP_EOL;
				 //Status
				 $csv_html .= "<td><a class='clmstatus' target='_blank' href='edi_history_main.php?ebrfile=$fnm&ebrclm=$pidenc'>{$csv_d[$i][3]}</a></td>".PHP_EOL;
				 //Batch
				 $csv_html .= "<td title=$btfile><a target='_blank' href='edi_history_main.php?fvkey=$btfile'>Batch</a></td>".PHP_EOL;
				 //File
				 $csv_html .= "<td title=$fnm><a target='_blank'href='edi_history_main.php?fvkey=$fnm'>File</a> <a target='_blank'href='edi_history_main.php?fvkey=$fnm&readable=yes'>R</a></td>".PHP_EOL;
				 //Payer
				 $csv_html .= "<td title=$msg>{$csv_d[$i][6]}</td>".PHP_EOL;
				 //
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 } elseif ($file_type == 'f997')  {
			 //
			 //['f997']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'ak_num', 'File_997', 'Ctn_837', 'err_seg'); 
			 $csv_html .= '<thead>'.PHP_EOL.'<tr>'.PHP_EOL;
			 $csv_html .= '<th>Name</th><th>SvcDate</th><th>CLM01</th><th>Status</th><th>ST</th><th>File</th><th>Batch</th>'.PHP_EOL;
			 $csv_html .= '</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 //
				 $dt = substr($csv_d[$i][1], 0, 4).'-'.substr($csv_d[$i][1], 4, 2).'-'.substr($csv_d[$i][1], 6, 2);
				 $pidenc = $csv_d[$i][2];
				 $akn = $csv_d[$i][4];
				 $fnm = $csv_d[$i][5];
				 $msg = strlen($csv_d[$i][7]) ? $csv_d[$i][7] : 'ST Number';
				 //
				 $csv_html .= "<tr class='{$bgc}'>";
				 //Name
				 $csv_html .= "<td>{$csv_d[$i][0]}</td>".PHP_EOL;
				 //SvcDate
				 $csv_html .= "<td>$dt</td>".PHP_EOL;
				 //CLM01
				 $csv_html .= "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch={$csv_d[$i][6]}&btpid=$pidenc'>$pidenc</a></td>".PHP_EOL;
				 //Status
				 $csv_html .= "<td><a class='clmstatus' target='_blank' href='edi_history_main.php?fv997=$fnm&aknum=$akn'>{$csv_d[$i][3]}</a></td>".PHP_EOL;
				 //ST
				 $csv_html .= "<td title='$msg'>$akn</td>".PHP_EOL;
				 //File
				 $csv_html .= "<td><a target='_blank' href='edi_history_main.php?fvkey=$fnm'>$fnm</a></td>".PHP_EOL;
				 //Batch
				 $csv_html .= "<td><a target='_blank' href='edi_history_main.php?btctln={$csv_d[$i][6]}'>{$csv_d[$i][6]}</a></td>".PHP_EOL;
				 //
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 }  elseif ($file_type == 'f277')  {
			 //
			 //['f277']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'st_277', 'File_277', 'payer_name', 'claim_id', 'bht03_837');
			 $csv_html .= '<thead>'.PHP_EOL.'<tr>'.PHP_EOL;
			 $csv_html .= '<th>Name</th><th>SvcDate</th><th>CLM01</th><th>Status</th><th>File_277</th><th>ClaimID</th>'.PHP_EOL; //<th>Batch</th>
			 $csv_html .= '</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $csv_html .= "<tr class='{$bgc}'>";
				 //
				 $dt = substr($csv_d[$i][1], 0, 4).'-'.substr($csv_d[$i][1], 4, 2).'-'.substr($csv_d[$i][1], 6, 2);
				 $btpid = $csv_d[$i][2];
				 $f277file = $csv_d[$i][5];
                 $clmid = $csv_d[$i][7];
				 $bt_bht03 = $csv_d[$i][8];
				 //$msg277 = (strlen($csv_d[$i][9])) ? $csv_d[$i][9] : '';
				 //Name
				 $csv_html .= "<td>{$csv_d[$i][0]}</td>".PHP_EOL;
				 //SvcDate
				 $csv_html .= "<td>$dt</td>".PHP_EOL;
				 //CLM01
				 $csv_html .= "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch=$bt_bht03&btpid=$btpid'>$btpid</a></td>".PHP_EOL;
				 //Status
				 $csv_html .= "<td><a class='clmstatus' target='_blank' href='edi_history_main.php?rspfile=$f277file&pidenc=$btpid&rspstnum={$csv_d[$i][4]}'>{$csv_d[$i][3]}</a></td>".PHP_EOL;
				 //File
				 $csv_html .= "<td title='$f277file'><a target='_blank' href='edi_history_main.php?fvkey=$f277file'>File</a></td>".PHP_EOL;
				 //ClaimID
				 $csv_html .= "<td>$clmid</td>".PHP_EOL;
				 //Batch
				 //$csv_html .= "<td title='$bt_bht03'><a target='_blank' href='edi_history_main.php?btctln=$bt_bht03'>Batch</a></td>".PHP_EOL;
				 //
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 } elseif ($file_type == 'batch')  {
			 //
			 //['batch']['claim'] = array('PtName', 'SvcDate', 'clm01', 'InsLevel', 'Ctn_837', 'File_837', 'Fee', 'PtPaid', 'Provider' );
			 $csv_html .= '<thead>'.PHP_EOL.'<tr>'.PHP_EOL;
			 $csv_html .= '<th>Name</th><th>SvcDate</th><th>CLM01</th><th>Ins</th><th>Fee/PtPd</th><th>File</th><th>Provider</th>'.PHP_EOL;
			 $csv_html .= '</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 //
				 $dt = substr($csv_d[$i][1], 0, 4).'-'.substr($csv_d[$i][1], 4, 2).'-'.substr($csv_d[$i][1], 6, 2);
				 $msg = $csv_d[$i][6].' ('.$csv_d[$i][7].')'; 
				 $btfile = $csv_d[$i][5];
				 //
				 $csv_html .= "<tr class='{$bgc}'>";
				 //Name
				 $csv_html .= "<td>{$csv_d[$i][0]}</td>".PHP_EOL;
				 //SvcDate
				 $csv_html .= "<td>$dt</td>".PHP_EOL;
				 //CLM01
				 $csv_html .= "<td><a class='btclm' target='_blank' href='edi_history_main.php?fvbatch=$btfile&btpid={$csv_d[$i][2]}'>{$csv_d[$i][2]}</a></td>".PHP_EOL;
				 //Ins
				 $csv_html .= "<td>{$csv_d[$i][3]}</td>".PHP_EOL;
				 //Fee/PtPd
				 $csv_html .= "<td>$msg</td>".PHP_EOL;
				 //File
				 $csv_html .= "<td><a target='_blank' href='edi_history_main.php?fvkey=$btfile'>$btfile</a></td>".PHP_EOL;
				 //Provider
				 $csv_html .= "<td>{$csv_d[$i][8]}</td>".PHP_EOL;
				 //
				 $csv_html .= "</tr>".PHP_EOL;
			 }			 
		 } else {
			 //
			 $csv_html .= '<thead>'.PHP_EOL.'<tr>';
			 foreach ($csv_d[0] as $h) { $csv_html .= "<th>$h</th>"; }
			 $csv_html .= '</tr>'.PHP_EOL.'</thead>'.PHP_EOL.'<tbody>'.PHP_EOL; 
			 //
			 for ($i=$rwst; $i<$ln_ct; $i++) {
				 $bgc = ($i % 2 == 1 ) ? 'odd' : 'even';
				 $csv_html .= "<tr class='{$bgc}'>";
				 foreach($csv_d[$i] as $idx=>$dta) {
					 $csv_html .= "<td>$dta</td>".PHP_EOL;
				 }
				 $csv_html .= "</tr>".PHP_EOL;
			 }
		 }			 
	 } // end body of the table			  
	 //$csv_html .= "</tbody>".PHP_EOL."</table>".PHP_EOL."</div>".PHP_EOL;
	 $csv_html .= "</tbody>".PHP_EOL."</table>".PHP_EOL;
	return $csv_html;
}	
		 
/**
 * Produce an html rendition of one of our files
 * 
 * this function accepts a file name array from uploads.php, one file only,
 * or a string file path as the filepath argument
 * 
 * @uses csv_check_filepath()
 * @uses csv_parameters()
 * @uses csv_x12_segments()
 * @param mixed $filepath    string or array path to or name of one of our files
 * @return string 	 	     html formatted 
 */				  
function csv_filetohtml	($filepath) {
	//
	if (is_array($filepath) && count($filepath) > 0)  {
		$ftkey = array_keys($filepath);
		$type = $ftkey[0];
		$ftestpath = $filepath[$type][0];
		$bn = basename($ftestpath);
	} else {	
		$bn = basename($filepath);
		$params = csv_parameters("ALL");
		//
		foreach($params as $ky=>$val) {
			if ( !$params[$ky]['regex'] ) { continue; }
			if (!preg_match($params[$ky]['regex'], $bn) ) { 
				continue;
			} else {
				$type = $params[$ky]['type'];
				//$ftestpath = dirname(__FILE__) . $params[$ky]['directory'].DIRECTORY_SEPARATOR.$bn;
                $ftestpath = $params[$ky]['directory'].DIRECTORY_SEPARATOR.$bn;
				break;
			}
		}
		//
	}
	//
	if (!isset($type) ) {
		csv_edihist_log("csv_filetohtml: failed to type $bn");
		$out_str = "csv_filetohtml: failed to classify $bn <br />". PHP_EOL;
		return $out_str;
	}
	//
	$fp = csv_check_filepath($ftestpath, $type);
	if (!$fp) {
		csv_edihist_log("csv_filetohtml: could not get good path for $bn");
		$out_str = "csv_filetohtml: could not get good path for $bn <br />". PHP_EOL;
		return $out_str;
	}
	// different file types		
	if (strpos("|batch|era|f277|f997|999|837|835|ta1", (string)$type) ) { 
		// x12 file types
		$seg_ar = csv_x12_segments($fp, $type);
		if (is_array($seg_ar) && count($seg_ar['segments']) ) {
			$txt_ar = $seg_ar['segments'];
			$seg_d = $seg_ar['delimiters']['t'];
			$fltp = 'x12';
		} else {
			csv_edihist_log("csv_filetohtml: did not get segments for $bn");
			$out_str = "csv_filetohtml: did not get segments for $bn <br />". PHP_EOL;
			return $out_str;
		}			
		
	} elseif (strpos("|ibr|ebr|dpr|ack", $type) ) { 
		// clearinghouse file types (newlines)
		$fh = fopen($fp, 'r');
		if ($fh) {
		    while (($buffer = fgets($fh, 1024)) !== false) {
		        $txt_ar[] = $buffer;
		    }
		    if (!feof($fh)) {
		        csv_edihist_log("csv_filetohtml: failed to open $bn <br />");
		    }
		    fclose($fh);
		    $fltp = 'ibr';
		} else {
			csv_edihist_log("csv_filetohtml: did not get lines for $bn");
			$out_str = "csv_filetohtml: did not get lines for $bn <br />". PHP_EOL;
			return $out_str;
		}
	} elseif (strpos("|text", $type) ) { 
		// clearinghouse readable versions
		$txt_ar = file_get_contents($fp);
		$fltp = 'text';
		if (!$txt_ar) {
			csv_edihist_log("csv_filetohtml: did not get contents for $bn");
			$out_str = "csv_filetohtml: did not get contents for $bn <br />". PHP_EOL;
			return $out_str;
		}
			
	} else {
		csv_edihist_log("csv_filetohtml: $type was not matched for $bn");
		$out_str = "csv_filetohtml: $type was not matched for $bn <br />". PHP_EOL;
		return $out_str;
	}
	// we have navigated our checks and read the file
	$out_str = "";
	//
	// now prepare the html page
	$out_str = '';
	// use an ordered list format for x12 and ebr/ibr, regular text for text type
	if ($fltp == "text") {
		$out_str .= "<h4>$bn</h4>
			<div class=\"filetext\">
			<pre>
		  ";
		$out_str .= $txt_ar;
		$out_str .= "
		  </pre>";
	} elseif ($fltp == 'x12') {
		$out_str .= "<h4>$bn</h4>
			<div class=\"filetext\">
			<ol>
			";
		foreach($txt_ar as $line) {
				$out_str .= "
			   <li>
			   <p>
			     $line$seg_d
			   </p>
			   </li>
			   ";
		}
		$out_str .= "</ol>
		  ";
	  } else { 
		$out_str .= "<h4>$bn</h4>
			<div class=\"filetext\">
			<ol>
			";
		foreach($txt_ar as $line) {
				$out_str .= "
			   <li>
			   <p>
			     $line
			   </p>
			   </li>
			   ";
		}
		$out_str .= "</ol>
		  ";
	  }
	//
	$out_str .= "
	     </div>
	   </body>
	   </html>";
	//
	return $out_str;
}

/**
 * Check that the file path we are working with is a readable file.
 * 
 * If it is a file we have uploaded and we have only the file name
 * this function will type the file and find it in the uploaded files directories
 * and return the complete path.
 * 
 * @uses csv_parameters()
 * @param string $filename    name of a file that is one of our types
 * @param string $type        optional; one of our file types
 * @return string             either an empty string or a readable filepath
 */
function csv_check_filepath($filename, $type = "ALL") {
	//
	// if file is readable, just return it
	if ( is_file($filename) && is_readable($filename) ) {
		return $filename;
	}
	//
	$goodpath = "";
	$fn = basename($filename);
	//
	if ($type != "ALL") {
		$p = csv_parameters($type);
		if (is_array($p) && array_key_exists('type', $p) ) {
			// type was found 
            $fp = $p['directory'].DIRECTORY_SEPARATOR.$fn;
			if ( is_file($fp) && is_readable($fp) ) {
				$goodpath = $fp;
			}
		} else {
            csv_edihist_log("csv_check_filepath: invalid type $type");
        }
	} else {
		$p_ar = csv_parameters("ALL");
		foreach ($p_ar as $tp=>$par) { 
			//
			if ( !$p_ar[$tp]['regex'] || !preg_match($p_ar[$tp]['regex'], $fn) ) {
				continue;
			} else {
				//
                $fp = $p_ar[$tp]['directory'].DIRECTORY_SEPARATOR.$fn;
				if ( is_file($fp) && is_readable($fp) ) {
					$goodpath = $fp;
				}
				break;
			}	
		}
	}	
	//
	return $goodpath;
}

/**
 * Verify readibility and check for some expected content.
 * 
 * @uses csv_check_filepath()
 * @param string $file_path  full path to file 
 * @param string $type       one of our file types
 * @param bool $val_array    optional; default is false, whether to return filepath only 
 *                             or array(filepath, next_segment)
 * @return string|array             string file path or array if valid, otherwise 'false'
 */
function csv_verify_file( $file_path, $type, $val_array=FALSE ) {
	// check whether $file_path actually leads to a plausible x12 file
	// and supply proper directory if needed
	$fp = csv_check_filepath($file_path, $type);
	//
	// verify that the file is correct format by checking the first ST segment
	if ($fp) {
		//
		$type = strtolower($type);
		//
		if ($type == "batch" || $type == "837") { $st_str = "ST*837"; $next_segment = "GS"; $slen = 250; } 
		if ($type == "era" || $type == "835") { $st_str = "ST*835"; $next_segment = "GS"; $slen = 250;  }
		if ($type == "997" || $type == "f997") { $st_str = "ST*997"; $next_segment = "TA1";  $slen = 250; } 
		if ($type == "999" || $type == "f999") { $st_str = "ST*999"; $next_segment = "TA1"; $slen = 250;  }
		if ($type == "277" || $type == "f277") { $st_str = "ST*277"; $next_segment = "GS"; $slen = 250;  }
		if ($type == "271" || $type == "f271") { $st_str = "ST*271"; $next_segment = "GS"; $slen = 250;  }
		if ($type == "ta1") { $st_str = "TA1*"; $next_segment = "TA1"; $slen = 200;  }
		// note the ebr, ibr, dpr ack checks will not be valid for years < 2010 or > 2019
		if ($type == "ebr") { $st_str = "1|201"; $slen = 10; }
		if ($type == "ibr") { $st_str = "1|201"; $slen = 10; }
		if ($type == "dpr") { $st_str = "DPR|201"; $slen = 10; }
		if ($type == "ack") { $st_str = "1|201"; $slen = 10; }
		if ($type == "text") { $st_str = "Date Received"; $slen = 800; }  
		// for proprietary file formats,  if we have a list of clearinghouses, 
		// the $st_str could be "Availity|Emdeon|ABC " etc. and name probably found in the first 100 characters
		// instead of Date Received, which I just guess will be standard and within the first 500 characters
		$f_str = file_get_contents($fp, FALSE, NULL, 0, $slen);
		$st_pos = strpos($f_str, $st_str);
		//		
		// special check for 997/999 types, since 999 may be type 997
		if (($type == "997" || $type == "f997") && preg_match('/\.999$/', $fp) ) {
			$st_pos = strpos($f_str, "ST*999");
			$next_segment = "TA1";
		}
		//$f_str = file_get_contents($fp);
		//$st_pos = strpos(substr($f_str, 0, $slen), $st_str);
		if ( $st_pos === FALSE ) { 
		  // did not find the magic word
		  csv_edihist_log ("csv_verify_file: Error, not a valid $type file: $file_path");
		  //echo "ibr_era_check_path Error, not a valid $type file: $file_path <br />"
		  $fp = FALSE;
		  $next_segment = FALSE;
		} 
	} else {
		$next_segment = '';
	}		
	if ($val_array) { 
		return array($fp, $next_segment); 
	} else {
		return $fp;
	}
}	

/**
 * Parse Availity ebr, ibr, or dpr file to an array
 * 
 * This function will return a multidimensional array that is indexed per line of the file
 * and another array of each field or element in the line.  It uses the
 * constant IBR_DELIMITER as the element delimiter
 * 
 * @param string $file_path  complete path to file
 * @return array             multidimensional array
 */
function csv_ebr_filetoarray ($file_path ) {
	// read the ibr, ebr, dpr file into an array of arrays
	// since the file is multi-line, use fgets()
	//$delim = "|";
    $ext = substr($file_path, -3);
    $fp = csv_verify_file( $file_path, $ext);
    if (!$fp) {
        csv_edihist_log("csv_ebr_filetoarray: failed to verify $file_path");
        return false;
    }
    $ar_ibr = array();
	$fh = fopen($fp, 'r');
	if ($fh) {
		while (($buffer = fgets($fh, 4096)) !== false) {
			$ar_ibr[] = explode ( IBR_DELIMITER, trim($buffer) );
		}
	} else	{
		//     
		csv_edihist_log( "csv_ebr_filetoarray Error: failed to read " . $file_path  );
	}
	fclose($fh);
	return $ar_ibr;
}


/**
 * Parse an x12 segment text into an array.
 * 
 * @param string $sgmt_str -- a substring of all or part of a segment 
 * @param string $seg_term -- segment delimiter default is "~"
 * @param string $elem_delim -- element delimiter default is "*"
 * @return array -- exploded $sgmt_str by $element_d 
 */
function csv_x12_segment_to_array($sgmt_str, $seg_term = "~", $elem_delim = "*") {
	//
	// allow for imprecise selecting of segment strings
	//
	$slen = strlen($sgmt_str);
	if (!$slen) { return FALSE; }
	if (!strpos($sgmt_str, $elem_delim)) { return FALSE; }
	// find segment delimiters (up to two)
	$p1 = strpos($sgmt_str, $seg_term);
	$p2 = ($p1) ? strpos($sgmt_str, $seg_term, $p1+1 ) : FALSE;
	if ($p1===FALSE) {
		// assume we have just the segment text
		$seg1 = trim($sgmt_str);
	} elseif ($p1 && $p2 && ($p2 > $p1) && ($p2-$p1 < $slen) ) {
		// assume we have  end of segment ~ segment ~ begining of next segment 
		$seg1 = substr($sgmt_str, $p1+1, $p2-$p1-1);
	} elseif ($p1 !== FALSE && $p1 == 0) {
		// assume we have ~segment 
		$seg1 = substr($sgmt_str, $p1+1);
	} elseif ($p1 !== FALSE && $p1+1 == $slen ) {
		// assume we have segment~
		$seg1 = substr($sgmt_str, 0, $p1-1);
	} elseif ($p1 !== FALSE && $p1+1 < $slen ) {
		// assume we have  segment~ start of segment
		$seg1 = substr($sgmt_str, 0, $p1-1);
	} else {
		// no conjecture matched, just use it		
		$seg1 = trim($sgmt_str);
	}
	//
	$ar_seg = explode ($elem_delim, $seg1 );
	//
	return $ar_seg;
}


/**
 * Extract x12 delimiters from the ISA segment
 * 
 * There are obviously easier/faster ways of doing this, but I wanted to be able
 * to possibly extract these needed values from malformed or mishandled
 * files, so we go character by character. The array returned is empty on error, otherwise:
 * <pre>
 * array('t'=>segment terminator, 'e'=>element delimiter, 
 *       's'=>sub-element delimiter, 'r'=>repetition delimiter)
 * </pre>
 * 
 * @param string $isa_str126    first 126 characters of x12 file 
 * @param string $next_segid    the segment ID immediately following ISA segment
 * @return mixed                array or false on error                     
 */
function csv_x12_delimiters($isa_str126, $next_segid) {
	// this function reads the ISA segment and into the next segment of 
	// an x12 file, to determine the delimiters,
	// ISA segment is 106 characters, 16 elements, so the $next_segid could be dispensed with
	//
	if (substr($isa_str126, 0, 3) != "ISA") {
		// not the starting 126 characters
		csv_edihist_log("csv_x12_delimiters Error: isa_str126 does not begin with ISA");
		return FALSE;
	}
	if (! strpos($isa_str126, $next_segid) ) {
		// not the starting 126 characters
		csv_edihist_log("csv_x12_delimiters Error: next_segment $next_segid not in isa_str126 ");
		return FALSE;
	}
	//	$ns_pos = strpos($isa_str126, $next_segid);
	//  $elem_pos = strrpos (substr($isa_str126, 0, $ns_pos-1), $elem_delim);
	//  $dstr = substr($isa_str126, $elem_pos, $ns_pos-1);
	//  
	$ret_ar = array();
	$delim_ct = 0;
	$seg2_len = strlen($next_segid);  // usually "GS"  but could be "TA1" or whatever the specification says
	$rep_d = "";                        // repetition delimiter ISA11  5010A1 - per Availity EDI guide
	//
	$elem_delim = substr($isa_str126, 3, 1);  // ISA*
	$s = '';
	$chars = strlen($isa_str126);
	//
	for ($i = 0; $i < $chars; $i++) {
		if (strlen($s) >= 3 && substr($s, -$seg2_len) == $next_segid) { break; }
		$c = substr($isa_str126, $i, 1);
		if ($c == $elem_delim) { 
			if ($delim_ct == 11) { $rep_d = substr($s, 1, 1); }
			$s = $elem_delim; 
			$delim_ct++;    
		} else {
			$s .= $c;
		}
		// there are 16 elements in ISA segment
		if ($delim_ct > 16) {
			// incorrect $next_segid argument
			csv_edihist_log("csv_x12_delimiters: incorrect next_segment $next_segid does not follow ISA");
			return FALSE;
		}
	}
	if (strlen($s) >= 5) {
		$ret_ar["t"] = $s[2];
		$ret_ar["e"] = $s[0];
		$ret_ar["s"] = $s[1];
		$ret_ar["r"] = $rep_d;
	} else {
		csv_edihist_log("csv_x12_delimiters: Invalid delimiters  $s");
	}
	return $ret_ar;
}

/**
 * from php help: tleffler [AT] gmail [DOT] com 12-May-2011 08:55
 * 
 * Not used, but possibly interesting. Thought is to pass a file handle as an argument
 * @param mixed $possibleResource
 * @return bool
 */
function isResource ($possibleResource) { return !is_null(@get_resource_type($possibleResource)); }

/**
 * Parse x12 file into array of segments.
 * 
 * This function relies on csv_x12_delimiters() to get the delimiters array 
 * and on csv_verify_file() to supply the next_segment value.
 * There are some basic verifications and failures are noted in the log.
 * <pre>
 *  'path'=>pathtofile
 *  'delimiters'=>array from x12_delimiters()
 *  'segments'=>segments[i]=>segment text
 *     if $seg_array is true then segments[i]=>array of elements
 * </pre>
 * 
 * @uses csv_x12_delimiters() 
 * @uses csv_verify_file()
 * @param string $file_path  the full path for the file
 * @param string $type       one of  batch|837|era|835|997|999|277|271
 * @param bool $seg_array    whether each segment should be made into an array of elements
 * @return array|bool        array['delimiters']['segments']['path'], or false on error
 */
function csv_x12_segments($file_path, $type, $seg_array = FALSE) {
	// do verifications 
	$fp_ar = csv_verify_file($file_path, $type, TRUE );
	//
	if (!$fp_ar || !$fp_ar[0]) { 
		csv_edihist_log ("csv_x12_segments: verification failed for $file_path");
		return FALSE; 
	}
	//
	if ($fp_ar) {
		//
		$fp =$fp_ar[0];
		$next_segment = $fp_ar[1]; 
		$f_str = file_get_contents($fp);
		//
		// verify $delimiters
		$delimiters = csv_x12_delimiters(substr($f_str,0,126), $next_segment);
		$dlm_ok = FALSE;
		// here, expect $delimiters['t'] ['e'] ['s'] ['r']
		if (is_array($delimiters) && array_keys($delimiters) == array('t', 'e', 's', 'r') ) {
			$dlm_ok = TRUE;
			$seg_d = $delimiters['t'];
			$elem_d = $delimiters['e'];
			$subelem_d = $delimiters['s'];
			$rep_d = $delimiters['r'];
		} else {
			csv_edihist_log ("csv_x12_segments: invalid delimiters or delimiters wrong for $fp");
			return FALSE;
		}
	}
	// OK, now initialize variables
	$fn = basename($fp);
	$ar_seg = array();
	$ar_seg['path'] = $fp;
	$ar_seg['delimiters'] = $delimiters;
	$ar_seg['segments'] = array();
	$seg_pos = 0;                         // position where segment begins
	$st_segs_ct = 0;                      // segments in ST-SE envelope
	$se_seg_ct = "";                      // segment count from SE segment
	$isa_segs_ct = 0;					  // segments in ISA envelope
	$isa_ct = 0;						  // ISA envelope count
	$iea_ct = 0; 						  // IEA count
	$trnset_seg_ct = 0; 				  // segments by sum of isa segment count
	//
	$isa_str = "ISA".$elem_d;     		  // to reduce evaluations
	$iea_str = "IEA".$elem_d;
	$st_str = "ST".$elem_d;
	$se_str = "SE".$elem_d;
	//
	$idx = -1;
	$moresegs = true;
	while ($moresegs) {
		$idx++;
		// extract each segment from the file text
		$seg_end = strpos($f_str, $seg_d, $seg_pos);
		$moresegs = strpos($f_str, $seg_d, $seg_end+1); 
		//
		$seg_text = substr($f_str, $seg_pos, $seg_end-$seg_pos);
		$seg_pos = $seg_end + 1;
		//
		// we trim in case there are line or carriage returns
		$seg_text = trim($seg_text);
		//
		// check for non ASCII basic characters.  Note reg_ex '/[^\x20-\xFF]/' allows extended ASCII characters
		// this is partly file syntax and partly protection -- we don't want to process an imposter
		// We are mostly concerned with \x00 to \x19, the "control" characters, but apparently some are allowed
		// 
		if (preg_match_all('/[^\x20-\x7E]/', $seg_text, $matches)) { 
			csv_edihist_log ("csv_x12_segments: Non-basic ASCII character in segment ($idx) in file $fn");
			// quit here? return false;  -- actually files have probably been scanned before in upload.php
			// also x12 files have more allowed characters than these
		}
		if ($seg_array) {
			$ar_seg['segments'][] = explode($elem_d, $seg_text);
		} else {
			$ar_seg['segments'][] = $seg_text;
		}
		$st_segs_ct++;
		$isa_segs_ct++;
		//
		// some checks, if wanted
		if (substr($seg_text, 0, 4) == $isa_str) { 
			$isa_seg = explode($elem_d, $seg_text);
			$isa_id = $isa_seg[13];
			$isa_segs_ct = 1;  
			$isa_ct++;
			continue;
		}
		//
		if (substr($seg_text, 0, 3) == $st_str) { 
			// $e = strpos($seg_text, $elem_d, 8);  // ST*835* is 7 characters
			// $st02 = substr($seg_text, 7, $e-7);
			$st_ar = explode($elem_d, $seg_text);
			$st_num = $st_ar[2];
			$st_segs_ct = 1; 
			continue;
		}
		if (substr($seg_text, 0, 3) == $se_str) {
			$se_ar = explode($elem_d, $seg_text);
			$se_seg_ct = $se_ar[1];
			$se_num = $se_ar[2];
			if ($se_num != $st_num) {
				csv_edihist_log ("csv_x12_segments: ST-SE number mismatch $st_num $se_num in $fn");
			}
			if (intval($se_seg_ct) != $st_segs_ct) {
				csv_edihist_log ("csv_x12_segments: ST-SE segment count mismatch $st_segs_ct $se_seg_ct in $fn");
			}
			continue;
		}
		if (substr($seg_text, 0, 4) == $iea_str) {
			$iea_seg = explode($elem_d, $seg_text);
			$iea_id = $iea_seg[2];
			$iea_ct++;
			// 
			if ($isa_id != $iea_id) {
				csv_edihist_log ("csv_x12_segments: ISA-IEA identifier mismatch set $iea_ct in $fn");
			}
			if ($iea_ct == $isa_ct) {
				$trnset_seg_ct += $isa_segs_ct;
				if ($idx+1 != $trnset_seg_ct ) { //
					csv_edihist_log ("csv_x12_segments: IEA segment count error ({idx+1}:$trnset_seg_ct set) $iea_ct in $fn");
				}
			} else {
				csv_edihist_log ("csv_x12_segments: ISA-IEA count mismatch set $isa_ct $iea_ct in $fn");
			}
		}
		//
	}
	//
	return $ar_seg;
}		


?>
