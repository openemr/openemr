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
$form_user = $_REQUEST['form_user'];

$res = sqlStatement("select distinct LEFT(date,10) as date from log order by date desc limit 30");
for($iter=0;$row=sqlFetchArray($res);$iter++) {
  $ret[$iter] = $row;
}

// Get the users list.
$ures = sqlStatement("SELECT username, fname, lname FROM users " .
  "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
  "ORDER BY lname, fname");
?>

<br>
<FORM METHOD="GET" name=the_form>
<span class=text><?php  xl('Date','e'); ?>: </span>
<SELECT NAME="date">
<?php
$getdate = $_GET["date"] ? $_GET["date"] : date("Y-m-d");

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

&nbsp;&nbsp;<span class='text'><?php  xl('User','e'); ?>: </span>

<?php
echo "<select name='form_user'>\n";
echo " <option value=''>" . xl('All') . "</option>\n";
while ($urow = sqlFetchArray($ures)) {
  if (!trim($urow['username'])) continue;
  echo " <option value='" . $urow['username'] . "'";
  if ($urow['username'] == $form_user) echo " selected";
  echo ">" . $urow['lname'];
  if ($urow['fname']) echo ", " . $urow['fname'];
  echo "</option>\n";
}
echo "</select>\n";

$res = sqlStatement("select distinct event from log order by event ASC");

$found = 0;
echo "&nbsp;<span class=text>";
for($iter=0;$row=sqlFetchArray($res);$iter++) {
?>
<input type="radio" name="event" value="<?php echo $row["event"];?>"
<?php
  if ($row["event"] == $_GET["event"]) {
    echo " checked";
    $found++;
  }
?>
><?echo $row["event"]?>&nbsp;&nbsp;
<?php
  $ret[$iter] = $row;
}
?>
<input type="radio" name="event" value="*"<?php if (!$found) echo " checked";?>><?php xl('All','e'); ?>
</span>&nbsp;
<a href="javascript:document.the_form.submit();" class=link_submit>[<?php  xl('Refresh','e'); ?>]</a>
</FORM>
<?php
if ($_GET["date"]) {
?>
<TABLE BORDER=1 CELLPADDING=4>
 <tr>
  <TD><span class=bold><?php  xl('Date'); ?></span></TD>
  <TD><span class=bold><?php  xl('Event','e'); ?></span></TD>
  <TD><span class=bold><?php  xl('User','e'); ?></span></TD>
  <TD><span class=bold><?php  xl('Group','e'); ?></span></TD>
  <TD><span class=bold><?php  xl('Comments','e'); ?></span></TD>
 </tr>
<?php
  if ($ret = getEventByDate($getdate, $form_user)) {
    if (!$_GET["event"])
      $gev = "*";
    else
      $gev = $_GET["event"];
    foreach ($ret as $iter) {
      if (($gev == "*") || ($gev == $iter["event"])) {
?>
 <TR>
  <TD nowrap><span class=text><?php echo $iter["date"]?></span></TD>
  <TD><span class=text><?php echo $iter["event"]?></span></TD>
  <TD><span class=text><?php echo $iter["user"]?></span></TD>
  <TD><span class=text><?php echo $iter["groupname"]?></span></TD>
  <TD><span class=text><?php echo $iter["comments"]?></span></TD>
 </TR>

<?php
      }
    }
  }
?>
</table>
<?php
}
?>
<br><br>

</body>
</html>
