<?PHP
/** 
 * ibr_uploads.php
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
 * 
 * 
 * @author Kevin McCormick
 * @link: http://www.open-emr.org
 * @package OpenEMR
 * @subpackage ediHistory
 */
  
// 
// handle multiple file uploads of edi files
// handle a zip archive of edi files
// we need the OpenEMR $GLOBALS['site_dir'] $site = $GLOBALS['site_dir'] 
// because IMHO it makes sense to store these files under /edi/history  
// However, the particular location of the files is not important
// 
// The big issue is, of course, security.  We don't want to damage our
// systems with malicious files.  There are efforts at security here.
// Files that are not edi type will be discarded and each file is actually scanned
// for invalid characters.  Also, file permissions are set to -r-------- for uploaded files
// 
// for linux, system("file -bi -- ".escapeshellarg($uploadedfile)) can be helpful
// in getting the mime-type and character class
// 
// also the FileInfo functions would be useful, but possibly not available on php 5.2
// 
// To-DO the "fileUplZIP" $_POST key was superceded -- remove references
// 
// Note:
// To execute the 1×1 jpeg hack on a PHP server
// Create a 1×1 jpeg; Put the PHP code you want executed on the server in the embedded jpeg header, surrounded by tags
// Name your file some_random_name.jpg.php
// Tell your browser/os that a .php file is of type image/jpeg.
// Upload the file
// The simple fix that works on 99.9% of the servers out there is to manually change 
// the file extension to .jpg, .png, .gif or whatever type it’s supposed to be when the file is moved
// to prevent image header script execution, the GD (or Imagemagick) library should be used 
// to recreate the image, and that new file should be saved. 
// 
//  there is also a description area of image files that would allow code
//
// Magic numbers from Wikipedia
// and http://www.garykessler.net/library/file_sigs.html
// GIF87a 	47 49 46 38 37 61 	
// GIF89b 	47 49 46 38 39 61 	
// exe 		4D 5A 				
// zip (PK) 	50 4B 03 04 			
// .png 	89 50 4E 47 0D 0A 1A 0A 	
// ï»¿ UTF-8		EF BB BF
// 0 		FF FE  Byte-order mark for text file encoded in little-endian 16-bit Unicode Transfer Format
// 0 		FF FE 00 00  Byte-order mark for text file encoded in little-endian 32-bit Unicode Transfer Format
// 			00 00 FE FF  Byte-order mark for 32-bit Unicode Transformation Format big-endian files
// MZ (4Dh 5Ah) DR DOS 6.0
// C9h 	CP/M 3, first byte of a COM file
// %PDF 	25 50 44 46 	PDF, FDF Adobe Portable Document Format and Forms Document file
// 
// ÿØÿà..JF 	FF D8 FF E0 xx xx 4A 46  JPEG/JFIF graphics file
// 49 46 00 	  	IFF.  JFIF, JPE, JPEG, JPG 	  Trailer: FF D9 (ÿÙ)
// 
////*************///

// a security measure to prevent direct web access to this file
// must be accessed through the main calling script ibr_history.php 
// from admin at rune-city dot com;  found in php manual
// if (!defined('SITE_IN')) die('Direct access not allowed!'); 

/**
 * Rearrange the multi-file upload array
 * 
 * @param array $_files -- the $_FILES array
 * @param bool $top -- do not change, for internal use in function recursion
 * @return array
 */
function ibr_upload_multiple(array $_files, $top = TRUE) {
	// from php documentation for $_FILES predefined variable BigShark666 at gmail
	
    $files = array();
    foreach($_files as $name=>$file){
        if($top) $sub_name = $file['name'];
        else    $sub_name = $name;
         
        if(is_array($sub_name)){
            foreach(array_keys($sub_name) as $key){
                $files[$name][$key] = array(
                    'name'     => $file['name'][$key],
                    'type'     => $file['type'][$key],
                    'tmp_name' => $file['tmp_name'][$key],
                    'error'    => $file['error'][$key],
                    'size'     => $file['size'][$key],
                );
                $files[$name] = ibr_upload_multiple($files[$name], FALSE);
            }
             
        } else {
            $files[$name] = $file;
        }
    }
    return $files;
}


/**
 * Categorize and check uploaded files
 * 
 * Files are typed and scanned, if they pass scan, then the file is moved
 * to the IBR_UPLOADS_DIR and an array('type', 'name') is returned 
 * If an error occurs, FALSE is returned
 * 
 * @uses csv_verify_file()
 * @param array $param_ar -- the csv_parameters("ALL") array (so we don't have to keep creating it)
 * @param array $fidx -- individual file array from $files[$i] array
 * @param string &$html_str -- the html output that is passed around
 * @return array|bool
 */
function ibr_upload_match_file($param_ar, $fidx, &$html_str) {
	//
	if (is_array($fidx) && isset($fidx['name']) ) {
		$fn = basename($fidx['name']);
		$ftmp = $fidx['tmp_name'];
	} else {
		$html_str .= "Error: invalid file argument <br />" . PHP_EOL;
		return FALSE;
	}
	//
	if ( is_array($param_ar) && count($param_ar)) {
		$p = $param_ar;          // csv_parameters("ALL");
	} else {
		$html_str .= "Error: invalid parameters <br />" . PHP_EOL;
		return FALSE;
	}
    //
    $ibr_upldir = csv_edih_tmpdir();
	//
	$ar_fn = array();
	//
	foreach ($p as $ky=>$par) { 
		//
		if ( !$p[$ky]['regex'] ) { continue; }
		if (!preg_match($p[$ky]['regex'], $fn) ) { continue; } 
        // file name has matched an allowed extension; now, we scan the file 
        //$is_tp = TRUE;
        // 
        $fstr = file_get_contents($ftmp);
        if (!$fstr) {
            $html_str .= "Error: could not read $fn <br />" . PHP_EOL;
            return FALSE; 
        }
        // check for Non-basic ASCII character and <%, <asp:, <?, ${, #!, <?, <scr (any other evil script indicators?) 
        // basically allows A-Z a-z 0-9 !"#$%&'()*+,-./:;<=>?@[\]^_`{|}~ and newline carriage_return
        // this may need to be adapted to the allowed x12 characters, but <%, <asp:, <?, ${, and #! should never occur
        //  any other bad stuff indicators should be added
        //
        //$fltr = filter_var ( $fstr, FILTER_SANITIZE_STRING ); FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH,
        if (preg_match('/[^\x20-\x7E\x0A\x0D]|(<\?)|(<%)|(<asp)|(<ASP)|(#!)|(\$\{)|(<scr)|(<SCR)/', $fstr, $matches, PREG_OFFSET_CAPTURE)) { 
            //
            $html_str .= "Filtered character in file $fn -- not accepted <br />" . PHP_EOL;
            $html_str .= " character: " . $matches[0][0] . "  position: " . $matches[0][1] . "<br />" . PHP_EOL;
            //
            return FALSE;
        } else {
            // file matches extension and contains only allowed characters
            $newname = $ibr_upldir.DIRECTORY_SEPARATOR.$fn;
            //
            $is_mv = rename($ftmp, $newname);
            if (!$is_mv) {
                // moving file failed, check permissions for directory, since this may be a problem at start
                if ( !is_writable( $ibr_upldir ) ) { 
                    $html_str .= "write permission denied for $ibr_upldir<br />" . PHP_EOL;
                }
                $html_str .= "Error: unable to move $fn to uploads directory<br />" . PHP_EOL;
                return FALSE;
            } else {
                // file was moved, now set permissions to be -rw-------
                $iscm = chmod($newname, 0600);
                if (!$iscm) { 
                    $html_str .= "Error: failed to set permissions for {$fa['name']} -- trying to remove<br />" . PHP_EOL;
                    unlink($newname);
                    return FALSE;
                }
            }
            if ( $is_mv && $iscm) {
                // verify file by checking for expected parts -- not extensive
                // function csv_verify_file() is in csv_record_include.php
                if (csv_verify_file( $newname, $ky ) ) {
                    //
                    $ar_fn['type'] = (string)$ky;
                    $ar_fn['name'] = $newname;
                } else {
                    //
                    //$html_str .= "verify failed for $newname <br />" .PHP_EOL;
                    return FALSE;
                }
            }

        } 
        // quit the extension check loop
        break;
	}
	return $ar_fn;
} 


/**
 * Function to deal with zip archives of uploaded files
 * 
 * This function examines the zip archive, checks for unwanted files, unpacks
 * the files to the IBR_UPLOAD_DIR, and creates an array of file paths by the
 * type of file
 * 
 * @uses ibr_upload_match_file()
 * @param string $zipfilename -- the $_FILES['file']['tmp_name'];
 * @param array $param_ar -- the parameters array, so we don't have to create it here
 * @param string &$html_str -- passed by reference for appending
 * @return array $f_ar -- paths to unpacked files accepted by this function
 */
function ibr_ziptoarray($zipfilename, $param_ar, &$html_str) {
	//
	// note that this function moves files and set permissions, so platform issues may occur
	//
	// zerr array is probably not needed, use $zip_obj->getStatusString  
	$zerr = array('0'=> 'ER_OK N No error',
				'1'=>'ER_MULTIDISK N Multi-disk zip archives not supported',
				'2'=>'ER_RENAME S Renaming temporary file failed',
				'3'=>'ER_CLOSE S Closing zip archive failed',
				'4'=>'ER_SEEK S Seek error',
				'5'=>'ER_READ S Read error',
				'6'=>'ER_WRITE S Write error',
				'7'=>'ER_CRC N CRC error',
				'8'=>'ER_ZIPCLOSED N Containing zip archive was closed',
				'9'=>'ER_NOENT N No such file',
				'10'=>'ER_EXISTS N File already exists',
				'11'=>'ER_OPEN S Can not open file',
				'12'=>'ER_TMPOPEN S Failure to create temporary file',
				'13'=>'ER_ZLIB Z Zlib error',
				'14'=>'ER_MEMORY N Malloc failure',
				'15'=>'ER_CHANGED N Entry has been changed',
				'16'=>'ER_COMPNOTSUPP N Compression method not supported',
				'17'=>'ER_EOF N Premature EOF',
				'18'=>'ER_INVAL N Invalid argument',
				'19'=>'ER_NOZIP N Not a zip archive',
				'20'=>'ER_INTERNAL N Internal error',
				'21'=>'ER_INCONS N Zip archive inconsistent',
				'22'=>'ER_REMOVE S Can not remove file',
				'23'=>'ER_DELETED N Entry has been deleted'
				);
	//	
    $ibr_upldir = csv_edih_tmpdir();
    //
	$zip_obj = new ZipArchive();  
	// open archive (the ZIPARCHIVE::CREATE is supposedly necessary for microsoft)
	if ($zip_obj->open($zipfilename, ZIPARCHIVE::CREATE) !== TRUE) {
		// 
		$html_str .= "Error: Could not open archive $zipfilename <br />" . PHP_EOL;
		return FALSE;
	}
	if ($zip_obj->status != 0) {
		//
		$html_str .= "Error code: " . $zip_obj->status ." ". $zip_obj->getStatusString() . "<br />" . PHP_EOL;
		return FALSE;
	}
    // initialize output array and counter
    $f_zr = array();
    $p_ct = 0;
    // get number of files
    $f_ct = $zip_obj->numFiles;
    // get the file names
    for ($i=0; $i<$f_ct; $i++) {
		//
		$isOK = TRUE;
		$fstr = "";
		$file = $zip_obj->statIndex($i);
		$name = $file['name'];
		$oldCrc = $file['crc']; 
		// get file contents
		$fstr = stream_get_contents($zip_obj->getStream($name));
		// file -bi  277-201203140830-001.277ibr --> 'text/plain'; charset=us-ascii 
		// for linux servers: echo system("file -b '<file path>'");
		if ($fstr) { 
			// use only the file name
			$bnm = basename($name);
			//
			// $newname = tempnam (IBR_UPLOAD_DIR , "edi"); --won't work since we need the file name to classify
			// the scheme of inserting "ediz" into the name allows us to do the CRC test and then
			// rename the file, all in the temporary directory
			// Note that BCBS files have no extension, just a name scheme (recently changed)			
			if (strpos($bnm, "835") === 0) { 
                $newname = $ibr_upldir.DIRECTORY_SEPARATOR.$bnm."ediz";
			} else {
                $newname = $ibr_upldir.DIRECTORY_SEPARATOR."ediz".$bnm;
			}
			//
			// extract the file to unzip tmp dir with read/write access
			$chrs = file_put_contents($newname, $fstr); 
			// test crc
			$newCrc = hexdec(hash_file("crc32b",$newname) );
			// is this the best way to do this test?
			if($newCrc !== $oldCrc && ($oldCrc + 4294967296) !== $newCrc) { 
				// failure case, mismatched crc file integrity values
				$html_str .= "CRC error: The files don't match! Removing file $bnm <br />" . PHP_EOL;
				$isGone = unlink($newname);
				if ($isGone) { 
					$is_tmpzip = FALSE;
					$html_str .= "File Removed $bnm<br />".PHP_EOL; 
				} else {
					$html_str .= "Failed to removed file $bnm<br />".PHP_EOL;
				}
			} else {
				// passed the CRC test, now type and verify file
				$fzp['name'] = $bnm;
				$fzp['tmp_name'] = $newname;		// tmp/edihist/ediz.$bnm or 83511111---ediz
				// verification checks special to our application
				$f_uplz = ibr_upload_match_file($param_ar, $fzp, $html_str);
				//
				if (is_array($f_uplz) && count($f_uplz) > 0 ) { 
					$t = $f_uplz['type'];
					$n = $f_uplz['name'];
					$f_zr[$t][] = $n;
					$p_ct++; 
				} else {
					// verification failed
					$f_zr['reject'][] = $fzp['name'];
				}
			}
			//				
		} else {
			$html_str .= "Did not get file contents $name" . PHP_EOL;
			$isOK = FALSE;
		} 
	} // end for ($i=0; $i<$numFiles; $i++)
	//
	$html_str .= "Accepted $p_ct of $f_ct files from $zipfilename <br />" .PHP_EOL;
	//
	return $f_zr;
}

/**
 * Main function that handles the upload files array
 * 
 * The return array has keys 'type' and subarray of file names
 * relies on global $_POST and $_FILES variables
 * 
 * @uses ibr_upload_multiple()
 * @uses ibr_ziptoarray()
 * @uses ibr_upload_match_file()
 * @uses csv_parameters()
 * @param string &$html_str   referenced and appended to in this function
 * @return array             array of files that pass the checks and scans
 */
function ibr_upload_files(&$html_str) {
	//
	// from php manual ling 03-Nov-2010 08:35
	if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {    
		$pmax = ini_get('post_max_size'); 
		//
		$html_str .= "Error: upload too large, maximum allowed size is $pmax <br />". PHP_EOL;
		return FALSE;
	} 
	if (empty($_FILES) ) {
		$html_str .= "Error: upload files indicated, but none received. <br />". PHP_EOL;
		return FALSE;
	}
	// only one is expected 
	$fkey = array_key_exists("fileUplEra", $_FILES) ? "fileUplEra" : "";
	//$fkey = array_key_exists("fileUplZIP", $_FILES) ? "fileUplZIP" : $fkey; // fileUplMulti does zip files
	$fkey = array_key_exists("fileUplMulti", $_FILES) ? "fileUplMulti" : $fkey;
	$fkey = array_key_exists("fileUplx12", $_FILES) ? "fileUplx12" : $fkey;
	//
	if (!$fkey) {
		$html_str .= "Error: file array name error <br />" . PHP_EOL;
		return FALSE;	
	}
	// these are the mime-types that we will accept -- however, mime-type is not reliable
	// for linux, system("file -bi -- ".escapeshellarg($uploadedfile)) gives mime-type and character encoding
	// 
	$m_types = array('application/octet-stream', 'text/plain', 'application/zip', 'application/x-zip-compressed');
	//
	// to give informative error message
	$upload_err = array('0' => array('UPLOAD_ERR_OK', 'There is no error, the file uploaded with success.'), 
						'1' => array('UPLOAD_ERR_INI_SIZE',  'The uploaded file too large.'), 
						'2' => array('UPLOAD_ERR_FORM_SIZE', 'The uploaded file too large'), 
						'3' => array('UPLOAD_ERR_PARTIAL', 'The uploaded file was only partially uploaded.'), 
						'4' => array('UPLOAD_ERR_NO_FILE', 'No file was uploaded.'), 
						'6' => array('UPLOAD_ERR_NO_TMP_DIR', 'Missing a temporary folder.'),
						'7' => array('UPLOAD_ERR_CANT_WRITE', 'Failed to write file to disk.'), 
						'8' => array('UPLOAD_ERR_EXTENSION', 'A PHP extension stopped the file upload.')
						);
    // we get the parameters here to send to ibr_upload_match_file()
    $param_ar = csv_parameters("ALL");
    $paramtypes = array_keys($param_ar);
    //
    // initialize retained files array and counter
    $f_ar = array();
    $p_ct = 0;
	
	// here send the $_FILES array to ibr_upload_multiple for "fileUplMulti"
	// instead of $_FILES[$fkey] ["name"][$i] ["tmp_name"][$i] ["type"][$i] ["error"][$i] ["size"][$i] 
	// we will have $files[$fkey][$i] ["name"]["tmp_name"]["type"]["error"]["size"]
	if ($fkey == "fileUplMulti") {
		$files = ibr_upload_multiple($_FILES);
	} else {
		$files[$fkey][] = $_FILES[$fkey];
	}
	//
	$f_ct = count($files[$fkey]);
	//begin the check and processing loop
	foreach($files[$fkey] as $idx=>$fa) {
		// verify that we have a usable name
		if (is_string($fa['name'])) {
			// check for null byte in file name, linux hidden file, directory
			if (strpos($fa['name'], '.') === 0 || strpos($fa['name'], "\0") || strpos($fa['name'], "./") ) {
				$html_str .= "Error: uploaded_file error for " . $fa['name'] . "<br />". PHP_EOL;
				unset($files[$fkey][$idx]);
				continue;
			}
			// replace spaces in file names -- should not happen, but response files from payers might have spaces
			// $fname = preg_replace("/[^a-zA-Z0-9_.-]/","_",$fname);
			$fa['name'] = str_replace(' ', '_', $fa['name']);
		} else {
			// name is not a string
			$html_str .= "Error: uploaded_file error for " . $fa['tmp_name'] . "<br />". PHP_EOL;
			unset($files[$fkey][$idx]);
			continue;
		}
		// basic php verification checks
		if ($fa['error'] !== UPLOAD_ERR_OK ) {
			$html_str .= "Error: code " . $fa['error'] ." ". $fa['name'] ." ". $upload_err[$fa['error']][1] . "<br />" . PHP_EOL;
			unset($files[$fkey][$idx]);
			continue;
		}
		
		if ( !$fa['tmp_name'] || !$fa['size'] ) {
			$html_str .= "Error: file name or size error <br />" . PHP_EOL;
			unset($files[$fkey][$idx]);
			continue;
		}
		
		if ( !is_uploaded_file($fa['tmp_name']) ) {
			$html_str .= "Error: uploaded_file error for " . $fa['tmp_name'] . "<br />". PHP_EOL;
			unset($files[$fkey][$idx]);
			continue;
		}

		if ( !in_array($fa['type'], $m_types) ) {
			$html_str .= "Error: mime-type {$fa['type']} not accepted for {$fa['name']} <br />" . PHP_EOL;
			unset($files[$fkey][$idx]);
			continue;
		}
		// verification checks special to our application
		//
		//////////////////////////////////
		// this is where check for additional upload control names would be inserted
		//  if ($fkey == 'fileUploadControlName')
		// each upload control name should have its classify and verify functions
		// and a type key for the $f_ar filenames array that is returned
		//////////////////////////////////
		///////// zip archives had a separate file upload input control, but it is redundant
		///////// because the functionality is handled through the fileUplMulti control
		// check for zip file archive -- they are dealt with elsewhere
		/* ********************* to be removed
		if ($fkey == "fileUplZIP" && in_array($fa['type'], array('application/zip', 'application/x-zip-compressed', 'application/octet-stream')) ) { 
			// debug
			//echo "ibr_upload_file: files array key: $fkey {$fa['name']}<br />" . PHP_EOL;
			// get the files -- we expect only one zip file in this key
			// if the type is 'application/octet-stream', log it
			if ($fa['type'] == 'application/octet-stream') {
				csv_edihist_log("ibr_upload_files: upload zip file, mime-type application/octet-stream");
			}
			$f_ar = ibr_ziptoarray($fa['tmp_name'], $param_ar, $html_str);
			// get a count
			foreach($f_ar as $k=>$v) { $p_ct += count($v); }
			continue; 
		}
		* ************************ */
		//
		if ( $fkey != "fileUplMulti" && strpos($fa['name'], ".zip") ) {
			$html_str .= "zip archives are not accepted through this input {$fa['name']} <br />" . PHP_EOL;
			continue;
		}
		// case of a zip file included in the multi file upload or era upload
		if ( $fkey == "fileUplMulti" && strpos($fa['name'], ".zip") ) {
			//
			// this is a bit involved since we cannot predict how many files will be returned 
			// get an array of files from the zip unpack function
			$f_upl = ibr_ziptoarray($fa['tmp_name'], $param_ar, $html_str);
			// put them in the correct type array 
			if (is_array($f_upl) && count($f_upl)) { 
				foreach($f_upl as $tp=>$fz) {
					// expect $fz to be an array of file names
                    //strpos("|batch|ibr|ebr|dpr|f997|f277|era|ack|ta1|text", $tp)
					if ( strlen($tp) && in_array($tp, $paramtypes) ) {
						if (array_key_exists($tp, $f_ar) ) {
							foreach($f_upl[$tp] as $zf) {
								$f_ar[$tp][] = $zf;
								$p_ct ++;
							}
							//
						} else {
							$f_ar[$tp] = $f_upl[$tp];
							$p_ct += count($f_upl[$tp]);
						}
					} else {
                        // verification failed -- ibr_ziptoarray creates its own 'reject' key
                        $html_str .= "wrong classification for " . $fa['name'] . "<br />" .PHP_EOL;
                        unset($files[$fkey][$idx]);	
					}
				} // end foreach ($f_upl as $tp)
			} else {
				// nothing good from ibr_ziptoarray()
				$html_str .= "verification failed for " . $fa['name'] . "<br />" .PHP_EOL;
				unset($files[$fkey][$idx]);
			}
			// continue, since we have done everything that would happen below
			continue;
		}
		//////////
		// at this point, since we have come through all the if statements
		// then we have:
		//  a single file under "fileUplEra" 
		//  a single file under "fileUplx12"
		//  or one of possibly several files under "fileUplMulti"
		//////////
		$f_upl = ibr_upload_match_file($param_ar, $fa, $html_str);
		// 
		if (is_array($f_upl) && count($f_upl) > 0 ) { 
			$tk = $f_upl['type'];
			//if ( strlen($tk) && strpos("|batch|ibr|ebr|dpr|f997|f277|era|ack|ta1|text", $tk) ) {
            if (strlen($f_upl['type']) && in_array($f_upl['type'], $paramtypes)) {
				$f_ar[$f_upl['type']][] = $f_upl['name'];
				$p_ct++;
			}
		} else {
			// verification failed
			$html_str .= "verification failed for " . $fa['name'] . "<br />" .PHP_EOL;
			$f_ar['reject'][] = $fa['name'];
			unset($files[$fkey][$idx]);
		}
	} // end foreach($files[$fkey] as $idx=>$fa) 
	//
	$html_str .= "Received $f_ct files, accepted $p_ct<br />" . PHP_EOL;					
	return $f_ar;
}

/**
 * Sort the uploaded files array and save them to the correct directory
 * 
 * If a matching filename file is already in the directory it is not overwritten.
 * The uploaded file will just be discarded.
 * 
 * @uses csv_parameters()
 * @see ibr_upload_files() 
 * @param array $files_array  files array created by ibr_upload_files()
 * @param bool       -- whether to return html output
 * @param bool       -- whether to only report errors (ignored)
 * @return string    html formatted messages
 */
function ibr_sort_upload($files_array, $html_out = TRUE, $err_only = TRUE) {
	//
	$prc_htm = '';
	//
	if ( count($files_array) > 0 ) {
		// we have some files
		$p_ar = csv_parameters($type="ALL");
        $ptypes = array_keys($p_ar);
		//
		if (array_key_exists('reject', $files_array) ) {
			foreach($files_array['reject'] as $rjc) {
				$prc_htm .= "Rejected file: $rjc <br />" .PHP_EOL;
			}
			$prc_htm .="<p>&nbsp;</p>";
			unset($files_array['reject']);
		}
			
		foreach ($files_array as $key=>$val) {
			// use keys from parameters array $ftypes
			//if ( strpos("|batch|ibr|ebr|dpr|f997|f277|era|ack|ta1|text", $key) ) {
            if (in_array($key, $ptypes)) {
                $t_dir = $p_ar[$key]['directory'];
				$t_base = basename($t_dir);
			} else {
				$prc_htm .= "<p>Type $key is not stored </p>" .PHP_EOL;
				continue;
			}
			$idx = 0;
			foreach($files_array[$key] as $idx=>$nf) {
				// check if the file has already been stored
				// a matching file name will not be replaced
				$nfb = basename($nf);
				//
				if ($key == 'reject') {
					$prc_htm .= "Rejected: $nfb<br />" .PHP_EOL;
					continue;
				}
				
				$testname = $t_dir.DIRECTORY_SEPARATOR.$nfb;
				if ( is_file($testname) ) {
					$prc_htm .= "File already in $t_base: $nfb <br />" .PHP_EOL;
				} elseif (rename($nf, $testname) ) {
					$iscm = chmod($testname, 0400);
					if (!$iscm) { 
						// if we could write, we should be able to set permissions
						$prc_htm .= "Error: failed to set permissions for $nfb, attempting to remove file<br />" . PHP_EOL;
						unlink($testname);
					}					
					$prc_htm .= "Saved in $t_base: $nfb <br />" .PHP_EOL;
				} else {
					$prc_htm .= "error saving $nf to $t_dir directory <br />" .PHP_EOL;
				}
			}
			if (count($files_array[$key]) == 0) { 
				$prc_htm .= "Upload: type $key submitted with no files <br />" . PHP_EOL;
				continue; 
			}
		}
	} else {
		// should not happen since this function should not be called unless there are new files
		$prc_htm .= "No edi files submitted<br />" . PHP_EOL;
	}
	//
	$prc_htm .= "<p>Upload more or click the \"<em>Process New</em>\" button to process new files.</p>" .PHP_EOL;
	//$prc_htm .= "</body></html>";
	return $prc_htm;
}



?>
