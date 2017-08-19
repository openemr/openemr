<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__) . "/../library/forms.inc");

class C_Document extends Controller
{

    var $template_mod;
    var $documents;
    var $document_categories;
    var $tree;
    var $_config;
        var $manual_set_owner=false; // allows manual setting of a document owner/service
    var $facilityService;
    var $patientService;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->facilityService = new \services\FacilityService();
        $this->patientService = new \services\PatientService();
        $this->documents = array();
        $this->template_mod = $template_mod;
        $this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
        $this->assign("CURRENT_ACTION", $GLOBALS['webroot']."/controller.php?" . "document&");

        //get global config options for this namespace
        $this->_config = $GLOBALS['oer_config']['documents'];

        $this->_args = array("patient_id" => $_GET['patient_id']);

        $this->assign("STYLE", $GLOBALS['style']);
        $t = new CategoryTree(1);
        //print_r($t->tree);
        $this->tree = $t;
        $this->Document = new Document();
    }

    function upload_action($patient_id, $category_id)
    {
        $category_name = $this->tree->get_node_name($category_id);
        $this->assign("category_id", $category_id);
        $this->assign("category_name", $category_name);
        $this->assign("hide_encryption", $GLOBALS['hide_document_encryption']);
        $this->assign("patient_id", $patient_id);

        // Added by Rod to support document template download from general_upload.html.
        // Cloned from similar stuff in manage_document_templates.php.
        $templatedir = $GLOBALS['OE_SITE_DIR'] . '/documents/doctemplates';
        $templates_options = "<option value=''>-- " . xlt('Select Template') . " --</option>";
        if (file_exists($templatedir)) {
              $dh = opendir($templatedir);
        }
        if ($dh) {
              $templateslist = array();
            while (false !== ($sfname = readdir($dh))) {
                if (substr($sfname, 0, 1) == '.') {
                    continue;
                }
                $templateslist[$sfname] = $sfname;
            }
              closedir($dh);
              ksort($templateslist);
            foreach ($templateslist as $sfname) {
                $templates_options .= "<option value='" . attr($sfname) .
                  "'>" . text($sfname) . "</option>";
            }
        }
        $this->assign("TEMPLATES_LIST", $templates_options);

        $activity = $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_upload.html");
        $this->assign("activity", $activity);
        return $this->list_action($patient_id);
    }

    //Upload multiple files on single click
    function upload_action_process()
    {

        // Collect a manually set owner if this has been set
        // Used when want to manually assign the owning user/service such as the Direct mechanism
        $non_HTTP_owner=false;
        if ($this->manual_set_owner) {
            $non_HTTP_owner=$this->manual_set_owner;
        }

        $couchDB = false;
        $harddisk = false;
        if ($GLOBALS['document_storage_method']==0) {
            $harddisk = true;
        }
        if ($GLOBALS['document_storage_method']==1) {
            $couchDB = true;
        }

        if ($_POST['process'] != "true") {
            return;
        }

        $doDecryption = false;
        $encrypted = $_POST['encrypted'];
        $passphrase = $_POST['passphrase'];
        if (!$GLOBALS['hide_document_encryption'] &&
            $encrypted && $passphrase ) {
            $doDecryption = true;
        }

        if (is_numeric($_POST['category_id'])) {
            $category_id = $_POST['category_id'];
        }

        $patient_id = 0;
        if (isset($_GET['patient_id']) && !$couchDB) {
            $patient_id = $_GET['patient_id'];
        } else if (is_numeric($_POST['patient_id'])) {
            $patient_id = $_POST['patient_id'];
        }

        $sentUploadStatus = array();
        if (count($_FILES['file']['name']) > 0) {
            $upl_inc = 0;

            foreach ($_FILES['file']['name'] as $key => $value) {
                $fname = $value;
                $err = "";
                if ($_FILES['file']['error'][$key] > 0 || empty($fname) || $_FILES['file']['size'][$key] == 0) {
                    $fname = $value;
                    if (empty($fname)) {
                        $fname = htmlentities("<empty>");
                    }
                    $error = xl("Error number") .": " . $_FILES['file']['error'][$key] . " " . xl("occurred while uploading file named") . ": " . $fname . "\n";
                    if ($_FILES['file']['size'][$key] == 0) {
                        $error .= xl("The system does not permit uploading files of with size 0.") . "\n";
                    }
                } elseif ($GLOBALS['secure_upload'] && !isWhiteFile($_FILES['file']['tmp_name'][$key])) {
                       $error = xl("The system does not permit uploading files with MIME content type") . " - " . mime_content_type($_FILES['file']['tmp_name'][$key]) . ".\n";
                } else {
                    $tmpfile = fopen($_FILES['file']['tmp_name'][$key], "r");
                    $filetext = fread($tmpfile, $_FILES['file']['size'][$key]);
                    fclose($tmpfile);
                    if ($doDecryption) {
                        $filetext = $this->decrypt($filetext, $passphrase);
                    }
                    if ($_POST['destination'] != '') {
                        $fname = $_POST['destination'];
                    }
                    $d = new Document();
                    $rc = $d->createDocument(
                        $patient_id,
                        $category_id,
                        $fname,
                        $_FILES['file']['type'][$key],
                        $filetext,
                        empty($_GET['higher_level_path']) ? '' : $_GET['higher_level_path'],
                        empty($_POST['path_depth']) ? 1 : $_POST['path_depth'],
                        $non_HTTP_owner,
                        $_FILES['file']['tmp_name'][$key]
                    );
                    if ($rc) {
                        $error .= $rc . "\n";
                    } else {
                        $this->assign("upload_success", "true");
                    }
                    $sentUploadStatus[] = $d;
                    $this->assign("file", $sentUploadStatus);
                }

                // Option to run a custom plugin for each file upload.
                // This was initially created to delete the original source file in a custom setting.
                $upload_plugin = $GLOBALS['OE_SITE_DIR'] . "/documentUpload.plugin.php";
                if (file_exists($upload_plugin)) {
                    include_once($upload_plugin);
                }
                $upload_plugin_pp = 'documentUploadPostProcess';
                if (function_exists($upload_plugin_pp)) {
                    $tmp = call_user_func($upload_plugin_pp, $value, $d);
                    if ($tmp) {
                        $error = $tmp;
                    }
                }
                // Following is just an example of code in such a plugin file.
                /*****************************************************
                function documentUploadPostProcess($filename, &$d) {
                  $userid = $_SESSION['authUserID'];
                  $row = sqlQuery("SELECT username FROM users WHERE id = ?", array($userid));
                  $owner = strtolower($row['username']);
                  $dn = '1_' . ucfirst($owner);
                  $filepath = "/shared_network_directory/$dn/$filename";
                  if (@unlink($filepath)) return '';
                  return "Failed to delete '$filepath'.";
                }
                *****************************************************/
            }
        }

        $this->assign("error", nl2br($error));
        //$this->_state = false;
        $_POST['process'] = "";
        //return $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_upload.html");
    }

    function note_action_process($patient_id)
    {
        // this function is a dual function that will set up a note associated with a document or send a document via email.

        if ($_POST['process'] != "true") {
            return;
        }

        $n = new Note();
                $n->set_owner($_SESSION['authUserID']);
        parent::populate_object($n);
        if ($_POST['identifier'] == "no") {
                        // associate a note with a document
            $n->persist();
        } elseif ($_POST['identifier'] == "yes") {
                        // send the document via email
                        $d = new Document($_POST['foreign_id']);
                        $url =  $d->get_url();
                        $storagemethod = $d->get_storagemethod();
                        $couch_docid = $d->get_couch_docid();
                        $couch_revid = $d->get_couch_revid();
            if ($couch_docid && $couch_revid) {
                $couch = new CouchDB();
                $data = array($GLOBALS['couchdb_dbase'],$couch_docid);
                $resp = $couch->retrieve_doc($data);
                $content = $resp->data;
                if ($content=='' && $GLOBALS['couchdb_log']==1) {
                    $log_content = date('Y-m-d H:i:s')." ==> Retrieving document\r\n";
                    $log_content = date('Y-m-d H:i:s')." ==> URL: ".$url."\r\n";
                    $log_content .= date('Y-m-d H:i:s')." ==> CouchDB Document Id: ".$couch_docid."\r\n";
                    $log_content .= date('Y-m-d H:i:s')." ==> CouchDB Revision Id: ".$couch_revid."\r\n";
                    $log_content .= date('Y-m-d H:i:s')." ==> Failed to fetch document content from CouchDB.\r\n";
                    //$log_content .= date('Y-m-d H:i:s')." ==> Will try to download file from HardDisk if exists.\r\n\r\n";
                    $this->document_upload_download_log($d->get_foreign_id(), $log_content);
                    die(xlt("File retrieval from CouchDB failed"));
                }
        // place it in a temporary file and will remove the file below after emailed
                $temp_couchdb_url = $GLOBALS['OE_SITE_DIR'].'/documents/temp/couch_'.date("YmdHis").$d->get_url_file();
                $fh = fopen($temp_couchdb_url, "w");
                fwrite($fh, base64_decode($content));
                fclose($fh);
                $temp_url = $temp_couchdb_url; // doing this ensure hard drive file never deleted in case something weird happens
            } else {
                $url = preg_replace("|^(.*)://|", "", $url);
        // Collect filename and path
                $from_all = explode("/", $url);
                $from_filename = array_pop($from_all);
                $from_pathname_array = array();
                for ($i=0; $i<$d->get_path_depth(); $i++) {
                    $from_pathname_array[] = array_pop($from_all);
                }
                $from_pathname_array = array_reverse($from_pathname_array);
                $from_pathname = implode("/", $from_pathname_array);
                $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;
            }
            if (!file_exists($temp_url)) {
                echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.', '', '', ' ') . $temp_url;
            }
                        $url = $temp_url;
            $body_notes = attr($_POST['note']);
            $pdetails = getPatientData($patient_id);
            $pname = $pdetails['fname']." ".$pdetails['lname'];
            $this->document_send($_POST['provide_email'], $body_notes, $url, $pname);
            if ($couch_docid && $couch_revid) {
      // remove the temporary couchdb file
                unlink($temp_couchdb_url);
            }
        }
        $this->_state = false;
        $_POST['process'] = "";
        return $this->view_action($patient_id, $n->get_foreign_id());
    }

    function default_action()
    {
        return $this->list_action();
    }

    function view_action($patient_id = "", $doc_id)
    {
        // Added by Rod to support document delete:
        global $gacl_object, $phpgacl_location;
        global $ISSUE_TYPES;

        require_once(dirname(__FILE__) . "/../library/acl.inc");
        require_once(dirname(__FILE__) . "/../library/lists.inc");

        $d = new Document($doc_id);
        $notes = $d->get_notes();

        $this->assign("file", $d);
        $this->assign("web_path", $this->_link("retrieve") . "document_id=" . $d->get_id() . "&");
        $this->assign("NOTE_ACTION", $this->_link("note"));
        $this->assign("MOVE_ACTION", $this->_link("move") . "document_id=" . $d->get_id() . "&process=true");
        $this->assign("hide_encryption", $GLOBALS['hide_document_encryption']);

        // Added by Rod to support document delete:
        $delete_string = '';
        if (acl_check('admin', 'super')) {
            $delete_string = "<a href='' class='css_button' onclick='return deleteme(" . $d->get_id() .
                ")'><span><font color='red'>" . xl('Delete') . "</font></span></a>";
        }
        $this->assign("delete_string", $delete_string);
        $this->assign("REFRESH_ACTION", $this->_link("list"));

        $this->assign("VALIDATE_ACTION", $this->_link("validate") .
            "document_id=" . $d->get_id() . "&process=true");

        // Added by Rod to support document date update:
        $this->assign("DOCDATE", $d->get_docdate());
        $this->assign("UPDATE_ACTION", $this->_link("update") .
            "document_id=" . $d->get_id() . "&process=true");

        // Added by Rod to support document issue update:
        $issues_options = "<option value='0'>-- " . xlt('Select Issue') . " --</option>";
        $ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
            "pid = ? " . // AND enddate IS NULL " .
            "ORDER BY type, begdate", array($patient_id));
        while ($irow = sqlFetchArray($ires)) {
            $desc = $irow['type'];
            if ($ISSUE_TYPES[$desc]) {
                $desc = $ISSUE_TYPES[$desc][2];
            }
            $desc .= ": " . text($irow['begdate']) . " " . text(substr($irow['title'], 0, 40));
            $sel = ($irow['id'] == $d->get_list_id()) ? ' selected' : '';
            $issues_options .= "<option value='" . attr($irow['id']) . "'$sel>$desc</option>";
        }
        $this->assign("ISSUES_LIST", $issues_options);

        // For tagging to encounter
        // Populate the dropdown with patient's encounter list
        $this->assign("TAG_ACTION", $this->_link("tag") . "document_id=" . $d->get_id() . "&process=true");
        $encOptions = "<option value='0'>-- " . xlt('Select Encounter') . " --</option>";
        $result_docs = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
            "LEFT JOIN openemr_postcalendar_categories ON fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? ORDER BY fe.date desc", array($patient_id));
        if (sqlNumRows($result_docs) > 0) {
            while ($row_result_docs = sqlFetchArray($result_docs)) {
                $sel_enc = ($row_result_docs['encounter'] == $d->get_encounter_id()) ? ' selected' : '';
                $encOptions .= "<option value='" . attr($row_result_docs['encounter']) . "' $sel_enc>". oeFormatShortDate(date('Y-m-d', strtotime($row_result_docs['date']))) . "-" . text(xl_appt_category($row_result_docs['pc_catname'])) . "</option>";
            }
        }
        $this->assign("ENC_LIST", $encOptions);

        //clear encounter tag
        if ($d->get_encounter_id() != 0) {
            $this->assign('clear_encounter_tag', $this->_link('clear_encounter_tag')."document_id=" . $d->get_id());
        } else {
            $this->assign('clear_encounter_tag', 'javascript:void(0)');
        }

        //Populate the dropdown with category list
        $visit_category_list = "<option value='0'>-- " . xlt('Select One') . " --</option>";
        $cres = sqlStatement("SELECT pc_catid, pc_catname FROM openemr_postcalendar_categories ORDER BY pc_catname");
        while ($crow = sqlFetchArray($cres)) {
            $catid = $crow['pc_catid'];
            if ($catid < 9 && $catid != 5) {
                continue; // Applying same logic as in new encounter page.
            }
            $visit_category_list .="<option value='".attr($catid)."'>" . text(xl_appt_category($crow['pc_catname'])) . "</option>\n";
        }
        $this->assign("VISIT_CATEGORY_LIST", $visit_category_list);

        $this->assign("notes", $notes);

        $this->assign("IMG_PROCEDURE_TAG_ACTION", $this->_link("image_procedure") . "document_id=" . $d->get_id());
            // Populate the dropdown with image procedure order list
        $imgOptions = "<option value='0'>-- " . xlt('Select Image Procedure') . " --</option>";
        $imgOrders  = sqlStatement("select procedure_name,po.procedure_order_id,procedure_code from procedure_order po inner join procedure_order_code poc on poc.procedure_order_id = po.procedure_order_id where po.patient_id = ?  and poc.procedure_order_title = 'imaging'", array($patient_id));
        $mapping    = $this->get_mapped_procedure($d->get_id());
        if (sqlNumRows($imgOrders) > 0) {
            while ($row = sqlFetchArray($imgOrders)) {
                $sel_proc = '';
                if ((isset($mapping['procedure_code']) && $mapping['procedure_code'] == $row['procedure_code']) && (isset($mapping['procedure_code']) && $mapping['procedure_order_id'] == $row['procedure_order_id'])) {
                    $sel_proc = 'selected';
                }
                $imgOptions .= "<option value='". attr($row['procedure_order_id']). "' data-code='".attr($row['procedure_code'])."' $sel_proc>".text($row['procedure_name'].' - '.$row['procedure_code'])."</option>";
            }
        }

        $this->assign('IMAGE_PROCEDURE_LIST', $imgOptions);

        $this->assign('clear_procedure_tag', $this->_link('clear_procedure_tag')."document_id=" . $d->get_id());

        $this->_last_node = null;

        $menu  = new HTML_TreeMenu();

        //pass an empty array because we don't want the documents for each category showing up in this list box
        $rnode = $this->_array_recurse($this->tree->tree, array());
        $menu->addItem($rnode);
        $treeMenu_listbox  = new HTML_TreeMenu_Listbox($menu, array("promoText" => xl('Move Document to Category:')));

        $this->assign("tree_html_listbox", $treeMenu_listbox->toHTML());

        $activity = $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_view.html");
        $this->assign("activity", $activity);

        return $this->list_action($patient_id);
    }

    function encrypt($plaintext, $key, $cypher = 'tripledes', $mode = 'cfb')
    {
        $td = mcrypt_module_open($cypher, '', $mode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $crypttext = mcrypt_generic($td, $plaintext);
        mcrypt_generic_deinit($td);
        return $iv.$crypttext;
    }

    function decrypt($crypttext, $key, $cypher = 'tripledes', $mode = 'cfb')
    {
        $plaintext = '';
        $td = mcrypt_module_open($cypher, '', $mode, '');
        $ivsize = mcrypt_enc_get_iv_size($td) ;
        $iv = substr($crypttext, 0, $ivsize);
        $crypttext = substr($crypttext, $ivsize);
        if ($iv) {
            mcrypt_generic_init($td, $key, $iv);
            $plaintext = mdecrypt_generic($td, $crypttext);
        }
        return $plaintext;
    }

    /**
     * Retrieve file from hard disk / CouchDB.
     * In case that file isn't download this function will return thumbnail image (if exist).
     * @param (boolean) $show_original - enable to show the original image (not thumbnail) in inline status.
     * @param (string) $context - given a special document scenario (e.g.: patient avatar, custom image viewer document, etc), the context can be set so that a switch statement can execute a custom strategy.
     * */
    function retrieve_action($patient_id = "", $document_id, $as_file = true, $original_file = true, $disable_exit = false, $show_original = false, $context = "normal")
    {
        $encrypted = $_POST['encrypted'];
        $passphrase = $_POST['passphrase'];
        $doEncryption = false;
        if (!$GLOBALS['hide_document_encryption'] &&
            $encrypted == "true" &&
            $passphrase ) {
            $doEncryption = true;
        }

            //controller function ruins booleans, so need to manually re-convert to booleans
        if ($as_file == "true") {
                $as_file=true;
        } else if ($as_file == "false") {
                $as_file=false;
        }
        if ($original_file == "true") {
                $original_file=true;
        } else if ($original_file == "false") {
                $original_file=false;
        }
        if ($disable_exit == "true") {
                $disable_exit=true;
        } else if ($disable_exit == "false") {
                $disable_exit=false;
        }
        if ($show_original == "true") {
            $show_original=true;
        } else if ($show_original == "false") {
            $show_original=false;
        }

        switch ($context) {
            case "patient_picture":
                $this->patientService->setPid($patient_id);
                $document_id = $this->patientService->getPatientPictureDocumentId();
                break;
        }

        $d = new Document($document_id);
        $url =  $d->get_url();
        $th_url = $d->get_thumb_url();

        $storagemethod = $d->get_storagemethod();
        $couch_docid = $d->get_couch_docid();
        $couch_revid = $d->get_couch_revid();

        if ($couch_docid && $couch_revid && $original_file) {
            $couch = new CouchDB();
            $data = array($GLOBALS['couchdb_dbase'],$couch_docid);
            $resp = $couch->retrieve_doc($data);
            //Take thumbnail file when is not null and file is presented online
            if (!$as_file && !is_null($th_url) && !$show_original) {
                $content = $resp->th_data;
            } else {
                $content = $resp->data;
            }
            if ($content=='' && $GLOBALS['couchdb_log']==1) {
                $log_content = date('Y-m-d H:i:s')." ==> Retrieving document\r\n";
                $log_content = date('Y-m-d H:i:s')." ==> URL: ".$url."\r\n";
                $log_content .= date('Y-m-d H:i:s')." ==> CouchDB Document Id: ".$couch_docid."\r\n";
                $log_content .= date('Y-m-d H:i:s')." ==> CouchDB Revision Id: ".$couch_revid."\r\n";
                $log_content .= date('Y-m-d H:i:s')." ==> Failed to fetch document content from CouchDB.\r\n";
                $log_content .= date('Y-m-d H:i:s')." ==> Will try to download file from HardDisk if exists.\r\n\r\n";
                $this->document_upload_download_log($d->get_foreign_id(), $log_content);
                die(xl("File retrieval from CouchDB failed"));
            }
            if ($disable_exit == true) {
                return base64_decode($content);
            }
            header('Content-Description: File Transfer');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            $tmpcouchpath = $GLOBALS['OE_SITE_DIR'].'/documents/temp/couch_'.date("YmdHis").$d->get_url_file();
            $fh = fopen($tmpcouchpath, "w");
            fwrite($fh, base64_decode($content));
            fclose($fh);
            $f = fopen($tmpcouchpath, "r");
            if ($doEncryption) {
                $filetext = fread($f, filesize($tmpcouchpath));
                    $ciphertext = $this->encrypt($filetext, $passphrase);
                    $tmpfilepath = $GLOBALS['temporary_files_dir'];
                    $tmpfilename = "/encrypted_".$d->get_url_file();
                    $tmpfile = fopen($tmpfilepath.$tmpfilename, "w+");
                fwrite($tmpfile, $ciphertext);
                fclose($tmpfile);
                header('Content-Disposition: attachment; filename='.$tmpfilename);
                    header("Content-Type: application/octet-stream");
                    header("Content-Length: " . filesize($tmpfilepath.$tmpfilename));
                    ob_clean();
                flush();
                readfile($tmpfilepath.$tmpfilename);
                unlink($tmpfilepath.$tmpfilename);
            } else {
                header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename_international($d->get_url()) . "\"");
                    header("Content-Type: " . $d->get_mimetype());
                    header("Content-Length: " . filesize($tmpcouchpath));
                    fpassthru($f);
            }
            fclose($f);
            if ($content!='') {
                unlink($tmpcouchpath);
            }
            exit;//exits only if file download from CouchDB is successfull.
        }

        //Take thumbnail file when is not null and file is presented online
        if (!$as_file && !is_null($th_url) && !$show_original) {
            $url = $th_url;
        }

        //strip url of protocol handler
        $url = preg_replace("|^(.*)://|", "", $url);

        //change full path to current webroot.  this is for documents that may have
        //been moved from a different filesystem and the full path in the database
        //is not current.  this is also for documents that may of been moved to
        //different patients. Note that the path_depth is used to see how far down
                //the path to go. For example, originally the path_depth was always 1, which
                //only allowed things like documents/1/<file>, but now can have more structured
                //directories. For example a path_depth of 2 can give documents/encounters/1/<file>
                // etc.
        // NOTE that $from_filename and basename($url) are the same thing
        $from_all = explode("/", $url);
            $from_filename = array_pop($from_all);
            $from_pathname_array = array();
        for ($i=0; $i<$d->get_path_depth(); $i++) {
            $from_pathname_array[] = array_pop($from_all);
        }
                $from_pathname_array = array_reverse($from_pathname_array);
                $from_pathname = implode("/", $from_pathname_array);
        if ($couch_docid && $couch_revid) {
            //for couchDB no URL is available in the table, hence using the foreign_id which is patientID
            $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/' . $d->get_foreign_id() . '_' . $from_filename;
        } else {
            $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;
        }

        if (file_exists($temp_url)) {
            $url = $temp_url;
        }


        if (!file_exists($url)) {
            echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.', '', '', ' ') . $url;
        } else {
            if ($original_file) {
                //normal case when serving the file referenced in database
                if ($disable_exit == true) {
                    $f = fopen($url, "r");
                    $filetext = fread($f, filesize($url));
                    return $filetext;
                }
                    header('Content-Description: File Transfer');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    $f = fopen($url, "r");
                if ($doEncryption) {
                            $filetext = fread($f, filesize($url));
                    $ciphertext = $this->encrypt($filetext, $passphrase);
                    $tmpfilepath = $GLOBALS['temporary_files_dir'];
                    $tmpfilename = "/encrypted_".$d->get_url_file();
                    $tmpfile = fopen($tmpfilepath.$tmpfilename, "w+");
                        fwrite($tmpfile, $ciphertext);
                        fclose($tmpfile);
                        header('Content-Disposition: attachment; filename='.$tmpfilename);
                        header("Content-Type: application/octet-stream");
                        header("Content-Length: " . filesize($tmpfilepath.$tmpfilename));
                        ob_clean();
                        flush();
                        readfile($tmpfilepath.$tmpfilename);
                        unlink($tmpfilepath.$tmpfilename);
                } else {
                    header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename_international($d->get_url()) . "\"");
                    header("Content-Type: " . $d->get_mimetype());
                    header("Content-Length: " . filesize($url));
                    fpassthru($f);
                }
                    exit;
            } else {
                //special case when retrieving a document that has been converted to a jpg and not directly referenced in database
                $convertedFile = substr(basename_international($url), 0, strrpos(basename_international($url), '.')) . '_converted.jpg';
                if ($couch_docid && $couch_revid) {
                    $url = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/' . $convertedFile;
                } else {
                    $url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $convertedFile;
                }
                if ($disable_exit == true) {
                    return ;
                }
                        header("Pragma: public");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                        header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename_international($url) . "\"");
                        header("Content-Type: image/jpeg");
                        header("Content-Length: " . filesize($url));
                        $f = fopen($url, "r");
                        fpassthru($f);
                if ($couch_docid && $couch_revid) {
                    fclose($f);
                    unlink($url);
                    $url=str_replace("_converted.jpg", '.pdf', $url);
                    unlink($url);
                }
                        exit;
            }
        }
    }

    function queue_action($patient_id = "")
    {
        $messages = $this->_tpl_vars['messages'];
        $queue_files = array();

        //see if the repository exists and it is a directory else error
        if (file_exists($this->_config['repository']) && is_dir($this->_config['repository'])) {
            $dir = opendir($this->_config['repository']);
            //read each entry in the directory
            while (($file = readdir($dir)) !== false) {
                //concat the filename and path
                $file = $this->_config['repository'] .$file;
                $file_info = array();
                //if the filename is a file get its info and put into a tmp array
                if (is_file($file) && strpos(basename_international($file), ".") !== 0) {
                    $file_info['filename'] = basename_international($file);
                    $file_info['mtime'] = date("m/d/Y H:i:s", filemtime($file));
                    $d = $this->Document->document_factory_url("file://" . $file);
                    preg_match("/^([0-9]+)_/", basename_international($file), $patient_match);
                    $file_info['patient_id'] = $patient_match[1];
                    $file_info['document_id'] = $d->get_id();
                    $file_info['web_path'] = $this->_link("retrieve", true) . "document_id=" . $d->get_id() . "&";

                    //merge the tmp array into the larger array
                    $queue_files[] = $file_info;
                }
            }
            closedir($dir);
        } else {
            $messages .= "The repository directory does not exist, it is not a directory or there are not sufficient permissions to access it. '" . $this->config['repository'] . "'\n";
        }


        $this->assign("queue_files", $queue_files);
        $this->_last_node = null;

        $menu  = new HTML_TreeMenu();

        //pass an empty array because we don't want the documents for each category showing up in this list box
        $rnode = $this->_array_recurse($this->tree->tree, array());
        $menu->addItem($rnode);
        $treeMenu_listbox  = new HTML_TreeMenu_Listbox($menu, array());

        $this->assign("tree_html_listbox", $treeMenu_listbox->toHTML());

        $this->assign("messages", nl2br($messages));
        return $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_queue.html");
    }

    function queue_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $messages = $this->_tpl_vars['messages'];

        //build a category tree so we can have a list of category ids that are valid
        $ct = new CategoryTree(1);
        $categories = $ct->_id_name;

        //see if there were and posted files and assign them
        $files = null;
        is_array($_POST['files']) ? $files = $_POST['files']: $files = array();

        //loop through posted files
        foreach ($files as $doc_id => $file) {
            //only operate on files checked as active
            if (!$file['active']) {
                continue;
            }

            //run basic validation checks
            if (!is_numeric($file['patient_id']) || !is_numeric($file['category_id']) || !is_numeric($doc_id)) {
                $messages .= "Error processing file '" . $file['name'] ."' the patient id must be a number and the category must exist.\n";
                continue;
            }

            //validate that the pod exists
            $d = new Document($doc_id);
            $sql = "SELECT pid from patient_data where pubpid = '" . $file['patient_id'] . "'";
            $result = $d->_db->Execute($sql);

            if (!$result || $result->EOF) {
                //patient id does not exist
                $messages .= "Error processing file '" . $file['name'] ." the specified patient id '" . $file['patient_id'] . "' could not be found.\n";
                continue;
            }

            //validate that the category id exists
            if (!isset($categories[$file['category_id']])) {
                $messages .= "Error processing file '" . $file['name'] . " the specified category with id '" . $file['category_id'] . "' could not be found.\n";
                continue;
            }

            //now do the work of moving the file
            $new_path = $this->_config['repository'] . $file['patient_id'] ."/";

            //see if the patient dir exists in the repository and create if not
            if (!file_exists($new_path)) {
                if (!mkdir($new_path, 0700)) {
                    $messages .= "The system was unable to create the directory for this upload, '" . $new_path . "'.\n";
                    continue;
                }
            }

            //fname is the name of the file after it is moved
            $fname = $file['name'];

            //see if patient autonumbering is used in this filename, if so strip out the autonumber part
            preg_match("/^([0-9]+)_/", basename_international($fname), $patient_match);
            if ($patient_match[1] == $file['patient_id']) {
                $fname = preg_replace("/^([0-9]+)_/", "", $fname);
            }

            //filenames should not have funny chars
            $fname = preg_replace("/[^a-zA-Z0-9_.]/", "_", $fname);

            //see if there is an existing file with the same name and rename as necessary
            if (file_exists($new_path.$file['name'])) {
                $messages .= "File with same name already exists at location: " . $new_path . "\n";
                $fname = basename_international($this->_rename_file($new_path.$file['name']));
                $messages .= "Current file name was changed to " . $fname ."\n";
            }

            //now move the file
            if (rename($this->_config['repository'].$file['name'], $new_path.$fname)) {
                $messages .= "File " . $fname . " moved to patient id '" . $file['patient_id'] ."' and category '" . $categories[$file['category_id']]['name'] . "' successfully.\n";
                $d->url = "file://" .$new_path.$fname;
                $d->set_foreign_id($file['patient_id']);
                $d->set_mimetype($mimetype);
                $d->persist();
                $d->populate();

                if (is_numeric($d->get_id()) && is_numeric($file['category_id'])) {
                    $sql = "REPLACE INTO categories_to_documents set category_id = '" . $file['category_id'] . "', document_id = '" . $d->get_id() . "'";
                    $d->_db->Execute($sql);
                }
            } else {
                $error .= "The file could not be succesfully stored, this error is usually related to permissions problems on the storage system.\n";
            }
        }
            $this->assign("messages", $messages);
            $_POST['process'] = "";
    }

    function move_action_process($patient_id = "", $document_id)
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $new_category_id = $_POST['new_category_id'];
        $new_patient_id = $_POST['new_patient_id'];

        //move to new category
        if (is_numeric($new_category_id) && is_numeric($document_id)) {
            $sql = "UPDATE categories_to_documents set category_id = '" . $new_category_id . "' where document_id = '" . $document_id ."'";
            $messages .= xl('Document moved to new category', '', '', ' \'') . $this->tree->_id_name[$new_category_id]['name']  . xl('successfully.', '', '\' ') . "\n";
            //echo $sql;
            $this->tree->_db->Execute($sql);
        }

        //move to new patient
        if (is_numeric($new_patient_id) && is_numeric($document_id)) {
            $d = new Document($document_id);
            // $sql = "SELECT pid from patient_data where pubpid = '" . $new_patient_id . "'";
            $sql = "SELECT pid from patient_data where pid = '" . $new_patient_id . "'";
            $result = $d->_db->Execute($sql);

            if (!$result || $result->EOF) {
                //patient id does not exist
                $messages .= xl('Document could not be moved to patient id', '', '', ' \'') . $new_patient_id  . xl('because that id does not exist.', '', '\' ') . "\n";
            } else {
                $couchsavefailed = !$d->change_patient($new_patient_id);

                $this->_state = false;
                if (!$couchsavefailed) {
                    $messages .= xl('Document moved to patient id', '', '', ' \'') . $new_patient_id  . xl('successfully.', '', '\' ') . "\n";
                } else {
                    $messages .= xl('Document moved to patient id', '', '', ' \'') . $new_patient_id  . xl('Failed.', '', '\' ') . "\n";
                }
                $this->assign("messages", $messages);
                return $this->list_action($patient_id);
            }
        } //in this case return the document to the queue instead of moving it
        elseif (strtolower($new_patient_id) == "q" && is_numeric($document_id)) {
            $d = new Document($document_id);
            $new_path = $this->_config['repository'];
            $fname = $d->get_url_file();

            //see if there is an existing file with the same name and rename as necessary
            if (file_exists($new_path.$d->get_url_file())) {
                $messages .= "File with same name already exists in the queue.\n";
                $fname = basename_international($this->_rename_file($new_path.$d->get_url_file()));
                $messages .= "Current file name was changed to " . $fname ."\n";
            }

            //now move the file
            if (rename($d->get_url_filepath(), $new_path.$fname)) {
                $d->url = "file://" .$new_path.$fname;
                $d->set_foreign_id("");
                $d->persist();
                $d->persist();
                $d->populate();

                $sql = "DELETE FROM categories_to_documents where document_id =" . $d->_db->qstr($document_id);
                $d->_db->Execute($sql);
                $messages .= "Document returned to queue successfully.\n";
            } else {
                $messages .= "The file could not be succesfully stored, this error is usually related to permissions problems on the storage system.\n";
            }

            $this->_state = false;
            $this->assign("messages", $messages);
            return $this->list_action($patient_id);
        }

        $this->_state = false;
        $this->assign("messages", $messages);
        return $this->view_action($patient_id, $document_id);
    }

    function validate_action_process($patient_id = "", $document_id)
    {

                $d = new Document($document_id);
        if ($d->couch_docid && $d->couch_revid) {
            $file_path = $GLOBALS['OE_SITE_DIR'].'/documents/temp/';
            $url = $file_path.$d->get_url();
            $couch = new CouchDB();
            $data = array($GLOBALS['couchdb_dbase'],$d->couch_docid);
            $resp = $couch->retrieve_doc($data);
            $content = $resp->data;
            //--------Temporarily writing the file for calculating the hash--------//
            //-----------Will be removed after calculating the hash value----------//
            $temp_file = fopen($url, "w");
            fwrite($temp_file, base64_decode($content));
            fclose($temp_file);
        } else {
                $url =  $d->get_url();

                //strip url of protocol handler
                $url = preg_replace("|^(.*)://|", "", $url);

                //change full path to current webroot.  this is for documents that may have
                //been moved from a different filesystem and the full path in the database
                //is not current.  this is also for documents that may of been moved to
                //different patients. Note that the path_depth is used to see how far down
                //the path to go. For example, originally the path_depth was always 1, which
                //only allowed things like documents/1/<file>, but now can have more structured
                //directories. For example a path_depth of 2 can give documents/encounters/1/<file>
                // etc.
                // NOTE that $from_filename and basename($url) are the same thing
                $from_all = explode("/", $url);
                $from_filename = array_pop($from_all);
                $from_pathname_array = array();
            for ($i=0; $i<$d->get_path_depth(); $i++) {
                $from_pathname_array[] = array_pop($from_all);
            }
                $from_pathname_array = array_reverse($from_pathname_array);
                $from_pathname = implode("/", $from_pathname_array);
                $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;
            if (file_exists($temp_url)) {
                $url = $temp_url;
            }

            if ($_POST['process'] != "true") {
                die("process is '" . $_POST['process'] . "', expected 'true'");
                return;
            }
        }
        $d = new Document($document_id);
        $current_hash = sha1_file($url);
        $messages = xl('Current Hash').": ".$current_hash."<br>";
        $messages .= xl('Stored Hash').": ".$d->get_hash()."<br>";
        if ($d->get_hash() == '') {
            $d->hash = $current_hash;
            $d->persist();
            $d->populate();
            $messages .= xl('Hash did not exist for this file. A new hash was generated.');
        } else if ($current_hash != $d->get_hash()) {
            $messages .= xl('Hash does not match. Data integrity has been compromised.');
        } else {
            $messages .= xl('Document passed integrity check.');
        }
        $this->_state = false;
        $this->assign("messages", $messages);
        if ($d->couch_docid && $d->couch_revid) {
            //Removing the temporary file which is used to create the hash
            unlink($GLOBALS['OE_SITE_DIR'].'/documents/temp/'.$d->get_url());
        }
        return $this->view_action($patient_id, $document_id);
    }

    // Added by Rod for metadata update.
    //
    function update_action_process($patient_id = "", $document_id)
    {

        if ($_POST['process'] != "true") {
            die("process is '" . $_POST['process'] . "', expected 'true'");
            return;
        }

        $docdate = $_POST['docdate'];
        $docname = $_POST['docname'];
        $issue_id = $_POST['issue_id'];

        if (is_numeric($document_id)) {
            $messages = '';
            $d = new Document($document_id);
            $file_name = $d->get_url_file();
            if ($docname != '' &&
                 $docname != $file_name ) {
                // Ready to rename - check for relocation
                $old_url = $this->_check_relocation($d->get_url());
                $new_url = $this->_check_relocation($d->get_url(), null, $docname);
                $messages .= sprintf("%s -> %s<br>", $old_url, $new_url);
                if (rename($old_url, $new_url)) {
                    // check the "converted" file, and delete it if it exists. It will be regenerated when report is run
                    if (file_exists($old_url)) {
                        unlink($old_url);
                    }
                    $d->url = $new_url;
                    $d->persist();
                    $d->populate();
                    $messages .= xl('Document successfully renamed.')."<br>";
                } else {
                    $messages .= xl('The file could not be succesfully renamed, this error is usually related to permissions problems on the storage system.')."<br>";
                }
            }

            if (preg_match('/^\d\d\d\d-\d+-\d+$/', $docdate)) {
                $docdate = "'$docdate'";
            } else {
                $docdate = "NULL";
            }
            if (!is_numeric($issue_id)) {
                $issue_id = 0;
            }
            $couch_docid = $d->get_couch_docid();
            $couch_revid = $d->get_couch_revid();
            if ($couch_docid && $couch_revid) {
                $sql = "UPDATE documents SET docdate = $docdate, url = '".$_POST['docname']."', " .
                    "list_id = '$issue_id' " .
                    "WHERE id = '$document_id'";
                $this->tree->_db->Execute($sql);
            } else {
                $sql = "UPDATE documents SET docdate = $docdate, " .
                "list_id = '$issue_id' " .
                "WHERE id = '$document_id'";
                $this->tree->_db->Execute($sql);
            }
            $messages .= xl('Document date and issue updated successfully') . "<br>";
        }

        $this->_state = false;
        $this->assign("messages", $messages);
        return $this->view_action($patient_id, $document_id);
    }

    function list_action($patient_id = "")
    {
        $this->_last_node = null;
        $categories_list = $this->tree->_get_categories_array($patient_id);
        //print_r($categories_list);

        $menu  = new HTML_TreeMenu();
        $rnode = $this->_array_recurse($this->tree->tree, $categories_list);
        $menu->addItem($rnode);
        $treeMenu = new HTML_TreeMenu_DHTML($menu, array('images' => 'images', 'defaultClass' => 'treeMenuDefault'));
        $treeMenu_listbox  = new HTML_TreeMenu_Listbox($menu, array('linkTarget' => '_self'));
        $this->assign("tree_html", $treeMenu->toHTML());

        $is_new = isset($_GET['patient_name']) ? 1 : false;
        $place_hld = isset($_GET['patient_name']) ? filter_input(INPUT_GET, 'patient_name') : xl("Patient search or select.");
        $cur_pid = isset($_GET['patient_id']) ? filter_input(INPUT_GET, 'patient_id') : '';
        $used_msg = xl('Current patient unavailable here. Use Patient Documents');
        if ($cur_pid == '00') {
            $cur_pid = '0';
            $is_new = 1;
        }
        $this->assign('is_new', $is_new);
        $this->assign('place_hld', $place_hld);
        $this->assign('cur_pid', $cur_pid);
        $this->assign('used_msg', $used_msg);
        $this->assign('demo_pid', $_SESSION['pid']);

        $this->assign('GLOBALS', $GLOBALS);

        return $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_list.html");
    }

    /*	This is a recursive function to rename a file to something that doesn't already exist.
     *      Modified in version 3.2.0 to place a counter within the filename (previously was placed
     *      at end) to ensure documents opened correctly by external browser viewers. If the
     *      counter is at the end of the file, then will use it (to continue to work with older
     *      files), however all new counters will be placed within filenames.
     *
     *      Modified to only deal with base file name when renaming, to avoid issues with directory
     *      names with dots.
     */
    function _rename_file($fname, $self = false)
    {
        // Allow same routine for new file name check
        if (!file_exists($fname)) {
            return($fname);
        }

        $path = dirname($fname);
        $file = basename_international($fname);

        $fparts = explode(".", $file);
        switch (count($fparts)) {
            case 1:
                // Has a single node (base file name).  Create counter node with value 0
                $fparts[1] = '1';
                break;
            case 2:
                // If 2nd node is numeric, assume it is counter and add 1 else insert counter
                if (is_numeric($fparts[1])) {
                    $fparts[1] += 1;
                } else {
                    array_push($fparts, $fparts[1]);
                    $fparts[1] = '1';
                }
                break;
            default:
                // Multiple nodes
                $ix_end = count($fparts) - 1;
                if (is_numeric($fparts[$ix_end]) && !is_numeric($fparts[$ix_end - 1])) {
                    // Switch old style to new and check again
                    $wrk = $fparts[$ix_end - 1];
                    $fparts[$ix_end - 1] = $fparts[$ix_end];
                    $fparts[$ix_end] = $wrk;
                } else if (is_numeric($fparts[$ix_end - 1])) {
                    $fparts[$ix_end - 1] += 1;
                } else {
                    array_push($fparts, $fparts[$ix_end]);
                    $fparts[$ix_end] = '1';
                }
                break;
        }

        $fname = $path.DIRECTORY_SEPARATOR.join(".", $fparts);

        if (file_exists($fname)) {
            return $this->_rename_file($fname, true);
        } else {
            return($fname);
        }
    }

    function &_array_recurse($array, $categories = array())
    {
        if (!is_array($array)) {
            $array = array();
        }
        $node = &$this->_last_node;
        $current_node = &$node;
        $expandedIcon = 'folder-expanded.gif';
        foreach ($array as $id => $ar) {
            $icon = 'folder.gif';
            if (is_array($ar)  || !empty($id)) {
                if ($node == null) {
                    //echo "r:" . $this->tree->get_node_name($id) . "<br>";
                    $rnode = new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => false));
                    $this->_last_node = &$rnode;
                    $node = &$rnode;
                    $current_node = &$rnode;
                } else {
                    //echo "p:" . $this->tree->get_node_name($id) . "<br>";
                    $this->_last_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                    $current_node = &$this->_last_node;
                }

                $this->_array_recurse($ar, $categories);
            } else {
                if ($id === 0 && !empty($ar)) {
                    $info = $this->tree->get_node_info($id);
                  //echo "b:" . $this->tree->get_node_name($id) . "<br>";
                    $current_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $info['value'], 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                } else {
                    //there is a third case that is implicit here when title === 0 and $ar is empty, in that case we do not want to do anything
                    //this conditional tree could be more efficient but working with recursive trees makes my head hurt, TODO
                    if ($id !== 0 && is_object($node)) {
                      //echo "n:" . $this->tree->get_node_name($id) . "<br>";
                        $current_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                    }
                }
            }

            // If there are documents in this document category, then add their
            // attributes to the current node.
            $icon = "file3.png";
            if (is_array($categories[$id])) {
                foreach ($categories[$id] as $doc) {
                    $link = $this->_link("view") . "doc_id=" . $doc['document_id'] . "&";
          // If user has no access then there will be no link.
                    if (!acl_check_aco_spec($doc['aco_spec'])) {
                        $link = '';
                    }
                    if ($this->tree->get_node_name($id) == "CCR") {
                                $current_node->addItem(new HTML_TreeNode(array(
                        'text' => $doc['docdate'] . ' ' . basename_international($doc['url']),
                        'link' => $link,
                        'icon' => $icon,
                        'expandedIcon' => $expandedIcon,
                        'events' => array('Onclick' => "javascript:newwindow=window.open('ccr/display.php?type=CCR&doc_id=" . $doc['document_id'] . "','CCR');")
                                )));
                    } elseif ($this->tree->get_node_name($id) == "CCD") {
                                $current_node->addItem(new HTML_TreeNode(array(
                        'text' => $doc['docdate'] . ' ' . basename_international($doc['url']),
                        'link' => $link,
                        'icon' => $icon,
                        'expandedIcon' => $expandedIcon,
                        'events' => array('Onclick' => "javascript:newwindow=window.open('ccr/display.php?type=CCD&doc_id=" . $doc['document_id'] . "','CCD');")
                                )));
                    } else {
                                $current_node->addItem(new HTML_TreeNode(array(
                        'text' => $doc['docdate'] . ' ' . basename_international($doc['url']),
                        'link' => $link,
                        'icon' => $icon,
                        'expandedIcon' => $expandedIcon
                                )));
                    }
                }
            }
        }
        return $node;
    }

    //function for logging  the errors in writing file to CouchDB/Hard Disk
    function document_upload_download_log($patientid, $content)
    {
        $log_path = $GLOBALS['OE_SITE_DIR']."/documents/couchdb/";
        $log_file = 'log.txt';
        if (!is_dir($log_path)) {
            mkdir($log_path, 0777, true);
        }
        $LOG = fopen($log_path.$log_file, 'a');
        fwrite($LOG, $content);
        fclose($LOG);
    }

    function document_send($email, $body, $attfile, $pname)
    {
        if (empty($email)) {
            $this->assign("process_result", "Email could not be sent, the address supplied: '$email' was empty or invalid.");
            return;
        }

          $desc = "Please check the attached patient document.\n Content:".attr($body);
          $mail = new MyMailer();
          $from_name = $GLOBALS["practice_return_email_path"];
          $from =  $GLOBALS["practice_return_email_path"];
          $mail->AddReplyTo($from, $from_name);
          $mail->SetFrom($from, $from);
          $to = $email ;
        $to_name =$email;
          $mail->AddAddress($to, $to_name);
          $subject = "Patient documents";
          $mail->Subject = $subject;
          $mail->Body = $desc;
          $mail->AddAttachment($attfile);
        if ($mail->Send()) {
            $retstatus = "email_sent";
        } else {
            $email_status = $mail->ErrorInfo;
            //echo "EMAIL ERROR: ".$email_status;
            $retstatus =  "email_fail";
        }
    }

//place to hold optional code
//$first_node = array_keys($t->tree);
        //$first_node = $first_node[0];
        //$node1 = new HTML_TreeNode(array('text' => $t->get_node_name($first_node), 'link' => "test.php", 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => true), array('onclick' => "alert('foo'); return false", 'onexpand' => "alert('Expanded')"));

        //$this->_last_node = &$node1;

// Function to tag a document to an encounter.
    function tag_action_process($patient_id = "", $document_id)
    {
        if ($_POST['process'] != "true") {
            die("process is '" . text($_POST['process']) . "', expected 'true'");
            return;
        }

        // Create Encounter and Tag it.
        $event_date = date('Y-m-d H:i:s');
        $encounter_id = $_POST['encounter_id'];
        $encounter_check = $_POST['encounter_check'];
        $visit_category_id = $_POST['visit_category_id'];

        if (is_numeric($document_id)) {
            $messages = '';
            $d = new Document($document_id);
            $file_name = $d->get_url_file();
            if (!is_numeric($encounter_id)) {
                $encounter_id = 0;
            }

            $encounter_check = ( $encounter_check == 'on') ? 1 : 0;
            if ($encounter_check) {
                $provider_id = $_SESSION['authUserID'] ;

                // Get the logged in user's facility
                $facilityRow = sqlQuery("SELECT username, facility, facility_id FROM users WHERE id = ?", array("$provider_id"));
                $username = $facilityRow['username'];
                $facility = $facilityRow['facility'];
                $facility_id = $facilityRow['facility_id'];
                // Get the primary Business Entity facility to set as billing facility, if null take user's facility as billing facility
                $billingFacility = $this->facilityService->getPrimaryBusinessEntity();
                $billingFacilityID = ( $billingFacility['id'] ) ? $billingFacility['id'] : $facility_id;

                $conn = $GLOBALS['adodb']['db'];
                $encounter = $conn->GenID("sequences");
                $query = "INSERT INTO form_encounter SET
						date = ?,
						reason = ?,
						facility = ?,
						sensitivity = 'normal',
						pc_catid = ?,
						facility_id = ?,
						billing_facility = ?,
						provider_id = ?,
						pid = ?,
						encounter = ?";
                $bindArray = array($event_date,$file_name,$facility,$_POST['visit_category_id'],(int)$facility_id,(int)$billingFacilityID,(int)$provider_id,$patient_id,$encounter);
                $formID = sqlInsert($query, $bindArray);
                addForm($encounter, "New Patient Encounter", $formID, "newpatient", $patient_id, "1", date("Y-m-d H:i:s"), $username);
                $d->set_encounter_id($encounter);
                $this->image_result_indication($d->id, $encounter);
            } else {
                $d->set_encounter_id($encounter_id);
                $this->image_result_indication($d->id, $encounter_id);
            }
            $d->set_encounter_check($encounter_check);
            $d->persist();

            $messages .= xlt('Document tagged to Encounter successfully') . "<br>";
        }

        $this->_state = false;
        $this->assign("messages", $messages);

        return $this->view_action($patient_id, $document_id);
    }

    function image_procedure_action($patient_id = "", $document_id)
    {

        $img_procedure_id = $_POST['image_procedure_id'];
        $proc_code = $_POST['procedure_code'];

        if (is_numeric($document_id)) {
            $img_order  = sqlQuery("select * from procedure_order_code where procedure_order_id = ? and procedure_code = ? ", array($img_procedure_id,$proc_code));
            $img_report = sqlQuery("select * from procedure_report where procedure_order_id = ? and procedure_order_seq = ? ", array($img_procedure_id,$img_order['procedure_order_seq']));
            $img_report_id = !empty($img_report['procedure_report_id']) ? $img_report['procedure_report_id'] : 0;
            if ($img_report_id == 0) {
                $report_date = date('Y-m-d H:i:s');
                $img_report_id = sqlInsert("INSERT INTO procedure_report(procedure_order_id,procedure_order_seq,date_collected,date_report,report_status) values(?,?,?,?,'final')", array($img_procedure_id,$img_order['procedure_order_seq'],$img_order['date_collected'],$report_date));
            }

            $img_result = sqlQuery("select * from procedure_result where procedure_report_id = ? and document_id = ?", array($img_report_id,$document_id));
            if (empty($img_result)) {
                sqlInsert("INSERT INTO procedure_result(procedure_report_id,date,document_id,result_status) values(?,?,?,'final')", array($img_report_id,date('Y-m-d H:i:s'),$document_id));
            }

            $this->image_result_indication($document_id, 0, $img_procedure_id);
        }
        return $this->view_action($patient_id, $document_id);
    }

    function clear_procedure_tag_action($patient_id = "", $document_id)
    {
        if (is_numeric($document_id)) {
            sqlStatement("delete from procedure_result where document_id = ?", $document_id);
        }
        return $this->view_action($patient_id, $document_id);
    }

    function get_mapped_procedure($document_id)
    {
        $map = array();
        if (is_numeric($document_id)) {
            $map = sqlQuery("select poc.procedure_order_id,poc.procedure_code from procedure_result pres
						   inner join procedure_report pr on pr.procedure_report_id = pres.procedure_report_id
						   inner join procedure_order_code poc on (poc.procedure_order_id = pr.procedure_order_id and poc.procedure_order_seq = pr.procedure_order_seq)
						   inner join procedure_order po on po.procedure_order_id = poc.procedure_order_id
						   where pres.document_id = ?", array($document_id));
        }
        return $map;
    }

    function image_result_indication($doc_id, $encounter, $image_procedure_id = 0)
    {
        $doc_notes = sqlQuery("select note from notes where foreign_id = ?", array($doc_id));
        $narration = isset($doc_notes['note']) ? 'With Narration': 'Without Narration';

        if ($encounter != 0) {
            $ep = sqlQuery("select u.username as assigned_to from form_encounter inner join users u on u.id = provider_id where encounter = ?", array($encounter));
        } else if ($image_procedure_id != 0) {
            $ep = sqlQuery("select u.username as assigned_to from procedure_order inner join users u on u.id = provider_id where procedure_order_id = ?", array($image_procedure_id));
        } else {
            $ep = array('assigned_to' => $_SESSION['authUser']);
        }

        $encounter_provider = isset($ep['assigned_to']) ? $ep['assigned_to'] : $_SESSION['authUser'];
        $noteid = addPnote($_SESSION['pid'], 'New Image Report received '.$narration, 0, 1, 'Image Results', $encounter_provider, '', 'New', '');
        setGpRelation(1, $doc_id, 6, $noteid);
    }

/**  Function to accomodate the relocation of entire "documents" folder to another host or filesystem  **
 * Also usable for documents that may of been moved to different patients.
 *
 * @param string $url - Current url string from database.
 * @param string $new_pid - Include pid corrections to receive corrected url during move operation.
 * @param string $new_name - Include name corrections to receive corrected url during rename operation.
 *
 * @return string
 */
    function _check_relocation($url, $new_pid = null, $new_name = null)
    {
        //strip url of protocol handler
        $url = preg_replace("|^(.*)://|", "", $url);
        $fsnodes = explode(DIRECTORY_SEPARATOR, $url);
        while (current($fsnodes) != "documents") {
            array_shift($fsnodes);
        }
        if ($new_pid) {
            $fsnodes[1] = $new_pid;
        }
        if ($new_name) {
            $fsnodes[count($fsnodes)-1] = $new_name;
        }
        $url = $GLOBALS['OE_SITE_DIR'].DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $fsnodes);
        // Make sure the url is available after corrections
        if ($new_pid || $new_name) {
            $url = $this->_rename_file($url);
        }
        //Add full path and remaining nodes
        return $url;
    }

//clear encounter tag function
    function clear_encounter_tag_action($patient_id = "", $document_id)
    {
        if (is_numeric($document_id)) {
            sqlStatement("update documents set encounter_id='0' where foreign_id=? and id = ?", array($patient_id,$document_id));
        }
        return $this->view_action($patient_id, $document_id);
    }
}
