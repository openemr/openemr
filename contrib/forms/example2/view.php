<?php
/*
 * Sports Physical Form
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
 * @author    Jason Morrill
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @link      http://www.open-emr.org
 */


include_once("../../globals.php");
include_once("$srcdir/api.inc");

/** CHANGE THIS - name of the database table associated with this form **/
$table_name = "form_example";

/** CHANGE THIS name to the name of your form **/
$form_name = "My Example Form";

/** CHANGE THIS to match the folder you created for this form **/
$form_folder = "example";

formHeader("Form: ".$form_name);
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
<?php html_header_show();?>

<!-- supporting javascript code -->
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

function PrintForm() {
    newwin = window.open("<?php echo "http://".$_SERVER['SERVER_NAME'].$rootdir."/forms/".$form_folder."/print.php?id=".$_GET["id"]; ?>","mywin");
}
</script>

</head>

<body class="body_top">

<?php echo date("F d, Y", time()); ?>

<form method=post action="<?php echo $rootdir;?>/forms/<?php echo $form_folder; ?>/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form">
<span class="title"><?php xl($form_name,'e'); ?></span><br>

<!-- Save/Cancel links -->
<input type="button" class="save" value="<?php xl('Save Changes','e'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php xl('Don\'t Save Changes','e'); ?>"> &nbsp;
<input type="button" class="printform" value="<?php xl('Print','e'); ?>"> &nbsp;

<!-- container for the main body of the form -->
<div id="form_container">

<div id="general">
<table>
<tr><td>
Date:
   <input type='text' size='10' class='datepicker' name='form_date' id='form_date'
    value='<?php echo stripslashes($record['form_date']);?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>' />
</td></tr>
<tr><td>
Name: <input id="name" name="name" type="text" size="50" maxlength="250" value="<?php echo stripslashes($record['name']);?>">
Date of Birth:
   <input type='text' size='10' class='datepicker' name='dob' id='dob'
    value='<?php echo stripslashes($record['dob']);?>'
    title='<?php xl('yyyy-mm-dd Date of Birth','e'); ?>'
    />
</td></tr>
<tr><td>
Phone: <input name="phone" id="phone" type="text" size="15" maxlength="15" value="<?php echo stripslashes($record['phone']);?>">
</td></tr>
<tr><td>
Address: <input name="address" id="address" type="text" size="80" maxlength="250" value="<?php echo stripslashes($record['address']);?>">
</td></tr>
</table>
</div>

<div id="bottom">
Use this space to express notes <br>
<textarea name="notes" id="notes" cols="80" rows="4"><?php echo stripslashes($record['notes']);?></textarea>
<br><br>
<div style="text-align:right;">
Signature?
<input type="radio" id="sig" name="sig" value="y" <?php if ($record["sig"] == 'y') echo "CHECKED"; ?>>Yes
/
<input type="radio" id="sig" name="sig" value="n" <?php if ($record["sig"] == 'n') echo "CHECKED"; ?>>No
&nbsp;&nbsp;
Date of signature:
   <input type='text' size='10' class='datepicker' name='sig_date' id='sig_date'
    value='<?php echo stripslashes($record['sig_date']);?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>' />
</div>
</div>

</div> <!-- end form_container -->

<input type="button" class="save" value="<?php xl('Save Changes','e'); ?>"> &nbsp;
<input type="button" class="dontsave" value="<?php xl('Don\'t Save Changes','e'); ?>"> &nbsp;
<input type="button" class="printform" value="<?php xl('Print','e'); ?>"> &nbsp;

</form>

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); document.my_form.submit(); });
    $(".dontsave").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
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
