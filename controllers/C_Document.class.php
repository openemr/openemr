<?php

/**
 * C_Document.class.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../library/forms.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\PatientService;

class C_Document extends Controller
{

    var $template_mod;
    var $documents;
    var $document_categories;
    var $tree;
    var $_config;
    var $manual_set_owner = false; // allows manual setting of a document owner/service
    var $facilityService;
    var $patientService;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->facilityService = new FacilityService();
        $this->patientService = new PatientService();
        $this->documents = array();
        $this->template_mod = $template_mod;
        $this->assign("FORM_ACTION", $GLOBALS['webroot'] . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->assign("CURRENT_ACTION", $GLOBALS['webroot'] . "/controller.php?" . "document&");

        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

        $this->assign("IMAGES_STATIC_RELATIVE", $GLOBALS['images_static_relative']);

        //get global config options for this namespace
        $this->_config = $GLOBALS['oer_config']['documents'];

        $this->_args = array("patient_id" => $_GET['patient_id']);

        $this->assign("STYLE", $GLOBALS['style']);
        $t = new CategoryTree(1);
        //print_r($t->tree);
        $this->tree = $t;
        $this->Document = new Document();

        // Create a crypto object that will be used for for encryption/decryption
        $this->cryptoGen = new CryptoGen();
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
        if (!empty($dh)) {
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

        // duplicate template list for new template form editor sjp 05/20/2019
        // will call as module or individual template.
        $templatedir = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates';
        $templates_options = "<option value=''>-- " . xlt('Open Forms Module') . " --</option>";
        if (file_exists($templatedir)) {
            $dh = opendir($templatedir);
        }
        if ($dh) {
            $templateslist = array();
            while (false !== ($sfname = readdir($dh))) {
                if (substr($sfname, 0, 1) == '.') {
                    continue;
                }
                if (substr(strtolower($sfname), strlen($sfname) - 4) == '.tpl') {
                    $templateslist[$sfname] = $sfname;
                }
            }
            closedir($dh);
            ksort($templateslist);
            foreach ($templateslist as $sfname) {
                $optname = str_replace('_', ' ', basename($sfname, ".tpl"));
                $templates_options .= "<option value='" . attr($sfname) . "'>" . text($optname) . "</option>";
            }
        }
        $this->assign("TEMPLATES_LIST_PATIENT", $templates_options);

        $activity = $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_upload.html");
        $this->assign("activity", $activity);
        return $this->list_action($patient_id);
    }

    function zip_dicom_folder($study_name = null)
    {
        $zip = new ZipArchive();
        $zip_name = $GLOBALS['temporary_files_dir'] . "/" . $study_name;
        if ($zip->open($zip_name, (ZipArchive::CREATE | ZipArchive::OVERWRITE)) === true) {
            foreach ($_FILES['dicom_folder']['name'] as $i => $name) {
                $zfn = $GLOBALS['temporary_files_dir'] . "/" . $name;
                $fparts = pathinfo($name);
                if (empty($fparts['extension'])) {
                    // viewer requires lowercase.
                    $fparts['extension'] = "dcm";
                    $name = $fparts['filename'] . ".dcm";
                }
                if ($fparts['extension'] == "DCM") {
                    // viewer requires lowercase.
                    $fparts['extension'] = "dcm";
                    $name = $fparts['filename'] . ".dcm";
                }
                // required extension for viewer
                if ($fparts['extension'] != "dcm") {
                    continue;
                }
                move_uploaded_file($_FILES['dicom_folder']['tmp_name'][$i], $zfn);
                $zip->addFile($zfn, $name);
            }
            $zip->close();
        } else {
            return false;
        }
        $file_array['name'][] = $study_name;
        $file_array['type'][] = 'zip';
        $file_array['tmp_name'][] = $zip_name;
        $file_array['error'][] = '';
        $file_array['size'][] = filesize($zip_name);
        return $file_array;
    }

    //Upload multiple files on single click
    function upload_action_process()
    {

        // Collect a manually set owner if this has been set
        // Used when want to manually assign the owning user/service such as the Direct mechanism
        $non_HTTP_owner = false;
        if ($this->manual_set_owner) {
            $non_HTTP_owner = $this->manual_set_owner;
        }

        $couchDB = false;
        $harddisk = false;
        if ($GLOBALS['document_storage_method'] == 0) {
            $harddisk = true;
        }
        if ($GLOBALS['document_storage_method'] == 1) {
            $couchDB = true;
        }

        if ($_POST['process'] != "true") {
            return;
        }

        $doDecryption = false;
        $encrypted = $_POST['encrypted'] ?? false;
        $passphrase = $_POST['passphrase'] ?? '';
        if (
            !$GLOBALS['hide_document_encryption'] &&
            $encrypted && $passphrase
        ) {
            $doDecryption = true;
        }

        if (is_numeric($_POST['category_id'])) {
            $category_id = $_POST['category_id'];
        }

        $patient_id = 0;
        if (isset($_GET['patient_id']) && !$couchDB) {
            $patient_id = $_GET['patient_id'];
        } elseif (is_numeric($_POST['patient_id'])) {
            $patient_id = $_POST['patient_id'];
        }

        if (!empty($_FILES['dicom_folder']['name'][0])) {
            // let's zip um up then pass along new zip
            $study_name = $_POST['destination'] ? (trim($_POST['destination']) . ".zip") : 'DicomStudy.zip';
            $study_name =  preg_replace('/\s+/', '_', $study_name);
            $_POST['destination'] = "";
            $zipped = $this->zip_dicom_folder($study_name);
            if ($zipped) {
                $_FILES['file'] = $zipped;
            }
            // and off we go! just fall through and let routine
            // do its normal file processing..
        }

        $sentUploadStatus = array();
        if (count($_FILES['file']['name']) > 0) {
            $upl_inc = 0;

            foreach ($_FILES['file']['name'] as $key => $value) {
                $fname = $value;
                $error = "";
                if ($_FILES['file']['error'][$key] > 0 || empty($fname) || $_FILES['file']['size'][$key] == 0) {
                    $fname = $value;
                    if (empty($fname)) {
                        $fname = htmlentities("<empty>");
                    }
                    $error = xl("Error number") . ": " . $_FILES['file']['error'][$key] . " " . xl("occurred while uploading file named") . ": " . $fname . "\n";
                    if ($_FILES['file']['size'][$key] == 0) {
                        $error .= xl("The system does not permit uploading files of with size 0.") . "\n";
                    }
                } elseif ($GLOBALS['secure_upload'] && !isWhiteFile($_FILES['file']['tmp_name'][$key])) {
                    $error = xl("The system does not permit uploading files with MIME content type") . " - " . mime_content_type($_FILES['file']['tmp_name'][$key]) . ".\n";
                } else {
                    // Test for a zip of DICOM images
                    if (stripos($_FILES['file']['type'][$key], 'zip') !== false) {
                        $za = new ZipArchive();
                        $handler = $za->open($_FILES['file']['tmp_name'][$key]);
                        if ($handler) {
                            $mimetype = "application/dicom+zip";
                            for ($i = 0; $i < $za->numFiles; $i++) {
                                $stat = $za->statIndex($i);
                                $fp = $za->getStream($stat['name']);
                                if ($fp) {
                                    $head = fread($fp, 256);
                                    fclose($fp);
                                    if (strpos($head, 'DICM') === false) { // Fixed at offset 128. even one non DICOM makes zip invalid.
                                        $mimetype = "application/zip";
                                        break;
                                    }
                                    unset($head);
                                    // if here -then a DICOM
                                    $parts = pathinfo($stat['name']);
                                    if ($parts['extension'] != "dcm" || empty($parts['extension'])) { // required extension for viewer
                                        $new_name = $parts['filename'] . ".dcm";
                                        $za->renameIndex($i, $new_name);
                                        $za->renameName($parts['filename'], $new_name);
                                    }
                                } else { // Rarely here
                                    $mimetype = "application/zip";
                                    break;
                                }
                            }
                            $za->close();
                            if ($mimetype == "application/dicom+zip") {
                                $_FILES['file']['type'][$key] = $mimetype;
                                sleep(1); // Timing insurance in case of re-compression. Only acted on index so...!
                                $_FILES['file']['size'][$key] = filesize($_FILES['file']['tmp_name'][$key]); // file may have grown.
                            }
                        }
                    }
                    $tmpfile = fopen($_FILES['file']['tmp_name'][$key], "r");
                    $filetext = fread($tmpfile, $_FILES['file']['size'][$key]);
                    fclose($tmpfile);
                    if ($doDecryption) {
                        $filetext = $this->cryptoGen->decryptStandard($filetext, $passphrase);
                        if ($filetext === false) {
                            error_log("OpenEMR Error: Unable to decrypt a document since decryption failed.");
                            $filetext = "";
                        }
                    }
                    if ($_POST['destination'] != '') {
                        $fname = $_POST['destination'];
                    }
                    // set mime, test for single DICOM and assign extension if missing.
                    $mimetype = $_FILES['file']['type'][$key];
                    if (strpos($filetext, 'DICM') !== false) {
                        $mimetype = 'application/dicom';
                        $parts = pathinfo($fname);
                        if (!$parts['extension']) {
                            $fname .= '.dcm';
                        }
                    }
                    $d = new Document();
                    $rc = $d->createDocument(
                        $patient_id,
                        $category_id,
                        $fname,
                        $mimetype,
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
                $resp = $couch->retrieve_doc($couch_docid);
                $content = $resp->data;
                if ($content == '' && $GLOBALS['couchdb_log'] == 1) {
                    $log_content = date('Y-m-d H:i:s') . " ==> Retrieving document\r\n";
                    $log_content = date('Y-m-d H:i:s') . " ==> URL: " . $url . "\r\n";
                    $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Document Id: " . $couch_docid . "\r\n";
                    $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Revision Id: " . $couch_revid . "\r\n";
                    $log_content .= date('Y-m-d H:i:s') . " ==> Failed to fetch document content from CouchDB.\r\n";
                    //$log_content .= date('Y-m-d H:i:s')." ==> Will try to download file from HardDisk if exists.\r\n\r\n";
                    $this->document_upload_download_log($d->get_foreign_id(), $log_content);
                    die(xlt("File retrieval from CouchDB failed"));
                }
                // place it in a temporary file and will remove the file below after emailed
                $temp_couchdb_url = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/couch_' . date("YmdHis") . $d->get_url_file();
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
                for ($i = 0; $i < $d->get_path_depth(); $i++) {
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
            $pdetails = getPatientData($patient_id);
            $pname = $pdetails['fname'] . " " . $pdetails['lname'];
            $this->document_send($_POST['provide_email'], $_POST['note'], $url, $pname);
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

    function view_action(string $patient_id = null, $doc_id)
    {
        global $ISSUE_TYPES;

        require_once(dirname(__FILE__) . "/../library/lists.inc");

        $d = new Document($doc_id);
        $notes = $d->get_notes();

        $this->assign("csrf_token_form", CsrfUtils::collectCsrfToken());

        $this->assign("file", $d);
        $this->assign("web_path", $this->_link("retrieve") . "document_id=" . urlencode($d->get_id()) . "&");
        $this->assign("NOTE_ACTION", $this->_link("note"));
        $this->assign("MOVE_ACTION", $this->_link("move") . "document_id=" . urlencode($d->get_id()) . "&process=true");
        $this->assign("hide_encryption", $GLOBALS['hide_document_encryption']);
        $this->assign("assets_static_relative", $GLOBALS['assets_static_relative']);
        $this->assign("webroot", $GLOBALS['webroot']);

        // Added by Rod to support document delete:
        $delete_string = '';
        if (AclMain::aclCheckCore('patients', 'docs_rm')) {
            $delete_string = "<a href='' class='btn btn-danger' onclick='return deleteme(" . attr_js($d->get_id()) .
                ")'>" . xlt('Delete') . "</a>";
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
        $this->assign("TAG_ACTION", $this->_link("tag") . "document_id=" . urlencode($d->get_id()) . "&process=true");
        $encOptions = "<option value='0'>-- " . xlt('Select Encounter') . " --</option>";
        $result_docs = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe " .
            "LEFT JOIN openemr_postcalendar_categories ON fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? ORDER BY fe.date desc", array($patient_id));
        if (sqlNumRows($result_docs) > 0) {
            while ($row_result_docs = sqlFetchArray($result_docs)) {
                $sel_enc = ($row_result_docs['encounter'] == $d->get_encounter_id()) ? ' selected' : '';
                $encOptions .= "<option value='" . attr($row_result_docs['encounter']) . "' $sel_enc>" . text(oeFormatShortDate(date('Y-m-d', strtotime($row_result_docs['date'])))) . "-" . text(xl_appt_category($row_result_docs['pc_catname'])) . "</option>";
            }
        }
        $this->assign("ENC_LIST", $encOptions);

        //clear encounter tag
        if ($d->get_encounter_id() != 0) {
            $this->assign('clear_encounter_tag', $this->_link('clear_encounter_tag') . "document_id=" . urlencode($d->get_id()));
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
            $visit_category_list .= "<option value='" . attr($catid) . "'>" . text(xl_appt_category($crow['pc_catname'])) . "</option>\n";
        }
        $this->assign("VISIT_CATEGORY_LIST", $visit_category_list);

        $this->assign("notes", $notes);

        $this->assign("IMG_PROCEDURE_TAG_ACTION", $this->_link("image_procedure") . "document_id=" . urlencode($d->get_id()));
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
                $imgOptions .= "<option value='" . attr($row['procedure_order_id']) . "' data-code='" . attr($row['procedure_code']) . "' $sel_proc>" . text($row['procedure_name'] . ' - ' . $row['procedure_code']) . "</option>";
            }
        }

        $this->assign('IMAGE_PROCEDURE_LIST', $imgOptions);

        $this->assign('clear_procedure_tag', $this->_link('clear_procedure_tag') . "document_id=" . urlencode($d->get_id()));

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

    /**
     * Retrieve file from hard disk / CouchDB.
     * In case that file isn't download this function will return thumbnail image (if exist).
     * @param (boolean) $show_original - enable to show the original image (not thumbnail) in inline status.
     * @param (string) $context - given a special document scenario (e.g.: patient avatar, custom image viewer document, etc), the context can be set so that a switch statement can execute a custom strategy.
     * */
    function retrieve_action(string $patient_id = null, $document_id, $as_file = true, $original_file = true, $disable_exit = false, $show_original = false, $context = "normal")
    {
        $encrypted = $_POST['encrypted'] ?? false;
        $passphrase = $_POST['passphrase'] ?? '';
        $doEncryption = false;
        if (
            !$GLOBALS['hide_document_encryption'] &&
            $encrypted == "true" &&
            $passphrase
        ) {
            $doEncryption = true;
        }

            //controller function ruins booleans, so need to manually re-convert to booleans
        if ($as_file == "true") {
                $as_file = true;
        } elseif ($as_file == "false") {
                $as_file = false;
        }
        if ($original_file == "true") {
                $original_file = true;
        } elseif ($original_file == "false") {
                $original_file = false;
        }
        if ($disable_exit == "true") {
                $disable_exit = true;
        } elseif ($disable_exit == "false") {
                $disable_exit = false;
        }
        if ($show_original == "true") {
            $show_original = true;
        } elseif ($show_original == "false") {
            $show_original = false;
        }

        switch ($context) {
            case "patient_picture":
                $document_id = $this->patientService->getPatientPictureDocumentId($patient_id);
                break;
        }

        $d = new Document($document_id);
        $url =  $d->get_url();
        $th_url = $d->get_thumb_url();

        $storagemethod = $d->get_storagemethod();
        $couch_docid = $d->get_couch_docid();
        $couch_revid = $d->get_couch_revid();

        if ($couch_docid && $couch_revid && $original_file) {
            // standard case for collecting a document from couchdb
            $couch = new CouchDB();
            $resp = $couch->retrieve_doc($couch_docid);
            //Take thumbnail file when is not null and file is presented online
            if (!$as_file && !is_null($th_url) && !$show_original) {
                $content = $resp->th_data;
            } else {
                $content = $resp->data;
            }
            if ($content == '' && $GLOBALS['couchdb_log'] == 1) {
                $log_content = date('Y-m-d H:i:s') . " ==> Retrieving document\r\n";
                $log_content = date('Y-m-d H:i:s') . " ==> URL: " . $url . "\r\n";
                $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Document Id: " . $couch_docid . "\r\n";
                $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Revision Id: " . $couch_revid . "\r\n";
                $log_content .= date('Y-m-d H:i:s') . " ==> Failed to fetch document content from CouchDB.\r\n";
                $log_content .= date('Y-m-d H:i:s') . " ==> Will try to download file from HardDisk if exists.\r\n\r\n";
                $this->document_upload_download_log($d->get_foreign_id(), $log_content);
                die(xlt("File retrieval from CouchDB failed"));
            }
            if ($d->get_encrypted() == 1) {
                $filetext = $this->cryptoGen->decryptStandard($content, null, 'database');
            } else {
                $filetext = base64_decode($content);
            }
            if ($disable_exit == true) {
                return $filetext;
            }
            header('Content-Description: File Transfer');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            if ($doEncryption) {
                $ciphertext = $this->cryptoGen->encryptStandard($filetext, $passphrase);
                header('Content-Disposition: attachment; filename="' . "/encrypted_aes_" . $d->get_name() . '"');
                header("Content-Type: application/octet-stream");
                header("Content-Length: " . strlen($ciphertext));
                echo $ciphertext;
            } else {
                header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . $d->get_name() . "\"");
                header("Content-Type: " . $d->get_mimetype());
                header("Content-Length: " . strlen($filetext));
                echo $filetext;
            }
            exit;//exits only if file download from CouchDB is successfull.
        }
        if ($couch_docid && $couch_revid) {
            //special case when retrieving a document from couchdb that has been converted to a jpg and not directly referenced in openemr documents table
            //try to convert it if it has not yet been converted
            //first, see if the converted jpg already exists
            $couch = new CouchDB();
            $resp = $couch->retrieve_doc("converted_" . $couch_docid);
            $content = $resp->data;
            if ($content == '') {
                //create the converted jpg
                $couchM = new CouchDB();
                $respM = $couchM->retrieve_doc($couch_docid);
                if ($d->get_encrypted() == 1) {
                    $contentM = $this->cryptoGen->decryptStandard($respM->data, null, 'database');
                } else {
                    $contentM = base64_decode($respM->data);
                }
                if ($contentM == '' && $GLOBALS['couchdb_log'] == 1) {
                    $log_content = date('Y-m-d H:i:s') . " ==> Retrieving document\r\n";
                    $log_content = date('Y-m-d H:i:s') . " ==> URL: " . $url . "\r\n";
                    $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Document Id: " . $couch_docid . "\r\n";
                    $log_content .= date('Y-m-d H:i:s') . " ==> CouchDB Revision Id: " . $couch_revid . "\r\n";
                    $log_content .= date('Y-m-d H:i:s') . " ==> Failed to fetch document content from CouchDB.\r\n";
                    $log_content .= date('Y-m-d H:i:s') . " ==> Will try to download file from HardDisk if exists.\r\n\r\n";
                    $this->document_upload_download_log($d->get_foreign_id(), $log_content);
                    die(xlt("File retrieval from CouchDB failed"));
                }
                // place the from-file into a temporary file
                $from_file_tmp_name = tempnam($GLOBALS['temporary_files_dir'], "oer");
                file_put_contents($from_file_tmp_name, $contentM);
                // prepare a temporary file for the to-file
                $to_file_tmp = tempnam($GLOBALS['temporary_files_dir'], "oer");
                $to_file_tmp_name = $to_file_tmp . ".jpg";
                // convert file to jpg
                exec("convert -density 200 " . escapeshellarg($from_file_tmp_name) . " -append -resize 850 " . escapeshellarg($to_file_tmp_name));
                // remove from tmp file
                unlink($from_file_tmp_name);
                // save the to-file if a to-file was created in above convert call
                if (is_file($to_file_tmp_name)) {
                    $couchI = new CouchDB();
                    if ($d->get_encrypted() == 1) {
                        $document = $this->cryptoGen->encryptStandard(file_get_contents($to_file_tmp_name), null, 'database');
                    } else {
                        $document = base64_encode(file_get_contents($to_file_tmp_name));
                    }
                    $couchI->save_doc(['_id' => "converted_" . $couch_docid, 'data' => $document]);
                    // remove to tmp files
                    unlink($to_file_tmp);
                    unlink($to_file_tmp_name);
                } else {
                    error_log("ERROR: Document '" . errorLogEscape($d->get_name()) . "' cannot be converted to JPEG. Perhaps ImageMagick is not installed?");
                }
                // now collect the newly created converted jpg
                $couchF = new CouchDB();
                $respF = $couchF->retrieve_doc("converted_" . $couch_docid);
                if ($d->get_encrypted() == 1) {
                    $content = $this->cryptoGen->decryptStandard($respF->data, null, 'database');
                } else {
                    $content = base64_decode($respF->data);
                }
            } else {
                // decrypt/decode when converted jpg already exists
                if ($d->get_encrypted() == 1) {
                    $content = $this->cryptoGen->decryptStandard($resp->data, null, 'database');
                } else {
                    $content = base64_decode($resp->data);
                }
            }
            $filetext = $content;
            if ($disable_exit == true) {
                return $filetext;
            }
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . $d->get_name() . "\"");
            header("Content-Type: image/jpeg");
            header("Content-Length: " . strlen($filetext));
            echo $filetext;
            exit;
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
        for ($i = 0; $i < $d->get_path_depth(); $i++) {
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
                if ($d->get_encrypted() == 1) {
                    $filetext = $this->cryptoGen->decryptStandard(file_get_contents($url), null, 'database');
                } else {
                    $filetext = file_get_contents($url);
                }
                if ($disable_exit == true) {
                    return $filetext;
                }
                header('Content-Description: File Transfer');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                if ($doEncryption) {
                    $ciphertext = $this->cryptoGen->encryptStandard($filetext, $passphrase);
                    header('Content-Disposition: attachment; filename="' . "/encrypted_aes_" . $d->get_name() . '"');
                    header("Content-Type: application/octet-stream");
                    header("Content-Length: " . strlen($ciphertext));
                    echo $ciphertext;
                } else {
                    header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . $d->get_name() . "\"");
                    header("Content-Type: " . $d->get_mimetype());
                    header("Content-Length: " . strlen($filetext));
                    echo $filetext;
                }
                exit;
            } else {
                //special case when retrieving a document that has been converted to a jpg and not directly referenced in database
                //try to convert it if it has not yet been converted
                $originalUrl = $url;
                if (strrpos(basename_international($url), '.') === false) {
                    $convertedFile = basename_international($url) . '_converted.jpg';
                } else {
                    $convertedFile = substr(basename_international($url), 0, strrpos(basename_international($url), '.')) . '_converted.jpg';
                }
                $url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $convertedFile;
                if (!is_file($url)) {
                    if ($d->get_encrypted() == 1) {
                        // decrypt the from-file into a temporary file
                        $from_file_unencrypted = $this->cryptoGen->decryptStandard(file_get_contents($originalUrl), null, 'database');
                        $from_file_tmp_name = tempnam($GLOBALS['temporary_files_dir'], "oer");
                        file_put_contents($from_file_tmp_name, $from_file_unencrypted);
                        // prepare a temporary file for the unencrypted to-file
                        $to_file_tmp = tempnam($GLOBALS['temporary_files_dir'], "oer");
                        $to_file_tmp_name = $to_file_tmp . ".jpg";
                        // convert file to jpg
                        exec("convert -density 200 " . escapeshellarg($from_file_tmp_name) . " -append -resize 850 " . escapeshellarg($to_file_tmp_name));
                        // remove unencrypted tmp file
                        unlink($from_file_tmp_name);
                        // make the encrypted to-file if a to-file was created in above convert call
                        if (is_file($to_file_tmp_name)) {
                            $to_file_encrypted = $this->cryptoGen->encryptStandard(file_get_contents($to_file_tmp_name), null, 'database');
                            file_put_contents($url, $to_file_encrypted);
                            // remove unencrypted tmp files
                            unlink($to_file_tmp);
                            unlink($to_file_tmp_name);
                        }
                    } else {
                        // convert file to jpg
                        exec("convert -density 200 " . escapeshellarg($originalUrl) . " -append -resize 850 " . escapeshellarg($url));
                    }
                }
                if (is_file($url)) {
                    if ($d->get_encrypted() == 1) {
                        $filetext = $this->cryptoGen->decryptStandard(file_get_contents($url), null, 'database');
                    } else {
                        $filetext = file_get_contents($url);
                    }
                } else {
                    $filetext = '';
                    error_log("ERROR: Document '" . errorLogEscape(basename_international($url)) . "' cannot be converted to JPEG. Perhaps ImageMagick is not installed?");
                }
                if ($disable_exit == true) {
                    return $filetext;
                }
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . $d->get_name() . "\"");
                header("Content-Type: image/jpeg");
                header("Content-Length: " . strlen($filetext));
                echo $filetext;
                exit;
            }
        }
    }

    function move_action_process(string $patient_id = null, $document_id)
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $messages = '';

        $new_category_id = $_POST['new_category_id'];
        $new_patient_id = $_POST['new_patient_id'];

        //move to new category
        if (is_numeric($new_category_id) && is_numeric($document_id)) {
            $sql = "UPDATE categories_to_documents set category_id = ? where document_id = ?";
            $messages .= xl('Document moved to new category', '', '', ' \'') . $this->tree->_id_name[$new_category_id]['name']  . xl('successfully.', '', '\' ') . "\n";
            //echo $sql;
            $this->tree->_db->Execute($sql, [$new_category_id, $document_id]);
        }

        //move to new patient
        if (is_numeric($new_patient_id) && is_numeric($document_id)) {
            $d = new Document($document_id);
            $sql = "SELECT pid from patient_data where pid = ?";
            $result = $d->_db->Execute($sql, [$new_patient_id]);

            if (!$result || $result->EOF) {
                //patient id does not exist
                $messages .= xl('Document could not be moved to patient id', '', '', ' \'') . $new_patient_id  . xl('because that id does not exist.', '', '\' ') . "\n";
            } else {
                $changefailed = !$d->change_patient($new_patient_id);

                $this->_state = false;
                if (!$changefailed) {
                    $messages .= xl('Document moved to patient id', '', '', ' \'') . $new_patient_id  . xl('successfully.', '', '\' ') . "\n";
                } else {
                    $messages .= xl('Document moved to patient id', '', '', ' \'') . $new_patient_id  . xl('Failed.', '', '\' ') . "\n";
                }
                $this->assign("messages", $messages);
                return $this->list_action($patient_id);
            }
        }

        $this->_state = false;
        $this->assign("messages", $messages);
        return $this->view_action($patient_id, $document_id);
    }

    function validate_action_process(string $patient_id = null, $document_id)
    {

        $d = new Document($document_id);
        if ($d->couch_docid && $d->couch_revid) {
            $file_path = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/';
            $url = $file_path . $d->get_url();
            $couch = new CouchDB();
            $resp = $couch->retrieve_doc($d->couch_docid);
            if ($d->get_encrypted() == 1) {
                $content = $this->cryptoGen->decryptStandard($resp->data, null, 'database');
            } else {
                $content = base64_decode($resp->data);
            }
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
            for ($i = 0; $i < $d->get_path_depth(); $i++) {
                $from_pathname_array[] = array_pop($from_all);
            }
                $from_pathname_array = array_reverse($from_pathname_array);
                $from_pathname = implode("/", $from_pathname_array);
                $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;
            if (file_exists($temp_url)) {
                $url = $temp_url;
            }

            if ($_POST['process'] != "true") {
                die("process is '" . text($_POST['process']) . "', expected 'true'");
                return;
            }

            if ($d->get_encrypted() == 1) {
                $content = $this->cryptoGen->decryptStandard(file_get_contents($url), null, 'database');
            } else {
                $content = file_get_contents($url);
            }
        }

        if (!empty($d->get_hash()) && (strlen($d->get_hash()) < 50)) {
            // backward compatibility for documents that were hashed prior to OpenEMR 6.0.0
            $current_hash = sha1($content);
        } else {
            $current_hash = hash('sha3-512', $content);
        }
        $messages = xl('Current Hash') . ": " . $current_hash . " | ";
        $messages .= xl('Stored Hash') . ": " . $d->get_hash();
        if ($d->get_hash() == '') {
            $d->hash = $current_hash;
            $d->persist();
            $d->populate();
            $messages .= xl('Hash did not exist for this file. A new hash was generated.');
        } elseif ($current_hash != $d->get_hash()) {
            $messages .= xl('Hash does not match. Data integrity has been compromised.');
        } else {
            $messages = xl('Document passed integrity check.') . ' | ' . $messages;
        }
        $this->_state = false;
        $this->assign("messages", $messages);
        return $this->view_action($patient_id, $document_id);
    }

    // Added by Rod for metadata update.
    //
    function update_action_process(string $patient_id = null, $document_id)
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
            $file_name = $d->get_name();
            if (
                $docname != '' &&
                 $docname != $file_name
            ) {
                // Rename
                $d->set_name($docname);
                $d->persist();
                $d->populate();
                $messages .= xl('Document successfully renamed.') . "<br />";
            }

            if (preg_match('/^\d\d\d\d-\d+-\d+$/', $docdate)) {
                $docdate = "$docdate";
            } else {
                $docdate = "NULL";
            }
            if (!is_numeric($issue_id)) {
                $issue_id = 0;
            }
            $couch_docid = $d->get_couch_docid();
            $couch_revid = $d->get_couch_revid();
            if ($couch_docid && $couch_revid) {
                $sql = "UPDATE documents SET docdate = ?, url = ?, list_id = ? WHERE id = ?";
                $this->tree->_db->Execute($sql, [$docdate, $_POST['docname'], $issue_id, $document_id]);
            } else {
                $sql = "UPDATE documents SET docdate = ?, list_id = ? WHERE id = ?";
                $this->tree->_db->Execute($sql, [$docdate, $issue_id, $document_id]);
            }
            $messages .= xl('Document date and issue updated successfully') . "<br />";
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
        $treeMenu = new HTML_TreeMenu_DHTML($menu, array('images' => 'public/images', 'defaultClass' => 'treeMenuDefault'));
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
        $this->assign('demo_pid', ($_SESSION['pid'] ?? null));

        return $this->fetch($GLOBALS['template_dir'] . "documents/" . $this->template_mod . "_list.html");
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
                    //echo "r:" . $this->tree->get_node_name($id) . "<br />";
                    $rnode = new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => false));
                    $this->_last_node = &$rnode;
                    $node = &$rnode;
                    $current_node = &$rnode;
                } else {
                    //echo "p:" . $this->tree->get_node_name($id) . "<br />";
                    $this->_last_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                    $current_node = &$this->_last_node;
                }

                $this->_array_recurse($ar, $categories);
            } else {
                if ($id === 0 && !empty($ar)) {
                    $info = $this->tree->get_node_info($id);
                  //echo "b:" . $this->tree->get_node_name($id) . "<br />";
                    $current_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $info['value'], 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                } else {
                    //there is a third case that is implicit here when title === 0 and $ar is empty, in that case we do not want to do anything
                    //this conditional tree could be more efficient but working with recursive trees makes my head hurt, TODO
                    if ($id !== 0 && is_object($node)) {
                      //echo "n:" . $this->tree->get_node_name($id) . "<br />";
                        $current_node = &$node->addItem(new HTML_TreeNode(array("id" => $id, 'text' => $this->tree->get_node_name($id), 'link' => $this->_link("upload") . "parent_id=" . $id . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                    }
                }
            }

            // If there are documents in this document category, then add their
            // attributes to the current node.
            $icon = "file3.png";
            if (!empty($categories[$id]) && is_array($categories[$id])) {
                foreach ($categories[$id] as $doc) {
                    $link = $this->_link("view") . "doc_id=" . urlencode($doc['document_id']) . "&";
          // If user has no access then there will be no link.
                    if (!AclMain::aclCheckAcoSpec($doc['aco_spec'])) {
                        $link = '';
                    }
                    if ($this->tree->get_node_name($id) == "CCR") {
                        $current_node->addItem(new HTML_TreeNode(array(
                            'text' => oeFormatShortDate($doc['docdate']) . ' ' . $doc['document_name'] . '-' . $doc['document_id'],
                            'link' => $link,
                            'icon' => $icon,
                            'expandedIcon' => $expandedIcon,
                            'events' => array('Onclick' => "javascript:newwindow=window.open('ccr/display.php?type=CCR&doc_id=" . attr_url($doc['document_id']) . "','_blank');")
                        )));
                    } elseif ($this->tree->get_node_name($id) == "CCD") {
                        $current_node->addItem(new HTML_TreeNode(array(
                            'text' => oeFormatShortDate($doc['docdate']) . ' ' . $doc['document_name'] . '-' . $doc['document_id'],
                            'link' => $link,
                            'icon' => $icon,
                            'expandedIcon' => $expandedIcon,
                            'events' => array('Onclick' => "javascript:newwindow=window.open('ccr/display.php?type=CCD&doc_id=" . attr_url($doc['document_id']) . "','_blank');")
                        )));
                    } else {
                        $current_node->addItem(new HTML_TreeNode(array(
                            'text' => oeFormatShortDate($doc['docdate']) . ' ' . $doc['document_name'] . '-' . $doc['document_id'],
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
        $log_path = $GLOBALS['OE_SITE_DIR'] . "/documents/couchdb/";
        $log_file = 'log.txt';
        if (!is_dir($log_path)) {
            mkdir($log_path, 0777, true);
        }

        $LOG = file_get_contents($log_path . $log_file);

        if ($this->cryptoGen->cryptCheckStandard($LOG)) {
            $LOG = $this->cryptoGen->decryptStandard($LOG, null, 'database');
        }

        $LOG .= $content;

        if (!empty($LOG)) {
            if ($GLOBALS['drive_encryption']) {
                $LOG = $this->cryptoGen->encryptStandard($LOG, null, 'database');
            }
            file_put_contents($log_path . $log_file, $LOG);
        }
    }

    function document_send($email, $body, $attfile, $pname)
    {
        if (empty($email)) {
            $this->assign("process_result", "Email could not be sent, the address supplied: '$email' was empty or invalid.");
            return;
        }

          $desc = "Please check the attached patient document.\n Content:" . $body;
          $mail = new MyMailer();
          $from_name = $GLOBALS["practice_return_email_path"];
          $from =  $GLOBALS["practice_return_email_path"];
          $mail->AddReplyTo($from, $from_name);
          $mail->SetFrom($from, $from);
          $to = $email ;
        $to_name = $email;
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
    function tag_action_process(string $patient_id = null, $document_id)
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

            $messages .= xlt('Document tagged to Encounter successfully') . "<br />";
        }

        $this->_state = false;
        $this->assign("messages", $messages);

        return $this->view_action($patient_id, $document_id);
    }

    function image_procedure_action(string $patient_id = null, $document_id)
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
                sqlStatement("INSERT INTO procedure_result(procedure_report_id,date,document_id,result_status) values(?,?,?,'final')", array($img_report_id,date('Y-m-d H:i:s'),$document_id));
            }

            $this->image_result_indication($document_id, 0, $img_procedure_id);
        }
        return $this->view_action($patient_id, $document_id);
    }

    function clear_procedure_tag_action(string $patient_id = null, $document_id)
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
        $narration = isset($doc_notes['note']) ? 'With Narration' : 'Without Narration';

        if ($encounter != 0) {
            $ep = sqlQuery("select u.username as assigned_to from form_encounter inner join users u on u.id = provider_id where encounter = ?", array($encounter));
        } elseif ($image_procedure_id != 0) {
            $ep = sqlQuery("select u.username as assigned_to from procedure_order inner join users u on u.id = provider_id where procedure_order_id = ?", array($image_procedure_id));
        } else {
            $ep = array('assigned_to' => $_SESSION['authUser']);
        }

        $encounter_provider = isset($ep['assigned_to']) ? $ep['assigned_to'] : $_SESSION['authUser'];
        $noteid = addPnote($_SESSION['pid'], 'New Image Report received ' . $narration, 0, 1, 'Image Results', $encounter_provider, '', 'New', '');
        setGpRelation(1, $doc_id, 6, $noteid);
    }

//clear encounter tag function
    function clear_encounter_tag_action(string $patient_id = null, $document_id)
    {
        if (is_numeric($document_id)) {
            sqlStatement("update documents set encounter_id='0' where foreign_id=? and id = ?", array($patient_id,$document_id));
        }
        return $this->view_action($patient_id, $document_id);
    }
}
