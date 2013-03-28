<!-- Forms generated from formsWiz -->
<?php

$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once("../../globals.php");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_dictation", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/dictation/save.php?mode=update&id=<?php echo attr($_GET["id"]);?>" name="my_form">
<span class="title"><?php echo xlt('Speech Dictation'); ?></span><Br><br>
<span class=text><?php echo xlt('Dictation: '); ?></span><br><textarea cols=80 rows=24 wrap=virtual name="dictation" ><?php echo text($obj{"dictation"});?></textarea><br>
<span class=text><?php echo xlt('Additional Notes: '); ?></span><br><textarea cols=80 rows=8 wrap=virtual name="additional_notes" ><?php echo text($obj{"additional_notes"});?></textarea><br>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php echo xlt('Save'); ?>]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link"
 onclick="top.restoreSession()">[<?php echo xlt('Don\'t Save Changes'); ?>]</a>
</form>
<?php
formFooter();
?>
