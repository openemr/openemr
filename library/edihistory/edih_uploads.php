<?PHP

/**
 * edih_uploads.php
 *
 * @package    OpenEMR
 * @subpackage ediHistory
 * @link       https://www.open-emr.org
 * @author     Kevin McCormick
 * @copyright  Copyright (c) 2017 Kevin McCormick
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


/**
 * Rearrange the multi-file upload array
 *
 * @param array $_files -- the $_FILES array
 * @param bool $top -- do not change, for internal use in function recursion
 * @return array
 */
function edih_upload_reindex(array $_files, $top = true)
{
    // blatantly copied from BigShark666 at gmail dot com 22-Nov-2011 06:51
    // from php documentation for $_FILES predefined variable

     $files = array();
    foreach ($_files as $name => $file) {
        if ($top) {
            $sub_name = $file['name'];
        } else {
            $sub_name = $name;
        }

        if (is_array($sub_name)) {
            foreach (array_keys($sub_name) as $key) {
                $files[$name][$key] = array(
                    'name'     => $file['name'][$key],
                    'type'     => $file['type'][$key],
                    'tmp_name' => $file['tmp_name'][$key],
                    'error'    => $file['error'][$key],
                    'size'     => $file['size'][$key],
                );
                $files[$name] = edih_upload_reindex($files[$name], false);
            }
        } else {
            $files[$name] = $file;
        }
    }

     return $files;
}

/**
 * select error message in case of $_FILES error
 *
 * @param int
 * @return string
 */
function edih_upload_err_message($code)
{
    //
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
            break;
        case UPLOAD_ERR_PARTIAL:
            $message = "The uploaded file was only partially uploaded";
            break;
        case UPLOAD_ERR_NO_FILE:
            $message = "No file was uploaded";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $message = "Missing a temporary folder";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $message = "Failed to write file to disk";
            break;
        case UPLOAD_ERR_EXTENSION:
            $message = "File upload stopped by extension";
            break;

        default:
            $message = "Unknown upload error";
            break;
    }

    return $message;
}

/**
 * Categorize and check uploaded files
 *
 * Files are typed and scanned, if they pass scan, then the file is moved
 * to the edi_history temp dir and an array ['type'] ['name'] is returned
 * If an error occurs, false is returned
 *
 * @uses edih_x12_file()
 * @uses csv_file_type()
 * @uses csv_edih_tmpdir()
 * @param array $param_ar -- the csv_parameters("ALL") array (so we don't have to keep creating it)
 * @param array $fidx -- individual file array from $files[$i] array
 *
 * @return array|bool
 */
function edih_upload_match_file($param_ar, $fidx)
{
    //
    // =============
    $edih_upldir = csv_edih_tmpdir();
    // =============
    $ar_fn = array();
    $ftype = '';
    //
    if (is_array($fidx) && isset($fidx['name'])) {
        $fn = basename($fidx['name']);
        $ftmp = $fidx['tmp_name'];
    } else {
        csv_edihist_log('edih_upload_match_file: Error: invalid file argument');
        return false;
    }

    //csv_check_x12_obj($filepath, $type='') {
    $x12obj = new edih_x12_file($ftmp, false);
    //
    if ($x12obj->edih_hasGS()) {
        $ftype = csv_file_type($x12obj->edih_type());
    } elseif ($x12obj->edih_valid()) {
        if (is_array($param_ar) && count($param_ar)) {
            // csv_parameters("ALL");
            foreach ($param_ar as $ky => $par) {
                if (!isset($param_ar[$ky]['regex'])) {
                    continue;
                }

                if (preg_match($param_ar[$ky]['regex'], $fn)) {
                    $ftype = $ky;
                    break;
                }
            }
        } else {
            csv_edihist_log('edih_upload_match_file: invalid parameters');
            return false;
        }
    } else {
        // failed valdity test: unwanted characters or unmatched mime-type
        csv_edihist_log('edih_upload_match_file: invalid x12_file ' . strip_tags($x12obj->edih_message()));
        return false;
    }

    //
    if (!$ftype) {
        csv_edihist_log('edih_upload_match_file: unable to classify file ' . $fn);
        $ar_fn['reject'] = array('name' => $fn, 'comment' => 'unable to classify');
        return $ar_fn;
    }

    //
    $newname = $edih_upldir . DS . $fn;
    //
    if (rename($ftmp, $newname)) {
        if (chmod($newname, 0400)) {
            $ar_fn['type'] = $ftype;
            $ar_fn['name'] = $newname;
        } else {
            csv_edihist_log('edih_upload_match_file: failed to set permissions for ' . $fn);
            $ar_fn['reject'] = array('name' => $fn, 'comment' => 'failed to set permissions');
            unlink($newname);
            return false;
        }
    } else {
        csv_edihist_log("edih_upload_match_file: unable to move $fn to uploads directory");
        return false;
    }

    //
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
function edih_ziptoarray($zipfilename, $param_ar, $single = false)
{
    // note that this function moves files and set permissions, so platform issues may occur
    //
    $html_str = '';
    $edih_upldir = csv_edih_tmpdir();
    //
    $zip_obj = new ZipArchive();
    // open archive (ZipArchive::CHECKCONS the ZipArchive::CREATE is supposedly necessary for microsoft)
    if ($zip_obj->open($zipfilename, ZipArchive::CHECKCONS) !== true) {
        //$html_str .= "Error: Could not open archive $zipfilename <br />" . PHP_EOL;
        csv_edihist_log('edih_ziptoarray: Error: Could not open archive ' . $zipfilename);
        $f_zr['reject'][] = array('name' => $zipfilename, 'comment' => 'Error: Could not open archive ' . $zipfilename);
        return $f_zr;
    }

    if ($zip_obj->status != 0) {
        $err .= "Error code: " . text($zip_obj->status) . " " . text($zip_obj->getStatusString()) . "<br />" . PHP_EOL;
        csv_edihist_log('edih_ziptoarray: ' . $zipfilename . ' ' . $err);
        $f_zr['reject'][] = array('name' => $zipfilename, 'comment' => $err);
        return $f_zr;
    }

    // initialize output array and counter
    $f_zr = array();
    $p_ct = 0;
    // get number of files
    $f_ct = $zip_obj->numFiles;
    if ($single && $f_ct > 1) {
        csv_edihist_log('edih_ziptoarray: Usage: only single zipped file accepted through this input');
        $f_zr['reject'][] = array('name' => $zipfilename, 'comment' => 'Usage: only single zipped file accepted through this input');
        return $f_zr;
    }

    // get the file names
    for ($i = 0; $i < $f_ct; $i++) {
        //
        $isOK = true;
        $fstr = "";
        $file = $zip_obj->statIndex($i);
        $name = $file['name'];
        $oldCrc = $file['crc'];
        // get file contents
        $fstr = stream_get_contents($zip_obj->getStream($name));
        if ($fstr) {
            // use only the file name
            $bnm = basename($name);
            $newname = tempnam($edih_upldir, 'edi');
            //
            // extract the file to unzip tmp dir with read/write access
            $chrs = file_put_contents($newname, $fstr);
            // test crc
            $newCrc = hexdec(hash_file("crc32b", $newname));
            //
            if ($newCrc !== $oldCrc && ($oldCrc + 4294967296) !== $newCrc) {
                // failure case, mismatched crc file integrity values
                $html_str .= "CRC error: The files don't match! Removing file " . text($bnm) . " <br />" . PHP_EOL;
                $isGone = unlink($newname);
                if ($isGone) {
                    $is_tmpzip = false;
                    $html_str .= "File Removed " . text($bnm) . "<br />" . PHP_EOL;
                } else {
                    $html_str .= "Failed to removed file " . text($bnm) . "<br />" . PHP_EOL;
                }
            } else {
                // passed the CRC test, now type and verify file
                $fzp['name'] = $bnm;
                $fzp['tmp_name'] = $newname;
                // verification checks special to our application
                $f_uplz = edih_upload_match_file($param_ar, $fzp, $html_str);
                //
                if (is_array($f_uplz) && count($f_uplz)) {
                    if (isset($f_uplz['reject'])) {
                        $f_zr['reject'][] = $f_uplz['reject'];
                    } elseif (isset($f_uplz['name'])) {
                        $f_zr[$f_uplz['type']][] = $f_uplz['name'];
                        $p_ct++;
                    }
                } else {
                    // verification failed
                    $f_zr['reject'][] = array('name' => $fzp['name'], 'comment' => 'verification failed');
                }
            }

            //
        } else {
            csv_edihist_log("Did not get file contents $name");
            $isOK = false;
        }
    } // end for ($i=0; $i<$numFiles; $i++)
    //
    csv_edihist_log("Accepted $p_ct of $f_ct files from $zipfilename");
    //
    return $f_zr;
}

/**
 * Main function that handles the upload files array
 *
 * The return array has keys 'type' and subarray of file names
 * relies on global $_POST and $_FILES variables
 *
 * @uses edih_upload_reindex()
 * @uses edih_ziptoarray()
 * @uses ibr_upload_match_file()
 * @uses csv_parameters()
 *
 * @param string &$html_str   referenced and appended to in this function
 * @return array             array of files that pass the checks and scans
 */
function edih_upload_files()
{
    //
    $html_str = '';
    //
    // from php manual ling 03-Nov-2010 08:35
    if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $pmax = ini_get('post_max_size');
        //
        csv_edihist_log('edih_upload_files: Error: upload too large, max size is ' . $pmax);
        return false;
    }

    if (empty($_FILES)) {
        csv_edihist_log('Error: upload files indicated, but none received.');
        return false;
    }

    // only one $_FILES array key is expected
    $uplkey = '';
    $uplkey = array_key_exists("fileUplMulti", $_FILES) ? "fileUplMulti" : $uplkey;
    $uplkey = array_key_exists("fileUplx12", $_FILES) ? "fileUplx12" : $uplkey;
    //
    if (!$uplkey) {
        csv_edihist_log('edih_upload_files: Error: file array name error');
        return false;
    }

    //
    if ($uplkey != "fileUplMulti") {
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (!isset($_FILES[$uplkey]['error']) || is_array($_FILES[$uplkey]['error'])) {
            csv_edihist_log('edih_upload_files: Error: file array keys error');
            return false;
        }
    }

    //
    // these are the mime-types that we will accept -- however, mime-type is not reliable
    // for linux, system("file -bi -- ".escapeshellarg($uploadedfile)) gives mime-type and character encoding
    $m_types = array('application/octet-stream', 'text/plain', 'application/zip', 'application/x-zip-compressed');
    //
    // some unwanted file extensions that might be accidentally included in upload files
    $ext_types = 'sh|asp|html|htm|cm|js|xml|jpg|png|tif|xpm|pdf|php|py|pl|tcl|doc|pub|ppt|xls|xla|vsd|rtf|odt|ods|odp';
    // we get the parameters here to send to ibr_upload_match_file()
    $param_ar = csv_parameters("ALL");
    //
    // initialize retained files array and counter
    $f_ar = array();
    $p_ct = 0;
    //
    // here send the $_FILES array to edih_upload_reindex for "fileUplMulti"
    // instead of $_FILES[$uplkey] ["name"][$i] ["tmp_name"][$i] ["type"][$i] ["error"][$i] ["size"][$i]
    // we will have $files[$uplkey][$i] ["name"]["tmp_name"]["type"]["error"]["size"]
    if ($uplkey == "fileUplMulti") {
        $files = edih_upload_reindex($_FILES);
    } else {
        $files[$uplkey][] = $_FILES[$uplkey];
    }

    //
    $f_ct = count($files[$uplkey]);
    //begin the check and processing loop
    foreach ($files[$uplkey] as $idx => $fa) {
        // basic php verification checks
        if ($fa['error'] !== UPLOAD_ERR_OK) {
            //$html_str .= "Error: [{$fa['name']}] " . edih_upload_err_message($fa['error']) . "<br />" . PHP_EOL;
            $err = edih_upload_err_message($fa['error']);
            $f_ar['reject'][] = array('name' => $fa['name'],'comment' => $err);
            csv_edihist_log('edih_upload_files: _FILES error ' . $fa['name'] . ' ' . $err);
            unset($files[$uplkey][$idx]);
            continue;
        }

        if (!is_uploaded_file($fa['tmp_name'])) {
            //$html_str .= "Error: uploaded_file error for {$fa['name']}<br />". PHP_EOL;
            $f_ar['reject'][] = array('name' => $fa['name'],'comment' => 'php uploaded file error');
            csv_edihist_log('edih_upload_files: _FILES error tmp_name ' . $fa['name']);
            unset($files[$uplkey][$idx]);
            continue;
        }

        if (!in_array($fa['type'], $m_types)) {
            //$html_str .= "Error: mime-type {$fa['type']} not accepted for {$fa['name']} <br />" . PHP_EOL;
            $f_ar['reject'][] = array('name' => $fa['name'],'comment' => 'mime-type ' . $fa['type']);
            csv_edihist_log('edih_upload_files: _FILES error mime-type ' . $fa['name'] . ' mime-type ' . $fa['type']);
            unset($files[$uplkey][$idx]);
            continue;
        }

        // verify that we have a usable name
        $fext = ( strpos($fa['name'], '.') ) ? pathinfo($fa['name'], PATHINFO_EXTENSION) : '';
        if ($fext && preg_match('/' . $ext_types . '\?/i', $fext)) {
            //$html_str .= 'Error: uploaded_file error for '.$fa['name'].' extension '.$fext.'<br />'. PHP_EOL;
            $f_ar['reject'][] = array('name' => $fa['name'],'comment' => 'extension ' . $fext);
            csv_edihist_log('edih_upload_files: _FILES error name ' . $fa['name'] . ' extension ' . $fext);
            unset($files[$uplkey][$idx]);
            continue;
        }

        if (is_string($fa['name'])) {
            // check for null byte in file name, linux hidden file, directory
            if (strpos($fa['name'], '.') === 0 || strpos($fa['name'], "\0") !== false || strpos($fa['name'], "./") !== false) {
                //$html_str .= "Error: uploaded_file error for " . $fa['name'] . "<br />". PHP_EOL;
                $fname = preg_replace("/[^a-zA-Z0-9_.-]/", "_", $fa['name']);
                $f_ar['reject'][] = array('name' => $fname,'comment' => 'null byte, hidden, invalid');
                csv_edihist_log('edih_upload_files: null byte, hidden, invalid ' . $fname);
                unset($files[$uplkey][$idx]);
                continue;
            }

            // replace spaces in file names -- should not happen, but response files from payers might have spaces
            // $fname = preg_replace("/[^a-zA-Z0-9_.-]/","_",$fname);
            $fa['name'] = str_replace(' ', '_', $fa['name']);
        } else {
            // name is not a string
            //$html_str .= "Error: uploaded_file error for " . $fa['tmp_name'] . "<br />". PHP_EOL;
            $f_ar['reject'][] = array('name' => (string)$fa['name'],'comment' => 'invalid name');
            unset($files[$uplkey][$idx]);
            continue;
        }

        if (!$fa['tmp_name'] || !$fa['size']) {
            //$html_str .= "Error: file name or size error <br />" . PHP_EOL;
            $f_ar['reject'][] = array('name' => (string)$fa['name'],'comment' => 'php file upload error');
            unset($files[$uplkey][$idx]);
            continue;
        }

        // verification checks special to our application
        //
        //////////////////////////////////
        // check for zip file archive -- sent to edih_ziptoarray
        //
        if (strpos(strtolower($fa['name']), '.zip') || strpos($fa['type'], 'zip')) {
            //
            // this is a bit involved since we cannot predict how many files will be returned
            // get an array of files from the zip unpack function"fileUplx12"
            //
            //if ($uplkey != "fileUplmulti") {
                //$f_upl = edih_ziptoarray($fa['tmp_name'], $param_ar, false);
            //} else {
                //$f_upl = edih_ziptoarray($fa['tmp_name'], $param_ar, true);
            //}
            $f_upl = edih_ziptoarray($fa['tmp_name'], $param_ar);
            //
            // put them in the correct type array
            // expect fupl in form [type] = array(fn1, fn2, fn3, ...)
            if (is_array($f_upl) && count($f_upl)) {
                // $tp is file type, fz is file name
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

        //////////
        // at this point, since we have come through all the if statements
        // then we have:
        //  a single file under "fileUplEra"
        //  a single file under "fileUplx12"
        //  or one of possibly several files under "fileUplMulti"
        //////////
        $f_upl = edih_upload_match_file($param_ar, $fa);
        //
        if (is_array($f_upl) && count($f_upl) > 0) {
            $f_ar[$f_upl['type']][] = $f_upl['name'];
            $p_ct++;
        } else {
            // verification failed
            csv_edihist_log('edih_upload_file: verification failed for ' . $fa['name']);
            $f_ar['reject'][] = array('name' => $fa['name'], 'comment' => 'verification failed');
            unset($files[$uplkey][$idx]);
        }
    } // end foreach($files[$uplkey] as $idx=>$fa)
    //
    $f_ar['remark'][] = "Received $f_ct files, accepted $p_ct" . PHP_EOL;
    return $f_ar;
}

/**
 * Save the uploaded files array to the correct directory
 *
 * If a matching filename file is already in the directory it is not overwritten.
 * The uploaded file will just be discarded.
 *
 * @uses csv_parameters()
 * @see edih_upload_files()
 * @param array $files_array  files array created by edih_upload_files()
 * @param bool       -- whether to return html output
 * @param bool       -- whether to only report errors (ignored)
 * @return string    html formatted messages
 */
function edih_sort_upload($files_array, $html_out = true, $err_only = true)
{
    //
    $prc_htm = '';
    $rmk_htm = '';
    $dirpath = csv_edih_basedir();
    //
    if (is_array($files_array) && count($files_array)) {
        // we have some files
        $p_ar = csv_parameters($type = "ALL");
        //
        $prc_htm .=  "<p><em>Received Files</em></p>" . PHP_EOL;
        foreach ($files_array as $key => $val) {
            //
            $prc_htm .=  "<ul class='fupl'>" . PHP_EOL;
            if (isset($p_ar[$key])) {
                $tp_dir = $p_ar[$key]['directory'];
                $tp_base = basename($tp_dir);
                $idx = 0;
                $prc_htm .= "<li>type " . text($key) . "</li>" . PHP_EOL;
                if (!is_array($val) || !count($val)) {
                    $prc_htm .= "<li>no new files</li>" . PHP_EOL;
                    continue;
                }

                foreach ($val as $idx => $nf) {
                    // check if the file has already been stored
                    // a matching file name will not be replaced
                    $nfb = basename($nf);
                    $testname = $tp_dir . DS . $nfb;
                    $prc_htm .= "<li>" . text($nfb) . "</li>" . PHP_EOL;
                    if (is_file($testname)) {
                        $prc_htm .= "<li> -- file exists</li>" . PHP_EOL;
                    } elseif (rename($nf, $testname)) {
                        $iscm = chmod($testname, 0400);
                        if (!$iscm) {
                            // if we could write, we should be able to set permissions
                            $prc_htm .= "<li> -- file save error</li>" . PHP_EOL;
                            unlink($testname);
                        }
                    } else {
                        $prc_htm .= "<li> -- file save error</li>" . PHP_EOL;
                    }
                }
            } elseif ($key == 'reject') {
                $prc_htm .= "<li><bd>Reject:</bd></li>" . PHP_EOL;
                foreach ($val as $idx => $nf) {
                    $prc_htm .= "<li>" . text($nf['name']) . "</li>" . PHP_EOL;
                    $prc_htm .= "<li> --" . text($nf['comment']) . "</li>" . PHP_EOL;
                }
            } elseif ($key == 'remark') {
                $rmk_htm .= "<p><bd>Remarks:</bd><br />" . PHP_EOL;
                foreach ($val as $idx => $r) {
                    $rmk_htm .= text($r) . "<br />" . PHP_EOL;
                }

                $rmk_htm .= "</p>" . PHP_EOL;
            } else {
                $prc_htm .= "<li>" . text($key) . " type not stored</li>" . PHP_EOL;
                foreach ($val as $idx => $nf) {
                    $prc_htm .= "<li>" . text(basename($nf)) . "</li>" . PHP_EOL;
                }
            }

            $prc_htm .= "</ul>" . PHP_EOL;
            $prc_htm .= $rmk_htm . PHP_EOL;
        }
    } else {
        // should not happen since this function should not be called unless there are new files
        $prc_htm .= "<ul><li>No files submitted</li></ul>" . PHP_EOL;
    }

    //
    return $prc_htm;
}
