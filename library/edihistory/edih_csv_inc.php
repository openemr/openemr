<?php

/**
 * edih_csv_inc.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin McCormick Longview, Texas
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2012 Kevin McCormick Longview, Texas
 * @copyright Copyright (c) 2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
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
 * Also, the constant IBR_HISTORY_DIR must be correct
 * **************************
 * </pre>
 *
 * The claim_history x12 files are claim (837) acknowledgement (997/999) claim status (277) and claim payment (835)
 * Also eligibility request (270) and eligibility response (271)
 *
 * <pre>
 * Basic workflow:
 *  Each file type has a row in the array from csv_paramaters()
 *     type  directory files_csv  claims_csv  column  regex
 *
 *  1. open submitted file in edih_x12_class to verify and produce properties
 *  2. Read the parameters array and choose the parameters using 'type'
 *  2. Search the matched type 'directory' for the filename files matching the 'regex' regular expressions and
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
 * TO_DO Some type of "find in files" search would be helpful for locating all references to a claim, patient, etc.
 *    [ grep -nHIrF 'findtext']
 *
 * TO_DO functions to zip old files, put them aside, and remove them from csv tables
 */

/**
 * Constant that is checked in included files to prevent direct access.
 * concept taken from Joomla
 */
define('_EDIH', 1);
//DIRECTORY_SEPARATOR;
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Log messages to the log file
 *
 * @param string $msg_str  the log message
 * @return int             number of characters written
 */
function csv_edihist_log($msg_str)
{
    //
    //$dir = dirname(__FILE__).DS.'log';
    //$dir = $GLOBALS['OE_EDIH_DIR'].DS.'log';
    //$logfile = $GLOBALS['OE_EDIH_DIR'] . "/log/edi_history_log.txt";
    $logfile = 'edih_log_' . date('Y-m-d') . '.txt';
    $dir = csv_edih_basedir() . DS . 'log';
    $rslt = 0;
    if (is_string($msg_str) && strlen($msg_str)) {
        $tm = date('Ymd:Hms') . ' ' . $msg_str . PHP_EOL;
        //
        $rslt = file_put_contents($dir . DS . $logfile, $tm, FILE_APPEND);
    } else {
        //
        $fnctn = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        csv_edihist_log('invalid message string ' . $fnctn);
    }

    //
    return $rslt;  // number of characters written
}

/**
 * read the edi_history_log.txt file into an
 * html formatted ordered list
 *
 * @return string
 */
function csv_log_html($logname = '')
{
    check_file_dir_name($logname);
    $html_str = "<div class='filetext'>" . PHP_EOL . "<ol class='logview'>" . PHP_EOL;
    $fp = csv_edih_basedir() . DS . 'log' . DS . $logname;
    if (is_file($fp)) {
        $fh = fopen($fp, 'r');
        if ($fh !== false) {
            while (($buffer = fgets($fh)) !== false) {
                $html_str .= "<li>" . text($buffer) . "</li>" . PHP_EOL;
            }

            $html_str .= "</ol>" . PHP_EOL . "</div>" . PHP_EOL;
            if (!feof($fh)) {
                $html_str .= "<p>Error in logfile: unexpected file ending</p>" . PHP_EOL;
            }

            fclose($fh);
        } else {
            $html_str = "<p>Error: unable to open log file</p>" . PHP_EOL;
        }
    }

    return $html_str;
}


/**
 * list log files and store old logs in an archive
 *
 * @param bool
 * @return array (json)
 */
function csv_log_manage($list = true)
{
    //
    //$dir = dirname(__FILE__).DS.'log';
    $dir = csv_edih_basedir() . DS . 'log';
    $list_ar = array();
    $old_ar = array();
    $lognames = scandir($dir);
    if ($list) {
        foreach ($lognames as $log) {
            if (!strpos($log, '_log_')) {
                continue;
            }

            $list_ar[] = $log;
        }

        $s = (count($list_ar)) ? rsort($list_ar) : false;
        //
        return json_encode($list_ar);
        //
    } else {
        // list is false, must be archive
        $datetime1 = date_create(date('Y-m-d'));
        //
        foreach ($lognames as $log) {
            if ($log == '.' || $log == '..') {
                continue;
            }

            //
            $pos1 = strrpos($log, '_');
            if ($pos1) {
                $ldate = substr($log, $pos1 + 1, 10);
                $datetime2 = date_create($ldate);
                $interval = date_diff($datetime1, $datetime2);
                //echo '== date difference '.$ldate.' '.$interval->format('%R%a days').PHP_EOL;
                if ($interval->format('%R%a') < -7) {
                    // older log files are put in zip archive
                    if (is_file($dir . DS . $log)) {
                        $old_ar[] = $log;
                    }
                }
            }
        }
    }

    //
    $ok = false;
    $archname = $dir . DS . 'edih-log-archive.zip';
    $filelimit = 200;
    //
    if (count($old_ar)) {
        $zip = new ZipArchive();
        if (is_file($archname)) {
            $ok = $zip->open($archname, ZipArchive::CHECKCONS);
        } else {
            $ok = $zip->open($archname, ZipArchive::CREATE);
        }

        //
        if ($ok) {
            if ($zip->numFiles >= $filelimit) {
                $zip->close();
                $dte = $datetime1->format('Ymd');
                $ok = rename($dir . DS . $archname, $dir . DS . $dte . '_' . $archname);
                csv_edihist_log('csv_log_archive: rename full archive ' . $dte . '_' . $archname);
                if ($ok) {
                    $ok = $zip->open($archname, ZipArchive::CREATE);
                    if (!$ok) {
                        csv_edihist_log('csv_log_archive: cannot create ' . $archname);
                    }
                } else {
                    csv_edihist_log('csv_log_archive: cannot rename ' . $archname);
                }
            }

            //
            if ($ok) {
                foreach ($old_ar as $lg) {
                    if (is_file($dir . DS . $lg)) {
                        $a = $zip->addFile($dir . DS . $lg, $lg);
                        if ($a) {
                            csv_edihist_log('csv_log_archive: add to archive ' . $lg);
                        } else {
                            csv_edihist_log('csv_log_archive: error archiving ' . $lg);
                        }
                    }
                }

                $c = $zip->close();
                if ($c) {
                    foreach ($old_ar as $lg) {
                        $u = unlink($dir . DS . $lg);
                        if ($u) {
                            continue;
                        } else {
                            csv_edihist_log('csv_log_archive: error removing ' . $dir . DS . $lg);
                        }
                    }
                } else {
                    csv_edihist_log('csv_log_archive: error closing log file archive');
                }
            } else {
                csv_edihist_log('csv_log_manage: error failed to open ' . $archname);
            }
        }
    }

    //
    return json_encode($old_ar);
}


/**
 * open or save a user notes file
 *
 * @param string
 * @param bool
 * @return string
 */
function csv_notes_file($content = '', $open = true)
{
    //
    $str_html = '';
    //$fp = $GLOBALS['OE_EDIH_DIR'].'/edi_notes.txt';
    $fp = csv_edih_basedir() . DS . 'archive' . DS . 'edi_notes.txt';
    if (! is_writable($fp)) {
        $fh = fopen($fp, 'a+b');
        fclose($fh);
    }

    // for retrieving notes
    if ($open) {
        // if contents were previously deleted by user and file is empty,
        // the text 'empty' is put in content in save operation
        $ftxt = file_get_contents($fp);
        if ($ftxt === false) {
            $str_html .= 'csv_notes_file: file error <br />' . PHP_EOL;
            csv_edihist_log('csv_notes_file: file error');
        }

        if (substr($ftxt, 0, 5) == 'empty' && strlen($ftxt) == 5) {
            $ftxt = '## ' . date("F j, Y, g:i a");
        } elseif (!$ftxt) {
            $ftxt = '## ' . date("F j, Y, g:i a");
        }

        $str_html .= PHP_EOL . text($ftxt) . PHP_EOL;
    // next stanza for saving content
    } elseif (strlen($content)) {
        //echo "csv_notes_file: we have content<br />".PHP_EOL;
        // use finfo php class
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME);
            $mimeinfo = $finfo->buffer($content);
            if (strncmp($mimeinfo, 'text/plain; charset=us-ascii', 28) !== 0) {
                csv_edihist_log('csv_notes_file: invalid mime-type ' . $mimeinfo);
                $str_html = 'csv_notes_file: invalid mime-type <br />' . text($mimeinfo);
                //
                return $str_html;
            }
        } elseif (preg_match('/[^\x20-\x7E\x0A\x0D]|(<\?)|(<%)|(<asp)|(<ASP)|(#!)|(\$\{)|(<scr)|(<SCR)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            csv_edihist_log('csv_notes_file: Filtered character in file content -- character: ' . $matches[0][0] . ' position: ' . $matches[0][1]);
            $str_html .= 'Filtered character in file content not accepted <br />' . PHP_EOL;
            $str_html .= ' character: ' . text($matches[0][0]) . '  position: ' . text($matches[0][1]) . '<br />' . PHP_EOL;
            //
            return $str_html;
        }
    } else {
        $ftxt = ($content) ? $content : 'empty';
        $saved = file_put_contents($fp, $ftxt);
        $str_html .= ($saved) ? '<p>Save Error with notes file</p>' : '<p>Notes content saved</p>';
    }

    //
    return $str_html;
}

/**
 * generates path to edi history files
 *
 * @return string|bool   directory path
 */
function csv_edih_basedir()
{
    // should be something like /var/www/htdocs/openemr/sites/default
    if (isset($GLOBALS['OE_SITE_DIR'])) {
        // debug
        //echo 'csv_edih_basedir OE_SITE_DIR '.$GLOBALS['OE_SITE_DIR'].'<br />'.PHP_EOL;
        return $GLOBALS['OE_SITE_DIR'] . DS . 'documents' . DS . 'edi' . DS . 'history';
    } else {
        csv_edihist_log('csv_edih_basedir: failed to obtain OpenEMR Site directory');
        return false;
    }
}

/**
 * generates path to edi_history tmp dir for file upload operations
 *
 * @uses csv_edih_basedir()
 * @return string   directory path
 */
function csv_edih_tmpdir()
{
    //
    $bdir = csv_edih_basedir();
    $tdir = ($bdir) ? $bdir . DS . 'tmp' : false;
    //$systmp = sys_get_temp_dir();
    //$systmp = stripcslashes($systmp);
    //$systdir = $systmp.DS.'edihist';
    //if ( $tdir && (is_dir($tdir) || mkdir($tdir, 0755) ) ) {
    if ($tdir) {
        return $tdir;
    } else {
        return false;
    }
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
 * @uses csv_table_header()
 * @uses csv_edih_basedir()
 *
 * @param string &$out_str  referenced, should be created in calling function
 * @return boolean
 */
function csv_setup()
{
    //
    $isOK = false;
    $out_str = '';
    $chr = 0;
    // $GLOBALS['OE_SITE_DIR'] should be like /var/www/htdocs/openemr/sites/default
    $sitedir = $GLOBALS['OE_SITE_DIR'];
    //$sitedir = csv_edih_basedir();
    //
    if (is_readable($sitedir)) {
        $basedir = $sitedir . DS . 'documents' . DS . 'edi';
        $edihist_dir = $basedir . DS . 'history';
        $csv_dir = $edihist_dir . DS . 'csv';
        $archive_dir = $edihist_dir . DS . 'archive';
        $log_dir = $edihist_dir . DS . 'log';
        $tmp_dir = $edihist_dir . DS . 'tmp';
    } else {
       //csv_edihist_log('setup: failed to obtain OpenEMR Site directory');
        echo 'setup: failed to obtain OpenEMR Site directory<br />' . PHP_EOL;
        return false;
    }

    //
    if (is_writable($basedir)) {
        $isOK = true;
        //csv_edihist_log('setup: directory '.$basedir);
        $out_str .= 'EDI_History Setup should not overwrite existing data.<br />' . PHP_EOL;
        $out_str .= 'Setup: directory ' . text($basedir) . '<br />' . PHP_EOL;
        //
        if (is_dir($edihist_dir) || mkdir($edihist_dir, 0755)) {
            $out_str .= 'created folder ' . text($edihist_dir) . '<br />' . PHP_EOL;
            $isOK = true;
            if (is_dir($csv_dir) || mkdir($csv_dir, 0755)) {
                $out_str .= 'created folder ' . text($csv_dir) . '<br />' . PHP_EOL;
                $isOK = true;
            } else {
                $isOK = false;
                $out_str .= 'Setup: Failed to create csv folder... ' . '<br />' . PHP_EOL;
                die('Failed to create csv folder... ' . text($archive_dir));
            }

            if (is_dir($archive_dir) || mkdir($archive_dir, 0755)) {
                $out_str .= 'created folder ' . text($archive_dir) . '<br />' . PHP_EOL;
                $isOK = true;
            } else {
                $isOK = false;
                $out_str .= 'Setup: Failed to create archive folder... ' . '<br />' . PHP_EOL;
                die('Failed to create archive folder... ');
            }

            if (is_dir($log_dir) || mkdir($log_dir, 0755)) {
                $out_str .= 'created folder ' . text($log_dir) . '<br />' . PHP_EOL;
                $isOK = true;
            } else {
                $isOK = false;
                $out_str .= 'Setup: Failed to create log folder... ' . '<br />' . PHP_EOL;
                die('Failed to create log folder... ');
            }

            if (is_dir($tmp_dir) || mkdir($tmp_dir, 0755)) {
                $out_str .= 'created folder ' . text($tmp_dir) . PHP_EOL;
                $isOK = true;
            } else {
                $isOK = false;
                $out_str .= 'Setup: Failed to create tmp folder... ' . '<br />' . PHP_EOL;
                die('Failed to create tmp folder... ');
            }
        } else {
            $isOK = false;
            $out_str .= 'Setup failed: cannot write to folder ' . text($basedir) . '<br />' . PHP_EOL;
            die('Setup failed: cannot write to ' . text($basedir));
        }
    } else {
        $isOK = false;
        $out_str .= 'Setup: Failed to create history folder... ' . '<br />' . PHP_EOL;
        die('Failed to create history folder... ' . text($edihist_dir));
    }

    if ($isOK) {
        $p_ar = csv_parameters('ALL');
        $old_csv = array('f837' => 'batch', 'f835' => 'era');
        foreach ($p_ar as $key => $val) {
            // rename existing csv files to old_filename
            if (is_dir($csv_dir)) {
                if ($dh = opendir($csv_dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if (is_file($csv_dir . DS . $file) && strpos($file, 'csv')) {
                            $rn = rename($csv_dir . DS . $file, $csv_dir . DS . 'old_' . $file);
                            if ($rn) {
                                $out_str .= 'renamed csv/' . text($file) . ' to old_' . text($file) . '<br />' . PHP_EOL;
                            } else {
                                $out_str .= 'attempt to rename csv/' . text($file) . ' failed<br />' . PHP_EOL;
                            }
                        }
                    }
                }
            }

            //;
            // make the edi files storage subdirs
            $tp = $p_ar[$key]['type'];
            $type_dir = $p_ar[$key]['directory'];
            //
            if (is_dir($type_dir)) {
                $out_str .= 'folder for ' . text($tp) . ' exists ' . text($type_dir) . '<br />' . PHP_EOL;
            } elseif (mkdir($type_dir, 0755)) {
                if ($tp == 'f835') {
                    // in upgrade case the f835 directory should not exist
                    // move 'era' files from /era to /f835
                    if (is_dir($edihist_dir . DS . 'era')) {
                        $fct = 0;
                        $rct = 0;
                        if ($dh = opendir($edihist_dir . DS . 'era')) {
                            while (($file = readdir($dh)) !== false) {
                                if (is_file($edihist_dir . DS . 'era' . DS . $file)) {
                                    $rct++;
                                    $rn = rename($edihist_dir . DS . 'era' . DS . $file, $type_dir . DS . $file);
                                    $fct = ($rn) ? $fct + 1 : $fct;
                                }
                            }
                        }

                        $out_str .= 'created type folder ' . text($type_dir) . ' and moved ' . text($fct) . ' of ' . text($rct) . ' files from /era<br />' . PHP_EOL;
                    }
                } else {
                    $out_str .= 'created type folder ' . text($type_dir) . '<br />' . PHP_EOL;
                }
            } else {
                $out_str .= 'Setup failed to create directory for ' . text($tp) . '<br />' . PHP_EOL;
            }
        }
    } else {
        $out_str .= 'Setup failed: Can not create directories <br />' . PHP_EOL;
    }

    if ($isOK) {
        csv_edihist_log($out_str);
        return true;
    } else {
        return $out_str;
    }
}


/**
 * Empty all contents of tmp dir /documents/edi/history/tmp
 *
 * @uses csv_edih_tmpdir()
 * @param  none
 * @return bool
 */
function csv_clear_tmpdir()
{
    //
    $tmpdir = csv_edih_tmpdir();
    if (basename($tmpdir) != 'tmp') {
        csv_edihist_log('tmp dir not /documents/edi/history/tmp');
        return false;
    }

    $tmp_files = scandir($tmpdir);
    if (count($tmp_files) > 2) {
        foreach ($tmp_files as $idx => $tmpf) {
            if ($tmpf == "." || $tmpf == "..") {
                // can't delete . and ..
                continue;
            } elseif (is_file($tmpdir . DS . $tmpf)) {
                unlink($tmpdir . DS . $tmpf);
            } elseif (is_dir($tmpdir . DS . $tmpf)) {
                $tdir_ar = scandir($tmpdir . DS . $tmpf);
                foreach ($tdir_ar as $tfn) {
                    if ($tfn == "." || $tfn == "..") {
                        continue;
                    } elseif (is_file($tmpdir . DS . $tmpf . DS . $tfn)) {
                        unlink($tmpdir . DS . $tmpf . DS . $tfn);
                    }
                }

                rmdir($tmpdir . DS . $tmpf);
            }
        }
    }

    $tmp_files = scandir($tmpdir);
    if (count($tmp_files) > 2) {
        csv_edihist_log('tmp dir contents remain in ... /documents/edi/history/tmp');
        return false;
    } else {
        return true;
    }
}

/**
 * open and verify a default edih_x12_file object
 *
 * @uses csv_check_filepath()
 *
 * @param string   filepath or filename
 * @parm string    file x12 type
 * @return object  edih_x12_file class
 */
function csv_check_x12_obj($filepath, $type = '')
{
    //
    $x12obj = false;
    $ok = false;
    //
    $fp = csv_check_filepath($filepath, $type);
    //
    if ($fp) {
        $x12obj = new edih_x12_file($fp);
        if ('edih_x12_file' == get_class($x12obj)) {
            if ($x12obj->edih_valid() == 'ovigs') {
                $ok = count($x12obj->edih_segments());
                $ok = ($ok) ?  count($x12obj->edih_envelopes()) : false;
                $ok = ($ok) ?  count($x12obj->edih_delimiters()) : false;
                if (!$ok) {
                    csv_edihist_log("csv_check_x12_obj: object missing properties [$filepath]");
                    csv_edihist_log($x12obj->edih_message());
                    return false;
                }
            } else {
                csv_edihist_log("csv_check_x12_obj: invalid object $filepath");
                return false;
            }
        } else {
            csv_edihist_log("csv_check_x12_obj: object not edih_x12_file $filepath");
            return false;
        }
    } else {
        csv_edihist_log("csv_check_x12_obj: invalid file path $filepath");
        return false;
    }

    //
    return $x12obj;
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
function csv_check_filepath($filename, $type = 'ALL')
{
    //
    // if file is readable, just return it
    if (is_file($filename) && is_readable($filename)) {
        return $filename;
    }

    //
    $goodpath = '';
    $fp = '';
    $fn = basename($filename);
    //
    if ($type && $type != 'ALL') {
        $p = csv_parameters($type);
        if (is_array($p) && array_key_exists('type', $p)) {
            $fp = $p['directory'] . DS . $fn;
        }
    } else {
        $p_ar = csv_parameters("ALL");
        foreach ($p_ar as $tp => $par) {
            if (!$p_ar[$tp]['regex'] || !preg_match($p_ar[$tp]['regex'], $fn)) {
                continue;
            } else {
                $fp = $p_ar[$tp]['directory'] . DS . $fn;
                break;
            }
        }
    }

    if (is_file($fp) && is_readable($fp)) {
        $goodpath = realpath($fp);
    }

    //
    return $goodpath;
}

/**
 * verify file type parameter
 *
 * @param string    file type
 * @param bool      return GS02 code or fXXX
 * @return string   file type or empty
 */
function csv_file_type($type, $gs_code = false)
{
    //
    if (!$type) {
        csv_edihist_log('csv_file_type: invalid or missing type argument ' . $type);
        return false;
    } else {
        $tp_type = (string)$type;
    }

    //
    if (strpos('|f837|batch|HC', $tp_type)) {
        $tp = ($gs_code) ? 'HC' : 'f837';
    } elseif (strpos('|f835|era|HP', $tp_type)) {
        $tp = ($gs_code) ? 'HP' : 'f835';
    } elseif (strpos('|f999|f997|ack|ta1|FA', $tp_type)) {
        $tp = ($gs_code) ? 'FA' : 'f997';
    } elseif (strpos('|f277|HN', $tp_type)) {
        $tp = ($gs_code) ? 'HN' : 'f277';
    } elseif (strpos('|f276|HR', $tp_type)) {
        $tp = ($gs_code) ? 'HR' : 'f276';
    } elseif (strpos('|f271|HB', $tp_type)) {
        $tp = ($gs_code) ? 'HB' : 'f271';
    } elseif (strpos('|f270|HS', $tp_type)) {
        $tp = ($gs_code) ? 'HS' : 'f270';
    } elseif (strpos('|f278|HI', $tp_type)) {
        $tp = ($gs_code) ? 'HI' : 'f278';
    } else {
        $tp = '';
    }

    //
    if (!$tp) {
        csv_edihist_log('csv_file_type error: incorrect type ' . $tp_type);
    }

    return $tp;
}


/**
 * The array that holds the various parameters used in dealing with files
 *
 * A key function since it holds the paths, columns, etc.
 * Unfortunately, there is an issue with matching the type in  * the case of the
 * values '997', '277', '999', etc, becasue these strings may be recast
 * from strings to integers, so the 'type' originally supplied is lost.
 * This introduces an inconsistency when the 'type' is used in comparison tests.
 * We call the csv_file_type() function to return a usable file type identifier.
 * The 'datecolumn' and 'fncolumn' entries are used in csv_to_html() to filter by date
 * or place links to files.
 *
 * @param string $type -- default = ALL or one of batch, ibr, ebr, dpr, f997, f277, era, ack, text
 * @return array
 */
function csv_parameters($type = 'ALL')
{
    //
    // This will need the OpenEMR 'oe_site_dir' to replace global
    //
    $p_ar = array();

    $tp = ($type === 'ALL') ? $type : csv_file_type($type);
    if (!$tp) {
        csv_edihist_log('csv_parameters() error: incorrect type ' . $type);
        return $p_ar;
    }

    //$edihist_dir = $GLOBALS['OE_SITE_DIR'].'/documents/edi/history';
    $edihist_dir = csv_edih_basedir();
    //
    // the batch file directory is a special case - decide whether to use OpenEMR batch files or make our own copies
    // OpenEMR copies each batch file to sites/default/documents/edi and this project never writes to that directory
    // batch reg ex -- '/20[01][0-9]-[01][0-9]-[0-3][0-9]-[0-9]{4}-batch*\.txt/' '/\d{4}-\d{2}-\d{2}-batch*\.txt$/'
    //
    $p_ar['f837'] = array('type' => 'f837', 'directory' => $GLOBALS['OE_SITE_DIR'] . DS . 'documents' . DS . 'edi', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f837.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f837.csv', 'filedate' => 'Date', 'claimdate' => 'SvcDate', 'regex' => '/\-batch(.*)\.txt$/');
    //
    //$p_ar['csv'] = array("type"=>'csv', "directory"=>$edihist_dir.'/csv', "claims_csv"=>'ibr_parameters.csv',
    //                  "files_csv"=>'', "column"=>'', "regex"=>'/\.csv$/');
    $p_ar['f997'] = array('type' => 'f997', 'directory' => $edihist_dir . DS . 'f997', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f997.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f997.csv', 'filedate' => 'Date', 'claimdate' => 'RspDate', 'regex' => '/\.(99[79]|ta1|ack)$/i');
    $p_ar['f276'] = array('type' => 'f276', 'directory' => $edihist_dir . DS . 'f276', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f276.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f276.csv', 'filedate' => 'Date', 'claimdate' => 'ReqDate', 'regex' => '/\.276([ei]br)?$/');
    $p_ar['f277'] = array('type' => 'f277', 'directory' => $edihist_dir . DS . 'f277', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f277.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f277.csv', 'filedate' => 'Date', 'claimdate' => 'SvcDate', 'regex' => '/\.277([ei]br)?$/i');
    $p_ar['f270'] = array('type' => 'f270', 'directory' => $edihist_dir . DS . 'f270', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f270.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f270.csv', 'filedate' => 'Date', 'claimdate' => 'ReqDate', 'regex' => '/\.270([ei]br)?$/i');
    $p_ar['f271'] = array('type' => 'f271', 'directory' => $edihist_dir . DS . 'f271', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f271.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f271.csv', 'filedate' => 'Date', 'claimdate' => 'RspDate', 'regex' => '/\.271([ei]br)?$/i');
    $p_ar['f278'] = array('type' => 'f278', 'directory' => $edihist_dir . DS . 'f278', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f278.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f278.csv', 'filedate' => 'Date', 'claimdate' => 'FileDate', 'regex' => '/\.278/');
    // OpenEMR stores era files, but the naming scheme is confusing, so we will just use our own directory for them
    $p_ar['f835'] = array('type' => 'f835', 'directory' => $edihist_dir . DS . 'f835', 'claims_csv' => $edihist_dir . DS . 'csv' . DS . 'claims_f835.csv',
                        'files_csv' => $edihist_dir . DS . 'csv' . DS . 'files_f835.csv', 'filedate' => 'Date', 'claimdate' => 'SvcDate', 'regex' => '/835[0-9]{5}\.835*|\.(era|ERA|835)$/i');
    //
    if (array_key_exists($tp, $p_ar)) {
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
function csv_table_select_list($outtp = 'json')
{
    $optlist = array();
    $labels = array('f835' => 'Payments', 'f837' => 'Claims', 'batch' => 'Claims', 'f277' => 'Status', 'f276' => 'Status Req',
                    'f997' => 'Ack','f271' => 'Benefit', 'f270' => 'Benefit Req', 'f278' => 'Auth');

    $edihist_dir = csv_edih_basedir();  // $GLOBALS['OE_SITE_DIR'].'/documents/edi/history'
    $csvdir = $edihist_dir . DS . 'csv';
    $tbllist = scandir($csvdir);
    $idx = 0;
    foreach ($tbllist as $csvf) {
        if ($csvf == "." || $csvf == "..") {
            continue;
        }

        if (strpos($csvf, 'old') === 0) {
            continue;
        }

        if (filesize($csvdir . DS . $csvf) < 70) {
            continue;
        }

        if (substr($csvf, -1) == '~') {
            continue;
        }

        $finfo = pathinfo($csvdir . DS . $csvf);
        $fn = $finfo['filename'];
        // e.g. files_f997
        $tp = explode('_', $fn);
        //$lblkey = $labels[$tp[1]];
        $optlist[$tp[0]][$tp[1]]['fname'] = $fn;
        $optlist[$tp[0]][$tp[1]]['desc'] = $tp[0] . '-' . $labels[$tp[1]]; //$tp[1] .' '.$tp[0];
        $idx++;
    }

    if ($outtp == 'json') {
        return json_encode($optlist);
    } else {
        return $optlist;
    }
}

/**
 * list existing archive files
 *
 * @param string   default 'json'
 * @return array   json if argument is 'json'
 */
function csv_archive_select_list($outtp = 'json')
{
    //
    $flist = array();
    $archdir = csv_edih_basedir() . DS . 'archive';
    //
    // debug
    csv_edihist_log("csv_archive_select_list: using $archdir");
    //
    $scan = scandir($archdir);
    if (is_array($scan) && count($scan)) {
        foreach ($scan as $s) {
            if ($s == '.' || $s == '..') {
                continue;
            } elseif (strpos($s, 'note')) {
                continue;
            } else {
                $flist[] = $s;
            }
        }
    }

    if ($outtp == 'json') {
        return json_encode($flist);
    } else {
        return $flist;
    }
}

/**
 * List files in the directory for the given type
 *
 * Write an entry in the log if a file is in the directory
 * that does not match the type
 *
 * @uses csv_parameters()
 * @param string $type    a type from our list
 * @return array
 */
function csv_dirfile_list($type)
{
    // return false if location is not appropriate
    $tp = csv_file_type($type);
    if (!$tp) {
        csv_edihist_log("csv_dirfile_list error: incorrect type $type");
        return false;
    }

    $params = csv_parameters($tp);
    if (empty($params) || csv_singlerecord_test($params) == false) {
        csv_edihist_log("csv_dirfile_list() error: incorrect type $type");
        return false;
    }

    $search_dir = $params['directory'];
    $ext_re = $params['regex'];
    $dirfiles = array();
    //
    if (is_dir($search_dir)) {
        if ($dh = opendir($search_dir)) {
            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..' || $file == "process_bills.log") {
                    continue;
                } elseif ($tp == 'f837' && ($file == 'history' || $file == 'README.txt')) {
                    continue;
                } elseif (is_file($search_dir . DS . $file)) {
                    $dirfiles[] = $file;
                } else {
                    if ($tp == 'f837' && $file == 'history') {
                        continue;
                    }

                    csv_edihist_log("csv_dirfile_list $type : not a file $file");
                }
            }
        } else {
            csv_edihist_log("csv_dirfile_list $type : error in scan $search_dir");
        }
    } else {
        csv_edihist_log("csv_dirfile_list $type : not a directory $search_dir");
    }

    //
    return $dirfiles;
} // end function


/**
 * List files that are in the csv record
 *
 * @uses csv_parameters()
 * @uses csv_table_header()
 *
 * @param string $type -- one of our types
 * @return array
 */
function csv_processed_files_list($type)
{
    //
    $tp = csv_file_type($type);
    if (!$tp) {
        csv_edihist_log("csv_processed_files_list: incorrect type $type");
        return false;
    }

    $processed_files = array();
    $param = csv_parameters($tp);
    $hdr_ar = csv_table_header($tp, 'file');
    if (is_array($hdr_ar)) {
        foreach ($hdr_ar as $k => $hd) {
            if ($hd == 'FileName') {
                $csv_col = $k;
                break;
            }
        }
    }

    $csv_col = (isset($csv_col)) ? $csv_col : 1;
    $csv_file = $param['files_csv'];
    //if ($tp == 'dpr') {
        //$csv_file = $param['claims_csv'];
        //$csv_col = '5';
    //} else {
        //$csv_file = $param['files_csv'];
    //}
    //
    //$idx = 0;
    if (is_file($csv_file)) {
        if (($fh1 = fopen($csv_file, "r")) !== false) {
            while (($data = fgetcsv($fh1, 1024, ",")) !== false) {
                $processed_files[] = $data[$csv_col];
                //
                //if ($idx) { $processed_files[] = $data[$csv_col]; }
                // skip the header row
                //$idx++;
            }

            fclose($fh1);
        } else {
            csv_edihist_log("csv_list_processed_files: failed to access $csv_file");
            return false;
        }
    } else {
        // first run - no file exists
        csv_edihist_log("csv_processed_files_list: csv file does not exist " . basename($csv_file));
    }

    // remove the header row, but avoid NULL or false
    $ret_ar = (empty($processed_files)) ? $processed_files : array_slice($processed_files, 1);
    return $ret_ar;
} // end function


/**
 * Give an array of files in the storage directories that are not in the csv record
 *
 * @param string $type -- one of our types
 * @return array
 */
function csv_newfile_list($type)
{
    //
    $ar_new = array();
    $tp = csv_file_type($type);
    if (!$tp) {
        csv_edihist_log('csv_newfile_list: incorrect type ' . $type);
        return false;
    }

    //
    $dir_files = csv_dirfile_list($tp);
    $csv_files = csv_processed_files_list($tp);
    //
    // $dir_files should come first in array_diff()
    if (empty($dir_files)) {
        $ar_new = array();
    } elseif (empty($csv_files) || is_null($csv_files)) {
        $ar_new = $dir_files;
    } else {
        $ar_new = array_diff($dir_files, $csv_files);
    }

    //
    return $ar_new;
}

/**
 * Parse 997 IK3 error segment to identify segment causing rejection
 * The error segment string is specially created in edih_997_csv_data()
 * Simple analysis, but the idea is just to identify the bad segment
 *
 * @param string            error segment from edih_997_csv_data()
 * @param bool              true if only the 1st segmentID is wanted
 * return array|string
 */
function edih_errseg_parse($err_seg, $id = false)
{
    // ['err_seg'] = '|IK3*segID*segpos*loop*errcode*bht03syn|CTX-IK3*transID*segID*segpos*elempos
    //                |IK4*elempos*errcode*elem*|CTX-IK4*segID*segpos*elempos
    //
    // note: multiple IK3 segments are allowed in 997/999 x12
    //
    $ret_ar = array();
    if (!$err_seg || strpos($err_seg, 'IK3') === false) {
        csv_edihist_log('edih_errseg_parse: invalid argument');
        return $ret_ar;
    }

    //'|IK3*segID*segpos*loop*errcode*bht03syn|CTX-IK3*segID*segPos*loopLS*elemPos:compositePos:repPos
    // revised: 123456789004*IK3*segID*segpos[*segID*segpos*segID*segpos]
    $ik = explode('*', $err_seg);
    foreach ($ik as $i => $k) {
        switch ((int)$i) {
            case 0:
                $ret_ar['trace'] = $k;
                break;
            case 1:
                break;  // IK3
            case 2:
                $ret_ar['id'][] = $k;
                break;   // segment ID
            case 3:
                $ret_ar['err'][] = $k;
                break;  // segment position
            case 4:
                $ret_ar['id'][] = $k;
                break;
            case 5:
                $ret_ar['err'][] = $k;
                break;
            case 6:
                $ret_ar['id'][] = $k;
                break;
            case 7:
                $ret_ar['err'][] = $k;
                break;
        }
    }

    //
    return $ret_ar;
}

/**
 * Order the csv data array according to the csv table heading row
 * so the data to be added to csv table rows are correctly ordered
 *  the supplied data should be in an array with thie structure
 *  array['icn'] ['file'][i]['key']  ['claim'][i]['key']  ['type']['type']
 *
 * @uses csv_table_header()
 *
 * @param array   data_ar    data array from edih_XXX_csv_data()
 * @return array|bool        ordered array or false on error
 */
function edih_csv_order($csvdata)
{
    //
    $wrcsv = array();
    $order_ar = array();
    //
    foreach ($csvdata as $icn => $data) {
        // [icn]['type']['file']['claim']
        $ft = $data['type'];
        $wrcsv[$icn]['type'] = $ft;
        //
        foreach ($data as $key => $val) {
            if ($key == 'type') {
                continue;
            }

            $order_ar[$icn][$key] = csv_table_header($ft, $key);
            $ct = count($order_ar[$icn][$key]);
            foreach ($val as $k => $rcrd) {
                //
                foreach ($order_ar[$icn][$key] as $ky => $vl) {
                    $wrcsv[$icn][$key][$k][$ky] = $rcrd[$vl];
                }
            }
        }
    }

    return $wrcsv;
}

/**
 * insert dashes in ten-digit telephone numbers
 *
 * @param string $str_val   the telephone number
 * @return string           the telephone number with dashes
 */
function edih_format_telephone($str_val)
{
    $strtel = (string)$str_val;
    $strtel = preg_replace('/\D/', '', $strtel);
    if (strlen($strtel) != 10) {
        csv_edihist_log('edih_format_telephone: invalid argument: ' . $str_val);
        return $str_val;
    } else {
        $tel = substr($strtel, 0, 3) . "-" . substr($strtel, 3, 3) . "-" . substr($strtel, 6);
    }

    return $tel;
}

/**
 * order MM DD YYYY values and insert slashes in eight-digit dates
 *
 * US MM/DD/YYYY or general YYYY-MM-DD
 *
 * @param string $str_val   the eight-digit date
 * @param string $pref      if 'US' (default) anything else means YYYY-MM-DD
 * @return string           the date with slashes
 */
function edih_format_date($str_val, $pref = "Y-m-d")
{
    $strdt = (string)$str_val;
    $strdt = preg_replace('/\D/', '', $strdt);
    $dt = '';
    if (strlen($strdt) == 6) {
        $tdy = date('Ymd');
        if ($pref == "US") {
            // assume mmddyy
            $strdt = substr($tdy, 0, 2) . substr($strdt, -2) . substr($strdt, 0, 4);
        } else {
            // assume yymmdd
            $strdt = substr($tdy, 0, 2) . $strdt;
        }
    }

    if ($pref == "US") {
        $dt = substr($strdt, 4, 2) . "/" . substr($strdt, 6) . "/" . substr($strdt, 0, 4);
    } else {
        $dt = substr($strdt, 0, 4) . "-" . substr($strdt, 4, 2) . "-" . substr($strdt, 6);
    }

    return $dt;
}

/**
 * format monetary amounts with two digits after the decimal place
 *
 * @todo                    add other formats
 * @param string $str_val   the amount string
 * @return string           the telephone number with dashes
 */
function edih_format_money($str_val)
{
    //
    if ($str_val || $str_val === '0') {
        $mny = sprintf("$%01.2f", $str_val);
    } else {
        $mny = $str_val;
    }

    return $mny;
}

/**
 * format percentage amounts with % sign
 * typical example ".50" from x12 edi segment element
 *
 * @param string $str_val   the amount string
 * @return string           the value as a percentage
 */
function edih_format_percent($str_val)
{
    $val = (float)$str_val;
    if (is_float($val)) {
        $pct = $val * 100 . '%';
    } else {
        $pct = $str_val . '%';
    }

    return $pct;
}

/**
 * HTML string for table thead element
 *
 * @uses csv_table_header()
 * @param string
 * @param string
 * @return string
 */
function csv_thead_html($file_type, $csv_type, $tblhd = null)
{
    //
    if (is_array($tblhd) && count($tblhd)) {
        $hvals = $tblhd;
    } else {
        $hvals = csv_table_header($file_type, $csv_type);
    }

    if (is_array($hvals) && count($hvals)) {
        $str_html = '';
    } else {
        return false;
    }

    $str_html .= "<thead>" . PHP_EOL . "<tr>" . PHP_EOL;
    foreach ($hvals as $val) {
        $str_html .= "<th>" . text($val) . "</th>";
    }

    $str_html .= PHP_EOL . "</tr>" . PHP_EOL . "</thead>" . PHP_EOL;
    //
    return $str_html;
}


/**
 * Give the column headings for the csv files
 *
 * @uses csv_file_type()
 * @param string $file_type     one of our edi types
 * @param string $csv_type      either 'file' or 'claim'
 * @return array
 */
function csv_table_header($file_type, $csv_type)
{
    //
    $ft = csv_file_type($file_type);
    $ct = strpos('|file', $csv_type) ? 'file' : $csv_type;
    $ct = strpos('|claim', $ct) ? 'claim' : $ct;
    //
    $hdr = array();
    if (!$ft || !$ct) {
        csv_edihist_log('csv_table_header error: incorrect file [' . $file_type . ']or csv [' . $csv_type . '] type');
        return $hdr;
    }

    //
    if ($ct === 'file') {
        switch ((string)$ft) {
            //case 'ack': $hdr = array('Date', 'FileName', 'isa13', 'ta1ctrl', 'Code'); break;
            //case 'ebr': $hdr = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch'); break;
            //case 'ibr': $hdr = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch'); break;
            //
            case 'f837':
                $hdr = array('Date', 'FileName', 'Control', 'Claim_ct', 'x12Partner');
                break;
            case 'ta1':
                $hdr = array('Date', 'FileName', 'Control', 'Trace', 'Code');
                break;
            case 'f997':
                $hdr = array('Date', 'FileName', 'Control', 'Trace', 'RspType', 'RejCt');
                break;
            case 'f276':
                $hdr = array('Date', 'FileName', 'Control', 'Claim_ct', 'x12Partner');
                break;
            case 'f277':
                $hdr = array('Date', 'FileName', 'Control', 'Accept', 'AccAmt', 'Reject', 'RejAmt');
                break;
            case 'f270':
                $hdr = array('Date', 'FileName', 'Control', 'Claim_ct', 'x12Partner');
                break;
            case 'f271':
                $hdr = array('Date', 'FileName', 'Control', 'Claim_ct', 'Reject', 'Payer');
                break;
            case 'f278':
                $hdr = array('Date', 'FileName', 'Control', 'TrnCount', 'Auth', 'Payer');
                break;
            case 'f835':
                $hdr = array('Date', 'FileName', 'Control', 'Trace', 'Claim_ct', 'Denied', 'Payer');
                break;
        }
    } elseif ($ct === 'claim') {
        switch ((string)$ft) {
            //case 'ebr': $hdr = array('PtName','SvcDate', 'CLM01', 'Status', 'Batch', 'FileName', 'Payer'); break;
            //case 'ibr': $hdr = array('PtName','SvcDate', 'CLM01', 'Status', 'Batch', 'FileName', 'Payer'); break;
            //case 'dpr': $hdr = array('PtName','SvcDate', 'CLM01', 'Status', 'Batch', 'FileName', 'Payer'); break;
            //
            case 'f837':
                $hdr = array('PtName', 'SvcDate', 'CLM01', 'InsLevel', 'BHT03', 'FileName', 'Fee', 'PtPaid', 'Provider' );
                break;
            case 'f997':
                $hdr = array('PtName', 'RspDate', 'Trace', 'Status', 'Control', 'FileName', 'RspType', 'err_seg');
                break;
            case 'f276':
                $hdr = array('PtName', 'SvcDate', 'CLM01', 'ClaimID', 'BHT03', 'FileName', 'Payer', 'Trace');
                break;
            case 'f277':
                $hdr = array('PtName', 'SvcDate', 'CLM01', 'Status', 'BHT03', 'FileName', 'Payer', 'Trace');
                break;
            case 'f270':
                $hdr = array('PtName', 'ReqDate', 'Trace', 'InsBnft', 'BHT03', 'FileName', 'Payer');
                break;
            case 'f271':
                $hdr = array('PtName', 'RspDate', 'Trace', 'Status', 'BHT03', 'FileName', 'Payer');
                break;
            case 'f278':
                $hdr = array('PtName', 'FileDate', 'Trace', 'Status', 'BHT03', 'FileName', 'Auth', 'Payer');
                break;
            case 'f835':
                $hdr = array('PtName', 'SvcDate', 'CLM01', 'Status', 'Trace', 'FileName', 'ClaimID', 'Pmt', 'PtResp', 'Payer');
                break;
        }
    } else {
        // unexpected error
        csv_edihist_log('edih_csv_table_header() error: failed to match file type [' . $ft . '] or csv type [' . $ct . ']');
        return false;
    }

    if (count($hdr)) {
        return $hdr;
    } else {
        return false;
    }
}

/*
function csv_files_header($file_type, $csv_type) {
    //
    $tp = csv_file_type($type);
    if (!$tp) {
        csv_edihist_log('csv_files_header: incorrect type '.$file_type);
        return false;
    }
    if (!strpos('|file|claim', $csv_type) ) {
        csv_edihist_log('csv_files_header error: incorrect csv type '.$csv_type);
        return false;
    }
    //
    $ft = strpos('|277', $file_type) ? 'f277' : $file_type;
    $ft = strpos('|835', $file_type) ? 'era' : $ft;
    $ft = strpos('|837', $file_type) ? 'batch' : $ft;
    $ft = strpos('|999|997|ack|ta1', $file_type) ? 'f997' : $ft;
    //
    $csv_hd_ar = array();
    // dataTables: | 'date' | 'file_name (link)' | 'file_text (link fmt)' | 'claim_ct' | 'reject_ct' |
    $csv_hd_ar['ack']['file'] = array('Date', 'FileName', 'isa13', 'ta1ctrl', 'Code');
    $csv_hd_ar['ebr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch');
    $csv_hd_ar['ibr']['file'] = array('Date', 'FileName', 'clrhsid', 'claim_ct', 'reject_ct', 'Batch');
    //
    // dataTables: | 'date' | 'file_name (link)' | 'file_text (link fmt)' | 'claim_ct' | 'partner' |
    $csv_hd_ar['batch']['file'] = array('Date', 'FileName', 'Ctn_837', 'claim_ct', 'x12_partner');
    $csv_hd_ar['ta1']['file'] =   array('Date', 'FileName', 'Ctn_ta1', 'ta1ctrl', 'Code');
    $csv_hd_ar['f997']['file'] =  array('Date', 'FileName', 'Ctn_999', 'ta1ctrl', 'RejCt');
    $csv_hd_ar['f277']['file'] =  array('Date', 'FileName', 'Ctn_277', 'Accept', 'AccAmt', 'Reject', 'RejAmt');
    $csv_hd_ar['f270']['file'] =  array('Date', 'FileName', 'Ctn_270', 'claim_ct', 'x12_partner');
    $csv_hd_ar['f271']['file'] =  array('Date', 'FileName', 'Ctn_271', 'claim_ct', 'Denied', 'Payer');
    $csv_hd_ar['era']['file'] =   array('Date', 'FileName', 'Trace', 'claim_ct', 'Denied', 'Payer');
    //
    // dataTables: | 'pt_name' | 'svc_date' | 'clm01 (link clm)' | 'status (mouseover)' | b f t (links to files) | message (mouseover) |
    $csv_hd_ar['ebr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
    $csv_hd_ar['ibr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
    $csv_hd_ar['dpr']['claim'] = array('PtName','SvcDate', 'clm01', 'Status', 'Batch', 'FileName', 'Payer');
    //
    // dataTables: | 'pt_name' | 'svc_date' | 'clm01 (link clm)' | 'status (mouseover)' | 'bht03_837 (link rsp)' | message (mouseover) |
    $csv_hd_ar['batch']['claim'] = array('PtName', 'SvcDate', 'clm01', 'InsLevel', 'Ctn_837', 'File_837', 'Fee', 'PtPaid', 'Provider' );
    $csv_hd_ar['f997']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'ak_num', 'File_997', 'Ctn_837', 'err_seg');
    $csv_hd_ar['f277']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'st_277', 'File_277', 'payer_name', 'claim_id', 'bht03_837');
    $csv_hd_ar['f270']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'InsLevel', 'st_270', 'File_270', 'payer_name', 'bht03_270');
    $csv_hd_ar['f271']['claim'] =  array('PtName', 'SvcDate', 'clm01', 'Status', 'st_271', 'File_271', 'payer_name', 'bht03_270');
    $csv_hd_ar['era']['claim'] =   array('PtName', 'SvcDate', 'clm01', 'Status', 'trace', 'File_835', 'claimID', 'Pmt', 'PtResp', 'Payer');
    //
    return $csv_hd_ar[$ft][$csv_type];
}
*/

/**
 * adapted from http://scratch99.com/web-development/javascript/convert-bytes-to-mb-kb/
 *
 * @param int
 *
 * @return string
 */
function csv_convert_bytes($bytes)
{
    $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB');
    if ($bytes == 0) {
        return 'n/a';
    }

    $i = floor(log($bytes) / log(1024));
    //$i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    if ($i == 0) {
        return $bytes . ' ' . $sizes[$i];
    } else {
        return round($bytes / pow(1024, $i), 1) . ' ' . $sizes[$i];
    }
}

/**
 * Determine whether an array is multidimensional
 *
 * @param array
 * @return bool   false if arrayis multidimensional
 */
function csv_singlerecord_test($array)
{
    // the two versions of count() are compared
    // if the array has a sub-array, count recursive is greater
    if (is_array($array)) {
        $is_sngl = count($array, COUNT_RECURSIVE) == count($array, COUNT_NORMAL);
    } else {
        $is_sngl = false;
    }

    //
    return $is_sngl;
}

/*
 * give first and last index keys for an array
 *
 * @param array
 * @return array
 */
function csv_array_bounds($array)
{
    // get the segment array bounds
    $ret_ar = array();
    if (is_array($array) && count($array)) {
        if (reset($array) !== false) {
            $ret_ar[0] = key($array);
        }

        if (end($array) !== false) {
            $ret_ar[1] = key($array);
        }
    }

    return $ret_ar;
}

/*
 * return a csv file as an associative array
 * the first row is the header or array keys for the row
 * array structure:
 *  array[i]=>array(hdr0=>csvrow[0], hdr1=>csvrow[1], hdr2=>csvrow[2], ...)
 *
 * @param string   file type e.g. f837
 * @param string   csv type claim or file
 * @return array
 */
function csv_assoc_array($file_type, $csv_type)
{
    //
    if (!$file_type || !$csv_type) {
        csv_edihist_log('csv_assoc_array; invalid arguments ft: ' . $file_type . ' csvt: ' . $csv_type);
        return false;
    }

    $csv_ar = array();
    $h = array();
    $fp = '';
    //
    $param = csv_parameters($file_type);
    $fcsv = (strpos($csv_type, 'aim')) ? 'claims_csv' : 'files_csv';
    //
    $fp = (isset($param[$fcsv])) ? $param[$fcsv] : '';
    if (!is_file($fp)) {
        csv_edihist_log('csv_assoc_array; invalid csv file ' . basename($fp));
        return $csv_ar;
    }

    $ct = 0;
    $row = 0;
    $ky = -1;
    if (($fh = fopen($fp, "rb")) !== false) {
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
         csv_edihist_log('csv_assoc_array; invalid file path ' . $fp);
         return false;
    }

    //
    return $csv_ar;
}


/**
 * A multidimensional array will be flattened to a single row.
 *
 * @param array $array array to be flattened
 * @return array
 */
function csv_array_flatten($array)
{
    //
    if (!is_array($array)) {
        return false;
    }

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
 * Write parsed data from edi x12 files to csv file
 *
 * @uses csv_parameters()
 * @usescsv_table_header()
 *
 * @param array    data array from parse functions
 * @return bool    true if no error
 */
function edih_csv_write($csv_data)
{
    //
    if (! (is_array($csv_data) && count($csv_data))) {
        csv_edihist_log('edih_csv_write(): invalid data array');
        return false;
    }

    //
    foreach ($csv_data as $icn => $isa) {
        // should be array[icn] => [file][j][key]  [claim][j][key]  [type]
        $ft = ( isset($isa['type']) ) ? $isa['type'] : '';
        if (!$ft) {
            csv_edihist_log('edih_csv_write(): invalid file type');
            continue;
        }

        //
        $param = csv_parameters($ft);
        $f_hdr = csv_table_header($ft, 'file');
        $c_hdr = csv_table_header($ft, 'claim');
        if (is_array($param)) {
            // if either csv files does not exist, create them both
            // all unlisted files in type directory will be processed on next process round
            if (is_file($param['files_csv']) && (filesize($param['files_csv']) > 20)) {
                csv_edihist_log('edih_csv_write: csv check for files csv ' . $ft);
            } else {
                $nfcsv = $param['files_csv'];
                $fh = fopen($nfcsv, 'wb');
                if ($fh !== false) {
                    fputcsv($fh, $f_hdr);
                    fclose($fh);
                    chmod($nfcsv, 0600);
                }

                csv_edihist_log('edih_csv_write: created files_csv file for ' . $ft);
            }

            if (is_file($param['claims_csv']) && filesize($param['claims_csv'])) {
                csv_edihist_log('edih_csv_write: csv check for claims csv ' . $ft);
            } else {
                $nfcsv = $param['claims_csv'];
                $fh = fopen($nfcsv, 'wb');
                if ($fh !== false) {
                    fputcsv($fh, $c_hdr);
                    fclose($fh);
                    chmod($nfcsv, 0600);
                }

                csv_edihist_log('edih_csv_write: created claims_csv file for ' . $ft);
            }
        } else {
            csv_edihist_log('edih_csv_write: parameters error for type ' . $ft);
            return false;
        }

        //
        foreach ($isa as $key => $data) {
            if ($key == 'type') {
                continue;
            }

            // get the csv file path from parameters
            $fp = ($key == 'file') ? $param['files_csv'] : $param['claims_csv'];
            // get the csv row header
            $order_ar = ($key == 'file') ? $f_hdr : $c_hdr;
            $ct = count($order_ar);
            $chrs = 0;
            $rws = 0;
            //
            $fh = fopen($fp, 'ab');
            if (is_resource($fh)) {
                // to assure proper order of data in each row, the
                // csv row is assembled by matching keys to the header row
                foreach ($data as $ky => $row) {
                    $csvrow = array();
                    for ($i = 0; $i < $ct; $i++) {
                        $csvrow[$i] = $row[$order_ar[$i]];
                    }

                    $chrs += fputcsv($fh, $csvrow);
                    $rws++;
                }
            } else {
                csv_edihist_log('edih_csv_write(): failed to open ' . $fp);
                return false;
            }

            //
            csv_edihist_log('edih_csv_write() wrote ' . $rws . ' rows to ' . basename($fp));
        }
    }

    //
    return $rws;
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
 * ex: csv_search_record('batch', 'claim', array('s_val'=>'0024', 's_col'=>9, 'r_cols'=>array(1, 2, 7)), "1" )
 *
 * @uses csv_parameters()
 * @param string $file_type
 * @param string $csv_type
 * @param array $search_ar
 * @param mixed $expect
 * @return array
 */
function csv_search_record($file_type, $csv_type, $search_ar, $expect = '1')
{
    //
    csv_edihist_log("csv_search_record: " . strval($file_type) . " " . strval($csv_type) . " " . strval($search_ar['s_val']));
    //
    $tp = csv_file_type($file_type);
    if (!$tp) {
        csv_edihist_log("csv_search_record: incorrect type $file_type");
        return false;
    }

    //
    $params = csv_parameters($tp);
    //
    if ($csv_type == 'claim') {
        $fp = $params['claims_csv'];
    } elseif ($csv_type == 'file') {
        $fp = $params['files_csv'];
    } else {
        csv_edihist_log('csv_search_record: incorrect csv type ' . $csv_type);
        return false;
    }

    //
    if (!is_array($search_ar) || array_keys($search_ar) != array('s_val', 's_col', 'r_cols')) {
        csv_edihist_log('csv_search_record: invalid search criteria');
        return false;
    }

    $sv = $search_ar['s_val'];
    $sc = $search_ar['s_col'];
    $rv = (is_array($search_ar['r_cols']) && count($search_ar['r_cols'])) ? $search_ar['r_cols'] : 'all';
    $ret_ar = array();
    $idx = 0;
    if (($fh1 = fopen($fp, "r")) !== false) {
        while (($data = fgetcsv($fh1)) !== false) {
            // check for a match
            if ($data[$sc] == $sv) {
                if ($rv == 'all') {
                    $ret_ar[$idx] = $data;
                } else {
                    // now loop through the 'r_cols' array for data index
                    $dct = count($data);
                    foreach ($rv as $c) {
                        // make sure we don't access a non-existing index
                        if ($c >= $dct) {
                            continue;
                        }

                        //
                        $ret_ar[$idx][] = $data[$c];
                    }
                }

                $idx++;
                if ($expect == '1') {
                    break;
                }
            }
        }

        fclose($fh1);
    } else {
        csv_edihist_log('csv_search_record: failed to open ' . $fp);
        return false;
    }

    if (empty($ret_ar)) {
        return false;
    } else {
        return $ret_ar;
    }
}

/**
 * Search the 'claims' csv table for the patient control and find the associated file name
 *
 * Searchtype
 * In 'claims' csv tables, clm01 is position 2, ISA13 number is pos 4, and filename is pos 5;
 * Since we are interested usually in the filename, ISA13 is irrelevant usually.
 *
 * @uses csv_parameters()
 * @uses csv_pid_enctr_parse()
 * @param string                     patient control-- pid-encounter, encounter, or pid
 * @param string                     filetype -- x12 type or f837, f277, etc
 * @param string                     search type encounter, pid, or clm01
 * @return array|bool                [i] data row array  or empty on error
 */
function csv_file_by_enctr($clm01, $filetype = 'f837')
{
    //
    // return array of [i](pid_encounter, filename), there may be more than one file
    //
    if (!$clm01) {
        return 'invalid encounter data<br />' . PHP_EOL;
    }

    //
    $ret_ar = array();
    $ft = csv_file_type($filetype);
    //
    if (!$ft) {
        csv_edihist_log('csv_file_by_enctr: incorrect file type ' . $filetype);
        return $ret_ar;
    } else {
        $params = csv_parameters($ft);
        //$fp = isset($params['claims_csv']) ? dirname(__FILE__).$params['claims_csv'] : false;
        $fp = isset($params['claims_csv']) ? $params['claims_csv'] : false;
        $h_ar = csv_table_header($ft, 'claim');
        $hct = count($h_ar);
        if (!$fp) {
            csv_edihist_log('csv_file_by_enctr: incorrect file type ' . $filetype);
            return $ret_ar;
        }
    }

    //
    $enct = csv_pid_enctr_parse(strval($clm01));
    $p = (isset($enct['pid'])) ? $enct['pid'] : '';
    $e = (isset($enct['enctr'])) ? $enct['enctr'] : '';
    if ($p && $e) {
        $pe = $p . '-' . $e;
        $srchtype = '';
    } elseif ($e) {
        $srchtype = 'encounter';
    } elseif ($p) {
        $srchtype = 'pid';
    } else {
        csv_edihist_log('csv_file_by_enctr: unable to determine encounter value ' . $clm01);
        return 'unable to determine encounter value ' . text($clm01) . '<br />' . PHP_EOL;
    }

    // OpenEMR creates CLM01 as nnn-nnn in genX12 batch
    //$pm = preg_match('/\D/', $enctr, $match2, PREG_OFFSET_CAPTURE);
    $val = array();
    //array_combine ( array $keys , array $values )
    // in 'claims' csv tables, clm01 is position 2 and filename is position 5
    if (($fh1 = fopen($fp, "r")) !== false) {
        if ($srchtype == 'encounter') {
            while (($data = fgetcsv($fh1, 1024, ",")) !== false) {
                // check for a match
                if (strpos($data[2], $e)) {
                    $te = substr($data[2], strpos($data[2], '-') + 1);
                    if (strcmp($te, $e) === 0) {
                        for ($i = 0; $i < $hct; $i++) {
                            $val[$h_ar[$i]] = $data[$i];
                        }

                        $ret_ar[] = $val;  // array_combine($h_ar, $data);
                    }
                }
            }
        } elseif ($srchtype == 'pid') {
            while (($data = fgetcsv($fh1, 1024, ',')) !== false) {
                if (strpos($data[2], $p) !== false) {
                    $te = (strpos($data[2], '-')) ? substr($data[2], 0, strpos($data[2], '-')) : '';
                    if (strcmp($te, $p) === 0) {
                        for ($i = 0; $i < $hct; $i++) {
                            $val[$h_ar[$i]] = $data[$i];
                        }

                        $ret_ar[] = $val;  // $ret_ar[] = array_combine($h_ar, $data);
                    }
                }
            }
        } else {
            while (($data = fgetcsv($fh1, 1024, ",")) !== false) {
                // check for a match
                if (strcmp($data[2], $pe) === 0) {
                    for ($i = 0; $i < $hct; $i++) {
                        $val[$h_ar[$i]] = $data[$i];
                    }

                    $ret_ar[] = $val;  // $ret_ar[] = array_combine($h_ar, $data);
                }
            }
        }

        fclose($fh1);
    } else {
        csv_edihist_log('csv_file_by_enctr: failed to open csv file ' . basename($fp));
        return false;
    }

    return $ret_ar;
}


/**
 * get the x12 file containing the control_num ISA13
 *
 * @todo the csv for x12 files 999, 277, 835, 837 must have the control number
 *
 * @uses csv_search_record()
 * @param string $control_num   the interchange control number, isa13
 * @return  string              the file name
 */
function csv_file_by_controlnum($type, $control_num)
{
    // get the batch file containing the control_num
    //
    $tp = csv_file_type($type);
    //
    $hdr = csv_table_header($tp, 'file');
    $scol = array_search('Control', $hdr);
    $rcol = array_search('FileName', $hdr);
    //
    // $search_ar should have keys ['s_val']['s_col'] array(['r_cols'][])
    //    like "batch', 'claim, array(9, '0024', array(1, 2, 7))
    //$csv_hd_ar['batch']['file'] = array('time', 'file_name', 'control_num', 'claims', 'x12_partner', 'x12_version');
    //
    $fn = '';
    $ctln = (strlen($control_num) >= 9) ? substr($control_num, 0, 9) : $control_num;
    $search = array('s_val' => $ctln, 's_col' => $scol, 'r_cols' => array($rcol));
    $result = csv_search_record($tp, 'file', $search, "1");
    if (is_array($result) && count($result[0]) == 1) {
        $fn = $result[0][0];
    }

    return $fn;
}


/**
 * Search the csv table to obtain the file name for a given
 * trace value (835 / 997 999 type only)
 *
 * Note: the 997/999 trace is the ISA13 of a batch file
 *
 *
 * @param string     trace value (TRN02, TA101, or BHT03)
 * @param string     from type (default is f835)
 * @param string     to type (default is f835)
 * @return string    file name or empty string
 */
function csv_file_by_trace($trace, $from_type = 'f835', $to_type = 'f837')
{
    // get the file referenced by the trace value
    //
    $ft = ($from_type) ? csv_file_type($from_type) : '';
    $tt = ($to_type) ? csv_file_type($to_type) : '';
    $fn = '';
    $csv_type = '';
    $type = '';
    $search = array();
    //
    csv_edihist_log("csv_file_by_trace: $trace from  $ft to $tt");
    //
    // $search_ar should have keys ['s_val']['s_col'] array(['r_cols'])
    //    like "f837', 'claim, array(9, '0024', array(1, 2, 7))
    //
    if ($ft == 'f835') {
        // trace payment to status or claim
        $search = array('s_val' => $trace, 's_col' => 3, 'r_cols' => 'All');
        $type = $tt;
        $csv_type = 'file';
    } elseif ($ft == 'f997') {
        // trace ACK to batch file
        $icn = (is_numeric($trace) && strlen($trace) >= 9) ? substr($trace, 0, 9) : $trace;
        $search = array('s_val' => $icn, 's_col' => 2, 'r_cols' => 'All');
        $type = $tt;
        $csv_type = 'file';
    } elseif ($ft == 'f277') {
        // trace status to status req or claim
        if ($tt == 'f276') {
            $search = array('s_val' => $trace, 's_col' => 7, 'r_cols' => 'All');
            $type = $tt;
            $csv_type = 'claim';
        } elseif ($tt == 'f837') {
            // expect CLM01 for trace value
            $search = array('s_val' => $trace, 's_col' => 2, 'r_cols' => 'All');
            $type = $tt;
            $csv_type = 'claim';
        }
    } elseif ($ft == 'f271') {
        // trace benefit to benefit req
        if ($tt == 'f270') {
            $search = array('s_val' => $trace, 's_col' => 2, 'r_cols' => 'All');
            $type = $tt;
            $csv_type = 'claim';
        }
    } elseif ($ft == 'f278') {
        // trace auth to auth req
        $search = array('s_val' => $trace, 's_col' => 2, 'r_cols' => 'All');
        $type = 'f278';
        $csv_type = 'claim';
    } else {
        csv_edihist_log('csv_file_by_trace: incorrect file type ' . $file_type);
        return $fn;
    }

    //
    if ($type && $csv_type && $search) {
        $result = csv_search_record($type, $csv_type, $search, false);
        if (is_array($result) && count($result)) {
            if ($ft == 'f278') {
                foreach ($result as $r) {
                    if ($r[6] == 'Rsp' || $r[6] == 'Reply') {
                        $fn = $result[0][5];
                        break;
                    }
                }
            } elseif ($csv_type == 'claim') {
                $fn = $result[0][5];
            } else {
                $fn = $result[0][1];
            }
        } else {
            csv_edihist_log("csv_file_by_trace: search failed $type csv $csv_type for trace $trace $from_type $to_type");
        }
    } else {
        csv_edihist_log("csv_file_by_trace: error type $type csv $csv_type for trace $trace $from_type $to_type");
    }

    return $fn;
}

/**
 * list claim records with Denied or Reject status in  given file
 *
 * @param string
 * @param string
 *
 * @return array
 */
function csv_denied_by_file($filetype, $filename, $trace = '')
{
    //
    $ret_ar = array();
    $ft = csv_file_type($filetype);
    if (strpos('|f997|f271|f277|f835', $ft)) {
        $param = csv_parameters($ft);
        $csv_file = $param['claims_csv'];
    } else {
        csv_edihist_log("csv_errors_by_file: incorrect file type $filetype");
        return $ret_ar;
    }

    //
    csv_edihist_log("csv_errors_by_file: $ft searching $filename with trace $trace");
    //
    if (($fh1 = fopen($csv_file, "r")) !== false) {
        if ($ft == 'f835') {
            while (($data = fgetcsv($fh1, 1024, ",")) !== false) {
                // check filename, then status
                if ($trace) {
                    if ($data[4] == $trace) {
                        if (!in_array($data[3], array('1', '2', '3', '19', '20', '21'))) {
                            $ret_ar[] = $data;
                        }
                    }
                } elseif ($data[5] == $filename) {
                    if (!in_array($data[3], array('1', '2', '3', '19', '20', '21'))) {
                        $ret_ar[] = $data;
                    }
                }
            }
        } elseif ($ft == 'f277') {
            while (($data = fgetcsv($fh1, 1024, ",")) !== false) {
                if ($data[5] == $filename) {
                    if (!strpos('|A1|A2|A5', substr($data[3], 0, 2))) {
                        $ret_ar[] = $data;
                    }
                }
            }
        } elseif (strpos('|f997|f999|f271', $ft)) {
            while (($data = fgetcsv($fh1, 1024, ",")) !== false) {
                if ($data[5] == $filename) {
                    if ($data[3] !== 'A') {
                        $ret_ar[] = $data;
                    }
                }
            }
        } else {
            csv_edihist_log("csv_errors_by_file: file type did not match $filetype");
        }

        fclose($fh1);
    }

    //
    return $ret_ar;
}


/**
* A function to try and assure the pid-encounter is correctly parsed
*
* assume a format of pid-encounter, since that is sent in the OpenEMR x12 837
*
* @param string $pid_enctr   the value from element CLM01
* return array               array('pid' => $pid, 'enctr' => $enc)
*/
function csv_pid_enctr_parse($pid_enctr)
{
    // evaluate the patient account field
    //
    if (!$pid_enctr || !is_string($pid_enctr)) {
        csv_edihist_log("csv_pid_enctr_parse: invalid argument");
        return false;
    }

    $pval = trim($pid_enctr);
    if (strpos($pval, '-')) {
        $pid = substr($pval, 0, strpos($pval, '-'));
        $enc = substr($pval, strpos($pval, '-') + 1);
    } elseif (ctype_digit($pval)) {
        if (preg_match('/(19|20)\d{2}[01]\d{1}[0-3]\d{1}/', $pval)) {
            $enc = $pval;
        } else {
            $enc = ( strlen($pval) ) >= ENCOUNTER_MIN_DIGIT_LENGTH ? $pval : '';
            $pid = '';
        }
    } elseif (preg_match('/\D/', $pval, $match2, PREG_OFFSET_CAPTURE)) {
        $inv_split = (count($match2)) ? preg_split('/\D/', $pval, 2, PREG_SPLIT_NO_EMPTY) : false;
        if ($inv_split) {
            $pid = $inv_split[0];
            $enc = $inv_split[1];
        }
    } else {
        $enc = ( strlen($pval) ) >= ENCOUNTER_MIN_DIGIT_LENGTH ? $pval : '';
        $pid = '';
    }

    return array('pid' => $pid, 'enctr' => $enc);
}
