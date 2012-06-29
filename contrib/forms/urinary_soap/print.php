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
$table_name = 'form_urinary_soap';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'Urinary Symptoms';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'urinary_soap';

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
 'urinary_complaints' => 
   array( 'field_id' => 'urinary_complaints',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Urinary_Complaints' ),
 'previous_cultures' => 
   array( 'field_id' => 'previous_cultures',
          'data_type' => '2',
          'fld_length' => '151',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'other' => 
   array( 'field_id' => 'other',
          'data_type' => '3',
          'fld_length' => '151',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' ),
 'duration' => 
   array( 'field_id' => 'duration',
          'data_type' => '2',
          'fld_length' => '140',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'exam' => 
   array( 'field_id' => 'exam',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Urinary_Exam' ),
 'diagnosis' => 
   array( 'field_id' => 'diagnosis',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Urinary_Diagnosis' ),
 'plan' => 
   array( 'field_id' => 'plan',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Urinary_Plans' )
 );

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';


/* define check field functions. used for translating from fields to html viewable strings */

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
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_1' value='1' data-section="subjective" checked="checked" />Subjective</td></tr><tr><td><div id="print_subjective" class='section'><table>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Urinary Complaints','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['urinary_complaints'], $xyzzy['urinary_complaints']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Previous Cultures','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['previous_cultures'], $xyzzy['previous_cultures']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Other','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['other'], $xyzzy['other']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Duration','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['duration'], $xyzzy['duration']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section subjective -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_2' value='1' data-section="objective" checked="checked" />Objective</td></tr><tr><td><div id="print_objective" class='section'><table>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Exam','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['exam'], $xyzzy['exam']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section objective -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_3' value='1' data-section="assessment" checked="checked" />Assessment</td></tr><tr><td><div id="print_assessment" class='section'><table>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Diagnosis','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['diagnosis'], $xyzzy['diagnosis']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section assessment -->
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_4' value='1' data-section="plan" checked="checked" />Plan</td></tr><tr><td><div id="print_plan" class='section'><table>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Plan','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['plan'], $xyzzy['plan']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section plan -->
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

