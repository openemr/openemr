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
$obj = formFetch("form_pain", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/pain/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Pain Evaluation</span><Br><br>




<input type=checkbox name="dull"  <?if ($obj{"dull"} == "on") {echo "checked";};?>><span class=text>Dull</span>
<input type=checkbox name="colicky"  <?if ($obj{"colicky"} == "on") {echo "checked";};?>><span class=text>Colicky</span>
<input type=checkbox name="sharp"  <?if ($obj{"sharp"} == "on") {echo "checked";};?>><span class=text>Sharp</span>
<span class=text>Duration of Pain: </span><input type=entry name="duration_of_pain" value="<?echo stripslashes($obj{"duration_of_pain"});?>" ><br>


<span class=text>History of Pain: </span><br><textarea cols=40 rows=8 wrap=virtual name="history_of_pain" ><?echo stripslashes($obj{"history_of_pain"});?></textarea><br>


<table><tr><td>
<table><tr>
<td><span class=text>Accompanying Symptoms Vomitting: </span></td><td><input type=entry name="accompanying_symptoms_vomitting" value="<?echo stripslashes($obj{"accompanying_symptoms_vomitting"});?>" ></td>
</tr><tr>
<td><span class=text>Accompanying Symptoms Nausea: </span></td><td><input type=entry name="accompanying_symptoms_nausea" value="<?echo stripslashes($obj{"accompanying_symptoms_nausea"});?>" ></td>
</tr><tr>
<td><span class=text>Accompanying Symptoms Headache: </span></td><td><input type=entry name="accompanying_symptoms_headache" value="<?echo stripslashes($obj{"accompanying_symptoms_headache"});?>" ></td>
</tr></table>
</td><td>
<span class=text>Accompanying Symptoms Other: </span><br><textarea cols=40 rows=8 wrap=virtual name="accompanying_symptoms_other" ><?echo stripslashes($obj{"accompanying_symptoms_other"});?></textarea><br>
</td></tr></table>

<table>
<tr><td>
<span class=text>Pain Referred to Other Sites?: </span><br><textarea cols=40 rows=4 wrap=virtual name="pain_referred_to_other_sites" ><?echo stripslashes($obj{"pain_referred_to_other_sites"});?></textarea>
</td><td>
<span class=text>What Relieves Pain?: </span><br><textarea cols=40 rows=4 wrap=virtual name="what_relieves_pain" ><?echo stripslashes($obj{"what_relieves_pain"});?></textarea>
</td></tr><tr><td>
<span class=text>What Makes Pain Worse (Movement/Positions/Activities)?: </span><br><textarea cols=40 rows=4 wrap=virtual name="what_makes_pain_worse" ><?echo stripslashes($obj{"what_makes_pain_worse"});?></textarea>
</td><td>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=4 wrap=virtual name="additional_notes" ><?echo stripslashes($obj{"additional_notes"});?></textarea>
</td></tr></table>



<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>" class="link" target=Main>[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
