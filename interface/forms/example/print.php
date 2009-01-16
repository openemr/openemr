<?php
/*
 * Sports Physical Form created by Jason Morrill: January 2009
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
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

/* load the saved record */
$record = formFetch($table_name, $_GET["id"]);

/* remove the time-of-day from the date fields */
if ($record['form_date'] != "") {
    $dateparts = split(" ", $record['form_date']);
    $record['form_date'] = $dateparts[0];
}
if ($record['dob'] != "") {
    $dateparts = split(" ", $record['dob']);
    $record['dob'] = $dateparts[0];
}
if ($record['sig_date'] != "") {
    $dateparts = split(" ", $record['sig_date']);
    $record['sig_date'] = $dateparts[0];
}
?>

<html><head>
<?php html_header_show();?>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>

<!-- page styles -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css">

</head>

<body class="body_top">

Printed on <?php echo date("F d, Y", time()); ?>

<form method=post action="">
<span class="title"><?php xl($form_name,'e'); ?></span><br>

<!-- container for the main body of the form -->
<div id="print_form_container">

<div id="print_general">
<table>
<tr><td>
Date:
   <input type='text' size='10' name='form_date' id='form_date'
    value='<?php echo stripslashes($record['form_date']);?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    />
</td></tr>
<tr><td>
Name: <input id="name" name="name" type="text" size="50" maxlength="250" value="<?php echo stripslashes($record['name']);?>">
Date of Birth:
   <input type='text' size='10' name='dob' id='dob'
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

<div id="print_bottom">
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
   <input type='text' size='10' name='sig_date' id='sig_date'
    value='<?php echo stripslashes($record['sig_date']);?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>' />
</div>
</div>

</div> <!-- end form_container -->

</form>

</body>

<script language="javascript">
window.print();
window.close();
</script>

</html>
