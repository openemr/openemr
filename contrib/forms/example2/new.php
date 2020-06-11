<?php

/**
 * The page shown when the user requests a new form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

/** CHANGE THIS name to the name of your form **/
$form_name = "My Example Form";

/** CHANGE THIS to match the folder you created for this form **/
$form_folder = "example";

formHeader("Form: " . $form_name);

$returnurl = 'encounter_top.php';
?>

<html><head>

<!-- assets -->
<?php Header::setupHeader('datetime-picker'); ?>
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css?v=<?php echo $v_js_includes; ?>">

</head>

<body class="body_top">

<?php echo date("F d, Y", time()); ?>

<form method=post action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/save.php?mode=new" name="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class="title"><?php echo xlt($form_name); ?></span><br />

<!-- Save/Cancel buttons -->
<input type="button" class="save" value="<?php echo xla('Save'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;

<!-- container for the main body of the form -->
<div id="form_container">

<div id="general">
<table>
<tr><td>
Date:
   <input type='text' size='10' class='datepicker' name='form_date' id='form_date'
    value='<?php echo date('Y-m-d', time()); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
</td></tr>
<tr><td>
Name: <input id="name" name="name" type="text" size="50" maxlength="250">
Date of Birth:
   <input type='text' size='10' class='datepicker' name='dob' id='dob'
    value='<?php echo attr($date); ?>'
    title='<?php echo xla('yyyy-mm-dd Date of Birth'); ?>' />
</td></tr>
<tr><td>
Phone: <input name="phone" id="phone" type="text" size="15" maxlength="15">
</td></tr>
<tr><td>
Address: <input name="address" id="address" type="text" size="80" maxlength="250">
</td></tr>
</table>
</div>

<div id="bottom">
Use this space to express notes <br />
<textarea name="notes" id="notes" cols="80" rows="4"></textarea>
<br /><br />
<div style="text-align:right;">
Signature?
<input type="radio" id="sig" name="sig" value="y">Yes
/
<input type="radio" id="sig" name="sig" value="n">No
&nbsp;&nbsp;
Date of signature:
   <input type='text' size='10' class='datepicker' name='sig_date' id='sig_date'
    value='<?php echo date('Y-m-d', time()); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
</div>
</div>

</div> <!-- end form_container -->

<!-- Save/Cancel buttons -->
<input type="button" class="save" value="<?php echo xla('Save'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</form>

</body>

<script>

// jQuery stuff to make the page a little easier to use

$(function () {
    $(".save").click(function() { top.restoreSession(); document.my_form.submit(); });
    $(".dontsave").click(function() { parent.closeTab(window.name, false); });

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
