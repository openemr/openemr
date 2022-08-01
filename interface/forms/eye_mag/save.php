<?php

/**
 * forms/eye_mag/save.php
 *
 * This saves the submitted data.
 *  Forms: new and updates
 *  User preferences for displaying the form as the user desires.
 *    Each time a form is used, layout choices auto-change preferences.
 *  Retrieves old records so the user can flip through old values within this form,
 *    ideally with the intent that the old data can be carried forward.
 *    Yeah, gotta write that carry forward stuff yet.  Next week it'll be done?
 *  HTML5 Canvas images the user draws.
 *    For now we have one image per section
 *    I envision a user definable image they can upload to draw on and name such as
 *    A face image to draw injectable location/dosage for fillers or botulinum toxins.
 *    Ideally this concept when it comes to fruition will serve as a basis for any specialty image form
 *    to be used.  Upload image, drop widget and save it...
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2016- Raymond Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$table_name = "form_eye_mag";
$form_name = "eye_mag";
$form_folder = "eye_mag";


require_once(__DIR__ . "/../../globals.php");

require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("php/" . $form_name . "_functions.php");
require_once($srcdir . "/../controllers/C_Document.class.php");
require_once($srcdir . "/documents.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");

use Mpdf\Mpdf;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Logging\EventAuditLogger;

$returnurl = 'encounter_top.php';

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
}

if (!($id ?? '')) {
    $id = $_REQUEST['pid'];
}

$encounter = $_REQUEST['encounter'] ?? '';

$AJAX_PREFS = $_REQUEST['AJAX_PREFS'] ?? '';
if ($encounter == "" && !$id && !$AJAX_PREFS && (($_REQUEST['mode'] != "retrieve") or ($_REQUEST['mode'] == "show_PDF"))) {
    echo "Sorry Charlie..."; //should lead to a database of errors for explanation.
    exit;
}

/**
 * Save/update the preferences
 */
if ($_REQUEST['AJAX_PREFS'] ?? '') {
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
                VALUES
                ('PREFS','VA','Vision',?,'RS','51',?,'1')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_VA']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
                VALUES
                ('PREFS','W','Current Rx',?,'W','52',?,'2')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_W']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
                VALUES
                ('PREFS','W_width','Detailed Rx',?,'W_width','80',?,'100')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_W_width']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','MR','Manifest Refraction',?,'MR','53',?,'3')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_MR']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
                VALUES
                ('PREFS','MR_width','Detailed MR',?,'MR_width','81',?,'110')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_W_width']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','CR','Cycloplegic Refraction',?,'CR','54',?,'4')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_CR']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','CTL','Contact Lens',?,'CTL','55',?,'5')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_CTL']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS', 'VAX', 'Visual Acuities', ?, 'VAX','65', ?,'15')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_VAX']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS', 'RXHX', 'Prior Refractions', ?, 'RXHX','65', ?,'115')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_RXHX']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','ADDITIONAL','Additional Data Points',?,'ADDITIONAL','56',?,'6')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_ADDITIONAL']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','CLINICAL','CLINICAL',?,'CLINICAL','57',?,'7')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_CLINICAL']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','IOP','Intraocular Pressure',?,'IOP','67',?,'17')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_IOP']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','EXAM','EXAM',?,'EXAM','58',?,'8')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_EXAM']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','CYLINDER','CYL',?,'CYL','59',?,'9')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_CYL']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','HPI_VIEW','HPI View',?,'HPI_VIEW','60',?,'10')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_HPI_VIEW']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','EXT_VIEW','External View',?,'EXT_VIEW','66',?,'16')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_EXT_VIEW']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','ANTSEG_VIEW','Anterior Segment View',?,'ANTSEG_VIEW','61',?,'11')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_ANTSEG_VIEW']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','RETINA_VIEW','Retina View',?,'RETINA_VIEW','62',?,'12')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_RETINA_VIEW']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','NEURO_VIEW','Neuro View',?,'NEURO_VIEW','63',?,'13')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_NEURO_VIEW']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','ACT_VIEW','ACT View',?,'ACT_VIEW','64',?,'14')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_ACT_VIEW']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','ACT_SHOW','ACT Show',?,'ACT_SHOW','65',?,'15')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_ACT_SHOW']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','HPI_RIGHT','HPI DRAW',?,'HPI_RIGHT','70',?,'16')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_HPI_RIGHT']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','PMH_RIGHT','PMH DRAW',?,'PMH_RIGHT','71',?,'17')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_PMH_RIGHT']));
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','EXT_RIGHT','EXT DRAW',?,'EXT_RIGHT','72',?,'18')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_EXT_RIGHT']));
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','ANTSEG_RIGHT','ANTSEG DRAW',?,'ANTSEG_RIGHT','73',?,'19')";
    $result = sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_ANTSEG_RIGHT']));

    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','RETINA_RIGHT','RETINA DRAW',?,'RETINA_RIGHT','74',?,'20')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_RETINA_RIGHT']));
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','NEURO_RIGHT','NEURO DRAW',?,'NEURO_RIGHT','75',?,'21')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_NEURO_RIGHT']));
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','IMPPLAN_RIGHT','IMPPLAN DRAW',?,'IMPPLAN_RIGHT','76',?,'22')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_IMPPLAN_RIGHT']));
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','PANEL_RIGHT','PMSFH Panel',?,'PANEL_RIGHT','77',?,'23')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_PANEL_RIGHT']));
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','KB_VIEW','KeyBoard View',?,'KB_VIEW','78',?,'24')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_KB']));
    $query = "REPLACE INTO " . $table_name . "_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,GOVALUE,ordering)
              VALUES
              ('PREFS','TOOLTIPS','Toggle Tooltips',?,'TOOLTIPS','79',?,'25')";
    sqlQuery($query, array($_SESSION['authUserID'], $_REQUEST['PREFS_TOOLTIPS']));

    // These settings are sticky user preferences linked to a given page.
// Could do ALL preferences this way instead of the modified extract above...
// mdsupport - user_settings prefix
    $uspfx = "EyeFormSettings_";
    $setting_tabs_left = prevSetting($uspfx, 'setting_tabs_left', 'setting_tabs_left', '0');
    $setting_HPI = prevSetting($uspfx, 'setting_HPI', 'setting_HPI', '1');
    $setting_PMH = prevSetting($uspfx, 'setting_PMH', 'setting_PMH', '1');
    $setting_ANTSEG = prevSetting($uspfx, 'setting_ANTSEG', 'setting_ANTSEG', '1');
    $setting_POSTSEG = prevSetting($uspfx, 'setting_POSTSEG', 'setting_POSTSEG', '1');
    $setting_EXT = prevSetting($uspfx, 'setting_EXT', 'setting_EXT', '1');
    $setting_NEURO = prevSetting($uspfx, 'setting_NEURO', 'setting_NEURO', '1');
    $setting_IMPPLAN = prevSetting($uspfx, 'setting_IMPPLAN', 'setting_IMPPLAN', '1');
}

/**
 * ADD ANY NEW PREFERENCES above, and as a hidden field in the body.
 */

/** <!-- End Preferences --> **/

/**
 * Create, update or retrieve a form and its values
 */
if (!$pid) {
    $pid = $_SESSION['pid'];
}

$userauthorized = $_SESSION['userauthorized'];
if ($encounter == "") {
    $encounter = date("Ymd");
}

$form_id = $_REQUEST['form_id'] ?? '';
$zone = $_REQUEST['zone'] ?? '';

$providerID = findProvider($pid, $encounter);
if ($providerID == '0') {
    $providerID = $userauthorized;//who is the default provider?
}

// The form is submitted to be updated or saved in some way.
// Give each instance of a form a uniqueID.  If the form has no owner, update DB with this uniqueID.
// If the DB shows a uniqueID ie. an owner, and the save request uniqueID does not = the uniqueID in the DB,
// ask if the new user wishes to take ownership?
// If yes, any other's attempt to save fields/form are denied and the return code says you are not the owner...
if (($_REQUEST['unlock'] ?? null) === '1') {
    // we are releasing the form, by closing the page or clicking on ACTIVE FORM, so unlock it.
    // if it's locked and they own it ($REQUEST[LOCKEDBY] == LOCKEDBY), they can unlock it
    $query = "SELECT LOCKED,LOCKEDBY,LOCKEDDATE from form_eye_locking WHERE ID=?";
    $lock = sqlQuery($query, array($form_id));
    if (($lock['LOCKED'] > '')) { //&& ($_REQUEST['LOCKEDBY'] == $lock['LOCKEDBY'])) {
        $query = "update form_eye_locking set LOCKED='',LOCKEDBY='' where id=?";
        sqlQuery($query, array($form_id));
    }

    exit;
} elseif (($_REQUEST['acquire_lock'] ?? null) === "1") {
    //we are taking over the form's active state, others will go read-only
    $query = "UPDATE form_eye_locking set LOCKED='1',LOCKEDBY=? where id=?";//" and LOCKEDBY=?";
    $result = sqlQuery($query, array($_REQUEST['uniqueID'], $form_id ));
    $query = "SELECT LOCKEDDATE from form_eye_locking WHERE ID=?";
    $lock = sqlQuery($query, array($form_id));
    echo $lock['LOCKEDDATE'];

    exit;
} else {
    $query = "SELECT LOCKED,LOCKEDBY,LOCKEDDATE from form_eye_locking WHERE ID=?";
    $lock = sqlQuery($query, array($form_id));
    if (($lock['LOCKED'] ?? '') && ($_REQUEST['uniqueID'] != $lock['LOCKEDBY'])) {
        // This session not the owner or it is not new so it is locked
        // Did the user send a demand to take ownership?
        if ($lock['LOCKEDBY'] != $_REQUEST['ownership']) {
            //tell them they are locked out by another user now
            echo "Code 400";
            // or return a JSON encoded string with current LOCK ID?
            // echo "Sorry Charlie, you get nothing since this is locked...  No save for you!";
            exit;
        } elseif ($lock['LOCKEDBY'] == $_REQUEST['ownership']) {
            // then they are taking ownership - all others get locked...
            // new LOCKEDBY becomes our uniqueID LOCKEDBY
            $_REQUEST['LOCKED'] = '1';
            $_REQUEST['LOCKEDBY'] = $_REQUEST['uniqueID'];
            //update table
            $query = "update form_eye_locking set LOCKED=?,LOCKEDBY=? where id=?";
            sqlQuery($query, array('1', $_REQUEST['LOCKEDBY'], $form_id));
            //go on to save what we want...
        }
    } elseif (!($lock['LOCKED'] ?? '')) { // it is not locked yet
        $_REQUEST['LOCKED'] = '1';
        $query = "update form_eye_locking set LOCKED=?,LOCKEDBY=?,LOCKEDDATE=NOW() where id=?";
        sqlQuery($query, array('1', ($_REQUEST['LOCKEDBY'] ?? ''), $form_id));
        //go on to save what we want...
    }

    if (!($_REQUEST['LOCKEDBY'] ?? '')) {
        $_REQUEST['LOCKEDBY'] = rand();
    }
}

if (($_REQUEST["mode"]  ?? '') == "new") {
    $base_array = array();
    $newid = formSubmit('form_eye_base', '', $id, $userauthorized);

    addForm($encounter, $form_name, $newid, $form_folder, $pid, $userauthorized);
        //we need to poulate all the rest of  $tables with an $newid and blank values...
    $tables = array('form_eye_hpi','form_eye_ros','form_eye_vitals',
        'form_eye_acuity','form_eye_refraction','form_eye_biometrics',
        'form_eye_external', 'form_eye_antseg','form_eye_postseg',
        'form_eye_neuro','form_eye_locking');

    foreach ($tables as $table_name) {
        $query = "INSERT INTO " . $table_name . " ('id','pid') VALUES (?,?)";
        $result = sqlStatement($query, array($new_id,$pid));
    }
} elseif (($_REQUEST["mode"]  ?? '') == "update") {
    // The user has write privileges to work with...

    if (($_REQUEST['action'] ?? '') == "store_PDF") {
         /**
          * We want to store/overwrite the current PDF version of this encounter's f
          * Currently this is only called 'beforeunload', ie. when you finish the form
          * In this current paradigm, anytime the form is opened, then closed, the PDF
          * is overwritten.  With esign implemented, the PDF should be locked.  I suppose
          * with esign the form can't even be opened so the only way to get to the PDF
          * is through the Documents->Encounters links.
          */
        $query = "select id from categories where name = 'Encounters'";
        $result = sqlStatement($query);
        $ID = sqlFetchArray($result);
        $category_id = $ID['id'];
        $PDF_OUTPUT = '1';

        $filename = $pid . "_" . $encounter . ".pdf";
        $filepath = $GLOBALS['oer_config']['documents']['repository'] . $pid;
        foreach (glob($filepath . '/' . $filename) as $file) {
            unlink($file);
        }

        $sql = "DELETE from categories_to_documents where document_id IN (SELECT id from documents where documents.url like ?)";
        sqlQuery($sql, ['%' . $filename]);
        $sql = "DELETE from documents where documents.url like ?";
        sqlQuery($sql, ['%' . $filename]);
        // We want to overwrite so only one PDF is stored per form/encounter
        $config_mpdf = array(
            'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
            'mode' => $GLOBALS['pdf_language'],
            'format' => $GLOBALS['pdf_size'],
            'default_font_size' => '9',
            'default_font' => '',
            'margin_left' => $GLOBALS['pdf_left_margin'],
            'margin_right' => $GLOBALS['pdf_right_margin'],
            'margin_top' => $GLOBALS['pdf_top_margin'],
            'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
            'margin_header' => '',
            'margin_footer' => '',
            'orientation' => $GLOBALS['pdf_layout'],
            'shrink_tables_to_fit' => 1,
            'use_kwt' => true,
            'keep_table_proportions' => true
        );
        $pdf = new mPDF($config_mpdf);
        if ($_SESSION['language_direction'] == 'rtl') {
            $pdf->SetDirectionality('rtl');
        }
        ob_start();
        ?>
        <link rel="stylesheet" href="<?php echo $webserver_root; ?>/interface/themes/style_pdf.css">
    <div id="report_custom" style="width:100%;">  <!-- large outer DIV -->
        <?php
        echo report_header($pid);
        include_once($GLOBALS['incdir'] . "/forms/eye_mag/report.php");
        call_user_func($form_name . "_report", $pid, $form_encounter, $N, $form_id);
        if ($printable) {
            echo "" . xl('Signature') . ": _______________________________<br />";
        }
        ?>
      </div> <!-- end of report_custom DIV -->

        <?php

        global $web_root, $webserver_root;
        $content = ob_get_clean();
        // Fix a nasty html2pdf bug - it ignores document root!
        $i = 0;
        $wrlen = strlen($web_root);
        $wsrlen = strlen($webserver_root);
        while (true) {
            $i = stripos($content, " src='/", $i + 1);
            if ($i === false) {
                break;
            }

            if (
                substr($content, $i + 6, $wrlen) === $web_root &&
                substr($content, $i + 6, $wsrlen) !== $webserver_root
            ) {
                $content = substr($content, 0, $i + 6) . $webserver_root . substr($content, $i + 6 + $wrlen);
            }
        }
        // Below is for including style sheet for report specific styles. Left here for future use.
        //$styles = file_get_contents('../css/report.css');
        //$pdf->writeHTML($styles, 1);
        //$pdf->writeHTML($content, 2);

        $pdf->writeHTML($content);
        $tmpdir = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/'; // Best to get a known system temp directory to ensure a writable directory.
        $temp_filename = $tmpdir . $filename;
        $content_pdf = $pdf->Output($temp_filename, 'F');
        $type = "application/pdf";
        $size = filesize($temp_filename);
        $return = addNewDocument($filename, $type, $temp_filename, 0, $size, $_SESSION['authUserID'], $pid, $category_id);
        $doc_id = $return['doc_id'];
        $sql = "UPDATE documents set encounter_id=? where id=?"; //link it to this encounter
        sqlQuery($sql, array($encounter, $doc_id));

        unlink($temp_filename);

        exit();
    }

    // Store the IMPPLAN area.  This is separate from the rest of the form
    // It is in a separate table due to its one-to-many relationship with the form_id.
    if (($_REQUEST['action'] ?? '') == "store_IMPPLAN") {
        $IMPPLAN = json_decode($_REQUEST['parameter'], true);
        //remove what is there and replace it with this data.
        $query = "DELETE from form_" . $form_folder . "_impplan where form_id=? and pid=?";
        sqlQuery($query, array($form_id, $pid));

        for ($i = 0; $i < count($IMPPLAN); $i++) {
            $query = "INSERT IGNORE INTO form_" . $form_folder . "_impplan (form_id, pid, title, code, codetype, codedesc, codetext, plan, IMPPLAN_order, PMSFH_link) VALUES(?,?,?,?,?,?,?,?,?,?) ";
            $response = sqlQuery($query, array($form_id, $pid, $IMPPLAN[$i]['title'], $IMPPLAN[$i]['code'], $IMPPLAN[$i]['codetype'], $IMPPLAN[$i]['codedesc'], $IMPPLAN[$i]['codetext'], $IMPPLAN[$i]['plan'], $i, $IMPPLAN[$i]['PMSFH_link']));
            //if it is a duplicate then delete this from the array and return the array via json.
            //or rebuild it from mysql
        }

        //Since we are potentially ignoring duplicates, build json IMPPLAN_items and return it to the user to rebuild IMP/Plan area
        $IMPPLAN_items = build_IMPPLAN_items($pid, $form_id);
        echo json_encode($IMPPLAN_items);
        exit;
    }

    //change PCP/referring doc
    if (($_POST['action'] ?? '') == 'docs') {
        $query = "update patient_data set ref_providerID=?,providerID=? where pid =?";
        sqlQuery($query, array($_POST['rDOC'], $_POST['pcp'], $pid));

        if ($_POST['pcp']) {
            //return PCP's data to end user to update their form
            $query = "SELECT * FROM users WHERE id =?";
            $DOC1 = sqlQuery($query, array($_POST['pcp']));
            $DOCS['pcp']['name'] = $DOC1['fname'] . " " . $DOC1['lname'];
            if ($DOC1['suffix']) {
                $DOCS['pcp']['name'] .= ", " . $DOC1['suffix'];
            }
            $DOCS['pcp']['address'] =  $DOC1['street'] . "<br />" . $DOC1['city'] . ", " . $DOC1['state'] . "  " . $DOC1['zip'] . "<br />";
            if (!empty($DOC1['organization'])) {
                $DOCS['pcp']['address'] = $DOC1['organization'] . "<br />" . $DOCS['pcp']['address'];
            }
            $DOCS['pcp']['fax']     = $DOC1['fax'];
            $DOCS['pcp']['phone']   = $DOC1['phonew1'];
            // does the fax already exist?
            $query = "SELECT * FROM form_taskman WHERE TO_ID=? AND PATIENT_ID=? AND ENC_ID=?";
            $FAX_PCP = sqlQuery($query, array($_REQUEST['pcp'], $pid, $encounter));
            if ($FAX_PCP['ID']) { //it is here already, make them print and manually fax it.  Show icon
                $DOCS['pcp']['fax_info'] = "&nbsp;&nbsp;
                                            <span id='status_Fax_pcp'>
                                                <a href='" . $webroot . "/controller.php?document&view&patient_id=" . $pid . "&doc_id=" . $FAX_PCP['DOC_ID'] . "'
                                                    target='_blank' title='" . xla('View the Summary Report sent via Fax Server on') . " " . $FAX_PCP['COMPLETED_DATE'] . ".'>
                                                    <i class='far fa-file-pdf fa-fw'></i>
                                                </a>
                                                <i class='fas fa-redo fa-fw'
                                                   title='" . xla("Click to Re-Send this fax") . "'
                                                   onclick=\"top.restoreSession(); create_task('" . attr($_REQUEST['pcp']) . "','Fax-resend','ref'); return false;\"></i>
                                            </span>";
            } else {
                $DOCS['pcp']['fax_info'] = ' &nbsp;&nbsp;
                    <a href="JavaScript:void(0);"
                       title="' . xla('Send a report to this provider') . '"
                       onclick="top.restoreSession(); create_task(\'' . attr($_REQUEST['pcp']) . '\',\'Fax\',\'pcp\'); return false;">
                       <i class="fa fa-fax fa-fw"></i>
                    </a>';
            }
        }

        if ($_REQUEST['rDOC']) {
            //return referring Doc's data to end user to update their form
            $query = "SELECT * FROM users WHERE id =?";
            $DOC2 = sqlQuery($query, array($_REQUEST['rDOC']));
            $DOCS['ref']['name'] = $DOC2['fname'] . " " . $DOC2['lname'];
            if ($DOC2['suffix']) {
                $DOCS['ref']['name'] .= ", " . $DOC2['suffix'];
            }
            if ($DOCS['ref']['address'] > '') {
                $DOCS['ref']['address'] = $DOC2['organization'] . "<br />";
            }
            $DOCS['ref']['address'] .= $DOC2['street'] . "<br />" . $DOC2['city'] . ", " . $DOC2['state'] . "  " . $DOC2['zip'] . "<br />";
            $DOCS['ref']['fax'] = $DOC2['fax'];
            $DOCS['ref']['phone'] = $DOC2['phonew1'];

            // does the fax already exist?
            $query = "SELECT * FROM form_taskman WHERE TO_ID=? AND PATIENT_ID=? AND ENC_ID=?";
            $FAX_REF = sqlQuery($query, array($_REQUEST['rDOC'], $pid, $encounter));
            if ($FAX_REF['ID'] > '') { //it is here already, make them print and manually fax it.  Show icon
                $DOCS['ref']['fax_info'] = "&nbsp;&nbsp;
                                            <span id='status_Fax_ref'>
                                                <a href='" . $webroot . "/controller.php?document&view&patient_id=" . $pid . "&doc_id=" . $FAX_REF['DOC_ID'] . "'
                                                    target='_blank' title='" . xla('View the Summary Report sent via Fax Server on') . " " . $FAX_REF['COMPLETED_DATE'] . ".'>
                                                    <i class='far fa-file-pdf fa-fw'></i>
                                                </a>
                                                <i class='fas fa-redo fa-fw'
                                                    title='" . xla("Click to Re-Send this fax") . "'
                                                   onclick=\"top . restoreSession(); create_task('" . attr($_REQUEST['rDOC']) . "','Fax-resend','ref'); return false;\"></i>
                                            </span>";
            } else {
                $DOCS['ref']['fax_info'] = '&nbsp;&nbsp;
                    <a href="JavaScript:void(0);"
                       title="' . xla('Send a report to this provider') . '"
                       onclick="top.restoreSession(); create_task(\'' . attr($_REQUEST['rDOC']) . '\',\'Fax\',\'ref\'); return false;">
                        <span id="status_Fax_ref"><i class="fas fa-fax fa-fw"></i></span>
                    </a>';
            }
        }

        echo json_encode($DOCS);
        exit;
    }

    /*** START CODE to DEAL WITH PMSFH/ISUUE_TYPES  ****/
    if (($_REQUEST['PMSFH_save'] ?? '') == '1') {
        if (!$PMSFH) {
            $PMSFH = build_PMSFH($pid);
        }

        $issue = $_REQUEST['issue'];
        $deletion = $_REQUEST['deletion'];
        $form_save = $_REQUEST['form_save'];
        $pid = $_SESSION['pid'];
        $encounter = $_SESSION['encounter'];
        $form_id = $_REQUEST['form_id'];
        $form_type = $_REQUEST['form_type'];
        $r_PMSFH = $_REQUEST['r_PMSFH'];
        if ($deletion == 1) {
            row_delete("issue_encounter", "list_id = '" . add_escape_custom($issue) . "'");
            row_delete("lists", "id = '" . add_escape_custom($issue) . "'");
            $PMSFH = build_PMSFH($pid);
            send_json_values($PMSFH);
            exit;
        } else {
            if ($form_type == 'ROS') { //ROS
                $query = "UPDATE form_eye_ros set ROSGENERAL=?,ROSHEENT=?,ROSCV=?,ROSPULM=?,ROSGI=?,ROSGU=?,ROSDERM=?,ROSNEURO=?,ROSPSYCH=?,ROSMUSCULO=?,ROSIMMUNO=?,ROSENDOCRINE=?,ROSCOMMENTS=?,pid=? where id=?";
                sqlStatement($query, array($_REQUEST['ROSGENERAL'], $_REQUEST['ROSHEENT'], $_REQUEST['ROSCV'], $_REQUEST['ROSPULM'], $_REQUEST['ROSGI'], $_REQUEST['ROSGU'], $_REQUEST['ROSDERM'], $_REQUEST['ROSNEURO'], $_REQUEST['ROSPSYCH'], $_REQUEST['ROSMUSCULO'], $_REQUEST['ROSIMMUNO'], $_REQUEST['ROSENDOCRINE'], $_REQUEST['ROSCOMMENTS'],$pid, $form_id));
                $PMSFH = build_PMSFH($pid);
                send_json_values($PMSFH);
                exit;
            } elseif ($form_type == 'SOCH') { //SocHx
                $newdata = array();
                $fres = sqlStatement("SELECT * FROM layout_options " .
                    "WHERE form_id = 'HIS' AND uor > 0 AND field_id != '' " .
                    "ORDER BY group_id, seq");
                while ($frow = sqlFetchArray($fres)) {
                    $field_id = $frow['field_id'];
                    //get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
                    if (isset($_POST["form_$field_id"])) {
                        $newdata[$field_id] = get_layout_form_value($frow);
                    }
                }
                //have to figure where to put comments in this next line for the rest of openemr
                updateHistoryData($pid, $newdata);
                if ($_REQUEST['marital_status'] > '') {
                    // have to match input with list_option for marital to not break openEMR
                    $query = "select * from list_options where list_id='marital'";
                    $fres = sqlStatement($query);
                    while ($frow = sqlFetchArray($fres)) {
                        if (($_REQUEST['marital_status'] == $frow['option_id']) || ($_REQUEST['marital_status'] == $frow['title'])) {
                            $status = $frow['option_id'];
                            $query = "UPDATE patient_data set status=? where pid=?";
                            sqlStatement($query, array($status, $pid));
                        }
                    }
                }

                if ($_REQUEST['occupation'] > '') {
                    $query = "UPDATE patient_data set occupation=? where pid=?";
                    sqlStatement($query, array($_REQUEST['occupation'], $pid));
                }

                $PMSFH = build_PMSFH($pid);
                send_json_values($PMSFH);
                exit;
            } elseif ($form_type == 'FH') {
                $query = "UPDATE history_data set
                relatives_cancer=?,
                relatives_diabetes=?,
                relatives_high_blood_pressure=?,
                relatives_heart_problems=?,
                relatives_stroke=?,
                relatives_epilepsy=?,
                relatives_mental_illness=?,
                relatives_suicide=?,
                usertext11=?,
                usertext12=?,
                usertext13=?,
                usertext14=?,
                usertext15=?,
                usertext16=?,
                usertext17=?,
                usertext18=? where pid=?";
                $resFH = sqlStatement($query, array($_REQUEST['relatives_cancer'], $_REQUEST['relatives_diabetes'], $_REQUEST['relatives_high_blood_pressure'], $_REQUEST['relatives_heart_problems'], $_REQUEST['relatives_stroke'], $_REQUEST['relatives_epilepsy'], $_REQUEST['relatives_mental_illness'], $_REQUEST['relatives_suicide'], $_REQUEST['usertext11'], $_REQUEST['usertext12'], $_REQUEST['usertext13'], $_REQUEST['usertext14'], $_REQUEST['usertext15'], $_REQUEST['usertext16'], $_REQUEST['usertext17'], $_REQUEST['usertext18'], $pid));
                $PMSFH = build_PMSFH($pid);
                send_json_values($PMSFH);
                exit;
            } else {
                if ($_REQUEST['form_title'] == '') {
                    return;
                }

                $subtype = '';
                if ($form_type == "POH") {
                    $form_type = "medical_problem";
                    $subtype = "eye";
                } elseif ($form_type == "PMH") {
                    $form_type = "medical_problem";
                } elseif ($form_type == "Allergy") {
                    $form_type = "allergy";
                } elseif ($form_type == "Surgery") {
                    $form_type = "surgery";
                } elseif ($form_type == "POS") {
                    $form_type = "surgery";
                    $subtype = "eye";
                } elseif (($form_type == "Medication") || ($form_type == "Eye Meds")) {
                    $form_type = "medication";
                    if ($_REQUEST['form_eye_subtype']) {
                        $subtype = "eye";
                        //we always want a default begin date
                        //if it is empty, fill it with today
                        if ($_REQUEST['form_begin'] == '') {
                            $_REQUEST['form_begin'] = date("Y-m-d");
                        }
                    }

                    if ($_REQUEST['form_begin'] == '') {
                        $_REQUEST['form_begin'] = $visit_date;
                    }
                }

                $i = 0;
                $form_begin = DateToYYYYMMDD($_REQUEST['form_begin']);
                $form_end   = DateToYYYYMMDD($_REQUEST['form_end']);

                /**
                 *  When adding an issue, see if the issue is already here.
                 *  If so we need to update it.  If not we are adding it.
                 *  Check the PMSFH array first by title.
                 *  If not present in PMSFH, check the DB to be sure.
                 */
                foreach ($PMSFH[$form_type] as $item) {
                    if ($item['title'] == $_REQUEST['form_title']) {
                        $issue = $item['issue'];
                    }
                }

                if (!$issue) {
                    if ($subtype == '') {
                        $query = "SELECT id,pid from lists where title=? and type=? and pid=?";
                        $issue2 = sqlQuery($query, array($_REQUEST['form_title'], $form_type, $pid));
                        $issue = $issue2['id'];
                    } else {
                        $query = "SELECT id,pid from lists where title=? and type=? and pid=? and subtype=?";
                        $issue2 = sqlQuery($query, array($_REQUEST['form_title'], $form_type, $pid, $subtype));
                        $issue = $issue2['id'];
                    }
                }

                $issue = 0 + $issue;
                if ($_REQUEST['form_reinjury_id'] == "") {
                    $form_reinjury_id = "0";
                }

                if ($_REQUEST['form_injury_grade'] == "") {
                    $form_injury_grade = "0";
                }

                if ($_REQUEST['form_outcome'] == '') {
                    $_REQUEST['form_outcome'] = '0';
                }

                if ($issue != '0') { //if this issue already exists we are updating it...
                    // TODO: @adunsulag do we need to have the eye-form use the PatientIssuesService?
                    $query = "UPDATE lists SET " .
                        "type = '" . add_escape_custom($form_type) . "', " .
                        "title = '" . add_escape_custom($_REQUEST['form_title']) . "', " .
                        "comments = '" . add_escape_custom($_REQUEST['form_comments']) . "', " .
                        "begdate = " . QuotedOrNull($form_begin) . ", " .
                        "enddate = " . QuotedOrNull($form_end) . ", " .
                        "returndate = " . QuotedOrNull($form_return) . ", " .
                        "diagnosis = '" . add_escape_custom($_REQUEST['form_diagnosis']) . "', " .
                        "occurrence = '" . add_escape_custom($_REQUEST['form_occur']) . "', " .
                        "classification = '" . add_escape_custom($_REQUEST['form_classification']) . "', " .
                        "reinjury_id = '" . add_escape_custom($_REQUEST['form_reinjury_id']) . "', " .
                        "referredby = '" . add_escape_custom($_REQUEST['form_referredby']) . "', " .
                        "injury_grade = '" . add_escape_custom($_REQUEST['form_injury_grade']) . "', " .
                        "injury_part = '" . add_escape_custom($form_injury_part) . "', " .
                        "injury_type = '" . add_escape_custom($form_injury_type) . "', " .
                        "outcome = '" . add_escape_custom($_REQUEST['form_outcome']) . "', " .
                        "destination = '" . add_escape_custom($_REQUEST['form_destination']) . "', " .
                        "reaction ='" . add_escape_custom($_REQUEST['form_reaction']) . "', " .
                        "erx_uploaded = '0', " .
                        "modifydate = NOW(), " .
                        "subtype = '" . $subtype . "' " .
                        "WHERE id = '" . add_escape_custom($issue) . "'";
                    sqlStatement($query);
                    if ($text_type == "medication" && enddate != '') {
                        sqlStatement('UPDATE prescriptions SET '
                            . 'medication = 0 where patient_id = ? '
                            . " and upper(trim(drug)) = ? "
                            . ' and medication = 1', array($pid, strtoupper($_REQUEST['form_title'])));
                    }
                } else {
                    $query = "INSERT INTO lists ( " .
                        "date, pid, type, title, activity, comments, " .
                        "begdate, enddate, returndate, " .
                        "diagnosis, occurrence, classification, referredby, user, " .
                        "groupname, outcome, destination,reaction,subtype " .
                        ") VALUES ( " .
                        "NOW(), ?,?,?,1,?," .
                        QuotedOrNull($form_begin) . ", " . QuotedOrNull($form_end) . ", " . QuotedOrNull($form_return) . ", " .
                        "?,?,?,?,?," .
                        "?,?,?,?,?)";
                    $issue = sqlInsert($query, array($pid, $form_type, $_REQUEST['form_title'], $_REQUEST['form_comments'],
                        $_REQUEST['form_diagnosis'], $_REQUEST['form_occur'], $_REQUEST['form_clasification'], $_REQUEST['form_referredby'], $_SESSION['authUser'],
                        $_SESSION['authProvider'], QuotedOrNull($_REQUEST['form_outcome']), $_REQUEST['form_destination'], $_REQUEST['form_reaction'], $subtype));

                    // For record/reporting purposes, place entry in lists_touch table.
                    setListTouch($pid, $form_type);

                    // If requested, link the issue to a specified encounter.
                    // we always link them, automatically.
                    if ($encounter) {
                        $query = "INSERT INTO issue_encounter ( " .
                            "pid, list_id, encounter " .
                            ") VALUES ( ?,?,? )";
                        sqlStatement($query, array($pid, $issue, $encounter));
                    }
                }

                $irow = '';
                //if it is a medication do we need to do something with dosage fields?
                //leave all in title field form now.
            }

            $PMSFH = build_PMSFH($pid);
            send_json_values($PMSFH);
            exit;
        }
    }

    if (($_REQUEST['action'] ?? '') == 'code_PMSFH') {
        $query = "UPDATE lists SET diagnosis = ? WHERE id = ?";
        sqlStatement($query, array($_POST['code'], $_POST['issue']));
        exit;
    }

    if (($_REQUEST['action'] ?? '') == 'code_visit') {
        $CODING = json_decode($_REQUEST['parameter'], true);
        $query = "delete from billing where encounter =?";
        sqlStatement($query, array($encounter));
        foreach ($CODING as $item) { //need toremove duplicate codes
            if ($dups[$item["code"]] == '1') {
                continue;
            }

            $dups[$item["code"]] = "1";
            $sql = "SELECT codes.*, prices.pr_price FROM codes " .
                "LEFT OUTER JOIN patient_data ON patient_data.pid = ? " .
                "LEFT OUTER JOIN prices ON prices.pr_id = codes.id AND " .
                "prices.pr_selector = '' AND " .
                "prices.pr_level = patient_data.pricelevel " .
                "WHERE code =?" .
                " LIMIT 1";
            $result = sqlStatement($sql, array($pid, $item['code']));
            while ($res = sqlFetchArray($result)) {
                $item["codedesc"] = $res["code_text"];// eg. = "NP EYE intermediate exam"
                if (!$item["modifier"]) {
                    $modifier = $res["modifier"];
                }
                $item["units"] = $res["units"];
                $item["fee"] = $res["pr_price"];
            }
            $billed = "0";
            $item["justify"] .= ":";
            BillingUtilities::addBilling($encounter, $item["codetype"], $item["code"], $item["codedesc"], $pid, '1', $providerID, $item["modifier"], $item["units"], $item["fee"], $ndc_info, $item["justify"], $billed, '');
        }
        echo "OK";
        exit;
    }


    if (($_REQUEST['action']  ?? '') == 'new_pharmacy') {
        $query = "UPDATE patient_data set pharmacy_id=? where pid=?";
        sqlStatement($query, array($_POST['new_pharmacy'], $pid));
        echo "Pharmacy updated";
        exit;
    }
    /*** END CODE to DEAL WITH PMSFH/ISUUE_TYPES  ****/
    //Update the visit status for this appointment (from inside the Coding Engine)
    //we also have to update the flow board...  They are not linked automatically.
    //Flow board counts items for each events so we need to insert new item and update total for the event, via pc_eid...
    if (($_REQUEST['action'] ?? '') == 'new_appt_status') {
        if ($_POST['new_status']) {
            //make sure visit_date is in YYYY-MM-DD format
            $Vdated = new DateTime($_POST['visit_date']);
            $Vdate = $Vdated->format('Y-m-d');
            //get eid
            $sql = "select * from patient_tracker where  `pid` = ? and `apptdate`=?";
            $tracker = sqlFetchArray(sqlStatement($sql, array($_POST['pid'], $Vdate)));
            sqlStatement("UPDATE `patient_tracker` SET  `lastseq` = ? WHERE `id` = ?", array(($tracker['lastseq'] + 1), $tracker['id']));
            #Add a tracker item.
            $sql = "INSERT INTO `patient_tracker_element` " .
                "(`pt_tracker_id`, `start_datetime`, `user`, `status`, `room`, `seq`) " .
                "VALUES (?,NOW(),?,?,?,?)";
            sqlStatement($sql, array($tracker['id'], $userauthorized, $_POST['new_status'], ' ', ($tracker['lastseq'] + 1)));
            $sql = "UPDATE `openemr_postcalendar_events` SET `pc_apptstatus` = ?, pc_room='' WHERE `pc_eid` = ?";
            sqlStatement($sql, array($_POST['new_status'], $tracker['eid']));
            echo "saved";
            exit;
        }
        echo "Failed to update Patient Tracker.";
        exit;
    }
    /** Let's save the encounter specific values.
     * Any field that exists in the database could be updated
     * so we need to exclude the important ones...
     * id  date  pid   user  groupname   authorized  activity.  Any other just add them below.
     * Doing it this way means you can add new fields on a web page and in the DB without touching this function.
     * The update feature still works because it only updates columns that are in the table you are working on.
     */
    if (($_POST['IOPTIME'] == '00:00:00') || (!$_POST['IOPTIME'])) {
        $_POST['IOPTIME'] = date('H:i:s');
    }

    $_POST['IOPTIME'] = date('H:i:s', strtotime($_POST['IOPTIME']));
    // orders are checkboxes created from a user defined list in the PLAN area and stored as item1|item2|item3
    // if there are any, create the $field['PLAN'] value.
    // Remember --  If you uncheck a box, it won't be sent!
    // So delete all made today by this provider and reload with any Orders sent in this $_POST
    // in addition, we made a special table for orders, and when completed we can mark done?
    $query = "select form_encounter.date as encounter_date from form_encounter where form_encounter.encounter =?";
    $encounter_data = sqlQuery($query, array($encounter));
    $dated = new DateTime($encounter_data['encounter_date']);
    $visit_date = $dated->format('Y-m-d');

    $N = count($_POST['PLAN']);
    $sql_clear = "DELETE from form_eye_mag_orders where pid =? and ORDER_PLACED_BYWHOM=? and ORDER_DATE_PLACED=? and ORDER_STATUS ='pending'";
    sqlQuery($sql_clear, array($pid, $providerID, $visit_date));
    if ($N > '0') {
        for ($i = 0; $i < $N; $i++) {
            if ($_POST['PLAN'][$i] == '') {
                continue;
            }
            $fields['PLAN'] .= $_POST['PLAN'][$i] . "|"; //this makes an entry for form_eyemag: PLAN
            $ORDERS_sql = "INSERT INTO form_eye_mag_orders (form_id,pid,ORDER_DETAILS,ORDER_PRIORITY,ORDER_STATUS,ORDER_DATE_PLACED,ORDER_PLACED_BYWHOM) VALUES (?,?,?,?,?,?,?)";
            $okthen = sqlQuery($ORDERS_sql, array($form_id, $pid, $_POST['PLAN'][$i], $i, 'pending', $visit_date, $providerID));
        }

        $_POST['PLAN'] = mb_substr(($fields['PLAN'] ?? ''), 0, -1); //get rid of trailing "|"
    }

    $M = empty($_POST['TEST']) ? 0 : count($_POST['TEST']);
    if ($M > '0') {
        for ($i = 0; $i < $M; $i++) {
            $_POST['Resource'] .= $_POST['TEST'][$i] . "|"; //this makes an entry for form_eyemag: Resource
        }

        $_POST['Resource'] = mb_substr($_POST['Resource'], 0, -1); //get rid of trailing "|"
    }

    /** Empty Checkboxes need to be entered manually as they are only submitted via POST when they are checked
     * If NOT checked on the form, they are sent via POST and thus are NOT overridden in the DB,
     *  so DB won't change unless we define them into the $fields array as "0"...
     */
    if (!$_POST['alert']) {
        $_POST['alert'] = '0';
    }

    if (!$_POST['oriented']) {
        $_POST['oriented'] = '0';
    }

    if (!$_POST['confused']) {
        $_POST['confused'] = '0';
    }

    if (!$_POST['PUPIL_NORMAL']) {
        $_POST['PUPIL_NORMAL'] = '0';
    }

    if (!$_POST['MOTILITYNORMAL']) {
        $_POST['MOTILITYNORMAL'] = '0';
    }

    if (!$_POST['ACT']) {
        $_POST['ACT'] = 'off';
    }

    if (!$_POST['DIL_RISKS']) {
        $_POST['DIL_RISKS'] = '0';
    }

    if (!($_POST['ATROPINE'] ?? '')) {
        $_POST['ATROPINE'] = '0';
    }

    if (!($_POST['CYCLOGYL'] ?? '')) {
        $_POST['CYCLOGYL'] = '0';
    }

    if (!($_POST['CYCLOMYDRIL'] ?? '')) {
        $_POST['CYCLOMYDRIL'] = '0';
    }

    if (!($_POST['NEO25'] ?? '')) {
        $_POST['NEO25'] = '0';
    }

    if (!($_POST['TROPICAMIDE'] ?? '')) {
        $_POST['TROPICAMIDE'] = '0';
    }

    if (!($_POST['BALANCED'] ?? '')) {
        $_POST['BALANCED'] = '0';
    }

    if (!($_POST['ODVF1'] ?? '')) {
        $_POST['ODVF1'] = '0';
    }

    if (!($_POST['ODVF2'] ?? '')) {
        $_POST['ODVF2'] = '0';
    }

    if (!($_POST['ODVF3'] ?? '')) {
        $_POST['ODVF3'] = '0';
    }

    if (!($_POST['ODVF4'] ?? '')) {
        $_POST['ODVF4'] = '0';
    }

    if (!($_POST['OSVF1'] ?? '')) {
        $_POST['OSVF1'] = '0';
    }

    if (!($_POST['OSVF2'] ?? '')) {
        $_POST['OSVF2'] = '0';
    }

    if (!($_POST['OSVF3'] ?? '')) {
        $_POST['OSVF3'] = '0';
    }

    if (!($_POST['OSVF4'] ?? '')) {
        $_POST['OSVF4'] = '0';
    }

    if (!($_POST['TEST'] ?? '')) {
        $_POST['Resource'] = '';
    }

    if (!$_POST['PLAN']) {
        $_POST['PLAN'] = ' ';
    }

    $tables = array('form_eye_hpi','form_eye_ros','form_eye_vitals',
        'form_eye_acuity','form_eye_refraction','form_eye_biometrics',
        'form_eye_external', 'form_eye_antseg','form_eye_postseg',
        'form_eye_neuro','form_eye_locking');

    foreach ($tables as $table_name) {
        $query = "SHOW COLUMNS from " . $table_name . "";
        $result = sqlStatement($query);
        if (!$result) {
            return 'Could not run query: No columns found in your table!  ';// . mysql_error();
            continue;
        }

        $fields = array();
        $sql2 = '';
        if (sqlNumRows($result) > 0) {
            while ($row = sqlFetchArray($result)) {
                //exclude critical columns/fields and those needing special processing from update
                if (
                    $row['Field'] == 'id' or
                    $row['Field'] == 'date' or
                    $row['Field'] == 'pid' or
                    $row['Field'] == 'user' or
                    $row['Field'] == 'groupname' or
                    $row['Field'] == 'authorized' or
                    $row['Field'] == 'LOCKED' or
                    $row['Field'] == 'LOCKEDBY' or
                    $row['Field'] == 'activity' or
                    $row['Field'] == 'PLAN' or
                    $row['Field'] == 'Resource'
                ) {
                    continue;
                }
                $fields[] = $_POST[$row['Field']] ?? '';
                $sql2 .= " " . add_escape_custom($row['Field']) . " = ?,";
            }
            $sql = "update " . escape_table_name($table_name) . " set pid ='" . add_escape_custom($_SESSION['pid']) . "'," . $sql2;

            $sql = substr($sql, 0, -1);
            $sql .= " where id=?";
            $fields[] = $form_id;
            $success = sqlStatement($sql, $fields);
        }
    }
    //now save any Wear RXs (1-4) entered.

    $rx_number = '1';
    if ($_POST['W_1'] == '1') {
        $query = "REPLACE INTO `form_eye_mag_wearing` (`ENCOUNTER` ,`FORM_ID` ,`PID` ,`RX_NUMBER` ,`ODSPH` ,`ODCYL` ,`ODAXIS` ,
            `ODVA` ,`ODADD` ,`ODNEARVA` ,`OSSPH` ,`OSCYL` ,`OSAXIS` ,
            `OSVA` ,`OSADD` ,`OSNEARVA` ,`ODMIDADD` ,`OSMIDADD` ,
            `RX_TYPE` ,`COMMENTS`,
            `ODHPD`,`ODHBASE`,`ODVPD`,`ODVBASE`,`ODSLABOFF`,`ODVERTEXDIST`,
            `OSHPD`,`OSHBASE`,`OSVPD`,`OSVBASE`,`OSSLABOFF`,`OSVERTEXDIST`,
            `ODMPDD`,`ODMPDN`,`OSMPDD`,`OSMPDN`,`BPDD`,`BPDN`,`LENS_MATERIAL`,
            `LENS_TREATMENTS`
            ) VALUES
            (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $LENS_TREATMENTS_1 = implode("|", (empty($_POST['LENS_TREATMENTS_1']) ? array() : $_POST['LENS_TREATMENTS_1']));
            sqlQuery($query, array($encounter, $form_id, $pid, $rx_number, $_POST['ODSPH_1'], $_POST['ODCYL_1'], $_POST['ODAXIS_1'],
                $_POST['ODVA_1'], $_POST['ODADD_1'], $_POST['ODNEARVA_1'], $_POST['OSSPH_1'], $_POST['OSCYL_1'], $_POST['OSAXIS_1'],
                $_POST['OSVA_1'], $_POST['OSADD_1'], $_POST['OSNEARVA_1'], $_POST['ODMIDADD_1'], $_POST['OSMIDADD_1'],
                0 + ($_POST['RX_TYPE_1'] ?? null), ($_POST['COMMENTS_1'] ?? ''),
                $_POST['ODHPD_1'], $_POST['ODHBASE_1'], $_POST['ODVPD_1'], $_POST['ODVBASE_1'], $_POST['ODSLABOFF_1'], $_POST['ODVERTEXDIST_1'],
                $_POST['OSHPD_1'], $_POST['OSHBASE_1'], $_POST['OSVPD_1'], $_POST['OSVBASE_1'], $_POST['OSSLABOFF_1'], $_POST['OSVERTEXDIST_1'],
                $_POST['ODMPDD_1'], $_POST['ODMPDN_1'], $_POST['OSMPDD_1'], $_POST['OSMPDN_1'], $_POST['BPDD_1'], $_POST['BPDN_1'], $_POST['LENS_MATERIAL_1'],
                $LENS_TREATMENTS_1));
            $rx_number++;
    } else {
        $query = "DELETE FROM form_eye_mag_wearing where ENCOUNTER=? and PID=? and FORM_ID=? and RX_NUMBER=?";
        sqlQuery($query, array($encounter, $pid, $form_id, '1'));
    }
    if ($_POST['W_2'] == '1') {
        //store W_2
        $query = "REPLACE INTO `form_eye_mag_wearing` (`ENCOUNTER` ,`FORM_ID` ,`PID` ,`RX_NUMBER` ,`ODSPH` ,`ODCYL` ,`ODAXIS` ,
        `ODVA` ,`ODADD` ,`ODNEARVA` ,`OSSPH` ,`OSCYL` ,`OSAXIS` ,
        `OSVA` ,`OSADD` ,`OSNEARVA` ,`ODMIDADD` ,`OSMIDADD` ,
        `RX_TYPE` ,`COMMENTS`,
        `ODHPD`,`ODHBASE`,`ODVPD`,`ODVBASE`,`ODSLABOFF`,`ODVERTEXDIST`,
        `OSHPD`,`OSHBASE`,`OSVPD`,`OSVBASE`,`OSSLABOFF`,`OSVERTEXDIST`,
        `ODMPDD`,`ODMPDN`,`OSMPDD`,`OSMPDN`,`BPDD`,`BPDN`,`LENS_MATERIAL`,
        `LENS_TREATMENTS`
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $LENS_TREATMENTS_2 = implode("|", $_POST['LENS_TREATMENTS_2']);
        sqlQuery($query, array($encounter, $form_id, $pid, $rx_number, $_POST['ODSPH_2'], $_POST['ODCYL_2'], $_POST['ODAXIS_2'],
            $_POST['ODVA_2'], $_POST['ODADD_2'], $_POST['ODNEARVA_2'], $_POST['OSSPH_2'], $_POST['OSCYL_2'], $_POST['OSAXIS_2'],
            $_POST['OSVA_2'], $_POST['OSADD_2'], $_POST['OSNEARVA_2'], $_POST['ODMIDADD_2'], $_POST['OSMIDADD_2'],
            0 + $_POST['RX_TYPE_2'], $_POST['COMMENTS_2'],
            $_POST['ODHPD_2'], $_POST['ODHBASE_2'], $_POST['ODVPD_2'], $_POST['ODVBASE_2'], $_POST['ODSLABOFF_2'], $_POST['ODVERTEXDIST_2'],
            $_POST['OSHPD_2'], $_POST['OSHBASE_2'], $_POST['OSVPD_2'], $_POST['OSVBASE_2'], $_POST['OSSLABOFF_2'], $_POST['OSVERTEXDIST_2'],
            $_POST['ODMPDD_2'], $_POST['ODMPDN_2'], $_POST['OSMPDD_2'], $_POST['OSMPDN_2'], $_POST['BPDD_2'], $_POST['BPDN_2'], $_POST['LENS_MATERIAL_2'],
            $LENS_TREATMENTS_2));
        $rx_number++;
    } else {
        $query = "DELETE FROM form_eye_mag_wearing where ENCOUNTER=? and PID=? and FORM_ID=? and RX_NUMBER=?";
        sqlQuery($query, array($encounter, $pid, $form_id, '2'));
    }
    if ($_POST['W_3'] == '1') {
        //store W_3
        $query = "REPLACE INTO `form_eye_mag_wearing` (`ENCOUNTER` ,`FORM_ID` ,`PID` ,`RX_NUMBER` ,`ODSPH` ,`ODCYL` ,`ODAXIS` ,
        `ODVA` ,`ODADD` ,`ODNEARVA` ,`OSSPH` ,`OSCYL` ,`OSAXIS` ,
        `OSVA` ,`OSADD` ,`OSNEARVA` ,`ODMIDADD` ,`OSMIDADD` ,
        `RX_TYPE` ,`COMMENTS`,
        `ODHPD`,`ODHBASE`,`ODVPD`,`ODVBASE`,`ODSLABOFF`,`ODVERTEXDIST`,
        `OSHPD`,`OSHBASE`,`OSVPD`,`OSVBASE`,`OSSLABOFF`,`OSVERTEXDIST`,
        `ODMPDD`,`ODMPDN`,`OSMPDD`,`OSMPDN`,`BPDD`,`BPDN`,`LENS_MATERIAL`,
        `LENS_TREATMENTS`
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $LENS_TREATMENTS_3 = implode("|", $_POST['LENS_TREATMENTS_3']);
        sqlQuery($query, array($encounter, $form_id, $pid, $rx_number, $_POST['ODSPH_3'], $_POST['ODCYL_3'], $_POST['ODAXIS_3'],
            $_POST['ODVA_3'], $_POST['ODADD_3'], $_POST['ODNEARVA_3'], $_POST['OSSPH_3'], $_POST['OSCYL_3'], $_POST['OSAXIS_3'],
            $_POST['OSVA_3'], $_POST['OSADD_3'], $_POST['OSNEARVA_3'], $_POST['ODMIDADD_3'], $_POST['OSMIDADD_3'],
            0 + $_POST['RX_TYPE_3'], $_POST['COMMENTS_3'],
            $_POST['ODHPD_3'], $_POST['ODHBASE_3'], $_POST['ODVPD_3'], $_POST['ODVBASE_3'], $_POST['ODSLABOFF_3'], $_POST['ODVERTEXDIST_3'],
            $_POST['OSHPD_3'], $_POST['OSHBASE_3'], $_POST['OSVPD_3'], $_POST['OSVBASE_3'], $_POST['OSSLABOFF_3'], $_POST['OSVERTEXDIST_3'],
            $_POST['ODMPDD_3'], $_POST['ODMPDN_3'], $_POST['OSMPDD_3'], $_POST['OSMPDN_3'], $_POST['BPDD_3'], $_POST['BPDN_3'], $_POST['LENS_MATERIAL_3'],
            $LENS_TREATMENTS_3));
        $rx_number++;
    } else {
        $query = "DELETE FROM form_eye_mag_wearing where ENCOUNTER=? and PID=? and FORM_ID=? and RX_NUMBER=?";
        sqlQuery($query, array($encounter, $pid, $form_id, '3'));
    }
    if ($_POST['W_4'] == '1') {
        //store W_4
        $query = "REPLACE INTO `form_eye_mag_wearing` (`ENCOUNTER` ,`FORM_ID` ,`PID` ,`RX_NUMBER` ,`ODSPH` ,`ODCYL` ,`ODAXIS` ,
        `ODVA` ,`ODADD` ,`ODNEARVA` ,`OSSPH` ,`OSCYL` ,`OSAXIS` ,
        `OSVA` ,`OSADD` ,`OSNEARVA` ,`ODMIDADD` ,`OSMIDADD` ,
        `RX_TYPE` ,`COMMENTS`,
        `ODHPD`,`ODHBASE`,`ODVPD`,`ODVBASE`,`ODSLABOFF`,`ODVERTEXDIST`,
        `OSHPD`,`OSHBASE`,`OSVPD`,`OSVBASE`,`OSSLABOFF`,`OSVERTEXDIST`,
        `ODMPDD`,`ODMPDN`,`OSMPDD`,`OSMPDN`,`BPDD`,`BPDN`,`LENS_MATERIAL`,
        `LENS_TREATMENTS`
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $LENS_TREATMENTS_4 = implode("|", $_POST['LENS_TREATMENTS_4']);
        sqlQuery($query, array($encounter, $form_id, $pid, $rx_number, $_POST['ODSPH_4'], $_POST['ODCYL_4'], $_POST['ODAXIS_4'],
            $_POST['ODVA_4'], $_POST['ODADD_4'], $_POST['ODNEARVA_4'], $_POST['OSSPH_4'], $_POST['OSCYL_4'], $_POST['OSAXIS_4'],
            $_POST['OSVA_4'], $_POST['OSADD_4'], $_POST['OSNEARVA_4'], $_POST['ODMIDADD_4'], $_POST['OSMIDADD_4'],
            0 + $_POST['RX_TYPE_4'], $_POST['COMMENTS_4'],
            $_POST['ODHPD_4'], $_POST['ODHBASE_4'], $_POST['ODVPD_4'], $_POST['ODVBASE_4'], $_POST['ODSLABOFF_4'], $_POST['ODVERTEXDIST_4'],
            $_POST['OSHPD_4'], $_POST['OSHBASE_4'], $_POST['OSVPD_4'], $_POST['OSVBASE_4'], $_POST['OSSLABOFF_4'], $_POST['OSVERTEXDIST_4'],
            $_POST['ODMPDD_4'], $_POST['ODMPDN_4'], $_POST['OSMPDD_4'], $_POST['OSMPDN_4'], $_POST['BPDD_4'], $_POST['BPDN_4'], $_POST['LENS_MATERIAL_4'],
            $LENS_TREATMENTS_4));
        $rx_number++;
    } else {
        $query = "DELETE FROM form_eye_mag_wearing where ENCOUNTER=? and PID=? and FORM_ID=? and RX_NUMBER=?";
        sqlQuery($query, array($encounter, $pid, $form_id, '4'));
    }

    for ($i = $rx_number; $i < 5; $i++) {
        $query = "DELETE FROM form_eye_mag_wearing where ENCOUNTER=? and PID=? and FORM_ID=? and RX_NUMBER=?";
        sqlQuery($query, array($encounter, $pid, $form_id, $i));
    }
    //now return the obj
    $send['IMPPLAN_items'] = build_IMPPLAN_items($pid, $form_id);
    $send['Clinical'] = start_your_engines($_REQUEST);
    $send['PMH_panel'] = display_PMSFH('2');
    $send['right_panel'] = show_PMSFH_panel($PMSFH);
    $send['PMSFH'] = $PMSFH[0];
    $send['Coding'] = build_CODING_items($pid, $encounter);

    echo json_encode($send);
    exit;
} elseif (($_REQUEST["mode"]  ?? '') == "retrieve") {
    if ($_REQUEST['PRIORS_query']) {
        if ($_REQUEST['zone'] == 'REFRACTIONS') {
            //TODO:  Fix this so it works!
//have to do query to join with _base pn pid since pid is not in sub files
            //get the last 3 encounters with refraction data, not Wear data, and display all that encounters Rx/W data.
            $sql = "SELECT id,date FROM form_eye_refraction WHERE
                    pid=? AND id < ? AND
                    (MRODVA <> '' OR
                      MROSVA <> '' OR
                      ARODVA <> '' OR
                      AROSVA <> '' OR
                      CRODVA <> '' OR
                      CROSVA <> '' OR
                      CTLODVA <> '' OR
                      CTLOSVA <> ''
                    )
                    ORDER BY id DESC LIMIT 3";

            //$result = sqlStatement($sql, array($pid, $_REQUEST['orig_id']));

            $sql = "SELECT id from form_eye_refraction where
                      id in (SELECT id from form_eye_base where pid=? ORDER BY `date` DESC)
                      ORDER by id DESC LIMIT 10;
            ";

            $result = sqlStatement($sql, array($pid));

            while ($visit = sqlFetchArray($result)) {
                echo display_PRIOR_section('REFRACTIONS', $visit['id'], $visit['id'], $pid);
            }
            exit;
        } else {
            echo display_PRIOR_section($_REQUEST['zone'], $_REQUEST['orig_id'], $_REQUEST['id_to_show'], $pid);
            exit;
        }
    }
}

/**
 * Save the canvas drawings
 */

if ($_REQUEST['canvas'] ?? '') {
    if (!$pid || !$encounter || !$zone || !$_POST["imgBase64"]) {
        exit;
    }

    $side = "OU";
    $base_name = $pid . "_" . $encounter . "_" . $side . "_" . $zone . "_VIEW";
    $filename = $base_name . ".jpg";

    // we receive a canvas: adding or replacing this image
    // Does it exist already? If so delete it. Yep.
    //      We should not need to keep each progressive stroke on a canvas, just the last one...
    //      We are attaching it ot this encounter so when the encounter is locked
    //      this file should also be locked.  Right?
    // Then add this.

    $sql = "SELECT * from documents where documents.name like ?";
    $ans1 = sqlQuery($sql, array('%' . $base_name . '%'));
    if ($ans1['id']) {  //it is new, add it
        $file = substr($ans1['url'], 7);
        foreach (glob($file) as $file_to_delete) {
            unlink($file_to_delete);
        }
        $query = "select id from categories where name like 'Drawings%'";
        $result = sqlStatement($query);
        $ID = sqlFetchArray($result);
        $category_id = $ID['id'];//we need to know where to store this new one.

        $sql = "DELETE from categories_to_documents where document_id = ?";
        sqlQuery($sql, [$ans1['id']]);
        $sql = "DELETE from documents where id = ?";
        sqlQuery($sql, [$ans1['id']]);
    }

    $type = "image/jpeg"; // all our canvases are this type
    $data = $_POST["imgBase64"];
    $data = substr($data, strpos($data, ",") + 1);
    $data = base64_decode($data);
    $size = strlen($data);

    $return = addNewDocument($filename, $type, $_POST["imgBase64"], 0, $size, $_SESSION['authUserID'], $pid, $category_id);
    $doc_id = $return['doc_id'];
    $sql = "UPDATE documents set encounter_id=? where id=?"; //link it to this encounter
    sqlQuery($sql, array($encounter, $doc_id));

    echo "doc stored.";
    exit;
}

if ($_REQUEST['copy']) {
    copy_forward($_REQUEST['zone'], $_REQUEST['copy_from'], ($_SESSION['ID'] ?? ''), $pid);
    return;
}

function QuotedOrNull($fld)
{
    if ($fld) {
        return "'" . add_escape_custom($fld) . "'";
    }

    return "NULL";
}

function debug($local_var)
{
    echo "<pre><BR>We are in the debug function.<BR>";
    echo "Passed variable = " . $local_var . " <BR>";
    print_r($local_var);
    exit;
}

/* From original issue.php */

function row_delete($table, $where)
{
    $query = "SELECT * FROM " . escape_table_name($table) . " WHERE $where";
    $tres = sqlStatement($query);
    $count = 0;
    while ($trow = sqlFetchArray($tres)) {
        $logstring = "";
        foreach ($trow as $key => $value) {
            if (!$value || $value == '0000-00-00 00:00:00') {
                continue;
            }

            if ($logstring) {
                $logstring .= " ";
            }

            $logstring .= $key . "='" . addslashes($value) . "'";
        }

        EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
        ++$count;
    }

    if ($count) {
        $query = "DELETE FROM " . escape_table_name($table) . " WHERE $where";
        sqlStatement($query);
    }
}

// Given an issue type as a string, compute its index.
// Not sure of the value of this sub given transition to array $PMSFH
// Can I use it to find out which PMSFH item we are looking for?  YES
function issueTypeIndex($tstr)
{
    global $ISSUE_TYPES;
    $i = 0;
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($key == $tstr) {
            break;
        }

        ++$i;
    }

    return $i;
}

exit;
?>
