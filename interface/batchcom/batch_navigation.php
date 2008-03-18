<table border="0" align="center" cellspacing="0" cellpadding="0" width="100%" height="22">
<tr bgcolor="#00ffff">
<? if (acl_check('admin', 'batchcom')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/batchcom.php"
 onclick="top.restoreSession()"
 title="Batch Communication and Export"><?xl('BatchCom','e');?></a>&nbsp;
</td>
<? } ?>
<? if (acl_check('admin', 'notification')) { ?>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/smsnotification.php"
 onclick="top.restoreSession()"
 title="SMS Notification"><?xl('SMS Notification','e');?></a>&nbsp;
</td>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/emailnotification.php"
 onclick="top.restoreSession()"
 title="SMS Notification"><?xl('Email Notification','e');?></a>&nbsp;
</td>
<td valign="middle" nowrap>
&nbsp;<a class=menu target=Main href="../batchcom/settingsnotification.php"
 onclick="top.restoreSession()"
 title="SMS/Email Alert Settings"><?xl('SMS/Email Alert Settings','e');?></a>&nbsp;
</td>
<? } ?>

<td width="20%">&nbsp;</td>
</tr>
</table>
