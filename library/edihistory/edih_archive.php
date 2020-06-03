<?php

/**
 * edih_archive.php
 * Purpose: to archive old entries in the csv files and old files
 *
 * @package    OpenEMR
 * @subpackage ediHistory
 * @link       https://www.open-emr.org
 * @author     Kevin McCormick
 * @copyright  Copyright (c) 2016 Kevin McCormick    Longview, Texas
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// a security measure to prevent direct web access to this file
// must be accessed through the main calling script ibr_history.php
// from admin at rune-city dot com;  found in php manual
//if (!defined('SITE_IN')) die('Direct access not allowed!');

//
// required functions
//require_once("$srcdir/edihistory/test_edih_csv_inc.php");
//
// constant DS = DIRECTORY_SEPARATOR

/**
 * Report on edi_history
 *
 * @uses csv_parameters()
 * @uses csv_assoc_array()
 *
 * @param string  archive date in CCYYMMDD format
 *
 * @return array   array[i] = filename
 */
function edih_archive_report($period = '')
{
    //
    $str_html = '';
    $chkdt = '';
    $strdt = '';
    // edih_archive_date returns empty string if no period
    $tper =  edih_archive_date($period);
    $chkdt = ($tper) ? $tper : 'None';
    $strdt = ($tper) ? substr($chkdt, 0, 4) . '-' . substr($chkdt, 4, 2) . '-' . substr($chkdt, 6, 2) : 'None';
    //
    csv_edihist_log("edih_archive_report: creating archive report with date $chkdt");
    //
    $bdir = csv_edih_basedir();
    $params = csv_parameters('ALL');
    if (!is_array($params)  && count($params)) {
        csv_edihist_log("edih_archive_report: invalid csv_parameters");
        return "<p>There was an error creating the report.</p>";
    }

    //
    $str_html .= "<h3>Report on edi files using archive date " . text($strdt) . "</h3>" . PHP_EOL;
    foreach ($params as $key => $param) {
        $old_ct = 0;
        $clm_ct = 0;
        $dir_ct = 0;
        $dir_sz = 0;
        $row_ct = 0;
        $dir_kb = '';
        $subdir_ct = 0;
        $fntp = '';
        $fntp_ct = 0;
        //
        $tp = $param['type'];
        $fdir = $param['directory'];
        //
        if (is_dir($fdir)) {
            $dir_ar = scandir($fdir);
            if (is_array($dir_ar) && ((count($dir_ar) - 2) > 0)) {
                $str_html .= "<H3><em>Type</em> " . text($tp) . "</H3>" . PHP_EOL;
                $str_html .= "<ul>" . PHP_EOL;
                $dir_ct = count($dir_ar);
                foreach ($dir_ar as $fn) {
                    if ($fn == 'README.txt') {
                        $dir_ct--;
                        continue;
                    }

                    if (substr($fn, 0, 1) == '.') {
                        $dir_ct--;
                        continue;
                    }

                    if (is_dir($fdir . DS . $fn)) {
                        $subdir_ct++;
                        $dir_ct--;
                        continue;
                    }

                    $dir_sz += filesize($fdir . DS . $fn);
                }

                //
                $csv_ar = csv_assoc_array($tp, 'file');
                $row_ct = (is_array($csv_ar)) ? count($csv_ar) : 0;
                if ($row_ct) {
                    foreach ($csv_ar as $row) {
                        // tally amount of claim transactions in the files
                        if ($tp == 'f837') {
                            $clm_ct += $row['Claim_ct'];
                        }

                        if ($tp == 'f277') {
                            $clm_ct += ($row['Accept'] + $row['Reject']);
                        }

                        if ($tp == 'f835') {
                            $clm_ct += $row['Claim_ct'];
                        }

                        // check for duplicates
                        // here assume that files with multiple rows are consecutive
                        if ($fntp !== $row['FileName']) {
                            $fntp = $row['FileName'];
                            // count files that would be archived
                            if (($chkdt != 'None') && strcmp($row['Date'], $chkdt) < 0) {
                                $old_ct++;
                            }

                            $fntp_ct++;
                        }
                    }

                    //
                    $dir_kb = csv_convert_bytes($dir_sz);
                } else {
                    csv_edihist_log("edih_archive_report: error $tp file count $dir_ct with csv rows $row_ct");
                }

                //
                $mis = ($fntp_ct == $dir_ct ) ? "(check/eft count or ISA--IEA count)" : "(mismatch csv $fntp_ct dir $dir_ct)";
                //
                if ($tp == 'f837') {
                    $str_html .= "<li><em>Note:</em> 837 Claims files are not archived </li>" . PHP_EOL;
                }

                //
                $str_html .= "<li>files directory has " . text($dir_ct) . " files, using " . text($dir_kb) . " </li>" . PHP_EOL;
                $str_html .= ($subdir_ct && ($tp != 'f837'))  ? "<li> <em>warning</em> found " . text($subdir_ct) . " sub-directories</li>" . PHP_EOL : "";
                $str_html .= "<li>files csv table has " . text($row_ct) . " rows " . text($mis) . "</li>" . PHP_EOL;
                $str_html .= ($clm_ct) ? "<li>there are " . text($clm_ct) . " claim transactions counted</li>" . PHP_EOL : "";
                $str_html .= ($old_ct) ? "<li>Archive date " . text($strdt) . " would archive " . text($old_ct) . " files </li>" . PHP_EOL : "";
                $str_html .= "</ul>" . PHP_EOL;
            } else {
                $str_html .= "<p><em>Type</em> <b>" . text($tp) . "</b> <br /> -- empty " . text($tp) . " file directory</p>" . PHP_EOL;
            }
        } else {
            $str_html .= "<p><em>warning</em> <b>" . text($tp) . "</b> file directory does not exist</p>" . PHP_EOL;
        }
    }

    $str_html .= "<p>Report end</p>" . PHP_EOL;
    //
    return $str_html;
}


/**
 * Format the date used in comparisons
 *
 * @param string   period from select list e.g. 6m, 12m
 *
 * @return string  archive date in CCYYMMDD format
 */
function edih_archive_date($period)
{
    //
    $dtpd2 = '';
    if (!$period) {
        return $dtpd2;
    }

    $is_period = preg_match('/\d{1,2}(?=m)/', $period, $matches);
    //
    if (count($matches)) {
        $gtdt = getdate();
        //
        if (strpos($period, 'm')) {
            // take the number part of 'period'
            // so modstr will be '-N month'
            $modstr = '-' . $matches[0] . ' month';
            $dtstr1 = $gtdt['mon'] . '/01/' . $gtdt['year'];
        } elseif (strpos($period, 'y')) {
            $modstr = '-' . $matches[0] . ' year';
            $dtstr1 = $gtdt['mon'] . '/01/' . $gtdt['year'];
        } else {
            csv_edihist_log("edih_archive_date: incorrect date period $period");
            return false;
        }

        //
        if ($modstr) {
            $dtpd1 = date_create($dtstr1);
            $dtm = date_modify($dtpd1, $modstr);
            $dtpd2 = $dtm->format('Ymd');
        } else {
            csv_edihist_log("edih_archive_date: failed to parse $period");
            return false;
        }
    } else {
        csv_edihist_log("edih_archive_date: invalid argment $period");
        return false;
    }

    // the testing date in CCYYMMDD format
    return $dtpd2;
}


/**
 * Create an array of file names to be archived
 * The 'Date' column in the files_[type].csv file
 * is compared to the archive date.  If the date is less
 * than the archive date, the "FileName' value is copied
 *
 * @param array  csv file rows array
 * @param string  archive date in CCYYMMDD format
 *
 * @return array   array[i] = filename
 */
function edih_archive_filenames($csv_ar, $archive_date)
{
    //
    if ($archive_date && strlen($archive_date) == 8 && is_numeric($archive_date)) {
        $testdate = (string)$archive_date;
    } else {
        csv_edihist_log("edih_archive_filenames: invalid archive date $archive_date");
        return false;
    }

    //
    if (!is_array($csv_ar) || !count($csv_ar)) {
        csv_edihist_log("edih_archive_filenames: failed to get csv file array $file_type");
        return false;
    }

    //
    $fn_ar = array();
    foreach ($csv_ar as $row) {
        if (strcmp($row['Date'], $archive_date) < 0) {
            $fn_ar[] = $row['FileName'];
        }
    }

    $ret_ar = (count($fn_ar)) ? array_values(array_unique($fn_ar)) : $fn_ar;
    //
    return $ret_ar;
}

/**
 * Create a new csv array by omitting rows which reference
 * a file name that is to be archived
 *
 * @uses csv_file_type()
 * @uses csv_assoc_array()
 *
 * @param string  the file type
 * @param string  the csv type file or claim
 * @param array   the array of archived file names and retained file names
 *
 * @return array
 */
function edih_archive_csv_split($csv_ar, $filename_array)
{
    //
    if (!is_array($filename_array) || !count($filename_array)) {
        csv_edihist_log('csv_archive_table; invalid filename array');
        return false;
    }

    //
    if (is_array($csv_ar) && count($csv_ar)) {
        csv_edihist_log("edih_archive_csv_split: csv rows " . count($csv_ar) . " old files " . count($filename_array));
    } else {
        csv_edihist_log("edih_archive_csv_split: failed to get csv file array");
        return false;
    }

    //
    // if the to be archived file name is in the row,
    // do not copy it to the new csv array
    $arch_ar = array();
    $arch_ar['arch'] = array();
    $arch_ar['keep'] = array();
    //
    foreach ($csv_ar as $row) {
        if (in_array($row['FileName'], $filename_array)) {
            $arch_ar['arch'][] = $row;
        } else {
            $arch_ar['keep'][] = $row;
        }
    }

    //
    csv_edihist_log("edih_archive_csv_split: 'arch' array rows " . count($arch_ar['arch']));
    csv_edihist_log("edih_archive_csv_split: 'keep' array rows " . count($arch_ar['keep']));
    //
    return $arch_ar;
}


/**
 * Creates a zip archive of the files in the $filename_ar array and
 * returns the path/name of the archive or FALSE on error
 *
 * @param array $parameters array for file type from csv_parameters
 * @param array $filename_ar array of filenames to be archived
 * @param string $archive_date  date of archive to be incorporated in archive file name
 *
 * @return bool   result of zipArchive functions
 */
function edih_archive_create_zip($parameters, $filename_ar, $archive_date, $archive_filename)
{
    // we deal with possible maximum files issues by chunking the $fn_ar array
    //
    $ft = $parameters['type'];
    $fdir = $parameters['directory'];
    $tmp_dir = csv_edih_tmpdir();
    // archive csv rows -- same name as from edih_archive_main
    // $fn_files_arch = $tmp_dir.DS.'arch_'.basename($files_csv);
    $files_csv_arch = 'arch_' . basename($parameters['files_csv']);
    // $fn_claims_arch = $tmp_dir.DS.'arch_'.basename($claim_csv);
    $claims_csv_arch = 'arch_' . basename($parameters['claims_csv']);
    //
    $f_max = 200;
    $fn_ar2 = array();
    // to handle possibility of more than 200 files in the archive
    // use the 'chunk' method
    if (count($filename_ar) > $f_max) {
        $fn_ar2 = array_chunk($filename_ar, $f_max);
    } else {
        $fn_ar2[] = $filename_ar;
    }

    //
    $zip_name = $tmp_dir . DS . $archive_filename;
    csv_edihist_log("edih_archive_create_zip: using $zip_name");
    //
    $zip_obj = new ZipArchive();
    csv_edihist_log("edih_archive_create_zip: now opening archive $archive_filename");
    if (is_file($zip_name)) {
        $isOK = $zip_obj->open($zip_name, ZipArchive::CHECKCONS);
        if ($isOK) {
            if ($zip_obj->locateName($ft) === false) {
                $isOK = $zip_obj->addEmptyDir($ft);
                if (!$isOK) {
                    csv_edihist_log("edih_archive_create_zip: adding $ft ZipArchive error $msg");
                    return $isOK;
                }
            }
        } else {
            $msg = $zip_obj->getStatusString();
            csv_edihist_log("edih_archive_create_zip: $ft ZipArchive error $msg");
            return $isOK;
        }
    } else {
        $isOK = $zip_obj->open($zip_name, ZipArchive::CREATE);
        $isOK = $zip_obj->addEmptyDir('csv');
        $isOK = $zip_obj->addEmptyDir($ft);
        $zip_obj->setArchiveComment("edi_history archive prior to $archive_date");
    }

    // we are working with the open archive
    // now add the old csv files to the archive
    if (is_file($tmp_dir . DS . $files_csv_arch)) {
        csv_edihist_log("edih_archive_create_zip: now adding $files_csv_arch to archive");
        $isOK = $zip_obj->addFile($tmp_dir . DS . $files_csv_arch, 'csv' . DS . $files_csv_arch);
    }

    if (is_file($tmp_dir . DS . $claims_csv_arch)) {
        csv_edihist_log("edih_archive_create_zip: now adding $claims_csv_arch to archive");
        $isOK = $zip_obj->addFile($tmp_dir . DS . $claims_csv_arch, 'csv' . DS . $claims_csv_arch);
    }

    // close zip archive
    csv_edihist_log("edih_archive_create_zip: now closing archive");
    $isOK = $zip_obj->close();
    if ($isOK !== true) {
        $msg = $zip_obj->getStatusString();
        csv_edihist_log("edih_archive_create_zip: $ft ZipArchive error $msg");
        return $isOK;
    }

    // $fn_ar2[i][j]
    csv_edihist_log("edih_archive_create_zip: with file name groups " . count($fn_ar2));
    foreach ($fn_ar2 as $fnz) {
        // reopen the zip archive on each loop so the open file count is controlled
        if (is_file($zip_name)) {
            csv_edihist_log("edih_archive_create_zip: now opening archive");
            $isOK = $zip_obj->open($zip_name, ZipArchive::CHECKCONS);
        }

        //
        if ($isOK === true) {
            // we are working with the open archive
            // now add the old x12 files to the archive
            csv_edihist_log("edih_archive_create_zip: now adding $ft files to archive");
            foreach ($fnz as $fz) {
                if ($fz == '.' || $fz == '..') {
                    continue;
                }

                if (is_file($fdir . DS . $fz) && is_readable($fdir . DS . $fz)) {
                    $isOK = $zip_obj->addFile($fdir . DS . $fz, $ft . DS . $fz);
                } else {
                    // possible that file is in csv table, but not in directory?
                    $msg = $zip_obj->getStatusString();
                    csv_edihist_log("edih_archive_create_zip: error adding file $fz zipArchive: $msg");
                }
            } // end foreach($fnz as $fz)
            // close zip object for next iteration of chunked array
            csv_edihist_log("edih_archive_create_zip: now closing archive");
            $isOK = $zip_obj->close();
            // errors on close would be non-existing file added or something else
            if ($isOK !== true) {
                $msg = $zip_obj->getStatusString();
                csv_edihist_log("edih_archive_create_zip: $ft ZipArchive error $msg");
                //
                return $isOK;
            }
        } else {
            // ZipArchive open() failed -- try to get the error message and return false
            $msg = $zip_obj->getStatusString();
            csv_edihist_log("edih_archive_create_zip: $ft ZipArchive failed  $msg");
            return $isOK;
        }// end if ($isOK)
        //
    } // end foreach($fn_ar2 as $fnz)
    //
    return $isOK;
}

/**
 * Archived files have been included in archive file
 * so we move the files to the archive tmp directory, for later deletion
 *
 * @param array   parameters array for type
 * @param array   filename array
 *
 * @return int    count of moved files
 */
function edih_archive_move_old($parameters, $filename_ar)
{
    //
    if (!is_array($filename_ar) || !count($filename_ar)) {
        return false;
    }

    if (!is_array($parameters) || !count($parameters)) {
        return false;
    }

    //
    clearstatcache(true);
    //
    $fnct = 0;
    $fn_ar_ct = count($filename_ar);
    $ft = $parameters['type'];
    $fdir = $parameters['directory'];
    $fdir = realpath($fdir);
    $rndir = csv_edih_tmpdir() . DS . $ft;
    //
    if (is_dir($fdir)) {
        csv_edihist_log("edih_archive_delete_old: $ft dir OK");
        if (is_dir($rndir) || mkdir($rndir)) {
            $rndir = realpath($rndir);
            csv_edihist_log("edih_archive_delete_old: $ft move dir OK");
            $isOK = true;
        } else {
            csv_edihist_log("edih_archive_delete_old: $ft move dir error");
            $isOK = false;
        }
    } else {
        csv_edihist_log("edih_archive_delete_old: $ft dir error");
        $isOK = false;
    }

    //
    if ($isOK) {
        csv_edihist_log("edih_archive_delete_old: $ft old file count $fn_ar_ct");
        foreach ($filename_ar as $fn) {
            // if we have added the file to the archive, remove it from the storage directory
            // but keep the /history/tmp file copy for now
            if (is_file($fdir . DS . $fn)) {
                $isrn = rename($fdir . DS . $fn, $rndir . DS . $fn);
            }

            //
            if ($isrn) {
                $fnct++;
            } else {
                csv_edihist_log("edih_archive_delete_old: $ft failed to move $fn");
            }
        }
    } else {
        csv_edihist_log("edih_archive_delete_old: $ft directory error for files or tmp");
    }

    //
    return $fnct;
}


/**
 * create associative array from archive csv file
 *
 * @uses edih_archive_csv_array()
 *
 * @param string
 * @param string
 * @param string    optional filepath
 *
 * @return array
 */
function edih_archive_csv_array($filetype, $csv_type, $filepath = '')
{
    //
    $str_out = '';
    $csv_ar = array();
    $tmpdir = csv_edih_tmpdir();
    $tmpcsv = $tmpdir . DS . 'csv';
    //
    $csvtp = (strpos($csv_type, 'aim')) ? 'claims' : 'files';
    //
    if (is_file($filepath)) {
        $csv_arch_path = $filepath;
    } else {
        $csv_arch_path = $tmpcsv . DS . 'arch_' . $csvtp . '_' . $filetype . '.csv';
    }

    //
    $ct = 0;
    $row = 0;
    $ky = -1;
    // relies on first row being header or column names
    if (($fh = fopen($csv_arch_path, "rb")) !== false) {
        while (($data = fgetcsv($fh, 2048, ",")) !== false) {
            if (is_null($data)) {
                continue;
            }

            if ($row) {
                for ($i = 0; $i < $ct; $i++) {
                    $csv_ar[$ky][$h[$i]] = $data[$i];
                }
            } else {
                $ct = count($data);
                $h = $data;
            }

            $row++;
            $ky++;
        }

        fclose($fh);
    } else {
         // invalid file path
         csv_edihist_log('edih_archive_csv_array; invalid file path ' . $csv_arch_path);
         return false;
    }

    //
    return $csv_ar;
}

/**
 * combine the csv file in the archive with the current csv file
 *
 * @uses edih_archive_csv_array()
 *
 * @param string
 * @param string
 *
 * @return string
 */
function edih_archive_csv_combine($filetype, $csvtype)
{
    //
    $str_out = '';
    $hdr_ar = array();
    $bdir = csv_edih_basedir();
    $tmpdir = csv_edih_tmpdir();
    $tmpcsv = $tmpdir . DS . 'csv';
    //
    $csvtp = (strpos($csvtype, 'aim')) ? 'claims' : 'files';
    $csv_arch_file = $tmpcsv . DS . 'arch_' . $csvtp . '_' . $filetype . '.csv';
    $csv_new_file = $tmpdir . DS . 'cmb_' . $csvtp . '_' . $filetype . '.csv';
    //
    // arrays used to eliminate duplicate rows
    $dup_ar = $dup_unique = $dup_keys = array();
    // combine files by combining arrays and writing a tmp file
    // get the present csv file contents
    $car1 = csv_assoc_array($filetype, $csvtp);
    // get the archived csv contents
    if (is_file($csv_arch_file)) {
        $car2 = edih_archive_csv_array($filetype, $csvtp, $csv_arch_file);
    }

    // possibility of empty arrays if no data rows in a csv file
    $hdrc1 = (is_array($car1) && count($car1)) ? array_keys($car1[0]) : array();
    $hdrc2 = (is_array($car2) && count($car2)) ? array_keys($car2[0]) : array();
    if (count($hdrc1) && ($hdrc1 === $hdrc2)) {
        $hdr_ar = $hdrc1;
    } elseif (empty($hdrc1) && count($hdrc2)) {
        $hdr_ar = $hdrc2;
    } elseif (empty($hdrc2) && count($hdrc1)) {
        $hdr_ar = $hdrc1;
    } else {
        // array mismatch error (impossible?)
        csv_edihist_log("edih_archive_csv_combine: $filetype $csvtp array header mismatch");
        // just use the current csv file
        $hdr_ar = csv_table_header($filetype, $csvtp);
        $car_cmb_unique = $car1;
        // debug
        if (count($hdrc1)) {
            $dbg_str = '';
            foreach ($hdrc1 as $h) {
                $dbg_str .= $h . ' ';
            }

            csv_edihist_log("edih_archive_csv_combine: $csvtp car1 header $dbg_str");
        } else {
            csv_edihist_log("edih_archive_csv_combine: $csvtp car1 header empty");
        }

        if (count($hdrc2)) {
            $dbg_str = '';
            foreach ($hdrc2 as $h) {
                $dbg_str .= $h . ' ';
            }

            csv_edihist_log("edih_archive_csv_combine: $csvtp car2 header $dbg_str");
        } else {
            csv_edihist_log("edih_archive_csv_combine: $csvtp car2 header empty");
        }

        //
    }

    // if the arrays checked out
    if (!isset($car_cmb_unique)) {
        // if we have archive csv rows
        if (is_array($car1) && is_array($car2)) {
            // put the archive rows first
            $car_cmb = array_merge($car2, $car1);
            // now eliminate duplicates
            if ($csvtp == 'files') {
                if ($filetype == 'f835') {
                    $ky = 'Trace';
                } else {
                    $ky = 'Control';
                }

                // array_column() php v5.5
                foreach ($car_cmb as $idx => $row) {
                    $dup_ar[$idx] = $row[$ky];
                }

                csv_edihist_log("edih_archive_csv_combine: $csvtp array row count " . count($dup_ar));
                $dup_unique = array_unique($dup_ar);
                $dup_keys = array_keys($dup_unique);
                csv_edihist_log("edih_archive_csv_combine: $csvtp index row count " . count($dup_keys));
                foreach ($dup_keys as $k) {
                    $car_cmb_unique[] = $car_cmb[$k];
                }

                csv_edihist_log("edih_archive_csv_combine: $csvtp combined row count " . count($car_cmb_unique));
            } elseif ($csvtp == 'claims') {
                $ct = count($hdr_ar);
                $ftxt = $csvtp . ' array' . PHP_EOL;
                foreach ($car_cmb as $idx => $row) {
                    $r_str = '';
                    for ($i = 0; $i < $ct; $i++) {
                        $r_str .= $row[$hdr_ar[$i]];
                    }

                    $dup_ar[$idx] = $r_str;
                    $ftxt .= $r_str . PHP_EOL;
                }

                csv_edihist_log("edih_archive_csv_combine: $csvtp array row count " . count($dup_ar));
                file_put_contents($tmpdir . DS . 'archive' . DS . 'claimstr.txt', $ftxt);
                //
                $dup_unique = array_unique($dup_ar);
                $dup_keys = array_keys($dup_unique);
                csv_edihist_log("edih_archive_csv_combine: $csvtp index row count " . count($dup_keys));
                foreach ($dup_keys as $k) {
                    $car_cmb_unique[] = $car_cmb[$k];
                }

                csv_edihist_log("edih_archive_csv_combine: $csvtp combined row count " . count($car_cmb_unique));
            } else {
                $car_cmb_unique = $car_cmb;
            }
        } else {
            csv_edihist_log("edih_archive_csv_combine: array keys mismatch $filetype");
        }
    } else {
        csv_edihist_log("edih_archive_csv_combine: error reading archived csv " . $csvtp . "_" . $filetype . ".csv");
    }

    $rwct = 0;
    $fh = fopen($csv_new_file, 'wb');
    if ($fh) {
        fputcsv($fh, $hdr_ar);
        $rwct++;
        //
        foreach ($car_cmb_unique as $row) {
            fputcsv($fh, $row);
            $rwct++;
        }

        // close new csv file
        fclose($fh);
    } else {
        csv_edihist_log("edih_archive_csv_combine: failed to open $filetype new csv file");
    }

    return $rwct;
}

/**
 * Unpack an existing archive and restore it to current csv records
 * and replace the files in the respective directories
 *
 * @uses edih_archive_csv_combine
 * @param string
 *
 * @return string
 */
function edih_archive_restore($archive_name)
{
    //
    $str_out = '';
    $bdir = csv_edih_basedir();
    $tmpdir = csv_edih_tmpdir();
    $archdir = $bdir . DS . 'archive';
    //
    if (is_file($archdir . DS . $archive_name)) {
        $arch = realpath($archdir . DS . $archive_name);
        $str_out .= "Archive: restoring " . text($archive_name) . "<br />";
        csv_edihist_log("edih_archive_restore: restoring $archive_name");
    } else {
        $str_out = "Archive: restore archive bad file name " . text($archive_name) . " <br />";
        csv_edihist_log("edih_archive_restore: restore archive bad file name $archive_name");
        return $str_out;
    }

    //
    $zip_obj = new ZipArchive();
    // open archive (ZipArchive::CHECKCONS the ZipArchive::CREATE is supposedly necessary for microsoft)
    //$res = $zip_obj->open($arch, ZipArchive::CHECKCONS);
    if ($zip_obj->open($arch, ZipArchive::CHECKCONS) === true) {
        $f_ct = $zip_obj->numFiles;
        $str_out .= "Extracting " . text($f_ct) . " items from " . text($archive_name) . " <br />";
        csv_edihist_log("edih_archive_restore: Extracting $f_ct items from $archive_name");
        $isOK = $zip_obj->extractTo($tmpdir);
        if (!$isOK) {
            $msg = $zip_obj->getStatusString();
            csv_edihist_log("edih_archive_restore: error extracting archive");
            $str_out .= "Archive: error extracting archive " . text($archive_name) . " <br />";
            $str_out .= "zipArchive: " . text($msg) . " <br />";
            return $str_out;
        }
    } else {
        $msg = $zip_obj->getStatusString();
        csv_edihist_log("edih_archive_restore: error opening archive");
        $str_out .= "Archive: error opening archive <br />" . PHP_EOL;
        $str_out .= "zipArchive: " . text($msg) . " <br />";
        return $str_out;
    }

    // now traverse the tmpdir and replace things
    // we should have tmp/csv/files_[ftype].csv  claims_[ftype].csv
    //                tmp/[ftype]/x12_filenames
    $arch_ar = scandir($tmpdir);
    $tpstr = '';
    foreach ($arch_ar as $fa) {
        if ($fa == '.' || $fa == '..') {
            continue;
        }

        if (is_dir($tmpdir . DS . $fa)) {
            if ($fa == 'csv') {
                continue;
            }

            // if a /history/ftype dir exists
            if (is_dir($bdir . DS . $fa)) {
                $type_ar[] = $fa;
                $tpstr .= "$fa ";
            }
        } else {
            continue;
        }
    }

    //
    csv_edihist_log("edih_archive_restore: types in archive $tpstr");
    $str_out .= "Archive: types in archive " . text($tpstr) . " <br />" . PHP_EOL;
    //
    foreach ($type_ar as $ft) {
        $str_out .= "Archive: now restoring " . text($ft) . "<br />" . PHP_EOL;
        csv_edihist_log("edih_archive_restore: now restoring $ft");
        //
        $frows = edih_archive_csv_combine($ft, 'file');
        csv_edihist_log("edih_archive_restore: files_$ft csv combined rows $frow");
        $crows = edih_archive_csv_combine($ft, 'claim');
        csv_edihist_log("edih_archive_restore: claims_$ft csv combined rows $frow");
        //
        $file_ar = scandir($tmpdir . DS . $ft);
        foreach ($file_ar as $fn) {
            if ($fn == '.' || $fn == '..') {
                continue;
            }

            if (is_file($tmpdir . DS . $ft . DS . $fn)) {
                $rn = rename($tmpdir . DS . $ft . DS . $fn, $bdir . DS . $ft . DS . $fn);
                if (!$rn) {
                    $str_out .= " -- error restoring " . text($ft) . DS . text($fn) . "<br />" . PHP_EOL;
                    csv_edihist_log("edih_archive_restore: error restoring " . $ft . DS . $fn);
                }
            }
        }

        // this will catch the csv files for the particulat type
        $str_out .= "Archive: now replacing csv tables for " . text($ft) . "<br />" . PHP_EOL;
        csv_edihist_log("edih_archive_restore: now replacing csv tables for $ft");
        //
        $rnf = rename($tmpdir . DS . 'cmb_files_' . $ft . '.csv', $bdir . DS . 'csv' . DS . 'files_' . $ft . '.csv');
        $rnc = rename($tmpdir . DS . 'cmb_claims_' . $ft . '.csv', $bdir . DS . 'csv' . DS . 'claims_' . $ft . '.csv');
        $str_out .= ($rnf) ? "" : " -- error restoring files_" . text($ft) . ".csv <br />" . PHP_EOL;
        $str_out .= ($rnc) ? "" : " -- error restoring claims_" . text($ft) . ".csv <br />" . PHP_EOL;
    }

    //
    csv_edihist_log("edih_archive_restore: now removing archive file");
    $str_out .= "Archive:  now removing archive file <br />" . PHP_EOL;
    $rm = unlink($arch);
    if (!$rm) {
        csv_edihist_log("edih_archive_restore: error removing $archdir.DS.$archive_name");
        $str_out .= ($rnf) ? "" : " -- error removing " . text($archdir) . "." . DS . "." . text($archive_name) . PHP_EOL;
    }

    //
    //edih_archive_cleanup($arch_fn, $tp_ar);
    csv_edihist_log("edih_archive_restore: now removing temporary files");
    $str_out .= "Archive:  now removing temporary files <br />" . PHP_EOL;
    //

    $is_clear = csv_clear_tmpdir();
    if ($is_clear) {
        $str_out .= "Archive: temporary files removed. Process complete.<br />" . PHP_EOL;
    } else {
        $str_out .= "Archive: still some files in /history/tmp/. Process complete.<br />" . PHP_EOL;
    }

    //
    return $str_out;
}


/**
 * restores files from the tmp dir if the archive process needs to be aborted
 *
 * @uses csv_edih_basedir()
 * @uses csv_edih_tmpdir()
 * @uses csv_parameters()
 *
 * @return string
 */
function edih_archive_undo()
{
    //
    // archive process creates files in /history/tmp
    //    /tmp/old_files_[type].csv   copy of pre-archive csv record
    //    /tmp/old_claims_[type].csv   copy of pre-archive csv record
    //    /tmp/new_files_[type].csv   csv record of non-archived files
    //    /tmp/new_claims_[type].csv   csv record of non-archived files
    //    /tmp/arch_files_[type].csv   csv record of archived files (to be put in zip file)
    //    /tmp/arch_claims_[type].csv   csv record of archived files (to be put in zip file)
    //    /tmp/[type]/filename_to_be_archived     all the archived files for [type]
    //
    $str_out = '';
    $bdir = csv_edih_basedir();
    $tmpdir = csv_edih_tmpdir();
    $archdir = $bdir . DS . 'archive';
    //
    $params = csv_parameters("ALL");
    $types_ar = array_keys($params);
    //
    csv_edihist_log("edih_archive_undo: restoring prior csv files files");
    foreach ($types_ar as $ft) {
        if (is_file($tmpdir . DS . 'old_files_' . $ft . '.csv')) {
            $rn = rename($tmpdir . DS . 'old_files_' . $ft . '.csv', $bdir . DS . 'csv' . DS . 'files_' . $ft . '.csv');
            if ($rn) {
                csv_edihist_log("edih_archive_undo: restored prior files_$ft ");
            } else {
                csv_edihist_log("edih_archive_undo: restore failed for prior files_$ft ");
            }
        }

        if (is_file($tmpdir . DS . 'old_claims_' . $ft . '.csv')) {
            $rn = rename($tmpdir . DS . 'old_claims_' . $ft . '.csv', $bdir . DS . 'csv' . DS . 'claims_' . $ft . '.csv');
            if ($rn) {
                csv_edihist_log("edih_archive_undo: restored prior claimss_$ft ");
            } else {
                csv_edihist_log("edih_archive_undo: restore failed for prior claims_$ft ");
            }
        }
    }

    $arch_ar = scandir($tmpdir);
    foreach ($arch_ar as $fa) {
        if ($fa == "." && $fa == "..") {
            continue;
        }

        if (is_dir($tmpdir . DS . $fa)) {
            if (in_array($fa, $types_ar)) {
                $fpath = $params[$fa]['directory'];
                if ($dh = opendir($tmpdir . DS . $fa)) {
                    $str_out .= "Archive: undo restoring " . text($fa) . " files<br />" . PHP_EOL;
                    csv_edihist_log("edih_archive_undo: restoring $fa files");
                    while (false !== ($entry = readdir($dh))) {
                        if ($entry != "." && $entry != "..") {
                            if (is_file($fpath . DS . $entry)) {
                                // file was not moved
                            } else {
                                rename($tmpdir . DS . $fa . DS . $entry, $fpath . DS . $entry);
                            }
                        }
                    }

                    closedir($dh);
                }
            }
        }
    }

    return $str_out;
}


/**
 * After the archive is created, the csv record needs to be re-written so the archived
 * files are not in the csv file and hence, not searched for.
 *
 * @uses csv_table_header()
 *
 * @param string $csv_path   the tmp csv file path is expected
 * @param array $row_array   the data rows to be written (an associative array)
 *
 * @return integer           count the rows written
 */
function edih_archive_rewrite_csv($csv_path, $csv_keys, $row_array)
{
    // @param string $csv_path -- the tmp csv file path is expected
    // @param array $heading_ar -- the column heading for the csv file
    // @param array $row_array -- the data rows to be written
    //
    // count characters written -- returned by fputcsv
    $ocwct = 0;
    $rwct = 0;
    //
    if (is_array($row_array)) {
        csv_edihist_log("edih_archive_rewrite_csv: row array count " . count($row_array));
    } else {
        csv_edihist_log("edih_archive_rewrite_csv: row array not array");
    }

    //
    if (is_array($row_array) && is_array($csv_keys)) {
        if (count($csv_keys)) {
            $h_ar = $csv_keys;
        }
    } else {
        csv_edihist_log("edih_archive_rewrite_csv: invalid row array");
        return $rwct;
    }

    //$csv_path should end with /history/tmp/[arch|keep]_[files|claims]_[type].csv
    // with 'w' flag, place the file pointer at the beginning of the file
    // and truncate the file to zero length.
    // If the file does not exist, attempt to create it.
    $fh3 = fopen($csv_path, 'wb');
    if ($fh3) {
        // write the heading row first
        $ocwct += fputcsv($fh3, $h_ar);
        // wrote heading, now add rows
        foreach ($row_array as $row) {
            $ocwct += fputcsv($fh3, $row);
            $rwct++;
        }

        fclose($fh3);
        csv_edihist_log("edih_archive_rewrite_csv: wrote " . count($row_array) . " rows to " . basename($csv_path));
    } else {
        csv_edihist_log("edih_archive_rewrite_csv: failed to open $csv_path");
    }

    return $rwct;
}


/**
 * cleanup archived files after archive created
 *
 * @param string     name of archive file
 * @param array      array of types included in archive
 *
 * @return string
 */
function edih_archive_cleanup($archivename, $types_ar)
{
    //
    $str_out = '';
    //
    if (is_array($types_ar) && count($types_ar)) {
        $tdirs = $types_ar;
    } else {
        csv_edihist_log("edih_archive_cleanup: no types in file types list");
        $str_out = "no types in file types list" . PHP_EOL;
        return $str_out;
    }

    $bdir = csv_edih_basedir();
    $tmpdir = csv_edih_tmpdir();
    $archivedir = $bdir . DS . 'archive';
    $fct = 0;
    // move archive file to archive directory
    csv_edihist_log("edih_archive_cleanup: now clearing temporary files");
    $str_out .= "Archive: now clearing temporary files<br />" . PHP_EOL;
    // delete archived files from edih tmp dir
    foreach ($tdirs as $td) {
        csv_edihist_log("edih_archive_cleanup: cleaning up for $td");
        if (is_dir($tmpdir . DS . $td)) {
            $fn_ar = scandir($tmpdir . DS . $td);
            foreach ($fn_ar as $fn) {
                if ($fn == '.' || $fn == '..') {
                    continue;
                }

                if (is_file($tmpdir . DS . $td . DS . $fn)) {
                    $ul = unlink($tmpdir . DS . $td . DS . $fn);
                    if (!$ul) {
                        csv_edihist_log("edih_archive_cleanup: error removing file $fn");
                        $str_out .= "<p>edih_archive_cleanup: error removing file " . text($td) . DS . text($fn) . "</p>";
                    } else {
                        $fct++;
                    }
                }
            }

            // try to remove the now empty directory
            csv_edihist_log("edih_archive_cleanup: removing tmp $td");
            rmdir($tmpdir . DS . $td);
        }

        csv_edihist_log("edih_archive_cleanup: removed $fct files from $td");
    }

    return $str_out;
}


/**
 * The main function in this edih_archive.php script.  This function gets the parameters array
 * from csv_parameters() and calls the archiving functions on each type of file
 * in the parameters array.
 *
 * @uses edih_archive_date()
 * @uses csv_edih_basedir()
 * @uses csv_parameters()
 * @uses csv_edih_tmpdir()
 * @uses csv_table_header()
 * @uses edih_archive_filenames()
 * @uses edih_archive_rewrite_csv()
 * @uses edih_archive_csv_split()
 * @uses edih_archive_create_zip()
 *
 * @param string        from select drop-down 6m, 12m, 18m, etc
 *
 * @return string       descriptive message in html format
 */
function edih_archive_main($period)
{
    //
    $out_html = '';
    if ($period) {
        $archive_date = edih_archive_date($period);
        if ($archive_date) {
            $archive_dir = csv_edih_basedir() . DS . 'archive';
            $tmp_dir = csv_edih_tmpdir();
            $arch_fn = $archive_date . '_archive.zip';
            $params = csv_parameters();
        } else {
            csv_edihist_log("edih_archive_main: error creating archive date from $period");
            $out_html = "Error creating archive date from " . text($period) . "<br />" . PHP_EOL;
        }
    } else {
        $out_html = "Archive period invalid.<br />" . PHP_EOL;
        return $out_html;
    }

    //
    if (is_dir($archive_dir)) {
        if (is_file($archive_dir . DS . $arch_fn)) {
            csv_edihist_log("edih_archive_main: archive file $arch_fn already exists");
            $out_html = "Archive: archive file " . text($arch_fn) . " already exists<br />" . PHP_EOL;
            return $out_html;
        }
    } else {
        // should have been created at setup
        if (!mkdir($archive_dir, 0755)) {
            csv_edihist_log("edih_archive_main: archive directory does not exist");
            $out_html = "Archive: archive directory does not exist<br />" . PHP_EOL;
            return $out_html;
        }
    }

    //
    foreach ($params as $k => $p) {
        //
        $ft = $p['type'];  // could be $k
        //
        if ($ft == 'f837') {
            csv_edihist_log("edih_archive_main: 837 Claims files are not archived");
            continue;
        }

        $fdir = $p['directory'];
        $scan = scandir($fdir);
        if (!$scan || count($scan) < 3) {
            continue;
        }

        //
        $files_csv = $p['files_csv'];
        $claims_csv = $p['claims_csv'];
        $date_col = $p['filedate'];
        $fncol = 'FileName';
        //
        // create three csv file paths 'old_', 'arch_', and 'keep_'
        // files csv temporary names
        $fn_files_old = $tmp_dir . DS . 'old_' . basename($files_csv);
        $fn_files_arch = $tmp_dir . DS . 'arch_' . basename($files_csv);
        $fn_files_keep = $tmp_dir . DS . 'keep_' . basename($files_csv);
        // claims csv temporary names
        $fn_claims_old = $tmp_dir . DS . 'old_' . basename($claims_csv);
        $fn_claims_arch = $tmp_dir . DS . 'arch_' . basename($claims_csv);
        $fn_claims_keep = $tmp_dir . DS . 'keep_' . basename($claims_csv);
        // table headings
        $fh_ar = csv_table_header($ft, 'file');
        $ch_ar = csv_table_header($ft, 'claim');
        // copy existing csv files -- continue to next type if no files_csv
        $iscpc = $iscpf = false;
        if (is_file($files_csv)) {
            $iscpf = copy($files_csv, $fn_files_old);
            csv_edihist_log("edih_archive_main: copy $ft files csv to tmp dir");
        } else {
            csv_edihist_log("edih_archive_main: $ft files csv does not exist");
            continue;
        }

        if (is_file($claims_csv)) {
            $iscpc = copy($claims_csv, $fn_claims_old);
            csv_edihist_log("edih_archive_main: copy $ft claims csv to tmp dir");
        } else {
            if ($ft == 'f997') {
                // there may be no 997 type claims records, so create a dummy file
                $fh = fopen($fn_claims_old, 'wb');
                if ($fh) {
                    fputcsv($fh, $ch_ar);
                    fclose($fh);
                }
            } else {
                csv_edihist_log("edih_archive_main: $ft claims csv does not exist");
                continue;
            }
        }

        //
        if (!$iscpf || !$iscpc) {
            csv_edihist_log("edih_archive_csv_old: copy to tmp dir failed for csv file $ft");
            $out_html = "Archive temporary files operation failed ... aborting <br />" . PHP_EOL;
            // need to call archive_undo()
            $out_html .= edih_archive_undo();
            return $out_html;
        }

        // get the csv data
        $csv_files_ar = csv_assoc_array($ft, 'file');
        $csv_claims_ar = csv_assoc_array($ft, 'claim');
        // get filenames to be archived
        $fn_ar = array();
        $tp_ar = array();
        $fn_ar = edih_archive_filenames($csv_files_ar, $archive_date);
        if (count($fn_ar)) {
            // add type to list
            $tp_ar[] = $ft;
            // get the old and new csv row arrays for files_csv
            $arch_new = edih_archive_csv_split($csv_files_ar, $fn_ar);
            if ($arch_new) {
                if (isset($arch_new['keep'])) {
                    // write the new
                    $frws = edih_archive_rewrite_csv($fn_files_keep, $fh_ar, $arch_new['keep']);
                    $out_html .= "type " . text($ft) . " keep files_csv file with " . text($frws) . " rows<br />";
                }

                if (isset($arch_new['arch'])) {
                    // write the old
                    $frws2 = edih_archive_rewrite_csv($fn_files_arch, $fh_ar, $arch_new['arch']);
                    $out_html .= "type " . text($ft) . " archive files_csv file with " . text($frws2) . " rows<br />";
                }
            } else {
                $out_html .= "type $ft error creating new files_csv tables";
            }

            // repeat for claims_csv
            $arch_new = edih_archive_csv_split($csv_claims_ar, $fn_ar);
            if ($arch_new) {
                if (isset($arch_new['keep'])) {
                    // write the new
                    $crws = edih_archive_rewrite_csv($fn_claims_keep, $ch_ar, $arch_new['keep']);
                    $out_html .= "type " . text($ft) . " keep claims_csv file with " . text($crws) . " rows<br />";
                }

                if (isset($arch_new['arch'])) {
                    // write the old
                    $crws = edih_archive_rewrite_csv($fn_claims_arch, $ch_ar, $arch_new['arch']);
                    $out_html .= "type " . text($ft) . " archive claims_csv file with " . text($crws) . " rows<br />";
                }
            } else {
                $out_html .= "type " . text($ft) . " error creating new claims csv tables<br />";
            }

            // now the csv_records are in files
            // file records in $fn_files_arch  $fn_files_keep
            // claim records in $fn_claims_arch  $fn_claims_new
            //
            // create a zip archive
            // zf is result of zipArchive functions true or false
            $zf = edih_archive_create_zip($p, $fn_ar, $archive_date, $arch_fn);
            //
            // delete archived files
            if ($zf) {
                // replace the csv files
                $rn = rename($fn_files_keep, $files_csv);
                if ($rn) {
                    csv_edihist_log("edih_archive_main: replaced $files_csv");
                } else {
                    csv_edihist_log("edih_archive_main: error trying to replace $files_csv");
                }

                $rn = rename($fn_claims_keep, $claims_csv);
                if ($rn) {
                    csv_edihist_log("edih_archive_main: replaced $claims_csv");
                } else {
                    csv_edihist_log("edih_archive_main: error trying to replace $claims_csv");
                }

                // move archive files to tmpdir/ftype
                // $rndir = mkdir($tmpdir.DS.$fdir);
                csv_edihist_log("edih_archive_main: $ft now moving old files ");
                $del = edih_archive_move_old($p, $fn_ar);
                $out_html .= "Archive moved " . text($del . " " . $ft) . " type files<br />" . PHP_EOL;
                //
            } else {
                csv_edihist_log("edih_archive_main: type $ft error in creating archive");
                $out_html .= "type " . text($ft) . " error in creating archive<br />" . PHP_EOL;
            }
        } else {
            csv_edihist_log("edih_archive_main: search found no type $ft files older than $period");
            $out_html .= "Archive: type " . text($ft) . " archive found no files older than " . text($period) . "<br />" . PHP_EOL;
        }
    } // end foreach($params as $k=>$p)
    //
    if (is_file($tmp_dir . DS . $arch_fn)) {
        $rn = rename($tmp_dir . DS . $arch_fn, $archive_dir . DS . $arch_fn);
        $cm = chmod($archive_dir . DS . $arch_fn, 0400);
        if ($rn) {
            csv_edihist_log("edih_archive_main: moved $arch_fn to archive directory");
        } else {
            csv_edihist_log("edih_archive_main: error moving archive file $arch_fn");
            $out_html .= "<p>edih_archive_main: error moving archive file " . text($arch_fn) . "</p>";
        }
    } else {
        csv_edihist_log("edih_archive_main: is_file false $tmp_dir.DS.$arch_fn");
    }

    //edih_archive_cleanup($arch_fn, $tp_ar);
    $is_clear = csv_clear_tmpdir();
    if ($is_clear) {
        $out_html .= "Archive: temporary files removed. Process complete.<br />" . PHP_EOL;
    } else {
        $out_html .= "Archive: still some files in /history/tmp/. Process complete.<br />" . PHP_EOL;
    }

    //
    return $out_html;
}
