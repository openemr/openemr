<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
?>
<html><head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_hearingtest", $_GET["id"]);
?>
<form method=post action="<?echo $rootdir?>/forms/hearingtest/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form">
<span class="title">Hearing Test</span><Br><br>
<input type=checkbox name="with_hearing_aid"  <?if ($obj{"with_hearing_aid"} == "on") {echo "checked";};?>><span class=text>With hearing Aid?</span><br>
<table>
<tr>
<td>
<span class=text>Right Ear 250: </span><input type=entry name="right_ear_250" value="<?echo $obj{"right_ear_250"};?>" ><br>
<span class=text>Right Ear 500: </span><input type=entry name="right_ear_500" value="<?echo $obj{"right_ear_500"};?>" ><br>
<span class=text>Right Ear 1000: </span><input type=entry name="right_ear_1000" value="<?echo $obj{"right_ear_1000"};?>" ><br>
<span class=text>Right Ear 2000: </span><input type=entry name="right_ear_2000" value="<?echo $obj{"right_ear_2000"};?>" ><br>
<span class=text>Right Ear 3000: </span><input type=entry name="right_ear_3000" value="<?echo $obj{"right_ear_3000"};?>" ><br>
<span class=text>Right Ear 4000: </span><input type=entry name="right_ear_4000" value="<?echo $obj{"right_ear_4000"};?>" ><br>
<span class=text>Right Ear 5000: </span><input type=entry name="right_ear_5000" value="<?echo $obj{"right_ear_5000"};?>" ><br>
<span class=text>Right Ear 6000: </span><input type=entry name="right_ear_6000" value="<?echo $obj{"right_ear_6000"};?>" ><br>
</td>
<td>
<span class=text>Left Ear 250: </span><input type=entry name="left_ear_250" value="<?echo $obj{"left_ear_250"};?>" ><br>
<span class=text>Left Ear 500: </span><input type=entry name="left_ear_500" value="<?echo $obj{"left_ear_500"};?>" ><br>
<span class=text>Left Ear 1000: </span><input type=entry name="left_ear_1000" value="<?echo $obj{"left_ear_1000"};?>" ><br>
<span class=text>Left Ear 2000: </span><input type=entry name="left_ear_2000" value="<?echo $obj{"left_ear_2000"};?>" ><br>
<span class=text>Left Ear 3000: </span><input type=entry name="left_ear_3000" value="<?echo $obj{"left_ear_3000"};?>" ><br>
<span class=text>Left Ear 4000: </span><input type=entry name="left_ear_4000" value="<?echo $obj{"left_ear_4000"};?>" ><br>
<span class=text>Left Ear 5000: </span><input type=entry name="left_ear_5000" value="<?echo $obj{"left_ear_5000"};?>" ><br>
<span class=text>Left Ear 6000: </span><input type=entry name="left_ear_6000" value="<?echo $obj{"left_ear_6000"};?>" ><br>
</td>
</tr>
</table>
<br>
<table>
<tr>
<td valign=top>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8 wrap=virtual name="additional_notes" ><?echo $obj{"additional_notes"};?></textarea><br>
</td>
</tr>
</table>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
</form>
<?php
formFooter();
?>
