<?
include_once("../globals.php");
?>

<html>
<head>
<title>Navigation</title>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $nav_bg_line;?> topmargin=0 rightmargin=4 leftmargin=2 marginheight=0 bottommargin=0 link=#000000 vlink=#000000 alink=#000000>

<form border=0 method=post target="_top" name="find_patient" action="../main/finder/patient_finder.php">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>

<td valign="middle" nowrap>
&nbsp;&nbsp;<a class=menu target=Main href="usergroup_admin.php">Users & Groups</a>&nbsp;
</td>

<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../forms_admin/forms_admin.php">Forms Settings</a>&nbsp;
</td>

<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="<?=$GLOBALS['webroot']?>/controller.php?practice_settings">Practice Settings</a>&nbsp;
</td>

<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig">Calendar Settings</a>&nbsp;
</td>

<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../main/myadmin/index.php">Database&nbsp;Reporting</a>&nbsp;
</td>

<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="<?echo $rootdir?>/logview/logview.php">View Logs</a>&nbsp;
</td>

<td valign="middle" align="right" nowrap>
<?//<a href="../logout.php?auth=logout" target="_top" class="logout">Logout</a>?>
&nbsp;<a class=menu href="../main/main_screen.php" target="_top" class="menu">Back</a>&nbsp;&nbsp;
</td>
</tr>
</table>

</form>

</body>
</html>
