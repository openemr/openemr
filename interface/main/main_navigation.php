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
	<option value="ID"><? xl ('ID','e'); ?></option>
	<option value="Last" selected><? xl ('Name','e'); ?></option>
	<option value="SSN"><? xl ('SSN','e'); ?></option>
	<option value="DOB"><? xl ('DOB','e'); ?></option>
</select>
</td>

<td width="5%" nowrap>
<a href="javascript:document.find_patient.action='<?echo $rootdir?>/main/finder/patient_finder.php';document.find_patient.submit();" class=link>&nbsp;<? xl('Find','e'); ?>&nbsp;<? xl('Patient','e'); ?></a>
&nbsp;
</td>

<?
 $npauth = acl_check('patients', 'demo');
 if ($npauth == 'write' || $npauth == 'addonly') {
?>
<td align="center" nowrap>
&nbsp;<a class="menu" target=_top href="../new/new_patient.php"><? xl('New','e'); ?>&nbsp;<? xl('Patient','e'); ?></a>&nbsp;
</td>
<? } ?>

<td align="center" nowrap>
&nbsp;<a href="../usergroup/user_info.php" target="Main" class="menu"><? xl('Password','e'); ?></a>&nbsp;
</td>

<? if (acl_check('admin', 'calendar') || acl_check('admin', 'database') ||
       acl_check('admin', 'forms')    || acl_check('admin', 'practice') ||
       acl_check('admin', 'users')) { ?>
<td align="center" nowrap>
&nbsp;<a class=menu target=_top href="../usergroup/usergroup.php"><? xl('Administration','e'); ?></a>&nbsp;
</td>
<? } ?>

<td align="center" nowrap>
&nbsp;<a href="../reports/index.php" target="Main" class="menu"><? xl('Reports','e'); ?></a>&nbsp;
</td>

<td align="center" nowrap>
&nbsp;<a href="onotes/office_comments.php" target="Main" class="menu"><? xl('Notes','e'); ?></a>&nbsp;
</td>

<? if ($GLOBALS['hylafax_server']) { ?>
<td align="center" nowrap>
&nbsp;<a href="../fax/faxq.php" target="Main" class="menu"><? xl('Fax','e'); ?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('acct', 'rep') || acl_check('acct', 'eob') || acl_check('acct', 'bill')) { ?>
<td align="center" nowrap>
&nbsp;<a href="../billing/billing_report.php" target="Main" class="menu"><? xl('Billing','e'); ?></a>&nbsp;
</td>
<? } ?>

<? if ($GLOBALS['athletic_team']) { ?>
<td align="center" nowrap>
&nbsp;<a href="../reports/players_report.php?embed=1" target="Main" class="menu"><? xl('Roster','e'); ?></a>&nbsp;
</td>
<td align="center" nowrap>
&nbsp;<a href="main.php" target="Main" class="menu"><? xl('Calendar','e'); ?></a>&nbsp;
</td>
<? } else { ?>
<td align="center" nowrap>
&nbsp;<a href="main.php" target="Main" class="menu"><? xl('Home','e'); ?></a>&nbsp;
</td>
<? } ?>

<td align="center" nowrap>
&nbsp;<a href="../logout.php?auth=logout" target="_top" class="menu"><? xl('Logout','e'); ?></a>&nbsp;&nbsp;
</td>

</tr>
</table>

</form>

</body>
</html>
