<?php
/*
 * The page shown when the user requests a new form
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package   OpenEMR
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @link      http://www.open-emr.org
 */

include_once("../../globals.php");
include_once("$srcdir/api.inc");

/** CHANGE THIS name to the name of your form **/
$form_name = "My Example Form";

/** CHANGE THIS to match the folder you created for this form **/
$form_folder = "example";

formHeader("Form: ".$forn_name);

$returnurl = 'encounter_top.php';
?>

<html><head>
<?php html_header_show();?>

<!-- other supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<!-- page styles -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css?v=<?php echo $v_js_includes; ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

<script language="JavaScript">
// this line is to assist the calendar text boxes
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">

<?php echo date("F d, Y", time()); ?>

<form method=post action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/save.php?mode=new" name="my_form">
<span class="title"><?php xl($form_name, 'e'); ?></span><br>

<!-- Save/Cancel buttons -->
<input type="button" class="save" value="<?php xl('Save', 'e'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php xl('Don\'t Save', 'e'); ?>"> &nbsp;

<!-- container for the main body of the form -->
<div id="form_container">

<div id="general">
<table>
<tr><td>
Date:
   <input type='text' size='10' class='datepicker' name='form_date' id='form_date'
    value='<?php echo date('Y-m-d', time()); ?>'
    title='<?php xl('yyyy-mm-dd', 'e'); ?>' />
</td></tr>
<tr><td>
Name: <input id="name" name="name" type="text" size="50" maxlength="250">
Date of Birth:
   <input type='text' size='10' class='datepicker' name='dob' id='dob'
    value='<?php echo $date ?>'
    title='<?php xl('yyyy-mm-dd Date of Birth', 'e'); ?>' />
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
Use this space to express notes <br>
<textarea name="notes" id="notes" cols="80" rows="4"></textarea>
<br><br>
<div style="text-align:right;">
Signature?
<input type="radio" id="sig" name="sig" value="y">Yes
/
<input type="radio" id="sig" name="sig" value="n">No
&nbsp;&nbsp;
Date of signature:
   <input type='text' size='10' class='datepicker' name='sig_date' id='sig_date'
    value='<?php echo date('Y-m-d', time()); ?>'
    title='<?php xl('yyyy-mm-dd', 'e'); ?>' />
</div>
</div>

</div> <!-- end form_container -->

<!-- Save/Cancel buttons -->
<input type="button" class="save" value="<?php xl('Save', 'e'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php xl('Don\'t Save', 'e'); ?>"> &nbsp;
</form>

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
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

