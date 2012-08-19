<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<?php html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_contacts", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/contacts/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form">
<span class="title">Contacts</span><Br><br>
<table>
<tr>
<td valign=top>
<table>
<tr><td><span class=text>OD Base Curve: </span></td><td><input size=4 type=entry name="od_base_curve" value="<?php echo $obj{"od_base_curve"};?>" ></td></tr>
<tr><td><span class=text>OD Sphere: </span></td><td><input size=4 type=entry name="od_sphere" value="<?php echo $obj{"od_sphere"};?>" ></td></tr>
<tr><td><span class=text>OD Cylinder: </span></td><td><input size=4 type=entry name="od_cylinder" value="<?php echo $obj{"od_cylinder"};?>" ></td></tr>
<tr><td><span class=text>OD Axis: </span></td><td><input size=4 type=entry name="od_axis" value="<?php echo $obj{"od_axis"};?>" ></td></tr>
<tr><td><span class=text>OD Diameter: </span></td><td><input size=4 type=entry name="od_diameter" value="<?php echo $obj{"od_diameter"};?>" ></td></tr>
</table>
</td>
<td valign=top>
<table>
<tr><td><span class=text>OS Base Curve: </span></td><td><input size=4 type=entry name="os_base_curve" value="<?php echo $obj{"os_base_curve"};?>" ></td></tr>
<tr><td><span class=text>OS Sphere: </span></td><td><input size=4 type=entry name="os_sphere" value="<?php echo $obj{"os_sphere"};?>" ></td></tr>
<tr><td><span class=text>OS Cylinder: </span></td><td><input size=4 type=entry name="os_cylinder" value="<?php echo $obj{"os_cylinder"};?>" ></td></tr>
<tr><td><span class=text>OS Axis: </span></td><td><input size=4 type=entry name="os_axis" value="<?php echo $obj{"os_axis"};?>" ></td></tr>
<tr><td><span class=text>OS Diameter: </span></td><td><input size=4 type=entry name="os_diameter" value="<?php echo $obj{"os_diameter"};?>" ></td></tr>
</table>
</td>
</tr>
</table>

<table>
<tr>
<td><span class=text>Material: </span></td><td><input size=4 type=entry name="material" value="<?php echo $obj{"material"};?>" ></td>
<td><span class=text>Color: </span></td><td><input size=4 type=entry name="color" value="<?php echo $obj{"color"};?>" ></td>
<td><span class=text>Bifocal Type: </span></td><td><input size=4 type=entry name="bifocal_type" value="<?php echo $obj{"bifocal_type"};?>" ></td>
<td><span class=text>Add: </span></td><td><input size=4 type=entry name="add_value" value="<?php echo $obj{"add_value"};?>" ></td>
<td><span class=text>V/A Far: </span></td><td><input size=4 type=entry name="va_far" value="<?php echo $obj{"va_far"};?>" ></td>
<td><span class=text>V/A Near: </span></td><td><input size=4 type=entry name="va_near" value="<?php echo $obj{"va_near"};?>" ></td>
</tr>
</table>

<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ><?php echo $obj{"additional_notes"};?></textarea><br>
</td>
</tr>
</table>

<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
