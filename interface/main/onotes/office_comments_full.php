<?
include_once("../../globals.php");
include_once("$srcdir/onotes.inc");

//the number of records to display per screen
$N = 10;


if (!isset($offset)) {
	$offset=0;
}

if (!isset($active)) {
	$active="all";
}

//this code handles changing the state of activity tags when the user updates them through the interface
if (isset($mode)) {
if ($mode == "update") {
	foreach ($_POST as $var => $val) {
		if ($val == "true" || $val == "false") {
			$id = str_replace("act","",$var);
			if ($val == "true") {reappearOnote ($id);}elseif($val=="false"){disappearOnote($id);};
			//print "$id: $val > $act_state<br>\n";
			
		}
	}
} elseif ($mode == "new") {
	addOnote($_POST["note"]);
}
}
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<form border=0 method=post name=new_note action="office_comments_full.php">

<?
if ($userauthorized) {
$backurl="../main.php";
} else {
$backurl="../main_info.php";
}
?>


<a href="<?echo $backurl;?>" target="Main"><font class="title">Office Notes</font><font class=back><?echo $tback;?></font></a>
<br>
<input type=hidden name=mode value="new">
<input type=hidden name=offset value="<?echo $offset;?>">
<input type=hidden name=active value="<?echo $active;?>">

<textarea name=note rows=6 cols=40 wrap=virtual></textarea>
<br>
<a href="javascript:document.new_note.submit();" class=link_submit>[Add New Note]</a>
</form>



<form border=0 method=post name=update_activity action="office_comments_full.php">

<?//change the view on the current mode, whether all, active, or inactive
$all_class="link";$active_class="link";$inactive_class="link";
if ($active=="all") {
	$all_class="link_selected";
} elseif ($active==1) {
	$active_class="link_selected";
} elseif ($active==0) {
	$inactive_class="link_selected";
}


?>

<font class=text>View: </font> 
<a href="office_comments_full.php?offset=0&active=all" class=<?echo $all_class;?>>[All]</a>
<a href="office_comments_full.php?offset=0&active=1" class=<?echo $active_class;?>>[Only Active]</a>
<a href="office_comments_full.php?offset=0&active=0" class=<?echo $inactive_class;?>>[Only Inactive]</a>





<input type=hidden name=mode value="update">
<input type=hidden name=offset value="<?echo $offset;?>">
<input type=hidden name=active value="<?echo $active;?>">
<table border=0>
<tr><td colspan=3 align=left><a href="javascript:document.update_activity.submit();" class=link_submit>[Change Activity]</a></td></tr>
<?
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N

//retrieve all notes
if ($result = getOnoteByDate("", $active, "id,date,body,user,activity",$N,$offset)) {
$result_count = 0;
foreach ($result as $iter) {
	$result_count++;
	
	if (getdate() == strtotime($iter{"date"})) {
		$date_string = "Today, " . date( "D F dS" ,strtotime($iter{"date"}));
	} else {
		$date_string = date( "D F dS" ,strtotime($iter{"date"}));
	}
	
	if ($iter{"activity"}) {
		$checked = "checked";
	} else {
		$checked = "";
	}
	print "<tr><td><input type=hidden value='' name=act".$iter{"id"}.">";
	print "<input onClick='javascript:document.update_activity.act".$iter{"id"}.".value=this.checked' type=checkbox $checked></td>";
	print "<td><font class=bold>".$date_string . "</font>";
	print " <font class=bold>(". $iter{"user"}.")</font></td>";
	print "<td>" . "<font class=text>" . stripslashes($iter{"body"}) . "</font></td></tr>\n";
	
	
	$notes_count++;
}
}else{
//no results
print "<tr><td></td><td></td><td></td></tr>\n";
}

?>
<tr><td colspan=3 align=left><a href="javascript:document.update_activity.submit();" class=link_submit>[Change Activity]</a></td></tr>
</table>
</form>
<hr>
<table width=400 border=0 cellpadding=0 cellspacing=0>
<tr><td>
<?
if ($offset>($N-1)) {
echo "<a class=link href=office_comments_full.php?active=".$active."&offset=".($offset-$N).">[Previous]</a>";
}
?>
</td><td align=right>
<?
if ($result_count == $N) {
echo "<a class=link href=office_comments_full.php?active=".$active."&offset=".($offset+$N).">[Next]</a>";
}
?>
</td></tr>
</table>

<br><br><br>

<?//<a href="../main.php" target=Main class=link_submit>Back</a>?>

</body>
</html>
