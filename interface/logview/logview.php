<?php
include_once("../globals.php");
include_once("$srcdir/log.inc");
?>
<html>
<head>
<?php html_header_show();?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style>
#logview_header {
    width: 100%;
}
#logview_header table {
    width:100%;
    border-collapse: collapse;
}
#logview_header tr {
    width: 20%;
    background-color: #ccc;
    cursor: pointer; cursor: hand;
}
#logview_data {
    width: 100%;
    height: 50%;
    overflow: auto;
}
#logview_data table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
}
#logview_data td {
    width: 20%;
    border-bottom: 1px solid #eee;
    cursor: default;
}
.highlight {
    background-color: #336699;
    color: #fff;
}
</style>

</head>
<body class="body_top">
<font class="title"><?php  xl('Logs Viewer','e'); ?></font>
<br>

<?php
$form_user = $_REQUEST['form_user'];

$res = sqlStatement("select distinct LEFT(date,10) as date from log order by date desc limit 30");
for($iter=0;$row=sqlFetchArray($res);$iter++) {
  $ret[$iter] = $row;
}

// Get the users list.
$sqlQuery = "SELECT username, fname, lname FROM users " .
  "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) ";

$ures = sqlStatement($sqlQuery);
?>

<br>
<FORM METHOD="GET" name="theform" id="theform">
<input type="hidden" name="sortby" id="sortby" value="<?php echo $_GET['sortby']; ?>">
<span class="text"><?php  xl('Date','e'); ?>: </span>
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
echo "<br> <span class='text'>";
for($iter=0;$row=sqlFetchArray($res);$iter++) {
?>
<input type="radio" name="event" value="<?php echo $row["event"];?>"
<?php
  if ($row["event"] == $_GET["event"]) {
    echo " checked";
    $found++;
  }
?>
><?php echo xl($row["event"])?>&nbsp;&nbsp;
<?php
  $ret[$iter] = $row;
}
?>
<input type="radio" name="event" value="*"<?php if (!$found) echo " checked";?>><?php xl('All','e'); ?>
</span>&nbsp;
<a href="javascript:document.theform.submit();" class='link_submit'>[<?php  xl('Refresh','e'); ?>]</a>
</FORM>

<?php if ($_GET["date"]) { ?>
<div id="logview_header">
<TABLE>
 <tr>
  <!-- <TH><?php  xl('Date', 'e'); ?><TD> -->
  <th id="sortby_date" class="text" title="<?php xl('Sort by date/time','e'); ?>"><?php xl('Date','e'); ?></th>
  <TH id="sortby_event" class="text" title="<?php xl('Sort by Event','e'); ?>"><?php  xl('Event','e'); ?></TD>
  <TH id="sortby_user" class="text" title="<?php xl('Sort by User','e'); ?>"><?php  xl('User','e'); ?></TD>
  <TH id="sortby_group" class="text" title="<?php xl('Sort by Group','e'); ?>"><?php  xl('Group','e'); ?></TD>
  <TH id="sortby_comments" class="text" title="<?php xl('Sort by Comments','e'); ?>"><?php  xl('Comments','e'); ?></TD>
 </tr>
</table>
</div>

<div id="logview_data">
<table>
<?php
  if ($ret = getEvents(array('date' => $getdate, 'user' => $form_user, 'sortby' => $_GET['sortby']))) {
    if (!$_GET["event"]) $gev = "*";
    else $gev = $_GET["event"];
    foreach ($ret as $iter) {
      if (($gev == "*") || ($gev == $iter["event"])) {
        //translate comments
        $patterns = array ('/^success/','/^failure/','/ encounter/');
	$replace = array ( xl('success'), xl('failure'), xl('encounter','',' '));
	$trans_comments = preg_replace($patterns, $replace, $iter["comments"]);
?>
 <TR class="oneresult">
  <TD class="text"><?php echo $iter["date"]?></TD>
  <TD class="text"><?php echo xl($iter["event"])?></TD>
  <TD class="text"><?php echo $iter["user"]?></TD>
  <TD class="text"><?php echo $iter["groupname"]?></TD>
  <TD class="text"><?php echo $trans_comments?></TD>
 </TR>

<?php
      }
    }
  }
?>
</table>
</div>

<?php } ?>

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use
$(document).ready(function(){
    // funny thing here... good learning experience
    // the TR has TD children which have their own background and text color
    // toggling the TR color doesn't change the TD color
    // so we need to change all the TR's children (the TD's) just as we did the TR
    // thus we have two calls to toggleClass:
    // 1 - for the parent (the TR)
    // 2 - for each of the children (the TDs)
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); $(this).children().toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); $(this).children().toggleClass("highlight"); });

    // click-able column headers to sort the list
    $("#sortby_date").click(function() { $("#sortby").val("date"); $("#theform").submit(); });
    $("#sortby_event").click(function() { $("#sortby").val("event"); $("#theform").submit(); });
    $("#sortby_user").click(function() { $("#sortby").val("user"); $("#theform").submit(); });
    $("#sortby_group").click(function() { $("#sortby").val("groupname"); $("#theform").submit(); });
    $("#sortby_comments").click(function() { $("#sortby").val("comments"); $("#theform").submit(); });
});

</script>

</html>
