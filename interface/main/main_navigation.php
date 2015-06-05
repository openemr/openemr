<?php
include_once("../globals.php");
include_once("../../library/acl.inc");
?>
<html>
<head>
<title>Navigation</title>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_nav">

<form border=0 method=post target="_top" name="find_patient" action="<?php echo $rootdir?>/main/finder/patient_finder.php">

<div id="nav_topmenu">
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td width="10%" nowrap>
<?php ///<a href="javascript:document.find_patient.action='finder/patient_finder_keyboard.php';document.find_patient.submit();" class='link'>Find Patient:</a> ?>
	<input type="entry" size="10" name="patient" />
	<select name="findBy" size=1>
	<option value="Last" selected><?php xl ('Name','e'); ?></option>
        <option value="Phone"><?php xl ('Phone','e'); ?></option>
	<option value="ID"><?php xl ('ID','e'); ?></option>
	<option value="SSN"><?php xl ('SSN','e'); ?></option>
	<option value="DOB"><?php xl ('DOB','e'); ?></option>
</select>
</td>

<td width="5%" nowrap>
<a href="javascript:top.restoreSession();document.find_patient.action='<?php echo $rootdir?>/main/finder/patient_finder.php';document.find_patient.submit();"
 class='link'>&nbsp;<?php xl('Find','e'); ?>&nbsp;<?php xl('Patient','e'); ?></a>
&nbsp;
</td>

<?php
 if (acl_check('patients','demo','',array('write','addonly') )) {
?>
<td align="center" nowrap>
&nbsp;<a class="menu" target=_top href="../new/new_patient.php" onclick="top.restoreSession()">
<?php xl('New','e'); ?>&nbsp;<?php xl('Patient','e'); ?></a>&nbsp;
</td>
<?php } ?>

<td align="center" nowrap>
&nbsp;<a href="../usergroup/user_info.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Password','e'); ?></a>&nbsp;
</td>

<td align="center" nowrap>
&nbsp;<a href="../super/edit_globals.php?mode=user" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Settings','e'); ?></a>&nbsp;
</td>

<?php if (acl_check('admin', 'calendar') || acl_check('admin', 'database') ||
       acl_check('admin', 'forms')    || acl_check('admin', 'practice') ||
       acl_check('admin', 'users')    || acl_check('admin', 'acl')) { ?>
<td align="center" nowrap>
&nbsp;<a class="menu" target=_top href="../usergroup/usergroup.php" onclick="top.restoreSession()">
<?php xl('Admin','e'); ?></a>&nbsp;
</td>
<?php } ?>

<td align="center" nowrap>
&nbsp;<a href="../reports/appointments_report.php" target="_new" class="menu"> <?php xl('Appts','e'); ?></a>&nbsp;&nbsp;
</td>
<td align="center" nowrap>
&nbsp;<a href="../reports/index.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Reports','e'); ?></a>&nbsp;
</td>

<td align="center" nowrap>
&nbsp;<a href="onotes/office_comments.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Notes','e'); ?></a>&nbsp;
</td>

<td align="center" nowrap>
&nbsp;<a href="../usergroup/addrbook_list.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('AB','e'); ?></a>&nbsp;
</td>

<?php
  if ( (acl_check('patients','demo','',array('write','addonly') )) &&
    is_readable("$webserver_root/custom/import.php")) {
?>
<td align="center" nowrap>
&nbsp;<a href="<?php echo $web_root; ?>/custom/import.php" target="Main" class="menu"
 onclick="top.restoreSession()"><?php xl('Import','e'); ?></a>&nbsp;
</td>
<?php } ?>

<?php
 if ($GLOBALS['enable_hylafax'] || $GLOBALS['enable_scanner']) {
  $faxcount = 0;
  if ($GLOBALS['enable_hylafax']) {
   // Count the number of faxes in the recvq:
   $statlines = array();
   exec("faxstat -r -l -h " . $GLOBALS['hylafax_server'], $statlines);
   foreach ($statlines as $line) {
    if (substr($line, 0, 1) == '-') ++$faxcount;
   }
  }
  $faxcount = $faxcount ? "($faxcount)" : "";
?>
<td align="center" nowrap>
&nbsp;<a href="../fax/faxq.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php echo xl('Docs') . $faxcount; ?></a>&nbsp;
</td>
<?php } ?>

<?php if (acl_check('acct', 'rep') || acl_check('acct', 'eob') || acl_check('acct', 'bill')) { ?>
<td align="center" nowrap>
&nbsp;<a href="../billing/billing_report.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Billing','e'); ?></a>&nbsp;
</td>
<?php } ?>

<?php if ($GLOBALS['athletic_team']) { ?>

<td align="center" nowrap>
&nbsp;<a href="../reports/players_report.php?embed=1" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Roster','e'); ?></a>&nbsp;
</td>
<td align="center" nowrap>
&nbsp;<a href="main.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Calendar','e'); ?></a>&nbsp;
</td>

<?php } else { ?>

<td align="center" nowrap>
&nbsp;<a href="main.php" target="Main" class="menu" onclick="top.restoreSession()">
<?php xl('Home','e'); ?></a>&nbsp;
</td>

<?php } ?>

<td align="center" nowrap>
&nbsp;<a href="../logout.php" target="_top" class="menu" onclick="top.restoreSession()">
<?php xl('Logout','e'); ?></a>&nbsp;&nbsp;
</td>

<td align="center" nowrap>
&nbsp;<a href="http://open-emr.org/wiki/index.php/OpenEMR_4.2.0_Users_Guide" target="_blank" class="menu"> 
<?php xl('Manual','e'); ?></a>&nbsp;&nbsp;
</td>

</tr>
</table>
</div>

</form>

</body>
</html>
