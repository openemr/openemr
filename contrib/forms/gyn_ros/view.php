<?php
/*
 * The page shown when the user requests to see this form. Allows the user to edit form contents, and save. has a button for printing the saved form contents.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for generate_form_field, ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');
/* note that we cannot include options_listadd.inc here, as it generates code before the <html> tag */

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

if ($thisauth != 'write' && $thisauth != 'addonly')
  die($form_name.': Adding is not authorized.');
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
$submiturl = $GLOBALS['rootdir'].'/forms/'.$form_folder.'/save.php?mode=update&amp;return=encounter&amp;id='.$_GET['id'];
if ($_GET['mode']) {
 if ($_GET['mode']=='noencounter') {
 $submiturl = $GLOBALS['rootdir'].'/forms/'.$form_folder.'/save.php?mode=new&amp;return=show&amp;id='.$_GET['id'];
 $returnurl = 'show.php';
 }
}
else
{
 $returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
}

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
<!-- For jquery, required by the save, discard, and print buttons. -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>

<!-- Global Stylesheet -->
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css"/>
<!-- Form Specific Stylesheet. -->
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css"/>

<!-- supporting code for the pop up calendar(date picker) -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>


<script type="text/javascript">
// this line is to assist the calendar text boxes
var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';

<!-- support code for collapsing sections -->
function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

<!-- FIXME: this needs to detect access method, and construct a URL appropriately! -->
function PrintForm() {
    newwin = window.open("<?php echo $rootdir.'/forms/'.$form_folder.'/print.php?id='.$_GET['id']; ?>","print_<?php echo $form_name; ?>");
}

</script>
<title><?php echo htmlspecialchars('View '.$form_name); ?></title>

</head>
<body class="body_top">

<div id="title">
<a href="<?php echo $returnurl; ?>" onclick="top.restoreSession()">
<span class="title"><?php htmlspecialchars(xl($form_name,'e')); ?></span>
<span class="back">(<?php xl('Back','e'); ?>)</span>
</a>
</div>

<form method="post" action="<?php echo $submiturl; ?>" id="<?php echo $form_folder; ?>"> 

<!-- Save/Cancel buttons -->
<div id="top_buttons" class="top_buttons">
<fieldset class="top_buttons">
<input type="button" class="save" value="<?php xl('Save Changes','e'); ?>" />
<input type="button" class="dontsave" value="<?php xl('Don\'t Save Changes','e'); ?>" />
<input type="button" class="print" value="<?php xl('Print','e'); ?>" />
</fieldset>
</div><!-- end top_buttons -->

<!-- container for the main body of the form -->
<div id="form_container">
<fieldset>

<!-- display the form's manual based fields -->
<table border='0' cellpadding='0' width='100%'>
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_1' value='1' onclick='return divclick(this,"cardio")' checked="checked" />Cardio - Respiratory System</td></tr><tr><td><div id="cardio" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cardio_reviewed'], $xyzzy['cardio_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cardio_note'], $xyzzy['cardio_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section cardio -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_2' value='1' onclick='return divclick(this,"gastro")' checked="checked" />Gastro - Intestinal System</td></tr><tr><td><div id="gastro" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['gastro_reviewed'], $xyzzy['gastro_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['gastro_note'], $xyzzy['gastro_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section gastro -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_3' value='1' onclick='return divclick(this,"urinary")' checked="checked" />Urinary System</td></tr><tr><td><div id="urinary" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['Urinary_reviewed'], $xyzzy['Urinary_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['Urinary_note'], $xyzzy['Urinary_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section urinary -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_4' value='1' onclick='return divclick(this,"cns")' checked="checked" />Central Nervous System</td></tr><tr><td><div id="cns" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cns_reviewed'], $xyzzy['cns_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cns_note'], $xyzzy['cns_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section cns -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_5' value='1' onclick='return divclick(this,"othersys")' checked="checked" />Other Systems</td></tr><tr><td><div id="othersys" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['other_reviewed'], $xyzzy['other_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['other_note'], $xyzzy['other_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section othersys -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_6' value='1' onclick='return divclick(this,"complications")' checked="checked" />Complications</td></tr><tr><td><div id="complications" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['complications_reviewed'], $xyzzy['complications_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['complications_note'], $xyzzy['complications_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section complications -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_7' value='1' onclick='return divclick(this,"menstrual")' checked="checked" />Menstrual History</td></tr><tr><td><div id="menstrual" class='section'><table>
<!-- called consumeRows 015--> <!--  generating 5 cells and calling --><td>
<span class="fieldlabel"><?php xl('LMP Start Date','e'); ?> (yyyy-mm-dd): </span>
</td><td>
   <input type='text' size='10' name='lmpdate' id='lmpdate' title='When was the the first day of your last menstrual period?'
    value="<?php $result=chkdata_Date($xyzzy,'lmpdate'); echo $result; ?>"
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../../pic/show_calendar.gif' width='24' height='22'
    id='img_lmpdate' alt='[?]' style='cursor:pointer'
    title="<?php xl('Click here to choose a date','e'); ?>" />
<script type="text/javascript">
Calendar.setup({inputField:'lmpdate', ifFormat:'%Y-%m-%d', button:'img_lmpdate'});
</script>
</td>
<!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Cycle Interval','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cycle_int'], $xyzzy['cycle_int']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['cycle_int_note'], $xyzzy['cycle_int_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Flow - FH count','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['flowfhcount'], $xyzzy['flowfhcount']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Flow - Hrs between changes','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['flowhrs'], $xyzzy['flowhrs']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Post Menapausal Bleeding','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['pmb'], $xyzzy['pmb']); ?></td><!-- called consumeRows 215--> <!-- Exiting not($fields) and generating 3 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section menstrual -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_8' value='1' onclick='return divclick(this,"infection")' checked="checked" />Vaginal Infection</td></tr><tr><td><div id="infection" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Discharge','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_discharge'], $xyzzy['vag_discharge']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_discharge_note'], $xyzzy['vag_discharge_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Itching','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_itching'], $xyzzy['vag_itching']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_itching_note'], $xyzzy['vag_itching_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Odor','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_odor'], $xyzzy['vag_odor']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_odor_note'], $xyzzy['vag_odor_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Irritation','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_irratation'], $xyzzy['vag_irratation']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_irratation_note'], $xyzzy['vag_irratation_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Spotting','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_spotting'], $xyzzy['vag_spotting']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_spotting_note'], $xyzzy['vag_spotting_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Prior Treatment','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['priortreatment'], $xyzzy['priortreatment']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['priortreatment_note'], $xyzzy['priortreatment_note']); ?></td><!-- called consumeRows 525--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section infection -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_9' value='1' onclick='return divclick(this,"pelvic pain")' checked="checked" />Pelvic Pain</td></tr><tr><td><div id="pelvic pain" class='section'><table>
<!-- called consumeRows 015--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Menses Pain','e').':'; ?></td><td class='text data' colspan='4'><?php echo generate_form_field($manual_layouts['pain_menses'], $xyzzy['pain_menses']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 325--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Level','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_level'], $xyzzy['pain_level']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Location','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['pain_location'], $xyzzy['pain_location']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 325--> <!--  generating 7 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Length of Time','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_lenth'], $xyzzy['pain_lenth']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain OTC/NSAIDS Response','e').':'; ?></td><td class='text data' colspan='3'><?php echo generate_form_field($manual_layouts['pain_drug_resp'], $xyzzy['pain_drug_resp']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 325--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain with Intercourse','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_intercourse'], $xyzzy['pain_intercourse']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('How Long','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_intercourse_time'], $xyzzy['pain_intercourse_time']); ?></td><!-- called consumeRows 625--> <!-- Exiting not($fields) and generating -1 empty fields --></tr>
</table></div>
</td></tr> <!-- end section pelvic pain -->
</table>

</fieldset>
</div> <!-- end form_container -->

<!-- Save/Cancel buttons -->
<div id="bottom_buttons" class="button_bar">
<fieldset>
<input type="button" class="save" value="<?php xl('Save Changes','e'); ?>" />
<input type="button" class="dontsave" value="<?php xl('Don\'t Save Changes','e'); ?>" />
</fieldset>
</div><!-- end bottom_buttons -->
</form>
<script type="text/javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); document.forms["<?php echo $form_folder; ?>"].submit(); });
    $(".dontsave").click(function() { location.href='<?php echo $returnurl; ?>'; });
});
</script>
</body>
</html>

