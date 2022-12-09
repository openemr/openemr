<?php

$sql = "SELECT id, username, specialty, msg_status.*, o.notes, o.title ".
	"FROM msg_status LEFT JOIN users ON user_id=id LEFT JOIN ".
	"(SELECT * FROM list_options WHERE list_id='msg_status') AS o ".
	"ON status = option_id ORDER BY id";
$fres = sqlStatementNoLog($sql);
$ustat = array();
while($frow = sqlFetchArray($fres)) {
	$ustat[$frow{'username'}] = $frow;
}

echo "<div style='display: none;'>\n";
foreach($ustat as $u) {
?>
<input name="u_id_<?php echo $u['username']; ?>" id="u_id_<?php echo $u['username']; ?>" type="hidden" tabindex="-1" readonly="readonly" disabled="disabled" value="<?php echo $u['username']; ?>" />
<input name="u_stat_<?php echo $u['username']; ?>" id="u_stat_<?php echo $u['username']; ?>" type="hidden" tabindex="-1" readonly="readonly" disabled="disabled" value="<?php echo $u['status']; ?>" />
<input name="u_until_<?php echo $u['username']; ?>" id="u_until_<?php echo $u['username']; ?>" type="hidden" tabindex="-1" readonly="readonly" disabled="disabled" value="<?php echo $u['until']; ?>" />
<input name="u_msg_<?php echo $u['username']; ?>" id="u_msg_<?php echo $u['username']; ?>" type="hidden" tabindex="-1" readonly="readonly" disabled="disabled" value="<?php echo $u['user_msg']; ?>" />
<input name="u_time_<?php echo $u['username']; ?>" id="u_time_<?php echo $u['username']; ?>" type="hidden" tabindex="-1" readonly="readonly" disabled="disabled" value="<?php echo $u['timestamp']; ?>" />
<input name="u_alert_<?php echo $u['username']; ?>" id="u_alert_<?php echo $u['username']; ?>" type="hidden" tabindex="-1" readonly="readonly" disabled="disabled" value="<?php echo $u['notes']; ?>" />
<input name="u_title_<?php echo $u['username']; ?>" id="u_title_<?php echo $u['username']; ?>" type="hidden" tabindex="-1" readonly="readonly" disabled="disabled" value="<?php echo $u['title']; ?>" />

<?php } ?>
</div>
