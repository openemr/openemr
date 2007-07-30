<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_vitalsigns", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/vitalsigns/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Vital Signs</span><Br><br>
<span class=bold>Blood Pressure</span><br>

<table width=100%>
<tr><td>
<span class=text>Standing: </span><input size=3 type=entry name="standing_bp_1" value="<?echo $obj{"standing_bp_1"};?>" >
<input size=3 type=entry name="standing_bp_2" value="<?echo $obj{"standing_bp_2"};?>" >
</td><td>
<span class=text>Sitting: </span><input size=3 type=entry name="sitting_bp_1" value="<?echo $obj{"sitting_bp_1"};?>" >
<input size=3 type=entry name="sitting_bp_2" value="<?echo $obj{"sitting_bp_2"};?>" >
</td><td>
<span class=text>Supine: </span><input size=3 type=entry name="supine_bp_1" value="<?echo $obj{"supine_bp_1"};?>" >
<input size=3 type=entry name="supine_bp_2" value="<?echo $obj{"supine_bp_2"};?>" >
</td><td>
<span class=text>Systolic: </span><input size=3 type=entry name="systolic_bp_1" value="<?echo $obj{"systolic_bp_1"};?>" >
<input size=3 type=entry name="systolic_bp_2" value="<?echo $obj{"systolic_bp_2"};?>" >
</td><td>
<span class=text>Diastolic: </span><input size=3 type=entry name="diastolic_bp_1" value="<?echo $obj{"diastolic_bp_1"};?>" >
<input size=3 type=entry name="diastolic_bp_2" value="<?echo $obj{"diastolic_bp_2"};?>" >
</tr>
</table>

<table><tr><td>
<span class=text>Heart Rate (Beats/Minute): </span></td><td><input size=3 type=entry name="heart_rate_beats_per_minute" value="<?echo $obj{"heart_rate_beats_per_minute"};?>" ></td>
<td><span class=text>Respiration (Breaths/Minute): </span></td><td><input size=3 type=entry name="respiration_beats_per_minute" value="<?echo $obj{"respiration_beats_per_minute"};?>" ></td></tr>
</table>

<table>
<tr>
<td><span class=text>Temperature (C): </span><input size=3 type=entry name="temperature_c" value="<?echo $obj{"temperature_c"};?>" ></td>
<td><span class=text>Temperature (F): </span><input size=3 type=entry name="temperature_f" value="<?echo $obj{"temperature_f"};?>" ></td>
<td><span class=text>Temperature Method: </span><input type=entry name="temperature_method" value="<?echo $obj{"temperature_method"};?>" ></td>
</tr><tr>
<td><span class=text>Height (Feet): </span><input size=3 type=entry name="height_feet" value="<?echo $obj{"height_feet"};?>" ></td>
<td><span class=text>Height (Inches): </span><input size=3 type=entry name="height_inches" value="<?echo $obj{"height_inches"};?>" ></td>
<td><span class=text>Height (Centimeters): </span><input size=3 type=entry name="height_centimeters" value="<?echo $obj{"height_centimeters"};?>" ></td>
</tr><tr>
<td><span class=text>Weight (Lbs): </span><input size=3 type=entry name="weight_lbs" value="<?echo $obj{"weight_lbs"};?>" ></td>
<td><span class=text>Weight (Ozs): </span><input size=3 type=entry name="weight_ozs" value="<?echo $obj{"weight_ozs"};?>" ></td>
<td><span class=text>Weight (Kgs): </span><input size=3 type=entry name="weight_kgs" value="<?echo $obj{"weight_kgs"};?>" ></td>
</tr>
</table>


<span class=text>Body Mass Index: </span><input size=5 type=entry name="body_mass_index" value="<?echo $obj{"body_mass_index"};?>" >
<span class=text>Figure Shape (If Overweight): </span><input type=entry name="figure_shape" value="<?echo $obj{"figure_shape"};?>" >
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ><?echo $obj{"additional_notes"};?></textarea>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
