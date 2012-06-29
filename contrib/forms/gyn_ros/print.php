<?php
/*
 * The page shown when the user requests to print this form. This page automatically prints itsself, and closes its parent browser window.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for generate_form_field, ?? */
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

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

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
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>

<!-- Global Stylesheet -->
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css"/>
<!-- Form Specific Stylesheet. -->
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css"/>
<title><?php echo htmlspecialchars('Print '.$form_name); ?></title>

</head>
<body class="body_top">

<div class="print_date"><?php xl('Printed on ','e'); echo date('F d, Y', time()); ?></div>

<form method="post" id="<?php echo $form_folder; ?>" action="">
<div class="title"><?php xl($form_name,'e'); ?></div>

<!-- container for the main body of the form -->
<div id="print_form_container">
<fieldset>

<!-- display the form's manual based fields -->
<table border='0' cellpadding='0' width='100%'>
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_1' value='1' onclick='return divclick(this,"cardio")' checked="checked" />Cardio - Respiratory System</td></tr><tr><td><div id="print_cardio" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cardio_reviewed'], $xyzzy['cardio_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cardio_note'], $xyzzy['cardio_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section cardio -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_2' value='1' onclick='return divclick(this,"gastro")' checked="checked" />Gastro - Intestinal System</td></tr><tr><td><div id="print_gastro" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['gastro_reviewed'], $xyzzy['gastro_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['gastro_note'], $xyzzy['gastro_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section gastro -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_3' value='1' onclick='return divclick(this,"urinary")' checked="checked" />Urinary System</td></tr><tr><td><div id="print_urinary" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['Urinary_reviewed'], $xyzzy['Urinary_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['Urinary_note'], $xyzzy['Urinary_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section urinary -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_4' value='1' onclick='return divclick(this,"cns")' checked="checked" />Central Nervous System</td></tr><tr><td><div id="print_cns" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cns_reviewed'], $xyzzy['cns_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cns_note'], $xyzzy['cns_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section cns -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_5' value='1' onclick='return divclick(this,"othersys")' checked="checked" />Other Systems</td></tr><tr><td><div id="print_othersys" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['other_reviewed'], $xyzzy['other_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['other_note'], $xyzzy['other_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section othersys -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_6' value='1' onclick='return divclick(this,"complications")' checked="checked" />Complications</td></tr><tr><td><div id="print_complications" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Reviewed','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['complications_reviewed'], $xyzzy['complications_reviewed']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['complications_note'], $xyzzy['complications_note']); ?></td><!-- called consumeRows 425--> <!-- Exiting not($fields) and generating 1 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section complications -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_7' value='1' onclick='return divclick(this,"menstrual")' checked="checked" />Menstrual History</td></tr><tr><td><div id="print_menstrual" class='section'><table>
<!-- called consumeRows 015--> <!--  generating 5 cells and calling --><td>
<span class="fieldlabel"><?php xl('LMP Start Date','e'); ?>: </span>
</td><td>
   <input type='text' size='10' name='lmpdate' id='lmpdate' title='When was the the first day of your last menstrual period?'
    value="<?php $result=chkdata_Date($xyzzy,'lmpdate'); echo $result; ?>"
    />
</td>
<!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Cycle Interval','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['cycle_int'], $xyzzy['cycle_int']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['cycle_int_note'], $xyzzy['cycle_int_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Flow - FH count','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['flowfhcount'], $xyzzy['flowfhcount']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Flow - Hrs between changes','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['flowhrs'], $xyzzy['flowhrs']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Post Menapausal Bleeding','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['pmb'], $xyzzy['pmb']); ?></td><!-- called consumeRows 215--> <!-- Exiting not($fields) and generating 3 empty fields --><td class='emptycell' colspan='1'></td></tr>
</table></div>
</td></tr> <!-- end section menstrual -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_8' value='1' onclick='return divclick(this,"infection")' checked="checked" />Vaginal Infection</td></tr><tr><td><div id="print_infection" class='section'><table>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Discharge','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_discharge'], $xyzzy['vag_discharge']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_discharge_note'], $xyzzy['vag_discharge_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Itching','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_itching'], $xyzzy['vag_itching']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_itching_note'], $xyzzy['vag_itching_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Odor','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_odor'], $xyzzy['vag_odor']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_odor_note'], $xyzzy['vag_odor_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Irritation','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_irratation'], $xyzzy['vag_irratation']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_irratation_note'], $xyzzy['vag_irratation_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Spotting','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['vag_spotting'], $xyzzy['vag_spotting']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['vag_spotting_note'], $xyzzy['vag_spotting_note']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 225--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Prior Treatment','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['priortreatment'], $xyzzy['priortreatment']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Note','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['priortreatment_note'], $xyzzy['priortreatment_note']); ?></td><!-- called consumeRows 525--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section infection -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_9' value='1' onclick='return divclick(this,"pelvic pain")' checked="checked" />Pelvic Pain</td></tr><tr><td><div id="print_pelvic pain" class='section'><table>
<!-- called consumeRows 015--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Menses Pain','e').':'; ?></td><td class='text data' colspan='4'><?php echo generate_form_field($manual_layouts['pain_menses'], $xyzzy['pain_menses']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 325--> <!--  generating 5 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Level','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_level'], $xyzzy['pain_level']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Location','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['pain_location'], $xyzzy['pain_location']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 325--> <!--  generating 7 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain Length of Time','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_lenth'], $xyzzy['pain_lenth']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain OTC/NSAIDS Response','e').':'; ?></td><td class='text data' colspan='3'><?php echo generate_form_field($manual_layouts['pain_drug_resp'], $xyzzy['pain_drug_resp']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 015--> <!-- just calling --><!-- called consumeRows 325--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Pain with Intercourse','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_intercourse'], $xyzzy['pain_intercourse']); ?></td><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('How Long','e').':'; ?></td><td class='text data' colspan='2'><?php echo generate_form_field($manual_layouts['pain_intercourse_time'], $xyzzy['pain_intercourse_time']); ?></td><!-- called consumeRows 625--> <!-- Exiting not($fields) and generating -1 empty fields --></tr>
</table></div>
</td></tr> <!-- end section pelvic pain -->
</table>


</fieldset>
</div><!-- end print_form_container -->

</form>
<script type="text/javascript">
window.print();
window.close();
</script>
</body>
</html>

