<?php

/**
 * Sports Physical Form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jason Morrill
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = "form_example";

/** CHANGE THIS name to the name of your form **/
$form_name = "My Example Form";

/** CHANGE THIS to match the folder you created for this form **/
$form_folder = "example";

formHeader("Form: " . $form_name);
$returnurl = 'encounter_top.php';

/* load the saved record */
$record = formFetch($table_name, $_GET["id"]);

/* remove the time-of-day from the date fields */
if ($record['form_date'] != "") {
    $dateparts = explode(" ", $record['form_date']);
    $record['form_date'] = $dateparts[0];
}

if ($record['dob'] != "") {
    $dateparts = explode(" ", $record['dob']);
    $record['dob'] = $dateparts[0];
}

if ($record['sig_date'] != "") {
    $dateparts = explode(" ", $record['sig_date']);
    $record['sig_date'] = $dateparts[0];
}
?>

<html><head>

<?php Header::setupHeader('datetime-picker'); ?>

<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css?v=<?php echo $v_js_includes; ?>">

<script>
function PrintForm() {
    newwin = window.open("<?php echo "http://" . $_SERVER['SERVER_NAME'] . $rootdir . "/forms/" . $form_folder . "/print.php?id=" ?>" + <?php echo js_url($_GET["id"]); ?>,"mywin");
}
</script>

</head>

<body class="body_top">

<?php echo date("F d, Y", time()); ?>

<form method=post action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/save.php?mode=update&id=<?php echo attr_url($_GET["id"]);?>" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class="title"><?php echo xlt($form_name); ?></span><br />

<!-- Save/Cancel links -->
<input type="button" class="save" value="<?php echo xla('Save Changes'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save Changes'); ?>"> &nbsp;
<input type="button" class="printform" value="<?php echo xla('Print'); ?>"> &nbsp;

<!-- container for the main body of the form -->
<div id="form_container">

<div id="general">
<table>
<tr><td>
Date:
   <input type='text' size='10' class='datepicker' name='form_date' id='form_date'
    value='<?php echo attr($record['form_date']);?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
</td></tr>
<tr><td>
Name: <input id="name" name="name" type="text" size="50" maxlength="250" value="<?php echo attr($record['name']);?>">
Date of Birth:
   <input type='text' size='10' class='datepicker' name='dob' id='dob'
    value='<?php echo attr($record['dob']);?>'
    title='<?php echo xla('yyyy-mm-dd Date of Birth'); ?>'
    />
</td></tr>
<tr><td>
Phone: <input name="phone" id="phone" type="text" size="15" maxlength="15" value="<?php echo attr($record['phone']);?>">
</td></tr>
<tr><td>
Address: <input name="address" id="address" type="text" size="80" maxlength="250" value="<?php echo attr($record['address']);?>">
</td></tr>
</table>
</div>

<div id="bottom">
Use this space to express notes <br />
<textarea name="notes" id="notes" cols="80" rows="4"><?php echo attr($record['notes']);?></textarea>
<br /><br />
<div style="text-align:right;">
Signature?
<input type="radio" id="sig" name="sig" value="y" <?php if ($record["sig"] == 'y') {
    echo "CHECKED";
                                                  } ?>>Yes
/
<input type="radio" id="sig" name="sig" value="n" <?php if ($record["sig"] == 'n') {
    echo "CHECKED";
                                                  } ?>>No
&nbsp;&nbsp;
Date of signature:
   <input type='text' size='10' class='datepicker' name='sig_date' id='sig_date'
    value='<?php echo attr($record['sig_date']);?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
</div>
</div>

</div> <!-- end form_container -->

<input type="button" class="save" value="<?php echo xla('Save Changes'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save Changes'); ?>"> &nbsp;
<input type="button" class="printform" value="<?php echo xla('Print'); ?>"> &nbsp;

</form>

</body>

<script>
// jQuery stuff to make the page a little easier to use

$(function () {
    $(".save").click(function() { top.restoreSession(); document.my_form.submit(); });
    $(".dontsave").click(function() { parent.closeTab(window.name, false); });
    $(".printform").click(function() { PrintForm(); });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

</html>
