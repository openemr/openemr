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

//echo "<pre>hello";
//var_dump($_REQUEST);
//exit;
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("php/eye_mag_functions.php");
include_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");

//we need privileges to be restricted here?
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/

$table_name = "form_eye_mag";
$form_name = "eye_mag";
$form_folder = "eye_mag";
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
@extract($_SESSION);
@extract($_REQUEST);

$id = $_GET['id'];

if ($encounter == "" && !$id) {
    return "Sorry Charlie...";
    exit;
}

/**  
 * Save/update the preferences  
 * could probably make these values into an array and loop through it to look prettier but this works...
 * and maybe it helps people understand what and why we are doing what we are doing?
 * Leave it to the professionals...
 */
if ($AJAX_PREFS) { 
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
                VALUES 
                ('PREFS','VA','Vision',?,'RS','51',?,'1') 
                on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_VA,$PREFS_VA));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
                VALUES 
                ('PREFS','W','Current Rx',?,'W','52',?,'2') 
                on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_W,$PREFS_W));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','MR','Manifest Refraction',?,'MR','53',?,'3') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_MR,$PREFS_MR));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CR','Cycloplegic Refraction',?,'CR','54',?,'4') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_CR,$PREFS_CR));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CTL','Contact Lens',?,'CTL','55',?,'5') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_CTL,$PREFS_CTL));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ADDITIONAL','Additional Data Points',?,'ADDITIONAL','56',?,'6') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_ADDITIONAL,$PREFS_ADDITIONAL));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CLINICAL','CLINICAL',?,'CLINICAL','57',?,'7') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_CLINICAL,$PREFS_CLINICAL));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','EXAM','EXAM',?,'EXAM','58',?,'8') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_EXAM,$PREFS_EXAM));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','CYLINDER','CYL',?,'CYL','59',?,'9') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_CYL,$PREFS_CYL));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','HPI_VIEW','HPI View',?,'HPI_VIEW','60',?,'10') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_HPI_VIEW,$PREFS_HPI_VIEW));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','EXT_VIEW','External View',?,'EXT_VIEW','60',?,'10') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_EXT_VIEW,$PREFS_EXT_VIEW));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ANTSEG_VIEW','Anterior Segment View',?,'ANTSEG_VIEW','61',?,'11') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_ANTSEG_VIEW,$PREFS_ANTSEG_VIEW));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','RETINA_VIEW','Retina View',?,'RETINA_VIEW','62',?,'12') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_RETINA_VIEW,$PREFS_RETINA_VIEW));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','NEURO_VIEW','Neuro View',?,'NEURO_VIEW','63',?,'13') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_NEURO_VIEW,$PREFS_NEURO_VIEW));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ACT_VIEW','ACT View',?,'ACT_VIEW','64',?,'14') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_ACT_VIEW,$PREFS_ACT_VIEW));
    $query = "INSERT INTO form_eye_mag_prefs (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','ACT_SHOW','ACT Show',?,'ACT_SHOW','65',?,'15') 
              on DUPLICATE KEY UPDATE VALUE=?";
    sqlQuery($query,array($_SESSION['authId'],$PREFS_ACT_SHOW,$PREFS_ACT_SHOW)); 
}
/**
  * ADD ANY NEW PREFERENCES HERE, and as a hidden field in the body.  I prefer this vs Session items but that would
  * also work here.  No good reason.
  */

/** <!-- End Preferences --> **/

/**  
 * Create, update or retrieve a form and its values  
 */
if ($encounter == "") $encounter = date("Ymd");
if ($_GET["mode"] == "new")             { 
  $newid = formSubmit($table_name, $_POST, $id, $userauthorized);
  addForm($encounter, $form_name, $newid, $form_folder, $pid, $userauthorized);
} elseif ($_GET["mode"] == "update")    { 
  // The form is submitted to be updated.
  // Submission are ongoing and then the final unload of page changes the 
  // DOM variable $("#final") to == 1.  As one draws on the HTML5 canvas, each step is saved incrementally allowing
  // the user to go back through their history should they make a drawing error or simply want to reverse a
  // step.  On finalization, we need to cleanup the drawing history images and leave just the final one.
  // For example, OU_EXTERNAL_DRAW_0.png through OU_EXTERNAL_DRAW_100.png exist since the user did 101 drawing
  // events in the EXTernal zone!  Now we only want OU_".$zone."_DRAW.png".  Clean up the directories:
  if ($_REQUEST['final'] =='1') {
    $storage = $GLOBALS['OE_SITES_BASE']."/".$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter;
    $zones = array("EXT","ANTSEG","RETINA","NEURO","VISION","IMPLAN");
    foreach ($zones as &$zone) {
      echo "unlinking ".$GLOBALS['OE_SITES_BASE']."/".$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter."/OU_".$zone."_DRAW_*.png<br /";
      unlink($GLOBALS['OE_SITES_BASE']."/".$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter."/OU_".$zone."_DRAW_*.png");
    }
    // but alas this is not deleting the files.  And the "go back" feature is not written yet on the drawings
  }
  
  // Any field that exists in the database can be updated
  // so we need to exclude the important ones...
  // id  date  pid   user  groupname   authorized  activity  .  Any other just add them below.
  // Doing it this way means you can add new fields and the update feature still works.    
  $query = "SHOW COLUMNS from form_eye_mag";
  $result = sqlStatement($query);
  if (!$result) {
    return 'Could not run query: ' . mysql_error();
    exit;
  }
  $fields = array();
  if (sqlNumRows($result) > 0) {
    while ($row = sqlFetchArray($result)) {
      if ($row['Field'] == 'id' or 
         $row['Field'] == 'date' or 
         $row['Field'] == 'pid' or 
         $row['Field'] == 'user' or 
         $row['Field'] == 'groupname' or 
         $row['Field'] == 'authorized' or 
         $row['Field'] == 'activity') continue;
      if (isset($_POST[$row['Field']])) $fields[$row['Field']] = $_POST[$row['Field']];
    }
    /** checkboxes need to be entered manually as they are only submitted when they are checked
      * if NOT checked they are NOT overridden in the DB, so DB won't change
      *  unless we include them into the $fields array as "0"...
      */
    if (!$_POST['MOTILITYNORMAL']) $fields['MOTILITYNORMAL'] = '0';
    if (!$_POST['ACT']) $fields['ACT'] = '0';
    if (!$_POST['DIL_RISKS']) $fields['DIL_RISKS'] = '0';
    if (!$_POST['ATROPINE']) $fields['ATROPINE'] = '0';
    if (!$_POST['CYCLOGYL']) $fields['CYCLOGYL'] = '0';
    if (!$_POST['CYCLOMYDRIL']) $fields['CYCLOMYDRIL'] = '0';
    if (!$_POST['NEO25']) $fields['NEO25'] = '0';
    if (!$_POST['TROPICAMIDE']) $fields['TROPICAMIDE'] = '0';
    if (!$_POST['BALANCED']) $fields['BALANCED'] = '0';
    if (!$_POST['RX1']) $fields['RX1'] = '0';

    $success = formUpdate($table_name, $fields, $form_id, $userauthorized);
    return $success;
  }
} elseif ($_GET["mode"] == "retrieve")  { 
    $query = "SELECT * FROM patient_data where pid=?";
    $pat_data =  sqlQuery($query,array($pid));
    @extract($pat_data);

    $query = "SELECT * FROM users where id = ?";
    $prov_data =  sqlQuery($query,array($_SESSION['authUserID']));
    $providerID = $prov_data['fname']." ".$prov_data['lname'];
      //the date in form_eye_mag is the date the form was created 
      //and may not equal the date of the encounter so we must make a special request to get the old data:
   /* $query = "select form_eye_mag.id as id_to_show from form_eye_mag left 
              join forms on form_eye_mag.id=forms.form_id and form_eye_mag.pid=forms.pid 
              where 
              forms.form_name = ? and 
              forms.id = ? and 
              forms.deleted !='1'  
              ORDER BY forms.date DESC";
              */
    //$visit_data =  sqlQuery($query,array($form_folder,$id_to_show));
   // $query = "select form_eye_mag.id as id_to_show from form_eye_mag where id=?";
   // $visit_data =  sqlQuery($query,array($id_to_show));
   // @extract($visit_data);
      //HERE WE DECIDE WHAT WE WANT TO SHOW = A SEGMENT, A ZONE OR EVEN A VALUE...  
      //ALL VARIABLES GET EXTRACTED AND ARE READY FOR USE.
    
    if ($PRIORS_query) {
      //$id_to_show = $id;
      include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
      display_section($zone,$orig_id,$id_to_show,$pid);
      return; 
    }
}


/**  
 * Save the canvas drawings  
 */

if ($canvas) {
  /**
   * Make the directory for this encounter to store the image
   * we are storing the images after the mouse leaves the canvas here:
   * $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/eye_mag/".$pid."/".$encounter
   * which for the "default" practice is going to be here:
   * /openemr/sites/default/documents/eye_mag/$pid/$encounter  
   */
  if (!is_dir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid)) {
              mkdir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid, 0755, true);
              mkdir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder, 0755, true);
              mkdir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter, 0755, true);
  } elseif (!is_dir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder)) {
              mkdir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder, 0755, true);
              mkdir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter, 0755, true);
  } elseif (!is_dir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter)) {
              mkdir($GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter, 0755, true);
  }

  /**
   *    Three png files (OU,OD,OS) per LOCATION (EXT,ANTSEG,RETINA,NEURO) 
   *    BASE, found in forms/$form_folder/images eg. OU_EXT_BASE.png
   *          BASE is the blank image to start from and can be customized. Currently 432x150px
   *    VIEW, found in /sites/$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter
   *    TEMP, intermediate png merge file of new drawings with BASE or previous VIEW
   */
  $storage = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/".$pid."/".$form_folder."/".$encounter;
  if (file_exists($storage."/OU_".$zone."_VIEW.png")) { //add new drawings to previous for this encounter
    $file_base = $storage."/OU_".$zone."_VIEW.png";
  } else  { //start from the base image found in eye_mag directory /images
    $file_base = $GLOBALS['webserver_root']."/interface/forms/".$form_folder."/images/OU_".$zone."_BASE.png";
  }
  $data =$_POST["imgBase64"];
  $data=substr($data, strpos($data, ",")+1);
  $data=base64_decode($data);
  $file_draw = $storage."/OU_".$zone."_DRAW.png";
  file_put_contents($file_draw, $data);

  /**
   *    Three png files (OU,OD,OS) per LOCATION (EXT,ANTSEG,RETINA,NEURO) 
   *    BASE, found in forms/$form_folder/images eg. OU_EXT_BASE.png
   *          BASE is the blank image to start from and can be customized. Currently 432x150px
   *    VIEW, found in /sites/$_SESSION['site_id']."/".$form_folder."/".$pid."/".$encounter
   *    TEMP, intermediate png merge file of new drawings with BASE or previous VIEW
   */
  if (file_exists($storage."/OU_".$zone."_VIEW.png")) { //add new drawings to previous for this encounter
    $file_base = $storage."/OU_".$zone."_VIEW.png";
  } else  { //start from the base image
    $file_base = $GLOBALS['webserver_root']."/interface/forms/".$form_folder."/images/OU_".$zone."_BASE.png";
  }
  $file_temp = $storage."/OU_".$zone."_TEMP.png"; //merge needs to store to a separate file first, then rename to new VIEW
  merge( $file_draw, $file_base, $file_temp);
  rename( $file_temp , $storage."/OU_".$zone."_VIEW.png" );
  // Store pointer to this in DB table form_eye_mag_draw
  // To be done yet.
  /** HISTORY FEATURE: Images.  
    * Store this latest drawing separately, incrementally, in the directory so user can go backwards - 
    * canvas stores everything in real time! We need to be able to correct a slip-up by reversing through
    * old images just like the PRIORS feature for the text fields but using today's most recent drawings...
    */
  $file_history = $storage."/OU_".$zone."_DRAW_1";
  $file_store= $file_history.".png";
  $additional = '1';
  //limit it to 10 for now...
  while (file_exists($file_history.".png")) {
    //echo $file_history;
        $file_history = $storage."/OU_".$zone."_DRAW_". $additional++;
        $file_store= $file_history.".png";
  }
  copy($storage."/OU_".$zone."_VIEW.png",$file_store);

}

if ($copy) {
//  echo $zone,$id,$form_id,$pid;
//  exit;
  copy_forward($zone,$copy_from,$copy_to,$pid);
}
function debug($local_var) {
    echo "<pre><BR>We are in the debug function.<BR>";
    echo "Passed variable = ". $local_var . " <BR>";
    print_r($local_var);
    exit;
}

function merge($filename_x, $filename_y, $filename_result) {

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
exit;
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>