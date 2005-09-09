<?
include_once("../globals.php");
include_once("../../library/acl.inc");
?>
<html>
<head>
<title>Navigation</title>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $nav_bg_line;?> topmargin=0 rightmargin=4 leftmargin=2 marginheight=0 bottommargin=0 link=#000000 vlink=#000000 alink=#000000>

<form border=0 method=post target="_top" name="find_patient" action="<?echo $rootdir?>/main/finder/patient_finder.php">
<?//<form border=0>?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td width="10%" nowrap>
<?//<a href="javascript:document.find_patient.action='finder/patient_finder_keyboard.php';document.find_patient.submit();" class=link>Find Patient:</a>?>
	<input type="entry" size="10" name="patient" />
	<select name="findBy" size=1>
	<option value="ID">ID</option>
	<option value="Last" selected>Name</option>
	<option value="SSN">SSN</option>
	<option value="DOB">DOB</option>
</select>
</td>

<td width="5%" nowrap>
<a href="javascript:document.find_patient.action='<?echo $rootdir?>/main/finder/patient_finder.php';document.find_patient.submit();" class=link>&nbsp;Find&nbsp;Patient</a>
&nbsp;
</td>

<?
 $npauth = acl_check('patients', 'demo');
 if ($npauth == 'write' || $npauth == 'addonly') {
?>
<td align="center" nowrap>
&nbsp;<a class="menu" target=_top href="../new/new_patient.php">New&nbsp;Patient</a>&nbsp;
</td>
<? } ?>

<td align="center" nowrap>
&nbsp;<a href="../usergroup/user_info.php" target="Main" class="menu">Password</a>&nbsp;
</td>

<? if (acl_check('admin', 'calendar') || acl_check('admin', 'database') ||
       acl_check('admin', 'forms')    || acl_check('admin', 'practice') ||
       acl_check('admin', 'users')) { ?>
<td align="center" nowrap>
&nbsp;<a class=menu target=_top href="../usergroup/usergroup.php">Administration</a>&nbsp;
</td>
<? } ?>

<td align="center" nowrap>
&nbsp;<a href="../reports/index.php" target="Main" class="menu">Reports</a>&nbsp;
</td>

<td align="center" nowrap>
&nbsp;<a href="onotes/office_comments.php" target="Main" class="menu">Notes</a>&nbsp;
</td>

<? if (acl_check('acct', 'rep') || acl_check('acct', 'eob') || acl_check('acct', 'bill')) { ?>
<td align="center" nowrap>
&nbsp;<a href="../billing/billing_report.php" target="Main" class="menu">Billing</a>&nbsp;
</td>
<? } ?>

<? if ($GLOBALS['athletic_team']) { ?>
<td align="center" nowrap>
&nbsp;<a href="../reports/players_report.php?embed=1" target="Main" class="menu">Roster</a>&nbsp;
</td>
<td align="center" nowrap>
&nbsp;<a href="main.php" target="Main" class="menu">Calendar</a>&nbsp;
</td>
<? } else { ?>
<td align="center" nowrap>
&nbsp;<a href="main.php" target="Main" class="menu">Home</a>&nbsp;
</td>
<? } ?>

<td align="center" nowrap>
&nbsp;<a href="../logout.php?auth=logout" target="_top" class="menu">Logout</a>&nbsp;&nbsp;
</td>

</tr>
</table>

</form>

</body>
</html>
