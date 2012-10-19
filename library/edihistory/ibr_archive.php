<?php
/*************  ibr_archive.php
 * Author:  Kevin McCormick   Longview Texas  
 * 
 * Copyright 2012 Kevin McCormick    Longview, Texas
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 * Purpose: to archive old entries in the csv files and old files
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
 
/**
 * @param string $date -- expect format mm/dd/yyyy
 * @return int $days days prior to today
 */
function csv_days_prior($date) {
	//  deal with CCYY/MM/DD or MM/DD/CCYY
	$d1 = preg_split('/\D/', $date);
	if (!is_array($d1) || count($d1) != 3) {return FALSE;}
	//
	$isOK = checkdate($d1[0], $d1[1], $d1[2]) ? TRUE : checkdate($d1[1], $d1[2], $d1[0]);
	if (!$isOK) { return FALSE; }
	//
	$date1 = new DateTime(date("m/d/Y", $now=time()));
	$date2 = new DateTime($date);
	$dys = ($date1->format('U') - $date2->format('U')) / 86400;
	//
	return abs(floor($dys));
}

/**
 * Creates a zip archive of the files in the $filename_ar array and
 * returns the path/name of the archive or FALSE on error
 * 
 * @param array $parameters array for file type from csv_parameters
 * @param array $filename_ar filenames to be archived
 * @param string $archive_date  date of archive to be incorporated in archive file name
 * @return mixed   either the name of the zip archive or FALSE in case or error
 */
function csv_zip_dir($parameters, $filename_ar, $archive_date) {
	// we deal with possible maximum files issues by chunking the $fn_ar array
	//$ulim = array();
	//exec("ulimit -a | grep 'open files'", $ulim);  open files  (-n) 1024
	//
	$f_max = 200;
	$fn_ar2 = array();
	if ( count($filename_ar) > $f_max) {
		$fn_ar2 = array_chunk($filename_ar, $f_max);
	} else {
		$fn_ar2[] = $filename_ar;
	}
	//
    $zpath = csv_edih_basedir();
    $ztemp = csv_edih_tmpdir();
	$zip_name = $zpath.DIRECTORY_SEPARATOR."archive".DIRECTORY_SEPARATOR.$type."_$archive_date.zip";
	$ftmpn = $ztemp.DIRECTORY_SEPARATOR;
	$fdir = $parameters['directory'].DIRECTORY_SEPARATOR; 
	$type = $parameters['type'];
	//
	$zip_obj = new ZipArchive();
	//
	foreach($fn_ar2 as $fnz) {
		// reopen the zip archive on each loop so the open file count is controlled
		if (is_file($zip_name) ) {
			$isOK = $zip_obj->open($zip_name, ZIPARCHIVE::CHECKCONS);
			$isNew = FALSE;
		} else { 
			$isOK = $zip_obj->open($zip_name, ZIPARCHIVE::CREATE);
			$isNew = $isOK;	
		}
		//
		if ($isOK && $isNew) {
			$zip_obj->addEmptyDir($type);
			$zip_obj->setArchiveComment("archive " . $fdir . "prior to $archive_date");
		}
		//
		if ($isOK) {
			// we are working with the open archive
			// now add the files to the archive
			foreach($fnz as $fz) {
				if (is_file($fdir.$fz) ) {
					$iscp = copy($fdir.$fz, $ftmpn.$fz);
					$isOK = $zip_obj->addFile($ftmpn.$fz, $type.DIRECTORY_SEPARATOR.$fz); 
				} else {
					csv_edihist_log("csv_zip_dir: in record, but not in directory $fz ");
					// possible that file is in csv table, but not in directory?
				}
				//
				if ($isOK && $iscp) {
					// if we have added the file to the archive, remove it from the storage directory
					// but keep the /tmp file copy for now
					unlink($fdir.$fz);
				} else {
					$msg = $zip_obj->getStatusString();
					csv_edihist_log("csv_zip_dir: $type ZipArchive failed for $fz  $msg");
				} 
			} // end foreach			
		} else {
			// ZipArchive open() failed -- try to get the error message and return false
			$msg = $zip_obj->getStatusString();
			csv_edihist_log("csv_zip_dir: $type ZipArchive open() failed  $msg");
			return $isOK;
		}
		//
		// errors on close would be non-existing file added or something else
		$isOK = $zip_obj->close($zip_name);
		if (!$isOK) {
			$msg = $zip_obj->getStatusString();
			csv_edihist_log("csv_zip_dir: $type ZipArchive close() error for $fz  $msg");
			//
			return $isOK;
		}
	}  // end foreach($fn_ar2 as $fnz) 
	//
	return ($isOK) ? $zip_name : $isOK;
}		
				
				
/**
 * restores files from a zip archive or the tmp dir if the archive process needs to be aborted
 * 
 * @param string $csv_type     either 'file' or 'claim'
 * @param array $parameters    the parameters array for the particular type
 * @param array $filename_ar   array of file names that may have been deleted
 */	
function csv_restore_files($csv_type, $parameters, $filename_ar) {	
	//	
	if (!is_array($filename_ar) || !count($filename_ar)) { return FALSE; }
	//
	$csv_p = ($csv_type == 'file') ? $parameters['files_csv'] : $parameters['claims_csv'];
	$csv_dir .= dirname($csv_p).DIRECTORY_SEPARATOR;
	$csv_file = basename($csv_p);
	//	
	$fdir = $parameters['directory'] . DIRECTORY_SEPARATOR;
    if (!is_dir($fdir) ) { 
        csv_edihist_log("csv_restore_files: missing directory $fdir");
        return FALSE; 
    }	
	//
	$fileslost = array();
	//
	// we are in a jam -- archive is messed up
    $ntmpname = csv_edih_tmpdir();
	$ntmpname .= $ntmpname.DIRECTORY_SEPARATOR;
	//
	foreach($filename_ar as $fnz) {
		foreach($fnz as $fz) {
			//
			if (is_file($fdir.$fz) ) {
				// file is still in our type directory
				continue;
			} else {
				$iscp = copy($ntmpname.$fz, $fdir.$fz);
				if (!$iscp) {
					csv_edihist_log("csv_restore_files: $type archive restore failed for $fz");
					$fileslost[] = $fz;
				}
			}
		}
	}
	// put the csv file back
	$ntmpcsv = $ntmpname.DIRECTORY_SEPARATOR.$csv_file;
	$iscp = copy($ntmpcsv, $csv_dir.$csv_file);
	if (!$iscp) { 
        csv_edihist_log("csv_restore_files: archive restore may have lost $csv_file");
        $fileslost[] = $csv_file; 
    }
	//
	return $fileslost;
}


/**
 * After the archive is created, the csv record needs to be re-written so the archived
 * files are not in the csv file and hence, not searched for
 * 
 * @param string $csv_path   the tmp csv file path is expected
 * @param array $heading_ar  the column heading for the csv file
 * @param array $row_array   the data rows to be written to the file
 * @return integer           count the characters written as returned by fputcsv()
 */ 
function csv_rewrite_record($csv_path, $heading_ar, $row_array) {
	//
	// count characters written -- returned by fputcsv
	$ocwct = 0;
	$fh3 = fopen($csv_path, 'w');	

	// if we fail to open the file, return the result, expect FALSE
	if (!$fh3) { 
		csv_edihist_log("csv_rewrite_record: failed to open $csv_path");
	} else {
		// it is a an empty file, so write the heading row
		if (count($row_array) ) { 
			$ocwct += fputcsv ( $fh3, $heading_ar ); 
			// wrote heading, now add rows
			foreach($row_array as $row) {
				$ocwct += fputcsv ($fh3, $row );
			}
			fclose($fh3); 
		} else {
			csv_edihist_log("csv_rewrite_record: empty records array passed for $csv_path");
		}
	}	
	csv_edihist_log("csv_rewrite_record: wrote $ocwct characters " . count($row_array) . " rows to $csv_path");
	return $ocwct;
}


/**
 * Reads the current csv file and divides it into two arrays 'arch_csv' and 'curr_csv'
 * the 'arch_csv' contains the rows that will be archived (prior to archive_date)
 * and the 'curr_csv' contains the rows that will be retained
 * Also, if the csv_type is 'file' an array of file names is stored under 'files' key
 * 
 * @param string $csv_type
 * @param string $csv_path
 * @param integer $date_col
 * @param integer $fn_column
 * @param string $archive_date
 * @return array                $arch_ar keys ['arch_csv'] ['curr_csv'] ['files']
 */ 
function csv_archive_array($csv_type, $csv_path, $date_col, $fn_column, $archive_date) {
	//
	$fh = fopen($csv_path, 'r');
	$idx = 0;
	//
	$arch_ar = array();
	//
	$dt = strpos($archive_date, '/') ? str_replace('/', '', $archive_date) : $archive_date;
	//
	if ($fh !== FALSE) {
		while (($data = fgetcsv($fh)) !== FALSE) {
			//
			if ($idx == 0) {
				$arch_csv[] = $data;
			} else {	
				$isok = (substr($data[$date_col], 0, 8) < $dt) ? TRUE : FALSE; 
				if ($isok) {
					$arch_ar['arch_csv'][] = $data;
					if ($csv_type == 'file') { $arch_ar['files'][] = $data[$fn_column]; }
				} else {
					// retained csv rows go here
					$arch_ar['curr_csv'][] = $data;
				}	
			}
			$idx++;
		}
		flock($fh2, LOCK_UN);
		fclose($fh);
	} else {
		csv_edihist_log("csv_archive_array: failed to open " . basename($csv_path));
		return FALSE;
	}		
	return $arch_ar;	
}

/**
 * The main function in this ibr_archive.php script.  This function gets the parameters array
 * from csv_parameters() and calls the archiving functions on each type of file
 * in the parameters array.
 * 
 * @param string $archive_date    yyyy/mm/dd date prior to which is archived 
 * @return string                 descriptive message in html format
 */
function csv_archive_old($archive_date) {
	// 
	// paths
    $edih_dir = csv_edih_basedir();
    $archive_dir = $edih_dir.DIRECTORY_SEPARATOR.'archive';
    $csv_dir = $edih_dir.DIRECTORY_SEPARATOR.'csv';
    $tmp_dir = csv_edih_tmpdir();
    $tmp_dir .= $tmp_dir.DIRECTORY_SEPARATOR;
    //
	if (!is_dir($edih_dir.DIRECTORY_SEPARATOR.'archive') ) {
		// should have been created at setup
		mkdir ($edih_dir.DIRECTORY_SEPARATOR.'archive', 0755);
	}
	//
	$days = csv_days_prior($archive_date);	
	if (!$days || $days < 90 ) {
		$out_html = "Archive date $archive_date invalid or less than 90 days prior <br />" .PHP_EOL;
		return $out_html;
	}
	//
	$out_html = "Archiving records prior to $archive_date <br />" .PHP_EOL;
	//
	$dt = str_replace('/', '', $archive_date);
	//
	$isarchived = FALSE;
	$haserr = FALSE;
	$params = csv_parameters();
	//
	$f_max = 200;
	//
	foreach($params as $k=>$p) {
		$type = $p['type'];
		$fdir = $p['directory'] . DIRECTORY_SEPARATOR;
		//
		$fn_ar = array();
		$arch_csv = array();
		$curr_csvd = array();
		//
		$archive_ar = array();
		//
		// type dpr has only a claim csv type
		$head_ar = ($type == 'dpr') ?  csv_files_header($type, 'claim') : csv_files_header($type, 'file');
		//
		$fncol = $p['fncolumn'];
		$datecol = $p['datecolumn'];
		//
		// files csv temporary names
		$file_csv = $p['files_csv'];
		$file_csv_copy = $tmp_dir.basename($file_csv);
		$tmp_fold_csv = $tmp_dir.$type.'_old_'.basename($file_csv);
		$tmp_fnew_csv = $tmp_dir.$type.'_new_'.basename($file_csv);
		$iscpf = copy ($file_csv, $file_csv_copy);		
		//
		// claims csv temporary names
		$claim_csv = $p['claims_csv'];
		$claim_csv_copy = $tmp_dir.basename($claim_csv);		
		$tmp_cold_csv = $tmp_dir.$type.'_old_'.basename($claim_csv);
		$tmp_cnew_csv = $tmp_dir.$type.'_new_'.basename($claim_csv);
		$iscpc = copy ($claim_csv, $claim_csv_copy);
		//
		if (!$iscpf || !$iscpc) {
			csv_edihist_log("csv_archive_old: copy to tmp dir failed for csv file $type");
			$out_html = "Archive temporary files operation failed ... aborting <br />" .PHP_EOL;
			return $out_html;
		}
		//			
		// lock the original files
		$fh1 = fopen($file_csv, 'r');
		$islk1 = flock($fh1, LOCK_EX);
		if (!$islk1) { fclose($fh1) ; } // assume we are on a system that does not support locks
		$fh2 = fopen($claim_csv, 'r');
		$islk2 = flock($fh2, LOCK_EX);	
		if (!$islk2) { fclose($fh2) ; } // assume we are on a system that does not support locks
		//		
		// do the archive for the files_type.csv  		
		$archive_ar = csv_archive_array('file', $file_csv_copy, $datecol, $fncol, $dt);
		if (!$archive_ar) {
			csv_edihist_log("csv_archive_old: creating archive information failed for " . basename($file_csv_copy));
			continue;
		}
		$och = csv_rewrite_record($tmp_old_csv, $head_ar, $archive_ar['arch_csv']);
		$nch = csv_rewrite_record($tmp_new_csv, $head_ar, $archive_ar['curr_csv']);
		$zarch = csv_zip_dir($params, $archive_ar['files'], $archive_date);	
		// now move the reconfigured files
		// unlink the present csv file, since it is possible for a rename error if it exists
		$islk1 = ($islk1) ? flock($fh1, LOCK_UN) : $islk1;
		if ($islk1) { fclose($fh1); }
		$isunl = unlink($file_csv);
        if ($zarch) {
            // we got back the zip archive name from csv_zip_dir()
            $ismvz = rename($zarch, $archive_dir.DIRECTORY_SEPARATOR.basename($zarch) );
            $ismvo = rename($tmp_fold_csv, $archive_dir.DIRECTORY_SEPARATOR.$dt.basename($tmp_fold_csv) );
            $ismvn = rename($tmp_fnew_csv, $file_csv );
            //		
            if ($ismvz && $ismvo && $ismvn) {
                // everything is working - clear out the files we put in tmp_dir
                // the tmp dir should be empty, but there might have been something else created there
                $isclr = csv_clear_tmpdir();
                $out_html .= "Archived: type $type <br />" .PHP_EOL;
                $out_html .= "&nbsp; archived " .count($archive_ar['files']) . " files	 <br />" .PHP_EOL;
                $out_html .= "&nbsp; archived " .count($archive_ar['arch_csv']) . " rows from " .basename($file_csv) ." <br />" .PHP_EOL;
                $out_html .= "&nbsp; there are now " .count($archive_ar['curr_csv']) . " rows in " .basename($file_csv) ." <br />" .PHP_EOL; 				
            } else {
                // in case or error, try to restore everything
                $fl_ar = csv_restore_files('file', $p, $archive_ar['files']);
                if (is_array($fl_ar) && count($fl_ar) > 0) {
                    foreach ($fl_ar as $f) {
                        csv_edihist_log("csv_archive_old: lost file $f");
                    }
                } elseif (is_array($fl_ar) && count($fl_ar) == 0) {
                    csv_edihist_log("csv_archive_old archiving failed, and files restored");
                } else {
                    csv_edihist_log("csv_archive_old archive failed and files were lost");
                }
                // give a message and quit
                $out_html .= "Archiving error: type $type archive errors ... aborting <br />" .PHP_EOL;
                return $out_html;
            }    
        } else {
            // zip create error
            csv_edihist_log("csv_archive_old: creating zip archive failed for " . basename($file_csv));
            $fl_ar = csv_restore_files('file', $p, $archive_ar['files']);
            if (is_array($fl_ar) && count($fl_ar) > 0) {
                foreach ($fl_ar as $f) {
                    csv_edihist_log("csv_archive_old: lost file $f");
                }
            }
            $out_html .= "Archiving error: type $type archive errors ... aborting <br />" .PHP_EOL;
            return $out_html;
        }	
		//	
		// now we do the claims table
		//$cldate = date_create($archive_date);
        //date_sub($cldate, date_interval_create_from_date_string('1 month'));
        //$cldt = date_format($cldate, 'Ymd');
        //
		// dpr type has only claim table, treated as a file table above
		if ($type == 'dpr') { continue; }
		//
		$head_ar = csv_files_header($type, 'claim');
		//
		$archive_ar = csv_archive_array('claim', $claim_csv_copy, $datecol, $fncol, $dt);
		if (!$archive_ar) {
			csv_edihist_log("csv_archive_old: creating archive information failed for " . basename($file_csv_copy));
			continue;
		}		
		//
		$och = csv_rewrite_record($tmp_cold_csv, $head_ar, $archive_ar['arch_csv']);
		$nch = csv_rewrite_record($tmp_cnew_csv, $head_ar, $archive_ar['curr_csv']);
		//
		$islk2 = ($islk2) ? flock($fh2, LOCK_UN) : $islk2;
		if ($islk2) { fclose($fh2); }
		$isunl = unlink($claim_csv);
		$ismvo = rename($tmp_cold_csv, $archive_dir.DIRECTORY_SEPARATOR.$dt.basename($tmp_cold_csv) );
		$ismvn = rename($tmp_cnew_csv, $claim_csv );
		//		
		if ($ismvo && $ismvn) {
			// everything is working - clear out the files we put in tmp_dir
			// the tmp dir should be empty, but there might have been something else created there
			$isclr = csv_clear_tmpdir();
			$out_html .= "&nbsp; archived " .count($archive_ar['arch_csv']) . " rows from " . basename($claim_csv) ." <br />" .PHP_EOL;
			$out_html .= "&nbsp; there are now " .count($archive_ar['curr_csv']) . " rows in " . basename($claim_csv) ." <br />" .PHP_EOL; 
		} else {
			$fl_ar = csv_restore_files('claim', $p, $archive_ar['files']);
			if (is_array($fl_ar) && count($fl_ar) > 0) {
				foreach ($fl_ar as $f) {
					csv_edihist_log("csv_archive_old: lost file $f");
				}
			} elseif (is_array($fl_ar) && count($fl_ar) == 0) {
				csv_edihist_log("csv_archive_old: archiving failed, and files restored");
			} else {
				csv_edihist_log("csv_archive_old: archive failed and " . count($fl_ar) . " files were lost");
			}
			$out_html .= "Archiving error: type $type archive errors ... aborting <br />" .PHP_EOL;
			return $out_html;
		}
	} // end foreach($params as $k=>$p) 
	//
	return $out_html;
}


	


?>
