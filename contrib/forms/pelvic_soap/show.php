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
$table_name = 'form_pelvic_soap';

/** CHANGE THIS name to the name of your form. **/
$form_name = 'POP';

/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = 'pelvic_soap';

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
 'pelvic_complaints' => 
   array( 'field_id' => 'pelvic_complaints',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Complaints' ),
 'pelvic_exam' => 
   array( 'field_id' => 'pelvic_exam',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Exam' ),
 'pelvic_assessment' => 
   array( 'field_id' => 'pelvic_assessment',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Assessment' ),
 'pelvic_plan' => 
   array( 'field_id' => 'pelvic_plan',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => '',
          'list_id' => 'Pelvic_Plan' ),
 'plan_discussion' => 
   array( 'field_id' => 'plan_discussion',
          'data_type' => '3',
          'fld_length' => '151',
          'max_length' => '4',
          'description' => '',
          'list_id' => '' )
 );

/* since we have no-where to return, abuse returnurl to link to the 'edit' page */
/* FIXME: pass the ID, create blank rows if necissary. */
$returnurl = "../../forms/$form_folder/view.php?mode=noencounter";


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
<tr><td class='sectionlabel'>Subjective</td><!-- called consumeRows 012--> <!-- called consumeRows 212--> <!-- Exiting not($fields)0--></tr>
<tr><td class='sectionlabel'>Objective</td><!-- called consumeRows 012--> <!-- called consumeRows 212--> <!-- Exiting not($fields)0--></tr>
<tr><td class='sectionlabel'>Assessment</td><!-- called consumeRows 012--> <!-- called consumeRows 212--> <!-- Exiting not($fields)0--></tr>
<tr><td class='sectionlabel'>Plan</td><!-- called consumeRows 012--> </tr>
<tr><td valign='top'>&nbsp;</td><!-- called consumeRows 012--> <td class='fieldlabel' colspan='1'><?php echo xl_layout_label('Discussion','e').':'; ?></td><td class='text data' colspan='1'><?php echo generate_display_field($manual_layouts['plan_discussion'], $xyzzy['plan_discussion']); ?></td><!-- called consumeRows 212--> <!-- Exiting not($fields)0--></tr>
</table>


</div><!-- end show -->

</div><!-- end form_container -->

<!-- Print button -->
<div id="button_bar" class="button_bar">
<fieldset class="button_bar">
<input type="button" class="print" value="<?php xl('Print','e'); ?>" />
</fieldset>
</div><!-- end button_bar -->

</form>
<script type="text/javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".print").click(function() { PrintForm(); });
});
</script>
</body>
</html>

