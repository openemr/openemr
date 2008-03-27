<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_neurologicalreview", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/neurologicalreview/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form">
<span class="title">Neurological Review</span><Br><br>
<table>
<tr>
<td valign=top>
<table>
<tr><td><span class=text>Burning: </span></td><td><input type=entry name="burning" value="<?php echo $obj{"burning"};?>" ></td></tr>
<tr><td><span class=text>Confusion: </span></td><td><input type=entry name="confusion" value="<?php echo $obj{"confusion"};?>" ></td></tr>
<tr><td><span class=text>Dizziness: </span></td><td><input type=entry name="dizziness" value="<?php echo $obj{"dizziness"};?>" ></td></tr>
<tr><td><span class=text>Dysphasia: </span></td><td><input type=entry name="dysphasia" value="<?php echo $obj{"dysphasia"};?>" ></td></tr>
<tr><td><span class=text>Facial tic: </span></td><td><input type=entry name="facial_tic" value="<?php echo $obj{"facial_tic"};?>" ></td></tr>
<tr><td><span class=text>Focal weakness: </span></td><td><input type=entry name="focal_weakness" value="<?php echo $obj{"focal_weakness"};?>" ></td></tr>
<tr><td><span class=text>Forgetfulness: </span></td><td><input type=entry name="forgetfulness" value="<?php echo $obj{"forgetfulness"};?>" ></td></tr>
</table>
</td>
<td valign=top>
<table>
<tr><td><span class=text>Headache: </span></td><td><input type=entry name="headache" value="<?php echo $obj{"headache"};?>" ></td></tr>
<tr><td><span class=text>Hyperesthesia: </span></td><td><input type=entry name="hyperesthesia" value="<?php echo $obj{"hyperesthesia"};?>" ></td></tr>
<tr><td><span class=text>Lightheadedness: </span></td><td><input type=entry name="lightheadedness" value="<?php echo $obj{"lightheadedness"};?>" ></td></tr>
<tr><td><span class=text>Numbness: </span></td><td><input type=entry name="numbness" value="<?php echo $obj{"numbness"};?>" ></td></tr>
<tr><td><span class=text>Paralysis: </span></td><td><input type=entry name="paralysis" value="<?php echo $obj{"paralysis"};?>" ></td></tr>
<tr><td><span class=text>Paresthesia: </span></td><td><input type=entry name="paresthesia" value="<?php echo $obj{"paresthesia"};?>" ></td></tr>
</table>
</td>
</td>
</table>

<table>
<tr>
<td>
<span class=text>Symptoms of Problems: </span><br><textarea cols=40 rows=8 name="symptoms_of_problems" wrap=virtual><?php echo $obj{"symptoms_of_problems"};?></textarea>
</td><td>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 name="additional_notes"  wrap=virtual><?php echo $obj{"additional_notes"};?></textarea><br>
</td></tr></table>
<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
