<?php
/*
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
 *    to be used.  Upload image, drop widget and save it...  Imagine the dermatologists and neurologists with
 *    a drawable form they made themselves within openEMR.  They'll smile and say it's about time we get to work...
 *    We need to get back to work first and make it happen...
 *
 * Copyright (C) 2014 Raymond Magauran <magauran@MedFetch.com> 
 * 
 * LICENSE: This program is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU General Public License 
 * as published by the Free Software Foundation; either version 3 
 * of the License, or (at your option) any later version. 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * GNU General Public License for more details. 
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;. 
 * 
 * @package OpenEMR 
 * @author Ray Magauran <magauran@MedFetch.com> 
 * @link http://www.open-emr.org 
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;


include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("php/eye_mag_functions.php");
include_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");

//we need privileges to be restricted here?

$table_name   = "form_eye_mag";
$form_name    = "eye_mag";
$form_folder  = "eye_mag";
$returnurl    = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
//@extract($_SESSION); //working to remomve
//@extract($_REQUEST); //working to remove
$id = $_REQUEST['id'];
if (!$id) $id = $_REQUEST['pid'];

$AJAX_PREFS = $_REQUEST['AJAX_PREFS'];
if ($encounter == "" && !$id && !$AJAX_PREFS && ($_REQUEST['mode'] != "retrieve")) {
    echo "Sorry Charlie..."; //should lead to a database of errors for explanation.
    exit;
}

/**  
 * Save/update the preferences  
 */
if ($_REQUEST['AJAX_PREFS']) { 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
                VALUES 
                ('PREFS','VA','Vision',?,'RS','51',?,'1') 
                ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_VA']));
 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
                VALUES 
                ('PREFS','W','Current Rx',?,'W','52',?,'2')";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_W']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','MR','Manifest Refraction',?,'MR','53',?,'3') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_MR']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CR','Cycloplegic Refraction',?,'CR','54',?,'4') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_CR']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CTL','Contact Lens',?,'CTL','55',?,'5') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_CTL']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ADDITIONAL','Additional Data Points',?,'ADDITIONAL','56',?,'6') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_ADDITIONAL']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CLINICAL','CLINICAL',?,'CLINICAL','57',?,'7') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_CLINICAL']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','IOP','Intraocular Pressure',?,'IOP','67',?,'17') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_IOP']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','EXAM','EXAM',?,'EXAM','58',?,'8') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_EXAM']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CYLINDER','CYL',?,'CYL','59',?,'9') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_CYL']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','HPI_VIEW','HPI View',?,'HPI_VIEW','60',?,'10') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_HPI_VIEW']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','EXT_VIEW','External View',?,'EXT_VIEW','66',?,'16') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_EXT_VIEW']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ANTSEG_VIEW','Anterior Segment View',?,'ANTSEG_VIEW','61',?,'11') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_ANTSEG_VIEW']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','RETINA_VIEW','Retina View',?,'RETINA_VIEW','62',?,'12') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_RETINA_VIEW']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','NEURO_VIEW','Neuro View',?,'NEURO_VIEW','63',?,'13') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_NEURO_VIEW']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ACT_VIEW','ACT View',?,'ACT_VIEW','64',?,'14') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_ACT_VIEW']));
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ACT_SHOW','ACT Show',?,'ACT_SHOW','65',?,'15') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_ACT_SHOW'])); 

    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','HPI_RIGHT','HPI DRAW',?,'HPI_RIGHT','70',?,'16') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_HPI_RIGHT'])); 

    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','PMH_RIGHT','PMH DRAW',?,'PMH_RIGHT','71',?,'17') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_PMH_RIGHT'])); 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','EXT_RIGHT','EXT DRAW',?,'EXT_RIGHT','72',?,'18') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_EXT_RIGHT'])); 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ANTSEG_RIGHT','ANTSEG DRAW',?,'ANTSEG_RIGHT','73',?,'19') 
              ";
    $result = sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_ANTSEG_RIGHT'])); 

    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','RETINA_RIGHT','RETINA DRAW',?,'RETINA_RIGHT','74',?,'20') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_RETINA_RIGHT'])); 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','NEURO_RIGHT','NEURO DRAW',?,'NEURO_RIGHT','75',?,'21') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_NEURO_RIGHT'])); 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','IMPPLAN_RIGHT','IMPPLAN DRAW',?,'IMPPLAN_RIGHT','76',?,'22') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_IMPPLAN_RIGHT'])); 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES   
              ('PREFS','PANEL_RIGHT','PMSFH Panel',?,'PANEL_RIGHT','77',?,'23') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_PANEL_RIGHT'])); 
    $query = "REPLACE INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES   
              ('PREFS','KB_VIEW','KeyBoard View',?,'KB_VIEW','78',?,'24') 
              ";
    sqlQuery($query,array($_SESSION['authId'],$_REQUEST['PREFS_KB'])); 

   //echo "HELLOO <br /><br /><br />".$_REQUEST['PREFS_PANEL_RIGHT'];
}
/**
  * ADD ANY NEW PREFERENCES above, and as a hidden field in the body.  I prefer this vs Session items but that would
  * also work here.  No good reason.
  */

/** <!-- End Preferences --> **/

/**  
 * Create, update or retrieve a form and its values  
 */
$pid            = $_SESSION['pid'];
$userauthorized = $_SESSION['userauthorized'];
$encounter      = $_REQUEST['encounter'];
if ($encounter == "") $encounter = date("Ymd");
$form_id        = $_REQUEST['form_id'];
$zone           = $_REQUEST['zone'];

if ($_REQUEST["mode"] == "new")             { 
  $newid = formSubmit($table_name, $_POST, $id, $userauthorized);
  addForm($encounter, $form_name, $newid, $form_folder, $pid, $userauthorized);
} elseif ($_REQUEST["mode"] == "update")    { 
  // The form is submitted to be updated.
  // Submission are ongoing and then the final unload of page changes the 
  // DOM variable $("#final") to == 1.  As one draws on the HTML5 canvas, each step is saved incrementally allowing
  // the user to go back through their history should they make a drawing error or simply want to reverse a
  // step.  They are saved client side now.  On finalization, we need to update the _VIEW.png file with the current
  // canvases.  

  // Is the form LOCKED? when and by whom, and esign it according to openEMR specs...
  // Need help here.
  // If this is LOCKED by esigning,tell user to move along, nothing to see here... Goto Report?
  // if this form/encounter? is esigned and locked, then return without touching data.

  // We also have the situation where the form is being udated in one place but opened in another.
  // We need to have only ONE be able to update the DB.
  // Give each instance of a form a uniqueID.  If the form has no owner, update it with this uniqueID.
  // If it has an owner, ask if they wish to take ownership.  If yes, any other attemot to save fiedls/form
  // are denied and the return code says you are not the owner...

if ($_REQUEST['finalize'] == '1') { // we are releasing the form by closing the page, so unlock it
  $_REQUEST['LOCKED'] ='0';
  $_REQUEST['LOCKEDDATE'] ='';
  $_REQUEST['LOCKEDBY'] ='no one';
  echo "\nUNLOCKED";  
  $query = "update form_eye_mag set LOCKED='',LOCKEDBY='',LOCKEDDATE='' where id=?";
  sqlQuery ($query,array($form_id));
  exit;
} else {

  $query = "SELECT LOCKED,LOCKEDBY,LOCKEDDATE from form_eye_mag WHERE ID=?";
  $lock = sqlQuery($query,array($form_id));
  if (($lock['LOCKED']) && ($_REQUEST['uniqueID'] != $lock['LOCKEDBY']))  { 
      // we are not the owner or it is not new so it is locked
      //Did the user send a demand to take ownership?
    // echo "1. LOCKEDBY = ".$lock['LOCKEDBY']. " and ".$_REQUEST['ownership']." and ".$_REQUEST['LOCKEDBY'];
   // var_dump($_REQUEST['ownership']);exit;
    if ($lock['LOCKEDBY'] != $_REQUEST['ownership']) {
      //tell them they are locked out by another user now
      // eg. echo "CODE 400";  For now, console log to figure it out.
       echo "Code 400";
       // No return a JSON encoded string with current LOCK ID
       // echo "Sorry Charlie, you get nothing since this is locked...  No save for you!";
        exit;
    } elseif ($lock['LOCKEDBY'] == $_REQUEST['ownership']) { 
      //then they are taking ownership - all others get locked...
      // new LOCKEDBY becomes our uniqueID LOCKEDBY
      $_REQUEST['LOCKED'] = '1';
      $_REQUEST['LOCKEDBY'] = $_REQUEST['uniqueID'];
//      echo "2. LOCKEDBY = ".$_REQUEST['LOCKEDBY'];
    }
  } elseif (!$lock['LOCKED']) { // it is not locked yet
    $_REQUEST['LOCKED'] = '1';
    echo "OK then ".$_REQUEST['LOCKEDBY']." - ".$form_id;
    $query = "update form_eye_mag set LOCKED=?,LOCKEDBY=? where id=?";
  sqlQuery ($query,array('1',$_REQUEST['LOCKEDBY'],$form_id));
  exit;

  }

if (!$_REQUEST['LOCKEDBY'])  $_REQUEST['LOCKEDBY'] = rand();
//echo "LOCKEDBY = ".$_REQUEST['LOCKEDBY'];
}


  //OK WE OWN IT, let's save it.
 
  // Any field that exists in the database could be updated
  // so we need to exclude the important ones...
  // id  date  pid   user  groupname   authorized  activity.  Any other just add them below.
  // Doing it this way means you can add new fields on a web page and in the DB without touching this function.
  // The update feature still works because it only updates columns that are in the table you are working on.  
  // Building an undo feature:  Ctl-Z is curently client side per field.  Global action ctrl-z not implemented.
  // A shadow table could exist and each update request is added there also server side.
  // An UNDO request goes down one.
  // We will need to send a variable to the form with the UNDO table entry info.
  // This table will have an incremental field, pid and new field.  Just save it for now.
  // When done with the chart, or maybe on a repetitive frequency, this UNDO table will be purged
  // Maybe an esign button in the document to do all that openEMR does + this stuff?  We'll see.

  $query = "SHOW COLUMNS from form_eye_mag";
  $result = sqlStatement($query);

  if (!$result) {
    return 'Could not run query: No columns found in your table!  ' . mysql_error();
    exit;
  }
  $fields = array();
  
  if (sqlNumRows($result) > 0) {
    while ($row = sqlFetchArray($result)) {
      //exclude critical columns/fields from update
      if ($row['Field'] == 'id' or 
         $row['Field'] == 'date' or 
         $row['Field'] == 'pid' or 
         $row['Field'] == 'user' or 
         $row['Field'] == 'groupname' or 
         $row['Field'] == 'authorized' or 
         $row['Field'] == 'activity') 
        continue;
    if (isset($_POST[$row['Field']])) $fields[$row['Field']] = $_POST[$row['Field']];
    }
    /** checkboxes need to be entered manually as they are only submitted when they are checked
      * if NOT checked they are NOT overridden in the DB, so DB won't change
      *  unless we include them into the $fields array as "0"...
      */
    if (!$_POST['alert']) $fields['alert'] = '0';
    if (!$_POST['oriented']) $fields['oriented'] = '0';
    if (!$_POST['confused']) $fields['confused'] = '0';
    if (!$_POST['MOTILITYNORMAL']) $fields['MOTILITYNORMAL'] = '0';
    if (!$_POST['ACT']) $fields['ACT'] = '0';
    if (!$_POST['DIL_RISKS']) $fields['DIL_RISKS'] = '0';
    if (!$_POST['ATROPINE']) $fields['ATROPINE'] = '0';
    if (!$_POST['CYCLOGYL']) $fields['CYCLOGYL'] = '0';
    if (!$_POST['CYCLOMYDRIL']) $fields['CYCLOMYDRIL'] = '0';
    if (!$_POST['NEO25']) $fields['NEO25'] = '0';
    if (!$_POST['TROPICAMIDE']) $fields['TROPICAMIDE'] = '0';
    if (!$_POST['BALANCED']) $fields['BALANCED'] = '0';
    if (!$_POST['ODVF1']) $fields['ODVF1'] = '0';
    if (!$_POST['ODVF2']) $fields['ODVF2'] = '0';
    if (!$_POST['ODVF3']) $fields['ODVF3'] = '0';
    if (!$_POST['ODVF4']) $fields['ODVF4'] = '0';
    if (!$_POST['OSVF1']) $fields['OSVF1'] = '0';
    if (!$_POST['OSVF2']) $fields['OSVF2'] = '0';
    if (!$_POST['OSVF3']) $fields['OSVF3'] = '0';
    if (!$_POST['OSVF4']) $fields['OSVF4'] = '0';
   
    $success = formUpdate($table_name, $fields, $form_id, $_SESSION['userauthorized']);
  //  $table_name = "form_".$form_folder."_undo";
 //   $update_undo = formSubmit($table_name, $fields, $form_id, $_SESSION['userauthorized']);
    return $success;
  }
} elseif ($_REQUEST["mode"] == "retrieve")  { 
    $query = "SELECT * FROM patient_data where pid=?";
    $pat_data =  sqlQuery($query,array($pid));
    @extract($pat_data);

    $query = "SELECT * FROM users where id = ?";
    $prov_data =  sqlQuery($query,array($_SESSION['authUserID']));
    $providerID = $prov_data['fname']." ".$prov_data['lname'];
      //the date in form_eye_mag is the date the form was created 
      //and may not equal the date of the encounter so we must make a special request to get the old data:
    $query = "select form_eye_mag.id as id_to_show from form_eye_mag left 
              join forms on form_eye_mag.id=forms.form_id and form_eye_mag.pid=forms.pid 
              where 
              forms.form_name = ? and 
              forms.id = ? and 
              forms.deleted !='1'  
              ORDER BY forms.date DESC";
    $visit_data =  sqlQuery($query,array($form_folder,$id_to_show));
    $query = "select form_eye_mag.id as id_to_show from form_eye_mag where id=?";
    $visit_data =  sqlQuery($query,array($id_to_show));
    @extract($visit_data);
      //ALL VARIABLES GET EXTRACTED AND ARE READY FOR USE.
      //HERE WE DECIDE WHAT WE WANT TO SHOW = A SEGMENT, A ZONE OR EVEN A VALUE...  
      
    if ($_REQUEST['PRIORS_query']) {
      include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
      display_PRIOR_section($_REQUEST['zone'],$_REQUEST['orig_id'],$_REQUEST['id_to_show'],$pid);
      return; 
    }
} 

/**  
 * Save the canvas drawings  
 */
if ($_REQUEST['canvas']) {
//try using addNewDocument from document.php

  require_once($GLOBALS['fileroot']."/controllers/C_Document.class.php");
function addNewDocument($name,$type,$tmp_name,$error,$size,$owner='',$patient_id_or_simple_directory="00",$category_id='1',$higher_level_path='',$path_depth='1') {

    if (empty($owner)) {
      $owner = $_SESSION['authUserID'];
    }

    // Build the $_FILES array
    $TEMP_FILES = array();
    $TEMP_FILES['file']['name'][0]="OU_".$zone."_VIEW.png";
    $TEMP_FILES['file']['type'][0]=$type;
    $TEMP_FILES['file']['tmp_name'][0]=$tmp_name;
    $TEMP_FILES['file']['error'][0]=$error;
    $TEMP_FILES['file']['size'][0]=$size;
    $_FILES = $TEMP_FILES;

    // Build the parameters
    $_GET['higher_level_path']=$higher_level_path;
    $_GET['patient_id']=$patient_id_or_simple_directory;
    $_POST['destination']='';
    $_POST['submit']='Upload';
    $_POST['path_depth']=$path_depth;
    $_POST['patient_id']=(is_numeric($patient_id_or_simple_directory) && $patient_id_or_simple_directory>0) ? $patient_id_or_simple_directory : "00";
    $_POST['category_id']=$category_id;
    $_POST['process']='true';

    // Add the Document and return the newly added document id
    $cd = new C_Document();
    $cd->manual_set_owner=$owner;
    $cd->upload_action_process();
    $v = $cd->get_template_vars("file");
    if (!isset($v) || !$v) return false;
    return array ("doc_id" => $v[0]->id, "url" => $v[0]->url); 
}

  /**
   * Make the directory for this encounter to store the images
   * we are storing the images after the mouse leaves the canvas here:
   * $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/eye_mag/".$pid."/".$encounter
   * which for the "default" practice is going to be here:
   * /openemr/sites/default/documents/$pid/eye_mag/$encounter  
   * Each file also needs to be filed as a Document to retrieve through controller to keep HIPAA happy
   * Documents directory and subdirs are NOT publicly accessible directly (w/o acl checking)
   */
  
  $location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid;
 
  if (!is_dir($location."/".$form_folder."/".$encounter)) {
    if (!is_dir($location)) {
                mkdir($location, 0755, true);
                mkdir($location."/".$form_folder, 0755, true);
                mkdir($location."/".$form_folder."/".$encounter, 0755, true);
    } elseif (!is_dir($location."/".$form_folder)) {
                mkdir($location."/".$form_folder, 0755, true);
                mkdir($location."/".$form_folder."/".$encounter, 0755, true);
    } elseif (!is_dir($location."/".$form_folder."/".$encounter)) {
                mkdir($location."/".$form_folder."/".$encounter, 0755, true);
    } 
  }

  /** 
   *    BASE, found in forms/$form_folder/images eg. OU_EXT_BASE.png
   *          BASE is the blank image to start from and can be customized. 
   *    VIEW, found in /sites/$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter
   *    TEMP, intermediate png merge file of new drawings with BASE or previous VIEW
   *          Currently not implementd/used since we merge them client side, but may be later for layers?
   *    side, optional.  To add OD and OS with pre-existing OU.  Will next increase 
   *          to three png files (OU,OD,OS) per LOCATION (HPI,PMH,EXT,ANTSEG,RETINA,NEURO,IMPPLAN) 
   *          Since we only have one drawing so far.  Can extend this to a 3D plot/interpretation (X100Y46Z359) when 
   *          integrating layers with objects, perhaps radiology, OCT or 3D Ultrasound
   *          to pick out images at a specific angle/slice.  For now just use OU.
   */
  $side = "OU";
  $storage = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter;  
  $data =$_POST["imgBase64"];
  $data=substr($data, strpos($data, ",")+1);
  $data=base64_decode($data);
  $file_draw = $storage."/OU_".$zone."_VIEW.png";
  file_put_contents($file_draw, $data);


   /** 
    *  We have a file in the right place
    *  We need to tell the documents engine about this file, add it to the documents BUT NOT doc_to_cat tables.
    *  So we can pullit up later for display.  It is part of the official record.
    */
  $file_here ="file://".$storage."/".$side."_".$zone."_VIEW.png";
  $doc = sqlQuery("Select * from documents where url='".$file_here."'");
  if ($doc['id'] < '1') {
    $doc = sqlQuery("select MAX(id)+1 as id from documents");
  }
    $sql = "REPLACE INTO documents set 
              id=?,
              encounter_id=?,
              type='file_url',size=?,
              date=NOW(),
              mimetype='image/png',
              owner=?,
              foreign_id=?,
              docdate=NOW(),
              path_depth='3',
              url=?";
              if ($doc['id'] == '0') { $doc['id'] ='1';}
    $doc_id = sqlQuery($sql,array($doc['id'],$encounter,filesize($file_here),$_SESSION['authUserID'],$pid,$file_here));  
  /*
    $category = sqlQuery("select id from categories where name='Drawings'");       
    $sql = "REPLACE INTO categories_to_documents set category_id = ?, document_id = ?";
    sqlQuery($sql,array($category['id'],$doc['id']));  
  */
  
  exit;
}

if ($_REQUEST['copy']) {
  copy_forward($_REQUEST['zone'],$_REQUEST['copy_from'],$_SESSION['ID'],$pid);
}

function debug($local_var) {
    echo "<pre><BR>We are in the debug function.<BR>";
    echo "Passed variable = ". $local_var . " <BR>";
    print_r($local_var);
    exit;
}

function merge($filename_x, $filename_y, $filename_result) {
  /**
   *    Three png files (OU,OD,OS) per LOCATION (EXT,ANTSEG,RETINA,NEURO) 
   *    BASE, found in forms/$form_folder/images eg. OU_EXT_BASE.png
   *          BASE is the blank image to start from and can be customized. Currently 432x150px
   *    VIEW, found in /sites/$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter
   *    TEMP, intermediate png merge file of new drawings with BASE or previous VIEW
   *          These are saved to be used in an undo feature...
   */
  /*  
  This section
  if (file_exists($storage."/OU_".$zone."_VIEW.png")) { //add new drawings to previous for this encounter
      $file_base = $storage."/OU_".$zone."_VIEW.png";
    } else  { //start from the base image
      $file_base = $GLOBALS['webserver_root']."/interface/forms/".$form_folder."/images/OU_".$zone."_BASE.png";
    }
    //merge needs to store to a separate file first, then rename to new VIEW
    $file_temp = $storage."/OU_".$zone."_TEMP.png"; 
    $file_here = $storage."/OU_".$zone."_VIEW.png";
    merge( $file_draw, $file_base, $file_temp);
    rename( $file_temp , $file_here );
   */
  // Get dimensions for specified images
  list($width_x, $height_x) = getimagesize($filename_x);
  list($width_y, $height_y) = getimagesize($filename_y);

  // Create new image with desired dimensions
  $image = imagecreatetruecolor($width_y, $height_y);

  // Load images and then copy to destination image
  $image_x = imagecreatefrompng($filename_x);
  $image_y = imagecreatefrompng($filename_y);

  imagecopy($image, $image_y, 0, 0, 0, 0, $width_x, $height_x);
  imagecopy($image, $image_x, 0, 0, 0, 0, $width_x, $height_x);
 
  // Save the resulting image to disk (as png)
  imagepng($image, $filename_result);

  // Clean up
  imagedestroy($image);
  imagedestroy($image_x);
  imagedestroy($image_y);
}
//finalize($pid,$encounter); //since we are storing images client side, we may not need this...
exit;
?>