<?
include_once("../../globals.php");
include_once("$srcdir/lists.inc");

if (!isset($active)) {
	$active="all";
}

//CLICKOPTIONS by Mark Leeds 2005, see clickoptions.txt in openemr/custom/ directory for more info
$clickoptions_category = "medical_problem";

//this code handles changing the state of activity tags when the user updates them through the interface
if (isset($mode)) {
if ($mode == "update") {
	foreach ($_POST as $var => $val) {
		if ($val == "true" || $val == "false") {
			$id = str_replace("act","",$var);
			if ($val == "true") {reappearList ($id);}elseif($val=="false"){disappearList($id);};
			//print "$id: $val > $act_state<br>\n";
			
		}
	}
} elseif ($mode == "new") {
	addList($pid,"medical_problem",$_POST["title"],$_POST["comments"],1);
}
}

?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<?php
//CLICKOPTIONS by Mark Leeds 2005, see clickoptions.txt in openemr/custom/ directory for more info
include("clickoptions1.php");
?>

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>
<td valign=top>


<a href="patient_summary.php" target="Main"><font class="title">Medical Problems</font><font class=back><?echo $tback;?></font></a>
<br>

<form border=0 method=post name=new_note action="medical_problems.php">
<input type=hidden name=mode value="new">
<input type=hidden name=active value="<?echo $active;?>">

<?php
//CLICKOPTIONS by Mark Leeds 2005, see clickoptions.txt in openemr/custom/ directory for more info
include("clickoptions2.php");
?>

<input type=entry size=15 name=title value="" onfocus="javascript:this.value=''"><br>
<textarea name=comments rows=3 cols=15 wrap=virtual onfocus="javascript:this.value=''">Comments</textarea>
<br>

<a href="javascript:document.new_note.submit();" class=link_submit>[Add New Medical Problem]</a>
</form>

<form border=0 method=post name=update_activity action="medical_problems.php">

<input type=hidden name=mode value="update">
<input type=hidden name=active value="<?echo $active;?>">

<?//change the view on the current mode, whether all, active, or inactive
$all_class="link";$active_class="link";$inactive_class="link";
if ($active=="all") {
	$all_class="link_selected";
} elseif ($active==1) {
	$active_class="link_selected";
} elseif ($active==0) {
	$inactive_class="link_selected";}

?>

<font class=text>View: </font> 
<a href="medical_problems.php?offset=0&active=all" class=<?echo $all_class;?>>[All]</a>
<a href="medical_problems.php?offset=0&active=1" class=<?echo $active_class;?>>[Only Active]</a>
<a href="medical_problems.php?offset=0&active=0" class=<?echo $inactive_class;?>>[Only Inactive]</a>

<br>
<a href="javascript:document.update_activity.submit();" class=link_submit>[Change Activity]</a>
<br><br>

<span class=text>
<?
if ($result = getListByType($pid, "medical_problem", "id,title,comments,activity,date", $active, "all", 0)){
	foreach ($result as $iter) {
		//echo "<dd>" . $iter{"title"} . "</dd>\n";
		
		
		
		if ($iter{"activity"}) {
			$checked = "checked";
		} else {
			$checked = "";
		}
		print "<input type=hidden value='' name=act".$iter{"id"}.">";
		print "<input onClick='javascript:document.update_activity.act".$iter{"id"}.".value=this.checked' type=checkbox $checked>";
		print "<font class=bold>".$iter{"title"} . "</font><font class=text>(".date("n/j/Y",strtotime($iter{"date"})).")</font><br>";
		print "<font class=text>" . $iter{"comments"} . "</font><br>\n";
	
		
	}
}

?>
</span>
<br>
</form>

</td></tr></table>

</body>
</html>
