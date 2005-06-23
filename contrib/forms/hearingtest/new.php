<!-- Form generated from formsWiz  -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: hearingtest");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/hearingtest/save.php?mode=new" name="my_form">
<span class="title">Hearing Test</span><br><br>
<input type=checkbox name='with_hearing_aid'  ><span class=text>With hearing Aid?</span><br>
<table>
<tr>
<td>
<span class=text>Right Ear 250: </span><input type=entry name="right_ear_250" value="" ><br>
<span class=text>Right Ear 500: </span><input type=entry name="right_ear_500" value="" ><br>
<span class=text>Right Ear 1000: </span><input type=entry name="right_ear_1000" value="" ><br>
<span class=text>Right Ear 2000: </span><input type=entry name="right_ear_2000" value="" ><br>
<span class=text>Right Ear 3000: </span><input type=entry name="right_ear_3000" value="" ><br>
<span class=text>Right Ear 4000: </span><input type=entry name="right_ear_4000" value="" ><br>
<span class=text>Right Ear 5000: </span><input type=entry name="right_ear_5000" value="" ><br>
<span class=text>Right Ear 6000: </span><input type=entry name="right_ear_6000" value="" ><br>
</td>
<td>
<span class=text>Left Ear 250: </span><input type=entry name="left_ear_250" value="" ><br>
<span class=text>Left Ear 500: </span><input type=entry name="left_ear_500" value="" ><br>
<span class=text>Left Ear 1000: </span><input type=entry name="left_ear_1000" value="" ><br>
<span class=text>Left Ear 2000: </span><input type=entry name="left_ear_2000" value="" ><br>
<span class=text>Left Ear 3000: </span><input type=entry name="left_ear_3000" value="" ><br>
<span class=text>Left Ear 4000: </span><input type=entry name="left_ear_4000" value="" ><br>
<span class=text>Left Ear 5000: </span><input type=entry name="left_ear_5000" value="" ><br>
<span class=text>Left Ear 6000: </span><input type=entry name="left_ear_6000" value="" ><br>
</td>
</tr>
</table>
<br>
<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 name="additional_notes" ></textarea>
</td>
</tr>
</table>
<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link">[Don't Save]</a>
</form>
<?php
formFooter();
?>
