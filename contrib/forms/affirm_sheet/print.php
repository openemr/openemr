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
$table_name = 'form_affirm_sheet';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'Affirm Sheet';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'affirm_sheet';

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
 'affirm' => 
   array( 'field_id' => 'affirm',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'exam_yeast' => 
   array( 'field_id' => 'exam_yeast',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'proc_res_bool' ),
 'exam_gardnerrla' => 
   array( 'field_id' => 'exam_gardnerrla',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'proc_res_bool' ),
 'exam_trichomonas' => 
   array( 'field_id' => 'exam_trichomonas',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => '',
          'list_id' => 'proc_res_bool' ),
 'lot_number' => 
   array( 'field_id' => 'lot_number',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' ),
 'exp_date' => 
   array( 'field_id' => 'exp_date',
          'data_type' => '2',
          'fld_length' => '163',
          'max_length' => '255',
          'description' => '',
          'list_id' => '' )
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
<tr><td class='sectionlabel'><input type='checkbox' id='form_cb_m_1' value='1' data-section="affirm" checked="checked" />Affirm</td></tr><tr><td><div id="print_affirm" class='section'><table>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Affirm','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['affirm'], $xyzzy['affirm']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Yeast','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['exam_yeast'], $xyzzy['exam_yeast']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Gardnerrlla','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['exam_gardnerrla'], $xyzzy['exam_gardnerrla']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Trichomonas','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['exam_trichomonas'], $xyzzy['exam_trichomonas']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!--  generating 2 cells and calling --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Lot Number','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['lot_number'], $xyzzy['lot_number']); ?></td><!--  generating empties --><td class='emptycell' colspan='1'></td></tr>
<!-- called consumeRows 012--> <!-- generating not($fields[$checked+1]) and calling last --><td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Exp. Date','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_form_field($manual_layouts['exp_date'], $xyzzy['exp_date']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields) and generating 0 empty fields --></tr>
</table></div>
</td></tr> <!-- end section affirm -->
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

