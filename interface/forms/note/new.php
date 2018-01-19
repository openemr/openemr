<?php
/** Work/School Note Form created by Nikolai Vitsyn: 2004/02/13 and update 2005/03/30
 *    Copyright (C) Open Source Medical Software
 *
 *    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    as published by the Free Software Foundation; either version 2
 *    of the License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. -->
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: note");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "note";
?>

<html><head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<script language="JavaScript">
// required for textbox date verification
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">
<?php echo date("F d, Y", time()); ?>

<form method=post action="<?php echo $rootdir."/forms/".$form_name."/save.php?mode=new";?>" name="my_form" id="my_form">
<span class="title"><?php echo xlt('Work/School Note'); ?></span><br></br>

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

<select name="note_type">
<option value="WORK NOTE"><?php echo xlt('WORK NOTE'); ?></option>
<option value="SCHOOL NOTE"><?php echo xlt('SCHOOL NOTE'); ?></option>
</select>
<br>
<b><?php echo xlt('MESSAGE:'); ?></b>
<br>
<textarea name="message" id="message" rows="7" cols="47"></textarea>
<br>

<?php
// commented out below private field, because no field in database, and causes error.
?>
<!--
<input type="checkbox" name="private" id="private"><label for="private">This note is private</label>
<br>
-->

<br>
<b><?php echo xlt('Signature:'); ?></b>
<br>

<table>
<tr><td>
<?php echo xlt('Doctor:'); ?>
<input type="text" name="doctor" id="doctor" value="<?php echo attr($provider_results["fname"]).' '.attr($provider_results["lname"]); ?>">
</td>

<td>
<span class="text"><?php echo xlt('Date'); ?></span>
   <input type='text' size='10' class='datepicker' name='date_of_signature' id='date_of_signature'
    value='<?php echo date('Y-m-d', time()); ?>'
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

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
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
