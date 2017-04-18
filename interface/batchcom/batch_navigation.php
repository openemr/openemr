<table border="0" align="center" cellspacing="0" cellpadding="0" width="100%" height="22">
<tr bgcolor="#00ffff">
<?php if (acl_check('admin', 'batchcom')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/batchcom.php"
 onclick="top.restoreSession()"
 title="Batch Communication and Export"><?php echo xlt('BatchCom');?></a>&nbsp;
</td>
<?php } ?>
<?php if (acl_check('admin', 'notification')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/smsnotification.php"
 onclick="top.restoreSession()"
 title="SMS Notification"><?php echo xlt('SMS Notification');?></a>&nbsp;
</td>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/emailnotification.php"
 onclick="top.restoreSession()"
 title="SMS Notification"><?php echo xlt('Email Notification');?></a>&nbsp;
</td>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/settingsnotification.php"
 onclick="top.restoreSession()"
 title="SMS/Email Alert Settings"><?php echo xlt('SMS/Email Alert Settings');?></a>&nbsp;
</td>
<?php } ?>

<td width="20%">&nbsp;</td>
</tr>
</table>
