<?php
/**
 * finder_navigation.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
?>

<html>
<head>
<?php html_header_show();?>
<title>Navigation</title>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_nav">

<div id="nav_topmenu">
<form method='post' target="_top" name="find_patient" action="<?php echo $rootdir?>/main/finder/patient_finder.php" onsubmit="return top.restoreSession()">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>

<td style="text-align:left; width: 250px; white-space: nowrap;">
<input type="textbox" size="10" name="patient" value="<?php echo attr($_REQUEST['patient']); ?>" >
<select name="findBy">
<option value="Last" <?php if ($_REQUEST['findBy'] == 'Last') {
    echo 'selected';
} ?>><?php xl('Name', 'e');?></option>
<option value="Phone" <?php if ($_REQUEST['findBy'] == 'Phone') {
    echo 'selected';
} ?>><?php xl('Phone', 'e');?></option>
<option value="ID" <?php if ($_REQUEST['findBy'] == 'ID') {
    echo 'selected';
} ?>><?php xl('ID', 'e');?></option>
<option value="SSN" <?php if ($_REQUEST['findBy'] == 'SSN') {
    echo 'selected';
} ?>><?php xl('SSN', 'e');?></option>
<option value="DOB" <?php if ($_REQUEST['findBy'] == 'DOB') {
    echo 'selected';
} ?>><?php xl('DOB', 'e');?></option>
</select>
<a href="javascript:top.restoreSession();document.find_patient.action='<?php echo $rootdir?>/main/finder/patient_finder.php';document.find_patient.submit();" class="link">&nbsp;<?php xl('Find Patient', 'e');?></a>
</td>

<td style="text-align:left">
&nbsp;&nbsp;&nbsp;<a class="menu" target="_top" href="../../new/new_patient.php" onclick="top.restoreSession()">
<?php xl('New Patient', 'e');?></a>&nbsp;
</td>

<td style="text-align:right">
&nbsp;<a href="../main_screen.php" target="_top" class="logout" onclick="top.restoreSession()">
<?php xl('Back', 'e');?></a>&nbsp;&nbsp;
</td>
</tr>
</table>

</form>
</div>

</body>
</html>
