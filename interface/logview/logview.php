<?php
include_once("../globals.php");
include_once("$srcdir/log.inc");

?>

<html>
<head>


<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">


</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<font class=title><?php  xl('Logs Viewer','e'); ?></font>
<br>

<?php

$res = sqlStatement("select distinct LEFT(date,10) as date from log order by date desc limit 30");

for($iter=0;$row=sqlFetchArray($res);$iter++) {
	$ret[$iter] = $row;
}

?>
<br>
<FORM METHOD="GET" name=the_form>
<span class=text><?php  xl('Date','e'); ?>: </span>
<SELECT NAME="date">
<?php

if ($_GET["date"])
	$getdate = $_GET["date"];
else
	$getdate = date("Y-m-d");
	

$found = 0;
foreach ($ret as $iter) {
	echo "<option value='{$iter["date"]}'";
	if (!$found && substr($iter["date"],0,10) == $getdate) {
		echo " selected";
		$found++;
	}
	echo ">{$iter["date"]}</option>\n";
}
if (!$found) {
	echo "<option value='$getdate'";
	echo " selected";
	echo ">$getdate</option>\n";
}

?>
</SELECT>

<?php
$res = sqlStatement("select distinct event from log order by event ASC");

$found = 0;
echo "&nbsp;<span class=text>";
for($iter=0;$row=sqlFetchArray($res);$iter++) {
?>
<input type="radio" name="event" value="<?php echo $row["event"];?>"<?php 
if ($row["event"] == $_GET["event"]) {
	echo " checked";
	$found++;
}
?>><?echo $row["event"]?>&nbsp;&nbsp;
<?php
	$ret[$iter] = $row;
}

?>
<input type="radio" name="event" value="*"<?php if (!$found) echo " checked";?>><?php xl('All','e'); ?>
</span>&nbsp;
<a href="javascript:document.the_form.submit();" class=link_submit>[<?php  xl('Refresh','e'); ?>]</a>
</FORM>
<TABLE BORDER=1 CELLPADDING=4>
<TR><TD><span class=bold><?php  xl('Date'); ?></span></TD><TD><span class=bold><?php  xl('Event','e'); ?></span></TD><TD><span class=bold><?php  xl('User','e'); ?></span></TD><TD><span class=bold><?php  xl('Group','e'); ?></span></TD><TD><span class=bold><?php  xl('Comments','e'); ?></span></TD>
<?php

if ($ret = getEventByDate($getdate) ) {

/*echo "<pre>";
print_r($ret);
echo "</pre>";
*/
if (!$_GET["event"])
	$gev = "*";
else
	$gev = $_GET["event"];
foreach ($ret as $iter) {
	if (($gev == "*") || ($gev == $iter["event"])) {
?>
<TR><TD><span class=text><?php echo $iter["date"]?></span></TD><TD><span class=text><?php echo $iter["event"]?></span></TD><TD><span class=text><?php echo $iter["user"]?></span></TD><TD><span class=text><?php echo $iter["groupname"]?></span></TD><TD><span class=text><?php echo $iter["comments"]?></span></TD></TR>

<?php
	}
}
}
?>
</table>
<br><br>

</body>
</html>
