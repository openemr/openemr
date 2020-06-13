<?php

/*
 * Work/School Note Form new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) Open Source Medical Software
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

formHeader("Form: note");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION["authUser"]));
/* name of this form */
$form_name = "note";
?>

<html><head>

<?php Header::setupHeader('datetime-picker'); ?>

<script>
// required for textbox date verification
var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;
</script>

</head>

<body class="body_top">
<?php echo text(date("F d, Y", time())); ?>

<form method=post action="<?php echo $rootdir . "/forms/" . $form_name . "/save.php?mode=new";?>" name="my_form" id="my_form">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class="title"><?php echo xlt('Work/School Note'); ?></span><br /><br />

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

<select name="note_type">
<option value="WORK NOTE"><?php echo xlt('WORK NOTE'); ?></option>
<option value="SCHOOL NOTE"><?php echo xlt('SCHOOL NOTE'); ?></option>
</select>
<br />
<b><?php echo xlt('MESSAGE:'); ?></b>
<br />
<textarea name="message" id="message" rows="7" cols="47"></textarea>
<br />

<?php
// commented out below private field, because no field in database, and causes error.
?>
<!--
<input type="checkbox" name="private" id="private"><label for="private">This note is private</label>
<br />
-->

<br />
<b><?php echo xlt('Signature:'); ?></b>
<br />

<table>
<tr><td>
<?php echo xlt('Doctor:'); ?>
<input type="text" name="doctor" id="doctor" value="<?php echo attr($provider_results["fname"]) . ' ' . attr($provider_results["lname"]); ?>">
</td>

<td>
<span class="text"><?php echo xlt('Date'); ?></span>
   <input type='text' size='10' class='datepicker' name='date_of_signature' id='date_of_signature'
    value='<?php echo attr(date('Y-m-d', time())); ?>'
    title='<?php echo xla('yyyy-mm-dd'); ?>' />
</td>
</tr>
</table>

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

</form>

</body>

<script>

// jQuery stuff to make the page a little easier to use

$(function () {
    $(".save").click(function() { top.restoreSession(); $('#my_form').submit(); });
    $(".dontsave").click(function() { parent.closeTab(window.name, false); });
    //$("#printform").click(function() { PrintForm(); });

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
