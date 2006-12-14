<!-- Form generated from formsWiz -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: obstetrical");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/obstetrical/save.php?mode=new" name="my_form">
<span class="title">Obstetrical Form</span><br><br>

<table>
<tr><td><span class=text>Name: </span></td><td><input type=entry name="name" value="" ></td>
<td><span class=text>Birthdate: </span></td><td><input type=entry name='birthdate' size=10 value='YYYY-MM-DD' ></td>
<td><span class=text>Birth Status: </span></td><td><input type=entry name="birth_status" value="" ></td>
<td><span class=text>Gender: </span></td><td><input type=entry name="gender" value="" ></td>
<td><input type=checkbox name='circumcised'  ></td><td><span class=text>Circumcised</span></td></tr>
</table>


<table>
<tr><td><span class=text>Pediatrician: </span></td><td><input type=entry name="pediatrician" value="" ></td>
<td><span class=text>Birth Weight: </span></td><td><input type=entry name="birth_weight" value="" ></td></tr>
<tr><td><span class=text>Length (Inches): </span></td><td><input type=entry name="length_inches" value="" ></td>
<td><span class=text>Head Circumference (Inches): </span></td><td><input type=entry name="head_circumference_inches" value="" ></td></tr>

<tr><td><span class=text>Feeding: </span></td><td><input type=entry name="feeding" value="" ></td>
<td><span class=text>Delivery Method: </span></td><td><input type=entry name="delivery_method" value="" ></td></tr>
<tr><td><span class=text>Labor Hours: </span></td><td><input type=entry name="labor_hours" value="" ></td>
<td><span class=text>Pregnancy (Weeks): </span></td><td><input type=entry name="pregnancy_weeks" value="" ></td></tr>
<tr><td><span class=text>Anesthesia: </span></td><td colspan=3><input type=entry name="anesthesia" value="" ></td></tr>
</table>


<table>
<tr><td>
<span class=text>Reactions to Medications and Immunizations: </span><br><textarea cols=40 rows=4 wrap=virtual name="reactions_to_medications_and_immunizations" ></textarea></td>
<td><span class=text>Birth Complications: </span><br><textarea cols=40 rows=4 wrap=virtual name="birth_complications" ></textarea></td></tr>
<tr><td>
<span class=text>Developmental Problems: </span><br><textarea cols=40 rows=4 wrap=virtual name="developmental_problems" ></textarea></td>
<td><span class=text>Chronic Illness: </span><br><textarea cols=40 rows=4 wrap=virtual name="chronic_illness" ></textarea></td></tr>
<tr><td>
<span class=text>Chronic Medication: </span><br><textarea cols=40 rows=4 wrap=virtual name="chronic_medication" ></textarea></td>
<td><span class=text>Hospitalization: </span><br><textarea cols=40 rows=4 wrap=virtual name="hospitalization" ></textarea></td></tr>
<tr><td>
<span class=text>Surgery: </span><br><textarea cols=40 rows=4 wrap=virtual name="surgery" ></textarea></td>
<td><span class=text>Injury: </span><br><textarea cols=40 rows=4 wrap=virtual name="injury" ></textarea></td></tr>
<tr><td>
<span class=text>Day Care: </span><br><textarea cols=40 rows=4 wrap=virtual name="day_care" ></textarea></td>
<td><span class=text>Additional Notes: </span><br><textarea cols=40 rows=4 wrap=virtual name="additional_notes" ></textarea></td></tr>
</table>
<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link">[Don't Save]</a>
</form>
<?php
formFooter();
?>
