<?php
/*
 * The page shown when the user requests to see this form in a "report view". does not allow editing contents, or saving. has 'print' and 'delete' buttons.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for display_layout_rows(), ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = 'form_gyn_ros';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'Gyn Review of Systems';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'gyn_ros';

/* Check the access control lists to ensure permissions to this page */
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
 die($form_name.': Access Denied.');
}
/* perform a squad check for pages touching patients, if we're in 'athletic team' mode */
if ($GLOBALS['athletic_team']!='false') {
  $tmp = getPatientData($pid, 'squad');
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
}
/* Use the formFetch function from api.inc to load the saved record */
$xyzzy = formFetch($table_name, $_GET['id']);

/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
$manual_layouts = array( 
 'cardio_reviewed' => 
   array( 'field_id' => 'cardio_reviewed',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'If CHECKED you must include a note',
          'list_id' => 'yesno' ),
 'cardio_note' => 
   array( 'field_id' => 'cardio_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Described the results of the review',
          'list_id' => '' ),
 'gastro_reviewed' => 
   array( 'field_id' => 'gastro_reviewed',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'If CHECKED you must include a note',
          'list_id' => 'yesno' ),
 'gastro_note' => 
   array( 'field_id' => 'gastro_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe the results of the review',
          'list_id' => '' ),
 'Urinary_reviewed' => 
   array( 'field_id' => 'Urinary_reviewed',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'If CHECKED you must include a note',
          'list_id' => 'yesno' ),
 'Urinary_note' => 
   array( 'field_id' => 'Urinary_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe the results of the review',
          'list_id' => '' ),
 'cns_reviewed' => 
   array( 'field_id' => 'cns_reviewed',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'If CHECKED you must include a note',
          'list_id' => 'yesno' ),
 'cns_note' => 
   array( 'field_id' => 'cns_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe the results of the review',
          'list_id' => '' ),
 'other_reviewed' => 
   array( 'field_id' => 'other_reviewed',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'If CHECKED you must include a note',
          'list_id' => 'yesno' ),
 'other_note' => 
   array( 'field_id' => 'other_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe system reviewed and the results of the review',
          'list_id' => '' ),
 'complications_reviewed' => 
   array( 'field_id' => 'complications_reviewed',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'If CHECKED you must include a note',
          'list_id' => 'yesno' ),
 'complications_note' => 
   array( 'field_id' => 'complications_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe the complication',
          'list_id' => '' ),
 'lmpdate' => 
   array( 'field_id' => 'lmpdate',
          'data_type' => '4',
          'fld_length' => '0',
          'description' => 'When was the the first day of your last menstrual period?',
          'list_id' => '' ),
 'cycle_int' => 
   array( 'field_id' => 'cycle_int',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => 'What is the Interval of your Flow?',
          'list_id' => 'menses_cycle' ),
 'cycle_int_note' => 
   array( 'field_id' => 'cycle_int_note',
          'data_type' => '3',
          'fld_length' => '100',
          'max_length' => '1',
          'description' => 'Enter any addtional interval information',
          'list_id' => '' ),
 'flowfhcount' => 
   array( 'field_id' => 'flowfhcount',
          'data_type' => '2',
          'fld_length' => '10',
          'max_length' => '2',
          'description' => 'How many tampons/pads per day',
          'list_id' => '' ),
 'flowhrs' => 
   array( 'field_id' => 'flowhrs',
          'data_type' => '2',
          'fld_length' => '10',
          'max_length' => '2',
          'description' => 'How many between tampons/pads changes',
          'list_id' => '' ),
 'pmb' => 
   array( 'field_id' => 'pmb',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'yesno' ),
 'vag_discharge' => 
   array( 'field_id' => 'vag_discharge',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'If CHECKED you must include a note',
          'list_id' => 'yesno' ),
 'vag_discharge_note' => 
   array( 'field_id' => 'vag_discharge_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe the color and amount',
          'list_id' => '' ),
 'vag_itching' => 
   array( 'field_id' => 'vag_itching',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'yesno' ),
 'vag_itching_note' => 
   array( 'field_id' => 'vag_itching_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe',
          'list_id' => '' ),
 'vag_odor' => 
   array( 'field_id' => 'vag_odor',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'yesno' ),
 'vag_odor_note' => 
   array( 'field_id' => 'vag_odor_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe',
          'list_id' => '' ),
 'vag_irratation' => 
   array( 'field_id' => 'vag_irratation',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'yesno' ),
 'vag_irratation_note' => 
   array( 'field_id' => 'vag_irratation_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe',
          'list_id' => '' ),
 'vag_spotting' => 
   array( 'field_id' => 'vag_spotting',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'yesno' ),
 'vag_spotting_note' => 
   array( 'field_id' => 'vag_spotting_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe',
          'list_id' => '' ),
 'priortreatment' => 
   array( 'field_id' => 'priortreatment',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => 'Have you tried to treat the symptoms prior to this visit',
          'list_id' => 'yesno' ),
 'priortreatment_note' => 
   array( 'field_id' => 'priortreatment_note',
          'data_type' => '3',
          'fld_length' => '160',
          'max_length' => '3',
          'description' => 'Describe',
          'list_id' => '' ),
 'pain_menses' => 
   array( 'field_id' => 'pain_menses',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => 'Pain with or between menses?',
          'list_id' => 'menses_pain' ),
 'pain_level' => 
   array( 'field_id' => 'pain_level',
          'data_type' => '2',
          'fld_length' => '10',
          'max_length' => '2',
          'description' => 'Level of pain 1-10',
          'list_id' => '' ),
 'pain_location' => 
   array( 'field_id' => 'pain_location',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => 'Select Quadrant',
          'list_id' => 'menses_pain_location' ),
 'pain_lenth' => 
   array( 'field_id' => 'pain_lenth',
          'data_type' => '2',
          'fld_length' => '10',
          'max_length' => '30',
          'description' => 'Enter Months, Years etc',
          'list_id' => '' ),
 'pain_drug_resp' => 
   array( 'field_id' => 'pain_drug_resp',
          'data_type' => '2',
          'fld_length' => '10',
          'max_length' => '30',
          'description' => 'Is the pain responsive to OTC drugs? Which?',
          'list_id' => '' ),
 'pain_intercourse' => 
   array( 'field_id' => 'pain_intercourse',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'yesno' ),
 'pain_intercourse_time' => 
   array( 'field_id' => 'pain_intercourse_time',
          'data_type' => '3',
          'fld_length' => '120',
          'max_length' => '1',
          'description' => 'How Long, etc?',
          'list_id' => '' )
 );

/* since we have no-where to return, abuse returnurl to link to the 'edit' page */
/* FIXME: pass the ID, create blank rows if necissary. */
$returnurl = "../../forms/$form_folder/view.php?mode=noencounter";

/* remove the time-of-day from all date fields */
if ($xyzzy['lmpdate'] != '') {
    $dateparts = split(' ', $xyzzy['lmpdate']);
    $xyzzy['lmpdate'] = $dateparts[0];
}

/* define check field functions. used for translating from fields to html viewable strings */

function chkdata_Date(&$record, $var) {
        return htmlspecialchars($record{"$var"},ENT_QUOTES);
}

function chkdata_Txt(&$record, $var) {
        return htmlspecialchars($record{"$var"},ENT_QUOTES);
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<!-- declare this document as being encoded in UTF-8 -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" ></meta>

<!-- supporting javascript code -->
<!-- for dialog -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<!-- For jquery, required by edit, print, and delete buttons. -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>

<!-- Global Stylesheet -->
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css"/>
<!-- Form Specific Stylesheet. -->
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css"/>

<script type="text/javascript">

<!-- FIXME: this needs to detect access method, and construct a URL appropriately! -->
function PrintForm() {
    newwin = window.open("<?php echo $rootdir.'/forms/'.$form_folder.'/print.php?id='.$_GET['id']; ?>","print_<?php echo $form_name; ?>");
}

</script>
<title><?php echo htmlspecialchars('Show '.$form_name); ?></title>

</head>
<body class="body_top">

<div id="title">
<span class="title"><?php xl($form_name,'e'); ?></span>
<?php
 if ($thisauth == 'write' || $thisauth == 'addonly')
  { ?>
<a href="<?php echo $returnurl; ?>" onclick="top.restoreSession()">
<span class="back"><?php xl($tmore,'e'); ?></span>
</a>
<?php }; ?>
</div>

<form method="post" id="<?php echo $form_folder; ?>" action="">

<!-- container for the main body of the form -->
<div id="form_container">

<div id="show">

<!-- display the form's manual based fields -->
<table border='0' cellpadding='0' width='100%'>
<tr><td class='sectionlabel'>Cardio - Respiratory System</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['cardio_reviewed'], $xyzzy['cardio_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['cardio_note'], $xyzzy['cardio_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields)1--><td class='emptycell' colspan='1'></td></tr>
<tr><td class='sectionlabel'>Gastro - Intestinal System</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['gastro_reviewed'], $xyzzy['gastro_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['gastro_note'], $xyzzy['gastro_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields)1--><td class='emptycell' colspan='1'></td></tr>
<tr><td class='sectionlabel'>Urinary System</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['Urinary_reviewed'], $xyzzy['Urinary_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['Urinary_note'], $xyzzy['Urinary_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields)1--><td class='emptycell' colspan='1'></td></tr>
<tr><td class='sectionlabel'>Central Nervous System</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['cns_reviewed'], $xyzzy['cns_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['cns_note'], $xyzzy['cns_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields)1--><td class='emptycell' colspan='1'></td></tr>
<tr><td class='sectionlabel'>Other Systems</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['other_reviewed'], $xyzzy['other_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['other_note'], $xyzzy['other_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields)1--><td class='emptycell' colspan='1'></td></tr>
<tr><td class='sectionlabel'>Complications</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['complications_reviewed'], $xyzzy['complications_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['complications_note'], $xyzzy['complications_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields)1--><td class='emptycell' colspan='1'></td></tr>
<tr><td class='sectionlabel'>Menstrual History</td><!-- called consumeRows 015--> <td class='fieldlabel' colspan='3'><?php echo xl_layout_label('LMP Start Date','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['lmpdate'], $xyzzy['lmpdate']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Cycle Interval','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['cycle_int'], $xyzzy['cycle_int']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['cycle_int_note'], $xyzzy['cycle_int_note']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Flow - FH count','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['flowfhcount'], $xyzzy['flowfhcount']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Flow - Hrs between changes','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['flowhrs'], $xyzzy['flowhrs']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Post Menapausal Bleeding','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['pmb'], $xyzzy['pmb']); ?></td><!-- called consumeRows 215--> <!-- Exiting not($fields)3--><td class='emptycell' colspan='1'></td></tr>
<tr><td class='sectionlabel'>Vaginal Infection</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Discharge','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['vag_discharge'], $xyzzy['vag_discharge']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['vag_discharge_note'], $xyzzy['vag_discharge_note']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Itching','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['vag_itching'], $xyzzy['vag_itching']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['vag_itching_note'], $xyzzy['vag_itching_note']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Odor','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['vag_odor'], $xyzzy['vag_odor']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['vag_odor_note'], $xyzzy['vag_odor_note']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Irritation','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['vag_irratation'], $xyzzy['vag_irratation']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['vag_irratation_note'], $xyzzy['vag_irratation_note']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Spotting','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['vag_spotting'], $xyzzy['vag_spotting']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['vag_spotting_note'], $xyzzy['vag_spotting_note']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 225--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Prior Treatment','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['priortreatment'], $xyzzy['priortreatment']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['priortreatment_note'], $xyzzy['priortreatment_note']); ?></td><!-- called consumeRows 525--> <!-- Exiting not($fields)0--></tr>
<tr><td class='sectionlabel'>Pelvic Pain</td><!-- called consumeRows 015--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Menses Pain','e').':'; ?></td><td class='text data' colspan='4'><?php echo generate_display_field($manual_layouts['pain_menses'], $xyzzy['pain_menses']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 325--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Level','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['pain_level'], $xyzzy['pain_level']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Location','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['pain_location'], $xyzzy['pain_location']); ?></td></tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 325--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Length of Time','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['pain_lenth'], $xyzzy['pain_lenth']); ?></td><td class='emptycell' colspan='1'></td></div>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 425--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain OTC/NSAIDS Response','e').':'; ?></td><td class='text data' colspan='3'><?php echo generate_display_field($manual_layouts['pain_drug_resp'], $xyzzy['pain_drug_resp']); ?></td><td class='emptycell' colspan='1'></td></div>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 015--> <!-- called consumeRows 325--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain with Intercourse','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['pain_intercourse'], $xyzzy['pain_intercourse']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('How Long','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_display_field($manual_layouts['pain_intercourse_time'], $xyzzy['pain_intercourse_time']); ?></td><!-- called consumeRows 625--> <!-- Exiting not($fields)-1--></tr>
</table>


</div><!-- end show -->

</div><!-- end form_container -->

</form>
<script type="text/javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".print").click(function() { PrintForm(); });
});
</script>
</body>
</html>

