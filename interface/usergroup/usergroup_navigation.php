<?php
include_once("../globals.php");
include_once("../../library/acl.inc");
?>
<html>
<head>
<title><? xl('Navigation','e'); ?></title>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $nav_bg_line;?> topmargin=0 rightmargin=4 leftmargin=2 marginheight=0 bottommargin=0 link=#000000 vlink=#000000 alink=#000000>

<form border=0 method=post target="_top" name="find_patient" action="../main/finder/patient_finder.php">

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>

<? if (acl_check('admin', 'users')) { ?>
<td valign="middle" nowrap>
&nbsp;&nbsp;<a class=menu target=Main href="usergroup_admin.php"
 onclick="top.restoreSession()"
 title="Add or Edit Users, Groups and Facilities"><? xl('Users & Groups','e'); ?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('admin', 'forms')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../forms_admin/forms_admin.php"
 onclick="top.restoreSession()"
 title="Activate New Forms"><? xl('Forms','e'); ?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('admin', 'practice')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="<?=$GLOBALS['webroot']?>/controller.php?practice_settings"
 onclick="top.restoreSession()"
 title="Practice Settings"><?xl('Practice','e');?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('admin', 'calendar')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig"
 onclick="top.restoreSession()"
 title="Calendar Settings"><? xl('Calendar','e'); ?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('admin', 'database')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../main/myadmin/index.php"
 onclick="top.restoreSession()"
 title="Database Reporting"><? xl('Database','e'); ?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('admin', 'batchcom')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/batchcom.php"
 onclick="top.restoreSession()"
 title="Batch Communication and Export"><?xl('BatchCom','e');?></a>&nbsp;
</td>
<? } ?>

<? if ($GLOBALS['inhouse_pharmacy'] && acl_check('admin', 'drugs')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../drugs/drug_inventory.php"
 onclick="top.restoreSession()"
 title="Drug Inventory Management"><?xl('Drugs','e');?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('admin', 'language')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../language/language.php"
 onclick="top.restoreSession()"
 title="Language Management"><? xl('Language','e'); ?></a>&nbsp;
</td>
<? } ?>

<? if (acl_check('admin', 'users')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="<?echo $rootdir?>/logview/logview.php"
 onclick="top.restoreSession()"
 title="View Logs"><? xl('Logs','e'); ?></a>&nbsp;
</td>
<? } ?>

<?php if (! $GLOBALS['concurrent_layout']) { ?>
<td valign="middle" align="right" nowrap>
&nbsp;<a class=menu href="../main/main_screen.php" target="_top" class="menu"
 onclick="top.restoreSession()"
 title=<? xl('Exit from Administration','e'); ?>><?xl('Back','e');?></a>&nbsp;&nbsp;
</td>
<?php } ?>

</tr>
</table>

</form>

</body>
</html>
