<!-- Form generated from formsWiz -->
<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader("Form: neurologicalreview");
?>
<html><head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?echo $rootdir;?>/forms/neurologicalreview/save.php?mode=new" name="my_form">
<span class="title">Neurological Review</span><br><br>
<table>
<tr>
<td valign=top>
<table>
<tr><td><span class=text>Burning: </span></td><td><input type=entry name="burning" value="" ></td></tr>
<tr><td><span class=text>Confusion: </span></td><td><input type=entry name="confusion" value="" ></td></tr>
<tr><td><span class=text>Dizziness: </span></td><td><input type=entry name="dizziness" value="" ></td></tr>
<tr><td><span class=text>Dysphasia: </span></td><td><input type=entry name="dysphasia" value="" ></td></tr>
<tr><td><span class=text>Facial tic: </span></td><td><input type=entry name="facial_tic" value="" ></td></tr>
<tr><td><span class=text>Focal weakness: </span></td><td><input type=entry name="focal_weakness" value="" ></td></tr>
<tr><td><span class=text>Forgetfulness: </span></td><td><input type=entry name="forgetfulness" value="" ></td></tr>
</table>
</td><td valign=top>
<table>
<tr><td><span class=text>Headache: </span></td><td><input type=entry name="headache" value="" ></td></tr>
<tr><td><span class=text>Hyperesthesia: </span></td><td><input type=entry name="hyperesthesia" value="" ></td></tr>
<tr><td><span class=text>Lightheadedness: </span></td><td><input type=entry name="lightheadedness" value="" ></td></tr>
<tr><td><span class=text>Numbness: </span></td><td><input type=entry name="numbness" value="" ></td></tr>
<tr><td><span class=text>Paralysis: </span></td><td><input type=entry name="paralysis" value="" ></td></tr>
<tr><td><span class=text>Paresthesia: </span></td><td><input type=entry name="paresthesia" value="" ></td></tr>
</table>
</td>
</tr>
</table>

<table>
<tr><td>
<span class=text>Symptoms of Problems: </span><br><textarea cols=40 rows=8 wrap=virtual name="symptoms_of_problems" ></textarea>
</td><td>
<span class=text>Additional Notes: </span><br><textarea cols=40 rows=8  wrap=virtual name="additional_notes" ></textarea>
</td>
</tr>
</table>

<br>
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link"
 onclick="top.restoreSession()">[Don't Save]</a>
</form>
<?php
formFooter();
?>
