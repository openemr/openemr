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
$obj = formFetch("form_neurologicalreview", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/neurologicalreview/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Neurological Review</span><Br><br>
<table>
<tr>
<td valign=top>
<table>
<tr><td><span class=text>Burning: </span></td><td><input type=entry name="burning" value="<?echo $obj{"burning"};?>" ></td></tr>
<tr><td><span class=text>Confusion: </span></td><td><input type=entry name="confusion" value="<?echo $obj{"confusion"};?>" ></td></tr>
<tr><td><span class=text>Dizziness: </span></td><td><input type=entry name="dizziness" value="<?echo $obj{"dizziness"};?>" ></td></tr>
<tr><td><span class=text>Dysphasia: </span></td><td><input type=entry name="dysphasia" value="<?echo $obj{"dysphasia"};?>" ></td></tr>
<tr><td><span class=text>Facial tic: </span></td><td><input type=entry name="facial_tic" value="<?echo $obj{"facial_tic"};?>" ></td></tr>
<tr><td><span class=text>Focal weakness: </span></td><td><input type=entry name="focal_weakness" value="<?echo $obj{"focal_weakness"};?>" ></td></tr>
<tr><td><span class=text>Forgetfulness: </span></td><td><input type=entry name="forgetfulness" value="<?echo $obj{"forgetfulness"};?>" ></td></tr>
</table>
</td>
<td valign=top>
<table>
<tr><td><span class=text>Headache: </span></td><td><input type=entry name="headache" value="<?echo $obj{"headache"};?>" ></td></tr>
<tr><td><span class=text>Hyperesthesia: </span></td><td><input type=entry name="hyperesthesia" value="<?echo $obj{"hyperesthesia"};?>" ></td></tr>
<tr><td><span class=text>Lightheadedness: </span></td><td><input type=entry name="lightheadedness" value="<?echo $obj{"lightheadedness"};?>" ></td></tr>
<tr><td><span class=text>Numbness: </span></td><td><input type=entry name="numbness" value="<?echo $obj{"numbness"};?>" ></td></tr>
<tr><td><span class=text>Paralysis: </span></td><td><input type=entry name="paralysis" value="<?echo $obj{"paralysis"};?>" ></td></tr>
<tr><td><span class=text>Paresthesia: </span></td><td><input type=entry name="paresthesia" value="<?echo $obj{"paresthesia"};?>" ></td></tr>
</table>
</td>
</td>
</table>

<table>
<tr>
<td>
<span class=text>Symptoms of Problems: </span><br><textarea cols=40 rows=8 name="symptoms_of_problems" wrap=virtual><?echo $obj{"symptoms_of_problems"};?></textarea>
</td><td>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 name="additional_notes"  wrap=virtual><?echo $obj{"additional_notes"};?></textarea><br>
</td></tr></table>
<br>
<a href="javascript:document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
