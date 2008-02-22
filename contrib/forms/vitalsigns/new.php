<!-- Form generated from formsWiz -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: vitalsigns");
?>
<html><head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/vitalsigns/save.php?mode=new" name="my_form">
<span class="title">Vital Signs</span><br><br>
<span class=bold>Blood Pressure</span><br>

<table width=100%>
<tr>
<td>
<span class=text>Standing: </span><input size=3 type=entry name="standing_bp_1" value="" >
<input size=3 type=entry name="standing_bp_2" value="" >
</td>
<td>
<span class=text>Sitting: </span><input size=3 type=entry name="sitting_bp_1" value="" >
<input size=3 type=entry name="sitting_bp_2" value="" >
</td>
<td>
<span class=text>Supine: </span><input size=3 type=entry name="supine_bp_1" value="" >
<input size=3 type=entry name="supine_bp_2" value="" >
</td>
<td>
<span class=text>Systolic: </span><input size=3 type=entry name="systolic_bp_1" value="" >
<input size=3 type=entry name="systolic_bp_2" value="" >
</td>
<td>
<span class=text>Diastolic: </span><input size=3 type=entry name="diastolic_bp_1" value="" >
<input size=3 type=entry name="diastolic_bp_2" value="" >
</td>
</tr>
</table>

<table><tr>
<tr><td><span class=text>Heart Rate (Beats/Minute): </span></td><td><input size=3 type=entry name="heart_rate_beats_per_minute" value="" ></td>
<td><span class=text>Respiration (Breaths/Minute): </span></td><td><input size=3 type=entry name="respiration_beats_per_minute" value="" ></td></tr>
</table>

<table>
<tr>
<td><span class=text>Temperature: </span><input size=3 type=entry name="temperature_c" value="" ><span class=text> (C)</span></td>
<td><input size=3 type=entry name="temperature_f" value="" ><span class=text> (F)</span></td>
<td><span class=text>Temperature Method: </span><input type=entry name="temperature_method" value="" ></td>

</tr><tr>

<td><span class=text>Height (Feet): </span><input size=3 type=entry name="height_feet" value="" ></td>
<td><span class=text>Height (Inches): </span><input size=3 type=entry name="height_inches" value="" ></td>
<td><span class=text>Height (Centimeters): </span><input size=3 type=entry name="height_centimeters" value="" ></td>

</tr><tr>

<td><span class=text>Weight (Lbs): </span><input size=3 type=entry name="weight_lbs" value="" ></td>
<td><span class=text>Weight (Ozs): </span><input size=3 type=entry name="weight_ozs" value="" ></td>
<td><span class=text>Weight (Kgs): </span><input size=3 type=entry name="weight_kgs" value="" ></td>

</tr><tr>

</tr></table>

<span class=text>Body Mass Index: </span><input size=5 type=entry name="body_mass_index" value="" >
<span class=text>Figure Shape (If Overweight): </span><input type=entry name="figure_shape" value="" >

<br>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ></textarea>

<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>
</form>
<?php
formFooter();
?>
