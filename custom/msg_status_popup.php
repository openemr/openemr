<?php
// Copyright (C) 2016 Rich Genandt <rgenandt@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmt.forms.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/msg.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmt.msg.inc');

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

$change_any_user = AclMain::aclCheckCore('admin','super');
$user_lookup_order = strtolower(globalKeyTest('wmt::user_lookup_order'));
$user_display_order = strtolower(globalKeyTest('wmt::user_display_order'));

$user_status = new WmtMsgStatus($_SESSION['authUserID']);
$mode = '';

if(isset($_GET['mode'])) $mode = strip_tags($_GET['mode']);
if(!isset($_POST['user'])) $_POST['user'] = $_SESSION['authUserID'];
if(!isset($_POST['status'])) $_POST['status'] = $user_status->status;
if(!isset($_POST['until'])) $_POST['until'] = $user_status->until;
if(!isset($_POST['user_msg'])) $_POST['user_msg'] = $user_status->user_msg;
if(!isset($_POST['user_groups'])) $_POST['user_groups'] = $user_status->groups;
if(!isset($_POST['groups'])) $_POST['groups'] = $user_status->group_desc;

if(!isset($_POST['group_mode'])) $_POST['group_mode'] = FALSE;
if(!isset($_POST['grp_group'])) $_POST['grp_group'] = '';
if(!isset($_POST['member'])) $_POST['member'] = '';
if(!isset($_POST['group_members'])) $_POST['group_members'] = '';
if(!isset($_POST['members'])) $_POST['members'] = '';

if($mode == 'save') {
	if($_POST['group_mode']) {
		$curr_users = explode('~|', $_POST['group_members']);
		// Now update the group links
		$del = 'DELETE FROM `msg_group_link` WHERE `id` = ?';
		$ins = 'INSERT INTO `msg_group_link` (`timestamp`, `user_id`, `set_by`, ' .
			'`group_id`) VALUES (NOW(), ?, ?, ?) ON DUPLICATE KEY UPDATE '.
			'`timestamp` = NOW(), `set_by` = VALUES(`set_by`)';
		$log = 'INSERT INTO `msg_group_history` (`timestamp`, `user_id`, ' .
			'`set_by`, `group_id`, `event`) VALUES (NOW(), ?, ?, ?, ?)';
		$prev = 'SELECT * FROM `msg_group_history` WHERE `user_id` = ? AND ' .
			'`group_id` = ? ORDER BY `timestamp` DESC LIMIT 1';
		$name_query = 'SELECT `lname`, `fname`, `mname` FROM `users` WHERE `id` = ?';

		$sql = 'SELECT * FROM `msg_group_link` WHERE `group_id` = ?';
		$fres = sqlStatement($sql, array(substr($_POST['grp_group'],4)));
		while($frow = sqlFetchArray($fres)) {
			if(!in_array($frow{'user_id'}, $curr_users)) {
				sqlStatement($del,array($frow{'id'}));
				sqlStatement($log,array($frow{'user_id'}, $_SESSION['authUserID'], 
					$frow{'group_id'}, 'exit'));
			}
		}
		foreach($curr_users as $user) {
			if($user) {
				$u = sqlQuery($name_query, array($user));
				if($_POST['members']) $_POST['members'] .= ', ';
				$_POST['members'] .= $u['fname'] . ' ' . $u['lname'];
				sqlStatement($ins, array($user,$_SESSION['authUserID'],
					substr($_POST['grp_group'],4)));
				$last_entry = 
						sqlQuery($prev, array($user, substr($_POST['grp_group'],4)));
				if($last_entry{'event'} != 'enter') {
					sqlStatement($log,array($user, $_SESSION['authUserID'], 
						substr($_POST['grp_group'],4), 'enter'));
				}
			}
		}
	} else if($_POST['user']) {
		$user_status = new WmtMsgStatus($_POST['user']);
		$user_status->status   = $_POST['status'];
		$user_status->until    = $_POST['until'];
		$user_status->user_msg = $_POST['user_msg'];
		$user_status->groups   = $_POST['user_groups'];
		$user_status->update();
	}

	// SAVE THIS IF WE USE AS A POPUP AGAIN
	/*
	if(!isset($_GET['continue']) && FALSE) {
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>Redirecting.....</title>\n";
		echo "</head>\n";
		echo "<body onload=\"opener.tabClose('msc');\">\n";
		echo "</body>\n";
		echo "</html>\n";
	}
	*/
	$user_status = new WmtMsgStatus($_POST['user']);
	$_POST['groups'] = $user_status->group_desc;
}
?>

<html>
<head>
<title><?php echo xlt('User Messaging System Status'); ?></title>
<?php Header::setupHeader(['main-theme', 'jquery', 'jquery-ui', 'bootstrap']); ?>
<script type="text/javascript">

<?php include(INC_DIR . 'init_ajax.inc.js'); ?>

function fetchUserStatus(user) {
	var output = 'error';
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/msg.ajax.php",
		datatype: "html",
		data: {
			type: 'status',
			id: user 
		},
		success: function(result) {
			if(result['error']) {
				output = '';
				alert('There was a problem retrieving user details\n'+result['error']);
			} else {
				output = result;
			}
		},
	  async: false
	});
	return output;
}

function handleUserSelect(obj) {
	var user = obj.value;
	if(user) {
		var dtl = fetchUserStatus(user);
		var dtls = dtl.split('~~');
		document.getElementById('status').value = dtls[0];
		if(dtls.length > 1) document.getElementById('until').value = dtls[1];
		if(dtls.length > 2) document.getElementById('user_msg').value = dtls[2];
		if(dtls.length > 3) {
			var span = document.getElementById('groups');
				while( span.firstChild ) {
					span.removeChild( span.firstChild );
				}
				span.appendChild( document.createTextNode( dtls[3] ) );
		}
		if(dtls.length > 4) document.getElementById('user_groups').value = dtls[4];
	}
}

function fetchGroupMembers(grp) {
	var output = 'error';
	var order = '<?php echo $user_lookup_order; ?>';
	if(order == 'first') {
		order = 'u.fname';
	} else {
		order = 'u.lname';
	}
	
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/msg.ajax.php",
		datatype: "html",
		data: {
			type: 'members',
			id: grp 
		},
		success: function(result) {
			if(result['error']) {
				output = '';
				alert('There was a problem retrieving group members\n'+result['error']);
			} else {
				output = result;
			}
		},
	  async: false
	});
	return output;
}

function handleGroupSelect(obj) {
	var grp = obj.value.substr(4);
	if(grp != '') {
		var dtl = fetchGroupMembers(grp);
		var dtls = dtl.split('~~');
		if(dtls.length > 0) {
			var span = document.getElementById('members');
				while( span.firstChild ) {
					span.removeChild( span.firstChild );
				}
				span.appendChild( document.createTextNode( dtls[0] ) );
		}
		document.getElementById('group_members').value = dtls[1];
	}
}

function toggleGroupDisplay() {
	if(document.getElementById('group_mode').checked == true) {
		document.getElementById('user_management').style.display = 'none';
		document.getElementById('group_management').style.display = 'block';
	} else {
		document.getElementById('user_management').style.display = 'block';
		document.getElementById('group_management').style.display = 'none';
	}
}
</script>

<style>
td { font-size:10pt; }
</style>
</head>

<?php
?>

<body class="body_top">
<form method='post' name='theform' action='msg_status_popup.php'>

<h2><?php echo xlt('Check / Update Messaging Status'); ?></h2>

<div id="user_management" style="width: 100%; display: <?php echo $_POST['group_mode'] ? 'none' : 'block'; ?>">
<table width='80%' border='0' cellpadding='2'>
  <tr>
    <td><b><?php echo xlt('User'); ?></b></td>
    <td><b><?php echo xlt('Status'); ?></b></td>
    <td><b><?php echo xlt('Will Return'); ?></b></td>
  </tr>
	<tr>
		<?php if($change_any_user) { ?>
		<td><select name="user" id="user" class="form-control" onchange="handleUserSelect(this);">
			<?php UserSelect($_POST['user'], TRUE, ' AND (UPPER(`info`) NOT LIKE "%MESSAGE EXCLUDE%" OR `info` IS NULL) ', array(), '', FALSE, TRUE); ?>
		</select></td>
		<?php } else { ?>
		<td><input name="user" id="user" type="hidden" readonly="readonly" tabindex="-1" value="<?php echo $_POST['user']; ?>" /><input name="tmp_user_id" id="tmp_user_id" disabled="disabled" readonly="readonly" tabindex="-1" value="<?php echo UserNameFromID($_POST['user']); ?>" /></td>
		<?php } ?>
		<td><select name="status" id="status" class="form-control">
			<?php ListSel($_POST['status'], 'msg_status'); ?>
		</td></select>
		<td><input name="until" id="until" class="form-control" type="text" value="<?php echo $_POST['until']; ?>" /></td>
	</tr>
	<tr>
		<td><b><?php echo xlt('Member of Group(s)'); ?></b></td>
	</tr>
	<tr>
		<td style="vertical-align: top;"><select name="group" id="group" class="form-control" onchange="UpdateSelDescription('group','user_groups','groups','~|');">
			<?php SelMultiWithDesc('', 'Messaging_Groups'); ?>
		</td></select>
		<td colspan="2">&nbsp;&nbsp;<span id="groups"><?php echo text($_POST['groups']); ?></span></td>
	</tr>
	<tr>
		<td><b>Message</b></td>
	</tr>
	<tr>
		<td colspan="4"><input name="user_msg" id="user_msg" style="width: 100%;" class="form-control" type="text" value="<?php echo attr($_POST['user_msg']); ?>" /></td>
	</tr>
</table>
</div>

<div id="group_management" style="width: 100%; display: <?php echo $_POST['group_mode'] ? 'block' : 'none'; ?>">
<table width='100%' border='0' cellpadding='4'>
  <tr>
    <td style="width: 30%;"><b><?php echo xlt('Group'); ?></b></td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td><select name="grp_group" id="grp_group" class="form-control" onchange="handleGroupSelect(this);">
			<?php MsgGroupSelect($_POST['grp_group'], FALSE, TRUE); ?>
		</select></td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td style="vertical-align: top;"><b><?php echo xlt('Member(s)'); ?></b></td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td><select name="member" id="member" class="form-control" onchange="UpdateSelDescription('member','group_members','members','~|');">
			<?php UserSelMultiWithDesc('', 'Select Another', ' AND (UPPER(`info`) NOT LIKE "%MESSAGE EXCLUDE%" OR `info` IS NULL) '); ?>
		</select></td>
		<td colspan="2">&nbsp;&nbsp;<span id="members"><?php echo text($_POST['members']); ?>&nbsp;</span></td>
	</tr>
</table>
</div>

<?php if($change_any_user) { ?>
<div style="float:left; margin-top: 15px; margin-bottom: 15px;" class="text">
<input name="group_mode" id="group_mode" type="checkbox" value="group" <?php echo $_POST['group_mode'] ? 'checked="checked"' : ''; ?> onclick="toggleGroupDisplay(); " />&nbsp;&nbsp;<label for="group_mode">Manage by Group</label>
</div>
<?php } ?>

<br>
<table width='100%' border='0' cellpadding='2'>
	<tr>
		<td colspan="3"><div style="float: left;"><a href="javascript: verifyMsgForm();" class="btn btn-primary css_button"><span><?php echo xlt('Save Changes'); ?></span></a></div>
		<?php if($change_any_user && FALSE) { ?>
		<div style="float: left; padding-left: 15px;"><a href="javascript: verifyMsgForm('continue');" class="btn btn-primary css_button"><span><?php echo xlt('Update & Continue'); ?></span></a></div>
		<?php } ?>
		</td>
		<td><!-- div style="float: right; padding-right: 15px;"><a href="javascript:;" class="css_button"><span><?php // echo xlt('Close'); ?></span></a></div -->&nbsp;</td>
	</tr>
</table>

<div style="display: hidden;">
<input name="user_groups" id="user_groups" type="hidden" tabindex="-1" value="<?php echo $_POST['user_groups']; ?>" />
<input name="group_members" id="group_members" type="hidden" tabindex="-1" value="<?php echo $_POST['group_members']; ?>" />
</div>
</center>
</form>
</body>
<script type="text/javascript" src="../library/wmt-v2/wmt.forms.js"></script>
<script type="text/javascript">

function verifyMsgForm() {
	var test = document.forms[0].elements['status'].value;
	var e = document.getElementById('user');
	if(test == '' || test == null) {
		alert("You must specify a status");
	} else {
		document.forms[0].action += "?mode=save";
		if(arguments.length > 0) 
				document.forms[0].action += "&continue=" + arguments[0];
		document.forms[0].submit();
	}
}

</script>
</html>
