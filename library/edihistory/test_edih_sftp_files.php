<?php

/* ********** flow pattern for using sftp with edi_history
 *
 * 1.  database query for "x12 Partners"
 * 		-- get the partner name
 * 		-- populate a "select" list with names
 *		-- "submit" runs script which gets url, password, etc and the sftp
 * 			transfer for selected x12 partner
 * 		-- put downloaded files in a designated directory e.g. history/sftp/
 *
 *  2 (alt) create php script to use most of edih_uploads.php function edih_upload_files()
 * 		-- but skip the $_FILES array rewrite and testing, just test, match type and store
 * 		-- maybe call it edih_sftp_upload()
 *
 *  3.  add if stanza to edih_main.php in section:
 *  		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
 * 		-- calls edih_io.php function edih_disp_sftp_upload()
 * 		-- remove the files from the download directory
 * 		(alt) -- this is where you would call maybe edih_sftp_upload()
 *
 *  4.  At this point the sftp files will be in the edi/history/[type] directories
 *  	and the "Process" button will run the script to parse the files and create csv table rows
 *
 */

// comment out below exit when need to use this script
exit;

/* ** add this function to edih_uploads.php
 * -- or work it into edih_upload_files(), since it is almost a direct copy
 *
 */
function edih_upload_sftp()
{
    //
    $html_str = '';
    //
    $sftp_dir = 'path/to/dir';
    // if ($_FILES) ) {
        //csv_edihist_log('Error: upload files indicated, but none received.');
        //return false;
    //} elseif
    if (is_dir($sftp_dir) && is_readable($sftp_dir)) {
        $sftp_dir = realpath($sftp_dir);
        $fn_ar = scandir($sftp_dir);
    } else {
        $html_str = 'unable to read the directory for sftp file uploads <br />';
        csv_edihist_log('unable to read the directory for sftp file uploads');
        return $html_str;
    }

    //
    $m_types = array('application/octet-stream', 'text/plain', 'application/zip', 'application/x-zip-compressed');
    //
    // some unwanted file extensions that might be accidentally included in upload files
    $ext_types = 'sh|asp|html|htm|cm|js|xml|jpg|png|tif|xpm|pdf|php|py|pl|tcl|doc|pub|ppt|xls|xla|vsd|rtf|odt|ods|odp';
    // we get the parameters here to send to ibr_upload_match_file()
    $param_ar = csv_parameters();
    //if ( class_exists('finfo') )
    foreach ($fn_ar as $idx => $fn) {
        $fa = array();
        $fa['tmp_name'] = tempnam($sftp_dir . DS . $fn, 'x12_');
        $fa['name'] = $sftp_dir . DS . $fn;
        $fa['type'] = mime_content_type($sftp_dir . DS . $fn);
        $fa['size'] = filesize($sftp_dir . DS . $fn);
        // now do verifications
        if (!in_array($fa['type'], $m_types)) {
            //$html_str .= "Error: mime-type {$fa['type']} not accepted for {$fa['name']} <br />" . PHP_EOL;
            $f_ar['reject'][] = array('name' => $fa['name'],'comment' => 'mime-type ' . $fa['type']);
            csv_edihist_log('edih_upload_sftp: error mime-type ' . $fa['name'] . ' mime-type ' . $fa['type']);
            unset($fn_ar[$idx]);
            continue;
        }

        // verify that we have a usable name
        $fext = (strpos($fa['name'], '.')) ? pathinfo($fa['name'], PATHINFO_EXTENSION) : '';
        if ($fext && preg_match('/' . $ext_types . '\?/i', $fext)) {
            //$html_str .= 'Error: uploaded_file error for '.$fa['name'].' extension '.$fext.'<br />'. PHP_EOL;
            $f_ar['reject'][] = array('name' => $fa['name'],'comment' => 'extension ' . $fext);
            csv_edihist_log('edih_upload_sftp: _FILES error name ' . $fa['name'] . ' extension ' . $fext);
            unset($fn_ar[$idx]);
            continue;
        }

        if (is_string($fa['name'])) {
            // check for null byte in file name, linux hidden file, directory
            if (strpos($fa['name'], '.') === 0 || strpos($fa['name'], "\0") !== false || strpos($fa['name'], "./") !== false) {
                //$html_str .= "Error: uploaded_file error for " . $fa['name'] . "<br />". PHP_EOL;
                $fname = preg_replace("/[^a-zA-Z0-9_.-]/", "_", $fa['name']);
                $f_ar['reject'][] = array('name' => $fname,'comment' => 'null byte, hidden, invalid');
                csv_edihist_log('edih_upload_sftp: null byte, hidden, invalid ' . $fname);
                unset($fn_ar[$idx]);
                continue;
            }

            // replace spaces in file names -- should not happen, but response files from payers might have spaces
            // $fname = preg_replace("/[^a-zA-Z0-9_.-]/","_",$fname);
            $fa['name'] = str_replace(' ', '_', $fa['name']);
        } else {
            // name is not a string
            //$html_str .= "Error: uploaded_file error for " . $fa['tmp_name'] . "<br />". PHP_EOL;
            $f_ar['reject'][] = array('name' => (string)$fa['name'],'comment' => 'invalid name');
            unset($fn_ar[$idx]);
            continue;
        }

        if (!$fa['tmp_name'] || !$fa['size']) {
            //$html_str .= "Error: file name or size error <br />" . PHP_EOL;
            $f_ar['reject'][] = array('name' => (string)$fa['name'],'comment' => 'php file upload error');
            unset($files[$uplkey][$idx]);
            continue;
        }

        //
        if (strpos(strtolower($fa['name']), '.zip') || strpos($fa['type'], 'zip')) {
            //
            $f_upl = edih_ziptoarray($fa['tmp_name'], $param_ar, false);
            //
            // put them in the correct type array
            if (is_array($f_upl) && count($f_upl)) {
                foreach ($f_upl as $tp => $fz) {
                    if ($tp == 'reject') {
                        if (isset($f_ar['reject']) && is_array($fz)) {
                            array_merge($f_ar['reject'], $fz);
                        } else {
                            $f_ar['reject'] = (is_array($fz)) ? $fz : array();
                        }
                    } else {
                        // expect $fz to be an array of file names
                        foreach ($fz as $zf) {
                            $f_ar[$tp][] = $zf;
                            $p_ct++;
                        }
                    }
                }
            } else {
                // nothing good from edih_ziptoarray()
                // $html_str .= "error with zip file or no files accepted for " . $fa['name'] . "<br />" .PHP_EOL;
                $f_ar['reject'][] = array('name' => $fa['name'],'comment' => 'error with zip archive');
                unset($files[$uplkey][$idx]);
            }

            // continue, since we have done everything that would happen below
            continue;
        }

        //
        $f_upl = edih_upload_match_file($param_ar, $fa);
        //
        if (is_array($f_upl) && count($f_upl) > 0) {
            $f_ar[$f_upl['type']][] = $f_upl['name'];
            $p_ct++;
        } else {
            // verification failed
            csv_edihist_log('edih_upload_file: verification failed for ' . $fa['name']);
            $f_ar['reject'][] = array('name' => $fa['name'], 'comment' => 'verification failed');
            unset($fn_ar[$idx]);
        }
    } // end foreach($files[$uplkey] as $idx=>$fa)
    //
    $f_ar['remark'][] = "Received $f_ct files, accepted $p_ct" . PHP_EOL;
    return $f_ar;
}

/* ** add this function to edih_io.php */
function edih_disp_sftp_upload()
{
    // sftp file upload
    // imaginary form and POST values
    $str_html = '';

    if (isset($_POST['post_sftp'])) {
        $la = (isset($_POST['post_sftp'])) ? filter_input(INPUT_POST, 'post_sftp', FILTER_DEFAULT) : '';
        $x12ptnr = (isset($_POST['sftp_select'])) ? filter_input(INPUT_POST, 'sftp_select', FILTER_DEFAULT) : '';
        //
        if (($la == 'get_sftp') && $x12ptnr) {
            // yet to be written -- gets x12 partner info and does sftp download
            $is_sftp = edih_sftp_connect($x12ptnr);
            //
            $f_array = ($is_sftp) ? edih_upload_sftp() : false;
            if (is_array($f_array) && count($f_array)) {
                $str_html .= edih_sort_upload($f_array);
            } else {
                $str_html .= "sftp connection did not get any files <br />" . PHP_EOL;
            }
        } else {
            $str_html .= "sftp file transfer invalid input <br />" . PHP_EOL;
        }

        //
        return $str_html;
    }
}
/* ** edih_view.php needs a form to give user control over sftp uploads */
// a select list of x12 partners and submit button and process button
// append output of edih_disp_sftp_upload() to displayed file list

/**
 * Unattended SFTP host operations using phpseclib.
 *
 * Copyright (C) 2014 MD Support <mdsupport@users.sourceforge.net>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    MD Support <mdsupport@users.sourceforge.net>
 *
 * Following parameters may be provided :
 * 1. actn - get or put
 * 2. Interface with Procedure providers : ppid
 * 3. Single host definition : host, port, user, pass, fdir, pdir
 * 4. ldir - local directory
 * 5. sub - 'ppid'/'host'/'fdir'/'pdir' to be used as subdir of ldir
 **/


if (php_sapi_name() == 'cli') {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
    $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
    $_SERVER['SERVER_NAME'] = 'localhost';
    $backpic = "";
    $ignoreAuth = 1;
    // Since from command line, set $sessionAllowWrite since need to set site_id session and no benefit to set to false
    $sessionAllowWrite = true;
}

$get_count = extract($_GET, EXTR_OVERWRITE);
// Following breaks link to OpenEMR structure dependency - assumes phpseclib is subdir
$script_dir = dirname(__FILE__);
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "$script_dir/phpseclib");
require_once("$script_dir/phpseclib/Net/SFTP.php");
function get_openemr_globals($libdir)
{
    if (!isset($site)) {
        $_GET['site'] = 'default';
    }

    require_once("$libdir/../interface/globals.php");
}
function sftp_status($msg, $val)
{
    if (php_sapi_name() == 'cli') {
        fwrite(STDOUT, xl($msg) . ' ' . $val . PHP_EOL);
    }
}
$exitmsgs = array (
     0 => ''
    ,1 => 'Missing/Invalid parameter(s) - SFTP host definition, actn, ldir'
    ,2 => 'File Transfer error(s)'
);
// OpenEMR specific mappings.  $sub works with procedure_providers fields
$submap = array(
     'ppid' => 'ppid'
    ,'host' => 'remote_host'
    ,'fdir' => 'results_path'
    ,'pdir' => 'orders_path'
);
$pathmap = array(
     'get' => 'results_path'
    ,'put' => 'orders_path'
);
$exitcd = 0;
// Perform parameter-driven actions
if (isset($ppid)) {
    if (!isset($srcdir)) {
        get_openemr_globals($script_dir);
    }

    $rsql = "SELECT * FROM procedure_providers WHERE protocol=? ";
    $rprm = array('SFTP');
    if ($ppid != "*") {
        $rsql .= " AND ppid=?";
        $rprm[] = $ppid;
    }

    $rs = sqlStatement($rsql, $rprm);
    while ($rsrec = sqlFetchArray($rs)) {
        $sftp_hosts[] = $rsrec;
    }
} else { // fill in host detais from parameters
    if (isset($fhost) && isset($user) && (isset($fdir) || isset($pdir))) {
        $sftp_hosts[] = array (
         'remote_host'  => $host
        ,'port'         => $port
        ,'login'        => $user
        ,'password'     => $pass
        ,'results_path' => $fdir
        ,'orders_path'  => $pdir
        );
    }
}

if (
    (!isset($sftp_hosts)) || (!isset($ldir)) ||
    (((!isset($actn)) || (!(in_array($actn, array_keys($pathmap)))))) ||
    (((isset($sub)) && (!(in_array($sub, array_keys($submap))))))
) {
    $exitcd = 1;
}

if (!$exitcd) {
    foreach ($sftp_hosts as $sftp_host) {
        $wrk =  explode(':', $sftp_host['remote_host']);
        $sftp_host['remote_host'] = $wrk[0];
        if (!isset($sftp_host['port'])) {
            $sftp_host['port'] = (isset($wrk[1]) ? $wrk[1] : '22');
        }

        $cn = new \phpseclib3\Net\SFTP($sftp_host['remote_host'], $sftp_host['port']);
        if (!$cn->login($sftp_host['login'], $sftp_host['password'])) {
            sftp_status('Login error', $sftp_host['remote_host'] . ':' . $sftp_host['port']);
        } else {
            $sdir = '';
            if ((isset($sub)) && (isset($sftp_host[$submap[$sub]]))) {
                $sdir = '/' . $sftp_host[$submap[$sub]];
            }

            // Get the list of files.  TBD: Overwrite protection.
            if ($actn == 'get') {
                $dir_from = $sftp_host[$pathmap[$actn]];
                $dir_to = ($ldir . $sdir);
                $full_list = $cn->rawlist($dir_from);
                foreach ($full_list as $file_name => $file_rec) {
                    if ($file_rec['type'] == NET_SFTP_TYPE_REGULAR) {
                        $dir_list[] = $file_name;
                    }
                }
            } else {
                $dir_to = $sftp_host[$pathmap[$actn]];
                $dir_from = ($ldir . $sdir);
                $full_list = new DirectoryIterator($dir_from);
                foreach ($full_list as $fileinfo) {
                    if ($fileinfo->isFile()) {
                        $dir_list[] = $fileinfo->getFilename();
                    }
                }
            }

                // Transfer each file
            if (isset($dir_list)) {
                foreach ($dir_list as $dir_file) {
                    // Skip directories
                    // mdsupport - now $dir_list should have only valid file names
                    // if (($dir_file == '.') || ($dir_file == '..')) {}
                    // else {
                    if ($actn == 'get') {
                        $sftp_ok = $cn->get(($dir_from . '/' . $dir_file), ($dir_to . '/' . $dir_file));
                        if ($sftp_ok) {
                            $sftp_del = $cn->delete(($dir_from . '/' . $dir_file));
                        }
                    } else {
                        $sftp_ok = $cn->put(($dir_to . '/' . $dir_file), ($dir_from . '/' . $dir_file), NET_SFTP_LOCAL_FILE);
                        if ($sftp_ok) {
                            $sftp_del = unlink(($dir_from . '/' . $dir_file));
                        }
                    }

                                sftp_status('File transfer ' . ($sftp_ok ? 'ok' : 'error'), ($dir_from . '/' . $dir_file));
                    if (isset($sftp_del) && (!$sftp_del)) {
                        sftp_status('File deletion error', $dir_file);
                    }

                    if ((!$sftp_ok) || (isset($sftp_del) && (!$sftp_del))) {
                        $exitcd = 2;
                    }

                                // }
                }
            }
        }

            sftp_status('Host action complete', " : $actn files from " . $sftp_host['remote_host'] . ':' . $sftp_host['port'] . " $dir_to");
    }
}

if (php_sapi_name() == 'cli') {
    fwrite(($exitcd ? STDERR : STDOUT), xl($exitmsgs[$exitcd]) . PHP_EOL);
    exit($exitcd);
}
