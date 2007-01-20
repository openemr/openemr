<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_dictation", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/dictation/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title"><?php xl('Speech Dictation','e'); ?></span><Br><br>
<span class=text><?php xl('Dictation: ','e'); ?></span><br><textarea cols=80 rows=24 wrap=virtual name="dictation" ><?echo stripslashes($obj{"dictation"});?></textarea><br>
<span class=text><?php xl('Additional Notes: ','e'); ?></span><br><textarea cols=80 rows=8 wrap=virtual name="additional_notes" ><?echo stripslashes($obj{"additional_notes"});?></textarea><br>
<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/$returnurl";?>" class="link">[<?php xl('Don\'t Save Changes','e'); ?>]</a>
</form>
<?php
formFooter();
?>
