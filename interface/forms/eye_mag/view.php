<?php

/**
 * forms/eye_mag/view.php
 *
 * Central view for the eye_mag form.  Here is where all new data is entered
 * New forms are created via new.php and then this script is displayed.
 * Edit requests come here too...
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2016- Raymond Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/FeeSheetHtml.class.php");
include_once("../../forms/eye_mag/php/eye_mag_functions.php");

use OpenEMR\Core\Header;

$form_name   = "eye_mag";
$form_folder = "eye_mag";
$Form_Name   = "Eye Exam";
$form_id     = $_REQUEST['id'];
$action      = $_REQUEST['action'] ?? null;
$finalize    = $_REQUEST['finalize'] ?? null;
$id          = $_REQUEST['id'];
$display     = $_REQUEST['display'] ?? null;
$pid         = $_REQUEST['pid'] ?? '';
$refresh     = $_REQUEST['refresh'] ?? null;

if (!empty($_REQUEST['url'])) {
    header('Location: ' . $_REQUEST['url']);
    exit;
}

// Get user preferences, for this user
$query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND (id=?) ORDER BY id,ZONE_ORDER,ordering";
$result = sqlStatement($query, array($_SESSION['authUserID']));
while ($prefs = sqlFetchArray($result)) {
    $LOCATION = $prefs['LOCATION'];
    $$LOCATION = text($prefs['GOVALUE']);
}
// These settings are sticky user preferences linked to a given page.
// Could do ALL preferences this way instead of the modified extract above...
// mdsupport - user_settings prefix
$uspfx = "EyeFormSettings_";
$setting_tabs_left  = prevSetting($uspfx, 'setting_tabs_left', 'setting_tabs_left', '0');
$setting_HPI        = prevSetting($uspfx, 'setting_HPI', 'setting_HPI', '1');
$setting_PMH        = prevSetting($uspfx, 'setting_PMH', 'setting_PMH', '1');
$setting_EXT        = prevSetting($uspfx, 'setting_EXT', 'setting_EXT', '1');
$setting_ANTSEG     = prevSetting($uspfx, 'setting_ANTSEG', 'setting_ANTSEG', '1');
$setting_POSTSEG    = prevSetting($uspfx, 'setting_POSTSEG', 'setting_POSTSEG', '1');
$setting_NEURO      = prevSetting($uspfx, 'setting_NEURO', 'setting_NEURO', '1');
$setting_IMPPLAN    = prevSetting($uspfx, 'setting_IMPPLAN', 'setting_IMPPLAN', '1');

$query10 = "select  *,form_encounter.date as encounter_date

               from forms,form_encounter,form_eye_base,
                form_eye_hpi,form_eye_ros,form_eye_vitals,
                form_eye_acuity,form_eye_refraction,form_eye_biometrics,
                form_eye_external, form_eye_antseg,form_eye_postseg,
                form_eye_neuro,form_eye_locking
                    where
                    forms.deleted != '1'  and
                    forms.formdir='eye_mag' and
                    forms.encounter=form_encounter.encounter  and
                    forms.form_id=form_eye_base.id and
                    forms.form_id=form_eye_hpi.id and
                    forms.form_id=form_eye_ros.id and
                    forms.form_id=form_eye_vitals.id and
                    forms.form_id=form_eye_acuity.id and
                    forms.form_id=form_eye_refraction.id and
                    forms.form_id=form_eye_biometrics.id and
                    forms.form_id=form_eye_external.id and
                    forms.form_id=form_eye_antseg.id and
                    forms.form_id=form_eye_postseg.id and
                    forms.form_id=form_eye_neuro.id and
                    forms.form_id=form_eye_locking.id and
                    forms.pid=form_eye_base.pid and
                    forms.pid=form_eye_hpi.pid and
                    forms.pid=form_eye_ros.pid and
                    forms.pid=form_eye_vitals.pid and
                    forms.pid=form_eye_acuity.pid and
                    forms.pid=form_eye_refraction.pid and
                    forms.pid=form_eye_biometrics.pid and
                    forms.pid=form_eye_external.pid and
                    forms.pid=form_eye_antseg.pid and
                    forms.pid=form_eye_postseg.pid and
                    forms.pid=form_eye_neuro.pid and
                    forms.pid=form_eye_locking.pid and
                    forms.form_id =? ";

$encounter_data = sqlQuery($query10, array($id));
@extract($encounter_data);
$id = $form_id;

list($ODIOPTARGET,$OSIOPTARGET) = getIOPTARGETS($pid, $id, $provider_id);

$query          = "SELECT * FROM patient_data where pid=?";
$pat_data       =  sqlQuery($query, array($pid));

$query          = "SELECT * FROM users where id = ?";
$prov_data      = sqlQuery($query, array($provider_id));


$query = "SELECT * FROM users WHERE id=?";
$pcp_data =  sqlQuery($query, array($pat_data['providerID']));
$ref_data =  sqlQuery($query, array($pat_data['ref_providerID']));
$insurance_info[1] = getInsuranceData($pid, "primary");
$insurance_info[2] = getInsuranceData($pid, "secondary");
$insurance_info[3] = getInsuranceData($pid, "tertiary");
$ins_coA = $insurance_info[1]['provider_name'];
$ins_coB = $insurance_info[2]['provider_name'];
$ins_coC = $insurance_info[3]['provider_name'];
// build $PMSFH array
global $priors;
global $earlier;
$PMSFH = build_PMSFH($pid);
$fs = new FeeSheetHtml();
/*
  Two windows anywhere with the same chart open is not compatible with the autosave feature.
  Data integrity problems will arise.
  We use a random number generated for each instance - each time the form is opened - == uniqueID.
  If:   the form is LOCKED
        and the LOCKEDBY variable != uniqueID
        and less than one hour has passed since it was locked
  then: a pop-up signals READ-ONLY mode.
  This user can take control if they wish.  If they confirm yes, take control,
  LOCKEDBY is changed to their uniqueID,
  Any other instance of the form cannot save data, and if they try,
  they will receive a popup saying hey buddy, you lost ownership, entering READ-ONLY mode.
  "Do you want to take control" is offered, should they wish to regain write privileges
  If they stay in READ-ONLY mode, the fields are locked and submit_form is not allowed...
  In READ-ONLY mode, the form is refreshed via ajax every 15 seconds with changed fields' css
  background-color attribute set to purple.
  Once the active user with write privileges closes their instance of the form, the form_id is unlocked.
  READ-ONLY users stay read only if they do nothing.
 */

$warning = 'nodisplay';
$uniqueID = mt_rand();
$warning_text = 'READ-ONLY mode.';

if (!$LOCKED || !$LOCKEDBY) { //no one else has write privs.
    $LOCKEDBY = $uniqueID;
    $LOCKED = '1';
} else {
    //warning.  This form is locked by another user.
    $warning = ""; //remove nodisplay class
    $take_ownership = $uniqueID;
}

/**
 * Remove TIME component from $encounter_date (which is in DATETIME format) to just get the date,
 * since OpenEMR assumes input is yyyy-mm-dd.
 */
$dated = new DateTime($encounter_data['encounter_date']);
$dated = $dated->format('Y-m-d');
$visit_date = oeFormatShortDate($dated);

if (!$form_id && !$encounter) {
    echo text($encounter) . "-" . text($form_id) . xlt('No encounter...');
    exit;
}

if ($refresh and $refresh != 'fullscreen') {
    if ($refresh == "PMSFH") {
        echo display_PRIOR_section($refresh, $id, $id, $pid);
    } elseif ($refresh == "PMSFH_panel") {
        echo show_PMSFH_panel($PMSFH);
    } elseif ($refresh == "page") {
        echo send_json_values($PMSFH);
    } elseif ($refresh == "GFS") {
        echo display_GlaucomaFlowSheet($pid);
    }
    exit;
}
?><!DOCTYPE html>
<html>
  <head>
      <title> <?php echo xlt('Chart'); ?>: <?php echo text($pat_data['fname']) . " " . text($pat_data['lname']) . " " . text($visit_date); ?></title>
      <meta name="description" content="OpenEMR: Eye Exam" />
      <meta name="author" content="OpenEMR: Ophthalmology" />


      <?php Header::setupHeader([ 'jquery-ui', 'jquery-ui-redmond','datetime-picker', 'dialog' ,'jscolor', 'chart' ]); ?>
      <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/style.css?v=<?php echo $v_js_includes; ?>" type="text/css">

  </head>
  <!--Need a margin-top due to fixed nav, move to style.css to separate view stuff? Long way from that... -->
  <body class="bgcolor2" background="<?php echo $GLOBALS['backpic']?>" style="margin:5px 0 0 0;">

  <div id="tabs_left" class="tabs ui-tabs ui-widget ui-widget-content ui-corner-all nodisplay">
      <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-corner-all">

          <li id="tabs-left-HPI" class="btn-primary">
              <span><?php echo xlt('HPI'); ?></span>
          </li>
          <li id="tabs-left-PMH" class="btn-primary">
              <span><?php echo xlt('PMH'); ?></span>
          </li>
          <li id="tabs-left-EXT" class="btn-primary">
              <span><?php echo xlt('Ext'); ?></span>
          </li>
          <li id="tabs-left-ANTSEG" class="btn-primary">
              <span><?php echo xlt('Ant'); ?></span>
          </li>
          <li id="tabs-left-POSTSEG" class="btn-primary">
              <span><?php echo xlt('Retina'); ?></span>
          </li>
          <li id="tabs-left-NEURO" class="btn-primary">
              <span><?php echo xlt('Neuro'); ?></span>
          </li>
          <li id="tabs-left-IMPPLAN" class="btn-primary">
              <span><?php echo xlt('Imp'); ?></span>
          </li>

      </ul>
  </div>

    <?php
      $input_echo = menu_overhaul_top($pid, $encounter);
    ?><br /><br />

    <div id="page-wrapper" data-role="page">
      <div id="Layer2" name="Layer2" class="nodisplay">
      </div>
      <div id="Layer3" name="Layer3" class="container-fluid">
        <?php
        $output_priors = priors_select("ALL", $id, $id, $pid);

        menu_overhaul_left($pid, $encounter);
        //define if this is a new or est patients for coding auto-suggestions
            //TODO: develop logic to recognize post-op visits 99024
            // if a prior encounter within 90 days are procedures with a global period still in effect, then post-op code
        ?>
          <script>
              var Code_new_est ='<?php
                if ($output_priors == '') {
                    echo xls("New");
                } else {
                    echo xls("Est");
                }
                ?>';
          </script>
        <!-- start form -->
        <form method="post" action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/save.php?mode=update" id="eye_mag" class="eye_mag pure-form" name="eye_mag">
          <div id="Layer1" name="Layer1" class="display">
            <div id="warning" name="warning" class="alert alert-warning <?php echo $warning; ?>">
              <span type="button" class="close" data-dismiss="alert">&times;</span>
              <h4><?php echo xlt('Warning'); ?>!
                <?php echo text($warning_text); ?></h4>
            </div>

            <!-- start form_container for the main body of the form -->
            <div class="body_top text-center row" id="form_container" name="form_container">
              <input type="hidden" name="menustate" id="menustate" value="start">
              <input type="hidden" name="form_folder" id="form_folder" value="<?php echo attr($form_folder); ?>">
              <input type="hidden" name="form_id" id="form_id" value="<?php echo attr($form_id); ?>">
              <input type="hidden" name="pid" id="pid" value="<?php echo attr($pid); ?>">
              <input type="hidden" name="encounter" id="encounter" value="<?php echo attr($encounter); ?>">
              <input type="hidden" name="visit_date" id="visit_date" value="<?php echo attr($encounter_date); ?>">
              <input type="hidden" name="PREFS_VA" id="PREFS_VA" value="<?php echo attr($VA ?? ''); ?>">
              <input type="hidden" name="PREFS_W" id="PREFS_W" value="<?php echo attr($W ?? ''); ?>">
              <input type="hidden" name="PREFS_W_width" id="PREFS_W_width" value="<?php echo attr($W_width ?? ''); ?>">
              <input type="hidden" name="PREFS_MR" id="PREFS_MR" value="<?php echo attr($MR ?? ''); ?>">
              <input type="hidden" name="PREFS_MR_width" id="PREFS_MR_width" value="<?php echo attr($MR_width ?? ''); ?>">
              <input type="hidden" name="PREFS_CR" id="PREFS_CR" value="<?php echo attr($CR ?? ''); ?>">
              <input type="hidden" name="PREFS_CTL" id="PREFS_CTL" value="<?php echo attr($CTL ?? ''); ?>">
              <input type="hidden" name="PREFS_VAX" id="PREFS_VAX" value="<?php echo attr($VAX ?? ''); ?>">
              <input type="hidden" name="PREFS_RXHX" id="PREFS_RXHX" value="<?php echo attr($RXHX ?? ''); ?>">
              <input type="hidden" name="PREFS_ADDITIONAL" id="PREFS_ADDITIONAL" value="<?php echo attr($ADDITIONAL ?? ''); ?>">
              <input type="hidden" name="PREFS_CLINICAL" id="PREFS_CLINICAL" value="<?php echo attr($CLINICAL ?? ''); ?>">
              <input type="hidden" name="PREFS_IOP" id="PREFS_IOP" value="<?php echo attr($IOP ?? ''); ?>">
              <input type="hidden" name="PREFS_EXAM" id="PREFS_EXAM" value="<?php echo attr($EXAM ?? ''); ?>">
              <input type="hidden" name="PREFS_CYL" id="PREFS_CYL" value="<?php echo attr($CYLINDER ?? ''); ?>">
              <input type="hidden" name="PREFS_HPI_VIEW" id="PREFS_HPI_VIEW" value="<?php echo attr($HPI_VIEW ?? ''); ?>">
              <input type="hidden" name="PREFS_EXT_VIEW" id="PREFS_EXT_VIEW" value="<?php echo attr($EXT_VIEW ?? ''); ?>">
              <input type="hidden" name="PREFS_ANTSEG_VIEW" id="PREFS_ANTSEG_VIEW" value="<?php echo attr($ANTSEG_VIEW ?? ''); ?>">
              <input type="hidden" name="PREFS_RETINA_VIEW" id="PREFS_RETINA_VIEW" value="<?php echo attr($RETINA_VIEW ?? ''); ?>">
              <input type="hidden" name="PREFS_NEURO_VIEW" id="PREFS_NEURO_VIEW" value="<?php echo attr($NEURO_VIEW ?? ''); ?>">
              <input type="hidden" name="PREFS_ACT_VIEW" id="PREFS_ACT_VIEW" value="<?php echo attr($ACT_VIEW ?? ''); ?>">
              <input type="hidden" name="PREFS_PMH_RIGHT" id="PREFS_PMH_RIGHT" value="<?php echo attr($PMH_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_HPI_RIGHT" id="PREFS_HPI_RIGHT" value="<?php echo attr($HPI_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_EXT_RIGHT" id="PREFS_EXT_RIGHT" value="<?php echo attr($EXT_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_ANTSEG_RIGHT" id="PREFS_ANTSEG_RIGHT" value="<?php echo attr($ANTSEG_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_RETINA_RIGHT" id="PREFS_RETINA_RIGHT" value="<?php echo attr($RETINA_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_NEURO_RIGHT" id="PREFS_NEURO_RIGHT" value="<?php echo attr($NEURO_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_IMPPLAN_RIGHT" id="PREFS_IMPPLAN_RIGHT" value="<?php echo attr($IMPPLAN_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_PANEL_RIGHT" id="PREFS_PANEL_RIGHT" value="<?php echo attr($PANEL_RIGHT ?? ''); ?>">
              <input type="hidden" name="PREFS_KB" id="PREFS_KB" value="<?php echo attr($KB_VIEW ?? ''); ?>">
              <input type="hidden" name="PREFS_TOOLTIPS" id="PREFS_TOOLTIPS" value="<?php echo attr($TOOLTIPS ?? ''); ?>">
              <input type="hidden" name="ownership" id="ownership" value="<?php echo attr($ownership ?? ''); ?>">
              <input type="hidden" name="PREFS_ACT_SHOW"  id="PREFS_ACT_SHOW" value="<?php echo attr($ACT_SHOW ?? ''); ?>">
              <input type="hidden" name="COPY_SECTION"  id="COPY_SECTION" value="">
              <input type="hidden" name="UNDO_ID"  id="UNDO_ID" value="<?php echo attr($UNDO_ID ?? ''); ?>">
              <input type="hidden" name="LOCKEDBY" id="LOCKEDBY" value="<?php echo attr($LOCKEDBY ?? ''); ?>">
              <input type="hidden" name="LOCKEDDATE" id="LOCKEDDATE" value="<?php echo attr($LOCKEDDATE ?? ''); ?>">
              <input type="hidden" name="LOCKED"  id="LOCKED" value="<?php echo attr($LOCKED ?? ''); ?>">
              <input type="hidden" name="uniqueID" id="uniqueID" value="<?php echo attr($uniqueID ?? ''); ?>">
              <input type="hidden" name="chart_status" id="chart_status" value="on">
              <input type="hidden" name="finalize"  id="finalize" value="0">
                <input type='hidden' name='setting_tabs_left' id='setting_tabs_left' value='<?php echo attr($setting_tabs_left); ?>'>
                <input type='hidden' name='setting_HPI' id='setting_HPI' value='<?php echo attr($setting_HPI); ?>'>
                <input type='hidden' name='setting_PMH' id='setting_PMH' value='<?php echo attr($setting_PMH); ?>'>
                <input type='hidden' name='setting_EXT' id='setting_EXT' value='<?php echo attr($setting_EXT); ?>'>
                <input type='hidden' name='setting_ANTSEG' id='setting_ANTSEG' value='<?php echo attr($setting_ANTSEG); ?>'>
                <input type='hidden' name='setting_POSTSEG' id='setting_POSTSEG' value='<?php echo attr($setting_POSTSEG); ?>'>
                <input type='hidden' name='setting_NEURO' id='setting_NEURO' value='<?php echo attr($setting_NEURO); ?>'>
                <input type='hidden' name='setting_IMPPLAN' id='setting_IMPPLAN' value='<?php echo attr($setting_IMPPLAN); ?>'>

                <!-- start first div -->
              <div id="first" name="first" class="text_clinical">
                <!-- start    HPI spinner -->
                <div class="loading" id="HPI_sections_loading" name="HPI_sections_loading"><i class="fa fa-spinner fa-spin"></i>
                </div>
                <!-- end      HPI spinner -->
                <?php (($CLINICAL ?? null) == '1') ? ($display_Add = "size100") : ($display_Add = "size50"); ?>
                <?php (($CLINICAL ?? null) == '0') ? ($display_Visibility = "display") : ($display_Visibility = "nodisplay"); ?>
                <!-- start    HPI_PMH row -->
                <div id="HPIPMH_sections" class="nodisplay">
                  <!-- start    HPI_section -->
                  <div id="HPI_1" name="HPI_1" class="<?php echo attr($display_Add); ?>">
                    <span class="anchor" id="HPI_anchor"></span>

                    <!-- start  HPI Left -->
                    <div id="HPI_left" name="HPI_left" class="exam_section_left borderShadow">
                      <div id="HPI_left_text" class="TEXT_class">
                        <span class="closeButton_2 fa fa-paint-brush" title="<?php echo xla('Open/Close the HPI Canvas'); ?>" id="BUTTON_DRAW_HPI" name="BUTTON_DRAW_HPI"></span>
                        <i class="closeButton_3 fa fa-database" title="<?php echo xla('Open/Close the detailed HPI panel'); ?>" id="BUTTON_QP_HPI" name="BUTTON_QP_HPI"></i>
                        <i class="closeButton_4 fa fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open/Close the Shorthand Window and display Shorthand Codes."); ?>"></i>
                          <i class="closeButton fa fa-minus-circle" title="<?php echo xla('Minimize this panel'); ?>" id="BUTTON_TAB_HPI" name="BUTTON_TAB_HPI"></i>

                          <b><?php echo xlt('HPI'); ?>:</b> <i class="fa fa-help"></i><br />
                          <div id="tabs_wrapper" >
                            <div id="tabs_container">
                              <ul id="tabs">
                                <li id="tab0_CC" class="inactive"></li>
                                <li id="tab1_CC" class="active" ><a class="fa fa-check" href="#tab1"> <?php echo xlt('CC{{Chief Complaint}}'); ?> 1</a></li>
                                <li id="tab2_CC"><a <?php if ($CC2 > '') {
                                    echo 'class="fa fa-check"';
                                                    } ?> href="#tab2"><?php echo xlt('CC{{Chief Complaint}}'); ?> 2</a></li>
                                <li id="tab3_CC"><a <?php if ($CC3 > '') {
                                    echo 'class="fa fa-check"';
                                                    } ?> href="#tab3"><?php echo xlt('CC{{Chief Complaint}}'); ?> 3</a></li>
                              </ul>
                            </div>
                            <div id="tabs_content_container" class="borderShadow">
                              <div id="tab1_CC_text" class="tab_content">
                                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td class="pad10" colspan="2">
                                      <div class="kb kb_left">CC</div><b><span title="<?php echo xla('In the patient\'s words'); ?>"><?php echo xlt('Chief Complaint'); ?> 1:
                                      </span>  </b>
                                      <br />
                                      <textarea name="CC1" id="CC1" class="HPI_text" tabindex="10"><?php echo text($CC1); ?></textarea>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="top pad10">
                                      <span id="HPI_HELP" title="<?php echo xla('History of Present Illness: A detailed HPI may be completed by using either four or more HPI elements OR the status of three chronic or inactive problems.'); ?>"><?php echo xlt('HPI'); ?>:
                                        </span><div class="kb kb_left">HPI</div>
                                      <br />
                                      <textarea name="HPI1" id="HPI1" class="HPI_text" tabindex="21"><?php echo text($HPI1); ?></textarea>
                                      <br />
                                    </td>
                                    <td class="top pad10"><span id="CHRONIC_HELP" title="<?php echo xla('Chronic/Inactive Problems') . ":&nbsp\n" . xla('document 3 and their status to reach the detailed HPI level') . "&nbsp\n";
                                    echo "PMH items flagged as Chronic with a comment regarding status will automatically appear here.";?>"><?php echo xlt('Chronic Problems') ?>:</span>
                                      <span class="kb_off"><br /></span><div class="kb kb_right">CHRONIC1</div>
                                      <textarea name="CHRONIC1" id="CHRONIC1" class="HPI_text chronic_HPI" tabindex="22"><?php echo text($CHRONIC1); ?></textarea>
                                      <span class="kb_off"><br /></span><div class="kb kb_right">CHRONIC2</div><textarea name="CHRONIC2" id="CHRONIC2" class="HPI_text chronic_HPI" tabindex="23"><?php echo text($CHRONIC2); ?></textarea>
                                      <span class="kb_off"><br /></span><div class="kb kb_right">CHRONIC3</div><textarea name="CHRONIC3" id="CHRONIC3" class="HPI_text chronic_HPI" tabindex="24"><?php echo text($CHRONIC3); ?></textarea>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td colspan="2" class="center">
                                      <i id="CODE_HIGH_0" name="CODE_HIGH" class="CODE_HIGH fa fa-check nodisplay" value="1"></i>
                                      <span id="CODE_HIGH_HELP">
                                        <span class="detailed_HPI" name=""><?php echo xlt('Detailed HPI') ?>:</span>
                                        <span class="detail_4_elements" name=""><?php echo xlt('> 3 HPI elements'); ?></span> <?php echo xlt('OR{{as in AND/OR, ie. not an abbreviation}}'); ?>
                                        <span class="chronic_3_elements"><?php echo xlt('the status of three chronic/inactive problems'); ?></span>
                                      </span>
                                    </td>
                                  </tr>
                                </table>
                              </div>

                              <div id="tab2_CC_text" class="tab_content">
                                  <table class="CC_table" border="0" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td class="top pad10" colspan="2">
                                        <b><span title="<?php echo xla('In the patient\'s words'); ?>"><?php echo xlt('Chief Complaint'); ?> 2:
                                        </span>  </b>
                                        <br />
                                        <textarea name="CC2" id="CC2" class="HPI_text CC_Box" tabindex="10"><?php echo text($CC2); ?></textarea>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td class="top pad10">
                                        <span class="HPI_TITLE" title="<?php echo xla('History of Present Illness: A detailed HPI may be completed by using either four or more HPI elements OR the status of three chronic or inactive problems.'); ?>"><?php echo xlt('HPI'); ?> 2:
                                        </span>
                                        <br />
                                        <textarea name="HPI2" id="HPI2" class="HPI_text" tabindex="21"><?php echo text($HPI2); ?></textarea>
                                        <br />
                                      </td>
                                    </tr>
                                  </table>
                              </div>
                              <div id="tab3_CC_text" class="tab_content">
                                  <table class="CC_table" border="0" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td class="top pad10" colspan="2">
                                        <b><span title="<?php echo xla('In the patient\'s words'); ?>"><?php echo xlt('Chief Complaint'); ?> 3:
                                        </span>  </b>
                                        <br />
                                        <textarea name="CC3" id="CC3" class="HPI_text CC_Box" tabindex="10"><?php echo text($CC3); ?></textarea>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td class="top pad10">
                                        <span class="HPI_TITLE" title="<?php echo xla('History of Present Illness: A detailed HPI may be completed by using either four or more HPI elements OR the status of three chronic or inactive problems.'); ?>"><?php echo xlt('HPI'); ?> 3:
                                        </span>
                                        <br />
                                        <textarea name="HPI3" id="HPI3" class="HPI_text" tabindex="21"><?php echo text($HPI3); ?></textarea>
                                        <br />
                                      </td>
                                    </tr>
                                  </table>
                              </div>
                            </div>
                          </div>

                        <?php (($HPI_VIEW ?? null) != 2) ? ($display_HPI_view = "wide_textarea") : ($display_HPI_view = "narrow_textarea");?>
                        <?php ($display_HPI_view == "wide_textarea") ? ($marker = "fa-minus-square") : ($marker = "fa-plus-square");?>
                      </div>
                    </div>
                    <!-- end    HPI Left -->

                    <!-- start  HPI Right -->
                    <div id="HPI_right" name="HPI_right" class="exam_section_right borderShadow">
                        <?php display_draw_section("HPI", $encounter, $pid); ?>
                      <!-- start    QP_HPI_Build -->
                      <div id="QP_HPI" name="QP_HPI" class="QP_class left">
                        <div id="HPI_text_list" name="HPI_text_list">
                          <span class="closeButton fa fa-times float-right z100" id="BUTTON_TEXTD_HPI" name="BUTTON_TEXTD_HPI" value="1"></span>
                          <b><?php echo xlt('HPI Elements'); ?>:</b> <br />
                          <div id="tabs_wrapper" >
                            <div id="tabs_container">
                              <ul id="tabs">
                                <li id="tab1_HPI_tab" class="active" ><a type="button" <?php if ($CC1 > '') {
                                    echo 'class="fa fa-check" ';
                                                                                       } ?> href="#tab1"> <?php echo xlt('HPI'); ?> 1</a></li>
                                <li id="tab2_HPI_tab" ><a <?php if ($CC2 > '') {
                                    echo 'class="fa fa-check"';
                                                          } ?> href="#tab2"><?php echo xlt('HPI'); ?> 2</a></li>
                                <li id="tab3_HPI_tab" ><a <?php if ($CC3 > '') {
                                    echo 'class="fa fa-check"';
                                                          } ?> href="#tab3"><?php echo xlt('HPI'); ?> 3</a></li>
                              </ul>
                            </div>
                            <div id="tabs_content_container" class="borderShadow">
                              <div id="tab1_HPI_text" class="tab_content">
                                <table>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Timing'); ?>:</b></td>
                                    <td>
                                      <textarea name="TIMING1" id="TIMING1" class="count_HPI" tabindex="30"><?php echo text($TIMING1); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('When and how often?'); ?></i><br /></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Context'); ?>:</b></td>
                                    <td>
                                      <textarea name="CONTEXT1" id="CONTEXT1" class="count_HPI" tabindex="31"><?php echo text($CONTEXT1); ?></textarea>
                                    </td>
                                    <td>
                                      <i><?php echo xlt('Does it occur in certain situations?'); ?></i>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Severity'); ?>:</b></td>
                                    <td>
                                      <textarea name="SEVERITY1" id="SEVERITY1" class="count_HPI" tabindex="32"><?php echo text($SEVERITY1); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('How bad is it? 0-10, mild, mod, severe?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td  class="right"><b><?php echo xlt('Modifying'); ?>:</b></td>
                                    <td>
                                      <textarea name="MODIFY1" id="MODIFY1" class="count_HPI" tabindex="33"><?php echo text($MODIFY1); ?></textarea>
                                    </td>
                                    <td><i ><?php echo xlt('Does anything make it better? Worse?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Associated'); ?>:</b></td>
                                    <td>
                                      <textarea name="ASSOCIATED1" id="ASSOCIATED1" class="count_HPI" tabindex="34"><?php echo text($ASSOCIATED1); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('Anything else occur at the same time?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Location'); ?>:</b></td>
                                    <td>
                                      <textarea name="LOCATION1" id="LOCATION1" class="count_HPI" tabindex="35"><?php echo text($LOCATION1); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('Where on your body does it occur?'); ?></i>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Quality'); ?>:</b></td>
                                    <td>
                                      <textarea name="QUALITY1" id="QUALITY1" class="count_HPI" tabindex="36"><?php echo text($QUALITY1); ?></textarea>
                                    </td>
                                    <td>
                                      <i><?php echo xlt('eg. aching, burning, radiating pain'); ?></i>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Duration'); ?>:</b></td>
                                    <td><textarea name="DURATION1" id="DURATION1" class="count_HPI" tabindex="37"><?php echo text($DURATION1); ?></textarea>
                                    </td>
                                    <td>
                                        <i><?php echo xlt('How long does it last?'); ?></i>
                                    </td>
                                  </tr>
                                </table>
                                <center>
                                  <i id="CODE_HIGH_1" name="CODE_HIGH" class="CODE_HIGH fa fa-check nodisplay" value="1"></i>
                                  <span id="CODE_HELP_1">
                                    <span class="detailed_HPI"><?php echo xlt('Detailed HPI') ?>:</span>
                                    <span class="detail_4_elements"><?php echo xlt('> 3 HPI elements'); ?></span> <?php echo xlt('OR{{as in AND/OR, ie. not an abbreviation}}'); ?>
                                    <span class="chronic_3_elements"><?php echo xlt('the status of three chronic/inactive problems'); ?></span>
                                  </span>
                                </center>

                              </div>
                              <div id="tab2_HPI_text" class="tab_content">
                                <table>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Timing'); ?>:</b></td>
                                    <td>
                                      <textarea name="TIMING2" id="TIMING2" tabindex="30" class="count_HPI"><?php echo text($TIMING2); ?></textarea>
                                    </td>
                                  </td><td><i><?php echo xlt('When and how often?'); ?></i><br /></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Context'); ?>:</b></td>
                                    <td>
                                      <textarea name="CONTEXT2" id="CONTEXT2" tabindex="31" class="count_HPI"><?php echo text($CONTEXT2); ?></textarea>
                                        <br />
                                    </td>
                                    <td>
                                      <i><?php echo xlt('Does it occur in certain situations?'); ?></i><br />
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Severity'); ?>:</b></td>
                                    <td>
                                      <textarea name="SEVERITY2" id="SEVERITY2" tabindex="32"><?php echo text($SEVERITY2); ?></textarea>
                                      </td>
                                      <td><i><?php echo xlt('How bad is it? 0-10, mild, mod, severe?'); ?></i>
                                      </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Modifying'); ?>:</b></td>
                                    <td>
                                      <textarea name="MODIFY2" id="MODIFY2" tabindex="33"  class="count_HPI"><?php echo text($MODIFY2); ?></textarea>
                                        </td>
                                        <td><i ><?php echo xlt('Does anything make it better? Worse?'); ?></i>
                                        </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Associated'); ?>:</b></td>
                                    <td>
                                      <textarea name="ASSOCIATED2" id="ASSOCIATED2" tabindex="34" class="count_HPI"><?php echo text($ASSOCIATED2); ?></textarea>
                                      </td>
                                      <td><i><?php echo xlt('Anything else occur at the same time?'); ?></i>
                                      </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Location'); ?>:</b></td>
                                    <td>
                                      <textarea name="LOCATION2" id="LOCATION2" tabindex="35" class="count_HPI"><?php echo text($LOCATION2); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('Where on your body does it occur?'); ?></i>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Quality'); ?>:</b></td>
                                    <td>
                                      <textarea name="QUALITY2" id="QUALITY2" tabindex="36" class="count_HPI"><?php echo text($QUALITY2); ?></textarea>

                                    </td><td>
                                    <i><?php echo xlt('eg. aching, burning, radiating pain'); ?></i>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Duration'); ?>:</b></td>
                                    <td><textarea name="DURATION2" id="DURATION2" tabindex="37" class="count_HPI"><?php echo text($DURATION2); ?></textarea>
                                    </td>
                                    <td>
                                      <i><?php echo xlt('How long does it last?'); ?></i>
                                    </td>
                                  </tr>
                                </table>
                                <center>
                                  <i id="CODE_HIGH_2" name="CODE_HIGH" class="CODE_HIGH fa fa-check nodisplay" value="1"></i>
                                  <span id="CODE_HELP_2">
                                    <span class="detailed_HPI"><?php echo xlt('Detailed HPI') ?>:</span>
                                    <span class="detail_4_elements"><?php echo xlt('> 3 HPI elements'); ?></span> <?php echo xlt('OR{{as in AND/OR, ie. not an abbreviation}}'); ?>
                                    <span class="chronic_3_elements"><?php echo xlt('the status of three chronic/inactive problems'); ?></span>
                                  </span>
                                </center>
                              </div>
                              <div id="tab3_HPI_text" class="tab_content">
                                <table>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Timing'); ?>:</b></td>
                                    <td>
                                      <textarea name="TIMING3" id="TIMING3" tabindex="30" class="count_HPI"><?php echo text($TIMING3); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('When and how often?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Context'); ?>:</b></td>
                                    <td>
                                      <textarea name="CONTEXT3" id="CONTEXT3" tabindex="31"  class="count_HPI"><?php echo text($CONTEXT3); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('Does it occur in certain situations?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Severity'); ?>:</b></td>
                                    <td>
                                      <textarea name="SEVERITY3" id="SEVERITY3" tabindex="32" class="count_HPI"><?php echo text($SEVERITY3); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('How bad is it? 0-10, mild, mod, severe?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Modifying'); ?>:</b></td>
                                    <td>
                                      <textarea name="MODIFY3" id="MODIFY3" tabindex="33"  class="count_HPI"><?php echo text($MODIFY3); ?></textarea>
                                    </td>
                                    <td><i ><?php echo xlt('Does anything make it better? Worse?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Associated'); ?>:</b></td>
                                    <td>
                                      <textarea name="ASSOCIATED3" id="ASSOCIATED3" tabindex="34" class="count_HPI"><?php echo text($ASSOCIATED3); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('Anything else occur at the same time?'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Location'); ?>:</b></td>
                                    <td>
                                      <textarea name="LOCATION3" id="LOCATION3" tabindex="35" class="count_HPI"><?php echo text($LOCATION3); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('Where on your body does it occur?'); ?></i>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Quality'); ?>:</b></td>
                                    <td>
                                      <textarea name="QUALITY3" id="QUALITY3" tabindex="36" class="count_HPI"><?php echo text($QUALITY3); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('eg. aching, burning, radiating pain'); ?></i></td>
                                  </tr>
                                  <tr>
                                    <td class="right"><b><?php echo xlt('Duration'); ?>:</b></td>
                                    <td>
                                      <textarea name="DURATION3" id="DURATION3" tabindex="37" class="count_HPI"><?php echo text($DURATION3); ?></textarea>
                                    </td>
                                    <td><i><?php echo xlt('How long does it last?'); ?></i></td>
                                  </tr>
                                </table>
                                <center>
                                  <i id="CODE_HIGH_3" name="CODE_HIGH" class="CODE_HIGH fa fa-check nodisplay" value="1"></i>
                                  <span ID="CODE_HELP_3">
                                    <span class="detailed_HPI"><?php echo xlt('Detailed HPI') ?>:</span>
                                    <span class="detail_4_elements"><?php echo xlt('> 3 HPI elements'); ?></span> <?php echo xlt('OR{{as in AND/OR, ie. not an abbreviation}}'); ?>
                                    <span class="chronic_3_elements"><?php echo xlt('the status of three chronic/inactive problems'); ?></span>
                                  </span>
                                </center>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- end      QP_HPI -->
                    </div>
                    <!-- end    HPI Right -->
                  </div>
                  <!-- end      HPI_section -->
                  <!-- start    PMH_section -->
                  <div id="PMH_1" name="PMH_1" class="<?php echo attr($display_Add); ?> clear_both">
                    <span class="anchor" id="PMH_anchor"></span>
                    <!-- start  PMH Left -->
                    <div id="PMH_left" name="PMH_left" class="exam_section_left borderShadow">
                      <div id="PMH_left_text" class="TEXT_class">
                        <b class="left"><?php echo xlt('PMSFH{{Abbreviation for Past medical Surgical Family and Social History}}'); ?>:</b> <i class="fa fa-help"></i><br />
                          <a class="closeButton_2 fa fa-list" title="<?php echo xla('Toggle the right-sided PMSFH panel'); ?>" id="right-panel-link" name="right-panel-link" href="#right-panel"></a>
                          <i class="closeButton_3 fa fa-paint-brush" title="<?php echo xla('Open/Close the PMH draw panel'); ?>" id="BUTTON_DRAW_PMH" name="BUTTON_DRAW_PMH"></i>
                        <i class="closeButton_4 fa fa-database" title="<?php echo xla('Open/Close the PMSFH panel'); ?>" id="BUTTON_QP_PMH" name="BUTTON_QP_PMH"></i>
                        <i class="closeButton_5 fa fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open/Close the Shorthand Window and display Shorthand Codes"); ?>"></i>
                          <i class="closeButton fa fa-minus-circle" title="<?php echo xla('Minimize this panel'); ?>" id="BUTTON_TAB_PMH" name="BUTTON_TAB_PMH"></i>

                        <?php (($PMH_VIEW ?? null) != 2) ? ($display_PMH_view = "wide_textarea") : ($display_PMH_view = "narrow_textarea");?>
                        <?php ($display_PMH_view == "wide_textarea") ? ($marker = "fa-minus-square") : ($marker = "fa-plus-square");?>
                        <div id="PMSFH_sections" name="PMSFH_sections">
                          <div id="Enter_PMH" name="Enter_PMH" class="PMH_class">
                              <iframe id="iframe" name="iframe"
                                src="../../forms/eye_mag/a_issue.php?uniqueID=<?php echo attr_url($uniqueID); ?>&form_type=POH&pid=<?php echo attr_url($pid); ?>&encounter=<?php echo attr_url($encounter); ?>&form_id=<?php echo attr_url($form_id); ?>"
                                width="510" height="363" scrolling= "yes" frameBorder= "0" >
                              </iframe>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- end    PMH Left -->
                    <!-- start  PMH Right -->
                    <div id="PMH_right" name="PMH_right" class="exam_section_right borderShadow">
                      <a class="nodisplay left_PMSFH_tab" id="right-panel-link" href="#right-panel">
                        <img src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/images/PMSFHx.png">
                      </a>
                      <?php display_draw_section("PMH", $encounter, $pid); ?>
                      <div id="QP_PMH" name="QP_PMH" class="QP_class" style="max-height:100%">
                        <?php echo display_PRIOR_section("PMSFH", $id, $id, $pid); ?>
                      </div>
                    </div>
                    <!-- end    PMH Right -->
                  </div>
                  <!-- end      PMH_section -->
                </div>
                <!-- end    HPI_PMH row -->
              </div>
              <!-- end first div -->

              <div id="clinical_anchor" name="clinical_anchor" class="clear_both"></div>
              <br />

              <!-- start of the CLINICAL BOX -->
                <?php
                $display_W_1 = "nodisplay";
                $display_W_2 = "nodisplay";
                $display_W_3 = "nodisplay";
                $display_W_4 = "nodisplay";
                $RX_count = '1';

                $query = "select * from form_eye_mag_wearing where PID=? and FORM_ID=? and ENCOUNTER=? ORDER BY RX_NUMBER";
                $wear = sqlStatement($query, array($pid,$form_id,$encounter));
                while ($wearing = sqlFetchArray($wear)) {
                    if (!empty($count_rx)) {
                        $count_rx++;
                    } else {
                        $count_rx = 1;
                    }
                    ${"display_W_$count_rx"}        = '';
                    ${"ODSPH_$count_rx"}            = $wearing['ODSPH'];
                    ${"ODCYL_$count_rx"}            = $wearing['ODCYL'];
                    ${"ODAXIS_$count_rx"}           = $wearing['ODAXIS'];
                    ${"OSSPH_$count_rx"}            = $wearing['OSSPH'];
                    ${"OSCYL_$count_rx"}            = $wearing['OSCYL'];
                    ${"OSAXIS_$count_rx"}           = $wearing['OSAXIS'];
                    ${"ODMIDADD_$count_rx"}         = $wearing['ODMIDADD'];
                    ${"OSMIDADD_$count_rx"}         = $wearing['OSMIDADD'];
                    ${"ODADD_$count_rx"}            = $wearing['ODADD'];
                    ${"OSADD_$count_rx"}            = $wearing['OSADD'];
                    ${"ODVA_$count_rx"}             = $wearing['ODVA'];
                    ${"OSVA_$count_rx"}             = $wearing['OSVA'];
                    ${"ODNEARVA_$count_rx"}         = $wearing['ODNEARVA'];
                    ${"OSNEARVA_$count_rx"}         = $wearing['OSNEARVA'];
                    ${"ODPRISM_$count_rx"}          = $wearing['ODPRISM'] ?? null;
                    ${"OSPRISM_$count_rx"}          = $wearing['OSPRISM'] ?? null;
                    ${"W_$count_rx"}                = '1';
                    ${"RX_TYPE_$count_rx"}          = $wearing['RX_TYPE'];
                    ${"ODHPD_$count_rx"}            = $wearing['ODHPD'];
                    ${"ODHBASE_$count_rx"}          = $wearing['ODHBASE'];
                    ${"ODVPD_$count_rx"}            = $wearing['ODVPD'];
                    ${"ODVBASE_$count_rx"}          = $wearing['ODVBASE'];
                    ${"ODSLABOFF_$count_rx"}        = $wearing['ODSLABOFF'];
                    ${"ODVERTEXDIST_$count_rx"}     = $wearing['ODVERTEXDIST'];
                    ${"OSHPD_$count_rx"}            = $wearing['OSHPD'];
                    ${"OSHBASE_$count_rx"}          = $wearing['OSHBASE'];
                    ${"OSVPD_$count_rx"}            = $wearing['OSVPD'];
                    ${"OSVBASE_$count_rx"}          = $wearing['OSVBASE'];
                    ${"OSSLABOFF_$count_rx"}        = $wearing['OSSLABOFF'];
                    ${"OSVERTEXDIST_$count_rx"}     = $wearing['OSVERTEXDIST'];
                    ${"ODMPDD_$count_rx"}           = $wearing['ODMPDD'];
                    ${"ODMPDN_$count_rx"}           = $wearing['ODMPDN'];
                    ${"OSMPDD_$count_rx"}           = $wearing['OSMPDD'];
                    ${"OSMPDN_$count_rx"}           = $wearing['OSMPDN'];
                    ${"BPDD_$count_rx"}             = $wearing['BPDD'];
                    ${"BPDN_$count_rx"}             = $wearing['BPDN'];
                    ${"LENS_MATERIAL_$count_rx"}    = $wearing['LENS_MATERIAL'];
                    ${"LENS_TREATMENTS_$count_rx"}  = $wearing['LENS_TREATMENTS'];
                    ${"COMMENTS_$count_rx"}         = $wearing['COMMENTS'];
                }
                ?>
              <div class="loading" id="LayerTechnical_sections_loading" name="LayerTechnical_sections_loading"><i class="fa fa-spinner fa-spin"></i>
              </div>
              <div class="clear_both row" id="LayerTechnical_sections_1" name="LayerTechnical_sections" >
                <!-- start of the Mood BOX -->
                <div id="LayerMood" class="vitals">
                  <div id="Lyr2.9" class="top_left">
                    <th class="text_clinical" nowrap><b id="MS_tab"><?php echo xlt('Mental Status'); ?>:</b></th>
                  </div>
                  <br />
                  <input type="checkbox" name="alert" id="alert" <?php if ($alert) {
                        echo "checked='checked'";
                                                                 } ?> value="1">
                  <label for="alert" class="input-helper input-helper--checkbox"><?php echo xlt('Alert{{Mental Status}}'); ?></label><br />
                  <input type="checkbox" name="oriented" id="oriented" <?php if ($oriented) {
                        echo "checked='checked'";
                                                                       } ?> value="1">
                  <label for="oriented" class="input-helper input-helper--checkbox"><?php echo xlt('Oriented TPP{{oriented to person and place}}'); ?></label><br />
                  <input type="checkbox" name="confused" id="confused" <?php if ($confused) {
                        echo "checked='checked'";
                                                                       } ?> value="1">
                  <label for="confused" class="input-helper input-helper--checkbox"><?php echo xlt('Mood/Affect Nml{{Mood and affect normal}}'); ?></label><br />

                </div>
                <!-- end of the Mood BOX -->

                <!-- start of the VISION BOX -->
                <div id="LayerVision" class="vitals">
                  <div id="Lyr30" class="top_left">
                    <th class="text_clinical"><b id="vision_tab" title="Show/hide the refraction panels"><?php echo xlt('Vision'); ?>:</b></th>
                  </div>
                        <?php
                                              //if the prefs show a field, ie visible, the highlight the zone.
                        if (($W ?? null) == '1') {
                            $button_W = "buttonRefraction_selected";
                        }

                        if (($MR ?? null) == '1') {
                            $button_MR = "buttonRefraction_selected";
                        }

                        if (($CR ?? null) == '1') {
                            $button_AR = "buttonRefraction_selected";
                        }

                        if (($CTL ?? null) == '1') {
                            $button_CTL = "buttonRefraction_selected";
                        }

                        if (($ADDITIONAL ?? null) == '1') {
                            $button_ADDITIONAL = "buttonRefraction_selected";
                        }

                        if (($VAX ?? null) == '1') {
                            $button_VAX = "buttonRefraction_selected";
                        }
                        if (($RXHX ?? null) == '1') {
                            $button_RXHX = "buttonRefraction_selected";
                        }
                        ?>
                  <div class="top_right">
                          <span id="tabs">
                              <ul>
                                  <li id="LayerVision_RXHX_lightswitch" class="<?php echo attr($button_RXHX ?? ''); ?>" value="Prior Refractions" title="<?php echo xla("Show the last three Refractions"); ?>"><?php echo xlt('R{{History of Refraction}}'); ?></li> |
                                  <li id="LayerVision_W_lightswitch" class="<?php echo attr($button_W ?? ''); ?>" value="Current" title="<?php echo xla("Display the patient's current glasses"); ?>"><?php echo xlt('W{{Current Rx - wearing}}'); ?></li> |
                                  <li id="LayerVision_MR_lightswitch" class="<?php echo attr($button_MR ?? ''); ?>" value="Auto" title="<?php echo xla("Display the Manifest Refraction panel"); ?>"><?php echo xlt('MR{{Manifest Refraction}}'); ?></li> |
                                  <li id="LayerVision_CR_lightswitch" class="<?php echo attr($button_AR ?? ''); ?>" value="Cyclo" title="<?php echo xla("Display the Autorefraction Panel"); ?>"><?php echo xlt('AR{{autorefraction}}'); ?></li> |
                                  <li id="LayerVision_CTL_lightswitch" class="<?php echo attr($button_CTL ?? ''); ?>" value="Contact Lens" title="<?php echo xla("Display the Contact Lens Panel"); ?>"><?php echo xlt('CTL{{Contact Lens}}'); ?></li> |
                                  <li id="LayerVision_ADDITIONAL_lightswitch" class="<?php echo attr($button_ADDITIONAL ?? ''); ?>" value="Additional" title="<?php echo xla("Display Additional measurements (Ks, IOL cals, etc)"); ?>"><?php echo xlt('Add.{{Additional Measurements}}'); ?></li> |
                                  <li id="LayerVision_VAX_lightswitch" class="<?php echo attr($button_VAX ?? ''); ?>" value="Visual Acuities" title="<?php echo xla("Summary of Acuities for this patient"); ?>"><?php echo xlt('Va{{Visual Acuities}}'); ?></li>
                              </ul>
                          </span>
                  </div>

                  <div id="Lyr31">
                    <?php echo xlt('V{{One letter abbrevation for Vision}}'); ?>
                    </div>
                  <div id="Visions_A" name="Visions_A">
                      <b>OD</b>
                      <input type="TEXT" tabindex="40" id="SCODVA" name="SCODVA" value="<?php echo attr($SCODVA); ?>">
                      <input type="TEXT" tabindex="42" id="ODVA_1_copy" name="ODVA_1_copy" value="<?php echo attr($ODVA_1 ?? ''); ?>">
                      <input type="TEXT" tabindex="44" id="PHODVA_copy" name="PHODVA_copy" value="<?php echo attr($PHODVA ?? ''); ?>">
                      <br />
                      <b>OS</b>
                      <input type="TEXT" tabindex="41" id="SCOSVA" name="SCOSVA" value="<?php echo attr($SCOSVA ?? ''); ?>">
                      <input type="TEXT" tabindex="43" id="OSVA_1_copy" name="OSVA_1_copy" value="<?php echo attr($OSVA_1 ?? ''); ?>">
                      <input type="TEXT" tabindex="45" id="PHOSVA_copy" name="PHOSVA_copy" value="<?php echo attr($PHOSVA ?? ''); ?>">
                      <br />
                      <span id="more_visions_1" name="more_visions_1"><b><?php echo xlt('Acuity'); ?></b> </span>
                      <span><b><?php echo xlt('SC{{without correction}}'); ?></b></span>
                      <span><b><?php echo xlt('CC{{with correction}}'); ?></b></span>
                      <span><b><?php echo xlt('PH{{pinhole acuity}}'); ?></b></span>
                  </div>
                  <div id="Visions_B" name="Visions_B" class="nodisplay">
                      <b><?php echo xlt('OD'); ?> </b>
                      <input type="TEXT" tabindex="46" id="ARODVA_copy" name="ARODVA_copy" value="<?php echo attr($ARODVA); ?>">
                      <input type="TEXT" tabindex="48" id="MRODVA_copy" name="MRODVA_copy" value="<?php echo attr($MRODVA); ?>">
                      <input type="TEXT" tabindex="50" id="CRODVA_copy" name="CRODVA_copy" value="<?php echo attr($CRODVA); ?>">
                      <br />
                      <b><?php echo xlt('OS'); ?> </b>
                      <input type="TEXT" tabindex="47" id="AROSVA_copy" name="AROSVA_copy" value="<?php echo attr($AROSVA); ?>">
                      <input type="TEXT" tabindex="49" id="MROSVA_copy" name="MROSVA_copy" value="<?php echo attr($MROSVA); ?>">
                      <input type="TEXT" tabindex="51" id="CROSVA_copy" name="CROSVA_copy" value="<?php echo attr($CROSVA); ?>">
                      <br />
                      <span id="more_visions_2" name="more_visions_2"><b><?php echo xlt('Acuity'); ?></b> </span>
                      <span><b><?php echo xlt('AR{{Autorefraction Acuity}}'); ?></b></span>
                      <span><b><?php echo xlt('MR{{Manifest Refraction}}'); ?></b></span>
                      <span><b><?php echo xlt('CR{{Cycloplegic Refraction}}'); ?></b></span>
                  </div>
                </div>
                <!-- end of the VISION BOX -->

                <!-- START OF THE PRESSURE BOX -->
                <div id="LayerTension" class="vitals">

                      <span title="Display the Glaucoma Flow Sheet" id="LayerVision_IOP_lightswitch" name="LayerVision_IOP_lightswitch" class="closeButton fa fa-chart-line" id="IOP_Graph" name="IOP_Graph"></span>
                      <!-- -->
                      <div id="Lyr40">
                          <span class="top_left">
                              <b id="tension_tab"><?php echo xlt('Tension'); ?>:</b>
                              <div>
                                    <?php
                                    if (($IOPTIME == '00:00:00') || (!$IOPTIME)) {
                                        $IOPTIME =  date('G:i A');
                                    }

                                    $show_IOPTIME = date('g:i A', strtotime($IOPTIME));
                                    ?>
                                  <input type="text" name="IOPTIME" id="IOPTIME" tabindex="-1" value="<?php echo attr($show_IOPTIME); ?>">

                              </div>
                          </span>
                      </div>
                      <div id="Lyr41">
                            <?php echo xlt('T{{one letter abbreviation for Tension/Pressure}}'); ?>
                      </div>
                      <div id="Lyr42">
                          <b><?php echo xlt('OD{{right eye}}'); ?></b>
                          <input type="text" tabindex="52" name="ODIOPAP" id="ODIOPAP" value="<?php echo attr($ODIOPAP); ?>">
                          <input type="text" tabindex="54" name="ODIOPTPN" id="ODIOPTPN" value="<?php echo attr($ODIOPTPN); ?>">
                          <input type="text" name="ODIOPFTN" id="ODIOPFTN" value="<?php echo attr($ODIOPFTN); ?>">
                          <br />
                          <b><?php echo xlt('OS{{left eye}}'); ?> </b>
                          <input type="text" tabindex="53" name="OSIOPAP" id="OSIOPAP" value="<?php echo attr($OSIOPAP); ?>">
                          <input type="text" tabindex="55" name="OSIOPTPN" id="OSIOPTPN" value="<?php echo attr($OSIOPTPN); ?>">
                          <input type="text" name="OSIOPFTN" id="OSIOPFTN" value="<?php echo attr($OSIOPFTN); ?>">
                          <br /><br />
                          <span name="IOP_AP"><b><?php echo xlt('AP{{applanation}}'); ?></b></span>
                          <span name="IOP_TPN"><b><?php echo xlt('TP{{tonopen}}'); ?></b></span>
                          <span name="IOP_FT"><b><?php echo xlt('FT{{finger tension}}'); ?></b></span>
                      </div>
                </div>
                <!-- END OF THE PRESSURE BOX -->

                <!-- start of the Amsler box -->
                <div id="LayerAmsler" class="vitals">
                    <div id="Lyr50">
                        <span class="top_left">
                            <b><?php echo xlt('Amsler'); ?>:</b>
                        </span>
                    </div>
                    <?php
                    if (!$AMSLEROD) {
                        $AMSLEROD = "0";
                    }

                    if (!$AMSLEROS) {
                        $AMSLEROS = "0";
                    }

                    if ($AMSLEROD || $AMSLEROS) {
                        $checked = 'value="0"';
                    } else {
                        $checked = 'value="1" checked';
                    }

                    ?>
                    <input type="hidden" id="AMSLEROD" name="AMSLEROD" value='<?php echo attr($AMSLEROD); ?>'>
                    <input type="hidden" id="AMSLEROS" name="AMSLEROS" value='<?php echo attr($AMSLEROS); ?>'>

                    <div id="Lyr501">
                        <label for="Amsler-Normal" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                        <input id="Amsler-Normal" type="checkbox" <?php echo attr($checked); ?> tabindex="56">
                    </div>
                    <div id="Lyr51">
                        <table cellpadding=0 cellspacing=0>
                            <tr>
                                <td colspan=3 class="center"><b><?php echo xlt('OD{{right eye}}'); ?></b>
                                </td>
                                <td></td>
                                <td colspan=3 class="center"><b><?php echo xlt('OS{{left eye}}'); ?></b>
                                </td>
                            </tr>

                            <tr>
                                <td colspan=3>
                                    <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROD); ?>.jpg" id="AmslerOD" /></td>
                                <td></td>
                                <td colspan=3>
                                    <img src="../../forms/<?php echo $form_folder; ?>/images/Amsler_<?php echo attr($AMSLEROS); ?>.jpg" id="AmslerOS" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan=3 class="center">
                                    <div class="AmslerValue">
                                        <span id="AmslerODvalue"><?php echo text($AMSLEROD); ?></span>/5
                                    </div>
                                </td>
                                <td></td>
                                <td colspan=3 style="text-align:center;">
                                    <div class="AmslerValue">
                                        <span id="AmslerOSvalue"><?php echo text($AMSLEROS); ?></span>/5
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- end of the Amsler box -->

                <!-- start of the Fields box -->
                <div id="LayerFields" class="vitals">
                    <div>
                        <span class="top_left"><b id="fields"><?php echo xlt('Fields{{visual fields}}'); ?>:</b></span>
                    </div>
                        <?php
                            // if the VF zone is checked, display it
                            // if ODVF1 = 1 (true boolean) the value="0" checked="true"
                            $bad = 0;
                        for ($z = 1; $z < 5; $z++) {
                            $ODzone = "ODVF" . $z;
                            if ($$ODzone == '1') {
                                $ODVF[$z] = 'checked value=1';
                                $bad++;
                            } else {
                                $ODVF[$z] = 'value=0';
                            }

                            $OSzone = "OSVF" . $z;
                            if ($$OSzone == "1") {
                                $OSVF[$z] = 'checked value=1';
                                $bad++;
                            } else {
                                $OSVF[$z] = 'value=0';
                            }
                        }

                        if (!$bad) {
                            $VFFTCF = "checked";
                        }
                        ?>
                    <div id="Lyr60">
                                <label for="FieldsNormal" class="input-helper input-helper--checkbox"><?php echo xlt('FTCF{{Full to count fingers}}'); ?></label>
                                <input id="FieldsNormal" type="checkbox" value="1" <?php echo attr($VFFTCF ?? ''); ?>>
                    </div>
                    <div id="Lyr511">
                        <table cellpadding="1" cellspacing="1">
                            <tr>
                                <td class="center" colspan="2"><b><?php echo xlt('OD{{right eye}}'); ?></b><br /></td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td class="center" colspan="2"><b><?php echo xlt('OS{{left eye}}'); ?></b></td>
                            </tr>
                            <tr>
                                <td class="VF_1">
                                    <input name="ODVF1" id="ODVF1" type="checkbox" <?php echo attr($ODVF['1'])?> class="hidden">
                                    <label for="ODVF1" class="input-helper input-helper--checkbox boxed"></label>
                                </td>
                                <td class="VF_2">
                                    <input name="ODVF2" id="ODVF2" type="checkbox" <?php echo attr($ODVF['2'])?> class="hidden">
                                    <label for="ODVF2" class="input-helper input-helper--checkbox boxed"></label>
                                </td>
                                <td></td>
                                <td class="VF_1">
                                    <input name="OSVF1" id="OSVF1" type="checkbox" <?php echo attr($OSVF['1']); ?> class="hidden" >
                                    <label for="OSVF1" class="input-helper input-helper--checkbox boxed"></label>
                                </td>
                                <td class="VF_2">
                                    <input name="OSVF2" id="OSVF2" type="checkbox" <?php echo attr($OSVF['2']); ?> class="hidden">
                                    <label for="OSVF2" class="input-helper input-helper--checkbox boxed"> </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="VF_3">
                                    <input name="ODVF3" id="ODVF3" type="checkbox"  class="hidden" <?php echo attr($ODVF['3']); ?>>
                                    <label for="ODVF3" class="input-helper input-helper--checkbox boxed"></label>
                                </td>
                                <td class="VF_4">
                                    <input  name="ODVF4" id="ODVF4" type="checkbox"  class="hidden" <?php echo attr($ODVF['4']); ?>>
                                    <label for="ODVF4" class="input-helper input-helper--checkbox boxed"></label>
                                </td>
                                <td></td>
                                <td class="VF_3">
                                    <input name="OSVF3" id="OSVF3" type="checkbox"  class="hidden" <?php echo attr($OSVF['3']); ?>>
                                    <label for="OSVF3" class="input-helper input-helper--checkbox boxed"></label>
                                </td>
                                <td class="VF_4">
                                    <input name="OSVF4" id="OSVF4" type="checkbox"  class="hidden" <?php echo attr($OSVF['4']); ?>>
                                    <label for="OSVF4" class="input-helper input-helper--checkbox boxed"></label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- end of the Fields box -->

                <!-- start of the Pupils box -->
                <div id="LayerPupils" class="vitals">
                  <span class="top_left"><b id="pupils"><?php echo xlt('Pupils'); ?>:</b> </span>
                  <div id="Lyr701">
                              <label for="PUPIL_NORMAL" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                              <input id="PUPIL_NORMAL" name="PUPIL_NORMAL" type="checkbox"  <?php if ($PUPIL_NORMAL == '1') {
                                    echo 'checked="checked" value="1"';
                                                                                            } ?>>
                  </div>
                  <div id="Lyr70">
                    <table>
                      <tr>
                          <th> &nbsp;
                          </th>
                          <th><?php echo xlt('size'); ?> (<?php echo xlt('mm{{millimeters}}'); ?>)
                          </th>
                          <th><?php echo xlt('react{{reactivity}}'); ?>
                          </th>
                          <th><?php echo xlt('APD{{afferent pupillary defect}}'); ?>
                          </th>
                      </tr>
                      <tr>
                          <td><b><?php echo xlt('OD{{right eye}}'); ?></b>
                          </td>
                          <td class="pupil_1">
                              <input type="text" id="ODPUPILSIZE1" name="ODPUPILSIZE1" value="<?php echo attr($ODPUPILSIZE1); ?>">
                              <font>&#8594;</font>
                              <input type="text" id="ODPUPILSIZE2" size="1" name="ODPUPILSIZE2" value="<?php echo attr($ODPUPILSIZE2); ?>">
                          </td>
                          <td class="pupil_2">
                              <input type="text" class="pupil_input_2" name='ODPUPILREACTIVITY' id='ODPUPILREACTIVITY' value='<?php echo attr($ODPUPILREACTIVITY); ?>'>
                          </td>
                          <td class="pupil_3">
                              <input type="text" class="pupil_input_2" name="ODAPD" id='ODAPD' value='<?php echo attr($ODAPD); ?>'>
                          </td>
                      </tr>
                      <tr>
                          <td><b><?php echo xlt('OS{{left eye}}'); ?></b>
                          </td>
                          <td class="pupil_4">
                              <input type="text" size="1" name="OSPUPILSIZE1" id="OSPUPILSIZE1" class="pupil_input" value="<?php echo attr($OSPUPILSIZE1); ?>">
                              <font>&#8594;</font>
                              <input type="text" size="1" name="OSPUPILSIZE2" id="OSPUPILSIZE2" class="pupil_input" value="<?php echo attr($OSPUPILSIZE2); ?>">
                          </td>
                          <td class="pupil_5">
                              <input type="text" class="pupil_input_2" name='OSPUPILREACTIVITY' id='OSPUPILREACTIVITY' value="<?php echo attr($OSPUPILREACTIVITY); ?>">
                          </td>
                          <td class="pupil_6">
                              <input type="text" class="pupil_input_2" name="OSAPD" id="OSAPD" value='<?php echo attr($OSAPD); ?>'>
                          </td>
                      </tr>
                    </table>
                  </div>
                </div>
                <!-- end of the Pupils box -->

                <br />

                <!-- start of slide down pupils_panel -->
                <?php (($DIMODPUPILSIZE ?? '') != '') ? ($display_dim_pupils_panel = "display") : ($display_dim_pupils_panel = "nodisplay"); ?>
                <div id="dim_pupils_panel" name="dim_pupils_panel" class="vitals <?php echo attr($display_dim_pupils_panel); ?>">
                  <span class="top_left"><b id="pupils_DIM"><?php echo xlt('Pupils') ?>: <?php echo xlt('Dim'); ?></b> </span>
                  <div id="Lyr71">
                    <table>
                      <tr>
                          <th></th>
                          <th><?php echo xlt('size'); ?> (<?php echo xlt('mm{{millimeters}}'); ?>)</th>
                      </tr>
                      <tr>
                          <td><b><?php echo xlt('OD{{right eye}}'); ?></b>
                          </td>
                          <td claa="border-bottom">
                              <input type="text" id ="DIMODPUPILSIZE1" name="DIMODPUPILSIZE1" value='<?php echo attr($DIMODPUPILSIZE1); ?>'>
                              <font>&#8594;</font>
                              <input type="text" id ="DIMODPUPILSIZE2"name="DIMODPUPILSIZE2" value='<?php echo attr($DIMODPUPILSIZE2); ?>'>
                          </td>
                      </tr>
                      <tr>
                          <td ><b><?php echo xlt('OS{{left eye}}'); ?></b>
                          </td>
                          <td class="border-top">
                              <input type="text" name="DIMOSPUPILSIZE1" id="DIMOSPUPILSIZE1" value="<?php echo attr($DIMOSPUPILSIZE1); ?>">
                              <font>&#8594;</font>
                              <input type="text" name="DIMOSPUPILSIZE2" id="DIMOSPUPILSIZE2" value="<?php echo attr($DIMOSPUPILSIZE2); ?>">
                          </td>
                      </tr>
                    </table>
                  </div>
                  <div class="pupil_dim_comments">
                      <b><?php echo xlt('Comments'); ?>:</b><br />
                      <textarea id="PUPIL_COMMENTS" name="PUPIL_COMMENTS"><?php echo text($PUPIL_COMMENTS); ?></textarea>
                  </div>
                </div>
                <!-- end of slide down pupils_panel -->
              </div>
              <!-- end of the CLINICAL BOX -->

                <!-- start IOP chart section -->
                <?php (($IOP ?? null) == 1) ? ($display_IOP = "") : ($display_IOP = "nodisplay"); ?>
              <div id="LayerVision_IOP" class="borderShadow <?php echo $display_IOP; ?>">
                    <?php echo display_GlaucomaFlowSheet($pid); ?>
              </div>
              <!-- end IOP chart section -->

              <!-- start of the refraction box -->
              <span class="anchor" id="REFRACTION_anchor"></span>
              <div class="loading" id="EXAM_sections_loading" name="REFRACTION_sections_loading"><i class="fa fa-spinner fa-spin"></i></div>
              <div id="REFRACTION_sections" name="REFRACTION_sections" class="row nodisplay clear_both">
                <div id="LayerVision2">
                    <?php (($RXHX ?? null) == 1) ? ($display_Add = "") : ($display_Add = "nodisplay"); ?>
                    <div id="LayerVision_RXHX" class="refraction borderShadow old_refractions ui-draggable ui-draggable-handle <?php echo $display_Add; ?>">
                        <span title="<?php echo attr('Close this panel and make this a Preference to stay closed'); ?>" class="closeButton fa  fa-times" id="Close_RXHX" name="Close_RXHX"></span>
                        <table class="GFS_table">
                            <tr>
                                <th class="text-center"><?php echo xlt('Prior Refractions'); ?></th>
                            </tr>
                        </table>


                        <div id="PRIORS_REFRACTIONS_left_text" name="PRIORS_REFRACTIONS_left_text">
                            <?php
                                $sql = "SELECT id FROM form_eye_acuity WHERE
                                        pid=? AND id < ? AND
                                        ( MRODVA  <> '' OR
                                          MROSVA  <> '' OR
                                          ARODVA  <> '' OR
                                          AROSVA  <> '' OR
                                          CRODVA  <> '' OR
                                          CROSVA  <> '' OR
                                          CTLODVA <> '' OR
                                          CTLOSVA <> ''
                                        )
                                        ORDER BY id DESC LIMIT 3";
                                $result = sqlStatement($sql, array($pid, $id));
                            while ($visit = sqlFetchArray($result)) {
                                echo display_PRIOR_section('REFRACTIONS', $visit['id'], $visit['id'], $pid);
                            }
                                //display_PRIOR_section('REFRACTIONS', $id, $id, $pid, '1');
                            ?>
                        </div>

                    </div>
                    <?php
                    (($W ?? null) == 1) ? ($display_W = "") : ($display_W = "nodisplay");
                    (($W_width ?? null) == '1') ? ($display_W_width = "refraction_wide") : ($display_W_width = "");
                    ?>
                  <div id="LayerVision_W" class="<?php echo $display_W; ?> ">
                    <input type="hidden" id="W_1" name="W_1" value="1">
                    <div id="LayerVision_W_1" name="currentRX" class="refraction current_W borderShadow <?php echo $display_W_width; ?>">
                      <i class="closeButton fa fa-times" id="Close_W_1" name="Close_W_1"
                        title="<?php echo xla('Close All Current Rx Panels and make this a Preference to stay closed'); ?>"></i>
                      <i class="closeButton_2 fas fa-arrows-alt-h" id="W_width_display_1" name="W_width_display"
                        title="<?php echo xla("Rx Details"); ?>" ></i>
                      <i onclick="top.restoreSession();  doscript('W','<?php echo attr($pid); ?>','<?php echo attr($encounter); ?>','1'); return false;"
                        title="<?php echo xla("Dispense this Rx"); ?>" class="closeButton_3 fa fa-print"></i>
                      <i onclick="top.restoreSession();  dispensed('<?php echo attr($pid); ?>');return false;"
                         title="<?php echo xla("List of previously dispensed Spectacle and Contact Lens Rxs"); ?>" class="closeButton_4 fa fa-list-ul"></i>
                      <table id="wearing_1">
                        <tr>
                          <th colspan="7"><?php echo xlt('Current Glasses'); ?>: #1
                            <i id="Add_Glasses" name="Add_Glasses" class="button btn"><?php echo xlt('Additonal Rx{{Additional glasses}}'); ?></i>
                          </th>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                          <td><i class="fa fa-gamepad" name="reverseme" title="<?php echo xla('Convert between plus and minus cylinder'); ?>" aria-hidden="true" id="revW1" ></i></td>
                          <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                          <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                          <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                          <td><?php echo xlt('Acuity'); ?></td>
                          <td name="W_wide"></td>
                          <td name="W_wide" title="<?php echo xla('Horizontal Prism Power'); ?>"><?php echo xlt('HP{{abbreviation for Horizontal Prism Power}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Horizontal Prism Base'); ?>"><?php echo xlt('HB{{abbreviation for Horizontal Prism Base}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Vertical Prism Power'); ?>"><?php echo xlt('VP{{abbreviation for Vertical Prism Power}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Vertical Prism Base'); ?>"><?php echo xlt('VB{{abbreviation for Vertical Prism Base}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Slab Off'); ?>"><?php echo xlt('Slab Off'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Vertex Distance'); ?>"><?php echo xlt('VD{{abbreviation for Vertex Distance}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Monocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('MPD-D{{abbreviation for Monocular Pupillary Diameter - Distance}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Monocular Pupillary Diameter - Near'); ?>"><?php echo xlt('MPD-N{{abbreviation for Monocular Pupillary Diameter - Near}}'); ?></td>

                          <td rowspan="6" class="right">
                            <span class="underline bold"><?php echo xlt('Rx Type{{Type of glasses prescription}}'); ?></span><br />
                            <label for="Single_1" class="input-helper input-helper--checkbox"><?php echo xlt('Single'); ?></label>
                            <input type="radio" value="0" id="Single_1" name="RX_TYPE_1" <?php if (($RX_TYPE_1 ?? null) == '0') {
                                echo 'checked="checked"';
                                                                                         } ?> /></span><br />
                            <label for="Bifocal_1" class="input-helper input-helper--checkbox"><?php echo xlt('Bifocal'); ?></label>
                            <input type="radio" value="1" id="Bifocal_1" name="RX_TYPE_1" <?php if (($RX_TYPE_1 ?? null) == '1') {
                                echo 'checked="checked"';
                                                                                          } ?> /></span><br />
                            <label for="Trifocal_1" class="input-helper input-helper--checkbox"><?php echo xlt('Trifocal'); ?></label>
                            <input type="radio" value="2" id="Trifocal_1" name="RX_TYPE_1" <?php if (($RX_TYPE_1 ?? null) == '2') {
                                echo 'checked="checked"';
                                                                                           } ?> /></span><br />
                            <label for="Progressive_1" class="input-helper input-helper--checkbox"><?php echo xlt('Prog.{{Progressive lenses}}'); ?></label>
                            <input type="radio" value="3" id="Progressive_1" name="RX_TYPE_1" <?php if (($RX_TYPE_1 ?? null) == '3') {
                                echo 'checked="checked"';
                                                                                              } ?> /></span><br />
                          </td>
                        </tr>
                        <tr>
                          <td rowspan="2"><?php echo xlt('Dist{{distance}}'); ?></td>
                          <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                          <td><input type="text" class="sphere" id="ODSPH_1" name="ODSPH_1"  value="<?php echo attr($ODSPH_1 ?? ''); ?>" tabindex="100"></td>
                          <td><input type="text" class="cylinder" id="ODCYL_1" name="ODCYL_1"  value="<?php echo attr($ODCYL_1 ?? ''); ?>" tabindex="101"></td>
                          <td><input type="text" class="axis" id="ODAXIS_1" name="ODAXIS_1" value="<?php echo attr($ODAXIS_1 ?? ''); ?>" tabindex="102"></td>
                          <td><input type="text" class="acuity" id="ODVA_1" name="ODVA_1" value="<?php echo attr($ODVA_1 ?? ''); ?>" tabindex="108"></td>

                          <td name="W_wide"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODHPD_1" name="ODHPD_1" value="<?php echo attr($ODHPD_1 ?? ''); ?>" tabindex="122"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODHBASE_1" name="ODHBASE_1" value="<?php echo attr($ODHBASE_1 ?? ''); ?>" tabindex="124"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODVPD_1" name="ODVPD_1" value="<?php echo attr($ODVPD_1 ?? ''); ?>" tabindex="126"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODVBASE_1" name="ODVBASE_1" value="<?php echo attr($ODVBASE_1 ?? ''); ?>" tabindex="128"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODSLABOFF_1" name="ODSLABOFF_1" value="<?php echo attr($ODSLABOFF_1 ?? ''); ?>" tabindex="130"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODVERTEXDIST_1" name="ODVERTEXDIST_1" value="<?php echo attr($ODVERTEXDIST_1 ?? ''); ?>" tabindex="132"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODMPDD_1" name="ODMPDD_1" value="<?php echo attr($ODMPDD_1 ?? ''); ?>" tabindex="134"></td>
                          <td name="W_wide"><input type="text" class="prism" id="ODMPDN_1" name="ODMPDN_1" value="<?php echo attr($ODMPDN_1 ?? ''); ?>" tabindex="136"></td>
                        </tr>
                        <tr>
                          <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                          <td><input type="text" class="sphere" id="OSSPH_1" name="OSSPH_1" value="<?php echo attr($OSSPH_1 ?? ''); ?>" tabindex="103"></td>
                          <td><input type="text" class="cylinder" id="OSCYL_1" name="OSCYL_1" value="<?php echo attr($OSCYL_1 ?? ''); ?>" tabindex="104"></td>
                          <td><input type="text" class="axis" id="OSAXIS_1" name="OSAXIS_1" value="<?php echo attr($OSAXIS_1 ?? ''); ?>" tabindex="105"></td>
                          <td><input type="text" class="acuity" id="OSVA_1" name="OSVA_1" value="<?php echo attr($OSVA_1 ?? ''); ?>" tabindex="109"></td>

                          <td name="W_wide"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSHPD_1" name="OSHPD_1" value="<?php echo attr($OSHPD_1 ?? ''); ?>"        tabindex="123"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSHBASE_1" name="OSHBASE_1" value="<?php echo attr($OSHBASE_1 ?? ''); ?>"  tabindex="125"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSVPD_1" name="OSVPD_1" value="<?php echo attr($OSVPD_1 ?? ''); ?>"        tabindex="127"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSVBASE_1" name="OSVBASE_1" value="<?php echo attr($OSVBASE_1 ?? ''); ?>"  tabindex="129"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSSLABOFF_1" name="OSSLABOFF_1" value="<?php echo attr($OSSLABOFF_1 ?? ''); ?>" tabindex="131"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSVERTEXDIST_1" name="OSVERTEXDIST_1" value="<?php echo attr($OSVERTEXDIST_1 ?? ''); ?>" tabindex="133"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSMPDD_1" name="OSMPDD_1" value="<?php echo attr($OSMPDD_1 ?? ''); ?>" tabindex="135"></td>
                          <td name="W_wide"><input type="text" class="prism" id="OSMPDN_1" name="OSMPDN_1" value="<?php echo attr($OSMPDN_1 ?? ''); ?>" tabindex="137"></td>
                        </tr>
                        <tr class="WNEAR">
                          <td rowspan=2><?php echo xlt('Mid{{middle Rx strength}}'); ?>/<br /><?php echo xlt('Near'); ?></td>
                          <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                            <?php echo '<input type="hidden" name="RXStart_1" id="RXStart_1" value="' . ($RX_TYPE_1 ?? '') . '">'; ?>
                          <td class="WMid"><input type="text" class="presbyopia" id="ODMIDADD_1" name="ODMIDADD_1" value="<?php echo attr($ODMIDADD_1 ?? ''); ?>"></td>
                          <td class="WAdd2"><input type="text" class="presbyopia" id="ODADD_1" name="ODADD_1" value="<?php echo attr($ODADD_1 ?? ''); ?>" tabindex="106"></td>
                          <td></td>
                          <td><input class="jaeger" type="text" id="ODNEARVA_1" name="ODNEARVA_1" value="<?php echo attr($ODNEARVA_1 ?? ''); ?>" tabindex="110"></td>

                          <td name="W_wide"></td>

                          <td name="W_wide" title="<?php echo xla('Binocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('PD-D{{abbreviation for Binocular Pupillary Diameter - Distance}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Binocular Pupillary Diameter - Near'); ?>"><?php echo xlt('PD-N{{abbreviation for Binocular Pupillary Diameter - Near}}'); ?></td>
                          <td name="W_wide" title="<?php echo xla('Lens Material'); ?>" colspan="2">
                            <a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_Lens_Material" target="RTop"
                                  title="<?php echo xla('Click here to edit list of available Lens Materials'); ?>"
                                  name="Lens_mat"><span class="underline"><?php echo xlt('Lens Material'); ?></span> <i class="fa fa-pencil-alt fa-fw"></i> </a>
                          </td>
                          <td name="W_wide2" colspan="4" rowspan="4">
                            <a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_Lens_Treatments" target="RTop"
                                  title="<?php echo xla('Click here to edit list of available Lens Treatment Options'); ?>"
                                  name="Lens_txs"><span class="underline"><?php echo xlt('Lens Treatments'); ?></span> <i class="fa fa-pencil-alt fa-fw"></i> </a>
                            <br />
                            <?php  echo generate_lens_treatments('1', ($LENS_TREATMENTS_1 ?? '')); ?>
                          </td>
                        </tr>
                        <tr class="WNEAR">
                          <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                          <td class="WMid"><input type="text" class="presbyopia" id="OSMIDADD_1" name="OSMIDADD_1" value="<?php echo attr($OSMIDADD_1 ?? ''); ?>"></td>
                          <td class="WAdd2"><input type="text" class="presbyopia" id="OSADD_1" name="OSADD_1" value="<?php echo attr($OSADD_1 ?? ''); ?>" tabindex="107"></td>
                          <td></td>
                          <td><input class="jaeger" type="text" id="OSNEARVA_1" name="OSNEARVA_1" value="<?php echo attr($OSNEARVA_1 ?? ''); ?>" tabindex="110"></td>

                          <td name="W_wide"></td>

                          <td name="W_wide"><input type="text" class="prism" id="BPDD_1" name="BPDD_1" value="<?php echo attr($BPDD_1 ?? ''); ?>" tabindex="138"></td>
                          <td name="W_wide"><input type="text" class="prism" id="BPDN_1" name="BPDN_1" value="<?php echo attr($BPDN_1 ?? ''); ?>" tabindex="140"></td>
                          <td name="W_wide" title="<?php echo xla('Lens Material Options'); ?>" colspan="2"  tabindex="142">
                            <?php echo generate_select_list("LENS_MATERIAL_1", "Eye_Lens_Material", ($LENS_MATERIAL_1 ?? ''), '', '--Lens Material--', '', 'restoreSession;submit_form();', '', array('style' => 'width:120px')); ?>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2"><b><?php echo xlt('Comments'); ?>:</b>
                          </td>
                          <td colspan="4" class="up"></td>
                        </tr>
                        <tr>
                          <td colspan="6">
                            <textarea id="COMMENTS_1" name="COMMENTS_W" tabindex="111"><?php echo text($COMMENTS_1 ?? ''); ?></textarea>
                          </td>
                          <td colspan="8">
                          </td>
                         </tr>
                      </table>
                    </div>
                    <?php
                    for ($i = 2; $i < 6; $i++) { //limit to a max of 5 pairs
                        echo generate_specRx($i);
                    }
                    ?>
                  </div>

                    <?php (($MR ?? null) == 1) ? ($display_AR = "") : ($display_AR = "nodisplay");?>
                  <div id="LayerVision_MR" class="refraction manifest borderShadow <?php echo $display_AR; ?>">
                    <i onclick="top.restoreSession();  dispensed('<?php echo attr($pid); ?>');return false;"
                     title="<?php echo xla("List of previously dispensed Spectacle and Contact Lens Rxs"); ?>" class="closeButton_3 fa fa-list-ul"></i>
                    <span class="closeButton_2 fa fa-print" title="<?php echo xla('Dispense this Rx'); ?>" onclick="top.restoreSession();doscript('MR',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></span>
                    <span class="closeButton fa  fa-times" id="Close_MR" name="Close_MR" title="<?php echo xla('Close this panel and make this a Preference to stay closed'); ?>"></span>
                    <table id="dry_wet_refraction">
                      <th colspan="5"><?php echo xlt('Manifest (Dry) Refraction'); ?></th>
                      <th NOWRAP colspan="2">
                        <input type="checkbox" name="BALANCED" id="Balanced" value="on" <?php if ($BALANCED == 'on') {
                            echo "checked='checked'";
                                                                                        } ?> tabindex="10182">
                        <label for="Balanced" class="input-helper input-helper--checkbox"><?php echo xlt('Balanced'); ?></label>
                      </th>

                      <tr>
                        <td><i class="fa fa-gamepad" name="reverseme" title="<?php echo xla('Convert between plus and minus cylinder'); ?>" aria-hidden="true" id="MR" ></i></td>
                        <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                        <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                        <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                        <td><?php echo xlt('Acuity'); ?></td>
                        <td><?php echo xlt('ADD'); ?></td>
                        <td><?php echo xlt('Jaeger'); ?></td>
                        <td><?php echo xlt('Prism'); ?></td>
                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input type="text" id="MRODSPH" name="MRODSPH" value="<?php echo attr($MRODSPH); ?>" tabindex="10170"></td>
                        <td><input type="text" id="MRODCYL" name="MRODCYL" value="<?php echo attr($MRODCYL); ?>" tabindex="10171"></td>
                        <td><input type="text" id="MRODAXIS"  name="MRODAXIS" value="<?php echo attr($MRODAXIS); ?>" tabindex="10172"></td>
                        <td><input type="text" id="MRODVA"  name="MRODVA" value="<?php echo attr($MRODVA); ?>" tabindex="10176"></td>
                        <td><input type="text" id="MRODADD"  name="MRODADD" value="<?php echo attr($MRODADD); ?>" tabindex="10178"></td>
                        <td><input class="jaeger" type="text" id="MRNEARODVA"  name="MRNEARODVA" value="<?php echo attr($MRNEARODVA); ?>" tabindex="10180"> </td>
                        <td><input type="text" id="MRODPRISM"  name="MRODPRISM" value="<?php echo attr($MRODPRISM); ?>"></td>
                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                        <td><input type="text" id="MROSSPH" name="MROSSPH" value="<?php echo attr($MROSSPH); ?>" tabindex="10173"></td>
                        <td><input type="text" id="MROSCYL" name="MROSCYL" value="<?php echo attr($MROSCYL); ?>" tabindex="10174"></td>
                        <td><input type="text" id="MROSAXIS"  name="MROSAXIS" value="<?php echo attr($MROSAXIS); ?>" tabindex="10175"></td>
                        <td><input type="text" id="MROSVA"  name="MROSVA" value="<?php echo attr($MROSVA); ?>" tabindex="10177"></td>
                        <td><input type="text" id="MROSADD"  name="MROSADD" value="<?php echo attr($MROSADD); ?>" tabindex="10179"></td>
                        <td><input class="jaeger" type="text" id="MRNEAROSVA"  name="MRNEAROSVA" value="<?php echo attr($MRNEAROSVA); ?>" tabindex="10181"></td>
                        <td><input type="text" id="MROSPRISM"  name="MROSPRISM" value="<?php echo attr($MROSPRISM); ?>"></td>
                      </tr>
                    </table>

                    <table>
                      <th colspan="7"><?php echo xlt('Cycloplegic (Wet) Refraction'); ?></th>
                      <th><i title="<?php echo xla("Dispense Rx"); ?>" class="fa fa-print" onclick="top.restoreSession();doscript('CR',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></i></th>

                      <tr>
                        <td><i class="fa fa-gamepad" name="reverseme" title="<?php echo xla('Convert between plus and minus cylinder'); ?>" aria-hidden="true" id="CR" ></i></td>
                        <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                        <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                        <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                        <td><?php echo xlt('Acuity'); ?></td>
                        <td rowspan="3">
                            <ul>
                                <li>
                                    <input type="radio" name="WETTYPE" id="Streak" value="Streak" <?php if ($WETTYPE == "Streak") {
                                        echo "checked='checked'";
                                                                                                  } ?>/>
                                    <label for="Streak" class="input-helper input-helper--checkbox"><?php echo xlt('Streak'); ?></label>
                                </li>
                                <li>
                                    <input type="radio" name="WETTYPE" id="Auto" value="Auto" <?php if ($WETTYPE == "Auto") {
                                        echo "checked='checked'";
                                                                                              } ?>>
                                    <label for="Auto" class="input-helper input-helper--checkbox"><?php echo xlt('Auto{{autorefraction}}'); ?></label>
                                </li>

                                <li>
                                    <input type="radio" name="WETTYPE" id="Manual" value="Manual" <?php if ($WETTYPE == "Manual") {
                                        echo "checked='checked'";
                                                                                                  } ?>>
                                    <label for="Manual" class="input-helper input-helper--checkbox"><?php echo xlt('Manual'); ?></label>
                                </li>
                            </ul>
                        </td>
                        <td id="IOP_dil"><?php echo xlt('IOP Dilated{{Dilated Intraocular Pressure}}'); ?>
                          <input type="hidden" name="IOPPOSTTIME" id="IOPPOSTTIME" value="">
                        </td>

                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input type="text" id="CRODSPH" name="CRODSPH" value="<?php echo attr($CRODSPH); ?>" tabindex="10183"></td>
                        <td><input type="text" id="CRODCYL" name="CRODCYL" value="<?php echo attr($CRODCYL); ?>" tabindex="10184"></td>
                        <td><input type="text" id="CRODAXIS" name="CRODAXIS" value="<?php echo attr($CRODAXIS); ?>" tabindex="10185"></td>
                        <td><input type="text" id="CRODVA" name="CRODVA"  value="<?php echo attr($CRODVA); ?>" tabindex="10189"></td>
                        <td><input type="text" id="ODIOPPOST" name="ODIOPPOST"  value="<?php echo attr($ODIOPPOST); ?>">
                        </tr>
                        <tr>
                          <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                          <td><input type="text" id="CROSSPH" name="CROSSPH" value="<?php echo attr($CROSSPH); ?>" tabindex="10186"></td>
                          <td><input type="text" id="CROSCYL" name="CROSCYL" value="<?php echo attr($CROSCYL); ?>" tabindex="10187"></td>
                          <td><input type="text" id="CROSAXIS" name="CROSAXIS" value="<?php echo attr($CROSAXIS); ?>" tabindex="10188"></td>
                          <td><input type="text" id="CROSVA" name="CROSVA" value="<?php echo attr($CROSVA); ?>" tabindex="10190"></td>

                          <td><input type="text" id="OSIOPPOST" name="OSIOPPOST"  value="<?php echo attr($OSIOPPOST); ?>"></td>
                        </tr>
                    </table>
                  </div>

                    <?php (($CR ?? null) == 1)  ? ($display_Cyclo = "") : ($display_Cyclo = "nodisplay"); ?>
                  <div id="LayerVision_CR" class="refraction autoref borderShadow <?php echo $display_Cyclo; ?>">
                    <i title="<?php echo xla('Dispense this Rx'); ?>" class="closeButton_2 fa fa-print" onclick="top.restoreSession();doscript('AR',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></i>
                    <span title="<?php echo xla('Close this panel and make this a Preference to stay closed'); ?>" class="closeButton fa  fa-times" id="Close_CR" name="Close_CR"></span>
                    <table id="autorefraction">
                      <th colspan="9"><?php echo xlt('Auto Refraction'); ?></th>
                      <tr>
                        <td><i class="fa fa-gamepad" name="reverseme" title="<?php echo xla('Convert between plus and minus cylinder'); ?>" aria-hidden="true" id="AR" ></i></td>
                        <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                        <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                        <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                        <td><?php echo xlt('Acuity'); ?></td>
                        <td><?php echo xlt('ADD'); ?></td>
                        <td><?php echo xlt('Jaeger{{Near Acuity Type Jaeger}}'); ?></td>
                        <td><?php echo xlt('Prism'); ?></td>
                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input type="text" id="ARODSPH" name="ARODSPH" value="<?php echo attr($ARODSPH); ?>" tabindex="10220"></td>
                        <td><input type="text" id="ARODCYL" name="ARODCYL" value="<?php echo attr($ARODCYL); ?>" tabindex="10221"></td>
                        <td><input type="text" id="ARODAXIS" name="ARODAXIS" value="<?php echo attr($ARODAXIS); ?>" tabindex="10222"></td>
                        <td><input type="text" id="ARODVA" name="ARODVA" value="<?php echo attr($ARODVA); ?>" tabindex="10228"></td>
                        <td><input type="text" id="ARODADD" name="ARODADD" value="<?php echo attr($ARODADD); ?>" tabindex="10226"></td>
                        <td><input class="jaeger" type="text" id="ARNEARODVA" name="ARNEARODVA" value="<?php echo attr($ARNEARODVA); ?>"></td>
                        <td><input type="text" id="ARODPRISM" name="ARODPRISM" value="<?php echo attr($ARODPRISM); ?>"></td>
                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                        <td><input type="text" id="AROSSPH" name="AROSSPH" value="<?php echo attr($AROSSPH); ?>" tabindex="10223"></td>
                        <td><input type="text" id="AROSCYL" name="AROSCYL" value="<?php echo attr($AROSCYL); ?>" tabindex="10224"></td>
                        <td><input type="text" id="AROSAXIS" name="AROSAXIS" value="<?php echo attr($AROSAXIS); ?>" tabindex="10225"></td>
                        <td><input type="text" id="AROSVA" name="AROSVA" value="<?php echo attr($AROSVA); ?>" tabindex="10229"></td>
                        <td><input type="text" id="AROSADD" name="AROSADD" value="<?php echo attr($AROSADD); ?>" tabindex="10227"></td>
                        <td><input class="jaeger" type="text" id="ARNEAROSVA" name="ARNEAROSVA" value="<?php echo attr($ARNEAROSVA); ?>"></td>
                        <td><input type="text" id="AROSPRISM" name="AROSPRISM" value="<?php echo attr($AROSPRISM); ?>"></td>
                      </tr>
                      <tr>
                        <th colspan="9" class="bold pad10"><br /><?php echo xlt('Refraction Comments'); ?>:</th>
                      </tr>
                      <tr>
                        <td colspan="9"><textarea id="CRCOMMENTS" name="CRCOMMENTS"><?php echo attr($CRCOMMENTS); ?></textarea>
                        </td>
                      </tr>
                    </table>
                  </div>

                    <?php (($CTL ?? null) == 1) ? ($display_CTL = "") : ($display_CTL = "nodisplay"); ?>
                  <div id="LayerVision_CTL" class="refraction CTL borderShadow <?php echo $display_CTL; ?>">
                      <i onclick="top.restoreSession();  dispensed('<?php echo attr($pid); ?>');return false;"
                         title="<?php echo xla("List of previously dispensed Spectacle and Contact Lens Rxs"); ?>" class="closeButton_3 fa fa-list-ul"></i>
                      <i title="<?php echo xla('Dispense this RX'); ?>" class="closeButton_2 fa fa-print" onclick="top.restoreSession();doscript('CTL',<?php echo attr($pid); ?>,<?php echo attr($encounter); ?>);return false;"></i>
                    <span title="<?php echo xla('Close this panel and make this a Preference to stay closed'); ?>" class="closeButton fa  fa-times" id="Close_CTL" name="Close_CTL"></span>
                    <table id="CTL">
                      <th colspan="9"><?php echo xlt('Contact Lens Refraction'); ?></th>
                      <tr>
                        <td class="center">
                          <div id="CTL_box">
                            <table>
                              <tr>
                                <td></td>
                                <td><a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=CTLManufacturer" target="RTop"
                                  title="<?php echo xla('Click here to Edit the Manufacter List'); ?>"
                                  name="CTL"><?php echo xlt('Manufacturer'); ?> <i class="fa fa-pencil-alt fa-fw"></i> </a>
                                </td>
                                <td><a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=CTLSupplier" target="RTop"
                                  title="<?php echo xla('Click here to Edit the Supplier List'); ?>"
                                  name="CTL"><?php echo xlt('Supplier'); ?> <i class="fa fa-pencil-alt fa-fw"></i> </a>
                                </td>
                                <td><a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=CTLBrand" target="RTop"
                                  title="<?php echo xla('Click here to Edit the Contact Lens Brand List'); ?>"
                                  name="CTL"><?php echo xlt('Brand'); ?> <i class="fa fa-pencil-alt fa-fw"></i> </a>
                                </td>
                              </tr>
                              <tr>
                                <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                                <td>
                                  <!--  Pull from CTL data from list_options which user populates the usual way -->
                                    <?php
                                                      //build manufacturer list from list_options::list_id::CTLManufacturer
                                    $query = "select * from list_options where list_id like 'CTLManufacturer' order by seq";
                                    $CTLMANUFACTURER_data = sqlStatement($query);
                                    while ($row = sqlFetchArray($CTLMANUFACTURER_data)) {
                                        if (!empty($CTLMANUFACTURER_list_OD)) {
                                            $CTLMANUFACTURER_list_OD .= '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLMANUFACTUREROD == $row['option_id']) {
                                                $CTLMANUFACTURER_list_OD .= "selected";
                                            }

                                            $CTLMANUFACTURER_list_OD .= '>' . text(substr($row['title'], 0, 12)) . '</option>
                                        ' ;
                                        } else {
                                            $CTLMANUFACTURER_list_OD = '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLMANUFACTUREROD == $row['option_id']) {
                                                $CTLMANUFACTURER_list_OD = "selected";
                                            }

                                            $CTLMANUFACTURER_list_OD = '>' . text(substr($row['title'], 0, 12)) . '</option>
                                        ' ;
                                        }

                                        if (!empty($CTLMANUFACTURER_list_OS)) {
                                            $CTLMANUFACTURER_list_OS .= '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLMANUFACTUREROS == $row['option_id']) {
                                                $CTLMANUFACTURER_list_OS .= "selected";
                                            }

                                            $CTLMANUFACTURER_list_OS .= '>' . text(substr($row['title'], 0, 12)) . '</option>
                                        ' ;
                                        } else {
                                            $CTLMANUFACTURER_list_OS = '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLMANUFACTUREROS == $row['option_id']) {
                                                $CTLMANUFACTURER_list_OS = "selected";
                                            }

                                            $CTLMANUFACTURER_list_OS = '>' . text(substr($row['title'], 0, 12)) . '</option>
                                        ' ;
                                        }
                                    }

                                                      //build supplier list from list_options::list_id::CTLSupplier
                                    $query = "select * from list_options where list_id like 'CTLSupplier' order by seq";
                                    $CTLSUPPLIER_data = sqlStatement($query);
                                    while ($row = sqlFetchArray($CTLSUPPLIER_data)) {
                                        if (!empty($CTLSUPPLIER_list_OD)) {
                                            $CTLSUPPLIER_list_OD .= '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLSUPPLIEROD == $row['option_id']) {
                                                $CTLSUPPLIER_list_OD .= "selected";
                                            }

                                            $CTLSUPPLIER_list_OD .= '>' . text(substr($row['title'], 0, 10)) . '</option>
                                        ' ;
                                        } else {
                                            $CTLSUPPLIER_list_OD = '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLSUPPLIEROD == $row['option_id']) {
                                                $CTLSUPPLIER_list_OD = "selected";
                                            }

                                            $CTLSUPPLIER_list_OD = '>' . text(substr($row['title'], 0, 10)) . '</option>
                                        ' ;
                                        }

                                        if (!empty($CTLSUPPLIER_list_OS)) {
                                            $CTLSUPPLIER_list_OS .= '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLSUPPLIEROS == $row['option_id']) {
                                                $CTLSUPPLIER_list_OS .= "selected";
                                            }

                                            $CTLSUPPLIER_list_OS .= '>' . text(substr($row['title'], 0, 10)) . '</option>
                                        ' ;
                                        } else {
                                            $CTLSUPPLIER_list_OS = '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLSUPPLIEROS == $row['option_id']) {
                                                $CTLSUPPLIER_list_OS = "selected";
                                            }

                                            $CTLSUPPLIER_list_OS = '>' . text(substr($row['title'], 0, 10)) . '</option>
                                        ' ;
                                        }
                                    }

                                                      //build manufacturer list from list_options::list_id::CTLManufacturer
                                    $query = "select * from list_options where list_id like 'CTLBrand' order by seq";
                                    $CTLBRAND_data = sqlStatement($query);
                                    while ($row = sqlFetchArray($CTLBRAND_data)) {
                                        if (!empty($CTLSBRAND_list_OD)) {
                                            $CTLBRAND_list_OD .= '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLBRANDOD == $row['option_id']) {
                                                $CTLBRAND_list_OD .= "selected";
                                            }

                                            $CTLBRAND_list_OD .= '>' . text(substr($row['title'], 0, 15)) . '</option>
                                        ' ;
                                        } else {
                                            $CTLBRAND_list_OD = '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLBRANDOD == $row['option_id']) {
                                                $CTLBRAND_list_OD = "selected";
                                            }

                                            $CTLBRAND_list_OD = '>' . text(substr($row['title'], 0, 15)) . '</option>
                                        ' ;
                                        }

                                        if (!empty($CTLSBRAN_list_OS)) {
                                            $CTLBRAND_list_OS .= '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLBRANDOS == $row['option_id']) {
                                                $CTLBRAND_list_OS .= "selected";
                                            }

                                            $CTLBRAND_list_OS .= '>' . text(substr($row['title'], 0, 15)) . '</option>
                                        ' ;
                                        } else {
                                            $CTLBRAND_list_OS = '<option value="' . attr($row['option_id']) . '"';
                                            if ($CTLBRANDOS == $row['option_id']) {
                                                $CTLBRAND_list_OS = "selected";
                                            }

                                            $CTLBRAND_list_OS = '>' . text(substr($row['title'], 0, 15)) . '</option>
                                        ' ;
                                        }
                                    }
                                    ?>
                                  <select id="CTLMANUFACTUREROD" name="CTLMANUFACTUREROD" tabindex="10230">
                                    <option></option>
                                    <?php echo $CTLMANUFACTURER_list_OD ?? ''; ?>
                                  </select>
                                </td>
                                <td>
                                  <select id="CTLSUPPLIEROD" name="CTLSUPPLIEROD" tabindex="10231">
                                    <option></option>
                                    <?php echo $CTLSUPPLIER_list_OD ?? ''; ?>
                                  </select>
                                </td>
                                <td>
                                  <select id="CTLBRANDOD" name="CTLBRANDOD" tabindex="10232">
                                    <option></option>
                                    <?php echo $CTLBRAND_list_OD ?? ''; ?>
                                  </select>
                                </td>
                              </tr>
                              <tr >
                                <td><b><?php echo xlt('OS'); ?>:</b></td>
                                <td>
                                  <select id="CTLMANUFACTUREROS" name="CTLMANUFACTUREROS" tabindex="10233">
                                    <option></option>
                                    <?php echo $CTLMANUFACTURER_list_OS ?? ''; ?>
                                  </select>
                                </td>
                                <td>
                                  <select id="CTLSUPPLIEROS" name="CTLSUPPLIEROS" tabindex="10234">
                                    <option></option>
                                    <?php echo $CTLSUPPLIER_list_OS ?? ''; ?>
                                  </select>
                                </td>
                                <td>
                                  <select id="CTLBRANDOS" name="CTLBRANDOS" tabindex="10235">
                                    <option></option>
                                    <?php echo $CTLBRAND_list_OS ?? ''; ?>
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
                        <td><i class="fa fa-gamepad" name="reverseme" title="<?php echo xla('Convert between plus and minus cylinder'); ?>" aria-hidden="true" id="CTL" ></i></td>
                        <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                        <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                        <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                        <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                        <td><?php echo xlt('Diam{{Diameter}}'); ?></td>
                        <td><?php echo xlt('ADD'); ?></td>
                        <td><?php echo xlt('Acuity'); ?></td>
                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input type="text" id="CTLODSPH" name="CTLODSPH" value="<?php echo attr($CTLODSPH); ?>" tabindex="10236"></td>
                        <td><input type="text" id="CTLODCYL" name="CTLODCYL" value="<?php echo attr($CTLODCYL); ?>" tabindex="10240"></td>
                        <td><input type="text" id="CTLODAXIS" name="CTLODAXIS" value="<?php echo attr($CTLODAXIS); ?>" tabindex="10241"></td>
                        <td><input type="text" id="CTLODBC" name="CTLODBC" value="<?php echo attr($CTLODBC); ?>" tabindex="10237"></td>
                        <td><input type="text" id="CTLODDIAM" name="CTLODDIAM" value="<?php echo attr($CTLODDIAM); ?>" tabindex="10238"></td>
                        <td><input type="text" id="CTLODADD" name="CTLODADD" value="<?php echo attr($CTLODADD); ?>" tabindex="10242"></td>
                        <td><input type="text" id="CTLODVA" name="CTLODVA" value="<?php echo attr($CTLODVA); ?>" tabindex="10239"></td>
                      </tr>
                      <tr >
                        <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                        <td><input type="text" id="CTLOSSPH" name="CTLOSSPH" value="<?php echo attr($CTLOSSPH); ?>" tabindex="10243"></td>
                        <td><input type="text" id="CTLOSCYL" name="CTLOSCYL" value="<?php echo attr($CTLOSCYL); ?>" tabindex="10247"></td>
                        <td><input type="text" id="CTLOSAXIS" name="CTLOSAXIS" value="<?php echo attr($CTLOSAXIS); ?>" tabindex="10248"></td>
                        <td><input type="text" id="CTLOSBC" name="CTLOSBC" value="<?php echo attr($CTLOSBC); ?>" tabindex="10244"></td>
                        <td><input type="text" id="CTLOSDIAM" name="CTLOSDIAM" value="<?php echo attr($CTLOSDIAM); ?>" tabindex="10245"></td>
                        <td><input type="text" id="CTLOSADD" name="CTLOSADD" value="<?php echo attr($CTLOSADD); ?>" tabindex="10249"></td>
                        <td><input type="text" id="CTLOSVA" name="CTLOSVA" value="<?php echo attr($CTLOSVA); ?>" tabindex="10246"></td>
                      </tr>
                      <tr>
                        <td colspan="2" class="right bold">
                            <?php echo xlt('Comments'); ?>:
                        </td>
                        <td colspan="6">
                          <textarea name="CTL_COMMENTS" id="CTL_COMMENTS" tabindex="10250"><?php echo text($CTL_COMMENTS); ?></textarea>
                        </td>
                      </tr>
                    </table>
                  </div>

                    <?php (($ADDITIONAL ?? null) == 1) ? ($display_Add = "") : ($display_Add = "nodisplay"); ?>
                  <div id="LayerVision_ADDITIONAL" class="refraction borderShadow <?php echo $display_Add; ?>">
                    <span title="<?php echo xla('Close and make this a Preference to stay closed'); ?>" class="closeButton fa  fa-times" id="Close_ADDITIONAL" name="Close_ADDITIONAL"></span>

                    <table id="Additional">
                      <th colspan=9><?php echo xlt('Additional Data Points'); ?></th>
                      <tr><td></td>
                        <td title="<?php echo xla('Pinhole Vision'); ?>"><?php echo xlt('PH{{pinhole acuity}}'); ?></td>
                        <td title="<?php echo xla('Potential Acuity Meter'); ?>"><?php echo xlt('PAM{{Potential Acuity Meter}}'); ?></td>
                        <td title="<?php echo xla('Laser Interferometry Acuity'); ?>"><?php echo xlt('LI{{Laser Interferometry Acuity}}'); ?></td>
                        <td title="<?php echo xla('Brightness Acuity Testing'); ?>"><?php echo xlt('BAT{{Brightness Acuity Testing}}'); ?></td>
                        <td><?php echo xlt('K1{{Keratometry 1}}'); ?></td>
                        <td><?php echo xlt('K2{{Keratometry 2}}'); ?></td>
                        <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                      </tr>
                      <tr><td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input type="text" id="PHODVA" name="PHODVA" title="<?php echo xla('Pinhole Vision'); ?>" value="<?php echo attr($PHODVA); ?>" tabindex="10251"></td>
                        <td><input type="text" id="PAMODVA" name="PAMODVA" title="<?php echo xla('Potential Acuity Meter'); ?>" value="<?php echo attr($PAMODVA); ?>" tabindex="10253"></td>
                        <td><input type="text" id="LIODVA" name="LIODVA"  title="<?php echo xla('Laser Interferometry'); ?>" value="<?php echo attr($LIODVA); ?>" tabindex="10255"></td>
                        <td><input type="text" id="GLAREODVA" name="GLAREODVA" title="<?php echo xla('Brightness Acuity Testing'); ?>" value="<?php echo attr($GLAREODVA); ?>" tabindex="10257"></td>
                        <td><input type="text" id="ODK1" name="ODK1" value="<?php echo attr($ODK1); ?>" tabindex="10259"></td>
                        <td><input type="text" id="ODK2" name="ODK2" value="<?php echo attr($ODK2); ?>" tabindex="10260"></td>
                        <td><input type="text" id="ODK2AXIS" name="ODK2AXIS" value="<?php echo attr($ODK2AXIS); ?>" tabindex="10261"></td>
                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                        <td><input type="text" id="PHOSVA" name="PHOSVA" title="<?php echo xla('Pinhole Vision'); ?>" value="<?php echo attr($PHOSVA); ?>" tabindex="10252"></td>
                        <td><input type="text" id="PAMOSVA" name="PAMOSVA" title="<?php echo xla('Potential Acuity Meter'); ?>" value="<?php echo attr($PAMOSVA); ?>" tabindex="10254"></td>
                        <td><input type="text" id="LIOSVA" name="LIOSVA" title="<?php echo xla('Laser Interferometry'); ?>" value="<?php echo attr($LIOSVA); ?>" tabindex="10256"></td>
                        <td><input type="text" id="GLAREOSVA" name="GLAREOSVA" title="<?php echo xla('Brightness Acuity Testing'); ?>" value="<?php echo attr($GLAREOSVA); ?>"  tabindex="10258"></td>
                        <td><input type="text" id="OSK1" name="OSK1" value="<?php echo attr($OSK1); ?>" tabindex="10262"></td>
                        <td><input type="text" id="OSK2" name="OSK2" value="<?php echo attr($OSK2); ?>" tabindex="10263"></td>
                        <td><input type="text" id="OSK2AXIS" name="OSK2AXIS" value="<?php echo attr($OSK2AXIS); ?>" tabindex="10264"></td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>
                      <tr>
                        <td></td>
                        <td title="<?php echo xla('Axial Length'); ?>"><?php echo xlt('AxLength{{axial Length}}'); ?></td>
                        <td title="<?php echo xla('Anterior Chamber Depth'); ?>"><?php echo xlt('ACD{{anterior chamber depth}}'); ?></td>
                        <td title="<?php echo xla('Inter-pupillary distance'); ?>"><?php echo xlt('PD{{Inter-pupillary distance}}'); ?></td>
                        <td title="<?php echo xla('Lens Thickness'); ?>"><?php echo xlt('LT{{lens thickness}}'); ?></td>
                        <td title="<?php echo xla('White-to-white'); ?>"><?php echo xlt('W2W{{white-to-white}}'); ?></td>
                        <td title="<?php echo xla('Equivalent contact lens power at the corneal level'); ?>"><?php echo xlt('ECL{{equivalent contact lens power at the corneal level}}'); ?></td>
                        <td><?php echo xlt('VABiNoc{{Binocular Visual Acuity}}'); ?></td>
                      </tr>
                      <tr><td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input type="text" id="ODAXIALLENGTH" name="ODAXIALLENGTH"  value="<?php echo attr($ODAXIALLENGTH); ?>"  tabindex="10265"></td>
                        <td><input type="text" id="ODACD" name="ODACD"  value="<?php echo attr($ODACD); ?>" tabindex="10267"></td>
                        <td><input type="text" id="ODPDMeasured" name="ODPDMeasured"  value="<?php echo attr($ODPDMeasured); ?>" tabindex="10269"></td>
                        <td><input type="text" id="ODLT" name="ODLT"  value="<?php echo attr($ODLT); ?>" tabindex="10271"></td>
                        <td><input type="text" id="ODW2W" name="ODW2W"  value="<?php echo attr($ODW2W); ?>" tabindex="10273"></td>
                        <td><input type="text" id="ODECL" name="ODECL"  value="<?php echo attr($ODECL ?? ''); ?>" tabindex="10275"></td>
                        <td rowspan="2"><input type="text" id="BINOCVA" name="BINOCVA"  value="<?php echo attr($BINOCVA); ?>"></td>
                      </tr>
                      <tr>
                        <td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                        <td><input type="text" id="OSAXIALLENGTH" name="OSAXIALLENGTH" value="<?php echo attr($OSAXIALLENGTH); ?>" tabindex="10266"></td>
                        <td><input type="text" id="OSACD" name="OSACD" value="<?php echo attr($OSACD); ?>" tabindex="10268"></td>
                        <td><input type="text" id="OSPDMeasured" name="OSPDMeasured" value="<?php echo attr($OSPDMeasured); ?>" tabindex="10270"></td>
                        <td><input type="text" id="OSLT" name="OSLT" value="<?php echo attr($OSLT); ?>" tabindex="10272"></td>
                        <td><input type="text" id="OSW2W" name="OSW2W" value="<?php echo attr($OSW2W); ?>" tabindex="10274"></td>
                        <td><input type="text" id="OSECL" name="OSECL" value="<?php echo attr($OSECL ?? ''); ?>" tabindex="10276"></td>
                        <!--  <td><input type="text" id="pend" name="pend" value="<?php echo attr($pend ?? ''); ?>"></td> -->
                      </tr>
                    </table>
                  </div>

                    <?php (($VAX ?? null) == 1) ? ($display_Add = "") : ($display_Add = "nodisplay"); ?>
                  <div id="LayerVision_VAX" class="refraction borderShadow <?php echo $display_Add; ?>">
                    <span title="<?php echo attr('Close this panel and make this a Preference to stay closed'); ?>" class="closeButton fa  fa-times" id="Close_VAX" name="Close_VAX"></span>
                    <table id="Additional_VA">
                      <tr>
                          <th colspan="9"><?php echo xlt('Visual Acuity'); ?></th>
                      </tr>
                      <tr><td></td>
                        <td title="<?php echo xla('Acuity without correction'); ?>"><?php echo xlt('SC{{Acuity without correction}}'); ?></td>
                        <td title="<?php echo xla('Acuity with correction'); ?>"><?php echo xlt('W Rx{{Acuity with correction}}'); ?></td>
                        <td title="<?php echo xla('Acuity with Autorefraction'); ?>"><?php echo xlt('AR{{Autorefraction Acuity}}'); ?></td>
                        <td title="<?php echo xla('Acuity with Manifest Refraction'); ?>"><?php echo xlt('MR{{Manifest Refraction}}'); ?></td>
                        <td title="<?php echo xla('Acuity with Cycloplegic Refraction'); ?>"><?php echo xlt('CR{{Cycloplegic Refraction}}'); ?></td>
                        <td title="<?php echo xla('Acuity with Pinhole'); ?>"><?php echo xlt('PH{{pinhole acuity}}'); ?></td>
                        <td title="<?php echo xla('Acuity with Contact Lenses'); ?>"><?php echo xlt('CTL{{Contact Lens}}'); ?></td>

                      </tr>
                      <tr><td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input type="text" id="SCODVA_copy_brd" name="SCODVA_copy_brd" value="<?php echo attr($SCODVA); ?>" tabindex="10300"></td>
                        <td><input type="text" id="ODVA_1_copy_brd" name="ODVA_1_copy_brd" value="<?php echo attr($ODVA_1 ?? ''); ?>" tabindex="10302"></td>
                        <td><input type="text" id="ARODVA_copy_brd" name="ARODVA_copy_brd" value="<?php echo attr($ARODVA); ?>" tabindex="10304"></td>
                        <td><input type="text" id="MRODVA_copy_brd" name="MRODVA_copy_brd" value="<?php echo attr($MRODVA); ?>" tabindex="10306"></td>
                        <td><input type="text" id="CRODVA_copy_brd" name="CRODVA_copy_brd" value="<?php echo attr($CRODVA); ?>" tabindex="10308"></td>
                        <td><input type="text" id="PHODVA_copy_brd" name="PHODVA_copy_brd" value="<?php echo attr($PHODVA); ?>" tabindex="10310"></td>
                        <td><input type="text" id="CTLODVA_copy_brd" name="CTLODVA_copy_brd" value="<?php echo attr($CTLODVA); ?>" tabindex="10312"></td>
                      </tr>
                      <tr><td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                        <td><input type="text" id="SCOSVA_copy"     name="SCOSVA_copy"     value="<?php echo attr($SCOSVA); ?>" tabindex="10301"></td>
                        <td><input type="text" id="OSVA_1_copy_brd" name="OSVA_1_copy_brd" value="<?php echo attr($OSVA_1 ?? ''); ?>" tabindex="10303"></td>
                        <td><input type="text" id="AROSVA_copy_brd" name="AROSVA_copy_brd" value="<?php echo attr($AROSVA); ?>" tabindex="10305"></td>
                        <td><input type="text" id="MROSVA_copy_brd" name="MROSVA_copy_brd" value="<?php echo attr($MROSVA); ?>" tabindex="10307"></td>
                        <td><input type="text" id="CROSVA_copy_brd" name="CROSVA_copy_brd" value="<?php echo attr($CROSVA); ?>" tabindex="10309"></td>
                        <td><input type="text" id="PHOSVA_copy_brd" name="PHOSVA_copy_brd" value="<?php echo attr($PHOSVA); ?>" tabindex="10311"></td>
                        <td><input type="text" id="CTLOSVA_copy_brd" name="CTLOSVA_copy_brd" value="<?php echo attr($CTLOSVA); ?>" tabindex="10313"></td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>
                      <tr>
                        <td></td>
                        <td title="<?php echo xla('Near Acuity without Correction'); ?>"><?php echo xlt('scNear{{without correction near}}'); ?></td>
                        <td title="<?php echo xla('Near Acuity with Correction'); ?>"><?php echo xlt('ccNear{{with correction at near}}'); ?></td>
                        <td title="<?php echo xla('Near Acuity with Autorefraction'); ?>"><?php echo xlt('ARNear{{autorefraction near}}'); ?></td>
                        <td title="<?php echo xla('Near Acuity with Manifest (Dry) refraction'); ?>"><?php echo xlt('MRNear{{manifest refraction near}}'); ?></td>
                        <td title="<?php echo xla('Potential Acuity'); ?>"><?php echo xlt('PAM{{Potential Acuity Meter}}'); ?></td>
                        <td title="<?php echo xla('Brightness Acuity Testing'); ?>"><?php echo xlt('BAT{{Brightness Acuity Testing}}'); ?></td>
                        <td title="<?php echo xla('Contrast Acuity'); ?>"><?php echo xlt('Contrast{{Constrast Visual Acuity}}'); ?></td>
                      </tr>
                      <tr><td><b><?php echo xlt('OD{{right eye}}'); ?>:</b></td>
                        <td><input class="jaeger" type="text" id="SCNEARODVA" title="<?php echo xla('Near Acuity without Correction'); ?>" name="SCNEARODVA" value="<?php echo attr($SCNEARODVA); ?>" tabindex="10320"></td>
                        <td><input class="jaeger" type="text" id="ODNEARVA_1_copy_brd" title="<?php echo xla('Near Acuity with Correction'); ?>" name="ODNEARVA_1_copy_brd" value="<?php echo attr($ODNEARVA_1 ?? ''); ?>" tabindex="10322"></td>
                        <td><input class="jaeger" type="text" id="ARNEARODVA_copy_brd" title="<?php echo xla('Near Acuity AutoRefraction'); ?>" name="ARNEARODVA_copy_brd" value="<?php echo attr($ARNEARODVA); ?>" tabindex="10324"></td>
                        <td><input class="jaeger" type="text" id="MRNEARODVA_copy_brd" title="<?php echo xla('Near Acuity Manifest Refraction'); ?>" name="MRNEARODVA_copy_brd" value="<?php echo attr($MRNEARODVA); ?>" tabindex="10326"></td>
                        <td><input type="text" id="PAMODVA_copy_brd" title="<?php echo xla('Potential Acuity Meter'); ?>" name="PAMODVA_copy_brd" value="<?php echo attr($PAMODVA); ?>" tabindex="10328"></td>
                        <td><input type="text" id="GLAREODVA_copy_brd" title="<?php echo xla('Brightness Acuity Testing'); ?>" name="GLAREODVA_copy_brd" value="<?php echo attr($GLAREODVA); ?>" tabindex="10330"></td>
                        <td><input type="text" id="CONTRASTODVA_copy_brd" title="<?php echo xla('Contrast Acuity Testing'); ?>" name="CONTRASTODVA_copy_brd" value="<?php echo attr($CONTRASTODVA ?? ''); ?>" tabindex="10332"></td>
                      </tr>
                      <tr><td><b><?php echo xlt('OS{{left eye}}'); ?>:</b></td>
                        <td><input class="jaeger" type="text" id="SCNEAROSVA" title="<?php echo xla('Near Acuity without Correction'); ?>" name="SCNEAROSVA" value="<?php echo attr($SCNEAROSVA); ?>" tabindex="10321"></td>
                        <td><input class="jaeger" type="text" id="OSNEARVA_1_copy_brd" title="<?php echo xla('Near Acuity with Correction'); ?>" name="OSNEARVA_1_copy_brd" value="<?php echo attr($OSNEARVA_1 ?? ''); ?>" tabindex="10323"></td>
                        <td><input class="jaeger" type="text" id="ARNEAROSVA_copy" title="<?php echo xla('Near Acuity AutoRefraction'); ?>" name="ARNEAROSVA_copy" value="<?php echo attr($ARNEAROSVA); ?>" tabindex="10325"></td>
                        <td><input class="jaeger" type="text" id="MRNEAROSVA_copy" title="<?php echo xla('Near Acuity Manifest Refraction'); ?>" name="MRNEAROSVA_copy" value="<?php echo attr($MRNEAROSVA); ?>" tabindex="10327"></td>
                        <td><input type="text" id="PAMOSVA_copy_brd" title="<?php echo xla('Potential Acuity Meter'); ?>" name="PAMOSVA_copy_brd" value="<?php echo attr($PAMOSVA); ?>" tabindex="10329"></td>
                        <td><input type="text" id="GLAREOSVA_copy_brd" title="<?php echo xla('Brightness Acuity Testing'); ?>" name="GLAREOSVA_copy_brd" value="<?php echo attr($GLAREOSVA); ?>" tabindex="10331"></td>
                        <td><input type="text" id="CONTRASTOSVA" title="<?php echo xla('Contrast Acuity Testing'); ?>" name="CONTRASTOSVA" value="<?php echo attr($CONTRASTOSVA ?? ''); ?>" tabindex="10333"></td>
                      </tr>
                    </table>
                  </div>
              </div>
              </div>
              <!-- end of the refraction box -->
              <!-- start of the exam selection/middle menu row -->
              <div class="sections" name="mid_menu" id="mid_menu">
                <span id="EXAM_defaults" name="EXAM_defaults" value="Defaults" class="btn btn-danger"><i class="fas fa-newspaper"></i>&nbsp;<b><?php echo xlt('Defaults'); ?></b></span>
                <span id="EXAM_TEXT" name="EXAM_TEXT" value="TEXT" class="btn btn-secondary"><i class="far fa-file-alt"></i>&nbsp;<b><?php echo xlt('Text'); ?></b></span>
                <span id="EXAM_DRAW" name="EXAM_DRAW" value="DRAW" class="btn btn-secondary">
                  <i class="fa fa-paint-brush fa-sm"> </i>&nbsp;<b><?php echo xlt('Draw'); ?></b></span>
                  <span id="EXAM_QP" name="EXAM_QP" title="<?php echo xla('Open the Quick Pick panels'); ?>" value="QP" class="btn btn-secondary">
                    <i class="fa fa-database fa-sm"> </i>&nbsp;<b><?php echo xlt('Quick Picks'); ?></b>
                  </span>
                    <?php
                  // output is defined above and if there are old visits, check for orders in eye_mag_functions:
                  // $output = priors_select("ALL",$id,$id,$pid);
                    ($output_priors == '') ? ($title = "There are no prior visits documented to display for this patient.") : ($title = "Display old exam findings and copy forward if desired");?>
                  <span id="PRIORS_ALL_left_text" name="PRIORS_ALL_left_text"
                  class="btn btn-secondary"><i class="fa fa-paste" title="<?php echo xla($title); ?>"></i>
                    <?php
                    if ($output_priors != '') {
                        echo $output_priors;
                    } else {
                        echo "<b>" . xlt("First visit: No Old Records") . "</b>";
                    }
                    ?>&nbsp;
                </span>
              </div>
              <!-- end of the exam selection row -->

              <!-- start of the Shorthand Entry Box -->
              <div id="EXAM_KB" name="EXAM_KB" class="kb borderShadow nodisplay">
                <span class="closeButton fa fa-times" id="CLOSE_kb" name="CLOSE_kb"></span>
                <span class="BAR2_kb" title="<?php echo xla('Click to display shorthand field names.'); ?>" class="ke"><b><?php echo xlt('Shorthand'); ?></b>
                </span>

                <a  target="_shorthand" href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php">
                <i title="<?php echo xla('Click for Shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i>
                </a><br />
                <textarea id="ALL_keyboard_left" name="ALL_keyboard_left" tabindex='1000'></textarea>
              </div>
              <!-- end of the Shorthand Entry Box -->

              <!-- end reporting div -->
              <span class="anchor" id="SELECTION_ROW_anchor"></span>

              <!-- Start of the exam sections -->
              <div class="loading" id="EXAM_sections_loading" name="EXAM_sections_loading">
                  <hr></hr>
                  <i class="fa fa-spinner fa-spin"></i>
              </div>

                  <div class="nodisplay" id="DA_EXAM_sections" name="DA_EXAM_sections">
                  <!-- start External Exam -->
                  <div id="EXT_1" name="EXT_1" class="clear_both">
                      <span class="anchor" id="EXT_anchor"></span>
                      <div id="EXT_left" class="exam_section_left borderShadow" >
                        <div id="EXT_left_text" class="TEXT_class">
                          <i class="closeButton_2 fa fa-paint-brush" title="<?php echo xla('External Draw Panel'); ?>" id="BUTTON_DRAW_EXT" name="BUTTON_DRAW_EXT"></i>
                          <i class="closeButton_3 fa fa-database" title="<?php echo xla('Quick Picks'); ?>" id="BUTTON_QP_EXT" name="BUTTON_QP_EXT"></i>
                          <i class="closeButton_4 fa fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open/Close the Shorthand Window and Codes"); ?>"></i>
                            <i class="closeButton fa fa-minus-circle" title="<?php echo xla('Open/Close External Exam panels'); ?>" id="BUTTON_TAB_EXT" name="BUTTON_TAB_EXT"></i>
                            <b><?php echo xlt('External Exam'); ?>:</b><div class="kb kb_left" title="<?php echo xla("External Exam Default Values"); ?>"><?php echo text('DEXT'); ?>

                            </div><br />
                          <div id="EXT_left_1">
                            <table>
                                <?php
                                  list($imaging,$episode) = display($pid, $encounter, "EXT");
                                  echo $episode;
                                ?>
                            </table>
                            <table>
                                  <tr>
                                    <td></td><td><?php echo xlt('R'); ?></td><td><?php echo xlt('L{{left}}'); ?></td>
                                  </tr>
                                  <tr>
                                      <td class="right" title="<?php echo xla('Levator Function'); ?>">
                                        <div class="kb kb_left"><?php echo xlt('LF{{levator function}}'); ?></div><?php echo xlt('Lev Fn{{levator function}}'); ?></td>
                                      <td><input  type="text"  name="RLF" id="RLF" class="EXT" value="<?php echo attr($RLF); ?>"></td>
                                      <td><input  type="text"  name="LLF" id="LLF" class="EXT" value="<?php echo attr($LLF); ?>"></td>
                                  </tr>
                                  <tr>
                                      <td class="right" title="<?php echo xla('Marginal Reflex Distance'); ?>">
                                        <div class="kb kb_left"><?php echo xlt('MRD{{marginal reflex distance}}'); ?></div><?php echo xlt('MRD{{marginal reflex distance}}'); ?></td>
                                      <td><input type="text" size="1" name="RMRD" id="RMRD" class="EXT" value="<?php echo attr($RMRD); ?>"></td>
                                      <td><input type="text" size="1" name="LMRD" id="LMRD" class="EXT" value="<?php echo attr($LMRD); ?>"></td>
                                  </tr>
                                  <tr>
                                      <td class="right" title="<?php echo xla('Vertical Fissure: central height between lid margins'); ?>">
                                        <div class="kb kb_left"><?php echo xlt('VF{{vertical fissure}}'); ?></div><?php echo xlt('Vert Fissure{{vertical fissure}}'); ?></td>
                                      <td><input type="text" size="1" name="RVFISSURE" id="RVFISSURE" class="EXT" value="<?php echo attr($RVFISSURE); ?>"></td>
                                      <td><input type="text" size="1" name="LVFISSURE" id="LVFISSURE" class="EXT" value="<?php echo attr($LVFISSURE); ?>"></td>
                                  </tr>
                                  <tr>
                                      <td class="right" title="<?php echo xla('Any carotid bruits appreciated?'); ?>">
                                        <div class="kb kb_left"><?php echo xlt('CAR{{carotid arteries}}'); ?></div><?php echo xlt('Carotid{{carotid arteries}}'); ?></td>
                                      <td><input  type="text"  name="RCAROTID" id="RCAROTID" class="EXT" class="EXT" value="<?php echo attr($RCAROTID); ?>"></td>
                                      <td><input  type="text"  name="LCAROTID" id="LCAROTID" class="EXT" value="<?php echo attr($LCAROTID); ?>"></td>
                                  </tr>
                                  <tr>
                                      <td class="right" title="<?php echo xla('Temporal Arteries'); ?>">
                                        <div class="kb kb_left"><?php echo xlt('TA{{temporal arteries}}'); ?></div>
                                        <?php echo xlt('Temp. Art.{{temporal arteries}}'); ?></td>
                                      <td><input type="text" size="1" name="RTEMPART" id="RTEMPART" class="EXT" value="<?php echo attr($RTEMPART); ?>"></td>
                                      <td><input type="text" size="1" name="LTEMPART" id="LTEMPART" class="EXT" value="<?php echo attr($LTEMPART); ?>"></td>
                                  </tr>
                                  <tr>
                                      <td class="right" title="<?php echo xla('Cranial Nerve 5: Trigeminal Nerve'); ?>">
                                        <div class="kb kb_left"><?php echo xlt('CN5{{cranial nerve five}}'); ?></div><?php echo xlt('CN V{{cranial nerve five}}'); ?></td>
                                      <td><input type="text" size="1" name="RCNV" id="RCNV" class="EXT" value="<?php echo attr($RCNV); ?>"></td>
                                      <td><input type="text" size="1" name="LCNV" id="LCNV" class="EXT" value="<?php echo attr($LCNV); ?>"></td>
                                  </tr>
                                  <tr>
                                      <td class="right" title="<?php echo xla('Cranial Nerve 7: Facial Nerve'); ?>">
                                        <div class="kb kb_left"><?php echo xlt('CN7{{cranial nerve seven}}'); ?></div><?php echo xlt('CN VII{{cranial nerve seven}}'); ?></td>
                                      <td><input type="text" size="1" name="RCNVII" class="EXT" id="RCNVII" value="<?php echo attr($RCNVII); ?>"></td>
                                      <td><input type="text" size="1" name="LCNVII" class="EXT" id="LCNVII" value="<?php echo attr($LCNVII); ?>"></td>
                                  </tr>

                                  <tr><td colspan=3 class="underline"><?php echo xlt('Hertel Exophthalmometry'); ?></td></tr>
                                  <tr class="center">
                                      <td>
                                          <input type="text" size="1" id="ODHERTEL" name="ODHERTEL" class="EXT" value="<?php echo attr($ODHERTEL); ?>">
                                          <i class="fa fa-minus"></i>
                                      </td>
                                      <td>
                                          <input type="text" size=3  id="HERTELBASE" name="HERTELBASE" class="EXT" value="<?php echo attr($HERTELBASE); ?>">
                                          <i class="fa fa-minus"></i>
                                      </td>
                                      <td>
                                          <input type="text" size=1  id="OSHERTEL" name="OSHERTEL" class="EXT" value="<?php echo attr($OSHERTEL); ?>">
                                      </td>
                                  </tr>
                                  <tr>
                                    <td><div class="kb kb_center"><?php echo xlt('RH{{right hertel measurement}}'); ?></div></td>
                                    <td><div class="kb kb_center"><?php echo xlt('HERT{{Hertel exophthalmometry}}'); ?></div></td>
                                    <td><div class="kb kb_center"><?php echo xlt('LH{{left hertel measurement}}'); ?></div></td>
                                  </tr>
                            </table>
                          </div>

                            <?php (($EXT_VIEW ?? null) == 1) ? ($display_EXT_view = "wide_textarea") : ($display_EXT_view = "narrow_textarea");?>
                            <?php ($display_EXT_view == "wide_textarea") ? ($marker = "fa-minus-square") : ($marker = "fa-plus-square");?>
                          <div id="EXT_text_list" name="EXT_text_list" class="borderShadow  <?php echo attr($display_EXT_view); ?>">
                              <span class="top_right far <?php echo attr($marker); ?>" name="EXT_text_view" id="EXT_text_view"></span>
                              <table cellspacing="0" cellpadding="0">
                                  <tr>
                                      <th>
                                          <i class="float-left fas fa-times copier" id="clear_EXT_R" title="<?php echo xla('Clear Right side values'); ?>"></i>
                                          <i class="fas fa-angle-double-down copier" id="EXT_defaults_R" title="<?php echo xla('Enter defaults for Right side');?>"></i>
                                          <?php echo xlt('Right'); ?>
                                          <i class="float-right fas fa-arrow-right copier" id="EXT_R_L" title="<?php echo xla('Copy Right to Left');?>"></i>
                                      </th>
                                      <th></th>
                                      <th>
                                          <i class="float-left fas fa-arrow-left copier" id="EXT_L_R" title="<?php echo xla('Copy Left to Right');?>"></i>
                                          <i class="fas fa-angle-double-down copier" id="EXT_defaults_L" title="<?php echo xla('Enter defaults values for Left side');?>"></i>
                                          <?php echo xlt('Left'); ?>
                                          <i class="delButton_2 fas fa-times copier" id="clear_EXT_L" title="<?php echo xla('Clear Left side values'); ?>"></i>
                                      </th>
                                  </tr>
                                  <tr>
                                      <td><textarea name="RBROW" id="RBROW" class="right EXT"><?php echo text($RBROW); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_RBROW_LBROW"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_LBROW_RBROW"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Brow'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RB{{right brow}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LB{{left brow}}'); ?></div>
                                      </td>
                                      <td><textarea name="LBROW" id="LBROW" class="left EXT"><?php echo text($LBROW); ?></textarea>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="RUL" id="RUL" class="right EXT"><?php echo text($RUL); ?></textarea></td>
                                      <td><div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_RUL_LUL"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_LUL_RUL"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Upper Lids'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RUL{{right upper eyelid}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LUL{{left upper eyelid}}'); ?></div>
                                      </td>
                                      <td><textarea name="LUL" id="LUL" class="left EXT"><?php echo text($LUL); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="RLL" id="RLL" class="right EXT"><?php echo text($RLL); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_RLL_LLL"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_LLL_RLL"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Lower Lids'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RLL{{right lower eyelid}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LLL{{left lower eyelid}}'); ?></div></td>
                                      <td><textarea name="LLL" id="LLL" class="left EXT"><?php echo text($LLL); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="RMCT" id="RMCT" class="right EXT"><?php echo text($RMCT); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_RMCT_LMCT"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_LMCT_RMCT"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Medial Canthi'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RMC{{right medial canthus}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LMC{{left medial chathus}}'); ?></div></td>
                                      <td><textarea name="LMCT" id="LMCT" class="left EXT"><?php echo text($LMCT); ?></textarea></td>
                                  </tr>
                                   <tr>
                                      <td><textarea name="RADNEXA" id="RADNEXA" class="right EXT"><?php echo text($RADNEXA); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_RADNEXA_LADNEXA"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_LADNEXA_RADNEXA"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Adnexa'); ?></div>
                                            <div class="kb kb_left"><?php echo xlt('RAD{{right adnexa}}'); ?></div>
                                            <div class="kb kb_right"><?php echo xlt('LAD{{left adnexa}}'); ?></div></td>
                                      <td><textarea name="LADNEXA" id="LADNEXA" class="left EXT"><?php echo text($LADNEXA); ?></textarea></td>
                                  </tr>
                              </table>
                          </div>  <br />
                          <div id="EXT_COMMENTS_DIV" class="QP_lengthen" >
                            <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left"><?php echo xlt('ECOM{{external comments abbreviation}}'); ?></div>
                            <br />
                            <textarea id="EXT_COMMENTS" name="EXT_COMMENTS" class="EXT"><?php echo text($EXT_COMMENTS); ?></textarea>
                          </div>
                        </div>
                      </div>
                      <div id="EXT_right" name="EXT_right" class="exam_section_right borderShadow text_clinical">
                        <?php display_draw_section("EXT", $encounter, $pid); ?>
                        <div id="PRIORS_EXT_left_text" name="PRIORS_EXT_left_text" class="PRIORS_class PRIORS">
                            <i class="fa fa-spinner fa-spin"></i>
                        </div>
                          <div id="QP_EXT" name="QP_EXT" class="QP_class">
                              <input type="hidden" id="EXT_prefix" name="EXT_prefix" value="<?php echo attr($EXT_prefix ?? ''); ?>">

                              <span class="closeButton fa fa-times float-right z100" id="BUTTON_TEXTD_EXT" name="BUTTON_TEXTD_EXT" value="1"></span>
                              <div class="qp10">
                                  <span class="eye_button eye_button_selected" id="EXT_prefix_off" name="EXT_prefix_off" onclick="$('#EXT_prefix').val('').trigger('change');"><?php echo xlt('Off'); ?></span>
                                  <span class="eye_button" id="EXT_defaults" name="EXT_defaults"><?php echo xlt('Defaults'); ?></span>
                                  <span class="eye_button" id="EXT_prefix_no" name="EXT_prefix_no" onclick="$('#EXT_prefix').val('no').trigger('change');"> <?php echo xlt('no'); ?> </span>
                                  <span class="eye_button" id="EXT_prefix_trace" name="EXT_prefix_trace"  onclick="$('#EXT_prefix').val('trace').trigger('change');"> <?php echo xlt('tr'); ?> </span>
                                  <span class="eye_button" id="EXT_prefix_1" name="EXT_prefix_1"  onclick="$('#EXT_prefix').val('+1').trigger('change');"> <?php echo xlt('+1'); ?> </span>
                                  <span class="eye_button" id="EXT_prefix_2" name="EXT_prefix_2"  onclick="$('#EXT_prefix').val('+2').trigger('change');"> <?php echo xlt('+2'); ?> </span>
                                  <span class="eye_button" id="EXT_prefix_3" name="EXT_prefix_3"  onclick="$('#EXT_prefix').val('+3').trigger('change');"> <?php echo xlt('+3'); ?> </span>
                                    <?php echo $selector = priors_select("EXT", $id, $id, $pid); ?>
                              </div>
                              <div name="QP_11">
                                  <span class="eye_button" id="EXT_prefix_1mm" name="EXT_prefix_1mm"  onclick="$('#EXT_prefix').val('1mm').trigger('change');"> 1<?php echo xlt('mm{{millimeters}}'); ?> </span>  <br />
                                  <span class="eye_button" id="EXT_prefix_2mm" name="EXT_prefix_2mm"  onclick="$('#EXT_prefix').val('2mm').trigger('change');"> 2<?php echo xlt('mm{{millimeters}}'); ?> </span>  <br />
                                  <span class="eye_button" id="EXT_prefix_3mm" name="EXT_prefix_3mm"  onclick="$('#EXT_prefix').val('3mm').trigger('change');"> 3<?php echo xlt('mm{{millimeters}}'); ?> </span>  <br />
                                  <span class="eye_button" id="EXT_prefix_4mm" name="EXT_prefix_4mm"  onclick="$('#EXT_prefix').val('4mm').trigger('change');"> 4<?php echo xlt('mm{{millimeters}}'); ?> </span>  <br />
                                  <span class="eye_button" id="EXT_prefix_5mm" name="EXT_prefix_5mm"  onclick="$('#EXT_prefix').val('5mm').trigger('change');"> 5<?php echo xlt('mm{{millimeters}}'); ?> </span>  <br />
                                  <span class="eye_button" id="EXT_prefix_medial" name="EXT_prefix_medial"  onclick="$('#EXT_prefix').val('medial').trigger('change');"><?php echo xlt('med{{medial}}'); ?></span>
                                  <span class="eye_button" id="EXT_prefix_lateral" name="EXT_prefix_lateral"  onclick="$('#EXT_prefix').val('lateral').trigger('change');"><?php echo xlt('lat{{lateral}}'); ?></span>
                                  <span class="eye_button" id="EXT_prefix_superior" name="EXT_prefix_superior"  onclick="$('#EXT_prefix').val('superior').trigger('change');"><?php echo xlt('sup{{superior}}'); ?></span>
                                  <span class="eye_button" id="EXT_prefix_inferior" name="EXT_prefix_inferior"  onclick="$('#EXT_prefix').val('inferior').trigger('change');"><?php echo xlt('inf{{inferior}}'); ?></span>
                                  <span class="eye_button" id="EXT_prefix_anterior" name="EXT_prefix_anterior"  onclick="$('#EXT_prefix').val('anterior').trigger('change');"><?php echo xlt('ant{{anterior}}'); ?></span>  <br />
                                  <span class="eye_button" id="EXT_prefix_mid" name="EXT_prefix_mid"  onclick="$('#EXT_prefix').val('mid').trigger('change');"><?php echo xlt('mid{{middle}}'); ?></span>  <br />
                                  <span class="eye_button" id="EXT_prefix_posterior" name="EXT_prefix_posterior"  onclick="$('#EXT_prefix').val('posterior').trigger('change');"><?php echo xlt('post{{posterior}}'); ?></span>  <br />
                                  <span class="eye_button" id="EXT_prefix_deep" name="EXT_prefix_deep"  onclick="$('#EXT_prefix').val('deep').trigger('change');"><?php echo xlt('deep'); ?></span>
                                  <br />
                                  <br />
                                  <span class="eye_button" id="EXT_prefix_clear" name="EXT_prefix_clear"
                                  title="<?php echo xla('This will clear the data from all External Exam fields'); ?>"
                                  onclick="$('#EXT_prefix').val('clear').trigger('change');"><?php echo xlt('clear'); ?></span>
                              </div>

                              <div id="EXT_QP_block1" name="EXT_QP_block1" class="QP_block borderShadow text_clinical" >

                                <?php
                                echo $QP_ANTSEG = display_QP("EXT", $provider_id); ?>
                              </div>
                          </div>
                      </div>
                </div>
                <!-- end External Exam -->

                <!-- start Anterior Segment -->
                <div id="ANTSEG_1" class="clear_both">
                  <div id="ANTSEG_left" name="ANTSEG_left" class="exam_section_left borderShadow">
                    <span class="anchor" id="ANTSEG_anchor"></span>
                    <div class="TEXT_class" id="ANTSEG_left_text">
                      <span class="closeButton_2 fa fa-paint-brush" title="<?php echo xla('Open/Close the Anterior Segment drawing panel'); ?>" id="BUTTON_DRAW_ANTSEG" name="BUTTON_DRAW_ANTSEG"></span>
                      <i class="closeButton_3 fa fa-database"title="<?php echo xla('Open/Close the Anterior Segment Exam Quick Picks panel'); ?>" id="BUTTON_QP_ANTSEG" name="BUTTON_QP_ANTSEG"></i>
                      <i class="closeButton_4 fa fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open/Close the Shorthand Window and display Shorthand Codes"); ?>"></i>
                        <i class="closeButton fa fa-minus-circle" title="<?php echo xla('Open/Close Ant Seg panels'); ?>" id="BUTTON_TAB_ANTSEG" name="BUTTON_TAB_ANTSEG"></i>
                        <b><?php echo xlt('Anterior Segment'); ?>:</b><div class="kb kb_left" title="<?php echo xla("Anterior Segment Default Values"); ?>"><?php echo text('DAS'); ?></div><br />
                      <div id="ANTSEG_left_1" class="text_clinical">
                        <table>
                            <?php
                            list($imaging,$episode) = display($pid, $encounter, "ANTSEG");
                            echo $episode;
                            ?>
                        </table>
                        <table  cellspacing="0" cellpadding="0">
                          <tr>
                              <td></td><td><?php echo xlt('R{{right}}'); ?></td><td><?php echo xlt('L{{left}}'); ?></td>
                          </tr>
                          <tr>
                              <td class="right" title="<?php echo xla('Gonioscopy'); ?>">
                                <div class="kb kb_left"><?php echo 'G'; ?></div>
                                <?php echo xlt('Gonio{{Gonioscopy abbreviation}}'); ?>
                              </td>
                              <td><input type="text" name="ODGONIO" id="ODGONIO" value="<?php echo attr($ODGONIO); ?>"></td>
                              <td><input type="text" name="OSGONIO" id="OSGONIO" value="<?php echo attr($OSGONIO); ?>"></td>
                          </tr>
                          <tr>
                              <td class="right" title="<?php echo xla('Pachymetry: Central Corneal Thickness'); ?>">
                                <div class="kb kb_left"><?php echo 'PACH'; ?></div>
                                <?php echo xlt('Pachy{{Pachymetry}}'); ?>
                              </td>
                              <td><input type="text" name="ODKTHICKNESS" id="ODKTHICKNESS" value="<?php echo attr($ODKTHICKNESS); ?>">
                              </td>
                              <td><input type="text" name="OSKTHICKNESS" id="OSKTHICKNESS" value="<?php echo attr($OSKTHICKNESS); ?>">
                              </td>
                          </tr>
                          <tr>
                              <td class="right" title="<?php echo xla('Schirmers I (w/o anesthesia)'); ?>">
                                <div class="kb kb_left"><?php echo 'SCH1'; ?></div>
                                <?php echo xlt('Schirmers I'); ?> </td>
                              <td><input type="text" name="ODSCHIRMER1" id="ODSCHIRMER1" value="<?php echo attr($ODSCHIRMER1); ?>">
                                </td>
                              <td><input type="text" name="OSSCHIRMER1" id="OSSCHIRMER1" value="<?php echo attr($OSSCHIRMER1); ?>">
                                </td>
                          </tr>
                           <tr>
                              <td class="right" title="<?php echo xla('Schirmers II (w/ anesthesia)'); ?>">
                                <div class="kb kb_left"><?php echo 'SCH2'; ?></div>
                                <?php echo xlt('Schirmers II'); ?> </td>
                              <td><input type="text" name="ODSCHIRMER2" id="ODSCHIRMER2" value="<?php echo attr($ODSCHIRMER2); ?>">
                              </td>
                              <td><input type="text" name="OSSCHIRMER2" id="OSSCHIRMER2" value="<?php echo attr($OSSCHIRMER2); ?>">
                              </td>
                          </tr>
                          <tr>
                              <td class="right" title="<?php echo xla('Tear Break Up Time'); ?>">
                                <div class="kb kb_left"><?php echo 'TBUT'; ?></div>
                                <?php echo xlt('TBUT{{tear breakup time}}'); ?> </td>
                              <td><input type="text" name="ODTBUT" id="ODTBUT" value="<?php echo attr($ODTBUT); ?>"></td>
                              <td><input type="text" name="OSTBUT" id="OSTBUT" value="<?php echo attr($OSTBUT); ?>"></td>
                          </tr>
                          <tr>
                            <td colspan="3" rowspan="4" id="dil_box" nowrap="">
                              <br />
                              <span id="dil_listbox_title"><?php echo xlt('Dilation'); ?>:</span>

                                <input type="text" class="float-right" title="<?php echo xla('Dilation Time'); ?>" id="DIL_MEDS" name="DIL_MEDS" value="<?php
                                if ($DIL_MEDS) {
                                    echo attr($DIL_MEDS); }
                                ?>" placeholder="Time"/>
                                <br />
                                <?php
                              //TODO: convert to list.  How about a jquery multiselect box, stored in DIL_MEDS field with "|" as a delimiter? OK...
                              //create a list of all our options for dilation Eye_Drug_Dilation
                              //create the jquery selector.  Store results in DB.
                              //on loading page, and on READ-ONLY, need to convert DIL_MEDS to correct thing here.
                              //We need times too...
                              //OK. Second delimiter @ for time, within "|" delimiters
                              //Do we know what time it is?  Yes from IOPTIME code?....
                                ?>
                              <table id="dil_listbox">
                                <tr>
                                  <td><input type="checkbox" class="dil_drug" id="CycloMydril" name="CYCLOMYDRIL" value="Cyclomydril" <?php if ($CYCLOMYDRIL == 'Cyclomydril') {
                                            echo "checked='checked'";
                                                                                                                                      } ?> /><label for="CycloMydril" class="input-helper input-helper--checkbox dil_drug_label"><?php echo xlt('CycloMydril'); ?></label>
                                  </td>
                                  <td><input type="checkbox" class="dil_drug" id="Tropicamide" name="TROPICAMIDE" value="Tropicamide 2.5%" <?php if ($TROPICAMIDE == 'Tropicamide 2.5%') {
                                            echo "checked='checked'";
                                                                                                                                           } ?> /><label for="Tropicamide" class="input-helper input-helper--checkbox dil_drug_label"><?php echo xlt('Tropic 2.5%'); ?></label>
                                  </td>
                                </tr>
                                <tr>
                                  <td><input type="checkbox" class="dil_drug" id="Neo25" name="NEO25" value="Neosynephrine 2.5%"  <?php if ($NEO25 == 'Neosynephrine 2.5%') {
                                            echo "checked='checked'";
                                                                                                                                  } ?> /><label for="Neo25" class="input-helper input-helper--checkbox dil_drug_label"><?php echo xlt('Neo 2.5%'); ?></label>
                                  </td>
                                  <td><input type="checkbox" class="dil_drug" id="Neo10" name="NEO10" value="Neosynephrine 10%"  <?php if (($NEO10 ?? null) == 'Neosynephrine 10%') {
                                            echo "checked='checked'";
                                                                                                                                 } ?> /><label for="Neo10" class="input-helper input-helper--checkbox dil_drug_label"><?php echo xlt('Neo 10%'); ?></label>
                                  </td>
                                </tr>
                                <tr>
                                  <td><input type="checkbox" class="dil_drug" id="Cyclogyl" name="CYCLOGYL" value="Cyclopentolate 1%"  <?php if ($CYCLOGYL == 'Cyclopentolate 1%') {
                                            echo "checked='checked'";
                                                                                                                                       } ?> /><label for="Cyclogyl" class="input-helper input-helper--checkbox dil_drug_label"><?php echo xlt('Cyclo 1%'); ?></label>
                                  </td>
                                  <td><input type="checkbox" class="dil_drug" id="Atropine" name="ATROPINE" value="Atropine 1%"  <?php if ($ATROPINE == 'Atropine 1%') {
                                        echo "checked='checked'";
                                                                                                                                 } ?> /><label for="Atropine" class="input-helper input-helper--checkbox dil_drug_label"><?php echo xlt('Atropine 1%'); ?></label>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                      </div>

                        <?php (($ANTSEG_VIEW ?? null) == '1') ? ($display_ANTSEG_view = "wide_textarea") : ($display_ANTSEG_view = "narrow_textarea");?>
                        <?php ($display_ANTSEG_view == "wide_textarea") ? ($marker = "fa-minus-square") : ($marker = "fa-plus-square");?>
                      <div id="ANTSEG_text_list" name="ANTSEG_text_list" class="borderShadow <?php echo attr($display_ANTSEG_view); ?>" >
                              <span class="top_right far <?php echo attr($marker); ?>" name="ANTSEG_text_view" id="ANTSEG_text_view"></span>
                              <table cellspacing="0" cellpadding="0">
                                  <tr>
                                      <th>
                                          <i class="float-left fas fa-times copier" id="clear_ANTSEG_OD" title="<?php echo xla('Clear OD{{right eye}} values'); ?>"></i>
                                          <i class="fas fa-angle-double-down copier" id="ANTSEG_defaults_OD" title="<?php echo xla('Enter default values for OD{{right eye}}');?>"></i>
                                          <?php echo xlt('OD{{right eye}}'); ?>
                                          <i class="float-right fas fa-arrow-right copier" id="ANTSEG_OD_OS" title="<?php echo xla('Copy OD{{right eye}} values to') . " " . xla('OS{{left eye}}');?>"></i>
                                      </th>
                                      <th></th>
                                      <th>
                                          <i class="float-left fas fa-arrow-left copier" id="ANTSEG_OS_OD" title="<?php echo xla('Copy OS{{left eye}} values to') . " " . xla('OD{{right eye}}');?>"></i>
                                          <i class="fas fa-angle-double-down copier" id="ANTSEG_defaults_OS" title="<?php echo xla('Enter defaults values for OS{{left eye}}');?>"></i>
                                          <?php echo xlt('OS{{left eye}}'); ?>
                                          <i class="delButton_2 fas fa-times copier" id="clear_ANTSEG_OS" title="<?php echo xla('Delete OS{{left eye}} values'); ?>"></i>
                                      </th>
                                  </tr>
                                  <tr>
                                      <td>
                                        <textarea name="ODCONJ" id="ODCONJ" class="right ANTSEG"><?php echo text($ODCONJ); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODCONJ_OSCONJ"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSCONJ_ODCONJ"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Conj{{Conjunctiva}}'); ?> / <?php echo xlt('Sclera'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RC{{right conjunctiva}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LC{{left conjunctiva}}'); ?></div></td>
                                      <td><textarea name="OSCONJ" id="OSCONJ" class="left ANTSEG"><?php echo text($OSCONJ); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODCORNEA" id="ODCORNEA" class="right ANTSEG"><?php echo text($ODCORNEA); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODCORNEA_OSCORNEA"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSCORNEA_ODCORNEA"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Cornea'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RK{{right cornea}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LK{{left cornea}}'); ?></div>
                                      </td>
                                      <td><textarea name="OSCORNEA" id="OSCORNEA" class="left ANTSEG"><?php echo text($OSCORNEA); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODAC" id="ODAC" class="right ANTSEG"><?php echo text($ODAC); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODAC_OSAC"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSAC_ODAC"></i>
                                              </div>
                                          </div><div class="ident"><?php echo xlt('A/C{{anterior chamber}}'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RAC{{right anterior chamber}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LAC{{left anterior chamber}}'); ?></div>
                                      </td>
                                      <td><textarea name="OSAC" id="OSAC" class="left ANTSEG"><?php echo text($OSAC); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODLENS" id="ODLENS" class="right ANTSEG"><?php echo text($ODLENS); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODLENS_OSLENS"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSLENS_ODLENS"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Lens'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RL{{right lens}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LL{{left lens}}'); ?></div>
                                      </td>
                                      <td><textarea name="OSLENS" id="OSLENS" class="left ANTSEG"><?php echo text($OSLENS); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODIRIS" id="ODIRIS" class="right ANTSEG"><?php echo text($ODIRIS); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODIRIS_OSIRIS"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSIRIS_ODIRIS"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Iris'); ?></div>
                                            <div class="kb kb_left"><?php echo xlt('RI{{right iris}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LI{{left iris}}'); ?></div>
                                      </td>
                                      <td><textarea name="OSIRIS" id="OSIRIS" class="left ANTSEG"><?php echo text($OSIRIS); ?></textarea></td>
                                  </tr>
                              </table>
                      </div>  <br />
                      <div class="QP_lengthen" id="ANTSEG_COMMENTS_DIV">
                        <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left"><?php echo xlt('ACOM{{Anterior Segment}}'); ?> </div><br />
                          <textarea id="ANTSEG_COMMENTS" name="ANTSEG_COMMENTS"><?php echo text($ANTSEG_COMMENTS); ?></textarea>
                      </div>
                    </div>
                  </div>
                  <div id="ANTSEG_right" name=="ANTSEG_right" class="exam_section_right borderShadow text_clinical ">
                      <div id="PRIORS_ANTSEG_left_text" name="PRIORS_ANTSEG_left_text" class="PRIORS_class PRIORS">
                                      <i class="fa fa-spinner fa-spin"></i>
                      </div>
                        <?php display_draw_section("ANTSEG", $encounter, $pid); ?>
                      <div id="QP_ANTSEG" name="QP_ANTSEG" class="QP_class">
                          <input type="hidden" id="ANTSEG_prefix" name="ANTSEG_prefix" value="">
                          <div class="qp10">
                              <span  class="eye_button eye_button_selected" id="ANTSEG_prefix_off" name="ANTSEG_prefix_off"  onclick="$('#ANTSEG_prefix').val('off').trigger('change');"><?php echo xlt('Off'); ?> </span>
                              <span  class="eye_button" id="ANTSEG_defaults" name="ANTSEG_defaults"><?php echo xlt('Defaults'); ?></span>
                              <span  class="eye_button" id="ANTSEG_prefix_no" name="ANTSEG_prefix_no" onclick="$('#ANTSEG_prefix').val('no').trigger('change');"> <?php echo xlt('no'); ?> </span>
                              <span  class="eye_button" id="ANTSEG_prefix_trace" name="ANTSEG_prefix_trace"  onclick="$('#ANTSEG_prefix').val('trace').trigger('change');"> <?php echo xlt('tr'); ?> </span>
                              <span  class="eye_button" id="ANTSEG_prefix_1" name="ANTSEG_prefix_1"  onclick="$('#ANTSEG_prefix').val('+1').trigger('change');"> <?php echo xlt('+1'); ?> </span>
                              <span  class="eye_button" id="ANTSEG_prefix_2" name="ANTSEG_prefix_2"  onclick="$('#ANTSEG_prefix').val('+2').trigger('change');"> <?php echo xlt('+2'); ?> </span>
                              <span  class="eye_button" id="ANTSEG_prefix_3" name="ANTSEG_prefix_3"  onclick="$('#ANTSEG_prefix').val('+3').trigger('change');"> <?php echo xlt('+3'); ?> </span>
                                <?php echo $selector = priors_select("ANTSEG", $id, $id, $pid); ?>
                          </div>
                          <div name="QP_11">
                              <span  class="eye_button" id="ANTSEG_prefix_1mm" name="ANTSEG_prefix_1mm"  onclick="$('#ANTSEG_prefix').val('1mm').trigger('change');"> <?php echo xlt('1mm'); ?> </span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_2mm" name="ANTSEG_prefix_2mm"  onclick="$('#ANTSEG_prefix').val('2mm').trigger('change');"> <?php echo xlt('2mm'); ?> </span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_3mm" name="ANTSEG_prefix_3mm"  onclick="$('#ANTSEG_prefix').val('3mm').trigger('change');"> <?php echo xlt('3mm'); ?> </span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_4mm" name="ANTSEG_prefix_4mm"  onclick="$('#ANTSEG_prefix').val('4mm').trigger('change');"> <?php echo xlt('4mm'); ?> </span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_5mm" name="ANTSEG_prefix_5mm"  onclick="$('#ANTSEG_prefix').val('5mm').trigger('change');"> <?php echo xlt('5mm'); ?> </span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_medial" name="ANTSEG_prefix_medial"  onclick="$('#ANTSEG_prefix').val('medial').trigger('change');"><?php echo xlt('med{{medial}}'); ?></span>
                              <span  class="eye_button" id="ANTSEG_prefix_lateral" name="ANTSEG_prefix_lateral"  onclick="$('#ANTSEG_prefix').val('lateral').trigger('change');"><?php echo xlt('lat{{lateral}}'); ?></span>
                              <span  class="eye_button" id="ANTSEG_prefix_superior" name="ANTSEG_prefix_superior"  onclick="$('#ANTSEG_prefix').val('superior').trigger('change');"><?php echo xlt('sup{{superior}}'); ?></span>
                              <span  class="eye_button" id="ANTSEG_prefix_inferior" name="ANTSEG_prefix_inferior"  onclick="$('#ANTSEG_prefix').val('inferior').trigger('change');"><?php echo xlt('inf{{inferior}}'); ?></span>
                              <span  class="eye_button" id="ANTSEG_prefix_anterior" name="ANTSEG_prefix_anterior"  onclick="$('#ANTSEG_prefix').val('anterior').trigger('change');"><?php echo xlt('ant{{anterior}}'); ?></span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_mid" name="ANTSEG_prefix_mid"  onclick="$('#ANTSEG_prefix').val('mid').trigger('change');"><?php echo xlt('mid'); ?></span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_posterior" name="ANTSEG_prefix_posterior"  onclick="$('#ANTSEG_prefix').val('posterior').trigger('change');"><?php echo xlt('post{{posterior}}'); ?></span>  <br />
                              <span  class="eye_button" id="ANTSEG_prefix_deep" name="ANTSEG_prefix_deep"  onclick="$('#ANTSEG_prefix').val('deep').trigger('change');"><?php echo xlt('deep'); ?></span>
                              <br />
                              <br />
                              <span class="eye_button" id="ANTSEG_prefix_clear" name="ANTSEG_prefix_clear" title="<?php echo xla('This will clear the data from all Anterior Segment Exam fields'); ?>" onclick="$('#ANTSEG_prefix').val('clear').trigger('change');"><?php echo xlt('clear'); ?></span>

                          </div>
                          <div class="QP_block borderShadow text_clinical " >
                            <?php echo $QP_ANTSEG = display_QP("ANTSEG", $provider_id); ?>
                          </div>
                          <span class="closeButton fa fa-times float-right z100" id="BUTTON_TEXTD_ANTSEG" name="BUTTON_TEXTD_ANTSEG"></span>
                      </div>
                  </div>
                </div>
                <!-- end Ant Seg -->

                <!-- start POSTSEG -->
                <div id="POSTSEG_1" class="clear_both" >
                  <div id="RETINA_left" name="RETINA_left" class="exam_section_left borderShadow">
                    <span class="anchor" id="RETINA_anchor"></span>
                    <div class="TEXT_class" id="RETINA_left_text" name="RETINA_left_text">
                      <span class="closeButton_2 fa fa-paint-brush" title="<?php echo xla('Open/Close the Retina drawing panel'); ?>" id="BUTTON_DRAW_RETINA" name="BUTTON_DRAW_RETINA"></span>
                      <i class="closeButton_3 fa fa-database"title="<?php echo xla('Open/Close the Retinal Exam Quick Picks panel'); ?>" id="BUTTON_QP_RETINA" name="BUTTON_QP_RETINA"></i>
                      <i class="closeButton_4 fa fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open/Close the Shorthand Window and display Shorthand Codes"); ?>"></i>
                        <i class="closeButton fa fa-minus-circle" title="<?php echo xla('Open/Close Post Seg panels'); ?>" id="BUTTON_TAB_POSTSEG" name="BUTTON_TAB_POSTSEG"></i>
                        <b><?php echo xlt('Retina'); ?>:</b><div class="kb kb_left" title="<?php echo xla("Retina Default Values"); ?>"><?php echo text('DRET'); ?></div>
                        <input type="checkbox" id="DIL_RISKS" name="DIL_RISKS" value="on" <?php if ($DIL_RISKS == 'on') {
                            echo "checked='checked'";
                                                                                          } ?>>
                        <label for="DIL_RISKS" class="input-helper input-helper--checkbox"><?php echo xlt('Dilation orders/risks reviewed'); ?></label>
                      <br />
                      <div id="RETINA_left_1" class="text_clinical">
                        <table>
                            <?php
                              list($imaging,$episode) = display($pid, $encounter, "POSTSEG");
                              echo $episode;
                            ?>
                        </table>

                        <table>
                            <tr class="bold">
                                <td></td>
                                <td><?php echo xlt('OD{{right eye}}'); ?> </td><td><?php echo xlt('OS{{left eye}}'); ?> </td>
                            </tr>
                            <tr>

                                <td class="bold right">
                                    <div class="kb kb_left"><?php echo 'CUP'; ?></div>
                                    <?php echo xlt('C/D Ratio{{cup to disc ration}}'); ?>:</td>
                                <td>
                                    <input type="text" class="RETINA" name="ODCUP" size="4" id="ODCUP" value="<?php echo attr($ODCUP); ?>">
                                </td>
                                <td>
                                    <input type="text" class="RETINA" name="OSCUP" size="4" id="OSCUP" value="<?php echo attr($OSCUP); ?>">
                                </td>
                            </tr>

                            <tr>
                                <td class="bold right">
                                    <div class="kb kb_left"><?php echo 'CMT'; ?></div>
                                    <?php echo xlt('CMT{{Central Macular Thickness}}'); ?>:</td>
                                <td>
                                    <input class="RETINA" type="text" name="ODCMT" size="4" id="ODCMT" value="<?php echo attr($ODCMT); ?>">
                                </td>
                                <td>
                                    <input class="RETINA" type="text" name="OSCMT" size="4" id="OSCMT" value="<?php echo attr($OSCMT); ?>">
                                </td>
                            </tr>
                        </table>
                        <br />
                        <table>
                            <?php
                            list($imaging,$episode) = display($pid, $encounter, "NEURO");
                            echo $episode;
                            ?>
                        </table>
                      </div>

                        <?php (($RETINA_VIEW ?? null) == 1) ? ($display_RETINA_view = "wide_textarea") : ($display_RETINA_view = "narrow_textarea");?>
                        <?php ($display_RETINA_view == "wide_textarea") ? ($marker = "fa-minus-square") : ($marker = "fa-plus-square");?>
                      <div>
                        <div id="RETINA_text_list" name="RETINA_text_list" class="borderShadow  <?php echo attr($display_RETINA_view); ?>">
                              <span class="top_right far <?php echo attr($marker); ?>" name="RETINA_text_view" id="RETINA_text_view"></span>
                              <table cellspacing="0" cellpadding="0">
                                  <tr>
                                      <th>
                                          <i class="float-left fas fa-times copier" id="clear_RETINA_OD" title="<?php echo xla('Clear OD{{right eye}} values'); ?>"></i>
                                          <i class="fas fa-angle-double-down copier" id="RETINA_defaults_OD" title="<?php echo xla('Enter default values for OD{{right eye}}');?>"></i>
                                          <?php echo xlt('OD{{right eye}}'); ?>
                                          <i class="float-right fas fa-arrow-right copier" id="RETINA_OD_OS" title="<?php echo xla('Copy OD{{right eye}} values to') . " " . xla('OS{{left eye}}');?>"></i>
                                      </th>
                                      <th></th>
                                      <th>
                                          <i class="float-left fas fa-arrow-left copier" id="RETINA_OS_OD" title="<?php echo xla('Copy OS{{left eye}} values to') . " " . xla('OD{{right eye}}');?>"></i>
                                          <i class="fas fa-angle-double-down copier" id="RETINA_defaults_OS" title="<?php echo xla('Enter defaults values for OS{{left eye}}');?>"></i>
                                          <?php echo xlt('OS{{left eye}}'); ?>
                                          <i class="delButton_2 fas fa-times copier" id="clear_RETINA_OS" title="<?php echo xla('Delete OS{{left eye}} values'); ?>"></i>
                                      </th>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODDISC" id="ODDISC"  class="RETINA right"><?php echo text($ODDISC); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODDISC_OSDISC"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSDISC_ODDISC"></i>
                                              </div>
                                          </div>

                                          <div class="ident"><?php echo xlt('Disc'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RD{{right disc}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LD{{left disc}}'); ?></div></td>
                                      <td><textarea name="OSDISC" id="OSDISC" class="left RETINA"><?php echo text($OSDISC); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODMACULA" id="ODMACULA" class="RETINA right"><?php echo text($ODMACULA); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODMACULA_OSMACULA"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSMACULA_ODMACULA"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Macula'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RMAC{{right macula}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LMAC{{left macula}}'); ?></div>
                                      </td>
                                      <td><textarea name="OSMACULA" id="OSMACULA" class="left RETINA"><?php echo text($OSMACULA); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODVESSELS" id="ODVESSELS" class="RETINA right"><?php echo text($ODVESSELS); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODVESSELS_OSVESSELS"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSVESSELS_ODVESSELS"></i>
                                              </div>
                                          </div>

                                          <div class="ident"><?php echo xlt('Vessels'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RV{{right vessels}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LV{{left vessels}}'); ?></div></td>
                                      <td><textarea name="OSVESSELS" id="OSVESSELS" class="left RETINA"><?php echo text($OSVESSELS); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODVITREOUS" id="ODVITREOUS" class="RETINA right"><?php echo text($ODVITREOUS); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODVITREOUS_OSVITREOUS"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSVITREOUS_ODVITREOUS"></i>
                                              </div>
                                          </div>

                                          <div class="ident"><?php echo xlt('Vitreous'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RVIT{{right vitreous}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LVIT{{left vitreous}}'); ?></div></td>
                                      <td><textarea name="OSVITREOUS" id="OSVITREOUS" class="left RETINA"><?php echo text($OSVITREOUS); ?></textarea></td>
                                  </tr>
                                  <tr>
                                      <td><textarea name="ODPERIPH" id="ODPERIPH" class="RETINA right"><?php echo text($ODPERIPH); ?></textarea></td>
                                      <td>
                                          <div class="overlay">
                                              <div class="cpf_left copier"><i class="fa fa-arrow-left" id="cpf_ODPERIPH_OSPERIPH"></i>
                                              </div>
                                              <div class="cpf_right copier"><i class="fa fa-arrow-right" id="cpf_OSPERIPH_ODPERIPH"></i>
                                              </div>
                                          </div>
                                          <div class="ident"><?php echo xlt('Periph{{peripheral retina}}'); ?></div>
                                          <div class="kb kb_left"><?php echo xlt('RP{{right peripheral retina}}'); ?></div>
                                          <div class="kb kb_right"><?php echo xlt('LP{{left peripheral retina}}'); ?></div>
                                      </td>
                                      <td><textarea name="OSPERIPH" id="OSPERIPH" class="left RETINA"><?php echo text($OSPERIPH); ?></textarea></td>
                                  </tr>
                              </table>
                        </div>
                      </div>
                      <div class="QP_lengthen" id="RETINA_COMMENTS_DIV">
                          <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left"><?php echo xlt('RCOM{{right comments}}'); ?></div><br />
                          <textarea id="RETINA_COMMENTS" class="RETINA" name="RETINA_COMMENTS"><?php echo text($RETINA_COMMENTS); ?></textarea>
                      </div>
                    </div>
                  </div>

                  <div id="RETINA_right" class="exam_section_right borderShadow text_clinical">
                    <div id="PRIORS_RETINA_left_text"
                         name="PRIORS_RETINA_left_text"
                         class="PRIORS_class PRIORS"><i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <?php display_draw_section("RETINA", $encounter, $pid); ?>
                    <div id="QP_RETINA" name="QP_RETINA" class="QP_class">
                      <input type="hidden" id="RETINA_prefix" name="RETINA_prefix" value="" />
                      <div class="qp10">
                           <span  class="eye_button  eye_button_selected" id="RETINA_prefix_off" name="RETINA_prefix_off"  onclick="$('#RETINA_prefix').val('').trigger('change');"><?php echo xlt('Off'); ?></span>
                           <span  class="eye_button" id="RETINA_defaults" name="RETINA_defaults"><?php echo xlt('Defaults'); ?></span>
                           <span  class="eye_button" id="RETINA_prefix_no" name="RETINA_prefix_no" onclick="$('#RETINA_prefix').val('no').trigger('change');"> <?php echo xlt('no'); ?> </span>
                           <span  class="eye_button" id="RETINA_prefix_trace" name="RETINA_prefix_trace"  onclick="$('#RETINA_prefix').val('trace').trigger('change');"> <?php echo xlt('tr'); ?> </span>
                           <span  class="eye_button" id="RETINA_prefix_1" name="RETINA_prefix_1"  onclick="$('#RETINA_prefix').val('+1').trigger('change');"> <?php echo xlt('+1'); ?> </span>
                           <span  class="eye_button" id="RETINA_prefix_2" name="RETINA_prefix_2"  onclick="$('#RETINA_prefix').val('+2').trigger('change');"> <?php echo xlt('+2'); ?> </span>
                           <span  class="eye_button" id="RETINA_prefix_3" name="RETINA_prefix_3"  onclick="$('#RETINA_prefix').val('+3').trigger('change');"> <?php echo xlt('+3'); ?> </span>
                            <?php echo $selector = priors_select("RETINA", $id, $id, $pid); ?>
                      </div>
                      <div name="QP_11">
                          <span  class="eye_button" id="RETINA_prefix_1mm" name="RETINA_prefix_1mm"  onclick="$('#RETINA_prefix').val('1mm').trigger('change');"> <?php echo xlt('1mm'); ?> </span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_2mm" name="RETINA_prefix_2mm"  onclick="$('#RETINA_prefix').val('2mm').trigger('change');"> <?php echo xlt('2mm'); ?> </span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_3mm" name="RETINA_prefix_3mm"  onclick="$('#RETINA_prefix').val('3mm').trigger('change');"> <?php echo xlt('3mm'); ?> </span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_4mm" name="RETINA_prefix_4mm"  onclick="$('#RETINA_prefix').val('4mm').trigger('change');"> <?php echo xlt('4mm'); ?> </span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_5mm" name="RETINA_prefix_5mm"  onclick="$('#RETINA_prefix').val('5mm').trigger('change');"> <?php echo xlt('5mm'); ?> </span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_nasal" name="RETINA_prefix_nasal"  onclick="$('#RETINA_prefix').val('nasal').trigger('change');"><?php echo xlt('nasal'); ?></span>
                          <span  class="eye_button" id="RETINA_prefix_temp" name="RETINA_prefix_temp"  onclick="$('#RETINA_prefix').val('temp').trigger('change');"><?php echo xlt('temp{{temporal}}'); ?></span>
                          <span  class="eye_button" id="RETINA_prefix_superior" name="RETINA_prefix_superior"  onclick="$('#RETINA_prefix').val('superior').trigger('change');"><?php echo xlt('sup{{superior}}'); ?></span>
                          <span  class="eye_button" id="RETINA_prefix_inferior" name="RETINA_prefix_inferior"  onclick="$('#RETINA_prefix').val('inferior').trigger('change');"><?php echo xlt('inf{{inferior}}'); ?></span>
                          <span  class="eye_button" id="RETINA_prefix_anterior" name="RETINA_prefix_anterior"  onclick="$('#RETINA_prefix').val('anterior').trigger('change');"><?php echo xlt('ant{{anterior}}'); ?></span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_mid" name="RETINA_prefix_mid"  onclick="$('#RETINA_prefix').val('mid').trigger('change');"><?php echo xlt('mid{{middle}}'); ?></span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_posterior" name="RETINA_prefix_posterior"  onclick="$('#RETINA_prefix').val('posterior').trigger('change');"><?php echo xlt('post{{posterior}}'); ?></span>  <br />
                          <span  class="eye_button" id="RETINA_prefix_deep" name="RETINA_prefix_deep"  onclick="$('#RETINA_prefix').val('deep').trigger('change');"><?php echo xlt('deep'); ?></span>
                          <br />
                          <br />
                          <span class="eye_button" id="RETINA_prefix_clear" name="RETINA_prefix_clear" title="<?php echo xla('This will clear the data from all Retina Exam fields'); ?>" onclick="$('#RETINA_prefix').val('clear').trigger('change');"><?php echo xlt('clear'); ?></span>
                      </div>
                      <div class="QP_block borderShadow text_clinical" >
                        <?php echo $QP_RETINA = display_QP("RETINA", $provider_id); ?>
                      </div>
                      <span class="closeButton fa fa-times float-right z100" id="BUTTON_TEXTD_RETINA" name="BUTTON_TEXTD_RETINA" value="1"></span>
                    </div>
                  </div>
                </div>
                <!-- end Retina -->

                <!-- start Neuro -->
                <div id="NEURO_1" class="clear_both">
                  <div id="NEURO_left" class="exam_section_left borderShadow">
                    <span class="anchor" id="NEURO_anchor"></span>
                    <div class="TEXT_class" id="NEURO_left_text" name="NEURO_left_text">
                      <span class="closeButton_2 fa fa-paint-brush" id="BUTTON_DRAW_NEURO" title="<?php echo xla('Open/Close the Neuro drawing panel'); ?>" name="BUTTON_DRAW_NEURO"></span>
                      <i class="closeButton_3 fa fa-database" title="<?php echo xla('Open/Close the Neuro Exam Quick Picks panel'); ?>" id="BUTTON_QP_NEURO" name="BUTTON_QP_NEURO"></i>
                      <i class="closeButton_4 fa fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open/Close the Shorthand Window and display Shorthand Codes"); ?>"></i>
                        <i class="closeButton fa fa-minus-circle" title="<?php echo xla('Open/Close Neuro panels'); ?>" id="BUTTON_TAB_NEURO" name="BUTTON_TAB_NEURO"></i>
                        <b><?php echo xlt('Neuro'); ?>:</b><div class="kb kb_left" title="<?php echo xla("Neuro/Phys Exam Default Values") . " " . xlt('including CVF{{Confrontational Visual Fields}} and Pupils'); ?>"><?php echo text('DNEURO'); ?></div><br />
                      <div id="NEURO_left_1" class="text_clinical">
                        <div id="NEURO_color" class="borderShadow">
                          <table>
                            <tr>
                              <td></td><td style="text-align:center;"><?php echo xlt('OD{{right eye}}'); ?></td>
                              <td style="text-align:center;"><?php echo xlt('OS{{left eye}}'); ?></td>
                            </tr>
                            <tr>
                                <td class="right"><?php echo xlt('Color'); ?>: </td>
                                <td><input type="text"  name="ODCOLOR" id="ODCOLOR" value="<?php if ($ODCOLOR) {
                                    echo  text($ODCOLOR);
                                                                                           } else {
                                                                                               echo "";
                                                                                           } ?>"/></td>
                                <td><input type="text" name="OSCOLOR" id="OSCOLOR" value="<?php if ($OSCOLOR) {
                                    echo  text($OSCOLOR);
                                                                                          } else {
                                                                                              echo "";
                                                                                          } ?>"/></td>
                                <td><!-- //Normals may be 11/11 or 15/15.  Need to make a preference here for the user.
                                    //or just take the normal they use and incorporate that ongoing?  -->
                                  &nbsp;<span title="<?php echo xlt('Insert normals'); ?> - 11/11" class="fa fa-reply" id="NEURO_COLOR" name="NEURO_COLOR" ></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="right">
                                    <span title="Variation in red color discrimination between the eyes (eg. OD=100, OS=75)"><?php echo xlt('Red Desat{{red desaturation}}'); ?>:</span>
                                </td>
                                <td>
                                    <input type="text" name="ODREDDESAT" id="ODREDDESAT" value="<?php echo attr($ODREDDESAT); ?>"/>
                                </td>
                                <td>
                                    <input type="text" name="OSREDDESAT" id="OSREDDESAT" value="<?php echo attr($OSREDDESAT); ?>"/>
                                </td>
                                <td>
                                  &nbsp; <span title="<?php echo xlt('Insert normals - 100/100'); ?>" class="fa fa-reply" id="NEURO_REDDESAT" name="NEURO_REDDESAT"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="right" style="white-space: nowrap;">
                                    <span title="<?php echo xlt('Variation in white (muscle) light brightness discrimination between the eyes (eg. OD=$1.00, OS=$0.75)'); ?>"><?php echo xlt('Coins'); ?>:</span>
                                </td>
                                <td>
                                    <input type="text" name="ODCOINS" id="ODCOINS" value="<?php echo attr($ODCOINS); ?>"/>
                                </td>
                                <td>
                                    <input type="text" name="OSCOINS" id="OSCOINS" value="<?php echo attr($OSCOINS); ?>"/>
                                </td>
                                <td>
                                   &nbsp;<span title="<?php echo xla('Insert normals'); ?> - 100/100" class="fa fa-reply" id="NEURO_COINS" name="NEURO_COINS"></span>
                                </td>
                            </tr>
                          </table>
                        </div>
                        <div class="borderShadow" id="NEURO_11">
                          <i class="fa fa-th fa-fw closeButton_2" id="Close_ACTMAIN" name="Close_ACTMAIN"></i>
                          <table class="ACT_top">
                            <tr>
                                <td >
                                    <span id="ACTTRIGGER" name="ACTTRIGGER"><?php echo xlt('Alternate Cover Test'); ?>:</span>
                                </td>
                                <td>
                                    <span id="ACTNORMAL_CHECK" name="ACTNORMAL_CHECK">
                                    <label for="ACT" class="input-helper input-helper--checkbox"><?php echo xlt('Ortho{{orthophoric}}'); ?></label>
                                    <input type="checkbox" name="ACT" id="ACT" <?php if ($ACT == 'on' or $ACT == '1') {
                                        echo "checked='checked'";
                                                                               } ?> /></span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"> <br />
                                    <div id="ACTMAIN" name="ACTMAIN" class="nodisplay ACT_TEXT">
                                      <table id="ACTTABLE">
                                            <tr>
                                                <td id="ACT_tab_SCDIST" name="ACT_tab_SCDIST" class="ACT_selected"> <?php echo xlt('scDist{{without correction distance}}'); ?> </td>
                                                <td id="ACT_tab_CCDIST" name="ACT_tab_CCDIST" class="ACT_deselected"> <?php echo xlt('ccDist{{with correction distance}}'); ?> </td>
                                                <td id="ACT_tab_SCNEAR" name="ACT_tab_SCNEAR" class="ACT_deselected"> <?php echo xlt('scNear{{without correction near}}'); ?> </td>
                                                <td id="ACT_tab_CCNEAR" name="ACT_tab_CCNEAR" class="ACT_deselected"> <?php echo xlt('ccNear{{with correction at near}}'); ?> </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"><div id="ACT_SCDIST" name="ACT_SCDIST" class="ACT_box">
                                                    <br />
                                                    <table>
                                                            <tr>
                                                                <td><?php echo xlt('R{{right}}'); ?></td>
                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;">
                                                                <textarea id="ACT1SCDIST" name="ACT1SCDIST" class="ACT"><?php echo text($ACT1SCDIST); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-top:0pt;">
                                                                <textarea id="ACT2SCDIST"  name="ACT2SCDIST"class="ACT"><?php echo text($ACT2SCDIST); ?></textarea></td>
                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;">
                                                                <textarea id="ACT3SCDIST"  name="ACT3SCDIST" class="ACT"><?php echo text($ACT3SCDIST); ?></textarea></td>
                                                                <td><?php echo xlt('L{{left}}'); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align:middle;"><i class="fa fa-reply rotate-left"></i></td>
                                                                <td style="border:1pt solid black;border-left:0pt;">
                                                                <textarea id="ACT4SCDIST" name="ACT4SCDIST" class="ACT"><?php echo text($ACT4SCDIST); ?></textarea></td>
                                                                <td style="border:1pt solid black;">
                                                                <textarea id="ACT5SCDIST"  class="neurosens2 ACT" name="ACT5SCDIST"><?php echo text($ACT5SCDIST); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-right:0pt;">
                                                                <textarea id="ACT6SCDIST" name="ACT6SCDIST" class="ACT"><?php echo text($ACT6SCDIST); ?></textarea></td>
                                                                <td><i class="fa fa-reply flip-left"></i></td>
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
                                                                <td style="text-align:center;"><?php echo xlt('R{{right}}'); ?></td>
                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                <textarea id="ACT1CCDIST" name="ACT1CCDIST" class="ACT"><?php echo text($ACT1CCDIST); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                <textarea id="ACT2CCDIST"  name="ACT2CCDIST"class="ACT"><?php echo text($ACT2CCDIST); ?></textarea></td>
                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                <textarea id="ACT3CCDIST"  name="ACT3CCDIST" class="ACT"><?php echo text($ACT3CCDIST); ?></textarea></td>
                                                                <td style="text-align:center;"><?php echo xlt('L{{left}}'); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align:middle;"><i class="fa fa-reply rotate-left"></i></td>
                                                                <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                <textarea id="ACT4CCDIST" name="ACT4CCDIST" class="ACT"><?php echo text($ACT4CCDIST); ?></textarea></td>
                                                                <td style="border:1pt solid black;text-align:center;">
                                                                <textarea id="ACT5CCDIST" name="ACT5CCDIST" class="neurosens2 ACT"><?php echo text($ACT5CCDIST); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                <textarea id="ACT6CCDIST" name="ACT6CCDIST" class="ACT"><?php echo text($ACT6CCDIST); ?></textarea></td>
                                                                <td><i class="fa fa-reply flip-left"></i></td>
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
                                                                <td style="text-align:center;"><?php echo xlt('R{{right}}'); ?></td>
                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                <textarea id="ACT1SCNEAR" name="ACT1SCNEAR" class="ACT"><?php echo text($ACT1SCNEAR); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                <textarea id="ACT2SCNEAR"  name="ACT2SCNEAR"class="ACT"><?php echo text($ACT2SCNEAR); ?></textarea></td>
                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                <textarea id="ACT3SCNEAR"  name="ACT3SCNEAR" class="ACT"><?php echo text($ACT3SCNEAR); ?></textarea></td>
                                                                <td style="text-align:center;"><?php echo xlt('L{{left}}'); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align:middle;"><i class="fa fa-reply rotate-left"></i></td>
                                                                <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                <textarea id="ACT4SCNEAR" name="ACT4SCNEAR" class="ACT"><?php echo text($ACT4SCNEAR); ?></textarea></td>
                                                                <td style="border:1pt solid black;text-align:center;">
                                                                <textarea id="ACT5SCNEAR" name="ACT5SCNEAR" class="neurosens2 ACT"><?php echo text($ACT5SCNEAR); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                <textarea id="ACT6SCNEAR" name="ACT6SCNEAR" class="ACT"><?php echo text($ACT6SCNEAR); ?></textarea></td>
                                                                <td><i class="fa fa-reply flip-left"></i></td>
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
                                                                <td style="text-align:center;"><?php echo xlt('R{{right}}'); ?></td>
                                                                <td style="border-right:1pt solid black;border-bottom:1pt solid black;text-align:right;">
                                                                <textarea id="ACT1CCNEAR" name="ACT1CCNEAR" class="ACT"><?php echo text($ACT1CCNEAR); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-top:0pt;text-align:center;">
                                                                <textarea id="ACT2CCNEAR"  name="ACT2CCNEAR"class="ACT"><?php echo text($ACT2CCNEAR); ?></textarea></td>
                                                                <td style="border-left:1pt solid black;border-bottom:1pt solid black;text-align:left;">
                                                                <textarea id="ACT3CCNEAR"  name="ACT3CCNEAR" class="ACT"><?php echo text($ACT3CCNEAR); ?></textarea></td>
                                                                <td style="text-align:center;"><?php echo xlt('L{{left}}'); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="text-align:middle;"><i class="fa fa-reply rotate-left"></i></td>
                                                                <td style="border:1pt solid black;border-left:0pt;text-align:right;">
                                                                <textarea id="ACT4CCNEAR" name="ACT4CCNEAR" class="ACT"><?php echo text($ACT4CCNEAR); ?></textarea></td>
                                                                <td style="border:1pt solid black;text-align:center;">
                                                                <textarea id="ACT5CCNEAR" name="ACT5CCNEAR" class="neurosens2 ACT"><?php echo text($ACT5CCNEAR); ?></textarea></td>
                                                                <td style="border:1pt solid black;border-right:0pt;text-align:left;">
                                                                <textarea id="ACT6CCNEAR" name="ACT6CCNEAR" class="ACT"><?php echo text($ACT6CCNEAR); ?></textarea></td>
                                                                <td><i class="fa fa-reply flip-left"></i></td>
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
                                        <br />
                                    </div>
                                </td>
                            </tr>
                          </table>
                          <div id="NPCNPA" name="NPCNPA">
                                <table style="position:relative;float:left;text-align:center;margin: 4 2;width:100%;font-size:1.0em;padding:4px;">
                                    <tr style="font-weight:bold;"><td style="width:50%;"></td><td><?php echo xlt('OD{{right eye}}'); ?></td><td><?php echo xlt('OS{{left eye}}'); ?></td></tr>
                                    <tr>
                                        <td class="right"><span title="<?php xla('Near Point of Accomodation'); ?>"><?php echo xlt('NPA{{near point of accomodation}}'); ?>:</span></td>
                                        <td><input type="text" id="ODNPA" style="width:70%;" class="neurosens2" name="ODNPA" value="<?php echo attr($ODNPA); ?>"></td>
                                        <td><input type="text" id="OSNPA" style="width:70%;" class="neurosens2" name="OSNPA" value="<?php echo attr($OSNPA); ?>"></td>
                                    </tr>
                                    <tr>
                                        <td class="right"><span title="<?php xla('Near Point of Convergence'); ?>"><?php echo xlt('NPC{{near point of convergence}}'); ?>:</span></td>
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
                                    <tr><td colspan="3">&nbsp;
                                        </td></tr>
                                    <tr><td  class="bold underline"><?php echo xlt('Amplitudes'); ?>:</td><td ><?php echo xlt('Distance'); ?></td><td><?php echo xlt('Near'); ?></td></tr>
                                    <tr>
                                        <td style="text-align:right;"><?php echo xlt('Divergence'); ?>: </td>
                                        <td><input type="text" id="DACCDIST" class="neurosens2" name="DACCDIST" value="<?php echo attr($DACCDIST); ?>"></td>
                                        <td><input type="text" id="DACCNEAR" class="neurosens2" name="DACCNEAR" value="<?php echo attr($DACCNEAR); ?>"></td></tr>
                                    <tr>
                                        <td style="text-align:right;"><?php echo xlt('Convergence'); ?>: </td>
                                        <td><input type="text" id="CACCDIST" class="neurosens2" name="CACCDIST" value="<?php echo attr($CACCDIST); ?>"></td>
                                        <td><input type="text" id="CACCNEAR" class="neurosens2" name="CACCNEAR" value="<?php echo attr($CACCNEAR); ?>"></td></tr>
                                    </tr>
                                     <tr>
                                        <td class="right">
                                            <?php echo xlt('Vertical Fusional'); ?>:
                                        </td>
                                        <td colspan="2">
                                            <input type="text" style="width:90%;" class="neurosens2" name="VERTFUSAMPS" id="VERTFUSAMPS" value="<?php echo attr($VERTFUSAMPS); ?>">
                                            <br />
                                        </td>
                                    </tr>
                                </table>
                          </div>
                        </div>
                        <div id="NEURO_MOTILITY" class="text_clinical borderShadow">
                          <table>
                            <tr>
                                <td class="left"><?php echo xlt('Motility'); ?>:</td>
                                <td class="right">
                                    <label for="MOTILITYNORMAL" class="input-helper input-helper--checkbox"><?php echo xlt('Normal'); ?></label>
                                    <input id="MOTILITYNORMAL" name="MOTILITYNORMAL" type="checkbox" <?php if ($MOTILITYNORMAL == 'on') {
                                        echo "checked='checked'";
                                                                                                     } ?>>
                                </td>
                            </tr>
                            <tr>
                              <td class="left">OD</td><td>OS</td>
                            </tr>
                            <tr>
                              <td colspan="2">
                                <input type="hidden" name="MOTILITY_RS"  id="MOTILITY_RS" value="<?php echo attr($MOTILITY_RS); ?>">
                                <input type="hidden" name="MOTILITY_RI"  id="MOTILITY_RI" value="<?php echo attr($MOTILITY_RI); ?>">
                                <input type="hidden" name="MOTILITY_RR"  id="MOTILITY_RR" value="<?php echo attr($MOTILITY_RR); ?>">
                                <input type="hidden" name="MOTILITY_RL"  id="MOTILITY_RL" value="<?php echo attr($MOTILITY_RL); ?>">
                                <input type="hidden" name="MOTILITY_LS"  id="MOTILITY_LS" value="<?php echo attr($MOTILITY_LS); ?>">
                                <input type="hidden" name="MOTILITY_LI"  id="MOTILITY_LI" value="<?php echo attr($MOTILITY_LI); ?>">
                                <input type="hidden" name="MOTILITY_LR"  id="MOTILITY_LR" value="<?php echo attr($MOTILITY_LR); ?>">
                                <input type="hidden" name="MOTILITY_LL"  id="MOTILITY_LL" value="<?php echo attr($MOTILITY_LL); ?>">

                                <input type="hidden" name="MOTILITY_RRSO" id="MOTILITY_RRSO" value="<?php echo attr($MOTILITY_RRSO); ?>">
                                <input type="hidden" name="MOTILITY_RRIO" id="MOTILITY_RRIO" value="<?php echo attr($MOTILITY_RLIO); ?>">
                                <input type="hidden" name="MOTILITY_RLSO" id="MOTILITY_RLSO" value="<?php echo attr($MOTILITY_RLSO); ?>">
                                <input type="hidden" name="MOTILITY_RLIO" id="MOTILITY_RLIO" value="<?php echo attr($MOTILITY_RLIO); ?>">

                                <input type="hidden" name="MOTILITY_LRSO" id="MOTILITY_LRSO" value="<?php echo attr($MOTILITY_LRSO); ?>">
                                <input type="hidden" name="MOTILITY_LRIO" id="MOTILITY_LRIO" value="<?php echo attr($MOTILITY_LLIO); ?>">
                                <input type="hidden" name="MOTILITY_LLSO" id="MOTILITY_LLSO" value="<?php echo attr($MOTILITY_LLSO); ?>">
                                <input type="hidden" name="MOTILITY_LLIO" id="MOTILITY_LLIO" value="<?php echo attr($MOTILITY_LLIO); ?>">

                                <div class="divTable">
                                  <div class="divRow">
                                      <div class="divCell">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RRSO_4" id="MOTILITY_RRSO_4">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRSO_4_2" id="MOTILITY_RRSO_4_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRSO_3_2" id="MOTILITY_RRSO_3_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_4_3" id="MOTILITY_RS_4_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_4_1" id="MOTILITY_RS_4_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_4" id="MOTILITY_RS_4" value="<?php echo attr($MOTILITY_RS); ?>">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_4_2" id="MOTILITY_RS_4_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_4_4" id="MOTILITY_RS_4_4">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_3_1" id="MOTILITY_RLSO_3_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_4_1" id="MOTILITY_RLSO_4_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_4" id="MOTILITY_RLSO_4">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RRSO_4_1" id="MOTILITY_RRSO_4_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRSO_3" id="MOTILITY_RRSO_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRSO_2_2" id="MOTILITY_RRSO_2_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_3_1" id="MOTILITY_RS_3_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_3" id="MOTILITY_RS_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_3_2" id="MOTILITY_RS_3_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_2_1" id="MOTILITY_RLSO_2_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_3" id="MOTILITY_RLSO_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_4_2" id="MOTILITY_RLSO_4_2">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RRSO_3_1" id="MOTILITY_RRSO_3_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRSO_2_1" id="MOTILITY_RRSO_2_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRSO_2" id="MOTILITY_RRSO_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_2_1" id="MOTILITY_RS_2_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_2" id="MOTILITY_RS_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_2_2" id="MOTILITY_RS_2_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_2" id="MOTILITY_RLSO_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_2_2" id="MOTILITY_RLSO_2_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_232" id="MOTILITY_RLSO_3_2">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRSO_1" id="MOTILITY_RRSO_1">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_1_1" id="MOTILITY_RS_1_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_1" id="MOTILITY_RS_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_1_2" id="MOTILITY_RS_1_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLSO_1" id="MOTILITY_RLSO_1">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RR_4_3" id="MOTILITY_RR_4_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RR_4_1" id="MOTILITY_RR_4_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RR_3_1" id="MOTILITY_RR_3_1">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_0_1" id="MOTILITY_RS_0_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_0" id="MOTILITY_RS_0">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RS_0_2" id="MOTILITY_RS_0_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RL_3_1" id="MOTILITY_RL_3_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RL_4_1" id="MOTILITY_RL_4_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RL_4_3" id="MOTILITY_RL_4_3">&nbsp;</div>
                                  </div>
                                  <div class="divMiddleRow">
                                    <div class="divCell" name="MOTILITY_RR_4_4" id="MOTILITY_RR_4_4">&nbsp;</div>
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
                                    <div class="divCell" name="MOTILITY_RL_4_4" id="MOTILITY_RL_4_4">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RR_4_5" id="MOTILITY_RR_4_5">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RR_4_2" id="MOTILITY_RR_4_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RR_3_2" id="MOTILITY_RR_3_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_0_1" id="MOTILITY_RI_0_1">&nbsp;</div>
                                    <div class="divCell" id="MOTILITY_RI_0" name="MOTILITY_RI_0">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_0_2" id="MOTILITY_RI_0_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RL_3_2" id="MOTILITY_RL_3_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RL_4_2" id="MOTILITY_RL_4_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RL_4_5" id="MOTILITY_RL_4_5">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRIO_1" id="MOTILITY_RRIO_1">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_1_1" id="MOTILITY_RI_1_1">&nbsp;</div>
                                    <div class="divCell" id="MOTILITY_RI_1" name="MOTILITY_RI_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_1_2" id="MOTILITY_RI_1_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_1" id="MOTILITY_RLIO_1">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RRIO_3_1" id="MOTILITY_RRIO_3_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRIO_2_1" id="MOTILITY_RRIO_2_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRIO_2"   id="MOTILITY_RRIO_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_2_1" id="MOTILITY_RI_2_1">&nbsp;</div>
                                    <div class="divCell" id="MOTILITY_RI_2" name="MOTILITY_RI_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_2_2" id="MOTILITY_RI_2_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_2" id="MOTILITY_RLIO_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_2_1" id="MOTILITY_RLIO_2_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_3_1" id="MOTILITY_RLIO_3_1">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RRIO_4_1" id="MOTILITY_RRIO_4_1">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRIO_3" id="MOTILITY_RRIO_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRIO_2_2" id="MOTILITY_RRIO_2_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_3_1" id="MOTILITY_RI_3_1">&nbsp;</div>
                                    <div class="divCell" id="MOTILITY_RI_3" name="MOTILITY_RI_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_3_2" id="MOTILITY_RI_3_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_2_2" id="MOTILITY_RLIO_2_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLI0_3" id="MOTILITY_RLIO_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_4_1" id="MOTILITY_RLIO_4_1">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell" name="MOTILITY_RRIO_4" id="MOTILITY_RRIO_4" value="<?php echo attr($MOTILITY_RRIO); ?>">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRIO_4_2" id="MOTILITY_RRIO_4_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RRIO_3_2" id="MOTILITY_RRIO_3_2">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_4_3" id="MOTILITY_RI_4_3">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_4_1" id="MOTILITY_RI_4_1">&nbsp;</div>
                                    <div class="divCell" id="MOTILITY_RI_4" name="MOTILITY_RI_4" value="<?php echo attr($MOTILITY_RI); ?>">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_4_2" id="MOTILITY_RI_4_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RI_4_4" id="MOTILITY_RI_4_4">&nbsp;</div>
                                    <div class="divCell">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_3_2" id="MOTILITY_RLIO_3_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_4_2" id="MOTILITY_RLIO_4_2">&nbsp;</div>
                                    <div class="divCell" name="MOTILITY_RLIO_4" id="MOTILITY_RLIO_4" value="<?php echo attr($MOTILITY_RLIO); ?>">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                    <div class="divCell">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell">&nbsp;</div>
                                  </div>
                                </div>
                                <div class="divTable">
                                  <div class="divRow">
                                      <div class="divCell">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LRSO_4" id="MOTILITY_LRSO_4" value="<?php echo attr($MOTILITY_LRSO); ?>">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRSO_4_2" id="MOTILITY_LRSO_4_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRSO_3_3" id="MOTILITY_LRSO_3_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_4_3" id="MOTILITY_LS_4_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_4_1" id="MOTILITY_LS_4_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_4" id="MOTILITY_LS_4" value="<?php echo attr($MOTILITY_LS); ?>">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_4_2" id="MOTILITY_LS_4_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_4_4" id="MOTILITY_LS_4_4">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_3_1" id="MOTILITY_LLSO_3_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_4_1" id="MOTILITY_LLSO_4_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_4" id="MOTILITY_LLSO_4" value="<?php echo attr($MOTILITY_LLSO); ?>">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LRSO_4_1" id="MOTILITY_LRSO_4_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRSO_3" id="MOTILITY_LRSO_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRSO_2_2" id="MOTILITY_LRSO_2_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_3_1" id="MOTILITY_LS_3_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_3" id="MOTILITY_LS_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_3_2" id="MOTILITY_LS_3_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_2_1" id="MOTILITY_LLSO_2_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_3" id="MOTILITY_LLSO_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_4_2" id="MOTILITY_LLSO_4_2">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LRSO_3_1" id="MOTILITY_LRSO_3_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRSO_2_1" id="MOTILITY_LRSO_2_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRSO_2" id="MOTILITY_LRSO_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_2_1" id="MOTILITY_LS_2_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_2" id="MOTILITY_LS_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_2_2" id="MOTILITY_LS_2_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_2" id="MOTILITY_LLSO_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_2_2" id="MOTILITY_LLSO_2_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_3_2" id="MOTILITY_LLSO_3_2">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRSO_1" id="MOTILITY_LRSO_1">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_1_1" id="MOTILITY_LS_1_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_1" id="MOTILITY_LS_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_1_2" id="MOTILITY_LS_1_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLSO_1" id="MOTILITY_LLSO_1">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LR_4_3" id="MOTILITY_LR_4_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LR_4_1" id="MOTILITY_LR_4_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LR_3_1" id="MOTILITY_LR_3_1">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_0_1" id="MOTILITY_LS_0_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_0" id="MOTILITY_LS_0">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LS_0_1" id="MOTILITY_LS_0_1">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LL_3_1" id="MOTILITY_LL_3_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LL_4_1" id="MOTILITY_LL_4_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LL_4_3" id="MOTILITY_LL_4_3">&nbsp;</div>
                                  </div>
                                  <div class="divMiddleRow">
                                      <div class="divCell" name="MOTILITY_LR_4_4" id="MOTILITY_LR_4_4">&nbsp;</div>
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
                                      <div class="divCell" name="MOTILITY_LL_4_4" id="MOTILITY_LL_4_4">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LR_4_5" id="MOTILITY_LR_4_5">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LR_4_2" id="MOTILITY_LR_4_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LR_3_3" id="MOTILITY_LR_3_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_0_1" id="MOTILITY_LI_0_1">&nbsp;</div>
                                      <div class="divCell" id="MOTILITY_LI_0" name="MOTILITY_LI_0">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_0_2" id="MOTILITY_LI_0_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LL_3_2" id="MOTILITY_LL_3_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LL_4_2" id="MOTILITY_LL_4_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LL_5_2" id="MOTILITY_LL_5_2">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRIO_1" id="MOTILITY_LRIO_1">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_1_1" id="MOTILITY_LI_1_1">&nbsp;</div>
                                      <div class="divCell" id="MOTILITY_LI_1" name="MOTILITY_LI_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_1_2" id="MOTILITY_LI_1_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_1" id="MOTILITY_LLIO_1">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LRIO_3_1" id="MOTILITY_LRIO_3_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRIO_2_1" id="MOTILITY_LRIO_2_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRIO_2"   id="MOTILITY_LRIO_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_2_1" id="MOTILITY_LI_2_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_2"   id="MOTILITY_LI_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_2_2" id="MOTILITY_LI_2_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_2"   id="MOTILITY_LLIO_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_2_1" id="MOTILITY_LLIO_2_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_3_1" id="MOTILITY_LLIO_3_1">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LRIO_4_1" id="MOTILITY_LRIO_4_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRIO_3"   id="MOTILITY_LRIO_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRIO_2_2" id="MOTILITY_LRIO_2_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_3_1" id="MOTILITY_LI_3_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_3"   id="MOTILITY_LI_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_3_2" id="MOTILITY_LI_3_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_2_2" id="MOTILITY_LLIO_2_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_3"   id="MOTILITY_LLIO_3">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_4_1" id="MOTILITY_LLIO_4_1">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell" name="MOTILITY_LRIO_4"   id="MOTILITY_LRIO_4" value="<?php echo attr($MOTILITY_LRIO); ?>">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRIO_4_2" id="MOTILITY_LRIO_4_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LRIO_3_2" id="MOTILITY_LRIO_3_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_4_1" id="MOTILITY_LI_4_1">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_4"   id="MOTILITY_LI_4" value="<?php echo attr($MOTILITY_LI); ?>">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LI_4_2" id="MOTILITY_LI_4_2">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_3_2" id="MOTILITY_LLIO_3_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_4_2" id="MOTILITY_LLIO_4_2">&nbsp;</div>
                                      <div class="divCell" name="MOTILITY_LLIO_4"   id="MOTILITY_LLIO_4" value="<?php echo attr($MOTILITY_LLIO); ?>">&nbsp;</div>
                                  </div>
                                  <div class="divRow">
                                      <div class="divCell">&nbsp;</div>
                                  </div><div class="divRow">
                                      <div class="divCell">&nbsp;</div>
                                  </div>
                                </div>
                              </td>
                            </tr>
                          </table>
                        </div>
                      </div>
                      <br />
                      <div class="QP_lengthen" id="NEURO_COMMENTS_DIV">
                          <b><?php echo xlt('Comments'); ?>:</b><div class="kb kb_left"><?php echo xlt('NCOM{{Neuro comments}}'); ?></div><br />
                          <textarea id="NEURO_COMMENTS" name="NEURO_COMMENTS"><?php echo text($NEURO_COMMENTS); ?></textarea>
                      </div>
                    </div>
                  </div>
                  <div id="NEURO_right" class="exam_section_right borderShadow text_clinical">
                    <div id="PRIORS_NEURO_left_text" name="PRIORS_NEURO_left_text" class="PRIORS_class PRIORS"><i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <?php display_draw_section("NEURO", $encounter, $pid); ?>
                    <div id="QP_NEURO" name="QP_NEURO" class="QP_class">
                      <input type="hidden" id="NEURO_ACT_zone" name="NEURO_ACT_zone" value="<?php echo $ACT_SHOW ?? ''; ?>">
                      <input type="hidden" id="NEURO_ACT_strab" name="NEURO_ACT_strab" value="">
                      <input type="hidden" id="NEURO_field" name="NEURO_field" value="5">
                      <input type="hidden" id="NEURO_value" name="NEURO_value" value="ortho">
                      <input type="hidden" id="NEURO_side" name="NEURO_side" value="">

                      <div id="NEURO_P_1">
                        <?php echo $selector = priors_select("NEURO", $id, $id, $pid); ?>
                      </div>
                      <div id="NEURO_P_2"><br />
                        <div class="borderShadow" class="NEURO_P_21"><span class="underline"><?php echo xlt('Laterality'); ?></span><br />
                          <span class="eye_button" id="NEURO_side_R" name="NEURO_side" style="padding-left:0.06in;padding-right:0.06in;"  onclick="$('#NEURO_side').val('R').trigger('change');"><?php echo xlt('Right'); ?></span>
                          <span class="eye_button" id="NEURO_side_L" name="NEURO_side" style="padding-left:0.06in;padding-right:0.06in;"  onclick="$('#NEURO_side').val('L').trigger('change');"><?php echo xlt('Left'); ?></span>
                          <span class="eye_button eye_button_selected" id="NEURO_side_None" name="NEURO_side"  onclick="$('#NEURO_side').val('').trigger('change');"><?php echo xlt('None{{Side}}'); ?></span> <br />
                        </div>
                        <div class="borderShadow" class="NEURO_P_21"><span class="underline"><?php echo xlt('Deviation'); ?></span><br />
                          <span class="eye_button" id="NEURO_ACT_strab_E" name="NEURO_ACT_strab" title="<?php echo xla('Esophoria'); ?>" onclick="$('#NEURO_ACT_strab').val('E').trigger('change');"><?php echo xlt('E{{esophoria}}'); ?></span>
                          <span class="eye_button" id="NEURO_ACT_strab_ET_int" name="NEURO_ACT_strab" title="<?php echo xla('Intermittent Esotropia'); ?>" onclick="$('#NEURO_ACT_strab').val('E\(T\)').trigger('change');"><?php echo xlt('E(T){{intermittent esotropia}}'); ?></span>
                          <span class="eye_button" id="NEURO_ACT_strab_ET" name="NEURO_ACT_strab" title="<?php echo xla('Esotropia'); ?>" onclick="$('#NEURO_ACT_strab').val('ET').trigger('change');"><?php echo xlt('ET{{esotropia}}'); ?></span>
                         <br />
                          <span class="eye_button" id="NEURO_ACT_strab_X" name="NEURO_ACT_strab" title="<?php echo xla('Exophoria'); ?>" onclick="$('#NEURO_ACT_strab').val('X').trigger('change');"><?php echo xlt('X{{exophoria}}'); ?></span>
                          <span class="eye_button" id="NEURO_ACT_strab_XT_int" name="NEURO_ACT_strab" title="<?php echo xla('Intermittent Exotropia'); ?>" onclick="$('#NEURO_ACT_strab').val('X\(T\)').trigger('change');"><?php echo xlt('X(T){{intermittent exophoria}}'); ?></span>
                          <span class="eye_button" id="NEURO_ACT_strab_XT" name="NEURO_ACT_strab" title="<?php echo xla('Exotropia'); ?>" onclick="$('#NEURO_ACT_strab').val('XT').trigger('change');"><?php echo xlt('XT{{exotropia}}'); ?></span>

                          <br />
                          <span class="eye_button" id="NEURO_ACT_strab_H" name="NEURO_ACT_strab" title="<?php echo xla('Hyperphoria'); ?>" onclick="$('#NEURO_ACT_strab').val('HT').trigger('change');"><?php echo xlt('HT{{hyperphoria}}'); ?></span>
                          <span class="eye_button" id="NEURO_ACT_strab_HT_int" name="NEURO_ACT_strab" title="<?php echo xla('intermittent hyperphoria'); ?>" onclick="$('#NEURO_ACT_strab').val('H\(T\)').trigger('change');"><?php echo xlt('H(T){{intermittent hypertropia}}'); ?></span>
                          <span class="eye_button" id="NEURO_ACT_strab_hypoT" name="NEURO_ACT_strab" title="<?php echo xla('Hypotropia'); ?>" onclick="$('#NEURO_ACT_strab').val('hypoT').trigger('change');"><?php echo xlt('hypoT{{hypotropia}}'); ?></span>
                          <span class="eye_button" id="NEURO_ACT_strab_hypoT_int" name="NEURO_ACT_strab" title="<?php echo xla('Intermittent hypotropia'); ?>" onclick="$('#NEURO_ACT_strab').val('hypo\(T\)').trigger('change');"><?php echo xlt('hypo(T){{intermittent hypotropia}}'); ?></span>
                          <br />
                        </div>  <br /><br />
                       <div>
                          <button id="NEURO_RECORD" name="NEURO_RECORD"> <?php echo xlt('RECORD'); ?> </button>
                        </div>
                      </div>
                      <div id="NEURO_P_3">
                        &nbsp;
                        <div class="borderShadow ACT_boxed"><span class="underline"><?php echo xlt('Rx/Distance'); ?></span><br />
                          <span class="eye_button <?php if (($ACT_SHOW ?? null) == 'SCDIST') {
                                echo "eye_button_selected";
                                                  } ?>" id="NEURO_ACT_zone_SCDIST" name="NEURO_ACT_zone" onclick="$('#NEURO_ACT_zone').val('SCDIST').trigger('change');"> <?php echo xlt('scDist{{without correction distance}}'); ?> </span>
                          <span class="eye_button <?php if (($ACT_SHOW ?? null) == 'CCDIST') {
                                echo "eye_button_selected";
                                                  } ?>" id="NEURO_ACT_zone_CCDIST" name="NEURO_ACT_zone" onclick="$('#NEURO_ACT_zone').val('CCDIST').trigger('change');"> <?php echo xlt('ccDist{{with correction distance}}'); ?> </span>
                          <span class="eye_button <?php if (($ACT_SHOW ?? null) == 'SCNEAR') {
                                echo "eye_button_selected";
                                                  } ?>" id="NEURO_ACT_zone_SCNEAR" name="NEURO_ACT_zone" onclick="$('#NEURO_ACT_zone').val('SCNEAR').trigger('change');"> <?php echo xlt('scNear{{without correction near}}'); ?> </span>
                          <span class="eye_button <?php if (($ACT_SHOW ?? null) == 'CCNEAR') {
                                echo "eye_button_selected";
                                                  } ?>" id="NEURO_ACT_zone_CCNEAR" name="NEURO_ACT_zone" onclick="$('#NEURO_ACT_zone').val('CCNEAR').trigger('change');"> <?php echo xlt('ccNear{{with correction at near}}'); ?> </span>
                        </div>
                        <div class="borderShadow ACT_boxed"><span class="underline"><?php echo xlt('Position of Gaze'); ?></span><br />
                          <span class="eye_button_blank"> <?php echo xlt('R{{right}}'); ?> </span>
                          <span class="eye_button" id="NEURO_field_1" name="NEURO_field"  onclick="$('#NEURO_field').val('1').trigger('change');"> 1 </span>
                          <span class="eye_button" id="NEURO_field_2" name="NEURO_field"  onclick="$('#NEURO_field').val('2').trigger('change');"> 2 </span>
                          <span class="eye_button" id="NEURO_field_3" name="NEURO_field"  onclick="$('#NEURO_field').val('3').trigger('change');"> 3 </span>
                          <span class="eye_button_blank"> <?php echo xlt('L{{left}}'); ?> </span>

                          <span class="eye_button_blank"><i class="fa fa-1 fa-reply rotate-left"></i></span>
                          <span class="eye_button" id="NEURO_field_4" name="NEURO_field"  onclick="$('#NEURO_field').val('4').trigger('change');"> 4 </span>
                          <span class="eye_button eye_button_selected" id="NEURO_field_5" name="NEURO_field" onclick="$('#NEURO_field').val('5').trigger('change');"> 5 </span>
                          <span class="eye_button" id="NEURO_field_6" name="NEURO_field"  onclick="$('#NEURO_field').val('6').trigger('change');">6</span>
                          <span class="eye_button_blank"><i class="fa fa-1 fa-reply flip-left"></i></span>

                          <span class="eye_button" id="NEURO_field_10" name="NEURO_field"  onclick="$('#NEURO_field').val('10').trigger('change');">10</span>
                          <span class="eye_button" id="NEURO_field_7" name="NEURO_field"  onclick="$('#NEURO_field').val('7').trigger('change');">7</span>
                          <span class="eye_button" id="NEURO_field_8" name="NEURO_field"  onclick="$('#NEURO_field').val('8').trigger('change');">8</span>
                          <span class="eye_button" id="NEURO_field_9" name="NEURO_field"  onclick="$('#NEURO_field').val('9').trigger('change');">9</span>
                          <span class="eye_button" id="NEURO_field_11" name="NEURO_field"  onclick="$('#NEURO_field').val('11').trigger('change');">11</span>
                        </div>

                        <div class="borderShadow ACT_boxed"><span class="underline"><?php echo xlt('Prism Diopters'); ?></span><br />
                          <span class="eye_button" id="NEURO_value_ortho" name="NEURO_value"  onclick="$('#NEURO_value').val('ortho').trigger('change');"><?php echo xlt('Ortho{{orthophoric}}'); ?></span>
                          <span class="eye_button" id="NEURO_value_1" name="NEURO_value"  onclick="$('#NEURO_value').val('1').trigger('change');">1</span>
                          <span class="eye_button" id="NEURO_value_2" name="NEURO_value"  onclick="$('#NEURO_value').val('2').trigger('change');">2</span>
                          <span class="eye_button" id="NEURO_value_3" name="NEURO_value"  onclick="$('#NEURO_value').val('3').trigger('change');">3</span>
                          <span class="eye_button" id="NEURO_value_4" name="NEURO_value"  onclick="$('#NEURO_value').val('4').trigger('change');">4</span>
                          <span class="eye_button" id="NEURO_value_5" name="NEURO_value"  onclick="$('#NEURO_value').val('5').trigger('change');">5</span>
                          <span class="eye_button" id="NEURO_value_6" name="NEURO_value"  onclick="$('#NEURO_value').val('6').trigger('change');">6</span>
                          <span class="eye_button" id="NEURO_value_8" name="NEURO_value"  onclick="$('#NEURO_value').val('8').trigger('change');">8</span>
                          <span class="eye_button" id="NEURO_value_10" name="NEURO_value"  onclick="$('#NEURO_value').val('10').trigger('change');">10</span>
                          <span class="eye_button" id="NEURO_value_12" name="NEURO_value"  onclick="$('#NEURO_value').val('12').trigger('change');">12</span>
                          <span class="eye_button" id="NEURO_value_14" name="NEURO_value"  onclick="$('#NEURO_value').val('14').trigger('change');">14</span>
                          <span class="eye_button" id="NEURO_value_16" name="NEURO_value"  onclick="$('#NEURO_value').val('16').trigger('change');">16</span>
                          <span class="eye_button" id="NEURO_value_18" name="NEURO_value"  onclick="$('#NEURO_value').val('18').trigger('change');">18</span>
                          <span class="eye_button" id="NEURO_value_20" name="NEURO_value"  onclick="$('#NEURO_value').val('20').trigger('change');">20</span>
                          <span class="eye_button" id="NEURO_value_25" name="NEURO_value"  onclick="$('#NEURO_value').val('25').trigger('change');">25</span>
                          <span class="eye_button" id="NEURO_value_30" name="NEURO_value"  onclick="$('#NEURO_value').val('30').trigger('change');">30</span>
                          <span class="eye_button" id="NEURO_value_35" name="NEURO_value"  onclick="$('#NEURO_value').val('35').trigger('change');">35</span>
                          <span class="eye_button" id="NEURO_value_40" name="NEURO_value"  onclick="$('#NEURO_value').val('40').trigger('change');">40</span>
                        </div>
                      </div>
                      <span class="closeButton fa fa-times float-right z100" id="BUTTON_TEXTD_NEURO" name="BUTTON_TEXTD_NEURO" value="1"></span>
                    </div>
                  </div>
                </div>
                <!-- end Neuro -->
<br />
                <!-- start IMP/PLAN -->
                <div class="size50 clear_both" id="IMPPLAN_1">
                  <div id="IMPPLAN_left" name="IMPPLAN_left" class="clear_both exam_section_left borderShadow">
                      <span class="anchor" id="IMPPLAN_anchor"></span>
                      <a class="closeButton_5 far fa-file-pdf"
                         title="<?php echo xla('Once completed, view and store this encounter as a PDF file'); ?>"
                         onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/report/custom_report.php?printable=1&pdf=1&<?php echo attr_url($form_folder) . "_" . attr_url($form_id) . "=" . attr_url($encounter); ?>&', 'Eye Report');"
                         href="JavaScript:void(0);">

                      </a>
                      <span class="closeButton_2 fa fa-paint-brush" id="BUTTON_DRAW_IMPPLAN" title="<?php echo xla('Open/Close the Imp/Plan drawing panel'); ?>"  name="BUTTON_DRAW_IMPPLAN"></span>
                      <i class="closeButton_3 fa fa-database" title="<?php echo xla('Show the Impression/Plan Builder panel'); ?>" id="BUTTON_QP_IMPPLAN" name="BUTTON_QP_IMPPLAN"></i>
                      <i class="closeButton_4 fa fa-user-md" name="Shorthand_kb" title="<?php echo xla("Open/Close the Shorthand Window and display Shorthand Codes"); ?>"></i>
                      <i class="closeButton fa fa-minus-circle" title="<?php echo xla('Open/Close Imp/Plan panels'); ?>" id="BUTTON_TAB_IMPPLAN" name="BUTTON_TAB_IMPPLAN"></i>
                      <div id="IMPPLAN_left_text" name="IMPPLAN_left_text">
                          <b><?php echo xlt('Impression/Plan'); ?>:</b><div class="kb kb_left"><?php echo xlt('IMP{{impression}}'); ?></div>
                          <div id="IMPPLAN_blank" name="IMPPLAN_blank" class="HPI_text">
                              <br />
                              <table class="IMPPLAN">
                                  <tr>
                                      <td class="right bold" style="width:75px;padding-right:10px;vertical-align:top;"><?php echo  xlt('New Dx{{new diagnosis}}'); ?>: </td>
                                      <td><textarea name="IMP" id="IMP"><?php echo text($IMP); ?></textarea></td>
                                  </tr>
                              </table>
                          </div>
                          <div id="IMPPLAN_text" name="IMPPLAN_text">
                                <?php
                                  echo '<br /><br /><span class="bold">';
                                  echo xlt('How-to Build the Impression/Plan') . ':';
                                  echo '</span><ol>';
                                  echo '<li>' . xlt('Manually type into the New DX box above.') . '<br />' . xlt('The *Tab* key creates each entry.') . '</li>';
                                  echo '<span class"bold" style="margin-left:-5px;">' . xlt('or utilize the Impression/Plan Builder') . '</span>';
                                  echo '<li>' . xlt('Drag a DX over by its handle') . ':&nbsp;<i class="fas fa-arrows-alt"></i></li>';
                                  echo '<li>' . xlt('Double click on a DX\'s handle') . ':&nbsp;<i class="fas fa-arrows-alt"></i></li>';
                                  echo '<li>' . xlt('Multi-select desired DX(s) and click the') . ' <i class="fa fa-reply"></i> ' . xlt('icon') . '</li>';
                                  echo '</ol>';
                                ?>
                          </div>
                          <div id="IMPPLAN_zone" name="IMPPLAN_zone" class="nodisplay">
                          </div>

                          <input type="hidden" name="IMPPLAN_count" id="IMPPLAN_count" value="<?php echo $IMPPLAN_count ?? ''; ?>">
                      </div>
                  </div>

                    <?php
                      /* There are at least 4 ways to build IMP/PLAN
                       *  1. Freehand - textarea
                       *  2. Copy Forward (prior_select)
                       *  3. Build automatically through workflows. (build_PMSFH)
                       *  4. Draw it in (display_draw_section)
                       */
                    ?>
                  <div class="size50">
                  <div id="IMPPLAN_right" class="exam_section_right borderShadow text_clinical clear_both ">
                        <?php display_draw_section("IMPPLAN", $encounter, $pid); ?>

                      <div id="PRIORS_IMPPLAN_left_text" name="PRIORS_IMPPLAN_left_text" class="PRIORS_class PRIORS"><i class="fa fa-spinner fa-spin"></i>
                      </div>
                      <div id="QP_IMPPLAN" name="QP_IMPPLAN" class="QP_class2">
                          <span id="iPLAN_BUILD" name="iPLAN_BUILD" class="bold"><?php echo xlt('Impression/Plan'); ?></span>
                          <div id="IP_P_1">
                                <?php echo $selector = priors_select("IMPPLAN", $id, $id, $pid); ?>
                          </div>
                          <span class="closeButton fa fa-times float-right z100" id="BUTTON_TEXTD_IMPPLAN" name="BUTTON_TEXTD_IMPPLAN" value="1"></span>
                          <br />
                            <?php
                              /*
                               *  Let's discuss 3. Build automatically through workflows - The Impression/Plan Builder
                               *  Since POH is our area of concern, use $PMSFH[0]['POH'] & $PMSFH[0]['POS'] first then
                               *  $PMSFH[0]['medical_problem'] then
                               *  system to extrapolate DXs from user-entered clinical findings (PE)
                               *  to build the IMP/PLAN options list
                               *    a. Diagnoses are sortable via dragging, to build an order.
                               *    b. Diagnoses are selectable and deselectable
                               *        then click the BUILD icon to append the selected DXs to the bottom of the IMP/Plan list.
                               *    c. Drag a DX across onto the IMP.textarea appends this data to the current IMP.textarea data
                               *    d. Drag a DX across onto the IMP/Plan area appends this DX to the bottom of the IMP/Plan list
                               *    e. DoubleClick a DX appends this DX to the bottom of the IMP/Plan list
                               */

                            if (!$PMSFH) {
                                $PMSFH = build_PMSFH($pid);
                            }

                              $total_DX = '0';
                            if ((($PMSFH[0]['POH'][0] ?? null) > '') && ($PMSFH[0]['PMH'][0] > '')) {
                                $total_DX = '1';
                            }

                            ?>


                          <br />
                          <dl class="building_blocks" id="building_blocks" name="building_blocks">
                              <dt class="borderShadow"><i title="<?php echo xla('Drag the arrow for each diagnosis to sort the list.');
                                      echo "\n";
                                      echo xla('Select the diagnoses to include in the Impression/Plan.') . "\n";
                                      echo xla('Press this icon to build your Impression/Plan.'); ?>" class="fa fa-reply" id="make_new_IMP" name="make_new_IMP"></i>
                                  <span id="IMP_start" name="IMP_start"><?php echo xlt('Impression/Plan Builder'); ?></span>
                                  <div id="IMP_start2">
                                      <input type="checkbox" id="inc_PE" name="inc_PE" checked="checked"><label for='inc_PE' class='input-helper input-helper--checkbox'><?php echo xlt('Exam{{Physical Exam}}'); ?></label>&nbsp;
                                      <input type="checkbox" id="inc_POH" name="inc_POH" checked="checked"><label for='inc_POH' class='input-helper input-helper--checkbox'><?php echo xlt('POH{{Past Ocular History}}'); ?></label>&nbsp;
                                      <input type="checkbox" id="inc_PMH" name="inc_PMH"><label for='inc_PMH' class='input-helper input-helper--checkbox'><?php echo xlt('PMH{{Past Medical History}}') ?></label>&nbsp;
                                  </div>
                              </dt>
                              <dd id="IMP_start_acc" name="IMP_start_acc">
                                  <ol id="build_DX_list" name="build_DX_list">
                                        <?php
                                          $i = 0;
                                        if ($total_DX == '1') {
                                            foreach ($PMSFH[0]['POH'] as $k => $v) {
                                                $insert_code = '';
                                                if ($v['diagnosis'] > '') {
                                                    $insert_code = "<code class='float-right diagnosis'>" . $v['diagnosis'] . "</code>";
                                                }

                                                $k = xla($k);
                                                $v['title'] = xlt($v['title']);
                                                $insert_code = text($insert_code);
                                                echo "<li class='ui-widget-content'> <span id='DX_POH_" . $k . "' name='DX_POH_" . $k . "'>" . $v['title'] . "</span> " . $insert_code . "</li>";
                                            }

                                            foreach ($PMSFH[0]['POS'] as $k => $v) {
                                                $insert_code = '';
                                                if ($v['diagnosis'] > '') {
                                                    $insert_code = "<code class='float-right diagnosis'>" . $v['diagnosis'] . "</code>";
                                                }

                                                $k = xla($k);
                                                $v['title'] = xlt($v['title']);
                                                $insert_code = text($insert_code);
                                                echo "<li class='ui-widget-content'> <span id='DX_POS_" . $k . "' name='DX_POS_" . $k . "'>" . $v['title'] . "</span> " . $insert_code . "</li>";
                                            }

                                            if (!empty($PMSFH[0]['medical_problem'])) {
                                                foreach ($PMSFH[0]['medical_problem'] as $k => $v) {
                                                    $insert_code = '';
                                                    if ($v['diagnosis'] > '') {
                                                        $insert_code = "<code class='float-right diagnosis'>" . $v['diagnosis'] . "</code>";
                                                    }

                                                    $k = xla($k);
                                                    $v['title'] = xlt($v['title']);
                                                    $insert_code = text($insert_code);
                                                    echo "<li class='ui-widget-content'> <span id='DX_PMH_" . $k . "' name='DX_PMH_" . $k . "'>" . $v['title'] . "</span> " . $insert_code . "</li>";
                                                }
                                            }
                                        } else {
                                            echo "<br /><span class='bold'>";
                                            echo xlt("The Past Ocular History (POH) and Past Medical History (PMH) are negative.");
                                            echo xlt('and') . ' ' . xlt('no diagnosis was auto-generated from the clinical findings.');
                                            echo "</span><br /><br />";
                                            echo xlt("Update the chart to activate the Builder.") . "<br />";
                                        }
                                        ?>
                                  </ol>
                              </dd>

                                <?php
                                  /*
                                   *  The goal here is to auto-code the encounter and link it directly to the billing module.
                                   *  Select Visit Type from dropdown (CPT4) built from practice's fee_sheet_options table.
                                   *  Active coding system = $GLOBALS['default_search_code_type'];
                                   *  We present the active coding system codes found in the Imp/Plan.
                                   *  Perhaps a minor procedure/test was performed?
                                   *  Select options drawn from Eye_todo_done_".$provider_id list with a CODE
                                   *  TODO: Finally we have the "Prior Visit" functionality of the form.
                                   *  We should be able to look past codes and perhaps carry this forward?
                                   */
                                ?>
                              <script>
                                  var default_search_type = '<?php echo text($GLOBALS['default_search_code_type']); ?>';
                              </script>

                              <dt class="borderShadow"><span><?php echo xlt('Coding Engine'); ?></span></dt>
                              <dd>
                                  <div style="padding:5px 10px 5px 10px;">
                                      <table style="width:100%;">
                                          <tr>
                                              <td colspan="3"><b><u><?php echo xlt('Diagnostic') . " " . xlt('Codes'); ?>:</u></b>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td colspan="3" style="padding-top:5px;padding-left:15px;"><span id="Coding_DX_Codes"><br /></span>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td style="padding-top:10px;width:60%;"><b><u><?php echo xlt('Visit');
                                                              echo " " . xlt('Codes'); ?>:</u></b>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td class="indent20">
                                <span class="CODE_LOW indent20" title="<?php echo xla('Documentation for a Detailed HPI requires') . ":\n " . xla('> 3 HPI elements') . "\n " .
                                    xla('OR{{as in AND/OR, ie. not an abbreviation}}') . "\n " .
                                    xla('the status of three chronic/inactive problems'); ?>">
                                  <i class="fa fa-check"></i> <?php
                                        echo xlt('Limited HPI');
                                    ?> </span><span class="CODE_HIGH nodisplay"><i class="fa fa-check"></i> <?php
                                                          echo xlt('Detailed HPI'); ?></span><span class="EXAM_LOW">, <?php echo xlt('Limited Exam'); ?></span>
                                                  <span class="DIL_RISKS nodisplay"><i class="fa fa-check"></i> <?php echo xlt('Detailed exam'); ?></span>
                                              </td>
                                              <td class="text-center">
                                                  <span style="text-decoration:underline;"><?php echo xlt('Modifiers'); ?></span>
                                              </td>
                                              <td class="text-center">
                                                  <span id="Coding_Visit_Codes" style="text-decoration:underline;"><?php echo xlt('Justify'); ?></span>
                                                  <span style="font-size:1.2em;">&#x21b4;</span>
                                              </td>
                                          </tr>
                                          <tr class="ui-widget-content">
                                              <td>
                                                  <div >
                                                      <select id="visit_codes">
                                                            <?php
                                                              $i = 0;
                                                              $last_category = '';

                                                              // Create drop-lists based on the fee_sheet_options table.
                                                              $res = sqlStatement("SELECT * FROM fee_sheet_options " .
                                                                  "ORDER BY fs_category, fs_option");
                                                              while ($row = sqlFetchArray($res)) {
                                                                  $fs_category = $row['fs_category'];
                                                                  $fs_option   = $row['fs_option'];
                                                                  $fs_codes    = $row['fs_codes'];
                                                                  list($code_type_here,$code) = explode("|", $fs_codes);
                                                                  if ($fs_category !== $last_category) {
                                                                      $last_category = $fs_category;
                                                                      echo "    <option value=''> " . text(substr($fs_category, 1)) . "</option>\n";
                                                                  }
                                                                    $code_text = (strlen(substr($fs_option, 1)) > 26) ? substr(substr($fs_option, 1), 0, 24) . '...' : substr($fs_option, 1);
                                                                    echo "    <option value='" . attr($fs_codes) . "'>" . text($code) . " " . text(substr($fs_category, 1)) . ": " . text($code_text) . "</option>\n";
                                                              }

                                                              // Create drop-lists based on categories defined within the codes.
                                                                $pres = sqlStatement("SELECT option_id, title FROM list_options " .
                                                                  "WHERE list_id = 'superbill' ORDER BY seq");
                                                                while ($prow = sqlFetchArray($pres)) {
                                                                    global $code_types;
                                                                    echo "    <option value=''> " . text($prow['title']) . "\n";
                                                                    $res = sqlStatement("SELECT code_type, code, code_text,modifier FROM codes " .
                                                                      "WHERE superbill = ? AND active = 1 " .
                                                                      "ORDER BY code_text", array($prow['option_id']));
                                                                    while ($row = sqlFetchArray($res)) {
                                                                        $ctkey = $fs->alphaCodeType($row['code_type']);
                                                                        if ($code_types[$ctkey]['nofs']) {
                                                                            continue;
                                                                        }

                                                                        $code_text = (strlen($row['code_text']) > 15) ? substr($row['code_text'], 0, 13) . '...' : $row['code_text'];
                                                                        echo "    <option value='" . attr($ctkey) . "|" .
                                                                          attr($row['code']) . ':' . attr($row['modifier']) . "|'>" . text($code_text) . "</option>\n";
                                                                    }
                                                                }
                                                                ?>
                                                      </select>
                                                  </div>
                                              </td>
                                              <td class="text-center">
                                                  <span class="modifier" name="visit_modifier" id="visit_mod_22" value="22" title="<?php echo xla('Modifier 22: Increased Procedural Services: When the work required to provide a service is substantially greater than typically required, it may be identified by adding modifier 22 to the usual procedure code.') ?>">22</span>
                                                  <span class="modifier" name="visit_modifier" id="visit_mod_24" value="24" title="<?php echo xla('Modifier 24: Unrelated Evaluation and Management Service by the Same Physician During a Postoperative Period') ?>">24</span>
                                                  <span class="modifier" name="visit_modifier" id="visit_mod_25" value="25" title="<?php echo xla('Modifier 25: Significant, separately identifiable evaluation and management (E/M) service by the same physician on the day of a procedure or other service') ?>">25</span>
                                                  <span class="modifier" name="visit_modifier" id="visit_mod_57" value="57" title="<?php echo xla('Modifier 57: Indicates an Evaluation and Management (E/M) service resulted in the initial decision to perform surgery either the day before a major surgery (90 day global) or the day of a major surgery.'); ?>">57</span>
                                              </td>
                                              <td>
                                                  <span id="visit_justification" class="float-right text-center" style="padding:7px 2px;"></span>
                                              </td>
                                          </tr>

                                          <tr id="neurosens_code" name="neurosens_code" class="nodisplay">
                                              <td colspan="3" style="padding-top:5px;padding-left:15px;"><input type="hidden" id="neurosens" style="width:50px;" value="92060" class="">
                                                  <i class="fa fa-check"></i> 92060 Sensorimotor Exam - no modifier required.
                                              </td>
                                          </tr>

                                          <tr>
                                              <td style="padding-top:10px;" colspan="3">
                                                  <b><u><?php echo xlt('Tests Performed'); ?>:</u></b>&nbsp;
                                                  <a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_todo_done_<?php echo attr($provider_id); ?>" target="RTop"
                                                     title="<?php echo xla('Click here to Edit this Doctor\'s Plan options') . ". \n" . xlt('Only entries with a Code are billable') . ". "; ?>"
                                                     name="provider_testing_codes" style="color:black;font-weight:600;"><i class="fa fa-pencil-alt fa-fw"></i> </a>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td colspan="3">
                                                  <table style="width:100%;">
                                                      <tr>
                                                            <?php

                                                              $counter = '0';
                                                              $count = '0';
                                                              $arrTESTS = explode("|", $Resource ?? ''); //form_eye_mag:Resource = billable things (not visit code) performed today
                                                              $query = "select * from list_options where list_id=? and activity='1' order by seq";
                                                              $TODO_data = sqlStatement($query, array("Eye_todo_done_" . $provider_id));
                                                            while ($row = sqlFetchArray($TODO_data)) {
                                                                if ($row['codes'] === '') {
                                                                    continue;
                                                                }
                                                                list($code_type_here,$code) = explode(":", $row['codes']);
                                                                $codedesc = lookup_code_descriptions($row['codes']);
                                                                $order   = array("\r\n", "\n","\r");
                                                                $codedesc = str_replace($order, '', $codedesc);
                                                                if ($codedesc == '') {
                                                                    $codedesc = $row['title'];
                                                                }
                                                                $codetext = $codedesc . " (" . $row['codes'] . ")";
                                                                $checked = '';
                                                                if (in_array($row['codes'], $arrTESTS)) {
                                                                    $checked = "checked='yes'";
                                                                    $class1 = "lights_on";
                                                                    $class2 = "";
                                                                } else {
                                                                    $class1 = "lights_off";
                                                                    $class2 = 'nodisplay';
                                                                }
                                                                /**
                                                                   *  This will link to a report generator for billable procedures/tests.
                                                                   *  They items need to be read/interpreted/dictated/documented to be billable.
                                                                   *  The reading may already be documented within the scanned item itself.
                                                                   *  Thus this will be optional.
                                                                   *  If needed, these reports should be categoriezed and filed ala the document engine.
                                                                   *  If a procedure/test has a document category and there is a document uploaded for today's encounter
                                                                   *  an icon should be displayed linked to the test/interpretation.
                                                                   *  Procedures/surgeries performed will need an op-note like format.
                                                                   *  This will be another series of forms then.
                                                                   *  echo "<i class='far fa-file-word'></i>";
                                                                   */
                                                                echo '<td class="' . $class1 . ' ">';
                                                                echo "<input type='checkbox' class='TESTS indent20' id='TEST_$counter' data-codetext='" . attr($codetext) . "' data-title='" . attr($codetext) . "' name='TEST[]' $checked value='" . attr($row['codes']) . "'> ";
                                                                $label = text(substr($row['title'], 0, 30));
                                                                echo "<label for='TEST_$counter' class='input-helper input-helper--checkbox'>";
                                                                echo $label . "</label>";
                                                                echo '<div id="TEST_' . $counter . '_justmods" class="' . $class2 . ' indent20" style="margin-bottom: 5px;">' . xlt('Modifier(s)') . ': <input type="text" style="width:100px;" id="TEST_' . $counter . '_modifier" value="' . ($row['modifier'] ?? '') . '">';
                                                                /*
                                                                OK we are going to attach this test to a specific ICD10 code listed above.
                                                                The codes are listed by number.
                                                                The user will add in the number here

                                                                */

                                                                echo '<br />' . xlt('Justify Dx') . ':

                                      <span class="TESTS_justify indent20" id="TEST_' . $counter . '_justify"></span>
                                      </div>
                                     ';

                                                                $count++;
                                                                $counter++;
                                                                if ($count == "3") {
                                                                    echo '</td><tr>';
                                                                    $count = '0';
                                                                } else {
                                                                    echo "</td>";
                                                                }
                                                            }

                                                            ?>
                                                          </td>
                                                      </tr>
                                                  </table>
                                                  <br />
                                              </td>
                                          </tr>
                                      </table>
                                      <table style="width:100%;padding-top:10px;vertical-align:top;">
                                          <tr>
                                              <td style="width:40%;">
                                                  <b><u><?php echo xlt('Appt{{Abbreviation for appointment}}') . " " . xlt('Status') . " / " . xlt('Flow Board'); ?>:</u></b><br />
                                                  <div class="indent20">
                                                      <input type="radio" name="visit_status" id="checked_out" value=">" /><label for="checked_out"> <b>></b> <?php echo xlt('Checked Out'); ?></label>
                                                      <br />
                                                      <input type="radio" name="visit_status" id="coded" value="$" /><label for="coded"> <b>$</b>&nbsp;<?php echo xlt('Coding complete'); ?></label>
                                                      <br />
                                                      <input type="radio" name="visit_status" id="send_notes" value="}" /><label for="send_notes"> <b>}</b> <?php echo xlt('Send Notes'); ?></label>
                                                  </div>
                                              </td>
                                              <td style="padding-left:15px;vertical-align:text-top;text-left">
                                                  <div class="widget text-center">
                                                      <b><u><?php echo xlt('Process');
                                                                  echo " " . xlt('Billing'); ?>:</b></u><br />
                                                      <button id="code_me_now" ><?php echo xlt('Populate Fee Sheet'); ?></button>
                                                      <button id="open_fee_sheet"
                                                              onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/encounter/load_form.php?formname=fee_sheet', 'Fee Sheet');" href="JavaScript:void(0);"
                                                              tabindex="-1"><?php echo xlt('Open Fee Sheet'); ?>
                                                      </button>
                                                  </div>
                                              </td>
                                          </tr>
                                      </table>
                                  </div>
                              </dd>

                                <?php
                                  /*
                                  *  This a provider-specific ORDER list of items that the user can define.
                                  *  Pencil icon links to 'list_options' in DB which opens in the RTop frame.
                                  *  If the provider-specific list does not exist, create it and populate it
                                  *  with generic starter items from list_options list "Eye_todo_done_defaults".
                                  *  This list is used to create the plan for the next visit.  Anything with a CODE
                                  *  is also listed as a billable item/TEST in the CODING ENGINE.
                                  */
                                  $query = "select * from list_options where list_id=? and activity='1' order by seq";
                                  $TODO_data = sqlStatement($query, array("Eye_todo_done_" . $provider_id));
                                if (sqlNumRows($TODO_data) < '1') {
                                    // Provider list is not created yet, or was deleted.
                                    // Create it fom defaults...
                                    $query = "INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `activity`) VALUES ('lists', ?, ?, '0', '1', '0', '', '', '', '0')";
                                    sqlStatement($query, array('Eye_todo_done_' . $provider_id,'Eye Orders ' . $prov_data['lname']));
                                    $SQL_INSERT = "INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `mapping`, `notes`, `codes`, `activity`, `subtype`) VALUES ";
                                    $number_rows = 0;
                                    $query = "SELECT * FROM list_options where list_id =? ORDER BY seq";
                                    $TODO_data = sqlStatement($query, array("Eye_todo_done_defaults"));
                                    while ($TODO = sqlFetchArray($TODO_data)) {
                                        if ($number_rows > 0) {
                                            $SQL_INSERT .= ",
                                            ";
                                        }
                                        $SQL_INSERT .= "('Eye_todo_done_" . add_escape_custom($provider_id) . "','" . add_escape_custom($TODO['option_id']) . "','" . add_escape_custom($TODO['title']) . "','" . add_escape_custom($TODO['seq']) . "','" . add_escape_custom($TODO['mapping']) . "','" . add_escape_custom($TODO['notes']) . "','" . add_escape_custom($TODO['codes']) . "','" . add_escape_custom($TODO['activity']) . "','" . add_escape_custom($TODO['subtype']) . "')";
                                        $number_rows++;
                                    }
                                    sqlStatement($SQL_INSERT . ";");
                                }
                                ?>
                              <dt class="borderShadow">
                                  <span><?php echo xlt('Next Visit Orders'); ?></span>
                                  <a href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_list.php?list_id=Eye_todo_done_<?php echo attr($provider_id); ?>" target="RTop"
                                     title="<?php echo xla('Click here to Edit this Doctor\'s Plan options'); ?>"
                                     name="provider_todo" style="color:black;font-weight:600;"><i class="fa fa-pencil-alt fa-fw"></i> </a>
                              </dt>
                              <dd>
                                  <table>
                                      <tr class="" style="vertical-align:bottom;margin: 10px;;">
                                          <td></td>
                                      </tr>
                                      <tr>
                                          <td style="padding-right:20px;padding-left:20px;">
                                                <?php
                                                  // Iterate through this "provider's" orders and compare to list of options.
                                                  $count = 0;
                                                  $counter = 0;
                                                  $query = "SELECT * FROM form_eye_mag_orders where form_id=? and pid=? ORDER BY id ASC";
                                                  $PLAN_results = sqlStatement($query, array($form_id, $pid ));
                                                while ($plan_row = sqlFetchArray($PLAN_results)) {
                                                    $PLAN_arr[] = $plan_row;
                                                }
                                                while ($row = sqlFetchArray($TODO_data)) {
                                                    $arrPLAN[$counter]['option_id'] = $row['option_id'];
                                                    $arrPLAN[$counter]['title'] = $row['title'];
                                                    $arrPLAN[$counter]['option_value'] = $row['option_value'];
                                                    $arrPLAN[$counter]['mapping'] = $row['mapping'];
                                                    $arrPLAN[$counter]['notes'] = $row['notes'];
                                                    $arrPLAN[$counter]['codes'] = $row['codes'];
                                                    $arrPLAN[$counter]['subtype'] = $row['subtype'];
                                                    $checked = '';
                                                    $title = $row['title'];
                                                    if ($here = in_array_r($title, ($PLAN_arr ?? ''))) {
                                                        $checked = "checked='yes'";
                                                        $found++;
                                                    }
                                                    // <!-- <i title="Build your plan." class="fa fa-mail-forward fa-flip-horizontal" id="make_blank_PLAN" name="make_blank_PLAN"></i>-->
                                                    echo "<input type='checkbox' id='PLAN$counter' name='PLAN[]' $checked value='" . attr($row['title']) . "'> ";
                                                    $label = text(substr($row['title'], 0, 30));
                                                    echo "<label for='PLAN$counter' class='input-helper input-helper--checkbox' title='" . attr($row['notes']) . "'>";
                                                    echo $label . "</label><br />";
                                                    $count++;
                                                    $counter++;
                                                    if ($count == "3") {
                                                        echo '</td><tr><td style="padding-right:20px;padding-left:20px;">';
                                                        $count = '0';
                                                    } else {
                                                        echo "</td><td>";
                                                    }
                                                }
                                                ?>
                                              <script>
                                                  var PLANoptions = <?php echo json_encode($arrPLAN ?? ''); ?>;
                                              </script>
                                          </td>
                                      </tr>
                                      <tr>
                                          <td colspan="3" style="padding-left:20px;padding-top:4px;">
                                <textarea id="Plan<?php echo $counter; ?>" name="PLAN[]" style="width: 440px;height: 44px;"><?php if (($found ?? null) < (empty($PLAN_arr) ? 0 : count($PLAN_arr))) {
                                    echo $PLAN_arr[count($PLAN_arr) - 1]['ORDER_DETAILS']; } ?></textarea>
                                          </td>
                                      </tr>
                                  </table>
                              </dd>


                              <dt class="borderShadow"><span><?php echo xlt('Communication Engine'); ?></span></dt>
                              <dd>
                                  <div style="padding:5px 20px 5px 20px;">

                                      <table style="width:100%;">
                                          <tr>
                                              <td class="bold underline" style="min-width:50px;"></td>
                                              <td class="bold underline" style="min-width:100px;">PCP</td>
                                              <td class="bold underline" style="min-width:100px;">Referrer</td>
                                          </tr>
                                          <tr>
                                              <td></td>
                                              <td>
                                                    <?php
                                                      $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
                                                          "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
                                                          "AND ( authorized = 1 OR ( username = '' AND npi != '' ) ) " .
                                                          "ORDER BY lname, fname");
                                                      echo "<select name='form_PCP' id='form_PCP' title='" . xla('Primary Care Provider') . "'>";
                                                      echo "<option value=''>" . xlt($empty_title ?? '') . "</option>";
                                                      $got_selected = false;
                                                      while ($urow = sqlFetchArray($ures)) {
                                                          $uname = text($urow['lname'] . ' ' . $urow['fname']);
                                                          $optionId = attr($urow['id']);
                                                          echo "<option value='$optionId'";
                                                          if ($urow['id'] == $pat_data['providerID']) {
                                                              echo " selected";
                                                              $got_selected = true;
                                                          }

                                                          echo ">$uname</option>";
                                                      }

                                                      if (!$got_selected && ($currvalue ?? null)) {
                                                          echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
                                                          echo "</select>";
                                                          echo "<span class='danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
                                                      } else {
                                                          echo "</select>";
                                                      }
                                                        ?>
                                              </td>
                                              <td>
                                                <?php
                                                  $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
                                                      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
                                                      "AND ( authorized = 1 OR ( username = '') ) " .
                                                      "ORDER BY lname, fname");
                                                  echo "<select name='form_rDOC' id='form_rDOC' title='" . xla('Every name in the address book appears here, not only physicians.') . "'>";
                                                  echo "<option value=''>" . xlt($empty_title ?? '') . "</option>";
                                                  $got_selected = false;
                                                  while ($urow = sqlFetchArray($ures)) {
                                                      $uname = text($urow['lname'] . ' ' . $urow['fname']);
                                                      $optionId = attr($urow['id']);
                                                      echo "<option value='$optionId'";
                                                      if ($urow['id'] == $pat_data['ref_providerID']) {
                                                          echo " selected";
                                                          $got_selected = true;
                                                      }

                                                      echo ">$uname</option>";
                                                  }

                                                  if (!$got_selected && ($currvalue ?? '')) {
                                                      echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
                                                      echo "</select>";
                                                      echo " <span class='danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
                                                  } else {
                                                      echo "</select>";
                                                  }
                                                    ?>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td class="bold top"><?php echo xlt('Phone'); ?>:</td>
                                              <td>
                                                  <span id="pcp_phone"><?php echo text($pcp_data['phonew1'] ?? ''); ?></span>
                                                  <span id="pcp_phonew2"><?php if ($pcp_data['phonew2'] ?? '') {
                                                        echo "<br />" . text($pcp_data['phonew2']);} ?>
                                                  </span>
                                              </td>
                                              <td>
                                                  <span id="ref_phone"><?php echo text($ref_data['phonew1'] ?? ''); ?></span>
                                                  <span id="ref_phonew2"><?php if ($pcp_data['phonew2'] ?? null) {
                                                        echo "<br />" . text($pcp_data['phonew2']);} ?>
                                                  </span>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td class="bold top"><?php echo xlt('Fax'); ?>:</td>
                                              <td class="bold">
                                                    <?php
                                                    if (($pcp_data['fax'] ?? '') > '') {
                                                        // does the fax already exist?
                                                        $query    = "SELECT * FROM form_taskman WHERE TO_ID=? and PATIENT_ID=? and ENC_ID=?";
                                                        $FAX_PCP  =  sqlQuery($query, array($pat_data['providerID'],$pid,$encounter));
                                                        if ($FAX_PCP['ID']) { //it is here already, make them print and manually fax it.  Show icon
                                                            ?>
                                                            <span id='pcp_fax'><?php echo text($pcp_data['fax']); ?></span>
                                                            <span id='pcp_fax_info'>
                                                                 <a onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/controller.php?document&view&patient_id=<?php echo attr($pid); ?>&doc_id=<?php echo attr($FAX_PCP['DOC_ID']); ?>', 'PCP: Fax Report');"
                                                                    href='JavaScript:void(0);'
                                                                    title='<?php echo xla('View the Summary Report sent via Fax Server on') . "\t " . attr($FAX_PCP['COMPLETED_DATE']); ?>'>
                                                                  <i class='far fa-file-pdf fa-fw'></i></a>
                                                                  <i class='fas fa-redo fa-fw'
                                                                     title='<?php echo xla("Click to Re-Send this fax"); ?>'
                                                                     onclick="top.restoreSession(); create_task('<?php echo attr($pat_data['providerID']); ?>','Fax-resend','pcp'); return false;"></i>
                                                            </span> <?php
                                                        } else { ?>
                                                            <span id='pcp_fax'>
                                                                <a href="JavaScript:void(0);"
                                                                   onclick="top.restoreSession(); create_task('<?php echo attr($pat_data['providerID']); ?>','Fax','pcp'); return false;">
                                                                    <?php echo text($pcp_data['fax']); ?>
                                                                    <span id="status_Fax_pcp"><i class="fas fa-fax fa-fw"></i></span>
                                                                </a>&nbsp;&nbsp;
                                                            </span>
                                                            <span id='pcp_fax_info'></span>
                                                            <?php
                                                        }
                                                    } ?>
                                              </td>
                                              <td class="bold">
                                                  <?php if (($ref_data['fax'] ?? null) > '') {
                                                      // does the fax already exist?
                                                        $query    = "SELECT * FROM form_taskman WHERE TO_ID=? and PATIENT_ID=? and ENC_ID=?";
                                                        $FAX_REF  =  sqlQuery($query, array($pat_data['ref_providerID'],$pid,$encounter));
                                                        if ($FAX_REF['ID']) { //it is here already, make them print and manually fax it.  Show icon ?>
                                                             <span id='ref_fax'><?php echo text($ref_data['fax']); ?></span>
                                                             <span id='ref_fax_info'>
                                                                 <a onclick="openNewForm('<?php echo $GLOBALS['webroot']; ?>/controller.php?document&view&patient_id=<?php echo attr($pid); ?>&doc_id=<?php echo attr($FAX_REF['DOC_ID']); ?>', 'Refer: Fax Report');"
                                                                    href='JavaScript:void(0);'
                                                                    title='<?php echo xla('View the Summary Report sent via Fax Server on') . "\t " . attr($FAX_REF['COMPLETED_DATE']); ?>'>
                                                                  <i class='far fa-file-pdf fa-fw'></i></a>
                                                                  <i class='fas fa-redo fa-fw'
                                                                     title='<?php echo xla("Click to Re-Send this fax"); ?>'
                                                                     onclick="top.restoreSession(); create_task('<?php echo attr($pat_data['ref_providerID']); ?>','Fax-resend','ref'); return false;"></i>
                                                              </span> <?php
                                                        } else { ?>
                                                             <span id='ref_fax'>
                                                                 <a href="JavaScript:void(0);" onclick="top.restoreSession(); create_task('<?php echo attr($pat_data['ref_providerID']); ?>','Fax','ref'); return false;">
                                                                    <?php echo text($ref_data['fax']); ?>
                                                                    <span id="status_Fax_ref"><i class="fas fa-fax fa-fw"></i></span>
                                                                </a>&nbsp;&nbsp;
                                                            </span>
                                                            <span id='ref_fax_info'></span>
                                                              <?php
                                                        }
                                                  } ?>
                                                  </span>
                                              </td>
                                          </tr>
                                          <tr>
                                              <td class="top bold"><?php echo xlt('Address'); ?>:</td>
                                              <td class="top">
                                                  <span id="pcp_address">
                                                        <?php
                                                        if (($pcp_data['organization'] ?? '') > '') {
                                                            echo text($pcp_data['organization']) . "<br />";
                                                        }
                                                        if (($pcp_data['street'] ?? '') > '') {
                                                            echo text($pcp_data['street']) . "<br />";
                                                        }
                                                        if (($pcp_data['streetb'] ?? '') > '') {
                                                            echo text($pcp_data['streetb']) . "<br />";
                                                        }
                                                        if (($pcp_data['city'] ?? '') > '') {
                                                            echo text($pcp_data['city']) . ", ";
                                                        }
                                                        if (($pcp_data['state'] ?? '') > '') {
                                                            echo text($pcp_data['state']) . " ";
                                                        }
                                                        if (($pcp_data['zip'] ?? '') > '') {
                                                            echo text($pcp_data['zip']) . "<br />";
                                                        }

                                                        if (($pcp_data['street2'] ?? '') > '') {
                                                            echo "<br />" . text($pcp_data['street2']) . "<br />";
                                                        }
                                                        if (($pcp_data['streetb2'] ?? '') > '') {
                                                            echo text($pcp_data['streetb2']) . "<br />";
                                                        }
                                                        if (($pcp_data['city2'] ?? '') > '') {
                                                            echo text($pcp_data['city2']) . ", ";
                                                        }
                                                        if (($pcp_data['state2'] ?? '') > '') {
                                                            echo text($pcp_data['state2']) . " ";
                                                        }
                                                        if (($pcp_data['zip2'] ?? '') > '') {
                                                            echo text($pcp_data['zip2']) . "<br />";
                                                        }
                                                        ?>
                                                  </span>
                                              </td>
                                              <td class="top">
                                                <span id="ref_address">
                                                    <?php
                                                    if (($ref_data['organization'] ?? null) > '') {
                                                        echo text($ref_data['organization']) . "<br />";
                                                    }
                                                    if (($ref_data['street'] ?? null) > '') {
                                                        echo text($ref_data['street']) . "<br />";
                                                    }
                                                    if (($ref_data['streetb'] ?? null) > '') {
                                                        echo text($ref_data['streetb']) . "<br />";
                                                    }
                                                    if (($ref_data['city'] ?? null) > '') {
                                                        echo text($ref_data['city']) . ", ";
                                                    }
                                                    if (($ref_data['state'] ?? null) > '') {
                                                        echo text($ref_data['state']) . " ";
                                                    }
                                                    if (($ref_data['zip'] ?? null) > '') {
                                                        echo text($ref_data['zip']) . "<br />";
                                                    }

                                                    if (($ref_data['street2'] ?? null) > '') {
                                                        echo "<br />" . text($ref_data['street2']) . "<br />";
                                                    }
                                                    if (($ref_data['streetb2'] ?? null) > '') {
                                                        echo text($ref_data['streetb2']) . "<br />";
                                                    }
                                                    if (($ref_data['city2'] ?? null) > '') {
                                                        echo text($ref_data['city2']) . ", ";
                                                    }
                                                    if (($ref_data['state2'] ?? null) > '') {
                                                        echo text($ref_data['state2']) . " ";
                                                    }
                                                    if (($ref_data['zip2'] ?? null) > '') {
                                                        echo text($ref_data['zip2']) . "<br />";
                                                    }
                                                    ?>
                                                </span>
                                              </td>
                                          </tr>
                                          <tr><td>&nbsp;</td></tr>
                                          <tr><td class="top bold"><?php echo xlt('Insurance'); ?>:</td><td><?php echo text($ins_coA); ?></td></tr>
                                            <?php if (!empty($ins_coB)) { ?>
                                          <tr><td class="top bold"><?php echo xlt('Secondary'); ?>:</td><td><?php echo text($ins_coB); ?></td></tr>
                                            <?php } ?>
                                          <tr><td class="top bold"><?php echo xlt('Pharmacy'); ?>:</td>
                                                <?php
                                                  $frow['data_type']    = "12";
                                                  $frow['form_id']      = 'EYE';
                                                  $frow['field_id']     = 'pharmacy_id';
                                                  $frow['list_id']      = 'pharmacy_id';
                                                  $frow['description']  = "Pharmacy";
                                                  echo "<td  colspan='2'>";
                                                  ob_start();
                                                  generate_form_field($frow, $pat_data['pharmacy_id']);
                                                  $select_pharm = ob_get_clean();
                                                  echo str_replace("form-control", "", $select_pharm);
                                                ?>
                                              </td><td class="top">
                                                  <button onclick="editScripts('<?php echo $GLOBALS['webroot']; ?>/controller.php?prescription&list&pid=<?php echo attr($pat_data['pid']); ?>');"><?php echo xlt('eRx'); ?></button>
                                              </td></tr>

                                      </table>
                                  </div>
                              </dd>
                          </dl>
                      </div>
                  </div>
                  </div>
                </div>
              </div>
              <!-- END IMP/PLAN -->
            <!-- end form_container for the main body of the form -->
          </div>
          <!-- end Layer1 -->

        </form>
        <!-- end form -->
      </div>    <!-- end Layer3 -->
    </div>     <!-- end page wrapper -->
    <?php
    if ($display != "fullscreen") {
      // trial fullscreen will lead to tablet versions and bootstrap menu overhaul
      // this function is in php/eye_mag_functions.php
        $output = menu_overhaul_bottom($pid, $encounter);
        echo $output;
    }
    ?>
  <script src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/js/jquery-panelslider/jquery.panelslider.min.js"></script>
  <script>
        function openNewForm(sel, label) {
            top.restoreSession();
            FormNameValueArray = sel.split('formname=');
            if(FormNameValueArray[1] == 'newpatient' )
            {
                parent.frames[0].location.href = sel;
            }
            else
            {
                parent.twAddFrameTab('enctabs', label, sel);
            }
        }
        /**
         * Function to add a CODE to an IMPRESSION/PLAN item
         * This is for callback by the find-code popup in IMPPLAN area.
         * Appends to or erases the current list of diagnoses.
         */
        function set_related(codetype, code, selector, codedesc) {
            //target is the index of IMPRESSION[index].code we are searching for.
            var span = document.getElementById('CODE_'+IMP_target);
            if ('textContent' in span) {
                span.textContent = code;
            } else {
                span.innerText = code;
            }
            $('#CODE_'+IMP_target).attr('title',codetype + ':' + code + ' ('+codedesc+')');

            obj.IMPPLAN_items[IMP_target].code = code;
            obj.IMPPLAN_items[IMP_target].codetype = codetype;
            obj.IMPPLAN_items[IMP_target].codedesc = codedesc;
            obj.IMPPLAN_items[IMP_target].codetext = codetype + ':' + code + ' ('+codedesc+')';
            // This lists the text for the CODE at the top of the PLAN_
            // It is already there on mouseover the code itself and is printed in reports//faxes, so it was removed here
            //  obj.IMPPLAN_items[IMP_target].plan = codedesc+"\r"+obj.IMPPLAN_items[IMP_target].plan;

            if (obj.IMPPLAN_items[IMP_target].PMSFH_link > '') {
                var data = obj.IMPPLAN_items[IMP_target].PMSFH_link.match(/(.*)_(.*)/);
                if ((data[1] == "POH")||(data[1] == "PMH")) {
                    obj.PMSFH[data[1]][data[2]].code= code;
                    obj.PMSFH[data[1]][data[2]].codetype = codetype;
                    obj.PMSFH[data[1]][data[2]].codedesc = codedesc;
                    obj.PMSFH[data[1]][data[2]].description = codedesc;
                    obj.PMSFH[data[1]][data[2]].diagnosis = codetype + ':' + code;
                    obj.PMSFH[data[1]][data[2]].codetext = codetype + ':' + code + ' ('+codedesc+')';
                    build_DX_list(obj);
                    update_PMSFH_code(obj.PMSFH[data[1]][data[2]].issue,codetype + ':' +code);
                }
            }
            store_IMPPLAN(obj.IMPPLAN_items,'1');
        }
        <?php require_once("$srcdir/restoreSession.php");
        ?>
        function dopclick(id) {
            <?php if (($thisauth ?? '') != 'write') : ?>
            dlgopen('../../patient_file/summary/a_issue.php?issue=0&thistype=' + encodeURIComponent(id), '_blank', 550, 400,  '', <?php echo xlj('Issues'); ?> );
            <?php else : ?>
            alert("<?php echo xls('You are not authorized to add/edit issues'); ?>");
            <?php endif; ?>
        }
        function doscript(type,id,encounter,rx_number) {
             dlgopen('../../forms/eye_mag/SpectacleRx.php?REFTYPE=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id) + '&encounter=' + encodeURIComponent(encounter) + '&form_id=' + <?php echo js_url($form_id); ?> + '&rx_number=' + encodeURIComponent(rx_number), '_blank', 660, 590,'', <?php echo xlj('Dispense Rx'); ?>);
        }

        function dispensed(pid) {
            dlgopen('../../forms/eye_mag/SpectacleRx.php?dispensed=1&pid='+encodeURIComponent(pid), '_blank', 560, 590, '', <?php echo xlj('Rx History'); ?>);
                    }
                    // This invokes the find-code popup.
                    function sel_diagnosis(target,term) {
                        if (target =='') {
                            target = "0";
                        }
                        IMP_target = target;
                        <?php
                        if (($irow['type'] ?? null) == 'PMH') { //or POH
                            ?>
            dlgopen('<?php echo $rootdir ?>/patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("medical_problem", "csv")) ?>&search_term='+encodeURI(term), '_blank', 600, 400,'', <?php echo xlj('Code Search'); ?>);
                            <?php
                        } else {
                            ?>
                        dlgopen('<?php echo $rootdir ?>/patient_file/encounter/find_code_popup.php?codetype=<?php echo attr(collect_codetypes("diagnosis", "csv")) ?>&search_term='+encodeURI(term), '_blank', 600, 400, '', <?php echo xlj('Code Search'); ?>);
                            <?php
                        }
                        ?>
        }

        var obj =[];
        <?php
        //also add in any obj.Clinical data if the form was already opened
        $codes_found = start_your_engines($encounter_data);
        if ($codes_found) { ?>
        obj.Clinical = [<?php echo json_encode($codes_found[0]); ?>];
            <?php
        } ?>

        var base = '<?php echo $GLOBALS['webroot']; ?>';
    </script>
    <script src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/js/shorthand_eye.js"></script>
    <script src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/js/shortcut.js-2-01-B/shortcut.js"></script>
    <script src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/js/eye_base.php?enc=<?php echo attr($encounter); ?>&providerID=<?php echo attr($provider_id); ?>"></script>
    <script src="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/js/canvasdraw.js"></script>
    <div id="right-panel" name="right-panel" class="panel_side" >
      <div class="text-center" style="margin-top: 10px;">
        <span class="fa fa-file-alt" id="PANEL_TEXT" name="PANEL_TEXT"></span>
        <span class="fa fa-database" id="PANEL_QP" name="PANEL_QP"></span>
        <span class="fa fa-paint-brush" id="PANEL_DRAW" name="PANEL_DRAW"></span>
        <span class="fa fa-user-md fa-sm" name="Shorthand_kb"></span>
        <span class="fa fa-times" id="close-panel-bt"></span>
      </div>
      <div id="right_panel_refresh" data-role="panel" name="right_panel_refresh">
        <?php
        // We are building the panel bar with the patient medical info
        // Since the "lists" table has a "subtype" field now
        // each section could be customized, like SOCHx, subtype "smoking"
        // However openEMR stores most of this SocHx data in the layout_options table with
        // the form_id='HIS' and group_name='4Lifestyle' fields.
        // So we need to conform and pull this information out of the current "History" fields.
        // We do this in the eye_mag_functions.php file by creating a new array $PMSFH
        // pulling in the issues, Social History, FH and ROS into one place.
        // Eye Form uses $PMSFH to build two display options:
        // 1. PMH_QP (square panel)
        //      - function display_PRIOR_section("PMSFH",$id,$id,$pid) in php/eye_mag_functions.php
        //      - creates/populates the PMSFH values to the right of the PMH zone, in the PMH_2: QP zone.
        // 2. Right Panel
        //      - function show_PMSFH_panel($PMSFH) in php/eye_mag_functions.php
        //      - creates/populates the right panel NavBar
        echo $output_PMSFH_panel = show_PMSFH_panel($PMSFH);
        ?>
      </div>
    </div>
    <script>
      $('#left-panel-link').panelslider({side: 'left', clickClose: false, duration: 600, easingOpen: 'easeInBack', easingClose: 'easeOutBack'});
      $('#right-panel-link').panelslider({side: 'right', clickClose: false, duration: 600, easingOpen: 'easeInBack', easingClose: 'easeOutBack'});
      $('#right-panel-link_2').panelslider({side: 'right', clickClose: false, duration: 600, easingOpen: 'easeInBack', easingClose: 'easeOutBack'});
      $('#close-panel-bt').click(function() {
      $.panelslider.close();
      });
        <?php
        if (($PANEL_RIGHT ?? null) > '0') { ?>
          $("#right-panel-link").trigger("click");
            <?php
        }
        ?>
    </script>
  </body>
</html>
