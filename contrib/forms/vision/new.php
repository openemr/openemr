<!-- Form generated from formsWiz -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: vision");
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/vision/save.php?mode=new" name="my_form">
<span class="title">Vision</span><br><br>
<span class=bold>Keratometry</span><br>

<table>
<tr>
<td><span class=text>OD K1: </span></td><td><input size=3 type=entry name="od_k1" value="" ></td>
<td><span class=text>OD K1 Axis: </span></td><td><input size=3 type=entry name="od_k1_axis" value="" ></td>
<td><span class=text>OD K2: </span></td><td><input size=3 type=entry name="od_k2" value="" ></td>
<td><span class=text>OD K2 Axis: </span></td><td><input size=3 type=entry name="od_k2_axis" value="" ></td>
</tr>
<tr>
<td colspan=8>

<span class=text>OD Testing Status: </span><input type=entry name="od_testing_status" value="" >
</td>
</tr>
</table>

<table>
<tr>
<td><span class=text>OS K1: </span></td><td><input size=3 type=entry name="os_k1" value="" ></td>
<td><span class=text>OS K1 Axis: </span></td><td><input size=3 type=entry name="os_k1_axis" value="" ></td>
<td><span class=text>OS K2: </span></td><td><input size=3 type=entry name="os_k2" value="" ></td>
<td><span class=text>OS K2 Axis: </span></td><td><input size=3 type=entry name="os_k2_axis" value="" ></td>
</tr>
<tr>
<td colspan=8>

<span class=text>OS Testing Status: </span><input type=entry name="os_testing_status" value="" >
</td>
</tr>
</table>

<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ></textarea>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>
</form>
<?php
formFooter();
?>
