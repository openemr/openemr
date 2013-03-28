<?php

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: note");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$provider_results = sqlQuery("select fname, lname from users where username=?",array($_SESSION{"authUser"}));

/* name of this form */
$form_name = "note"; 

// get the record from the database
if ($_GET['id'] != "") $obj = formFetch("form_".$form_name, $_GET["id"]);
/* remove the time-of-day from the date fields */
if ($obj['date_of_signature'] != "") {
    $dateparts = split(" ", $obj['date_of_signature']);
    $obj['date_of_signature'] = $dateparts[0];
}
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

</head>
<body class="body_top">

<form method=post action="">
<span class="title"><?php echo xlt('Work/School Note'); ?></span><br></br>
<?php echo xlt('Printed'); ?> <?php echo dateformat(); ?>
<br><br>
<select name="note_type">
<option value="WORK NOTE" <?php if ($obj['note_type']=="WORK NOTE") echo " SELECTED"; ?>><?php echo xlt('WORK NOTE'); ?></option>
<option value="SCHOOL NOTE" <?php if ($obj['note_type']=="SCHOOL NOTE") echo " SELECTED"; ?>><?php echo xlt('SCHOOL NOTE'); ?></option>
</select>
<br>
<b><?php echo xlt('MESSAGE:'); ?></b>
<br>
<div style="border: 1px solid black; padding: 5px; margin: 5px;"><?php echo text($obj["message"]);?></div>
<br></br>

<table>
<tr><td>
<span class=text><?php echo xlt('Doctor:'); ?> </span><input type=text name="doctor" value="<?php echo attr($obj["doctor"]);?>">
</td><td>
<span class="text"><?php echo xlt('Date'); ?></span>
   <input type='text' size='10' name='date_of_signature' id='date_of_signature'
    value='<?php echo attr($obj['date_of_signature']); ?>'
    />
</td></tr>
</table>

</form>

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    window.print();
    window.close();
});

</script>

</html>

