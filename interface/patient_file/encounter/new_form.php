<?
include_once("../../globals.php");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<!-- outdated
<dl>
<dt><span class="title">New Form</span></dt>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=physical";?>" class="link" target=Main>Physical Examination</a></dd>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=newpatient";?>" class="link" target=Main>New Patient Form</a></dd>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=sencounter";?>" class="link" target=Main>Small Patient Encounter</a></dd>
<dd><a href="<?echo "$rootdir/patient_file/encounter/load_form.php?formname=reviewofs";?>" class="link" target=Main>Review Of Systems</a></dd>
</dl>
-->
<dl>
<dt><span class="title">New Form</span></dt>
<?php //DYNAMIC FORM RETREIVAL
include_once("$srcdir/registry.inc");
$reg = getRegistered ();
if ($reg != false)
foreach ($reg as $entry) {
	echo '<dd><a href="'.$rootdir.'/patient_file/encounter/load_form.php?formname='.urlencode($entry['directory']).'" class="link" target=Main>';
	echo ($entry['name'] == 'Fee Sheet' && $GLOBALS['phone_country_code'] != '1') ? 'Coding Sheet' : $entry['name'];
	echo '</a></dd>';
}
?>
</dl>

</body>
</html>
