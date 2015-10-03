<?php
/** 
 * forms/eye_mag/view.php 
 * 
 * Central view for the eye_mag form.  Here is where all new data is entered
 * New forms are created via new.php and then this script is displayed.
 * Edit requsts come here too...
 * 
 * Copyright (C) 2015 Raymond Magauran <magauran@MedFetch.com> 
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
 *   
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;
include_once("../../globals.php");
include_once("$srcdir/acl.inc");
include_once("$srcdir/lists.inc");
include_once("$srcdir/api.inc");
include_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");

$form_name = "eye_mag";
$form_folder = "eye_mag";

include_once("../../forms/".$form_folder."/php/".$form_folder."_functions.php");
//@extract($_REQUEST); 
//@extract($_SESSION);
$form_id    = $_REQUEST['id']; //what form are we working on?  Each encounter can have multiple forms, like the eye_mag, ROS, Vitals, whatever
$action     = $_REQUEST['action'];
$finalize   = $_REQUEST['finalize'];

//$encounter  = $_REQUEST['encounter'];
//if ($encounter =='') $encounter = $_SESSION['id'];

$id         = $_REQUEST['id'];
$display    = $_REQUEST['display'];

$pid        = $_REQUEST['pid'];
if ($pid =='') $pid = $_SESSION['pid'];

$refresh    = $_REQUEST['refresh'];
if ($_REQUEST['url']) {
  redirector($_REQUEST['url']);
  exit;
}
// Get user preferences, for this user
$query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND (id=?) ORDER BY id,ZONE_ORDER,ordering";
$result = sqlStatement($query,array($_SESSION['authId']));
while ($prefs= sqlFetchArray($result))   {    
    @extract($prefs);    
    $$LOCATION = $VALUE; 

}
// exit;
// get pat_data and user_data
$query = "SELECT * FROM patient_data where pid=?";
$pat_data =  sqlQuery($query,array($pid));

$query = "SELECT * FROM users where id = ?";
$prov_data =  sqlQuery($query,array($_SESSION['authUserID']));
$providerID = $prov_data['fname']." ".$prov_data['lname'];

$query="select form_encounter.date as encounter_date,form_encounter.*, form_eye_mag.* from form_eye_mag ,forms,form_encounter 
                    where 
                    form_encounter.encounter =? and 
                    form_encounter.encounter = forms.encounter and 
                    form_eye_mag.id=forms.form_id and
                    forms.deleted != '1' and 
                    form_eye_mag.pid=? ";        
 
$encounter_data =sqlQuery($query,array($encounter,$pid));
@extract($encounter_data); //gotta have it. encodes whole form and every value is used, mostly ;) so far...

// build $PMSFH array
$PMSFH = build_PMSFH($pid); 

/*
  Re: incorporating e-signing and document finalization
  If a chart is "completed", the user should not be able to open this form for editing, just viewing.
  This is where the report.php becomes important.
  If the chart is "completed", ie. done, locked, only "amendable", there must be a link to the report only.
  This is done with e-signing - you cn't get here when it is e-signed, on to the report.php.
  if ($LOCKED) { we pop-up soemting to say the form is being edited elsewhere, do you want to take ownership?
  If you don't your changes will not be saved.  Need to allow view without incessant pop-ups.
  Maybe just once? }
  What if another user is editing it?  
  Consider flag the form_eye_mag table "locked" field with username/id and unflag when form is closed.
  Any attempt to save the form will meet with resistance, maybe a pop-up to say it is opened elsewhere, and an override option?
  and failure to alter DB on attempted submit_form if locked...  Once active user closes the form, 
  ie finalized for the active session, flag is removed.
  Need to think about this some.  Two windows anywhere with the same chart is not compatible with the autosave feature.
  Data integrity problems will arise.  Maybe the session variable is a good way incase the same user has it opened on a new computer?
  I assume a new window on a second computer will have a new session.  What is the idiot has two windows opened on the same machine?
  Maybe we need a random number generated each time the form is opened, which is removed from the "locked" field on closure.
  Any new windows meets with a pop-up and failure to save unless overridden.  If overridden, closing the other window will also
  meet with a popup and hopefully the numbnut won't say save this version.  I can imagine a doctor has the window opened and would like to see changes
  as they are entered by the tech?  Maybe there is a visible mode that does this without submitting, and auto refreshes every minute or so?
  Need to think this through.  Suggestions welcome...
*/
  
$warning = 'nodisplay';
$uniqueID = rand();
if (!$LOCKED||!$LOCKEDBY) {
    
    $LOCKEDBY= $uniqueID;
    $LOCKED='1';
  // echo "lockedBY =".$LOCKEDBY;exit;
  } else {
    //warning.  This form is locked by another user.
    $warning = ""; //remove nodisplay class
    $take_ownership = rand();
    $warning_text ='<br />You do not have ownership - Changes will not be saved!<br />
    <span id="take_ownership">Take Ownership</span>';
  }
$dated = new DateTime($encounter_data['encounter_date']);
if ($GLOBALS['date_display_format'] == 1)      
  {   
    $visit_date = $dated->format('m/d/Y');  // mm/dd/yyyy 
  } else {
    $visit_date = $dated->format('d/m/Y');  // dd/mm/yyyy
  }
if (!$form_id && !$encounter) { echo $encounter."-".$form_id." No encounter..."; exit;} 
//ideally this would point to an error databased by problem #, cause it'd be a problem.

if ($refresh and $refresh != 'fullscreen') {
  if ($refresh == "PMSFH")  { 
    display_PRIOR_section($refresh,$id,$id,$pid); 
  }
  if ($refresh == "PMSFH_panel") {
    
    show_PMSFH_panel($PMSFH);
  }
  exit;
}

//formHeader("Chart: ".$pat_data['fname']." ".$pat_data['lname']." ".$visit_date);
require_once("$incdir/patient_file/encounter/new_form.php");
?>
<html>
  <head>
    <title> Chart: <?php echo $pat_data['fname']." ".$pat_data['lname']." ".$visit_date; ?></title>
    <!-- jQuery library -->
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/bootstrap.min.js"></script>  
      <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
      <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
      <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
    <script language="JavaScript">    
    <?php  require_once("$srcdir/restoreSession.php"); 
    ?>
    function dopclick(id) {
      <?php if ($thisauth != 'write'): ?>
      dlgopen('../../patient_file/summary/a_issue.php?issue=0&thistype=' + id, '_blank', 550, 400);
      <?php else: ?>
      alert("<?php xl('You are not authorized to add/edit issues','e'); ?>");
      <?php endif; ?>
    }
    function doscript(type,id,encounter) {
      dlgopen('../../forms/eye_mag/SpectacleRx.php?target=' + type + '&id='+id+'&encounter='+ encounter, '_blank', 560, 590);
    }
     
    function dispensed(pid) {
      dlgopen('../../forms/eye_mag/SpectacleRx.php?dispensed=1&pid='+pid, '_blank', 560, 590);
    }
     
    </script>
      
      <!-- Add Font stuff for the look and feel.  -->
      <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
      <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/pure-min.css">
      <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/bootstrap-3-2-0.min.css">
      <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/bootstrap.min.js"></script>
      <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/bootstrap-responsive.min.css">
      <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css">    
      <link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
      <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/font-awesome-4.2.0/css/font-awesome.css">
      
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="openEMR: Eye Exam">
      <meta name="author" content="openEMR: ophthalmology">
      <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body class="bgcolor2" background="<?php echo $GLOBALS['backpic']?>" topmargin=0 rightmargin=0 leftmargin=0 bottommargin=0 marginwidth=0 marginheight=0>

  <?
    if ($display=="fullscreen") { 
      // trial fullscreen with mobile menu will lead to tablet versions and bootstrap menu 
      // this function is in php/eye_mag_functions.php
      echo '<div class="col-sm-12"><div class="row"> ';
  
      $input_echo = menu_overhaul_top($pid,$encounter);
      echo $input_echo;
      ?></div>
    </div>
      <div id="page-wrapper" style="margin: 0px 0px 0px 0px;">
      <div id="Layer2" name="Layer2" class="nodisplay">
      </div>
      <div id="Layer3" name="Layer3" class="container" style="text-align:center;">
            <!-- Page Heading -->
            <?php   
             echo    '<div class="col-sm-12"><div class="row">';
             menu_overhaul_left($pid,$encounter); 
             echo "</div></div>";
    } else {
      ?>
       <?php
    }
    ?>

    <div id="Layer1" name="Layer1" class="display">
     <!-- <div id="warning" name="warning" class="alert alert-warning <?php echo $warning; ?>">
                <span type="button" class="close" data-dismiss="alert">&times;</span>
                <strong>Warning!</strong> <?php echo $warning_text; ?>
            </div> -->
    <form method="post" action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/save.php?mode=update" id="eye_mag" class="eye_mag pure-form" name="eye_mag">
      <!-- start container for the main body of the form -->
      <div class="body_top text-center" id="form_container" name="form_container">
        <input type="hidden" name="menustate" id="menustate" value="start">
        <input type="hidden" name="form_id" id="form_id" value="<?php echo attr($form_id); ?>">
        <input type="hidden" name="pid" id="pid" value="<?php echo attr($pid); ?>">
        <input type="hidden" name="encounter" id="encounter" value="<?php echo attr($encounter); ?>">
        <input type="hidden" name="visit_date" id="visit_date" value="<?php echo $encounter_date; ?>">
        <input type="hidden" name="PREFS_VA" id="PREFS_VA" value="<?php echo attr($VA); ?>">
        <input type="hidden" name="PREFS_W" id="PREFS_W" value="<?php echo attr($W); ?>">
        <input type="hidden" name="PREFS_MR" id="PREFS_MR" value="<?php echo attr($MR); ?>">
        <input type="hidden" name="PREFS_CR" id="PREFS_CR" value="<?php echo attr($CR); ?>">
        <input type="hidden" name="PREFS_CTL" id="PREFS_CTL" value="<?php echo attr($CTL); ?>">
        <input type="hidden" name="PREFS_VAX" id="PREFS_VAX" value="<?php echo attr($VAX); ?>">
        <input type="hidden" name="PREFS_ADDITIONAL" id="PREFS_ADDITIONAL" value="<?php echo attr($ADDITIONAL); ?>">
        <input type="hidden" name="PREFS_CLINICAL" id="PREFS_CLINICAL" value="<?php echo attr($CLINICAL); ?>">
        <input type="hidden" name="PREFS_IOP" id="PREFS_IOP" value="<?php echo attr($IOP); ?>">
        <input type="hidden" name="PREFS_EXAM" id="PREFS_EXAM" value="<?php echo attr($EXAM); ?>">
        <input type="hidden" name="PREFS_CYL" id="PREFS_CYL" value="<?php echo attr($CYLINDER); ?>">
        <input type="hidden" name="PREFS_HPI_VIEW" id="PREFS_HPI_VIEW" value="<?php echo attr($HPI_VIEW); ?>">
        <input type="hidden" name="PREFS_EXT_VIEW" id="PREFS_EXT_VIEW" value="<?php echo attr($EXT_VIEW); ?>">
        <input type="hidden" name="PREFS_ANTSEG_VIEW" id="PREFS_ANTSEG_VIEW" value="<?php echo attr($ANTSEG_VIEW); ?>">
        <input type="hidden" name="PREFS_RETINA_VIEW" id="PREFS_RETINA_VIEW" value="<?php echo attr($RETINA_VIEW); ?>">
        <input type="hidden" name="PREFS_NEURO_VIEW" id="PREFS_NEURO_VIEW" value="<?php echo attr($NEURO_VIEW); ?>">
        <input type="hidden" name="PREFS_ACT_VIEW" id="PREFS_ACT_VIEW" value="<?php echo attr($ACT_VIEW); ?>">
        <input type="hidden" name="PREFS_PMH_RIGHT" id="PREFS_PMH_RIGHT" value="<?php echo attr($PMH_RIGHT); ?>">
        <input type="hidden" name="PREFS_HPI_RIGHT" id="PREFS_HPI_RIGHT" value="<?php echo attr($HPI_RIGHT); ?>">
        <input type="hidden" name="PREFS_EXT_RIGHT" id="PREFS_EXT_RIGHT" value="<?php echo attr($EXT_RIGHT); ?>">
        <input type="hidden" name="PREFS_ANTSEG_RIGHT" id="PREFS_ANTSEG_RIGHT" value="<?php echo attr($ANTSEG_RIGHT); ?>">
        <input type="hidden" name="PREFS_RETINA_RIGHT" id="PREFS_RETINA_RIGHT" value="<?php echo attr($RETINA_RIGHT); ?>">
        <input type="hidden" name="PREFS_NEURO_RIGHT" id="PREFS_NEURO_RIGHT" value="<?php echo attr($NEURO_RIGHT); ?>">
        <input type="hidden" name="PREFS_IMPPLAN_RIGHT" id="PREFS_IMPPLAN_RIGHT" value="<?php echo attr($IMPPLAN_RIGHT); ?>">
        <input type="hidden" name="PREFS_PANEL_RIGHT" id="PREFS_PANEL_RIGHT" value="<?php echo attr($PANEL_RIGHT); ?>">
        <input type="hidden" name="PREFS_KB" id="PREFS_KB" value="<?php echo attr($KB_VIEW); ?>">
        <input type="hidden" name="ownership" id="ownership" value="<?php echo attr($ownership); ?>">
        <input type="hidden" name="PREFS_ACT_SHOW"  id="PREFS_ACT_SHOW" value="<?php echo attr($ACT_SHOW); ?>">
        <input type="hidden" name="COPY_SECTION"  id="COPY_SECTION" value="">
        <input type="hidden" name="UNDO_ID"  id="UNDO_ID" value="<?php echo attr($UNDO_ID); ?>">
        <input type="hidden" name="LOCKEDBY" style="width:200px;" id="LOCKEDBY" value="<?php echo attr($LOCKEDBY); ?>">
        <input type="hidden" name="LOCKED"  id="LOCKED" value="<?php echo attr($LOCKED); ?>">
        <input type="hidden" name="uniqueID" style="width:200px;"   id="uniqueID" value="<?php echo attr($uniqueID); ?>">
        
        <input type="hidden" name="finalize"  id="finalize" value="0">
        <!-- start      accordion div -->

        <div id="accordion" name="accordion" class="text_clinical" style="position:relative;font-size: 14px;">
    
          <!-- start    HPI Zone -->
            <!-- start    HPI spinner -->
          <div class="loading" id="HPI_sections_loading" 
              name="HPI_sections_loading">
               <i class="fa fa-spinner fa-spin"></i>
          </div> 

            <!-- end      HPI spinner -->
            <?php ($CLINICAL =='1') ? ($display_Add = "size100") : ($display_Add = "size50"); ?>
            <?php ($CLINICAL =='0') ? ($display_Visibility = "display") : ($display_Visibility = "nodisplay"); ?>
            <!-- start    HPI row -->
          <div id="HPIPMH_sections" name="HPIPMH_sections" style="margin: 80 auto 10;width:100%;text-align: center;font-size:1.0em;" class="nodisplay" >   
            <!-- start    CC_HPI-->
            <div id="HPI_1" name="HPI_1" class="<?php echo attr($display_Add); ?>">
              <span class="anchor" id="HPI_anchor"></span>
              <!-- start  HPI Left -->
              <div id="HPI_left" name="HPI_left" class="exam_section_left borderShadow">
                <div id="HPI_left_text" style="height: 2.5in;text-align:left;" class="TEXT_class">
                  <span class="closeButton fa fa-paint-brush" id="BUTTON_DRAW_HPI" name="BUTTON_DRAW_HPI"></span>
                  <i class="closeButton_2 fa fa-database" id="BUTTON_QP_HPI" name="BUTTON_QP_HPI"></i>     
                  <b><?php echo xlt('HPI'); ?>:</b> <i class="fa fa-help"></i><br />
                    <div id="tabs_wrapper" >
                      <div id="tabs_container" style="z-index:3;">
                        <ul id="tabs">
                          <li id="tab1_CC" style="z-index:2;" class="active" ><a type="button" class="fa fa-check" href="#tab1"> <?php echo xlt('CC 1'); ?></a></li>
                          <li id="tab2_CC"><a <?php if ($CC2 >'') echo 'class="fa fa-check"'; ?> href="#tab2">CC 2</a></li>
                          <li id="tab3_CC"><a <?php if ($CC3 >'') echo 'class="fa fa-check"'; ?> href="#tab3">CC 3</a></li>
                        </ul>
                      </div>
                      <div id="tabs_content_container" style="z-index:1;" class="borderShadow">
                        <div id="tab1_CC_text" class="tab_content">
                          <table border="0" width="100%" cellspacing="0" cellpadding="0" 
                                style="font-size:1.1em;min-height: 2.0in;text-align:left;">
                            <tr>
                              <td class="" style="vertical-align:top;padding:10px;" colspan="2">
                                <b><span title="<?php echo xla('In the patient\'s words'); ?>"><?php echo xlt('Chief Complaint 1'); ?>:
                                </span>  </b>
                                <br />
                                <textarea name="CC1" id="CC1" class="HPI_text" tabindex="10"><?php echo text($CC1); ?></textarea>
                              </td>
                            </tr> 
                            <tr>
                              <td class="" style="vertical-align:top;padding:10px;">
                                <span title="<?php echo xla('History of Present Illness:  A detailed HPI may be completed by using either four or more HPI elements OR the status of three chronic or inactive problems.'); ?>" style="height:1in;font-weight:600;vertical-align:text-top;"><?php echo xlt('HPI'); ?>:
                                </span>
                                <br />
                                <textarea name="HPI1" id="HPI1" class="HPI_text" tabindex="21" style="min-height:1.4in;max-height:2.0in;width:2.1in;"><?php echo text($HPI1); ?></textarea>
                                <br />
                              </td>
                              <td style="vertical-align:top;padding:10px;"><span title="<?php echo xla('Chronic/Inactive Problems: document 3 and their status to reach the detailed HPI level'); ?>" style="height:1in;font-weight:600;vertical-align:text-top;"><?php echo xlt('Chronic Problems') ?>:</span>
                                <br />
                                <textarea name="CHRONIC1" id="CHRONIC1" class="HPI_text chronic_HPI" tabindex="22" style="min-height:0.4in;max-height:2.0in;width:1.8in;margin-bottom:9px;"><?php echo text($CHRONIC1); ?></textarea>
                                <br /><textarea name="CHRONIC2" id="CHRONIC2" class="HPI_text chronic_HPI" tabindex="23" style="min-height:0.4in;max-height:2.0in;width:1.8in;margin-bottom:9px;"><?php echo text($CHRONIC2); ?></textarea>
                                <br /><textarea name="CHRONIC3" id="CHRONIC3" class="HPI_text chronic_HPI" tabindex="24" style="min-height:0.4in;max-height:2.0in;width:1.8in;"><?php echo text($CHRONIC3); ?></textarea>
                                
                            </tr> 
                          </table> 
                           <center>
                            <i id="CODE_HIGH" name="CODE_HIGH" class="CODE_HIGH fa fa-check nodisplay" value="1"></i>
                            <span style="margin-top:4px;font-size:1.0em;text-align:center;width:85%;font-weight:800;color:#C0C0C0;">
                              <span class="detailed_HPI"><?php echo xlt('Detailed HPI') ?>:</span>
                              <span class="detail_4_elements"><?php echo xlt('> 3 HPI elements'); ?></span> OR 
                              <span class="chronic_3_elements"><?php echo xlt('the status of three chronic/inactive problems'); ?></span>
                            </span>
                          </center>
                        </div>
                             
                        <div id="tab2_CC_text" class="tab_content">
                            <table border="0" width="100%" cellspacing="0" cellpadding="0" 
                                style="font-size:1.1em;min-height: 2.0in;text-align:left;">
                              <tr>
                                <td class="" style="vertical-align:top;padding:10px;" colspan="2">
                                  <b><span title="<?php echo xla('In the patient\'s words'); ?>"><?php echo xlt('Chief Complaint 2'); ?>:
                                  </span>  </b>
                                  <br />
                                  <textarea name="CC2" id="CC2" class="HPI_text" tabindex="10"><?php echo text($CC2); ?></textarea>
                                </td>
                              </tr> 
                              <tr>
                                <td class="" style="vertical-align:top;padding:10px;">
                                  <span title="<?php echo xla('Detailed HPI: four or more HPI elements OR status of three chronic/inactive problems'); ?>" style="height:1in;font-weight:600;vertical-align:text-top;"><?php echo xlt('HPI'); ?>:
                                  </span>
                                  <br />
                                  <textarea name="HPI2" id="HPI2" class="HPI_text" tabindex="21" style="min-height:1.5in;max-height:2.0in;width:4.2in;"><?php echo text($HPI2); ?></textarea>
                                  <br />
                                </td>
                              </tr> 
                              </table>
                        </div>
                        <div id="tab3_CC_text" class="tab_content">
                            <table border="0" width="100%" cellspacing="0" cellpadding="0" 
                                style="font-size:1.1em;min-height: 2.0in;text-align:left;">
                              <tr>
                                <td class="" style="vertical-align:top;padding:10px;" colspan="2">
                                  <b><span title="<?php echo xla('In the patient\'s words'); ?>"><?php echo xlt('Chief Complaint 3'); ?>:
                                  </span>  </b>
                                  <br />
                                  <textarea name="CC3" id="CC3" class="HPI_text" tabindex="10"><?php echo text($CC3); ?></textarea>
                                </td>
                              </tr> 
                              <tr>
                                <td class="" style="vertical-align:top;padding:10px;">
                                  <span title="<?php echo xla('History of Present Illness:  A detailed HPI may be completed by using either four or more HPI elements OR the status of three chronic or inactive problems.'); ?>" style="height:1in;font-weight:600;vertical-align:text-top;"><?php echo xlt('HPI'); ?>:
                                  </span>
                                  <br />
                                  <textarea name="HPI3" id="HPI3" class="HPI_text" tabindex="21" style="min-height:1.5in;max-height:2.0in;width:4.2in;"><?php echo text($HPI3); ?></textarea>
                                  <br />
                                </td>
                              </tr> 
                            </table>
                        </div>
                      </div>
                    </div>
                   
                  <?php ($HPI_VIEW !=2) ? ($display_HPI_view = "wide_textarea") : ($display_HPI_view= "narrow_textarea");?>                                 
                  <?php ($display_HPI_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                </div>
              </div>
              <!-- end    HPI Left -->

              <!-- start  HPI Right -->
              <div id="HPI_right" name="HPI_right" class="exam_section_right borderShadow">
                <?php display_draw_section ("HPI",$encounter,$pid); ?>
                <!-- start    QP_HPI_Build -->
                <div id="QP_HPI" name="QP_HPI" class="QP_class" style="text-align:left;font-size:0.9em;">
                  <div id="HPI_text_list" name="HPI_text_list">
                    <span class="closeButton fa fa-close pull-right z100" id="BUTTON_TEXTD_HPI" name="BUTTON_TEXTD_HPI" value="1"></span>
                
                
                    <b>HPI Detail:</b> <br />
                    <div id="tabs_wrapper" >
                      <div id="tabs_container" style="margin-top:10px;">
                        <ul id="tabs">
                          <li id="tab1_HPI_tab" class="active" ><a type="button" <?php if ($CC1 >'') echo 'class="fa fa-check" '; ?> href="#tab1"> <?php echo xlt('HPI'); ?> 1</a></li>
                          <li id="tab2_HPI_tab" ><a <?php if ($CC2 >'') echo 'class="fa fa-check"'; ?> href="#tab2"><?php echo xlt('HPI'); ?> 2</a></li>
                          <li id="tab3_HPI_tab" ><a <?php if ($CC3 >'') echo 'class="fa fa-check"'; ?> href="#tab3"><?php echo xlt('HPI'); ?> 3</a></li>
                        </ul>
                      </div>
                      <div id="tabs_content_container" style="z-index:1;" class="borderShadow">
                        <div id="tab1_HPI_text" class="tab_content" style="min-height: 2.0in;text-align:left;">                 
                          <table style="font-size:1.1em;">
                            <tr>
                              <td class="right"><b><?php echo xlt('Timing'); ?>:</b></td>
                              <td>
                                <textarea name="TIMING1" id="TIMING1" class="count_HPI" tabindex="30" style="width:250px;"><?php echo text($TIMING1); ?></textarea>
                              </td>
                            </td><td><i><?php echo xlt('When and how often?'); ?></i><br /></td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Context'); ?>:</b></td>
                              <td>
                                <textarea name="CONTEXT1" id="CONTEXT1" class="count_HPI" tabindex="31"  style="width:250px;"><?php echo text($CONTEXT1); ?></textarea>
                                  <br />
                              </td>
                              <td>
                                <i><?php echo xlt('Does it occur in certain situations?'); ?></i><br />
                              </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Severity'); ?>:</b></td>
                              <td>
                                <textarea name="SEVERITY1" id="SEVERITY1" class="count_HPI" tabindex="32" style="width:250px;"><?php echo text($SEVERITY1); ?></textarea>
                                </td>
                                <td><i><?php echo xlt('How bad is it? 0-10, mild, moderate, severe?'); ?></i>
                                </td>
                            </tr>
                            <tr>
                              <td  class="right"><b><?php echo xlt('Modifying'); ?>:</b></td>
                              <td>
                                <textarea name="MODIFY1" id="MODIFY1" class="count_HPI" tabindex="33"  style="width:250px;"><?php echo text($MODIFY1); ?></textarea>
                                  </td>
                                  <td><i ><?php echo xlt('Does anything makes it better? Worse?'); ?></i>
                                  </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Associated'); ?>:</b></td>
                              <td>
                                <textarea name="ASSOCIATED1" id="ASSOCIATED1" class="count_HPI" tabindex="34" style="width:250px;"><?php echo text($ASSOCIATED1); ?></textarea>
                                </td>
                                <td><i><?php echo xlt('Anything else happen at the same time?'); ?></i>
                                </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Location'); ?>:</b></td>
                              <td>
                                <textarea name="LOCATION1" id="LOCATION1" class="count_HPI" tabindex="35" style="width:250px;"><?php echo text($LOCATION1); ?></textarea>                        
                              </td>
                              <td><i><?php echo xlt('Where on your body does it occur?'); ?></i>
                              </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Quality'); ?>:</b></td>
                              <td>
                                <textarea name="QUALITY1" id="QUALITY1" class="count_HPI" tabindex="36" style="width:250px;"><?php echo text($QUALITY1); ?></textarea>
                                    
                            </td><td>
                            <i><?php echo xlt('eg. aching, burning, radiating pain'); ?></i>
                            </td>
                            </tr> 
                            <tr>
                              <td class="right"><b><?php echo xlt('Duration'); ?>:</b></td>
                              <td><textarea name="DURATION1" id="DURATION1" class="count_HPI" tabindex="37" style="width:250px;"><?php echo text($DURATION1); ?></textarea>
                              </td>
                              <td>
                                <i><?php echo xlt('How long does it last?'); ?></i>
                              </td>
                            </tr>
                          </table>
                         
                        </div>
                        <div id="tab2_HPI_text" class="tab_content" style="min-height: 2.0in;text-align:left;">                 
                          <table style="font-size:1.1em;">
                            <tr>
                              <td class="right"><b><?php echo xlt('Timing'); ?>:</b></td>
                              <td>
                                <textarea name="TIMING2" id="TIMING2" tabindex="30" style="width:250px;"><?php echo text($TIMING2); ?></textarea>
                              </td>
                            </td><td><i><?php echo xlt('When and how often?'); ?></i><br /></td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Context'); ?>:</b></td>
                              <td>
                                <textarea name="CONTEXT2" id="CONTEXT2" tabindex="31"  style="width:250px;"><?php echo text($CONTEXT2); ?></textarea>
                                  <br />
                              </td>
                              <td>
                                <i><?php echo xlt('Does it occur in certain situations?'); ?></i><br />
                              </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Severity'); ?>:</b></td>
                              <td>
                                <textarea name="SEVERITY2" id="SEVERITY2" tabindex="32" style="width:250px;"><?php echo text($SEVERITY2); ?></textarea>
                                </td>
                                <td><i><?php echo xlt('How bad is it? 0-10, mild, moderate, severe?'); ?></i>
                                </td>
                            </tr>
                            <tr>
                              <td  class="right"><b><?php echo xlt('Modifying'); ?>:</b></td>
                              <td>
                                <textarea name="MODIFY2" id="MODIFY2" tabindex="33"  style="width:250px;"><?php echo text($MODIFY2); ?></textarea>
                                  </td>
                                  <td><i ><?php echo xlt('Does anything makes it better? Worse?'); ?></i>
                                  </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Associated'); ?>:</b></td>
                              <td>
                                <textarea name="ASSOCIATED2" id="ASSOCIATED2" tabindex="34" style="width:250px;"><?php echo text($ASSOCIATED2); ?></textarea>
                                </td>
                                <td><i><?php echo xlt('Anything else happen at the same time?'); ?></i>
                                </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Location'); ?>:</b></td>
                              <td>
                                <textarea name="LOCATION2" id="LOCATION2" tabindex="35" style="width:250px;"><?php echo text($LOCATION2); ?></textarea>                        
                              </td>
                              <td><i><?php echo xlt('Where on your body does it occur?'); ?></i>
                              </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Quality'); ?>:</b></td>
                              <td>
                                <textarea name="QUALITY2" id="QUALITY2" tabindex="36" style="width:250px;"><?php echo text($QUALITY2); ?></textarea>
                                    
                              </td><td>
                              <i><?php echo xlt('eg. aching, burning, radiating pain'); ?></i>
                              </td>
                            </tr> 
                            <tr>
                              <td class="right"><b><?php echo xlt('Duration'); ?>:</b></td>
                              <td><textarea name="DURATION2" id="DURATION2" tabindex="37" style="width:250px;"><?php echo text($DURATION2); ?></textarea>
                              </td>
                              <td>
                                <i><?php echo xlt('How long does it last?'); ?></i>
                              </td>
                            </tr>
                          </table>
                          <center>
                            <span style="font-size:1.0em;text-align:center;width:85%;font-weight:800;color:#C0C0C0;"><?php echo xlt('Detailed HPI:
                              four or more HPI elements OR the status of three chronic/inactive problems'); ?></span>
                          </center>
                        </div>
                        <div id="tab3_HPI_text" class="tab_content" style="min-height: 2.0in;text-align:left;">                 
                          <table style="font-size:1.1em;">
                            <tr>
                              <td class="right"><b><?php echo xlt('Timing'); ?>:</b></td>
                              <td>
                                <textarea name="TIMING3" id="TIMING3" tabindex="30" style="width:250px;"><?php echo text($TIMING3); ?></textarea>
                              </td>
                            </td><td><i><?php echo xlt('When and how often?'); ?></i><br /></td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Context'); ?>:</b></td>
                              <td>
                                <textarea name="CONTEXT3" id="CONTEXT3" tabindex="31"  style="width:250px;"><?php echo text($CONTEXT3); ?></textarea>
                                  <br />
                              </td>
                              <td>
                                <i><?php echo xlt('Does it occur in certain situations?'); ?></i><br />
                              </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Severity'); ?>:</b></td>
                              <td>
                                <textarea name="SEVERITY3" id="SEVERITY3" tabindex="32" style="width:250px;"><?php echo text($SEVERITY3); ?></textarea>
                                </td>
                                <td><i><?php echo xlt('How bad is it? 0-10, mild, moderate, severe?'); ?></i>
                                </td>
                            </tr>
                            <tr>
                              <td  class="right"><b><?php echo xlt('Modifying'); ?>:</b></td>
                              <td>
                                <textarea name="MODIFY3" id="MODIFY3" tabindex="33"  style="width:250px;"><?php echo text($MODIFY3); ?></textarea>
                                  </td>
                                  <td><i ><?php echo xlt('Does anything makes it better? Worse?'); ?></i>
                                  </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Associated'); ?>:</b></td>
                              <td>
                                <textarea name="ASSOCIATED3" id="ASSOCIATED3" tabindex="34" style="width:250px;"><?php echo text($ASSOCIATED3); ?></textarea>
                                </td>
                                <td><i><?php echo xlt('Anything else happen at the same time?'); ?></i>
                                </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Location'); ?>:</b></td>
                              <td>
                                <textarea name="LOCATION3" id="LOCATION3" tabindex="35" style="width:250px;"><?php echo text($LOCATION3); ?></textarea>                        
                              </td>
                              <td><i><?php echo xlt('Where on your body does it occur?'); ?></i>
                              </td>
                            </tr>
                            <tr>
                              <td class="right"><b><?php echo xlt('Quality'); ?>:</b></td>
                              <td>
                                <textarea name="QUALITY3" id="QUALITY3" tabindex="36" style="width:250px;"><?php echo text($QUALITY3); ?></textarea>
                                    
                            </td><td>
                            <i><?php echo xlt('eg. aching, burning, radiating pain'); ?></i>
                          </td>
                            </tr> 
                            <tr>
                              <td class="right"><b><?php echo xlt('Duration'); ?>:</b></td>
                              <td><textarea name="DURATION3" id="DURATION3" tabindex="37" style="width:250px;"><?php echo text($DURATION3); ?></textarea>
                              </td>
                              <td>
                                <i><?php echo xlt('How long does it last?'); ?></i>
                              </td>
                            </tr>
                          </table>
                          <center>
                            <span style="font-size:1.0em;text-align:center;width:85%;font-weight:800;color:#C0C0C0;"><?php echo xlt('Detailed HPI:
                              four or more HPI elements OR the status of three chronic/inactive problems'); ?></span>
                          </center>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>  
                <!-- end      QP_HPI -->
              </div>
              <!-- end HPI Right -->
            </div>
          
            <!-- end      HPI -->


            <!-- start    PMH row -->
            <!-- start    PMH_1 -->
           
            <div id="PMH_1" name="PMH_1" class="<?php echo attr($display_Add); ?> clear_both jquery_anchor">
              <span class="anchor" id="PMH_anchor"></span>
              <!-- start  HPI Left -->
              <div id="PMH_left" name="PMH_left" class="exam_section_left borderShadow">

                <div id="PMH_left_text" style="height: 2.5in;text-align:left;padding-top: 15px;" class="TEXT_class">
                  <span class="closeButton_2 fa fa-paint-brush" id="BUTTON_DRAW_PMH" name="BUTTON_DRAW_PMH"></span>
                  <i class="closeButton_3 fa fa-database" id="BUTTON_QP_PMH" name="BUTTON_QP_PMH"></i>
                  <a class="closeButton fa fa-indent" id="right-panel-link" name="right-panel-link" href="#right-panel"></a>

                  <?php ($PMH_VIEW !=2) ? ($display_PMH_view = "wide_textarea") : ($display_PMH_view= "narrow_textarea");?>                                 
                  <?php ($display_PMH_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                  <div id="PMSFH_sections" name="PMSFH_sections">
                    <div id="Enter_PMH" name="Enter_PMH" class="PMH_class" style="text-align:left;vertical align:middle; min-height: 2.3in;overflow:auto;font-size:0.7em;">
                      ../../forms/'.$form_folder.'/a_issue.php

                        <?php 
                       
                       // display_PMH_selector();

                         ?>
                      


                    </div><?php //display_PRIOR_section("PMSFH",$id,$id,$pid); ?>
                     
                  </div>
                  
                </div>

              </div>
              <!-- end    PMH Left -->
              <!-- start  PMH Right -->
              <div id="PMH_right" name="PMH_right" class="exam_section_right borderShadow">
                <a class="nodisplay left_PMSFH_tab" id="right-panel-link" href="#right-panel">
                  <img src="/openemr/interface/forms/eye_mag/images/PMSFHx.png">
                </a>
                <?php display_draw_section("PMH",$encounter,$pid); ?>
                <div id="QP_PMH" name="QP_PMH" class="QP_class" style="text-align:left;">
                  <?php display_PRIOR_section("PMSFH",$id,$id,$pid); ?>

                </div>
                <!--<div class="QP_block borderShadow " style="text-align:center;min-height: 300px;padding:4 10 4 10;font-weight:600;">
                          <span onclick="$('.kb').toggleClass('nodisplay');" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke">Keyboard Entry</span>&nbsp;<a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=ext');">
                          <i title="<?php echo xla('Click for External shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                          <textarea id="PMSFH_keyboard" name="PMSFH_keyboard" style="color:#0C0C0C;size:0.8em;height: 2.0in;width:1.5in;" tabindex='1000'>Not functioning yet</textarea>
                          <span style="font-size:0.9em;font-weight:400;color:#0C0C0C;">Type: location.text; ENTER<br />
                          eg. right lower lid stye laterally:<br /> <b>RLL.stye lat;</b>
                          </span>
                  </div> -->
              </div>
              <!-- end PMH Right -->
            </div>  
           
          </div>
            <!-- end      HPI -->

            <!-- if this is in a frame, allow us to go fullscreen.  Need to hide this if fullscreen though -->  
          <?php 
          if ($display != "fullscreen") {   ?>      
          
                  <i onclick="top.restoreSession();openNewForm('<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/encounter/load_form.php?formname=fee_sheet');dopopup('<?php echo $_SERVER['REQUEST_URI']. '&display=fullscreen&encounter='.$encounter; ?>');" 
                    class="fa fa-plus-square-o top_right" style="top: -0.2in;right: 0.2in;z-index:10000001;"></i>
                     <?php 
          }  else { ?>
           <!--<i class="fa fa-close top_right" OnClick="window.close()"></i>-->
            <?php 
          } 
          ?>
          <div id="clinical_anchor" name="clinical_anchor"></div>
          <br />
          
            <!-- start of the clinical BOX -->
          <div class="loading" id="LayerTechnical_sections_loading" 
                name="LayerTechnical_sections_loading">
                 <i class="fa fa-spinner fa-spin"></i>
          </div> 
          <div id="LayerTechnical_sections" name="LayerTechnical_sections" class="section nodisplay" style="vertical-align:text-top;text-align:center;">
            <div id="LayerAlert" class="vitals" style="width: 1.2in; min-height: 1.05in;padding: 0.02in;padding-top:25px; border: 1.00pt solid #000000;text-align:left;padding-left:10px;">
              <div id="Lyr2.9" class="top_left ">
                <th class="text_clinical"><b id="MS_tab"><?php echo xlt('Mental Status'); ?>:</b></th>
              </div>    
                <input type="checkbox" name="alert" id="alert" <?php if ($alert) echo "checked='checked'"; ?> value="yes"> Alert<br />
                <input type="checkbox" name="oriented" id="oriented" <?php if ($oriented) echo "checked='checked'"; ?> value="yes"> Oriented TPP<br />
                <input type="checkbox" name="confused" id="confused" <?php if ($confused) echo "checked='checked'"; ?> value="nml"> Mood/Affect Nml
            </div>

                  <!-- start of the VISION BOX -->                  
                  <div id="LayerVision" class="vitals" style="width: 2.0in; min-height: 1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                      <div id="Lyr3.0" class="top_left ">
                              <th class="text_clinical"><b id="vision_tab"><?php echo xlt('Vision'); ?>:</b></th>
                      </div>
                       <?php 
                                              //if the prefs show a field, ie visible, the highlight the zone.
                       if ($W == '1') $button_W = "buttonRefraction_selected";
                       if ($MR == '1') $button_MR = "buttonRefraction_selected";
                       if ($CR == '1') $button_AR = "buttonRefraction_selected";
                       if ($CTL == '1') $button_CTL = "buttonRefraction_selected";
                       if ($ADDITIONAL == '1') $button_ADDITIONAL = "buttonRefraction_selected";
                       if ($VAX == '1') $button_VAX = "buttonRefraction_selected";
                       ?>
                       <div class="top_right">
                          <span id="tabs">  
                              <ul>
                                  <li id="LayerVision_W_lightswitch" class="<?php echo attr($button_W); ?>" value="Current">W</li> | 
                                  <li  id="LayerVision_MR_lightswitch" class="<?php echo attr($button_MR); ?>" value="Auto">MR</li> | 
                                  <li  id="LayerVision_CR_lightswitch" class="<?php echo attr($button_AR); ?>" value="Cyclo">AR</li> | 
                                  <li  id="LayerVision_CTL_lightswitch" class="<?php echo attr($button_CTL); ?>" value="Contact Lens">CTL</li> | 
                                  <li  id="LayerVision_ADDITIONAL_lightswitch" class="<?php echo attr($button_ADDITIONAL); ?>" value="Additional"><?php echo xlt('Add.'); ?></li> | 
                                  <li  id="LayerVision_VAX_lightswitch" class="<?php echo attr($button_VAX); ?>" value="Visual Acuities">Va</li>                           
                              </ul>
                          </span>
                      </div>    

                      <div id="Lyr3.1" style="position: absolute; top: 0.23in; left: 0.09in;width: 0.4in;height: 0.3in; border: none; padding: 0in; " dir="LTR">
                          <font style="font-face:'San Serif'; font-size:3.5em;">V</font>
                          <font style="font-face:arial; font-size:0.9em;"></font>

                      </div>
                      <div id="Visions_A" name="Visions_A" class="" style="position: absolute; top: 0.35in; text-align:right;right:0.1in; height: 0.72in;  padding: 0in;" >
                          <b>OD </b>
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="40" Xsize="6" id="SCODVA" name="SCODVA" value="<?php echo attr($SCODVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="42" Xsize="6"  id="WODVA_copy" name="WODVA_copy" value="<?php echo attr($WODVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="44" Xsize="6"  id="PHODVA_copy" name="PHODVA_copy" value="<?php echo attr($PHODVA); ?>">
                          <br />                            
                          <b>OS </b>
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="41" size="8"  id="SCOSVA" name="SCOSVA" value="<?php echo attr($SCOSVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="43" size="8" id="WOSVA_copy" name="WOSVA_copy" value="<?php echo attr($WOSVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="45" size="8" id="PHOSVA_copy" name="PHOSVA_copy" value="<?php echo attr($PHOSVA); ?>">
                          <br />
                          <span id="more_visions_1" name="more_visions_1" style="position: absolute;top:0.44in;left:-0.37in;font-size: 0.9em;padding-right:4px;"><b><?php echo xlt('Acuity'); ?></b> </span>
                          <span style="position: absolute;top:0.41in;left:0.33in;font-size: 0.8em;"><b><?php echo xlt('SC'); ?></b></span>
                          <span style="position: absolute;top:0.41in;left:0.68in;font-size: 0.8em;"><b><?php echo xlt('CC'); ?></b></span>
                          <span style="position: absolute;top:0.41in;left:1.10in;font-size: 0.8em;"><b><?php echo xlt('PH'); ?></b></span><br /><br /><br />
                      </div>
                      <div id="Visions_B" name="Visions_B" class="nodisplay" style="position: absolute; top: 0.35in; text-align:right;right:0.1in; height: 0.72in;  padding: 0in;" >
                          <b><?php echo xlt('OD'); ?> </b>
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="46" Xsize="6" id="ARODVA_copy" name="ARODVA_copy" value="<?php echo attr($ARODVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="48" Xsize="6" id="MRODVA_copy" name="MRODVA_copy" value="<?php echo attr($MRODVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="50" Xsize="6" id="CRODVA_copy" name="CRODVA_copy" value="<?php echo attr($CRODVA); ?>">
                          <br />                            
                          <b><?php echo xlt('OS'); ?> </b>
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="47" Xsize="6" id="AROSVA_copy" name="AROSVA_copy" value="<?php echo attr($AROSVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="49" Xsize="6" id="MROSVA_copy" name="MROSVA_copy" value="<?php echo attr($MROSVA); ?>">
                          <input type="TEXT" style="left: 0.5in; width: 0.35in; height: 0.19in; " tabindex="51" Xsize="6" id="CROSVA_copy" name="CROSVA_copy" value="<?php echo attr($CROSVA); ?>">
                          <br />
                          <span id="more_visions_2" name="more_visions_2" style="position: absolute;top:0.44in;left:-0.37in;font-size: 0.9em;padding-right:4px;"><b><?php echo xlt('Acuity'); ?></b> </span>
                          <span style="position: absolute;top:0.41in;left:0.33in;font-size: 0.8em;"><b><?php echo xlt('AR'); ?></b></span>
                          <span style="position: absolute;top:0.41in;left:0.68in;font-size: 0.8em;"><b><?php echo xlt('MR'); ?></b></span>
                          <span style="position: absolute;top:0.41in;left:1.10in;font-size: 0.8em;"><b><?php echo xlt('CR'); ?></b></span>
                      </div>       
                  </div>
                  <!-- end of the VISION BOX -->

                  <!-- START OF THE PRESSURE BOX -->
                  <div id="LayerTension" class="vitals" style="width: 1.5in; height: 1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                      
                      <span title="This will display a graph of IOPs\n over time in a pop-up window" id="LayerVision_IOP_lightswitch" name="LayerVision_IOP_lightswitch" class="closeButton fa  fa-line-chart" id="IOP_Graph" name="IOP_Graph"></span>
                      <!-- -->
                      <div id="Lyr4.0" style="position:absolute; left:0.05in; width: 1.4in; top:0.0in; padding: 0in; ">
                          <span class="top_left">
                              <b id="tension_tab"><?php echo xlt('Tension'); ?>:</b> 
                              <div style="position:absolute;background-color:#ffffff;text-align:left;width:50px; top:0.7in;font-size:0.9em;left:0.02in;">
                                  <?php 
                                  if ($IOPTIME == '') {
                                      $IOPTIME =  date('g:i a'); 
                                  }
                                  ?>
                                  <input type="text" name="IOPTIME" id="IOPTIME" tabindex="-1" style="background-color:#ffffff;font-size:0.7em;border:none;" value="<?php echo attr($IOPTIME); ?>">

                              </div>    
                          </span>
                      </div>
                      <div id="Lyr4.1" style="position: absolute; top: 0.23in; left: 0.09in; width: 0.37in;height: 0.45in; border: none; padding: 0in;">
                          <font style="font-face:arial; font-size:3.5em;">T</font>
                          <font style="font-face:arial; font-size: 0.9em;"></font>
                      </div>
                      <div id="Lyr4.2" style="position: absolute; top: 0.35in; text-align:right;right:0.1in; height: 0.72in;  padding: 0in; border: 1pt black;">
                          <b><?php echo xlt('OD'); ?></b>
                          <input type="text" style="left: 0.5in; width: 0.23in; height: 0.18in; " tabindex="52" name="ODIOPAP" value="<?php echo attr($ODIOPAP); ?>">
                          <input type="text" style="left: 0.5in; width: 0.23in; height: 0.18in; " tabindex="54" name="ODIOPTPN" value="<?php echo attr($ODIOPTPN); ?>">
                          <input type="text" style="left: 0.5in; width: 0.23in; height: 0.18in; " name="ODIOPFTN" value="<?php echo attr($ODIOPTPN); ?>">
                          <br />
                          <b><?php echo xlt('OS'); ?> </b>
                          <input type="text" style="left: 0.5in; width: 0.23in; height: 0.18in; " tabindex="53" name="OSIOPAP" value="<?php echo attr($OSIOPAP); ?>">
                          <input type="text" style="left: 0.5in; width: 0.23in; height: 0.18in; " tabindex="55" name="OSIOPTPN" value="<?php echo attr($OSIOPTPN); ?>">
                          <input type="text" style="left: 0.5in; width: 0.23in; height: 0.18in; " name="OSIOPFTN" value="<?php echo attr($OSIOPFTN); ?>">
                          <br /><br />
                          <span style="position: absolute;top:0.44in;left:0.22in;font-size: 0.8em;"><b><?php echo xlt('AP'); ?></b></span>
                          <span style="position: absolute;top:0.44in;left:0.47in;font-size: 0.8em;"><b><?php echo xlt('TP'); ?></b></span>
                          <span style="position: absolute;top:0.44in;left:0.7in;font-size: 0.8em;"><b><?php echo xlt('FT'); ?></b></span>
                      </div>
                  </div>
                  <!-- END OF THE PRESSURE BOX -->

                  <!-- start of the Amsler box -->
                  <div id="LayerAmsler" class="vitals" style="width: 1.5in; height: 1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                      <div id="Lyr5.0" style="position:absolute;  left:0.05in; width: 1.4in; top:0in; padding: 0in;">
                          <span class="top_left">
                              <b><?php echo xlt('Amsler'); ?>:</b>
                          </span>
                      </div>
                      <?php 
                          if (!$AMSLEROD) $AMSLEROD= "0";
                          if (!$AMSLEROS) $AMSLEROS= "0";
                          if ($AMSLEROD || $AMSLEROS) {
                              $checked = 'value="0"'; 
                          } else {
                              $checked = 'value="1" checked';
                          }
                          
                      ?>
                      <input type="hidden" id="AMSLEROD" name="AMSLEROD" value='<?php echo attr($AMSLEROD); ?>'>
                      <input type="hidden" id="AMSLEROS" name="AMSLEROS" value='<?php echo attr($AMSLEROS); ?>'>
                      
                      <div style="position:absolute;text-align:right; top:0.03in;font-size:0.8em;right:0.1in;">
                          <label for="Amsler-Normal" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                          <input id="Amsler-Normal" type="checkbox" <?php echo attr($checked); ?> tabindex="56">
                      </div>     
                      <div id="Lyr5.1" style="position: absolute; top: 0.2in; left: 0.12in; display:inline-block;border: none; padding: 0.0in;">
                          <table cellpadding=0 cellspacing=0 style="padding:0px;margin:auto;width:90%;align:auto;font-size:0.8em;text-align:center;">
                              <tr>
                                  <td colspan=3 style="text-align:center;"><b><?php echo xlt('OD'); ?></b>
                                  </td>
                                  <td></td>
                                  <td colspan=3 style="text-align:center;"><b><?php echo xlt('OS'); ?></b>
                                  </td>
                              </tr>

                              <tr>
                                  <td colspan=3>
                                      <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROD); ?>.jpg" id="AmslerOD" style="margin:0.05in;height:0.45in;width:0.5in;" /></td>
                                  <td></td>
                                  <td colspan=3>
                                      <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROS); ?>.jpg" id="AmslerOS" style="margin:0.05in;height:0.45in;width:0.5in;" /></td>
                                  </tr>
                                  <tr>
                                       <td colspan=3 style="text-align:center;">
                                          <div class="AmslerValueOD" style="font-size:0.8em;text-decoration:italics;">
                                              <span id="AmslerODvalue"><?php echo text($AMSLEROD); ?></span>/5
                                          </div>
                                      </td>
                                      <td></td>
                                      <td colspan=3 style="text-align:center;">
                                          <div class="AmslerValueOS" style="font-size:0.8em;text-decoration:italics;">
                                              <span id="AmslerOSvalue"><?php echo text($AMSLEROS); ?></span>/5
                                          </div>
                                  </td>
                              </tr>
                          </table>
                      </div>
                  </div>
                  <!-- end of the Amsler box -->

                  <!-- start of the Fields box -->
                  <div id="LayerFields" class="vitals" style="width: 1.42in;height:1.05in;padding: 0.02in; border: 1.00pt solid #000000;">
                      <div  id="Lyr6.0" style="position:absolute;  left:0.05in; width: 1.4in; top:0.0in; padding: 2px; " dir="LTR">
                          <span class="top_left">
                              <b id="fields"><?php echo xlt('Fields'); ?>:</b>
                                     
                          </span>
                      </div> 
                          <?php 
                              // if the VF zone is checked, display it
                              // if ODVF1 = 1 (true boolean) the value="0" checked="true"
                              $bad='';
                              for ($z=1; $z <5; $z++) {
                                  $ODzone = "ODVF".$z;
                                  if ($$ODzone =='1') {
                                      $ODVF[$z] = 'checked value="true"';
                                      $bad++;
                                  } else {
                                      $ODVF[$z] = 'value="false"';
                                  }
                                  $OSzone = "OSVF".$z;
                                  if ($$OSzone =="1") {
                                      $OSVF[$z] = 'checked value="1"';
                                      $bad++;
                                  } else {
                                      $OSVF[$z] = 'value="0"';
                                  }
                              }
                              if (!$bad)  $VFFTCF = "checked";
                          ?>
                      <div style="position:relative;text-align:right; top:0.03in;font-size:0.8em;right:0.1in;">
                                  <label for="FieldsNormal" class="input-helper input-helper--checkbox"><?php echo xlt('FTCF'); ?></label>
                                  <input id="FieldsNormal" type="checkbox" value="1" <?php echo attr($VFFTCF); ?>>
                      </div>   
                      <div id="Lyr5.1" style="position: relative; top: 0.08in; left: 0.0in; border: none; background: white">
                          <table cellpadding='1' cellspacing="1" style="font-size: 0.8em;margin:auto;"> 
                              <tr>    
                                  <td style="width:0.6in;text-align:center;" colspan="2"><b><?php echo xlt('OD'); ?></b><br /></td>

                                  <td style="width:0.05in;"> </td>
                                  <td style="width:0.6in;text-align:center;" colspan="2"><b><?php echo xlt('OS'); ?></b></td>
                              </tr> 
                              <tr>    
                                  <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                      <input name="ODVF1" id="ODVF1" type="checkbox" <?php echo attr($ODVF['1'])?> class="hidden"> 
                                      <label for="ODVF1" class="input-helper input-helper--checkbox boxed"></label>
                                  </td>
                                  <td style="border-left:1pt solid black;border-bottom:1pt solid black;">
                                      <input name="ODVF2" id="ODVF2" type="checkbox" <?php echo attr($ODVF['2'])?> class="hidden"> 
                                      <label for="ODVF2" class="input-helper input-helper--checkbox boxed"></label>
                                  </td>
                                  <td></td>
                                  <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                      <input name="OSVF1" id="OSVF1" type="checkbox" <?php echo attr($OSVF['1']); ?> class="hidden" >
                                      <label for="OSVF1" class="input-helper input-helper--checkbox boxed"></label>
                                  </td>
                                  <td style="border-left:1pt solid black;border-bottom:1pt solid black;">
                                      <input name="OSVF2" id="OSVF2" type="checkbox" <?php echo attr($OSVF['2']); ?> class="hidden">                                                         
                                      <label for="OSVF2" class="input-helper input-helper--checkbox boxed"> </label>
                                  </td>
                              </tr>       
                              <tr>    
                                  <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                      <input name="ODVF3" id="ODVF3" type="checkbox"  class="hidden" <?php echo attr($ODVF['3']); ?>> 
                                      <label for="ODVF3" class="input-helper input-helper--checkbox boxed"></label>
                                  </td>
                                  <td style="border-left:1pt solid black;border-top:1pt solid black;">
                                      <input  name="ODVF4" id="ODVF4" type="checkbox"  class="hidden" <?php echo attr($ODVF['4']); ?>>
                                      <label for="ODVF4" class="input-helper input-helper--checkbox boxed"></label>  
                                  </td>
                                  <td></td>
                                  <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                      <input name="OSVF3" id="OSVF3" type="checkbox"  class="hidden" <?php echo attr($OSVF['3']); ?>>
                                      <label for="OSVF3" class="input-helper input-helper--checkbox boxed"></label>
                                  </td>
                                  <td style="border-left:1pt solid black;border-top:1pt solid black;">
                                      <input name="OSVF4" id="OSVF4" type="checkbox"  class="hidden" <?php echo attr($OSVF['4']); ?>>
                                      <label for="OSVF4" class="input-helper input-helper--checkbox boxed"></label>
                                  </td>                    
                              </tr>
                          </table>
                      </div>
                  </div>
                  <!-- end of the Fields box -->

                  <!-- start of the Pupils box -->
                  <div id="LayerPupils" class="vitals" style="width: 1.85in; height: 1.05in; padding: 0.02in; border: 1.00pt solid #000000; ">  
                      <span class="top_left"><b id="pupils"><?php echo xlt('Pupils'); ?>:</b> </span>
                      <div style="position:absolute;text-align:right; top:0.03in;font-size:0.8em;right:0.1in;">
                                  <label for="PUPIL_NORMAL" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                                  <input id="PUPIL_NORMAL" name="PUPIL_NORMAL" type="checkbox" value="1" <?php if ($PUPIL_NNORMAL =='1') echo 'checked="checked"'; ?>>
                      </div>
                      <div id="Lyr7.0" style="position: absolute; top: 0.3in; left: 0.15in; border: none;">
                          <table cellpadding=2 cellspacing=1 style="font-size: 0.9em;;"> 
                              <tr>    
                                  <th style="width:0.2in;"> &nbsp;
                                  </th>
                                  <th style="width:0.7in;padding: 0.1;"><?php echo xlt('size'); ?> (<?php echo xlt('mm'); ?>)
                                  </th>
                                  <th style="width:0.2in;padding: 0.1;"><?php echo xlt('react'); ?> 
                                  </th>
                                  <th style="width:0.2in;padding: 0.1;"><?php echo xlt('APD'); ?>
                                  </th>
                              </tr>
                              <tr>    
                                  <td><b><?php echo xlt('OD'); ?></b>
                                  </td>
                                  <td style="border-right:1pt solid black;border-bottom:1pt solid black;">
                                      <input type="text" id ="ODPUPILSIZE1" name="ODPUPILSIZE1" style="width:0.25in;height:0.2in;" value="<?php echo attr($ODPUPILSIZE1); ?>">
                                      <font>&#8594;</font>
                                      <input type="text" id ="ODPUPILSIZE2" size="1" name="ODPUPILSIZE2" style="width:0.25in;height:0.2in;" value="<?php echo attr($ODPUPILSIZE2); ?>">
                                  </td>
                                  <td style="border-left:1pt solid black;border-right:1pt solid black;border-bottom:1pt solid black;">
                                      <input type="text" style="margin: 0 2 0 2;width:0.3in;height:0.2in;" name='ODPUPILREACTIVITY' id='ODPUPILREACTIVITY' value='<?php echo attr($ODPUPILREACTIVITY); ?>'>
                                  </td>
                                  <td style="border-bottom:1pt solid black;">
                                      <input type="text" style="margin: 0 2 0 2;width:0.20in;height:0.2in;" name="ODAPD" id='ODAPD' value='<?php echo attr($ODAPD); ?>'>
                                  </td>
                              </tr>
                              <tr>    
                                  <td><b><?php echo xlt('OS'); ?></b>
                                  </td>
                                  <td style="border-right:1pt solid black;border-top:1pt solid black;">
                                      <input type="text" size="1" name="OSPUPILSIZE1" id="OSPUPILSIZE1" style="width:0.25in;height:0.2in;" value="<?php echo attr($OSPUPILSIZE1); ?>">
                                      <font>&#8594;</font>
                                      <input type="text" size="1" name="OSPUPILSIZE2" id="OSPUPILSIZE2" style="width:0.25in;height:0.2in;" value="<?php echo attr($OSPUPILSIZE2); ?>">
                                  </td>
                                  <td style="border-left:1pt solid black;border-right:1pt solid black;border-top:1pt solid black;">
                                      <input type=text style="margin: 0 2 0 2;width:0.3in;height:0.2in;" name='OSPUPILREACTIVITY' id='OSPUPILREACTIVITY' value="<?php echo attr($OSPUPILREACTIVITY); ?>">
                                  </td>
                                  <td style="border-top:1pt solid black;">
                                      <input type="text" style="margin: 0 2 0 2;width:0.20in;height:0.2in;" name="OSAPD" id="OSAPD" value='<?php echo attr($OSAPD); ?>'>
                                  </td>
                              </tr>
                          </table>
                      </div>  
                  </div>
                  <!-- end of the Pupils box -->
          </div>
            <!-- end of the CLINICAL BOX -->
                  <!-- start of slide down pupils_panel --> 
                  <?php ($DIMODPUPILSIZE != '') ? ($display_dim_pupils_panel = "display") : ($display_dim_pupils_panel = "nodisplay"); ?>
                  <div id="dim_pupils_panel" name="dim_pupils_panel" class="vitals <?php echo attr($display_dim_pupils_panel); ?>" 
                    style="z-index:99;position:relative;float:right;height: 1.05in; width:2.3in;padding: 0.02in; border: 1.00pt solid #000000; ">                     
                      <span class="top_left"><b id="pupils_DIM" style="width:100px;"><?php echo xlt('Pupils') ?>: <?php echo xlt('Dim'); ?></b> </span>
                      <div id="Lyr7.1" style="position: absolute; top: 0.3in; left: 0.1in; border: none;padding: auto;">
                          <table cellpadding="2" cellpadding="0" style="font-size: 0.9em;"> 
                              <tr>    
                                  <th></th>
                                  <th style="width:0.7in;padding: 0;"><?php echo xlt('size'); ?> (<?php echo xlt('mm'); ?>)
                                  </th>
                              </tr>
                              <tr>    
                                  <td><b><?php echo xlt('OD'); ?></b>
                                  </td>
                                  <td style="border-bottom:1pt solid black;">
                                      <input type="text" size=1 id ="DIMODPUPILSIZE1" name="DIMODPUPILSIZE1" style="width:0.25in;height:0.2in;" value='<?php echo attr($DIMODPUPILSIZE1); ?>'>
                                      <font style="font-size:1.0em;">&#8594;</font>
                                      <input type="text" id ="DIMODPUPILSIZE2" size=1 name="DIMODPUPILSIZE2" style="width:0.25in;height:0.2in;" value='<?php echo attr($DIMODPUPILSIZE2); ?>'>
                                  </td>
                              </tr>
                              <tr>    
                                  <td ><b><?php echo xlt('OS'); ?></b>
                                  </td>
                                  <td style="border-top:1pt solid black;">
                                      <input type="text" size=1 name="DIMOSPUPILSIZE1" id="DIMOSPUPILSIZE1" style="width:0.25in;height:0.2in;" value="<?php echo attr($DIMOSPUPILSIZE1); ?>">
                                      <font style="font-size:1.0em;">&#8594;</font>
                                      <input type='text' size=1 name='DIMOSPUPILSIZE2' id='DIMOSPUPILSIZE2' style="width:0.25in;height:0.2in;" value='<?php echo attr($DIMOSPUPILSIZE2); ?>'>
                                  </td>
                              </tr>
                          </table>
                      </div>   
                      <div style="position:absolute;  top: 0.2in; left: 1.1in; border: none;padding: auto;">
                          <b><?php echo xlt('Comments'); ?>:</b><br />
                          <textarea style="height:0.60in;width:95px;font-size:0.8em;" id="PUPIL_COMMENTS" name="PUPIL_COMMENTS"><?php echo text($PUPIL_COMMENTS); ?></textarea>
                      </div>
                  </div> 
                  <!-- end of slide down pupils_panel --> 
                  
         &nbsp;

            <!-- start of the refraction box -->
          <div class="loading" id="EXAM_sections_loading" 
            name="REFRACTION_sections_loading">
             <i class="fa fa-spinner fa-spin"></i>
          </div> 
          <div id="REFRACTION_sections" name="REFRACTION_sections nodisplay" style="position:relative;text-align:center;">
            <div id="LayerVision2" style="text-align:center;" class="section" >
                <table id="refraction_width" name="refraction_width" style="text-align:center;margin: 0 0;max-width: 900px;">
                    <tr>
                        <td style="text-align:center;">
                            <!-- start IOP chart section -->
                            <?php ($IOP ==1) ? ($display_IOP = "display") : ($display_IOP = "nodisplay"); ?>
                            <div id="LayerVision_IOP" class="borderShadow <?php echo $display_IOP; ?>">
                                <span class="closeButton fa fa-close" id="Close_IOP" name="Close_IOP"></span>
                                 <table id="iopgraph" name "iopgraph" >
                                    <h4> Placeholder: plot pressures over time</h4>
                                 </table>
                            </div>
                            <!-- end IOP chart section -->
                            <?php ($W ==1) ? ($display_W = "display") : ($display_W = "nodisplay"); ?>
                            <div id="LayerVision_W" class="refraction borderShadow <?php echo $display_W; ?>">
                              <span class="closeButton fa fa-close" id="Close_W" name="Close_W" title="<?php echo attr('Close and make this a Preference'); ?>"></span>
                              <i onclick="top.restoreSession();  doscript('W','<?php echo attr($pid); ?>','<?php echo attr($encounter); ?>'); return false;" 
                               title="<?php echo attr("Dispense Rx"); ?>" class="closeButton2 fa fa-print"></i>
                              <i onclick="top.restoreSession();  dispensed('<?php echo attr($pid); ?>');return false;" 
                                 title="<?php echo attr("Previously dispensed\n  Spectacle Rxs  "); ?>" class=" fa fa-list-ul closebutton3"></i>
                                        
                            
                                <table id="wearing" >
                                    <tr>
                                        <th colspan="9" id="wearing_title"><?php echo xlt('Current Glasses'); ?>
                                        </th>
                                    </tr>
                                    <tr style="font-weight:400;">
                                        <td ></td>
                                        <td></td>
                                        <td><?php echo xlt('Sph'); ?></td>
                                        <td><?php echo xlt('Cyl'); ?></td>
                                        <td><?php echo xlt('Axis'); ?></td>
                                        <td><?php echo xlt('Prism'); ?></td>
                                        <td><?php echo xlt('Acuity'); ?></td>
                                        <td rowspan="7" class="right" style="padding:10 0 10 0;font-size:0.6em;">
                                            <b style="font-weight:600;text-decoration:underline;">Rx Type</b><br />
                                            <label for="Single" class="input-helper input-helper--checkbox"><?php echo xlt('Single'); ?></label>
                                            <input type="radio" value="0" id="Single" name="RX1" <?php if ($RX1 == '0') echo 'checked="checked"'; ?> /></span><br /><br />
                                            <label for="Bifocal" class="input-helper input-helper--checkbox"><?php echo xlt('Bifocal'); ?></label>
                                            <input type="radio" value="1" id="Bifocal" name="RX1" <?php if ($RX1 == '1') echo 'checked="checked"'; ?> /></span><br /><br />
                                            <label for="Trifocal" class="input-helper input-helper--checkbox"><?php echo xlt('Trifocal'); ?></label>
                                            <input type="radio" value="2" id="Trifocal" name="RX1" <?php if ($RX1 == '2') echo 'checked="checked"'; ?> /></span><br /><br />
                                            <label for="Progressive" class="input-helper input-helper--checkbox"><?php echo xlt('Prog.'); ?></label>
                                            <input type="radio" value="3" id="Progressive" name="RX1" <?php if ($RX1 == '3') echo 'checked="checked"'; ?> /></span><br />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td rowspan="2"><?php echo xlt('Dist'); ?></td>    
                                        <td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="WODSPH" name="WODSPH"  value="<?php echo attr($WODSPH); ?>" tabindex="100"></td>
                                        <td><input type=text id="WODCYL" name="WODCYL"  value="<?php echo attr($WODCYL); ?>" tabindex="101"></td>
                                        <td><input type=text id="WODAXIS" name="WODAXIS" value="<?php echo attr($WODAXIS); ?>" tabindex="102"></td>
                                        <td><input type=text id="WODPRISM" name="WODPRISM" value="<?php echo attr($WODPRISM); ?>"></td>
                                        <td><input type=text id="WODVA" name="WODVA" value="<?php echo attr($WODVA); ?>" tabindex="108"></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="WOSSPH" name="WOSSPH" value="<?php echo attr($WOSSPH); ?>" tabindex="103"></td>
                                        <td><input type=text id="WOSCYL" name="WOSCYL" value="<?php echo attr($WOSCYL); ?>" tabindex="104"></td>
                                        <td><input type=text id="WOSAXIS" name="WOSAXIS" value="<?php echo attr($WOSAXIS); ?>" tabindex="105"></td>
                                        <td><input type=text id="WOSPRISM" name="WOSPRISM" value="<?php echo attr($WOSPRISM); ?>"></td>
                                        <td><input type=text id="WOSVA" name="WOSVA" value="<?php echo attr($WOSVA); ?>" tabindex="109"></td>
                                    </tr>
                                    <tr class="WNEAR">
                                        <td rowspan=2><span style="text-decoration:none;"><?php echo xlt('Mid'); ?>/<br /><?php echo xlt('Near'); ?></span></td>    
                                        <td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td class="WMid nodisplay"><input type=text id="WODADD1" name="WODADD1" value="<?php echo attr($WODADD1); ?>"></td>
                                        <td class="WAdd2"><input type=text id="WODADD2" name="WODADD2" value="<?php echo attr($WODADD2); ?>" tabindex="106"></td>
                                        <td class="WHIDECYL"><input type=text id="WNEARODCYL" name="WNEARODCYL" value="<?php echo attr($WNEARODCYL); ?>"></td>
                                        <td><input type=text id="WNEARODAXIS" name="WNEARODAXIS" value="<?php echo attr($WNEARODAXIS); ?>"></td>
                                        <td><input type=text id="WNEARODPRISM" name="WODPRISMNEAR" value="<?php echo attr($WNEARODPRISM); ?>"></td>
                                        <td><input type=text id="WNEARODVA" name="WNEARODVA" value="<?php echo attr($WNEARODVA); ?>" tabindex="110"></td>
                                    </tr>
                                    <tr class="WNEAR">
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td class="WMid nodisplay"><input type=text id="WOSADD1" name="WOSADD1" value="<?php echo attr($WOSADD1); ?>"></td>
                                        <td class="WAdd2"><input type=text id="WOSADD2" name="WOSADD2" value="<?php echo attr($WOSADD2); ?>" tabindex="107"></td>
                                        <td class="WHIDECYL"><input type=text id="WNEAROSCYL" name="WNEAROSCYL" value="<?php echo attr($WNEAROSCYL); ?>"></td>
                                        <td><input type=text id="WNEAROSAXIS" name="WNEAROSAXIS" value="<?php echo attr($WNEAROSAXIS); ?>"></td>
                                        <td><input type=text id="WNEAROSPRISM" name="WNEAROSPRISM" value="<?php echo attr($WNEAROSPRISM); ?>"></td>
                                        <td><input type=text id="WNEAROSVA" name="WNEAROSVA" value="<?php echo attr($WNEAROSVA); ?>" tabindex="110"></td>
                                    </tr>
                                    <tr style="top:3.5in;">
                                        <td colspan="2" style="text-align:right;vertical-align:top;top:0px;"><b><?php echo xlt('Comments'); ?>:</b>
                                        </td>
                                        <td colspan="4" class="up" style="text-align:left;vertical-align:middle;top:0px;"></td></tr>
                                    <tr><td colspan="8">
                                            <textarea style="width:100%;height:3.0em;" id="WCOMMENTS" name="WCOMMENTS"><?php echo text($WCOMMENTS); ?></textarea>     
                                        </td>
                                        <td colspan="2"> 
                                            
                                        </td>
                                    </tr>
                                    <tr id="signature_W" class="nodisplay">
                                        <td colspan="5">
                                            <span style="font-size:0.7em;font-weight:bold;"><?php echo xlt('e-signature'); ?>:</span> <i><?php echo text($providerID); ?></i>
                                        </td>
                                        <td colspan="3" style="text-align:right;text-decoration:underline;font-size:0.8em;font-weight:bold;"><?php echo xlt('DATE'); ?>: <?php echo $date; ?></td>
                                    </tr>
                                </table>
                            </div>

                            <?php ($MR==1) ? ($display_AR = "display") : ($display_AR = "nodisplay");?>
                            <div id="LayerVision_MR" class="refraction borderShadow <?php echo $display_AR; ?>">
                                <span class="closeButton fa  fa-close" id="Close_MR" name="Close_MR" title="<?php echo attr('Close and make this a Preference'); ?>"></span>
                                <span class="closeButton2 fa fa-print"  style="margin:0 7;" title="<?php echo attr("Dispense Rx"); ?>" onclick="top.restoreSession();doscript('MR',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></span>
                                    
                                <table id="dry_wet_refraction">
                                    <th colspan="5"><?php echo xlt('Manifest (Dry) Refraction'); ?></th>
                                    <th NOWRAP colspan="2">
                                            <input type="checkbox" name="BALANCED" id="Balanced" value="on" <?php if ($BALANCED =='on') echo "checked='checked'"; ?>>
                                            <label for="Balanced" class="input-helper input-helper--checkbox"><?php echo xlt('Balanced'); ?></label>
                                    </th>
                                   
                                    <tr>
                                        <td></td>
                                        <td><?php echo xlt('Sph'); ?></td>
                                        <td><?php echo xlt('Cyl'); ?></td>
                                        <td><?php echo xlt('Axis'); ?></td>
                                        <td><?php echo xlt('Acuity'); ?></td>
                                        <td><?php echo xlt('ADD'); ?></td>
                                        <td><?php echo xlt('Jaeger'); ?></td>
                                        <td><?php echo xlt('Prism'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="MRODSPH" name="MRODSPH" value="<?php echo attr($MRODSPH); ?>" tabindex="130"></td>
                                        <td><input type=text id="MRODCYL" name="MRODCYL" value="<?php echo attr($MRODCYL); ?>" tabindex="131"></td>
                                        <td><input type=text id="MRODAXIS"  name="MRODAXIS" value="<?php echo attr($MRODAXIS); ?>" tabindex="132"></td>
                                        <td><input type=text id="MRODVA"  name="MRODVA" value="<?php echo attr($MRODVA); ?>" tabindex="138"></td>
                                        <td><input type=text id="MRODADD"  name="MRODADD" value="<?php echo attr($MRODADD); ?>" tabindex="136"></td>
                                        <td><input type=text id="MRNEARODVA"  name="MRNEARODVA" value="<?php echo attr($MRNEARODVA); ?>"></td>
                                        <td><input type=text id="MRODPRISM"  name="MRODPRISM" value="<?php echo attr($MRODPRISM); ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="MROSSPH" name="MROSSPH" value="<?php echo attr($MROSSPH); ?>" tabindex="133"></td>
                                        <td><input type=text id="MROSCYL" name="MROSCYL" value="<?php echo attr($MROSCYL); ?>" tabindex="134"></td>
                                        <td><input type=text id="MROSAXIS"  name="MROSAXIS" value="<?php echo attr($MROSAXIS); ?>" tabindex="135"></td>
                                        <td><input type=text id="MROSVA"  name="MROSVA" value="<?php echo attr($MROSVA); ?>" tabindex="139"></td>
                                        <td><input type=text id="MROSADD"  name="MROSADD" value="<?php echo attr($MROSADD); ?>" tabindex="137"></td>
                                        <td><input type=text id="MRNEAROSVA"  name="MRNEAROSVA" value="<?php echo attr($MRNEAROSVA); ?>"></td>
                                        <td><input type=text id="MROSPRISM"  name="MROSPRISM" value="<?php echo attr($MROSPRISM); ?>"></td>
                                    </tr>
                                </table>
                                <table >
                                    <th colspan=6 style="padding-top:10px;"><?php echo xlt('Cycloplegic (Wet) Refraction'); ?></th>
                                     <th colspan=4 style="text-align:right;"><i title="<?php echo attr("Dispense Rx"); ?>" class="fa fa-print" onclick="top.restoreSession();doscript(CR',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></i></th>

                                    <tr>
                                        <td></td>
                                        <td><?php echo xlt('Sph'); ?></td>
                                        <td><?php echo xlt('Cyl'); ?></td>
                                        <td><?php echo xlt('Axis'); ?></td>
                                        <td><?php echo xlt('Acuity'); ?></td>
                                        <td colspan="1" style="text-align:left;width:75px;">
                                            <input type="radio" name="WETTYPE" id="Flash" value="Flash" <?php if ($WETTYPE == "Flash") echo "checked='checked'"; ?>/>
                                            <label for="Flash" class="input-helper input-helper--checkbox"><?php echo xlt('Flash'); ?></label>
                                        </td>
                                        <td><?php echo xlt('IOP'); ?>
                                          <input type="hidden" name="IOPPOSTTIME" id="IOPPOSTTIME" value="">
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="CRODSPH" name="CRODSPH" value="<?php echo attr($CRODSPH); ?>"></td>
                                        <td><input type=text id="CRODCYL" name="CRODCYL" value="<?php echo attr($CRODCYL); ?>"></td>
                                        <td><input type=text id="CRODAXIS" name="CRODAXIS" value="<?php echo attr($CRODAXIS); ?>"></td>
                                        <td><input type=text id="CRODVA" name="CRODVA"  value="<?php echo attr($CRODVA); ?>"></td>
                                        <td colspan="1" style="text-align:left;">
                                            <input type="radio" name="WETTYPE" id="Auto" value="Auto" <?php if ($WETTYPE == "Auto") echo "checked='checked'"; ?>>
                                            <label for="Auto" class="input-helper input-helper--checkbox"><?php echo xlt('Auto'); ?></label>
                                        </td>
                                        <td><input type=text id="ODIOPPOST" name="ODIOPPOST"  value="<?php echo attr($ODIOPPOST); ?>">
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="CROSSPH" name="CROSSPH" value="<?php echo attr($CROSSPH); ?>"></td>
                                        <td><input type=text id="CROSCYL" name="CROSCYL" value="<?php echo attr($CROSCYL); ?>"></td>
                                        <td><input type=text id="CROSAXIS" name="CROSAXIS" value="<?php echo attr($CROSAXIS); ?>"></td>
                                        <td><input type=text id="CROSVA" name="CROSVA" value="<?php echo attr($CROSVA); ?>"></td>
                                        <td colspan="1" style="text-align:left;">
                                            <input type="radio" name="WETTYPE" id="Manual" value="Manual" <?php if ($WETTYPE == "Manual") echo "checked='checked'"; ?>>
                                            <label for="Manual" class="input-helper input-helper--checkbox"><?php echo xlt('Manual'); ?></label>
                                        </td>
                                        <td><input type=text id="OSIOPPOST" name="OSIOPPOST"  value="<?php echo attr($OSIOPPOST); ?>"></td>
                                    </tr>
                                    
                                  </table>
                            </div>

                            <?php ($CR==1)  ? ($display_Cyclo = "display") : ($display_Cyclo = "nodisplay"); ?>
                            <div id="LayerVision_CR" class="refraction borderShadow <?php echo $display_Cyclo; ?>">
                                <span title="<?php echo attr('Close and make this a Preference'); ?>" class="closeButton fa  fa-close" id="Close_CR" name="Close_CR"></span>
                                <i title="<?php echo attr("Dispense Rx"); ?>" class="closeButton2 fa fa-print" onclick="top.restoreSession();doscript('CR',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></i>
                                <table id="autorefraction">
                                  <th colspan=9>Auto Refraction</th>
                                    <tr>
                                        <td></td>
                                        <td><?php echo xlt('Sph'); ?></td>
                                        <td><?php echo xlt('Cyl'); ?></td>
                                        <td><?php echo xlt('Axis'); ?></td>
                                        <td><?php echo xlt('Acuity'); ?></td>
                                        <td><?php echo xlt('ADD'); ?></td>
                                        <td><?php echo xlt('Jaeger'); ?></td>
                                        <td><?php echo xlt('Prism'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="ARODSPH" name="ARODSPH" value="<?php echo attr($ARODSPH); ?>" tabindex="120"></td>
                                        <td><input type=text id="ARODCYL" name="ARODCYL" value="<?php echo attr($ARODCYL); ?>" tabindex="121"></td>
                                        <td><input type=text id="ARODAXIS" name="ARODAXIS" value="<?php echo attr($ARODAXIS); ?>" tabindex="122"></td>
                                        <td><input type=text id="ARODVA" name="ARODVA" value="<?php echo attr($ARODVA); ?>" tabindex="128"></td>
                                        <td><input type=text id="ARODADD" name="ARODADD" value="<?php echo attr($ARODADD); ?>" tabindex="126"></td>
                                        <td><input type=text id="ARNEARODVA" name="ARNEARODVA" value="<?php echo attr($ARNEARODVA); ?>"></td>
                                        <td><input type=text id="ARODPRISM" name="ARODPRISM" value="<?php echo attr($ARODPRISM); ?>"></td>
                                    </tr>
                                     <tr>
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="AROSSPH" name="AROSSPH" value="<?php echo attr($AROSSPH); ?>" tabindex="123"></td>
                                        <td><input type=text id="AROSCYL" name="AROSCYL" value="<?php echo attr($AROSCYL); ?>" tabindex="124"></td>
                                        <td><input type=text id="AROSAXIS" name="AROSAXIS" value="<?php echo attr($AROSAXIS); ?>" tabindex="125"></td>
                                        <td><input type=text id="AROSVA" name="AROSVA" value="<?php echo attr($AROSVA); ?>" tabindex="129"></td>
                                        <td><input type=text id="AROSADD" name="AROSADD" value="<?php echo attr($AROSADD); ?>" tabindex="127"></td>
                                        <td><input type=text id="ARNEAROSVA" name="ARNEAROSVA" value="<?php echo attr($ARNEAROSVA); ?>"></td>
                                        <td><input type=text id="AROSPRISM" name="AROSPRISM" value="<?php echo attr($AROSPRISM); ?>"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="vertical-align:bottom;"><b><?php echo xlt('Comments'); ?>:</b></td>
                                        <td colspan="4"></td>

                                    </tr>
                                    <tr>
                                        <td colspan="9" style="text-align:center;"><textarea id="CRCOMMENTS" name="CRCOMMENTS" style="width:98%;height:3.5em;"><?php echo attr($CRCOMMENTS); ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <?php ($CTL==1) ? ($display_CTL = "display") : ($display_CTL = "nodisplay"); ?>
                            <div id="LayerVision_CTL" class="refraction borderShadow <?php echo $display_CTL; ?>">
                                <span title="<?php echo attr("Dispense Rx"); ?>" title="<?php echo attr('Close and make this a Preference'); ?>" class="closeButton fa  fa-close" id="Close_CTL" name="Close_CTL"></span>
                                <i title="<?php echo attr("Dispense Rx"); ?>" class="closeButton2 fa fa-print" onclick="top.restoreSession();doscript('CTL',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></i>
                                <table id="CTL" style="width:100%;">
                                    <th colspan="9"><?php echo xlt('Contact Lens Refraction'); ?></th>
                                    <tr>
                                        <td style="text-align:center;">
                                            <div style="box-shadow: 1px 1px 2px #888888;border-radius: 8px; margin: 5 auto; position:inline-block; Xpadding: 0.02in; border: 1.00pt solid #000000; ">
                                                <table>
                                                    <tr>
                                                        <td></td>
                                                        <td><?php echo xlt('Manufacturer'); ?></td>
                                                        <td><?php echo xlt('Supplier'); ?></td>
                                                        <td><?php echo xlt('Brand'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b><?php echo xlt('OD'); ?>:</b></td>
                                                        <td>
                                                            <!--  these will need to be pulled from a CTL specific table probably -->
                                                            <select id="CTLMANUFACTUREROD" name="CTLMANUFACTUREROD">
                                                                <option></option>
                                                                <option value="BL"><?php echo xlt('Bausch and Lomb'); ?></option>
                                                                <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select id="CTLSUPPLIEROD" name="CTLMANUFACTUREROD">
                                                                <option></option>
                                                                <option value="ABB"><?php echo xlt('ABB'); ?></option>
                                                                <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select id="CTLBRANDOD" name="CTLBRANDOD">
                                                                <option></option>
                                                                <option value="Accuvue"><?php echo xlt('Accuvue'); ?></option>
                                                                <option value="ExtremeH2O"><?php echo xlt('Extreme H2O'); ?></option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                                        <td>
                                                            <select id="CTLMANUFACTUREROS" name="CTLMANUFACTUREROS">
                                                              <!-- gotta generate this from a list -->
                                                                <option></option>
                                                                <option value="BL"><?php echo xlt('Bausch and Lomb'); ?></option>
                                                                <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select id="CTLSUPPLIEROS" name="CTLSUPPLIEROS">
                                                                <option></option>
                                                                <option value="ABB"><?php echo xlt('ABB'); ?></option>
                                                                <option value="JNJ"><?php echo xlt('JNJ'); ?></option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select id="CTLBRANDOS" name="CTLBRANDOS">
                                                                <option></option>
                                                                <option value="Accuvue"><?php echo xlt('Accuvue'); ?></option>
                                                                <option value="ExtremeH2O"><?php echo xlt('Extreme H2O'); ?></option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <table>
                                    <tr>
                                        <td></td>
                                        <td><?php echo xlt('Sph'); ?></td>
                                        <td><?php echo xlt('Cyl'); ?></td>
                                        <td><?php echo xlt('Axis'); ?></td>
                                        <td><?php echo xlt('BC'); ?></td>
                                        <td><?php echo xlt('Diam'); ?></td>
                                        <td><?php echo xlt('ADD'); ?></td>
                                        <td><?php echo xlt('Acuity'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="CTLODSPH" name="CTLODSPH" value="<?php echo attr($CTLODSPH); ?>"></td>
                                        <td><input type=text id="CTLODCYL" name="CTLODCYL" value="<?php echo attr($CTLODCYL); ?>"></td>
                                        <td><input type=text id="CTLODAXIS" name="CTLODAXIS" value="<?php echo attr($CTLODAXIS); ?>"></td>
                                        <td><input type=text id="CTLODBC" name="CTLODBC" value="<?php echo attr($CTLODBC); ?>"></td>
                                        <td><input type=text id="CTLODDIAM" name="CTLODDIAM" value="<?php echo attr($CTLODDIAM); ?>"></td>
                                        <td><input type=text id="CTLODADD" name="CTLODADD" value="<?php echo attr($CTLODADD); ?>"></td>
                                        <td><input type=text id="CTLODVA" name="CTLODVA" value="<?php echo attr($CTLODVA); ?>"></td>
                                    </tr>
                                    <tr >
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="CTLOSSPH" name="CTLOSSPH" value="<?php echo attr($CTLOSSPH); ?>"></td>
                                        <td><input type=text id="CTLOSCYL" name="CTLOSCYL" value="<?php echo attr($CTLOSCYL); ?>"></td>
                                        <td><input type=text id="CTLOSAXIS" name="CTLOSAXIS" value="<?php echo attr($CTLOSAXIS); ?>"></td>
                                        <td><input type=text id="CTLOSBC" name="CTLOSBC" value="<?php echo attr($CTLOSBC); ?>"></td>
                                        <td><input type=text id="CTLOSDIAM" name="CTLOSDIAM" value="<?php echo attr($CTLOSDIAM); ?>"></td>
                                        <td><input type=text id="CTLOSADD" name="CTLOSADD" value="<?php echo attr($CTLOSADD); ?>"></td>
                                        <td><input type=text id="CTLOSVA" name="CTLOSVA" value="<?php echo attr($CTLOSVA); ?>"></td>
                                    </tr>
                                </table>
                            </div>

                            <?php ($ADDITIONAL==1) ? ($display_Add = "display") : ($display_Add = "nodisplay"); ?>
                            <div id="LayerVision_ADDITIONAL" class="refraction borderShadow <?php echo $display_Add; ?>">
                                <span title="<?php echo xla("Dispense Rx"); ?>" title="<?php echo xla('Close and make this a Preference'); ?>" class="closeButton fa  fa-close" id="Close_ADDITIONAL" name="Close_ADDITIONAL"></span>

                                <table id="Additional">
                                    <th colspan=9><?php echo xlt('Additional Data Points'); ?></th>
                                    <tr><td></td>
                                        <td><?php echo xlt('PH'); ?></td>
                                        <td><?php echo xlt('PAM'); ?></td>
                                        <td><?php echo xlt('LI'); ?></td>
                                        <td><?php echo xlt('BAT'); ?></td>
                                        <td><?php echo xlt('K1'); ?></td>
                                        <td><?php echo xlt('K2'); ?></td>
                                        <td><?php echo xlt('Axis'); ?></td>
                                      </tr>
                                    <tr><td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="PHODVA" name="PHODVA" title="<?php echo xla('Pinhole Vision'); ?>" value="<?php echo attr($PHODVA); ?>"></td>
                                        <td><input type=text id="PAMODVA" name="PAMODVA" title="<?php echo xla('Potential Acuity Meter'); ?>" value="<?php echo attr($PAMODVA); ?>"></td>
                                        <td><input type=text id="LIODVA" name="LIODVA"  title="<?php echo xla('Laser Interferometry'); ?>" value="<?php echo attr($LIODVA); ?>"></td>
                                        <td><input type=text id="GLAREODVA" name="GLAREODVA" title="<?php echo xla('Brightness Acuity Testing'); ?>" value="<?php echo attr($GLAREODVA); ?>"></td>
                                        <td><input type=text id="ODK1" name="ODK1" value="<?php echo attr($ODK1); ?>"></td>
                                        <td><input type=text id="ODK2" name="ODK2" value="<?php echo attr($ODK2); ?>"></td>
                                        <td><input type=text id="ODK2AXIS" name="ODK2AXIS" value="<?php echo attr($ODK2AXIS); ?>"></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="PHOSVA" name="PHOSVA" title="<?php echo xla('Pinhole Vision'); ?>" value="<?php echo attr($PHOSVA); ?>"></td>
                                        <td><input type=text id="PAMOSVA" name="PAMOSVA" title="<?php echo xla('Potential Acuity Meter'); ?>" value="<?php echo attr($PAMOSVA); ?>"></td>
                                        <td><input type=text id="LIOSVA" name="LIOSVA" title="<?php echo xla('Laser Interferometry'); ?>" value="<?php echo attr($LIOSVA); ?>"></td>
                                        <td><input type=text id="GLAREOSVA" name="GLAREOSVA" title="<?php echo xla('Brightness Acuity Testing'); ?>" value="<?php echo attr($GLAREOSVA); ?>"></td>
                                        <td><input type=text id="OSK1" name="OSK1" value="<?php echo attr($OSK1); ?>"></td>
                                        <td><input type=text id="OSK2" name="OSK2" value="<?php echo attr($OSK2); ?>"></td>
                                        <td><input type=text id="OSK2AXIS" name="OSK2AXIS" value="<?php echo attr($OSK2AXIS); ?>"></td>
                                    </tr>
                                    <tr><td>&nbsp;</td></tr>
                                    <tr>
                                        <td></td>
                                        <td><?php echo xlt('AxLength'); ?></td>
                                        <td><?php echo xlt('ACD'); ?></td>
                                        <td><?php echo xlt('PD'); ?></td>
                                        <td><?php echo xlt('LT'); ?></td>
                                        <td><?php echo xlt('W2W'); ?></td>
                                        <td><?php echo xlt('ECL'); ?></td>
                                        <!-- <td><?php echo xlt('pend'); ?></td> -->
                                    </tr>
                                    <tr><td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="ODAXIALLENGTH" name="ODAXIALLENGTH"  value="<?php echo attr($ODAXIALLENGTH); ?>"></td>
                                        <td><input type=text id="ODACD" name="ODACD"  value="<?php echo attr($ODACD); ?>"></td>
                                        <td><input type=text id="ODPDMeasured" name="ODPDMeasured"  value="<?php echo attr($ODPDMeasured); ?>"></td>
                                        <td><input type=text id="ODLT" name="ODLT"  value="<?php echo attr($ODLT); ?>"></td>
                                        <td><input type=text id="ODW2W" name="ODW2W"  value="<?php echo attr($ODW2W); ?>"></td>
                                        <td><input type=text id="ODECL" name="ODECL"  value="<?php echo attr($ODECL); ?>"></td>
                                        <!-- <td><input type=text id="pend" name="pend"  value="<?php echo attr($pend); ?>"></td> -->
                                    </tr>
                                    <tr>
                                        <td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="OSAXIALLENGTH" name="OSAXIALLENGTH" value="<?php echo attr($OSAXIALLENGTH); ?>"></td>
                                        <td><input type=text id="OSACD" name="OSACD" value="<?php echo attr($OSACD); ?>"></td>
                                        <td><input type=text id="OSPDMeasured" name="OSPDMeasured" value="<?php echo attr($OSPDMeasured); ?>"></td>
                                            <td><input type=text id="OSLT" name="OSLT" value="<?php echo attr($OSLT); ?>"></td>
                                            <td><input type=text id="OSW2W" name="OSW2W" value="<?php echo attr($OSW2W); ?>"></td>
                                            <td><input type=text id="OSECL" name="OSECL" value="<?php echo attr($OSECL); ?>"></td>
                                            <!--  <td><input type=text id="pend" name="pend" value="<?php echo attr($pend); ?>"></td> -->
                                        </tr>
                                    </table>
                            </div>  

                            <?php ($VAX==1) ? ($display_Add = "display") : ($display_Add = "nodisplay"); ?>
                            <div id="LayerVision_VAX" class="refraction borderShadow <?php echo $display_Add; ?>">
                                <span title="<?php echo attr('Close and make this a Preference'); ?>" class="closeButton fa  fa-close" id="Close_VAX" name="Close_VAX"></span> 
                                <table id="Additional_VA">
                                    <th colspan="9"><?php echo xlt('Visual Acuity'); ?></th>
                                    <tr><td></td>
                                        <td><?php echo xlt('SC'); ?></td>
                                        <td><?php echo xlt('W Rx'); ?></td>
                                        <td><?php echo xlt('AR'); ?></td>
                                        <td><?php echo xlt('MR'); ?></td>
                                        <td><?php echo xlt('CR'); ?></td>
                                        <td><?php echo xlt('PH'); ?></td>
                                        <td><?php echo xlt('CTL'); ?></td>
                                        
                                    </tr>
                                    <tr><td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="SCODVA_copy_brd" name="SCODVA_copy_brd" value="<?php echo attr($SCODVA); ?>" tabindex="99"></td>
                                        <td><input type=text id="WODVA_copy_brd" name="WODVA_copy_brd" value="<?php echo attr($WODVA); ?>" tabindex="102"></td>
                                        <td><input type=text id="ARODVA_copy_brd" name="ARODVA_copy_brd" value="<?php echo attr($ARODVA); ?>" tabindex="104"></td>
                                        <td><input type=text id="MRODVA_copy_brd" name="MRODVA_copy_brd" value="<?php echo attr($MRODVA); ?>" tabindex="106"></td>
                                        <td><input type=text id="CRODVA_copy_brd" name="CRODVA_copy_brd" value="<?php echo attr($CRODVA); ?>" tabindex="108"></td>
                                        <td><input type=text id="PHODVA_copy_brd" name="PHODVA_copy_brd" value="<?php echo attr($PHODVA); ?>" tabindex="110"></td>
                                        <td><input type=text id="CTLODVA_copy_brd" name="CTLODVA_copy_brd" value="<?php echo attr($CTLODVA); ?>" tabindex="100"></td>
                                        </tr>
                                     <tr><td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="SCOSVA_copy" name="SCOSVA_copy" value="<?php echo attr($SCOSVA); ?>" tabindex="100"></td>
                                        <td><input type=text id="WOSVA_copy_brd" name="WOSVA_copy_brd" value="<?php echo attr($WOSVA); ?>" tabindex="101"></td>
                                        <td><input type=text id="AROSVA_copy_brd" name="AROSVA_copy_brd" value="<?php echo attr($AROSVA); ?>" tabindex="103"></td>
                                        <td><input type=text id="MROSVA_copy_brd" name="MROSVA_copy_brd" value="<?php echo attr($MROSVA); ?>" tabindex="105"></td>
                                        <td><input type=text id="CROSVA_copy_brd" name="CROSVA_copy_brd" value="<?php echo attr($CROSVA); ?>" tabindex="107"></td>
                                        <td><input type=text id="PHOSVA_copy_brd" name="PHOSVA_copy_brd" value="<?php echo attr($PHOSVA); ?>" tabindex="109"></td>
                                        <td><input type=text id="CTLOSVA_copy_brd" name="CTLOSVA_copy_brd" value="<?php echo attr($CTLOSVA); ?>" tabindex="111"></td>
                                    </tr>
                                    <tr><td>&nbsp;</td></tr>
                                    <tr>
                                        <td></td>
                                        <td><?php echo xlt('scNear'); ?></td>
                                        <td><?php echo xlt('ccNear'); ?></td>
                                        <td><?php echo xlt('ARNear'); ?></td>
                                        <td><?php echo xlt('MRNear'); ?></td>
                                        <td><?php echo xlt('PAM'); ?></td>
                                        <td><?php echo xlt('BAT'); ?></td>
                                        <td><?php echo xlt('Contrast'); ?></td>
                                    </tr>
                                     <tr><td><b><?php echo xlt('OD'); ?>:</b></td>
                                        <td><input type=text id="SCNEARODVA" title="<?php echo xla('Near Acuity without Correction'); ?>" name="SCNEARODVA" value="<?php echo attr($SCNEARODVA); ?>"></td>
                                        <td><input type=text id="WNEARODVA_copy_brd" title="<?php echo xla('Near Acuity with Correction'); ?>" name="WNEARODVA_copy_brd" value="<?php echo attr($WNEARODVA); ?>"></td>
                                        <td><input type=text id="ARNEARODVA_copy_brd" title="<?php echo xla('Near Acuity AutoRefraction'); ?>" name="ARNEARODVA_copy_brd" value="<?php echo attr($ARNEARODVA); ?>"></td>
                                        <td><input type=text id="MRNEARODVA_copy_brd" title="<?php echo xla('Near Acuity Manifest Refraction'); ?>" name="MRNEARODVA_copy_brd" value="<?php echo attr($MRNEARODVA); ?>"></td>
                                        <td><input type=text id="PAMODVA_copy_brd" title="<?php echo xla('Potential Acuity Meter'); ?>" name="PAMODVA_copy_brd" value="<?php echo attr($PAMODVA); ?>"></td>
                                        <td><input type=text id="GLAREODVA_copy_brd" title="<?php echo xla('Brightness Acuity Testing'); ?>" name="GLAREODVA_copy_brd" value="<?php echo attr($GLAREODVA); ?>"></td>
                                        <td><input type=text id="CONTRASTODVA_copy_brd" title="<?php echo xla('Contrast Acuity Testing'); ?>" name="CONTRASTODVA_copy_brd" value="<?php echo attr($CONTRASTODVA); ?>"></td>
                                    </tr>
                                    <tr><td><b><?php echo xlt('OS'); ?>:</b></td>
                                        <td><input type=text id="SCNEAROSVA" title="<?php echo xla('Near Acuity without Correction'); ?>" name="SCNEAROSVA" value="<?php echo attr($SCNEAROSVA); ?>"></td>
                                        <td><input type=text id="WNEAROSVA_copy_brd" title="<?php echo xla('Near Acuity with Correction'); ?>" name="WNEAROSVA_copy_brd" value="<?php echo attr($WNEAROSVA); ?>"></td>
                                        <td><input type=text id="ARNEAROSVA_copy" title="<?php echo xla('Near Acuity AutoRefraction'); ?>" name="ARNEAROSVA_copy" value="<?php echo attr($ARNEAROSVA); ?>"></td>
                                        <td><input type=text id="MRNEAROSVA_copy" title="<?php echo xla('Near Acuity Manifest Refraction'); ?>" name="MRNEAROSVA_copy" value="<?php echo attr($MRNEAROSVA); ?>"></td>
                                        <td><input type=text id="PAMOSVA_copy_brd" title="<?php echo xla('Potential Acuity Meter'); ?>" name="PAMOSVA_copy_brd" value="<?php echo attr($PAMOSVA); ?>"></td>
                                        <td><input type=text id="GLAREOSVA_copy_brd" title="<?php echo xla('Brightness Acuity Testing'); ?>" name="GLAREOSVA_copy_brd" value="<?php echo attr($GLAREOSVA); ?>"></td>
                                        <td><input type=text id="CONTRASTOSVA" title="<?php echo xla('Contrast Acuity Testing'); ?>" name="CONTRASTOSVA" value="<?php echo attr($CONTRASTOSVA); ?>"></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
          </div>   
            <!-- end of the refraction box -->

            <!-- my reporting div for development only remove the "X" to see output from save.php-->
          <div id="tellme" name="tellme"></div>
            <!-- end reporting div -->

          <!-- Start of the exam selection/middle menu row -->
          <div class="sections" style="text-align:center;vertical-align:top;width:100%;margin:0;" name="mid_menu" id="mid_menu">
            <span class="anchor" id="SELECTION_ROW_anchor"></span>

            <!--  <span id="EXAM_settings" name="EXAM_settings" class="bordershadow" href="#"><i class="fa fa-cog"></i>&nbsp;<?php echo xlt('Settings'); ?></span> -->
            <span id="EXAM_defaults" name="EXAM_defaults" value="Defaults" class="bordershadow"><i class="fa fa-newspaper-o"></i>&nbsp;<b><?php echo xlt('Defaults'); ?></b></span> 
            <span id="EXAM_TEXT" name="EXAM_TEXT" value="TEXT" class="bordershadow"><i class="fa fa-hospital-o"></i>&nbsp;<b><?php echo xlt('Text'); ?></b></span>
            <span id="EXAM_DRAW" name="EXAM_DRAW" value="DRAW" class="bordershadow">
              <i class="fa fa-paint-brush fa-sm"> </i>&nbsp;<b><?php echo xlt('Draw'); ?></b></span>
            <span id="EXAM_QP" name="EXAM_QP" value="QP" class="bordershadow">
              <i class="fa fa-database fa-sm"> </i>&nbsp;<b><?php echo xlt('DB Picks'); ?></b>
            </span>
            <span id="PRIORS_ALL_left_text" name="PRIORS_ALL_left_text" 
                  class="borderShadow" style="padding-right:5px;">
                <?php $output = priors_select("ALL",$id,$id,$pid); 
                if ($output !='') {  echo $output; } else { echo "<b>"; echo xlt('First visit: No Old Records'); echo "</b>"; }
                ?>
            &nbsp;</span> 
            <span id="EXAM_ALL_keyboard_left" name="EXAM_ALL_keyboard_left" class="borderShadow" style="padding-right:5px;width:250px;">
              <span id="BAR_kb" name="BAR_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class=""><b>Keyboard Entry</b>
              </span>
                &nbsp;
                <a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=ext');">
                  <i title="<?php echo xla('Click for External shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i>
                </a>
            </span>
          </div>   
          
          <br />
          <div style="margin: 0 auto;text-align: center;font-size:1.0em;width:640px;" class="kb borderShadow" id="EXAM_KB" name="EXAM_KB">   
            <span class="BAR2_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke"><b>Keyboard Entry</b>
              </span>
                &nbsp;
                <a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=all');">
                  <i title="<?php echo xla('Click for Shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i>
                </a><textarea id="ALL_keyboard_left" name="ALL_keyboard_left" style="color:#0C0C0C;size:0.7em;height: 8em;width:90%;" tabindex='1000'></textarea>
          </div> 
          <!-- end of the exam selection row -->

          <!-- Start of the exam sections -->
          <div class="loading" id="EXAM_sections_loading" name="EXAM_sections_loading">
              <hr></hr>
              <i class="fa fa-spinner fa-spin"></i>
          </div> 
          
          <div style="margin: 0 auto;text-align: center;font-size:1.0em;" class="nodisplay" id="EXAM_sections" name="EXAM_sections">   
            <!-- start External Exam -->
            <div id="EXT_1" name="EXT_1" class="clear_both">
              <span class="anchor" id="EXT_anchor"></span>

                <div id="EXT_left" class="exam_section_left borderShadow" >
                  <div id="EXT_left_text" style="height: 2.5in;text-align:left;" class="TEXT_class">
                    <span class="closeButton fa fa-paint-brush" id="BUTTON_DRAW_EXT" name="BUTTON_DRAW_EXT"></span>
                    <i class="closeButton_2 fa fa-database" id="BUTTON_QP_EXT" name="BUTTON_QP_EXT"></i>
                    <b><?php echo xlt('External Exam'); ?>:</b><br />
                    <div style="position:relative;float:right;top:0.2in;">
                      <table style="text-align:center;font-weight:600;font-size:0.8em;">
                         <?php 
                            list($documents,$episode) = display($pid,$encounter, "EXT"); 
                            echo $episode;
                          ?>
                      </table>
                        <table style="text-align:center;font-weight:600;font-size:0.8em;">
                            <tr>
                                <td></td><td><?php echo xlt('R'); ?></td><td><?php echo xlt('L'); ?></td>
                            </tr>
                            <tr>
                                <td class="right" title="<?php echo xla('Levator Function'); ?>">
                                  <div class="kb kb_left">LF</div><?php echo xlt('Lev Fn'); ?></td>
                                <td><input  type="text"  name="RLF" id="RLF" value="<?php echo attr($RLF); ?>"></td>
                                <td><input  type="text"  name="LLF" id="LLF" value="<?php echo attr($LLF); ?>"></td>
                            </tr>
                            <tr>
                                <td class="right" title="<?php echo xla('Marginal Reflex Distance'); ?>">
                                  <div class="kb kb_left">MRD</div><?php echo xlt('MRD'); ?></td>
                                <td><input type="text" size="1" name="RMRD" id="RMRD" value="<?php echo attr($RMRD); ?>"></td>
                                <td><input type="text" size="1" name="LMRD" id="LMRD" value="<?php echo attr($LMRD); ?>"></td>
                            </tr>
                            <tr>
                                <td class="right" title="<?php echo xla('Vertical Fissure: central height between lid margins'); ?>">
                                  <div class="kb kb_left">VF</div><?php echo xlt('Vert Fissure'); ?></td>
                                <td><input type="text" size="1" name="RVFISSURE" id="RVFISSURE" value="<?php echo attr($RVFISSURE); ?>"></td>
                                <td><input type="text" size="1" name="LVFISSURE" id="LVFISSURE" value="<?php echo attr($LVFISSURE); ?>"></td>
                            </tr>
                                                           <tr>
                                <td class="right" title="<?php echo xla('Any carotid bruits appreciated?'); ?>">
                                  <div class="kb kb_left">CAR</div><?php echo xlt('Carotid'); ?></td>
                                <td><input  type="text"  name="RCAROTID" id="RCAROTID" value="<?php echo attr($RCAROTID); ?>"></td>
                                <td><input  type="text"  name="LCAROTID" id="LCAROTID" value="<?php echo attr($LCAROTID); ?>"></td>
                            </tr>
                            <tr>
                                <td class="right" title="<?php echo xla('Temporal Arteries'); ?>">
                                  <div class="kb kb_left">TA</div><?php echo xlt('Temp. Art.'); ?></td>
                                <td><input type="text" size="1" name="RTEMPART" id="RTEMPART" value="<?php echo attr($RTEMPART); ?>"></td>
                                <td><input type="text" size="1" name="LTEMPART" id="LTEMPART" value="<?php echo attr($LTEMPART); ?>"></td>
                            </tr>
                            <tr>
                                <td class="right" title="<?php echo xla('Cranial Nerve 5: Trigeminal Nerve'); ?>">
                                  <div class="kb kb_left">CN5</div><?php echo xlt('CN V'); ?></td>
                                <td><input type="text" size="1" name="RCNV" id="RCNV" value="<?php echo attr($RCNV); ?>"></td>
                                <td><input type="text" size="1" name="LCNV" id="LCNV" value="<?php echo attr($LCNV); ?>"></td>
                            </tr>
                            <tr>
                                <td class="right" title="<?php echo xla('Cranial Nerve 7: Facial Nerve'); ?>">
                                  <div class="kb kb_left">CN7</div><?php echo xlt('CN VII'); ?></td>
                                <td><input type="text" size="1" name="RCNVII" id="RCNVII" value="<?php echo attr($RCNVII); ?>"></td>
                                <td><input type="text" size="1" name="LCNVII" id="LCNVII" value="<?php echo attr($LCNVII); ?>"></td>
                            </tr>
                       
                            <tr><td colspan=3 style="padding-top:0.05in;text-decoration:underline;"><br /><?php echo xlt('Hertel Exophthalmometry'); ?></td></tr>
                            <tr style="text-align:center;">
                                <td>
                                    <input type="text" size="1" id="ODHERTEL" name="ODHERTEL" value="<?php echo attr($ODHERTEL); ?>">
                                    <i class="fa fa-minus"></i>
                                    <div class="kb kb_center">RH</div>
                                </td>
                                <td>
                                    <input type=text size=3  id="HERTELBASE" name="HERTELBASE" value="<?php echo attr($HERTELBASE); ?>">
                                    <i class="fa fa-minus"></i><div class="kb kb_center">HERT</div>
                                </td>
                                <td>
                                    <input type=text size=1  id="OSHERTEL" name="OSHERTEL" value="<?php echo attr($OSHERTEL); ?>">
                                    <div class="kb kb_center">LH</div>
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                          </table>
                    </div>

                    <?php ($EXT_VIEW ==1) ? ($display_EXT_view = "wide_textarea") : ($display_EXT_view= "narrow_textarea");?>                                 
                    <?php ($display_EXT_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                    <div id="EXT_text_list" name="EXT_text_list" class="borderShadow  <?php echo attr($display_EXT_view); ?>">
                        <span class="top_right fa <?php echo attr($marker); ?>" name="EXT_text_view" id="EXT_text_view"></span>
                        <table cellspacing="0" cellpadding="0">
                            <tr>
                                <th><?php echo xlt('Right'); ?></th><td style="width:100px;"></td><th><?php echo xlt('Left'); ?></th>
                            </tr>
                            <tr>
                                <td><textarea name="RBROW" id="RBROW" class="right "><?php echo text($RBROW); ?></textarea></td>
                                <td><div class="ident"><?php echo xlt('Brow'); ?></div>
                                    <div class="kb kb_left">RB</div><div class="kb kb_right">LB</div></td>
                                <td><textarea name="LBROW" id="LBROW" class=""><?php echo text($LBROW); ?></textarea></td>
                            </tr> 
                            <tr>
                                <td><textarea name="RUL" id="RUL" class="right"><?php echo text($RUL); ?></textarea></td>
                                <td><div class="ident"><?php echo xlt('Upper Lids'); ?></div>
                                      <div class="kb kb_left">RUL</div><div class="kb kb_right">LUL</div></td>
                                <td><textarea name="LUL" id="LUL" class=""><?php echo text($LUL); ?></textarea></td>
                            </tr> 
                            <tr>
                                <td><textarea name="RLL" id="RLL" class="right"><?php echo text($RLL); ?></textarea></td>
                                <td><div class="ident"><?php echo xlt('Lower Lids'); ?></div>
                                      <div class="kb kb_left">RLL</div><div class="kb kb_right">LLL</div></td>
                                <td><textarea name="LLL" id="LLL" class=""><?php echo text($LLL); ?></textarea></td>
                            </tr>
                            <tr>
                                <td><textarea name="RMCT" id="RMCT" class="right"><?php echo text($RMCT); ?></textarea></td>
                                <td><div class="ident"><?php echo xlt('Medial Canthi'); ?></div>
                                      <div class="kb kb_left">RMC</div><div class="kb kb_right">LMC</div></td>
                                <td><textarea name="LMCT" id="LMCT" class=""><?php echo text($LMCT); ?></textarea></td>
                            </tr>
                             <tr>
                                <td><textarea name="RADNEXA" id="RADNEXA" class="right"><?php echo text($RADNEXA); ?></textarea></td>
                                <td><div class="ident"><?php echo xlt('Adnexa'); ?></div>
                                      <div class="kb kb_left">RAD</div><div class="kb kb_right">LAD</div></td>
                                <td><textarea name="LADNEXA" id="LADNEXA" class=""><?php echo text($LADNEXA); ?></textarea></td>
                            </tr>
                        </table>
                    </div>  <br />
                    <div class="QP_lengthen" id="EXT_COMMENTS_DIV">
                      <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left">ECOM</div><br />
                      <textarea id="EXT_COMMENTS" name="EXT_COMMENTS"><?php echo text($EXT_COMMENTS); ?></textarea>
                    </div> 
                    <div class="QP_not nodisplay" id="EXT_keyboard_left" style="position: absolute;bottom:0.05in;right:0.1in;font-size:0.7em;text-align:left;padding-left:25px;">
                      <span id="EXT_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke"><b>Keyboard Entry</b></span>
                      &nbsp;<a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=ext');">
                        <i title="<?php echo xla('Click for External shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                        <textarea id="EXT_keyboard_left" name="EXT_keyboard_left" style="color:#0C0C0C;size:0.8em;height: 3em;" tabindex='1000'></textarea>
                    </div>
                  </div>  
                </div>
                
                <div id="EXT_right" name="EXT_right" class="exam_section_right borderShadow text_clinical">
                  <?php display_draw_section ("EXT",$encounter,$pid); ?>
                  <div id="PRIORS_EXT_left_text" style="height: 2.5in;text-align:left;" name="PRIORS_EXT_left_text" class="PRIORS_class PRIORS"> 
                      <i class="fa fa-spinner fa-spin"></i>
                  </div>
                  <div id="QP_EXT" name="QP_EXT" class="QP_class" style="text-align:left;max-height: 2.5in;">
                      <input type="hidden" id="EXT_prefix" name="EXT_prefix" value="<?php echo attr($EXT_prefix); ?>">
                       
                      <span class="closeButton fa fa-close pull-right z100" id="BUTTON_TEXTD_EXT" name="BUTTON_TEXTD_EXT" value="1"></span>
                      <div class="z10" style="position:relative;top:0.0in;left:0.00in;margin:auto;">
                          <span class="eye_button eye_button_selected" id="EXT_prefix_off" name="EXT_prefix_off"  onclick="$('#EXT_prefix').val('').trigger('change');;"><?php echo xlt('Off'); ?></span>
                          <span class="eye_button" id="EXT_defaults" name="EXT_defaults"><?php echo xlt('Defaults'); ?></span>  
                          <span class="eye_button" id="EXT_prefix_no" name="EXT_prefix_no" onclick="$('#EXT_prefix').val('no').trigger('change');"> <?php echo xlt('no'); ?> </span>  
                          <span class="eye_button" id="EXT_prefix_trace" name="EXT_prefix_trace"  onclick="$('#EXT_prefix').val('trace').trigger('change');"> <?php echo xlt('tr'); ?> </span>  
                          <span class="eye_button" id="EXT_prefix_1" name="EXT_prefix_1"  onclick="$('#EXT_prefix').val('+1').trigger('change');"> <?php echo xlt('+1'); ?> </span>  
                          <span class="eye_button" id="EXT_prefix_2" name="EXT_prefix_2"  onclick="$('#EXT_prefix').val('+2').trigger('change');"> <?php echo xlt('+2'); ?> </span>  
                          <span class="eye_button" id="EXT_prefix_3" name="EXT_prefix_3"  onclick="$('#EXT_prefix').val('+3').trigger('change');"> <?php echo xlt('+3'); ?> </span>  


                          <?php echo priors_select("EXT",$id,$id,$pid); ?>
      
                      </div>
                       <div style="float:left;width:40px;text-align:left;">
                          <span class="eye_button" id="EXT_prefix_1mm" name="EXT_prefix_1mm"  onclick="$('#EXT_prefix').val('1mm').trigger('change');"> <?php echo xlt('1mm'); ?> </span>  <br />
                          <span class="eye_button" id="EXT_prefix_2mm" name="EXT_prefix_2mm"  onclick="$('#EXT_prefix').val('2mm').trigger('change');"> <?php echo xlt('2mm'); ?> </span>  <br />
                          <span class="eye_button" id="EXT_prefix_3mm" name="EXT_prefix_3mm"  onclick="$('#EXT_prefix').val('3mm').trigger('change');"> <?php echo xlt('3mm'); ?> </span>  <br />
                          <span class="eye_button" id="EXT_prefix_4mm" name="EXT_prefix_4mm"  onclick="$('#EXT_prefix').val('4mm').trigger('change');"> <?php echo xlt('4mm'); ?> </span>  <br />
                          <span class="eye_button" id="EXT_prefix_5mm" name="EXT_prefix_5mm"  onclick="$('#EXT_prefix').val('5mm').trigger('change');"> <?php echo xlt('5mm'); ?> </span>  <br />
                          <span class="eye_button" id="EXT_prefix_medial" name="EXT_prefix_medial"  onclick="$('#EXT_prefix').val('medial').trigger('change');"><?php echo xlt('med'); ?></span>   
                          <span class="eye_button" id="EXT_prefix_lateral" name="EXT_prefix_lateral"  onclick="$('#EXT_prefix').val('lateral').trigger('change');"><?php echo xlt('lat'); ?></span>  
                          <span class="eye_button" id="EXT_prefix_superior" name="EXT_prefix_superior"  onclick="$('#EXT_prefix').val('superior').trigger('change');"><?php echo xlt('sup'); ?></span>  
                          <span class="eye_button" id="EXT_prefix_inferior" name="EXT_prefix_inferior"  onclick="$('#EXT_prefix').val('inferior').trigger('change');"><?php echo xlt('inf'); ?></span> 
                          <span class="eye_button" id="EXT_prefix_anterior" name="EXT_prefix_anterior"  onclick="$('#EXT_prefix').val('anterior').trigger('change');"><?php echo xlt('ant'); ?></span>  <br /> 
                          <span class="eye_button" id="EXT_prefix_mid" name="EXT_prefix_mid"  onclick="$('#EXT_prefix').val('mid').trigger('change');"><?php echo xlt('mid'); ?></span>  <br />
                          <span class="eye_button" id="EXT_prefix_posterior" name="EXT_prefix_posterior"  onclick="$('#EXT_prefix').val('posterior').trigger('change');"><?php echo xlt('post'); ?></span>  <br />
                          <span class="eye_button" id="EXT_prefix_deep" name="EXT_prefix_deep"  onclick="$('#EXT_prefix').val('deep').trigger('change');"><?php echo xlt('deep'); ?></span> 
                      </div>   
                           
                      <div id="EXT_QP_block1" name="EXT_QP_block1" class="QP_block borderShadow text_clinical" >
                          <?
                          $query = "SELECT * FROM form_eye_mag_prefs where PEZONE = 'EXT' and (id=?) ORDER BY id,ZONE_ORDER,ordering ASC";
                          $result = sqlStatement($query,array($_SESSION['authUserID']));
                          $number_rows=0;
                          if (sqlNumRows($result) < '10') { //for new users, use default quick picks until a pref list is developed
                            $query = "SELECT * FROM form_eye_mag_prefs where PEZONE = 'EXT' and (id='2048') ORDER BY id,ZONE_ORDER,ordering ASC";
                            $result = sqlStatement($query);
                          }
                          while ($Select_data= sqlFetchArray($result))   {
                              $number_rows++;             
                              $string = $Select_data['selection'] ;
                              $string = (strlen($string) > 14) ? substr($string,0,11).'...' : $string;         

                              ?>
                              <a class="underline QP" onclick="fill_QP_field('EXT','R','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('R'); ?></a> | 
                              <a class="underline QP" onclick="fill_QP_field('EXT','L','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('L'); ?></a> | 
                              <a class="underline QP" onclick="fill_QP_field('EXT','R','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');fill_QP_field('EXT','L','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('B'); ?></a> </span>
                              &nbsp;    <?php echo text($Select_data['LOCATION']); ?>: <?php echo text($string); ?>
                              <br />
                              <?
                              if ($number_rows==16) {
                                  ?>
                                   </div>
                                   <div id="EXT_QP_block2" name="EXT_QP_block2" class="QP_block_outer  borderShadow text_clinical" >
                                      <?
                              }
                              if ($number_rows==32) break;
                          }
                              ?>   
                      </div>      
                      <div class="QP_block_outer borderShadow nodisplay" style="z-index:1;text-align:center;border:1pt solid black;padding:7 10 7 10;font-weight:600;">
                        <span id="EXT1_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke">Keyboard Entry</span>&nbsp;<a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=ext');">
                        <i title="<?php echo xla('Click for External shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                        <textarea id="EXT_keyboard" name="EXT_keyboard" style="color:#0C0C0C;size:0.8em;height: 0.48in;" tabindex='1000'></textarea>
                        <span style="font-size:0.9em;font-weight:400;color:#0C0C0C;">Type: location.text; ENTER<br />
                        eg. right lower lid stye laterally:<br /> <b>RLL.stye lat;</b></span>
                        </span>
                      </div>            
                  </div>
              </div>
          </div>
            <!-- end External Exam -->

            <!-- start Anterior Segment -->
            <div id="ANTSEG_1" class="clear_both">
              <span class="anchor" id="ANTSEG_anchor"></span>
              <div id="ANTSEG_left" nam="ANTSEG_left" class="exam_section_left borderShadow">
                  <div class="TEXT_class" id="ANTSEG_left_text" style="height: 2.5in;text-align:left;">
                      <span class="closeButton fa fa-paint-brush" id="BUTTON_DRAW_ANTSEG" name="BUTTON_DRAW_ANTSEG"></span>
                      <i class="closeButton_2 fa fa-database" id="BUTTON_QP_ANTSEG" name="BUTTON_QP_ANTSEG"></i>
              
                      <b><?php echo xlt('Anterior Segment'); ?>:</b><br />
                      <div class="text_clinical" style="position:relative;float:right;top:0.2in;">
                        <table style="text-align:center;font-weight:600;font-size:0.7em;">
                          <?php 
                              list($imaging,$episode) = display($pid,$encounter, "ANTSEG"); 
                              echo $episode;
                          ?>
                        </table>
                          <table style="text-align:center;font-size:0.7em;font-weight:600;width:170px;padding-left:5px;"> 
                              <tr >
                                  <td></td><td><?php echo xlt('R'); ?></td><td><?php echo xlt('L'); ?></td>
                              </tr>
                              <tr>
                                  <td class="right" title="<?php echo xla('Gonioscopy'); ?>">
                                    <?php echo xlt('Gonio'); ?> </td>
                                  <td><input  type="text" class="" name="ODGONIO" id="ODGONIO" value="<?php echo attr($ODGONIO); ?>">
                                    <div class="kb kb_center">RG</div></td>
                                  <td><input  type="text" size="2" name="OSGONIO" id="OSGONIO" value="<?php echo attr($OSGONIO); ?>">
                                    <div class="kb kb_center">LG</div></td>
                              </tr>
                              <tr>
                                  <td class="right" title="<?php echo xla('Pachymetry: Central Corneal Thickness'); ?>">
                                    <?php echo xlt('Pachy'); ?> </td>
                                  <td><input type="text" size="1" name="ODKTHICKNESS" id="ODKTHICKNESS" value="<?php echo attr($ODKTHICKNESS); ?>">
                                  <div class="kb kb_center">RPACH</div></td>
                                  <td><input type="text" size="1" name="OSKTHICKNESS" id="OSKTHICKNESS" value="<?php echo attr($OSKTHICKNESS); ?>">
                                  <div class="kb kb_center">LPACH</div></td>
                              </tr>
                              <tr>
                                  <td class="right" title="<?php echo xla('Schirmers I (w/o anesthesia)'); ?>">
                                    <?php echo xlt('Schirmers I'); ?> </td>
                                  <td><input type="text" size="1" name="ODSCHIRMER1" id="ODSCHIRMER1" value="<?php echo attr($ODSCHIRMER1); ?>">
                                    <div class="kb kb_center">RSCH1</div></td>
                                  <td><input type="text" size="1" name="OSSCHIRMER1" id="OSSCHIRMER1" value="<?php echo attr($OSSCHIRMER1); ?>">
                                    <div class="kb kb_center">LSCH1</div></td>
                              </tr>
                               <tr>
                                  <td class="right" title="<?php echo xla('Schirmers II (w/ anesthesia)'); ?>">
                                    <?php echo xlt('Schirmers II'); ?> </td>
                                  <td><input type="text" size="1" name="ODSCHIRMER2" id="ODSCHIRMER2" value="<?php echo attr($ODSCHIRMER2); ?>">
                                  <div class="kb kb_center">RSCH2</div></td>
                                  <td><input type="text" size="1" name="OSSCHIRMER2" id="OSSCHIRMER2" value="<?php echo attr($OSSCHIRMER2); ?>">
                                  <div class="kb kb_center">LSCH2</div></td>
                              </tr>
                              <tr style="padding-bottom:15px;">
                                  <td class="right" title="<?php echo xla('Tear Break Up Time'); ?>">
                                    <?php echo xlt('TBUT'); ?> </td>
                                  <td><input type="text" size="1" name="ODTBUT" id="ODTBUT" value="<?php echo attr($ODTBUT); ?>">
                                  <div class="kb kb_center">RTBUT</div></td>
                                  <td><input type="text" size="1" name="OSTBUT" id="OSTBUT" value="<?php echo attr($OSTBUT); ?>">
                                    <div class="kb kb_center">LTBUT</div></td>
                              </tr>
                              <tr style="text-align:center;" >
                                <td colspan="3" rowspan="4" style="text-align:left;bottom:0px;width:75px;font-size:1.0em;"><b style="text-align:center;width:70px;text-decoration:underline;"><?php echo xlt('Dilated with'); ?>:</b><br />
                                  <table style="font-size:0.9em;">
                                    <tr>
                                      <td>
                                            <input type="checkbox" class="dil_drug" id="CycloMydril" name="CYCLOMYDRIL" value="Cyclomydril" <?php if ($CYCLOMYDRIL == 'Cyclomydril') echo "checked='checked'"; ?> />
                                            <label for="CycloMydril" class="input-helper input-helper--checkbox"><?php echo xlt('CycloMydril'); ?></label>
                                      </td>
                                      <td>        
                                            <input type="checkbox" class="dil_drug" id="Tropicamide" style="left:150px;" name="TROPICAMIDE" value="Tropicamide 2.5%" <?php if ($TROPICAMIDE == 'Tropicamide 2.5%') echo "checked='checked'"; ?> />
                                            <label for="Tropicamide" class="input-helper input-helper--checkbox"><?php echo xlt('Tropic 2.5%'); ?></label>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>        
                                            <input type="checkbox" class="dil_drug" id="Neo25" name="NEO25" value="Neosynephrine 2.5%"  <?php if ($NEO25 =='Neosynephrine 2.5%') echo "checked='checked'"; ?> />
                                            <label for="Neo25" class="input-helper input-helper--checkbox"><?php echo xlt('Neo 2.5%'); ?></label>
                                      </td>
                                      <td>        
                                            <input type="checkbox" class="dil_drug" id="Neo10" name="NEO10" value="Neosynephrine 10%"  <?php if ($NEO10 =='Neosynephrine 10%') echo "checked='checked'"; ?> />
                                            <label for="Neo10" class="input-helper input-helper--checkbox"><?php echo xlt('Neo 10%'); ?></label>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>        
                                            <input type="checkbox" class="dil_drug" id="Cyclogyl" style="left:150px;" name="CYCLOGYL" value="Cyclopentolate 1%"  <?php if ($CYCLOGYL == 'Cyclopentolate 1%') echo "checked='checked'"; ?> />
                                            <label for="Cyclogyl" class="input-helper input-helper--checkbox"><?php echo xlt('Cyclo 1%'); ?></label>
                                      </td>
                                      <td>      <input type="checkbox" class="dil_drug" id="Atropine" name="ATROPINE" value="Atropine 1%"  <?php if ($ATROPINE == 'Atropine 1%') echo "checked='checked'"; ?> />
                                            <label for="Atropine" class="input-helper input-helper--checkbox"><?php echo xlt('Atropine 1%'); ?></label>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                          </table>
                      </div>

                      <?php ($ANTSEG_VIEW =='1') ? ($display_ANTSEG_view = "wide_textarea") : ($display_ANTSEG_view= "narrow_textarea");?>
                      <?php ($display_ANTSEG_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                      <div id="ANTSEG_text_list"  name="ANTSEG_text_list" class="borderShadow <?php echo attr($display_ANTSEG_view); ?>" >
                              <span class="top_right fa <?php echo attr($marker); ?>" name="ANTSEG_text_view" id="ANTSEG_text_view"></span>
                              <table class="" style="" cellspacing="0" cellpadding="0">
                                  <tr>
                                      <th><?php echo xlt('OD'); ?></th><td style="width:100px;"></td><th><?php echo xlt('OS'); ?></th></td>
                                  </tr>
                                  <tr>
                                      <td>
                                        <textarea name="ODCONJ" id="ODCONJ" class=" right"><?php echo text($ODCONJ); ?></textarea></td>
                                      <td><div class="ident"><?php echo xlt('Conj'); ?> / <?php echo xlt('Sclera'); ?></div>
                                        <div class="kb kb_left">RC</div><div class="kb kb_right">LC</div></td>
                                      <td><textarea name="OSCONJ" id="OSCONJ" class=""><?php echo text($OSCONJ); ?></textarea></td>
                                  </tr> 
                                  <tr>
                                      <td><textarea name="ODCORNEA" id="ODCORNEA" class=" right"><?php echo text($ODCORNEA); ?></textarea></td>
                                      <td><div class="ident"><?php echo xlt('Cornea'); ?></div>
                                        <div class="kb kb_left">RK</div><div class="kb kb_right">LK</div></td></td>
                                      <td><textarea name="OSCORNEA" id="OSCORNEA" class=""><?php echo text($OSCORNEA); ?></textarea></td>
                                  </tr> 
                                  <tr>
                                      <td><textarea name="ODAC" id="ODAC" class=" right"><?php echo text($ODAC); ?></textarea></td>
                                      <td><div class="ident"><?php echo xlt('A/C'); ?></div>
                                        <div class="kb kb_left">RAC</div><div class="kb kb_right">LAC</div></td></td>
                                      <td><textarea name="OSAC" id="OSAC" class=""><?php echo text($OSAC); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODLENS" id="ODLENS" class=" right"><?php echo text($ODLENS); ?></textarea></td>
                                      <td><div class="ident dropShadow"><?php echo xlt('Lens'); ?></div>
                                        <div class="kb kb_left">RL</div><div class="kb kb_right">LL</div></td></td>
                                      <td><textarea name="OSLENS" id="OSLENS" class=""><?php echo text($OSLENS); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODIRIS" id="ODIRIS" class="right"><?php echo text($ODIRIS); ?></textarea></td>
                                      <td><div class="ident"><?php echo xlt('Iris'); ?></div>
                                        <div class="kb kb_left">RI</div><div class="kb kb_right">LI</div></td></td>
                                      <td><textarea name="OSIRIS" id="OSIRIS" class=""><?php echo text($OSIRIS); ?></textarea></td>
                                  </tr>
                              </table>
                      </div>  <br />
                      <div class="QP_lengthen" id="ANTSEG_COMMENTS_DIV"> 
                        <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left">ACOM</div><br />
                          <textarea id="ANTSEG_COMMENTS" name="ANTSEG_COMMENTS"><?php echo text($ANTSEG_COMMENTS); ?></textarea>
                      </div>   
                      <div class="QP_not nodisplay" id="ANTSEG_keyboard_left" style="position: absolute;bottom:0.05in;right:0.1in;font-size:0.7em;text-align:left;padding-left:25px;">
                      <span id="ANTSEG_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke"><b>Keyboard Entry</b></span>
                      &nbsp;<a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=antseg');">
                        <i title="<?php echo xla('Click for Anterior Segment shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                        <textarea id="ANTSEG_keyboard_left" name="EXT_keyboard_left" style="color:#0C0C0C;size:0.8em;height: 3em;" tabindex='1000'></textarea>
                    </div>
                  </div>  
              </div>
              
              <div id="ANTSEG_right" NAME=="ANTSEG_right" class="exam_section_right borderShadow text_clinical ">
                  <div id="PRIORS_ANTSEG_left_text" style="height: 2.5in;text-align:left;" name="PRIORS_ANTSEG_left_text" class="PRIORS_class PRIORS">                                     
                                  <i class="fa fa-spinner fa-spin"></i>
                  </div>
                  <?php display_draw_section ("ANTSEG",$encounter,$pid); ?>
                  <div id="QP_ANTSEG" name="QP_ANTSEG" class="QP_class"  style="text-align:left;height: 2.5in;">
                      <input type="hidden" id="ANTSEG_prefix" name="ANTSEG_prefix" value="">
                             
                      <div style="position:relative;top:0.0in;left:0.00in;margin: auto;">
                          <span  class="eye_button eye_button_selected" id="ANTSEG_prefix_off" name="ANTSEG_prefix_off"  onclick="$('#ANTSEG_prefix').val('off').trigger('change');"><?php echo xlt('Off'); ?> </span> 
                          <span  class="eye_button" id="ANTSEG_defaults" name="ANTSEG_defaults"><?php echo xlt('Defaults'); ?></span>  
                          <span  class="eye_button" id="ANTSEG_prefix_no" name="ANTSEG_prefix_no" onclick="$('#ANTSEG_prefix').val('no').trigger('change');"> <?php echo xlt('no'); ?> </span>  
                          <span  class="eye_button" id="ANTSEG_prefix_trace" name="ANTSEG_prefix_trace"  onclick="$('#ANTSEG_prefix').val('trace').trigger('change');"> <?php echo xlt('tr'); ?> </span>  
                          <span  class="eye_button" id="ANTSEG_prefix_1" name="ANTSEG_prefix_1"  onclick="$('#ANTSEG_prefix').val('+1').trigger('change');"> <?php echo xlt('+1'); ?> </span>  
                          <span  class="eye_button" id="ANTSEG_prefix_2" name="ANTSEG_prefix_2"  onclick="$('#ANTSEG_prefix').val('+2').trigger('change');"> <?php echo xlt('+2'); ?> </span>  
                          <span  class="eye_button" id="ANTSEG_prefix_3" name="ANTSEG_prefix_3"  onclick="$('#ANTSEG_prefix').val('+3').trigger('change');"> <?php echo xlt('+3'); ?> </span>  
                          <?php echo priors_select("ANTSEG",$id,$id,$pid); ?>
                      </div>
                      <div style="float:left;width:40px;text-align:left;">
                          <span  class="eye_button" id="ANTSEG_prefix_1mm" name="ANTSEG_prefix_1mm"  onclick="$('#ANTSEG_prefix').val('1mm').trigger('change');"> <?php echo xlt('1mm'); ?> </span>  <br />
                          <span  class="eye_button" id="ANTSEG_prefix_2mm" name="ANTSEG_prefix_2mm"  onclick="$('#ANTSEG_prefix').val('2mm').trigger('change');"> <?php echo xlt('2mm'); ?> </span>  <br />
                          <span  class="eye_button" id="ANTSEG_prefix_3mm" name="ANTSEG_prefix_3mm"  onclick="$('#ANTSEG_prefix').val('3mm').trigger('change');"> <?php echo xlt('3mm'); ?> </span>  <br />
                          <span  class="eye_button" id="ANTSEG_prefix_4mm" name="ANTSEG_prefix_4mm"  onclick="$('#ANTSEG_prefix').val('4mm').trigger('change');"> <?php echo xlt('4mm'); ?> </span>  <br />
                          <span  class="eye_button" id="ANTSEG_prefix_5mm" name="ANTSEG_prefix_5mm"  onclick="$('#ANTSEG_prefix').val('5mm').trigger('change');"> <?php echo xlt('5mm'); ?> </span>  <br />
                          <span  class="eye_button" id="ANTSEG_prefix_medial" name="ANTSEG_prefix_medial"  onclick="$('#ANTSEG_prefix').val('medial').trigger('change');"><?php echo xlt('med'); ?></span>   
                          <span  class="eye_button" id="ANTSEG_prefix_lateral" name="ANTSEG_prefix_lateral"  onclick="$('#ANTSEG_prefix').val('lateral').trigger('change');"><?php echo xlt('lat'); ?></span>  
                          <span  class="eye_button" id="ANTSEG_prefix_superior" name="ANTSEG_prefix_superior"  onclick="$('#ANTSEG_prefix').val('superior').trigger('change');"><?php echo xlt('sup'); ?></span>  
                          <span  class="eye_button" id="ANTSEG_prefix_inferior" name="ANTSEG_prefix_inferior"  onclick="$('#ANTSEG_prefix').val('inferior').trigger('change');"><?php echo xlt('inf'); ?></span> 
                          <span  class="eye_button" id="ANTSEG_prefix_anterior" name="ANTSEG_prefix_anterior"  onclick="$('#ANTSEG_prefix').val('anterior').trigger('change');"><?php echo xlt('ant'); ?></span>  <br /> 
                          <span  class="eye_button" id="ANTSEG_prefix_mid" name="ANTSEG_prefix_mid"  onclick="$('#ANTSEG_prefix').val('mid').trigger('change');"><?php echo xlt('mid'); ?></span>  <br />
                          <span  class="eye_button" id="ANTSEG_prefix_posterior" name="ANTSEG_prefix_posterior"  onclick="$('#ANTSEG_prefix').val('posterior').trigger('change');"><?php echo xlt('post'); ?></span>  <br />
                          <span  class="eye_button" id="ANTSEG_prefix_deep" name="ANTSEG_prefix_deep"  onclick="$('#ANTSEG_prefix').val('deep').trigger('change');"><?php echo xlt('deep'); ?></span> 
                      </div>         
                      <div class="QP_block borderShadow text_clinical " >
                         <?
                                  $query = "SELECT * FROM form_eye_mag_prefs where PEZONE = 'ANTSEG' and (id=?) ORDER BY ZONE_ORDER,ordering";
                                  $result = sqlStatement($query,array($_SESSION['authUserID']));
                                  if (sqlNumRows($result) < '10') {
                                      $query = "SELECT * FROM form_eye_mag_prefs where PEZONE = 'ANTSEG' and (id='2048') ORDER BY id,ZONE_ORDER,ordering ASC";
                                      $result = sqlStatement($query);
                                    }
                                  $number_rows=0;
                                  while ($Select_data= sqlFetchArray($result))   {
                              $number_rows++;
                              $string = $Select_data['selection'] ;
                              $string = (strlen($string) > 12) ? substr($string,0,10).'...' : $string;   
                              ?> <span>
                              <a class="underline QP" onclick="fill_QP_field('ANTSEG','OD','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('OD'); ?></a> | 
                              <a class="underline QP" onclick="fill_QP_field('ANTSEG','OS','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('OS'); ?></a> | 
                              <a class="underline QP" onclick="fill_QP_field('ANTSEG','OD','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');fill_QP_field('ANTSEG','OS','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('OU'); ?></a> </span>
                              &nbsp;    <?php echo text($Select_data['LOCATION']); ?>: <?php echo text($string); ?>

                              <br />
                              <?php if ($number_rows==16) {  ?>
                                  </div>
                                  <div class="QP_block_outer  borderShadow text_clinical" ><?php  
                                  }  if ($number_rows == 32) break;
                              } 
                                  ?>      
                      </div>  
                      <div class="QP_block_outer borderShadow nodisplay" style="z-index:1;text-align:center;border:1pt solid black;padding:7 10 7 10;font-weight:600;">
                        <span id="ANTSEG1_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke">Keyboard Entry</span>
                        &nbsp;
                        <a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=antseg');" title="<?php echo xla('Click for Ant. Seg. shorthand help.'); ?>">
                          <i class="fa fa-info-circle fa-1"></i></a><br />
                        <textarea id="ANTSEG_keyboard" name="ANTSEG_keyboard" style="color:#0C0C0C;size:0.8em;height: 0.48in;" tabindex='1000'></textarea>
                        <span style="font-size:0.9em;font-weight:400;color:#0C0C0C;">Type: location.text; ENTER<br />
                        eg. OU cornea +2 kruckenberg spindle:<br /> <b>k.+2 ks;</b>
                        </span>
                      </div>  
                      <span class="closeButton fa fa-close pull-right z100" id="BUTTON_TEXTD_ANTSEG" name="BUTTON_TEXTD_ANTSEG" value="1"></span>
                      
                  </div>
              </div>
            </div>
            <!-- end Ant Seg -->
                   
            <!-- start Retina -->               
            <div id="RETINA_1" class="clear_both" >
              <span class="anchor" id="RETINA_anchor"></span>

                <div id="RETINA_left" class="exam_section_left borderShadow">
                    <div class="TEXT_class" id="RETINA_left_text" name="RETINA_left_text" style="height: 2.5in;text-align:left;"> 
                      <!-- 
                      <span class="closeButton fa fa-plus-square-o" id="MAX_RETINA" name="MAX_RETINA"></span>
                      -->
                      <span class="closeButton fa fa-paint-brush" id="BUTTON_DRAW_RETINA" name="BUTTON_DRAW_RETINA"></span>
                      <i class="closeButton_2 fa fa-database" id="BUTTON_QP_RETINA" name="BUTTON_QP_RETINA"></i>
                
                        <b><?php echo xlt('Retina'); ?>:</b>
                              <?
                                /*
                                      OCT, FA/ICG,Photos - External,Photos - AntSeg,Optic Disc,Photos - Retina,Radiology, VF
                                      are the Imaging categories we started with.  If you add more they are listed
                                      Here in retina we want to see:
                                      OCT, FA/ICG, Optic Disc, Fundus Photos, Electrophys
                                      for viewing images, if (count($category['OCT']) >0) show image and href= a popupform to display all the results
                                      build a get string for this:
                                      for ($i=0; $i < count($category['OCT']); $i++) {
                                        $get .= $category['OCT'][$i]."%20".
                                      }
                                      $href="/eye_mag/imaging.php?display=".$get;
                                */
                                      
                                        ?>
                                        <input style="margin-left:100px;" type="checkbox" id="DIL_RISKS" name="DIL_RISKS" value="on" <?php if ($DIL_RISKS =='on') echo "checked='checked'"; ?>>
                                            <label for="DIL_RISKS" class="input-helper input-helper--checkbox"><?php echo xlt('Dilation orders/risks reviewed'); ?></label>
                                        <?
                                      
                                      ?><br />

                        <div style="position:relative;float:right;top:0.2in;border:0pt solid black;">
                            <table style="float:right;text-align:right;font-size:0.8em;font-weight:bold;">
                              <?php 
                                list($imaging,$episode) = display($pid,$encounter, "POSTSEG"); 
                                echo $episode;
                              ?>
                            </table>
                            <br />
                            <table style="width:50%;text-align:right;font-size:0.8em;font-weight:bold;padding:10px;margin: 5px 0px;">
                                <tr style="text-align:center;text-decoration:underline;">
                                    <td></td>
                                    <td> <br /><?php echo xlt('OD'); ?></td><td> <br /><?php echo xlt('OS'); ?></td>
                                  </tr>
                                  <tr>
                                    <td>
                                        <span id="CMT" name="CMT" title="Central Macular Thickness"><?php echo xlt('CMT'); ?>:</span>
                                    </td>
                                    <td>
                                        <input name="ODCMT" size="4" id="ODCMT" value="<?php echo attr($ODCMT); ?>">
                                        <div class="kb kb_center">RCMT</div>
                                    </td>
                                    <td>
                                        <input name="OSCMT" size="4" id="OSCMT" value="<?php echo attr($OSCMT); ?>">
                                        <div class="kb kb_center">LCMT</div>
                                    </td>
                                </tr>
                            </table>
                            <br />
                            <table style="float:right;text-align:right;font-size:0.8em;font-weight:bold;Xpadding:0px 0px 5px 10px;">
                              <?php 
                                list($imaging,$episode) = display($pid,$encounter, "NEURO"); 
                                echo $episode;
                              ?>
                            </table>
                        </div>

                        <?php ($RETINA_VIEW ==1) ? ($display_RETINA_view = "wide_textarea") : ($display_RETINA_view= "narrow_textarea");?>
                        <?php ($display_RETINA_view == "wide_textarea") ? ($marker ="fa-minus-square-o") : ($marker ="fa-plus-square-o");?>
                        <div>
                            <div id="RETINA_text_list" name="RETINA_text_list" class="borderShadow  <?php echo attr($display_RETINA_view); ?>">
                                <span class="top_right fa <?php echo attr($marker); ?>" name="RETINA_text_view" id="RETINA_text_view"></span>
                                <table  cellspacing="0" cellpadding="0">
                                        <tr>
                                            <th><?php echo xlt('OD'); ?></th><td style="width:100px;"></td><th><?php echo xlt('OS'); ?></th></td>
                                        </tr>
                                        <tr>
                                            <td><textarea name="ODDISC" id="ODDISC" class="right"><?php echo text($ODDISC); ?></textarea></td>
                                            <td><div class="ident"><?php echo xlt('Disc'); ?></div>
                                          <div class="kb kb_left">RD</div><div class="kb kb_right">LD</div></td>
                                            <td><textarea name="OSDISC" id="OSDISC" class=""><?php echo text($OSDISC); ?></textarea></td>
                                        </tr> 
                                        <tr>
                                            <td><textarea name="ODCUP" id="ODCUP" class="right"><?php echo text($ODCUP); ?></textarea></td>
                                            <td><div class="ident"><?php echo xlt('Cup'); ?></div>
                                          <div class="kb kb_left">RCUP</div><div class="kb kb_right">LCUP</div></td>
                                            <td><textarea name="OSCUP" id="OSCUP" class=""><?php echo text($OSCUP); ?></textarea></td>
                                        </tr> 
                                        <tr>
                                            <td><textarea name="ODMACULA" id="ODMACULA" class="right"><?php echo text($ODMACULA); ?></textarea></td>
                                            <td><div class="ident"><?php echo xlt('Macula'); ?></div>
                                          <div class="kb kb_left">RMAC</div><div class="kb kb_right">LMAC</div></td>
                                            <td><textarea name="OSMACULA" id="OSMACULA" class=""><?php echo text($OSMACULA); ?></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><textarea name="ODVESSELS" id="ODVESSELS" class="right"><?php echo text($ODVESSELS); ?></textarea></td>
                                            <td><div class="ident"><?php echo xlt('Vessels'); ?></div>
                                          <div class="kb kb_left">RV</div><div class="kb kb_right">LV</div></td>
                                            <td><textarea name="OSVESSELS" id="OSVESSELS" class=""><?php echo text($OSVESSELS); ?></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><textarea name="ODPERIPH" id="ODPERIPH" class="right"><?php echo text($ODPERIPH); ?></textarea></td>
                                            <td><div class="ident"><?php echo xlt('Periph'); ?></div>
                                          <div class="kb kb_left">RP</div><div class="kb kb_right">LP</div></td>
                                            <td><textarea name="OSPERIPH" id="OSPERIPH" class=""><?php echo text($OSPERIPH); ?></textarea></td>
                                        </tr>
                                </table>
                            </div>
                        </div>
                        <div class="QP_lengthen" id="RETINA_COMMENTS_DIV">
                            <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left">RCOM</div><br />
                            <textarea id="RETINA_COMMENTS" name="RETINA_COMMENTS"><?php echo text($RETINA_COMMENTS); ?></textarea>
                        </div>  
                        <div class="QP_not" id="RETINA_keyboard_left" style="position: absolute;bottom:0.05in;right:0.1in;font-size:0.7em;text-align:left;padding-left:25px;">
                          <span id="RETINA_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke"><b>Keyboard Entry</b></span>
                          &nbsp;<a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=retina');">
                          <i title="<?php echo xla('Click for Retina shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                          <textarea id="RETINA_keyboard_left" name="RETINA_keyboard_left" style="color:#0C0C0C;size:0.8em;height: 3em;" tabindex='1000'></textarea>
                      </div>
                    </div>
                </div>
                
                <div id="RETINA_right" class="exam_section_right borderShadow text_clinical">
                  <div id="PRIORS_RETINA_left_text" style="height: 2.5in;text-align:left;" 
                       name="PRIORS_RETINA_left_text" 
                       class="PRIORS_class PRIORS"><i class="fa fa-spinner fa-spin"></i>
                  </div>
                  <?php display_draw_section ("RETINA",$encounter,$pid); ?>
                  <div id="QP_RETINA" name="QP_RETINA" class="QP_class" style="text-align:left;height: 2.5in;">
                    <input type="hidden" id="RETINA_prefix" name="RETINA_prefix" value="" />
                           
                    <div style="position:relative;top:0.0in;left:0.00in;margin: auto;">
                         <span  class="eye_button  eye_button_selected" id="RETINA_prefix_off" name="RETINA_prefix_off"  onclick="$('#RETINA_prefix').val('').trigger('change');"><?php echo xlt('Off'); ?></span> 
                         <span  class="eye_button" id="RETINA_defaults" name="RETINA_defaults"><?php echo xlt('Defaults'); ?></span>  
                         <span  class="eye_button" id="RETINA_prefix_no" name="RETINA_prefix_no" onclick="$('#RETINA_prefix').val('no').trigger('change');"> <?php echo xlt('no'); ?> </span>  
                         <span  class="eye_button" id="RETINA_prefix_trace" name="RETINA_prefix_trace"  onclick="$('#RETINA_prefix').val('trace').trigger('change');"> <?php echo xlt('tr'); ?> </span>  
                         <span  class="eye_button" id="RETINA_prefix_1" name="RETINA_prefix_1"  onclick="$('#RETINA_prefix').val('+1').trigger('change');"> <?php echo xlt('+1'); ?> </span>  
                         <span  class="eye_button" id="RETINA_prefix_2" name="RETINA_prefix_2"  onclick="$('#RETINA_prefix').val('+2').trigger('change');"> <?php echo xlt('+2'); ?> </span>  
                         <span  class="eye_button" id="RETINA_prefix_3" name="RETINA_prefix_3"  onclick="$('#RETINA_prefix').val('+3').trigger('change');"> <?php echo xlt('+3'); ?> </span>  
                         <?php echo priors_select("RETINA",$id,$id,$pid); ?>
                    </div>
                    <div style="float:left;width:40px;text-align:left;">

                        <span  class="eye_button" id="RETINA_prefix_1mm" name="RETINA_prefix_1mm"  onclick="$('#RETINA_prefix').val('1mm').trigger('change');"> <?php echo xlt('1mm'); ?> </span>  <br />
                        <span  class="eye_button" id="RETINA_prefix_2mm" name="RETINA_prefix_2mm"  onclick="$('#RETINA_prefix').val('2mm').trigger('change');"> <?php echo xlt('2mm'); ?> </span>  <br />
                        <span  class="eye_button" id="RETINA_prefix_3mm" name="RETINA_prefix_3mm"  onclick="$('#RETINA_prefix').val('3mm').trigger('change');"> <?php echo xlt('3mm'); ?> </span>  <br />
                        <span  class="eye_button" id="RETINA_prefix_4mm" name="RETINA_prefix_4mm"  onclick="$('#RETINA_prefix').val('4mm').trigger('change');"> <?php echo xlt('4mm'); ?> </span>  <br />
                        <span  class="eye_button" id="RETINA_prefix_5mm" name="RETINA_prefix_5mm"  onclick="$('#RETINA_prefix').val('5mm').trigger('change');"> <?php echo xlt('5mm'); ?> </span>  <br />
                        <span  class="eye_button" id="RETINA_prefix_nasal" name="RETINA_prefix_nasal"  onclick="$('#RETINA_prefix').val('nasal).trigger('change');"><?php echo xlt('nasal'); ?></span>   
                        <span  class="eye_button" id="RETINA_prefix_temp" name="RETINA_prefix_temp"  onclick="$('#RETINA_prefix').val('temp').trigger('change');"><?php echo xlt('temp'); ?></span>  
                        <span  class="eye_button" id="RETINA_prefix_superior" name="RETINA_prefix_superior"  onclick="$('#RETINA_prefix').val('superior').trigger('change');"><?php echo xlt('sup'); ?></span>  
                        <span  class="eye_button" id="RETINA_prefix_inferior" name="RETINA_prefix_inferior"  onclick="$('#RETINA_prefix').val('inferior').trigger('change');"><?php echo xlt('inf'); ?></span> 
                        <span  class="eye_button" id="RETINA_prefix_anterior" name="RETINA_prefix_anterior"  onclick="$('#RETINA_prefix').val('anterior').trigger('change');"><?php echo xlt('ant'); ?></span>  <br /> 
                        <span  class="eye_button" id="RETINA_prefix_mid" name="RETINA_prefix_mid"  onclick="$('#RETINA_prefix').val('mid').trigger('change');"><?php echo xlt('mid'); ?></span>  <br />
                        <span  class="eye_button" id="RETINA_prefix_posterior" name="RETINA_prefix_posterior"  onclick="$('#RETINA_prefix').val('posterior').trigger('change');"><?php echo xlt('post'); ?></span>  <br />
                        <span  class="eye_button" id="RETINA_prefix_deep" name="RETINA_prefix_deep"  onclick="$('#RETINA_prefix').val('deep').trigger('change');"><?php echo xlt('deep'); ?></span> 
                    </div>         
                    <div class="QP_block borderShadow text_clinical" >
                        <?php
                                $query = "SELECT * FROM form_eye_mag_prefs where PEZONE = 'RETINA' and (id=?) ORDER BY ZONE_ORDER,ordering";
                                $result = sqlStatement($query,array($_SESSION['authUserID']));
                                if (sqlNumRows($result) < '10') {
                                      $query = "SELECT * FROM form_eye_mag_prefs where PEZONE = 'RETINA' and (id='2048') ORDER BY id,ZONE_ORDER,ordering ASC";
                                      $result = sqlStatement($query);
                                    }
                                    $number_rows=0;
                                while ($Select_data= sqlFetchArray($result))     {

                            $number_rows++; 
                            $string = $Select_data['selection'] ;
                            $string = (strlen($string) > 12) ? substr($string,0,12).'...' : $string;   ?>
                        <span>
                            <a class="underline QP" onclick="fill_QP_field('RETINA','OD','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('OD'); ?></a>&nbsp;|&nbsp;
                            <a class="underline QP" onclick="fill_QP_field('RETINA','OS','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('OS'); ?></a>&nbsp;|&nbsp;
                            <a class="underline QP" onclick="fill_QP_field('RETINA','OD','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');fill_QP_field('RETINA','OS','<?php echo attr($Select_data['LOCATION_text']); ?>','<?php echo attr($Select_data['selection']); ?>','<?php echo attr($Select_data['FILL_ACTION']); ?>');"><?php echo xlt('OU'); ?></a>
                            &nbsp;    <?php echo text($Select_data['LOCATION']); ?>: <?php echo text($string); ?>

                            <br />
                            <?
                            if ($number_rows=='16') {
                                ?>
                            </div>
                            <div class="QP_block_outer  borderShadow text_clinical" ><?php 
                        }
                        if ($number_rows == 32) break;

                        } ?>     
                          </span>
                    </div>
                    <div class="QP_block_outer borderShadow nodisplay" style="z-index:1;text-align:center;border:1pt solid black;padding:7 10 7 10;font-weight:600;">
                      <span id="RETINA_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke">Keyboard Entry</span>&nbsp;
                      <a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=retina');">
                      <i title="<?php echo xla('Click for Ant. Seg. shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                      <textarea id="RETINA_keyboard" name="RETINA_keyboard" style="color:#0C0C0C;size:0.8em;height: 0.48in;" tabindex='1000'></textarea>
                      <span style="font-size:0.9em;font-weight:400;color:#0C0C0C;">Type: location.text; ENTER<br />
                      eg. OD C/D 0.5 with inferior notch:<br /> <b>RC.0.5 w/ inf notch;</b>
                      </span>
                    </div>  
                    <span class="closeButton fa fa-close pull-right z100" id="BUTTON_TEXTD_RETINA" name="BUTTON_TEXTD_RETINA" value="1"></span>
                  </div>
                </div>
            </div>
            <!-- end Retina -->

            <!-- start Neuro -->
            <div id="NEURO_1" class="clear_both">
             <span class="anchor" id="NEURO_anchor"></span>

                <div id="NEURO_left" class="exam_section_left borderShadow">
                    <span class="closeButton fa fa-paint-brush" id="BUTTON_DRAW_NEURO" name="BUTTON_DRAW_NEURO"></span>
                    <i class="closeButton_2 fa fa-database" id="BUTTON_QP_NEURO" name="BUTTON_QP_NEURO"></i>
                    <div class="TEXT_class" id="NEURO_left_text" name="NEURO_left_text" style="margin:auto 5;min-height: 2.5in;text-align:left;">
                        <b><?php echo xlt('Neuro'); ?>:</b>
                        <div style="float:left;font-size:0.9em;margin-top:8px;">
                            <div id="NEURO_text_list" class="borderShadow" style="border:1pt solid black;float:left;width:175px;padding:10px;text-align:center;margin:2 2;font-weight:bold;">
                                <table style="font-size:1.1em;font-weight:600;">
                                    <tr>
                                        <td></td><td style="text-align:center;"><?php echo xlt('OD'); ?></td><td style="text-align:center;"><?php echo xlt('OS'); ?></td></tr>
                                    <tr>
                                        <td class="right">
                                            <?php echo xlt('Color'); ?>: 
                                        </td>
                                        <td>
                                            <input type="text"  name="ODCOLOR" id="ODCOLOR" value="<?php if ($ODCOLOR) { echo  $ODCOLOR; } else { echo ""; } ?>"/>
                                        </td>
                                        <td>
                                            <input type="text" name="OSCOLOR" id="OSCOLOR" value="<?php if ($OSCOLOR) { echo  $OSCOLOR; } else { echo ""; } ?>"/>
                                        </td>
                                        <td><!-- //Normals may be 11/11 or 15/15.  Need to make a preference here for the user.
                                            //or just take the normal they use and incorporate that ongoing?
                                        -->
                                           <span title="<?php echo xlt('Insert normals'); ?> - 11/11" class="fa fa-share-square-o fa-flip-horizontal" id="NEURO_COLOR" name="NEURO_COLOR"></span>
                                        &nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="right" style="white-space: nowrap;font-size:0.9em;">
                                            <span title="Variation in red color discrimination between the eyes (eg. OD=100, OS=75)"><?php echo xlt('Red Desat'); ?>:</span>
                                        </td>
                                        <td>
                                            <input type="text" Xsize="6" name="ODREDDESAT" id="ODREDDESAT" value="<?php echo attr($ODREDDESAT); ?>"/> 
                                        </td>
                                        <td>
                                            <input type="text" Xsize="6" name="OSREDDESAT" id="OSREDDESAT" value="<?php echo attr($OSREDDESAT); ?>"/>
                                        </td>
                                        <td>
                                           <span title="Insert normals - 100/100" class="fa fa-share-square-o fa-flip-horizontal" id="NEURO_REDDESAT" name="NEURO_REDDESAT"></span>
                                        &nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="right" style="white-space: nowrap;">
                                            <span title="<?php echo xlt('Variation in white (muscle) light brightness discrimination between the eyes (eg. OD=$1.00, OS=$0.75)'); ?>"><?php echo xlt('Coins'); ?>:</span>
                                        </td>
                                        <td>
                                            <input type="text" Xsize="6" name="ODCOINS" id="ODCOINS" value="<?php echo attr($ODCOINS); ?>"/> 
                                        </td>
                                        <td>
                                            <input type="text" Xsize="6" name="OSCOINS" id="OSCOINS" value="<?php echo attr($OSCOINS); ?>"/>
                                        </td>
                                        <td>
                                           <span title="<?php echo xlt('Insert normals - 100/100'); ?>" class="fa fa-share-square-o fa-flip-horizontal" id="NEURO_COINS" name="NEURO_COINS"></span>
                                        &nbsp;</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="borderShadow" style="position:relative;float:right;text-align:center;width:238px;height:239px;z-index:1;margin:2 0 2 2;">                               
                                <i class="fa fa-th fa-fw closeButton " id="Close_ACTMAIN" style="right:0.15in;" name="Close_ACTMAIN"></i>
                                <table style="position:relative;float:left;font-size:1.2em;width:210px;font-weight:600;"> 
                                    <tr style="text-align:left;height:26px;vertical-align:middle;width:180px;">
                                        <td >
                                            <span id="ACTTRIGGER" name="ACTTRIGGER" style="text-decoration:underline;padding-left:2px;"><?php echo xlt('Alternate Cover Test'); ?>:</span>
                                        </td>
                                        <td>
                                            <span id="ACTNORMAL_CHECK" name="ACTNORMAL_CHECK">
                                            <label for="ACT" class="input-helper input-helper--checkbox"><?php echo xlt('Ortho'); ?></label>
                                            <input type="checkbox" name="ACT" id="ACT" <?php if ($ACT =='on') echo "checked='checked'"; ?> /></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:center;"> 
                                            <div id="ACTMAIN" name="ACTMAIN" class="nodisplay ACT_TEXT" style="position:relative;z-index:1;margin auto;">
                                              <table cellpadding="0" style="position:relative;text-align:center;font-size:1.0em;margin: 7 5 10 5;border-collapse: separate;">
                                                    <tr>
                                                        <td id="ACT_tab_SCDIST" name="ACT_tab_SCDIST" class="ACT_selected"> <?php echo xlt('scDist'); ?> </td>
                                                        <td id="ACT_tab_CCDIST" name="ACT_tab_CCDIST" class="ACT_deselected"> <?php echo xlt('ccDist'); ?> </td>
                                                        <td id="ACT_tab_SCNEAR" name="ACT_tab_SCNEAR" class="ACT_deselected"> <?php echo xlt('scNear'); ?> </td>
                                                        <td id="ACT_tab_CCNEAR" name="ACT_tab_CCNEAR" class="ACT_deselected"> <?php echo xlt('ccNear'); ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4" style="text-align:center;font-size:0.8em;"><div id="ACT_SCDIST" name="ACT_SCDIST" class="ACT_box">
                                                            <br />
                                                            <table> 
                                                                    <tr> 
                                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>   
                                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                        <textarea id="ACT1SCDIST" name="ACT1SCDIST" class="ACT"><?php echo text($ACT1SCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                        <textarea id="ACT2SCDIST"  name="ACT2SCDIST"class="ACT"><?php echo text($ACT2SCDIST); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                        <textarea id="ACT3SCDIST"  name="ACT3SCDIST" class="ACT"><?php echo text($ACT3SCDIST); ?></textarea></td>
                                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td> 
                                                                    </tr>
                                                                    <tr>    
                                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left"></i></td> 
                                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                        <textarea id="ACT4SCDIST" name="ACT4SCDIST" class="ACT"><?php echo text($ACT4SCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;text-align:center;">
                                                                        <textarea id="ACT5SCDIST"  class="neurosens2 ACT" name="ACT5SCDIST"><?php echo text($ACT5SCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                        <textarea id="ACT6SCDIST" name="ACT6SCDIST" class="ACT"><?php echo text($ACT6SCDIST); ?></textarea></td>
                                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                                    </tr> 
                                                                    <tr> 
                                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                            <textarea id="ACT10SCDIST" name="ACT10SCDIST" class="ACT"><?php echo text($ACT10SCDIST); ?></textarea></td>
                                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                            <textarea id="ACT7SCDIST" name="ACT7SCDIST" class="ACT"><?php echo text($ACT7SCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                            <textarea id="ACT8SCDIST" name="ACT8SCDIST" class="ACT"><?php echo text($ACT8SCDIST); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                            <textarea id="ACT9SCDIST" name="ACT9SCDIST" class="ACT"><?php echo text($ACT9SCDIST); ?></textarea></td>
                                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                            <textarea id="ACT11SCDIST" name="ACT11SCDIST" class="ACT"><?php echo text($ACT11SCDIST); ?></textarea>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <br />
                                                            </div>
                                                            <div id="ACT_CCDIST" name="ACT_CCDIST" class="nodisplay ACT_box">
                                                                <br />
                                                                <table> 
                                                                   <tr> 
                                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>   
                                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                        <textarea id="ACT1CCDIST" name="ACT1CCDIST" class="ACT"><?php echo text($ACT1CCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                        <textarea id="ACT2CCDIST"  name="ACT2CCDIST"class="ACT"><?php echo text($ACT2CCDIST); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                        <textarea id="ACT3CCDIST"  name="ACT3CCDIST" class="ACT"><?php echo text($ACT3CCDIST); ?></textarea></td>
                                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td> 
                                                                    </tr>
                                                                    <tr>    
                                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left"></i></td> 
                                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                        <textarea id="ACT4CCDIST" name="ACT4CCDIST" class="ACT"><?php echo text($ACT4CCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;text-align:center;">
                                                                        <textarea id="ACT5CCDIST" name="ACT5CCDIST" class="neurosens2 ACT"><?php echo text($ACT5CCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                        <textarea id="ACT6CCDIST" name="ACT6CCDIST" class="ACT"><?php echo text($ACT6CCDIST); ?></textarea></td>
                                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                                    </tr> 
                                                                    <tr> 
                                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                            <textarea id="ACT10CCDIST" name="ACT10CCDIST" class="ACT"><?php echo text($ACT10CCDIST); ?></textarea></td>
                                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                            <textarea id="ACT7CCDIST" name="ACT7CCDIST" class="ACT"><?php echo text($ACT7CCDIST); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                            <textarea id="ACT8CCDIST" name="ACT8CCDIST" class="ACT"><?php echo text($ACT8CCDIST); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                            <textarea id="ACT9CCDIST" name="ACT9CCDIST" class="ACT"><?php echo text($ACT9CCDIST); ?></textarea></td>
                                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                            <textarea id="ACT11CCDIST" name="ACT11CCDIST" class="ACT"><?php echo text($ACT11CCDIST); ?></textarea>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <br />
                                                            </div>
                                                            <div id="ACT_SCNEAR" name="ACT_SCNEAR" class="nodisplay ACT_box">
                                                                <br />
                                                                <table> 
                                                                    <tr> 
                                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>    
                                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                        <textarea id="ACT1SCNEAR" name="ACT1SCNEAR" class="ACT"><?php echo text($ACT1SCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                        <textarea id="ACT2SCNEAR"  name="ACT2SCNEAR"class="ACT"><?php echo text($ACT2SCNEAR); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                        <textarea id="ACT3SCNEAR"  name="ACT3SCNEAR" class="ACT"><?php echo text($ACT3SCNEAR); ?></textarea></td>
                                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td> 
                                                                    </tr>
                                                                    <tr>    
                                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left"></i></td> 
                                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                        <textarea id="ACT4SCNEAR" name="ACT4SCNEAR" class="ACT"><?php echo text($ACT4SCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;text-align:center;">
                                                                        <textarea id="ACT5SCNEAR" name="ACT5SCNEAR" class="neurosens2 ACT"><?php echo text($ACT5SCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                        <textarea id="ACT6SCNEAR" name="ACT6SCNEAR" class="ACT"><?php echo text($ACT6SCNEAR); ?></textarea></td>
                                                                        <td><i class="fa fa-share rotate-right"></i></td> 
                                                                    </tr> 
                                                                    <tr> 
                                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                            <textarea id="ACT10SCNEAR" name="ACT10SCNEAR" class="ACT"><?php echo text($ACT10SCNEAR); ?></textarea></td>
                                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                            <textarea id="ACT7SCNEAR" name="ACT7SCNEAR" class="ACT"><?php echo text($ACT7SCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                            <textarea id="ACT8SCNEAR" name="ACT8SCNEAR" class="ACT"><?php echo text($ACT8SCNEAR); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                            <textarea id="ACT9SCNEAR" name="ACT9SCNEAR" class="ACT"><?php echo text($ACT9SCNEAR); ?></textarea></td>
                                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                            <textarea id="ACT11SCNEAR" name="ACT11SCNEAR" class="ACT"><?php echo text($ACT11SCNEAR); ?></textarea>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <br />
                                                            </div>
                                                            <div id="ACT_CCNEAR" name="ACT_CCNEAR" class="nodisplay ACT_box">
                                                                <br />
                                                                <table> 
                                                                    <tr> 
                                                                        <td style="text-align:center;"><?php echo xlt('R'); ?></td>    
                                                                        <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                        <textarea id="ACT1CCNEAR" name="ACT1CCNEAR" class="ACT"><?php echo text($ACT1CCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                        <textarea id="ACT2CCNEAR"  name="ACT2CCNEAR"class="ACT"><?php echo text($ACT2CCNEAR); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                        <textarea id="ACT3CCNEAR"  name="ACT3CCNEAR" class="ACT"><?php echo text($ACT3CCNEAR); ?></textarea></td>
                                                                        <td style="text-align:center;"><?php echo xlt('L'); ?></td>
                                                                    </tr>
                                                                    <tr>    
                                                                        <td style="text-align:right;"><i class="fa fa-reply rotate-left"></i></td> 
                                                                        <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                        <textarea id="ACT4CCNEAR" name="ACT4CCNEAR" class="ACT"><?php echo text($ACT4CCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;text-align:center;">
                                                                        <textarea id="ACT5CCNEAR" name="ACT5CCNEAR" class="neurosens2 ACT"><?php echo text($ACT5CCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                        <textarea id="ACT6CCNEAR" name="ACT6CCNEAR" class="ACT"><?php echo text($ACT6CCNEAR); ?></textarea></td><td><i class="fa fa-share rotate-right"></i></td> 
                                                                    </tr> 
                                                                    <tr> 
                                                                        <td style="border:0; border-top:2pt solid black;border-right:2pt solid black;text-align:right;">
                                                                            <textarea id="ACT10CCNEAR" name="ACT10CCNEAR" class="ACT"><?php echo text($ACT10CCNEAR); ?></textarea></td>
                                                                        <td style="border-right:1pt solid black;border-top:1pt solid black;text-align:right;">
                                                                            <textarea id="ACT7CCNEAR" name="ACT7CCNEAR" class="ACT"><?php echo text($ACT7CCNEAR); ?></textarea></td>
                                                                        <td style="border:1pt solid black;border-bottom:0pt;text-align:center;">
                                                                            <textarea id="ACT8CCNEAR" name="ACT8CCNEAR" class="ACT"><?php echo text($ACT8CCNEAR); ?></textarea></td>
                                                                        <td style="border-left:1pt solid black;border-top:1pt solid black;text-align:left;">
                                                                            <textarea id="ACT9CCNEAR" name="ACT9CCNEAR" class="ACT"><?php echo text($ACT9CCNEAR); ?></textarea></td>
                                                                        <td style="border:0; border-top:2pt solid black;border-left:2pt solid black;text-align:left;vertical-align:middle;">
                                                                            <textarea id="ACT11CCNEAR" name="ACT11CCNEAR" class="ACT"><?php echo text($ACT11CCNEAR); ?></textarea>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                               <br />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <div id="NPCNPA" name="NPCNPA">
                                    <table style="position:relative;float:left;text-align:center;margin: 4 2;width:100%;font-weight:bold;font-size:1.1em;padding:4px;">
                                        <tr style=""><td style="width:50%;"></td><td><?php echo xlt('OD'); ?></td><td><?php echo xlt('OS'); ?></td></tr>
                                        <tr>
                                            <td class="right"><span title="Near Point of Accomodation"><?php echo xlt('NPA'); ?>:</span></td>
                                            <td><input type="text" id="ODNPA" style="width:70%;" class="neurosens2" name="ODNPA" value="<?php echo attr($ODNPA); ?>"></td>
                                            <td><input type="text" id="OSNPA" style="width:70%;" class="neurosens2" name="OSNPA" value="<?php echo attr($OSNPA); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td class="right"><span title="Near Point of Convergence"><?php echo xlt('NPC'); ?>:</span></td>
                                            <td colspan="2" ><input type="text" style="width:85%;" class="neurosens2" id="NPC" name="NPC" value="<?php echo attr($NPC); ?>">
                                            </td>
                                        </tr>
                                         <tr>
                                            <td class="right">
                                                <?php echo xlt('Stereopsis'); ?>:
                                            </td>
                                            <td colspan="2">
                                                <input type="text" style="width:85%;" class="neurosens" name="STEREOPSIS" id="STEREOPSIS" value="<?php echo attr($STEREOPSIS); ?>">
                                            </td>
                                        </tr>
                                        <tr><td colspan="3"><br /><br /><u><?php echo xlt('Amplitudes'); ?></u><br />
                                            </td></tr>
                                        <tr><td ></td><td ><?php echo xlt('Distance'); ?></td><td><?php echo xlt('Near'); ?></td></tr>
                                        <tr>
                                            <td style="text-align:right;"><?php echo xlt('Divergence'); ?></td>
                                            <td><input type="text" id="DACCDIST" class="neurosens2" name="DACCDIST" value="<?php echo attr($DACCDIST); ?>"></td>
                                            <td><input type="text" id="DACCNEAR" class="neurosens2" name="DACCNEAR" value="<?php echo attr($DACCNEAR); ?>"></td></tr>
                                        <tr>
                                            <td style="text-align:right;"><?php echo xlt('Convergence'); ?></td>
                                            <td><input type="text" id="CACCDIST" class="neurosens2" name="CACCDIST" value="<?php echo attr($CACCDIST); ?>"></td>
                                            <td><input type="text" id="CACCNEAR" class="neurosens2" name="CACCNEAR" value="<?php echo attr($CACCNEAR); ?>"></td></tr>
                                        </tr>
                                         <tr>
                                            <td class="right">
                                                <?php echo xlt('Vertical Fusional'); ?>:
                                            </td>
                                            <td colspan="2">
                                                <input type="text" style="width:85%;" class="neurosens2" name="VERTFUSAMPS" id="VERTFUSAMPS" value="<?php echo attr($VERTFUSAMPS); ?>">
                                                <br />
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div id="NEURO_MOTILITY" class="text_clinical borderShadow" 
                                style="float:left;font-size:0.9em;margin:2 2;padding:0 10;font-weight:bold;height:120px;width:175px;">
                                <div>
                                    <table style="width:100%;margin:0 0 1 0;">
                                        <tr>
                                            <td style="width:40%;font-size:1.0em;margin:0 auto;font-weight:bold;"><?php echo xlt('Motility'); ?>:</td>
                                            <td style="font-size:0.9em;vertical-align:top;text-align:right;top:0.0in;right:0.1in;height:0px;">
                                                <label for="MOTILITYNORMAL" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                                                <input id="MOTILITYNORMAL" name="MOTILITYNORMAL" type="checkbox" <?php if ($MOTILITYNORMAL =='on') echo "checked='checked'"; ?>>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <input type="hidden" name="MOTILITY_RS"  id="MOTILITY_RS" value="<?php echo attr($MOTILITY_RS); ?>">
                                <input type="hidden" name="MOTILITY_RI"  id="MOTILITY_RI" value="<?php echo attr($MOTILITY_RI); ?>">
                                <input type="hidden" name="MOTILITY_RR"  id="MOTILITY_RR" value="<?php echo attr($MOTILITY_RR); ?>">
                                <input type="hidden" name="MOTILITY_RL"  id="MOTILITY_RL" value="<?php echo attr($MOTILITY_RL); ?>">
                                <input type="hidden" name="MOTILITY_LS"  id="MOTILITY_LS" value="<?php echo attr($MOTILITY_LS); ?>">
                                <input type="hidden" name="MOTILITY_LI"  id="MOTILITY_LI" value="<?php echo attr($MOTILITY_LI); ?>">
                                <input type="hidden" name="MOTILITY_LR"  id="MOTILITY_LR" value="<?php echo attr($MOTILITY_LR); ?>">
                                <input type="hidden" name="MOTILITY_LL"  id="MOTILITY_LL" value="<?php echo attr($MOTILITY_LL); ?>">
                                
                                <div style="float:left;left:0.4in;text-decoration:underline;"><?php echo xlt('OD'); ?></div>
                                <div style="float:right;right:0.4in;text-decoration:underline;"><?php echo xlt('OS'); ?></div><br />
                                <div class="divTable" style="background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 90%;height:0.77in;width:0.71in;padding:1px;margin:6 1 1 2;">
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_4_3" id="MOTILITY_RS_4_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_4_1" id="MOTILITY_RS_4_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_4" id="MOTILITY_RS_4" value="<?php echo attr($MOTILITY_RS); ?>">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_4_2" id="MOTILITY_RS_4_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_4_4" id="MOTILITY_RS_4_4">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_3_1" id="MOTILITY_RS_3_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_3" id="MOTILITY_RS_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_3_2" id="MOTILITY_RS_3_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_2_1" id="MOTILITY_RS_2_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_2" id="MOTILITY_RS_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_2_2" id="MOTILITY_RS_2_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_1_1" id="MOTILITY_RS_1_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_1" id="MOTILITY_RS_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_1_2" id="MOTILITY_RS_1_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_0_1" id="MOTILITY_RS_0_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_0" id="MOTILITY_RS_0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RS_0_1" id="MOTILITY_RS_0_1">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divMiddleRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RR_4" id="MOTILITY_RR_4" value="<?php echo attr($MOTILITY_RR); ?>">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RR_3" id="MOTILITY_RR_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RR_2" id="MOTILITY_RR_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RR_1" id="MOTILITY_RR_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RR_0" id="MOTILITY_RR_0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_R0" id="MOTILITY_R0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RL_0" id="MOTILITY_RL_0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RL_1" id="MOTILITY_RL_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RL_2" id="MOTILITY_RL_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RL_3" id="MOTILITY_RL_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RL_4" id="MOTILITY_RL_4" value="<?php echo attr($MOTILITY_RL); ?>">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_0_1" id="MOTILITY_RI_0_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_RI_0" name="MOTILITY_RI_0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_0_2" id="MOTILITY_RI_0_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_1_1" id="MOTILITY_RI_1_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_RI_1" name="MOTILITY_RI_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_1_2" id="MOTILITY_RI_1_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_2_1" id="MOTILITY_RI_2_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_RI_2" name="MOTILITY_RI_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_2_2" id="MOTILITY_RI_2_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_3_5" id="MOTILITY_RI_3_5">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_3_3" id="MOTILITY_RI_3_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_3_1" id="MOTILITY_RI_3_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_RI_3" name="MOTILITY_RI_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_3_2" id="MOTILITY_RI_3_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_3_4" id="MOTILITY_RI_3_4">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_3_6" id="MOTILITY_RI_3_6">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_4_5" id="MOTILITY_RI_4_5">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_4_3" id="MOTILITY_RI_4_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_4_1" id="MOTILITY_RI_4_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_RI_4" name="MOTILITY_RI_4" value="<?php echo attr($MOTILITY_RI); ?>">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_4_2" id="MOTILITY_RI_4_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_4_4" id="MOTILITY_RI_4_4">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RI_4_6" id="MOTILITY_RI_4_6">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>   
                                    <div class="divRow"><div class="divCell">&nbsp;</div>
                                    </div>
                                </div> 
                                <div class="divTable" style="float:right;background: url(../../forms/<?php echo $form_folder; ?>/images/eom.bmp) no-repeat center center;background-size: 90% 90%;height:0.77in;width:0.71in;padding:1px;margin:6 2 0 0;">
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_4_3" id="MOTILITY_LS_4_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_4_1" id="MOTILITY_LS_4_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_4" id="MOTILITY_LS_4" value="<?php echo attr($MOTILITY_LS); ?>">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_4_2" id="MOTILITY_LS_4_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_4_4" id="MOTILITY_LS_4_4">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_3_1" id="MOTILITY_LS_3_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_3" id="MOTILITY_LS_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_3_2" id="MOTILITY_LS_3_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_2_1" id="MOTILITY_LS_2_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_2" id="MOTILITY_LS_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_2_2" id="MOTILITY_LS_2_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_1_1" id="MOTILITY_LS_1_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_1" id="MOTILITY_LS_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_1_2" id="MOTILITY_LS_1_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_0_1" id="MOTILITY_LS_0_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_0" id="MOTILITY_LS_0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LS_0_1" id="MOTILITY_LS_0_1">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divMiddleRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_4" id="MOTILITY_LR_4" value="<?php echo attr($MOTILITY_LR); ?>">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_3" id="MOTILITY_LR_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_2" id="MOTILITY_LR_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_1" id="MOTILITY_LR_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_0" id="MOTILITY_LR_0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_L0" id="MOTILITY_L0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_0" id="MOTILITY_LL_0">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_1" id="MOTILITY_LL_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_2" id="MOTILITY_LL_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_3" id="MOTILITY_LL_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_4" id="MOTILITY_LL_4" value="<?php echo attr($MOTILITY_LL); ?>">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_4_1" id="MOTILITY_LR_4_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_3_1" id="MOTILITY_LR_3_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_2_1" id="MOTILITY_LR_2_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RO_I_1" id="MOTILITY_RO_I_1">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_LI_0" name="MOTILITY_LI_0">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LO_I_1" id="MOTILITY_LO_I_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_2_2" id="MOTILITY_LL_2_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_3_2" id="MOTILITY_LL_3_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_4_2" id="MOTILITY_LL_4_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                     <div class="divRow">
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_4_3" id="MOTILITY_LR_4_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LR_3_3" id="MOTILITY_LR_3_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RO_I_2" id="MOTILITY_RO_I_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_LI_1" name="MOTILITY_LI_1">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LO_I_2" id="MOTILITY_LO_I_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_3_4" id="MOTILITY_LL_3_4">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LL_4_4" id="MOTILITY_LL_4_4">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell" name="MOTILITY_RO_I_3_1" id="MOTILITY_RO_I_3_1">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_RO_I_3" id="MOTILITY_RO_I_3">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_2_1" id="MOTILITY_LI_2_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_LI_2" name="MOTILITY_LI_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_2_2" id="MOTILITY_LI_2_2">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LO_I_2" id="MOTILITY_RO_I_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LO_I_3_1" id="MOTILITY_LO_I_3_1">&nbsp;</div>
                                        </div>
                                    <div class="divRow">
                                        <div class="divCell" name="MOTILITY_LO_I_3" id="MOTILITY_RO_I_3">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_3_5" id="MOTILITY_LI_3_5">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_3_3" id="MOTILITY_LI_3_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_3_1" id="MOTILITY_LI_3_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_LI_3" name="MOTILITY_LI_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_3_2" id="MOTILITY_LI_3_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_3_4" id="MOTILITY_LI_3_4">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_3_6" id="MOTILITY_LI_3_6">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LO_I_3" id="MOTILITY_LO_I_3">&nbsp;</div>
                                        
                                    </div>
                                    <div class="divRow">
                                        <div class="divCell" name="MOTILITY_RO_I_4" id="MOTILITY_RO_I_4">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_4_5" id="MOTILITY_LI_4_5">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_4_3" id="MOTILITY_LI_4_3">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_4_1" id="MOTILITY_LI_4_1">&nbsp;</div>
                                        <div class="divCell" id="MOTILITY_LI_4" name="MOTILITY_LI_4"  value="<?php echo attr($MOTILITY_LI); ?>">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_4_2" id="MOTILITY_LI_4_2">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_4_4" id="MOTILITY_LI_4_4">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LI_4_6" id="MOTILITY_LI_4_6">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell">&nbsp;</div>
                                        <div class="divCell" name="MOTILITY_LO_I_4" id="MOTILITY_LO_I_4">&nbsp;</div>
                                    </div>   
                                    <div class="divRow"><div class="divCell">&nbsp;</div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <br />
                        <div class="QP_lengthen" id="NEURO_COMMENTS_DIV"> 
                            <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left">NCOM</div><br />
                            <textarea id="NEURO_COMMENTS" name="NEURO_COMMENTS"><?php echo text($NEURO_COMMENTS); ?></textarea>
                        </div>
                        <div class="QP_not" id="NEURO_keyboard_left" style="position: absolute;bottom:0.05in;right:0.1in;font-size:0.7em;text-align:left;padding-left:25px;">
                          <span id="NEURO_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke"><b>Keyboard Entry</b></span>
                          &nbsp;<a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=neuro');">
                          <i title="<?php echo xla('Click for Neuro/Physiology shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                          <textarea id="NEURO_keyboard_left" name="NEURO_keyboard_left" style="color:#0C0C0C;size:0.8em;height: 3em;" tabindex='1000'></textarea>
                        </div>
                    </div>     
                </div>
                <div id="NEURO_right" class="exam_section_right borderShadow text_clinical">
                  <?php display_draw_section ("NEURO",$encounter,$pid); ?>
                    
                  <div id="PRIORS_NEURO_left_text" style="height: 2.5in;text-align:left;font-size: 0.9em;" name="PRIORS_NEURO_left_text" class="PRIORS_class PRIORS"><i class="fa fa-spinner fa-spin"></i>
                  </div>
                  <div id="QP_NEURO" name="QP_NEURO" class="QP_class" style="text-align:left;height: 2.5in;">
                    <input type="hidden" id="NEURO_ACT_zone" name="NEURO_ACT_zone" value="<?php echo $ACT_SHOW; ?>">
                    <input type="hidden" id="NEURO_ACT_strab" name="NEURO_ACT_strab" value="">
                    <input type="hidden" id="NEURO_field" name="NEURO_field" value="5">
                    <input type="hidden" id="NEURO_value" name="NEURO_value" value="ortho">
                    <input type="hidden" id="NEURO_side" name="NEURO_side" value="">

                    <div style="position:absolute;top:0.05in;right:0.241in;">
                            <?php echo priors_select("NEURO",$id,$id,$pid); ?>
                    </div>
                    <div style="float:right;top:1.0in;right:0.0in;margin: 0 25;"><br />
                      <div class="borderShadow" style="width:190px;text-align:center;border:1pt solid black;padding:4 10 4 10;margin:20 4 4 4;font-weight:600;"><span class="underline">Laterality</span><br />
                        <span class="eye_button" id="NEURO_side_R" name="NEURO_side" style="padding-left:0.06in;padding-right:0.06in;"  onclick="$('#NEURO_side').val('R').trigger('change');"><?php echo xlt('Right'); ?></span>  
                        <span class="eye_button" id="NEURO_side_L" name="NEURO_side" style="padding-left:0.06in;padding-right:0.06in;"  onclick="$('#NEURO_side').val('L').trigger('change');"><?php echo xlt('Left'); ?></span> 
                        <span class="eye_button eye_button_selected" id="NEURO_side_None" name="NEURO_side"  onclick="$('#NEURO_side').val('').trigger('change');"><?php echo xlt('None'); ?></span> <br />
                      </div>
                      <div class="borderShadow" style="width:190px;text-align:center;border:1pt solid black;padding:4 10 4 10;margin:4;font-weight:600;"><span class="underline">Deviation</span><br />
                        <span class="eye_button" id="NEURO_ACT_strab_E" name="NEURO_ACT_strab" alt="Esophoria" onclick="$('#NEURO_ACT_strab').val('E').trigger('change');"><?php echo xlt('E'); ?></span> 
                        <span class="eye_button" id="NEURO_ACT_strab_ET_int" name="NEURO_ACT_strab" alt="Intermittent Esotropia" onclick="$('#NEURO_ACT_strab').val('E\(T\)').trigger('change');"><?php echo xlt('E(T)'); ?></span> 
                        <span class="eye_button" id="NEURO_ACT_strab_ET" name="NEURO_ACT_strab" alt="Esotropia" onclick="$('#NEURO_ACT_strab').val('ET').trigger('change');"><?php echo xlt('ET'); ?></span> 
                       <br />
                        <span class="eye_button" id="NEURO_ACT_strab_X" name="NEURO_ACT_strab" alt="Exophoria" onclick="$('#NEURO_ACT_strab').val('X').trigger('change');"><?php echo xlt('X'); ?></span> 
                        <span class="eye_button" id="NEURO_ACT_strab_XT_int" name="NEURO_ACT_strab" alt="Intermittent Exotropia" onclick="$('#NEURO_ACT_strab').val('X\(T\)').trigger('change');"><?php echo xlt('X(T)'); ?></span> 
                        <span class="eye_button" id="NEURO_ACT_strab_XT" name="NEURO_ACT_strab" alt="Exotropia" onclick="$('#NEURO_ACT_strab').val('XT').trigger('change');"><?php echo xlt('XT'); ?></span> 
                        
                        <br />
                        <span class="eye_button" id="NEURO_ACT_strab_H" name="NEURO_ACT_strab"  onclick="$('#NEURO_ACT_strab').val('HT').trigger('change');"><?php echo xlt('HT'); ?></span> 
                        <span class="eye_button" id="NEURO_ACT_strab_HT_int" name="NEURO_ACT_strab"  onclick="$('#NEURO_ACT_strab').val('H\(T\)').trigger('change');"><?php echo xlt('H(T)'); ?></span> 
                        <span class="eye_button" id="NEURO_ACT_strab_hypoT" name="NEURO_ACT_strab"  onclick="$('#NEURO_ACT_strab').val('hypoT').trigger('change');"><?php echo xlt('hypoT'); ?></span> 
                        <span class="eye_button" id="NEURO_ACT_strab_hypoT_int" name="NEURO_ACT_strab"  onclick="$('#NEURO_ACT_strab').val('hypo\(T\)').trigger('change');"><?php echo xlt('hypo(T)'); ?></span> 
                        <br />
                      </div>  
                     <div style="width:195px;text-align:center;margin:4;font-weight:600;padding:10 2 10 2;">
                        <span class="borderShadow" style="margin:4;bottom:0;right:0.01in;height:20px;" id="NEURO_RECORD" name="NEURO_RECORD"><?php echo xlt(' RECORD '); ?></span> 
                      </div>
                      <div class="QP_block_outer borderShadow nodisplay" style="z-index:1;text-align:center;border:1pt solid black;padding:7 10 7 10;font-weight:600;">
                          <span id="NEURO1_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke">Keyboard Entry</span>&nbsp;<a onclick="goto_url('<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php?zone=neuro');">
                          <i title="<?php echo xla('Click for Neuro/Strabismus shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></a><br />
                          <textarea id="NEURO_keyboard" name="NEURO_keyboard" style="color:#0C0C0C;size:0.8em;height: 0.48in;" tabindex='1002'></textarea>
                          <span style="font-size:0.9em;font-weight:400;color:#0C0C0C;">Type: fieldnumber.PD strab ENTER<br />
                          eg. 10 X(T) in Primary postion: <b>5.10ix</b>
                          <br />Bilateral VIs:
                          <b>5.Ortho;4.10et;6.10et;</b></span>
                      </div>
                    </div>
                    <div style="position:left;top:0in;left:0.10in;margin: auto;">
                      &nbsp;
                      <div class="borderShadow" style="width:195px;text-align:center;border:1pt solid black;padding:4 5 4 5;margin:4;font-weight:600;"><span class="underline"><?php echo xlt('Rx/Distance'); ?></span><br />
                        <span class="eye_button <?php if ($ACT_SHOW =='SCDIST') echo "eye_button_selected"; ?>" id="NEURO_ACT_zone_SCDIST" name="NEURO_ACT_zone" style="padding-right:0.06in;" onclick="$('#NEURO_ACT_zone').val('SCDIST').trigger('change');"> <?php echo xlt('scDist'); ?> </span> 
                        <span class="eye_button <?php if ($ACT_SHOW =='CCDIST') echo "eye_button_selected"; ?>" id="NEURO_ACT_zone_CCDIST" name="NEURO_ACT_zone" style="padding-right:0.06in;" onclick="$('#NEURO_ACT_zone').val('CCDIST').trigger('change');"> <?php echo xlt('ccDist'); ?> </span> 
                        <span class="eye_button <?php if ($ACT_SHOW =='SCNEAR') echo "eye_button_selected"; ?>" id="NEURO_ACT_zone_SCNEAR" name="NEURO_ACT_zone" style="padding-right:0.06in;" onclick="$('#NEURO_ACT_zone').val('SCNEAR').trigger('change');"> <?php echo xlt('scNear'); ?> </span> 
                        <span class="eye_button <?php if ($ACT_SHOW =='CCNEAR') echo "eye_button_selected"; ?>" id="NEURO_ACT_zone_CCNEAR" name="NEURO_ACT_zone" onclick="$('#NEURO_ACT_zone').val('CCNEAR').trigger('change');"> <?php echo xlt('ccNear'); ?> </span> 
                      </div>
                      <div class="borderShadow" style="width:195px;text-align:center;border:1pt solid black;padding:4 10 4 10;margin:4;font-weight:600;"><span class="underline"><?php echo xlt('Position of Gaze'); ?></span><br />
                        <span class="eye_button_blank"> <?php echo xlt('R'); ?> </span> 
                        <span class="eye_button" id="NEURO_field_1" name="NEURO_field"  onclick="$('#NEURO_field').val('1').trigger('change');"> <?php echo xlt('1'); ?> </span> 
                        <span class="eye_button" id="NEURO_field_2" name="NEURO_field"  onclick="$('#NEURO_field').val('2').trigger('change');"> <?php echo xlt('2'); ?> </span> 
                        <span class="eye_button" id="NEURO_field_3" name="NEURO_field"  onclick="$('#NEURO_field').val('3').trigger('change');"> <?php echo xlt('3'); ?> </span> 
                        <span class="eye_button_blank"> <?php echo xlt('L'); ?> </span> 
                      
                        <span class="eye_button_blank"> <?php echo xlt(''); ?><i class="fa fa-reply rotate-left"></i></span> 
                        <span class="eye_button" id="NEURO_field_4" name="NEURO_field"  onclick="$('#NEURO_field').val('4').trigger('change');"> <?php echo xlt('4'); ?> </span> 
                        <span class="eye_button eye_button_selected" id="NEURO_field_5" name="NEURO_field"  onclick="$('#NEURO_field').val('5').trigger('change');"> <?php echo xlt('5'); ?> </span> 
                        <span class="eye_button" id="NEURO_field_6" name="NEURO_field"  onclick="$('#NEURO_field').val('6').trigger('change');"><?php echo xlt('6'); ?></span>  
                        <span class="eye_button_blank"> <?php echo xlt(''); ?><i class="fa fa-share rotate-right"></i></span> 
                  
                        <span class="eye_button" id="NEURO_field_10" name="NEURO_field"  onclick="$('#NEURO_field').val('10').trigger('change');"><?php echo xlt('10'); ?></span>  
                        <span class="eye_button" id="NEURO_field_7" name="NEURO_field"  onclick="$('#NEURO_field').val('7').trigger('change');"><?php echo xlt('7'); ?></span> 
                        <span class="eye_button" id="NEURO_field_8" name="NEURO_field"  onclick="$('#NEURO_field').val('8').trigger('change');"><?php echo xlt('8'); ?></span> 
                        <span class="eye_button" id="NEURO_field_9" name="NEURO_field"  onclick="$('#NEURO_field').val('9').trigger('change');"><?php echo xlt('9'); ?></span>
                        <span class="eye_button" id="NEURO_field_11" name="NEURO_field"  onclick="$('#NEURO_field').val('11').trigger('change');"><?php echo xlt('11'); ?></span>  
                      </div>
                 
                      <div class="borderShadow" style="width:195px;text-align:center;font-weight:600;padding:4 10 4 10;margin:4;"><span class="underline">Prism Diopters</span><br />
                        <span class="eye_button" id="NEURO_value_ortho" name="NEURO_value"  onclick="$('#NEURO_value').val('ortho').trigger('change');"><?php echo xlt('Ortho'); ?></span>  
                        <span class="eye_button" id="NEURO_value_1" name="NEURO_value"  onclick="$('#NEURO_value').val('1').trigger('change');"><?php echo xlt('1'); ?></span> 
                        <span class="eye_button" id="NEURO_value_2" name="NEURO_value"  onclick="$('#NEURO_value').val('2').trigger('change');"><?php echo xlt('2'); ?></span> 
                        <span class="eye_button" id="NEURO_value_3" name="NEURO_value"  onclick="$('#NEURO_value').val('3').trigger('change');"><?php echo xlt('3'); ?></span> 
                        <span class="eye_button" id="NEURO_value_4" name="NEURO_value"  onclick="$('#NEURO_value').val('4').trigger('change');"><?php echo xlt('4'); ?></span> 
                        <span class="eye_button" id="NEURO_value_5" name="NEURO_value"  onclick="$('#NEURO_value').val('5').trigger('change');"><?php echo xlt('5'); ?></span> 
                        <span class="eye_button" id="NEURO_value_6" name="NEURO_value"  onclick="$('#NEURO_value').val('6').trigger('change');"><?php echo xlt('6'); ?></span> 
                        <span class="eye_button" id="NEURO_value_8" name="NEURO_value"  onclick="$('#NEURO_value').val('8').trigger('change');"><?php echo xlt('8'); ?></span> 
                        <span class="eye_button" id="NEURO_value_10" name="NEURO_value"  onclick="$('#NEURO_value').val('10').trigger('change');"><?php echo xlt('10'); ?></span> 
                        <span class="eye_button" id="NEURO_value_12" name="NEURO_value"  onclick="$('#NEURO_value').val('12').trigger('change');"><?php echo xlt('12'); ?></span> 
                        <span class="eye_button" id="NEURO_value_14" name="NEURO_value"  onclick="$('#NEURO_value').val('14').trigger('change');"><?php echo xlt('14'); ?></span> 
                        <span class="eye_button" id="NEURO_value_16" name="NEURO_value"  onclick="$('#NEURO_value').val('16').trigger('change');"><?php echo xlt('16'); ?></span> 
                        <span class="eye_button" id="NEURO_value_18" name="NEURO_value"  onclick="$('#NEURO_value').val('18').trigger('change');"><?php echo xlt('18'); ?></span> 
                        <span class="eye_button" id="NEURO_value_20" name="NEURO_value"  onclick="$('#NEURO_value').val('20').trigger('change');"><?php echo xlt('20'); ?></span> 
                        <span class="eye_button" id="NEURO_value_25" name="NEURO_value"  onclick="$('#NEURO_value').val('25').trigger('change');"><?php echo xlt('25'); ?></span> 
                        <span class="eye_button" id="NEURO_value_30" name="NEURO_value"  onclick="$('#NEURO_value').val('30').trigger('change');"><?php echo xlt('30'); ?></span> 
                        <span class="eye_button" id="NEURO_value_35" name="NEURO_value"  onclick="$('#NEURO_value').val('35').trigger('change');"><?php echo xlt('35'); ?></span> 
                        <span class="eye_button" id="NEURO_value_40" name="NEURO_value"  onclick="$('#NEURO_value').val('40').trigger('change');"><?php echo xlt('40'); ?></span> 
                        
                      </div>
                      
                    </div>
                    <span class="closeButton fa fa-close pull-right z100" id="BUTTON_TEXTD_NEURO" name="BUTTON_TEXTD_NEURO" value="1"></span>
                  </div>
                </div>
            </div>
            <!-- end Neuro -->

            <!-- start IMP/PLAN -->    
            <div id="IMPPLAN_1" class="clear_both size50"> 
              <span class="anchor" id="IMPPLAN_anchor"></span>

              <div id="IMPPLAN_left" name="IMPPLAN_left" class="exam_section_left borderShadow">
                <!-- <span class="closeButton fa fa-plus-square-o" id="MAX_IMPPLAN" name="MAX_IMPPLAN"></span> -->
                <span class="closeButton fa fa-paint-brush" id="BUTTON_DRAW_IMPPLAN" name="BUTTON_DRAW_IMPPLAN"></span>
                <i class="closeButton_2 fa fa-database" id="BUTTON_QP_IMPPLAN" name="BUTTON_QP_IMPPLAN"></i>
                        
                <div id="IMPPLAN_left_text" name="IMPPLAN_left_text" style="margin:auto 5;min-height: 2.5in;text-align:left;">
                  <!-- this needs work to integrate it to auto populate with CPT/ICD codes based on form inputs above -->
                   <b><?php echo xlt('Impression/Plan'); ?>:</b>
                   <textarea rows=5 id="IMP" name="IMP" style="height:2.4in;width:90%;"><?php echo text($IMP); ?></textarea>
                   <!-- <b><?php echo xlt('Plan'); ?>/<?php echo xlt('Recommendation'); ?>:</b>
                   <textarea rows=5 id="PLAN" name="PLAN" style="height:1.3in;width:90%;"><?php echo text($PLAN); ?></textarea>
                 -->
                </div>
              </div>
              <div id="IMPPLAN_right" class="exam_section_right borderShadow text_clinical">
                 <?php display_draw_section ("IMPPLAN",$encounter,$pid); ?>
                    
                <div id="PRIORS_IMPPLAN_left_text" style="height: 2.5in;text-align:left;font-size: 0.9em;" name="PRIORS_IMPPLAN_left_text" class="PRIORS_class PRIORS"><i class="fa fa-spinner fa-spin"></i>
                </div>
                <div id="QP_IMPPLAN" name="QP_IMPPLAN" class="QP_class" style="text-align:left;height: 2.5in;">
                  <span class="closeButton fa fa-close pull-right z100" id="BUTTON_TEXTD_IMPPLAN" name="BUTTON_TEXTD_IMPPLAN" value="1"></span>
                   
                  
                  <a target="_report" href="/openemr/interface/patient_file/report/custom_report.php?printable=1&pdf=0&<?php echo $form_folder."_".$form_id."=".$encounter; ?>&">Printable Report</a><br />
            <!--
                  <br />Coding IN DEVELOPMENT ONLY - NOT FUNCTIONAL:<br />
                  <span class="CODE_HIGH nodisplay"><i class="fa fa-check"></i> Detailed HPI documentation.</span><br />
                  <span class="DIL_RISKS nodisplay"><i class="fa fa-check"></i> Detailed Exam documentation.</span><br />
                  <span id="neurosens_code" name="neurosens_code" class="nodisplay"><i class="fa fa-check"></i> NeuroSensory Exam (92060) Criteria met.</span><br />
                  <small>Only add to todays' charges if performed today:</small><br />
                  <span><input type="checkbox" id="visual Field" name="visual Field">Visual Field (92083)</span><br />
                  <span><input type="checkbox" id="Retina_photo" name="Retina_photo">Retina Photo(CPT 92250) or 
                    <input type="checkbox" id="Retina_OCT" name="Retina_OCT">Retina OCT(CPT 92134-50)</span><br />
                  <span><input type="checkbox" id="Retina_photo" name="Retina_photo">Optic Disc OCT (92133)</span><br />
                  <span><input type="checkbox" id="k_topo" name="k_topo">Corneal Topography (92025)</span><br />
                  <span><input type="checkbox" id="Refraction" name="Refraction">Refraction (Medicare)</span><br />
                  <span><input type="checkbox" id="pachy" name="pachy">Pachymetry (76514)</span><br />
                  76514 


                  
                  <?  //forach $PMSFH[0]['POH'] print + Code enter
                  $PMSFH = build_PMSFH($pid); 
                  if (count($PMSFH[0]['POH']) > 0) {
                    foreach ($PMSFH[0]['POH'] as $k => $v) {
                    //  echo $k ." = ".$v['title'].": ".$k['formdiagnosis']."<br />";
                    }
                  }
              ?>
            -->
                </div>
              </div>   
            </div>
            
            <br />  
            <!--  <span class="Button" action="finalize" id="action" name="action" value="finalize" onclick="finalize();">FINALIZE</span>  
             --> <br /><br />
            <!-- END IMP/PLAN -->  
          </div>
          <!-- end of the exam section -->
          </div>
            <!--
              <a style="bottom:10px;" onclick="top.restoreSession(); window.print(); return false;">Print PDF</a>
            -->       
        </div>
        
        <!-- end        accordion div -->
      </div>
      <!-- end container for the main body of the form -->
    </form>
    
  </div>
  </div>
<?php
    if ($display=="fullscreen") { 
      // trial fullscreen will lead to tablet versions and bootstrap menu overhaul
      // this function is in php/eye_mag_functions.php
      $output = menu_overhaul_bottom($pid,$encounter);
     // echo $output;
    }
    ?>
    <!-- 
    // Printing this and making a report from this are two separate ways to repesent the data and deserve their own forms...
    //so leave this out for now
            <input type="button" value="Print" id="PrintButton" />
    -->

   
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/panelslider/jquery.panelslider.js"></script>
    <!-- Add eye_mag js library -->
    <script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/shortcut.js"></script>
    
    <!-- Undo code -->
    <script src="../../forms/<?php echo $form_folder; ?>/js/undone.js"></script>
    <script src='../../forms/<?php echo $form_folder; ?>/js/jquery.undone.js'></script>
    <script>

        $.undone();

        $("#undo, #redo, #clear").click(function(){
            // undone[this.className]();
            $.undone(this.id);
        });

        $(document).on("mouseenter", ".toggle", function () {
            var self = $(this);
            $.undone("register",
                function () { self.addClass("hidden"); },
                function () { self.removeClass("hidden"); }
            );
        });


        $(document).keydown(function (e) {
            var key = e.which;
            if (e.ctrlKey) { // ctrl
                if (key === 90) $.undone("undo"); // z
                if (key === 89) $.undone("redo"); // y
            }
        });

        $(window).on("undone:change", function(e, name, undoLen, redoLen){
            $("#undo").prop("disabled", !undoLen);
            $("#redo").prop("disabled", !redoLen);
            $("input").val(undoLen)
        });


        // create the items
        var items = 800;
        while(items--) {
            var a = document.createElement("a");
            a.href = "#";
            a.className = "toggle";
            document.getElementsByTagName("div")[0].appendChild(a);
        }
    </script>
    <!-- Undo code done -->

    <script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/my_js_base.js"></script>
    <script type="text/javascript" src="../../forms/<?php echo $form_folder; ?>/js/canvasdraw.js"></script>

    <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/restoreSession.php"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>


 
  <div id="right-panel" name="right-panel" class="panel_side">
      <?php 
      // We are building the panel bar with the patient medical info
      // Since the "lists" table has a "subtype" field now
      // each section could be customized, like SOCHx, subtype "smoking"
      // However openEMR stores mos of this SocHx data in the layout_options table with
      // the form_id='HIS' and group_name='4Lifestyle' fields.  
      // So we need to conform and pull this information out of the current "History" fields.
      // We do this in the eye_mag_functions.php file by creating a new array $PMSFH 
      // pulling in the issues, Scial History and FH into one place.
      // When done, FH and Soc HX will just be another part of the PMSFH associative array.
      // The function display_PRIOR_section("PMSFH",$id,$id,$pid) in php/eye_mag_functions 
      // shows the PMSFH to the right of the PMH zone in the QP zone.  
      // It is tough to format this in a visually pleasing way when there is 
      // a lot of data.  Will need to crimp boxes to make it fit phsyical constraints of QP box. 
      // Right panel does not have such problems if screen is large enough, and if not it is scrollable.
      // The sub routine show_PMSFH_panel($PMSFH) creates the right panel NavBar from this array...
      // Probably a good way to go with ROS too.  We'll see when we get to doing ROS.
      echo $output_PMSFH_panel = show_PMSFH_panel($PMSFH);  
      ?>
  </div>
</div>
    
 <!-- Load jQuery from Google CDN -->
    <script>
$('#left-panel-link').panelslider({side: 'right', clickClose: false, duration: 600, easingOpen: 'easeInBack', easingClose: 'easeOutBack'});
$('#right-panel-link').panelslider({side: 'right', clickClose: false, duration: 600, easingOpen: 'easeInBack', easingClose: 'easeOutBack'});
$('#close-panel-bt').click(function() {
$.panelslider.close();
});
<?php if ($PANEL_RIGHT >'0') { ?>
  $("#right-panel-link").trigger("click");
<?php } ?>

</script>


  </body>   
</html>