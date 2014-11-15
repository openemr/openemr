<?php
/*
 * This saves the submitted form: new, updates or 
 * It also saves User preferences for displaying the form as the user desires.
 * With each use the preferences change if the user changes them.
 * It also retrieves old records so the user can flip through old values within this form,
 * ideally with the intent that the old data can be carried forward.  
 * Yeah, gotta write that carry forward stuff yet.  Next week it'll be done?
 */
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$table_name = "form_eye_mag";
$form_name = "eye_mag";
$form_folder = "eye_mag";

$escapedGet = array_map('mysql_real_escape_string', $_REQUEST); @extract($escapedGet);
$escapedGet = array_map('mysql_real_escape_string', $_SESSION); @extract($escapedGet);

if ($encounter == "" && !$_GET["id"]) {
    return "Sorry Charlie...";
    exit;
}
if ($PREFS_VA) {
   
  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','VA','Vision','".$_SESSION['authId']."','RS','51','".$PREFS_VA."','1') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_VA."'";
  //echo $query;
  sqlQuery($query);
  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
              VALUES 
              ('PREFS','W','Current Rx','".$_SESSION['authId']."','W','52','".$PREFS_W."','2') 
              on DUPLICATE KEY UPDATE VALUE='".$PREFS_W."'";
   // echo $query;
  sqlQuery($query);

  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','MR','Manifest Refraction','".$_SESSION['authId']."','MR','53','".$PREFS_MR."','3') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_MR."'";
  //echo $query;
  sqlQuery($query);

  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','CR','Cycloplegic Refraction','".$_SESSION['authId']."','CR','54','".$PREFS_CR."','4') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_CR."'";
  //echo $query;
  sqlQuery($query);

  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','CTL','Contact Lens','".$_SESSION['authId']."','CTL','55','".$PREFS_CTL."','5') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_CTL."'";
  //echo $query;
  sqlQuery($query);

  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','ADDITIONAL','Additional Data Points','".$_SESSION['authId']."','ADDITIONAL','56','".$PREFS_ADDITIONAL."','6') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_ADDITIONAL."'";
  //echo $query;
  sqlQuery($query);
      
  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','CLINICAL','CLINICAL','".$_SESSION['authId']."','CLINICAL','57','".$PREFS_CLINICAL."','7') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_ACLINICAL."'";
  //echo $query;
  sqlQuery($query);

    $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','EXAM','EXAM','".$_SESSION['authId']."','EXAM','58','".$PREFS_EXAM."','8') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_EXAM."'";
  //echo $query;
  sqlQuery($query);

  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','CYLINDER','CYL','".$_SESSION['authId']."','CYL','59','".$PREFS_CYL."','9') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_CYL."'";
  //echo $query;
  sqlQuery($query);
  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','EXT_VIEW','External View','".$_SESSION['authId']."','EXT_VIEW','60','".$PREFS_EXT_VIEW."','10') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_EXT_VIEW."'";
  //echo $query;
  sqlQuery($query);

  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','ANTSEG_VIEW','Anterior Segment View','".$_SESSION['authId']."','ANTSEG_VIEW','61','".$PREFS_ANTSEG_VIEW."','11') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_ANTSEG_VIEW."'";
  //echo $query;
  sqlQuery($query);

  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','RETINA_VIEW','Retina View','".$_SESSION['authId']."','RETINA_VIEW','62','".$PREFS_RETINA_VIEW."','12') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_RETINA_VIEW."'";
  //echo $query;
  sqlQuery($query);
  $query = "INSERT INTO dbSelectFindings (PEZONE,LOCATION,LOCATION_text,id,selection,ZONE_ORDER,VALUE,ordering) 
            VALUES 
            ('PREFS','NEURO_VIEW','Neuro View','".$_SESSION['authId']."','NEURO_VIEW','63','".$PREFS_NEURO_VIEW."','13') 
            on DUPLICATE KEY UPDATE VALUE='".$PREFS_NEURO_VIEW."'";
  //echo $query;
  sqlQuery($query);
        return;
}

if ($encounter == "") $encounter = date("Ymd");


if ($_GET["mode"] == "new") {
  $newid = formSubmit($table_name, $_POST, $id, $userauthorized);
  addForm($encounter, $form_name, $newid, $form_folder, $pid, $userauthorized);
       /*
       COPIED form view.php
                $query = "select * from form_eye_mag JOIN forms on form_eye_mag.encounter=forms.id where form_eye_mag.pid='".$pid."' and forms.deleted !='1'";
                echo $query;
                problem is the way the form is being stored is incorrect when a new form is made.  
                Need to fix the part in save.php to save a new form correctly.
                To do that you need to define the variables openEMR needs to know about to link them, hide them, unhide them.
                I added a new field to the eye_mag DB "encounter", to store the encounter number associated with this visit.
                That should make it work over there...  Maybe I should move this over there? hum.  OK, compromise and copy and paste it...
                The NEWFORM creation routine or functions or whatever is located in the form.inc or api.inc file in the library.
        */

} elseif ($_GET["mode"] == "update") {
           
        $query = "SHOW COLUMNS from form_eye_mag";
        $result = sqlStatement($query);
        if (!$result) {
            return 'Could not run query: ' . mysql_error();
            exit;
        }
        $fields = array();
        if (mysql_num_rows($result) > 0) {
          //checkboxes need to entered manually as they are only submitted when they are checked
          //if checked they are overridden below with the "on" value...
          $fields['DIL_RISKS'] = 'off';
          while ($row = mysql_fetch_assoc($result)) {
            if ($_POST[$row['Field']] >'') {
              $fields[$row[Field]] = $_POST[$row['Field']];
              echo $row[Field] ." = ".$_POST[$row['Field']]."<br />";
            }
          }
        }

        
        $success = formUpdate($table_name, $fields, $id, $userauthorized);
        echo "formUpdate returned a code of " .$success."<br />";
        return;
        exit;
} elseif ($_GET["mode"] == "retrieve") {
    // get pat_data and user_data

      $query = "SELECT * FROM patient_data where pid='$pid'";
      $pat_data =  sqlQuery($query);
      @extract($pat_data);

      $query = "SELECT * FROM users where id = '".$_SESSION['authUserID']."'";
      $prov_data =  sqlQuery($query);
      $providerID = $prov_data['fname']." ".$prov_data['lname'];
    //OK we got it now...

    // OK let's retrieve the data from this PRIOR eye_mag visit
  //  $objIns = formFetch("form_eye_mag", $visit_number);  //#Use the formFetch function from api.inc to get values for existing form. 
    //@extract($objIns);

//the date in form_eye_mag is the date the form was created and may not equal the date of the encounter so we must make a pecial request to get the old data:
         $query = "select * from form_eye_mag left join forms on form_eye_mag.id=forms.form_id and form_eye_mag.pid=forms.pid where forms.form_name = '".$form_folder."' and forms.date = '".$visit_number."' and forms.deleted !='1'  ORDER BY forms.date DESC";
    $visit_data =  sqlQuery($query);
      @extract($visit_data);

    /* update existing record */
    //$update_query = "UPDATE form_eye_mag set ";
    // _POST contains variables, GET contain update/new...
    // the the key value pairs are in the DB, Post them.
    //say they are not, like ODIOPAP, we need logic to store them.
    //NO we can update the DB STRUCTURE to accomodate this field as some patients will have both types of IOP measurement
    //select all the forms for this patientid, grab their number and put them into an associative array for the breadcrumb arrows
//echo $RBROW;
    //HERE WE DECIDE WHAT WE WANT TO SHOW = A SEGMENT, A ZONE OR EVEN A VALUE...  ALL VARIABLES ARE EXTRCATED AND READY FOR USE.
   // var_dump($_POST);exit;
    if ($PRIORS_query) {
    //show the prior data
      include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
      display_section($zone,$visit_date,$pid);
      return; 
    }
}
    
// debug($_SESSION);
  //      exit;
function debug($local_var) {
    echo "<pre><BR>We are in the debug function.<BR>";
    echo "Passed variable = ". $local_var . " <BR>";
    print_r($local_var);
    exit;
}

exit;
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
