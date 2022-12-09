<?php
if(!function_exists('MsgUserGroupSelect')) {
function MsgUserGroupSelect($thisField, $include_grps = false, $use_alerts = false, $show_all = true, $ustat = array(), $show_empty = false, $show_only_active_grp = true) {
	$user_lookup_order = globalKeyTest('wmt::user_lookup_order');
	$user_display_order = globalKeyTest('wmt::user_display_order');
  $sql = 'SELECT `username`, `info`, `lname`, `fname`, 1 as seq FROM `users` WHERE ' .
			'`active` = 1 AND `username` != "" AND (UPPER(`info`) NOT LIKE ' .
			'"%MESSAGE EXCLUDE%" OR `info` IS NULL) ';
	if($include_grps) {
		$sql .= 'UNION ALL SELECT CONCAT("GRP:",option_id) AS username, ';
		$sql .= '`notes` AS `info`, ';
		if($user_lookup_order == 'first') {
			$sql .= '`notes` AS lname, `title` AS fname ';
		} else {
			$sql .= '`title` AS lname, `notes` AS fname ';
		}
		$sql .= ', 0 as seq FROM `list_options` WHERE `list_id` = "Messaging_Groups" ';
		$sql .= 'AND (UPPER(`notes`) NOT LIKE "%MESSAGE EXCLUDE%" OR ' .
			'`notes` IS NULL) ';

		if($show_only_active_grp === true) {
			$sql .= 'AND `activity` = 1 ';
		}
	}
	if($user_lookup_order == 'first') {
		$sql .= 'ORDER BY seq, fname, lname';
	} else {
		$sql .= 'ORDER BY seq, lname, fname';
	}
  $ulist= sqlStatement($sql);
	if($show_empty) {
  	echo '<option value=""';
  	if(!$thisField) echo ' selected="selected"';
  	echo ">&nbsp;</option>";
	}
	if($show_all) {
  	echo '<option value="~all~"';
  	if(!$thisField == '~all~') echo ' selected="selected"';
  	echo ">-- ALL --</option>";
	}
  while ($urow= sqlFetchArray($ulist)) {
		$accent = $status = '';
		if($use_alerts) {
			if(!isset($ustat[$urow['username']]['title'])) 
								$ustat[$urow['username']]['title'] = '';
			if(!isset($ustat[$urow['username']]['notes'])) 
								$ustat[$urow['username']]['notes'] = '';
			if(strtolower($ustat[$urow['username']]['title']) == '') {
				$status = '&nbsp;&nbsp;&nbsp;&nbsp;- Unknown -';
			}
			if(strtolower($ustat[$urow['username']]['notes']) == 'no alert') {
				$status = '&nbsp;&nbsp;&nbsp;&nbsp;- In -';
				$accent = " style='color: blue;'";
			}
			if(strtolower($ustat[$urow['username']]['notes']) == 'alert') {
				$status = '&nbsp;&nbsp;&nbsp;&nbsp;* OUT *';
				$accent = " style='color: red;'";
			}
		} 
		if(stripos($urow{'fname'}, 'exclude') !== FALSE) $urow{'fname'} = '';
		if(strtolower($urow{'fname'}) == 'quest') $urow{'username'} = 'SYSTEM';
    echo '<option value="' . $urow['username'] . '"';
    if($thisField == $urow['username']) echo ' selected="selected"';
    echo $accent . '>';
		if($user_display_order == 'first') {
			echo text($urow['fname'].' '.$urow['lname']);
		} else {
			echo text($urow['lname']);
			if($urow['fname']) echo ', ' . text($urow['fname']);
		}
		echo $status;
    echo '</option>';
  }
}

function MsgGroupSelect($thisField, $all=false, $none=false, $filter='') {
	$user_lookup_order = globalKeyTest('wmt::user_lookup_order');
	$user_display_order = globalKeyTest('wmt::user_display_order');
	$flds = sqlListFields('list_options');
	$sql = 'SELECT CONCAT("GRP:",option_id) AS username, ';
	if($user_lookup_order == 'first') {
		$sql .= '`title` AS fname, `notes` AS lname ';
	} else {
		$sql .= '`title` AS lname, `notes` AS fname ';
	}
	$sql .= 'FROM `list_options` WHERE `list_id` = "Messaging_Groups" ';
	if(in_array('activity', $flds)) $sql .= 'AND `activity` > 0 ';
	if($filter) $sql .= 'AND `option_id` LIKE "%'.$filter.'%" ';
	if($user_lookup_order == 'first') {
		$sql .= 'ORDER BY fname, lname';
	} else {
		$sql .= 'ORDER BY lname, fname';
	}
  $ulist= sqlStatement($sql);
	if($none) {
  	echo '<option value=""';
  	if(!$thisField) echo ' selected="selected"';
  	echo '>&nbsp;</option>';
	}
	if($all) {
  	echo '<option value="~all~"';
  	if(!$thisField) echo ' selected="selected"';
  	echo '>-- ALL --</option>';
	}
  while ($urow= sqlFetchArray($ulist)) {
		if(strtolower($urow{'fname'}) == 'quest') $urow{'username'} = 'SYSTEM';
    echo '<option value="' . $urow['username'] . '"';
    if($thisField == $urow['username']) echo ' selected="selected"';
    echo '>';
		if($user_display_order == 'first') {
			echo text($urow['fname'] . ' ' . $urow['lname']);
		} else {
			echo text($urow['lname']);
			if($urow['fname'] && (stripos($urow['fname'], 'MESSAGE EXCLUDE') === FALSE)) echo ', ' . text($urow['fname']);
		}
    echo '</option>';
  }
}

function MsgUserGroupDisplay($thisField) {
	if(!$thisField) return '';
	$user_display_order = globalKeyTest('wmt::user_display_order');
  $sql = 'SELECT `username`, `lname`, `fname` FROM `users` WHERE `username` = ?';
	if(substr($thisField,0,4) == 'GRP:') $sql = 'SELECT `option_id` AS ' .
		'username, `title` AS lname, `notes` AS fname FROM `list_options` WHERE '.
		'`list_id` = "Messaging_Groups" AND `option_id` = ?';
	if(substr($thisField,0,4) == 'GRP:') $thisField = substr($thisField,4);
  $uname = sqlQuery($sql, array($thisField));
	if(stripos($uname{'fname'},'exclude') !== FALSE) $uname{'fname'} = '';
	$disp = $uname{'lname'};
	if($uname{'fname'}) $disp .= ', '.$uname{'fname'};
	if($user_display_order == 'first') $disp = $uname{'fname'}.' '.$uname{'lname'};
	return $disp;
}

function MsgSelect($thisField, $thisList = '') {
  $sql = 'SELECT `option_id`, `title` FROM `list_options` WHERE `list_id` = ? '.
		'ORDER BY `seq`';
  $slist= sqlStatement($sql, array($thisList));
  echo '<option value=""';
  if(!$thisField) echo ' selected="selected"';
  echo '>-- ALL --</option>';
  echo '<option value="~active~"';
  if($thisField == '~active~') echo ' selected="selected"';
  echo '>-- Active --</option>';
  while ($srow = sqlFetchArray($slist)) {
    echo '<option value="' . $srow['option_id'] . '"';
    if($thisField == $srow['option_id']) echo ' selected="selected"';
    echo '>' . text($srow['title']) . '</option>';
  }
}

function MsgTypeSelect($thisField,$thisList='note_type',$show_portal=true) {
  $sql = 'SELECT `option_id`, `title`, `codes` FROM `list_options` WHERE ' .
		'`list_id` = ? ORDER BY `seq`';
  $slist= sqlStatement($sql, array($thisList));
  echo '<option value=""';
  if(!$thisField) echo ' selected="selected"';
  echo '>-- ALL --</option>';
  while ($srow = sqlFetchArray($slist)) {
    echo '<option value="' . $srow['title'] . '"';
    if($thisField == $srow['title']) echo ' selected="selected"';
		if($srow['codes'] == 1 && $show_portal) echo  ' style="color: red;"';
  	echo '>' . text( $srow['title']);
		if($srow['codes'] == 1 && $show_portal) echo ' (Seen in the Portal)';
    echo '</option>';
  }
}

function addPnoteWMT($pid, $newtext, $authorized = '0', $activity = '1',
  $title='Unassigned', $assigned_to = '', $datetime = '', $message_status = "New")
{
  if (empty($datetime)) $datetime = date('Y-m-d H:i:s');

  // make inactive if set as Done
  if ($message_status == "Done") $activity = 0;

  $body = date('Y-m-d H:i') . ' ( PID: ' . $pid;
  if ($assigned_to) $body .= " to $assigned_to";
  $body = $body . ') ' . $newtext;

  return sqlInsert('INSERT INTO `pnotes` (`date`, `body`, `pid`, `user`, ' . 
		'`groupname`, `authorized`, `activity`, `title`, `assigned_to`, ' .
		'`message_status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    array($datetime, $body, $pid, $pid, '', $authorized, $activity, 
		$title, $assigned_to, $message_status) );
}

function getPnotesByUserWmt($activity="~active~",$show_from="",$show_to='',$count=false,$sortby='',$sortorder='',$begin='',$listnumber='',$start='',$end='',$type='')
{

	$binds = array();
	$binds[] = 1;
  // Set the activity part of query
  if ($activity == '~active~') {
    $filter = '(pnotes.`message_status` != "Done" AND pnotes.`activity` = 1)';
  } else if ($activity != '') {
    $filter = '(pnotes.`message_status` = ?)';
		$binds[] = $activity;
  } else {
    $filter = '';
  }

  // run the query
	$sql = 'SELECT pnotes.id, pnotes.user, pnotes.pid, pnotes.title, '.
		'pnotes.date, pnotes.message_status, pnotes.assigned_to, '.
		'list_options.option_id, '.
		'IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) AS '.
		'users_fname, '.
		'IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname) AS '.
		'users_lname, '.
		'IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), '.
			'IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", '.
			'list_options.title, msg_to.lname), patient_data.lname) '.
			'AS msg_to_lname, '.
		'IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), '.
			'IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", '.
			'list_options.notes, msg_to.fname), patient_data.fname) '.
			'AS msg_to_fname, '.
		'patient_data.fname AS patient_data_fname, '.
		'patient_data.lname AS patient_data_lname '.
		'FROM pnotes '.
		'LEFT JOIN users AS u ON pnotes.user = u.username '.
		'LEFT JOIN users AS msg_to ON pnotes.assigned_to = msg_to.username '.
		'LEFT JOIN list_options ON '.
		'(SUBSTRING(pnotes.assigned_to,5) = list_options.option_id '.
		'AND list_options.list_id = "Messaging_Groups") '.
		'LEFT JOIN patient_data ON pnotes.pid = patient_data.pid '.
		'WHERE pnotes.deleted != ?';
	if($filter) $sql .= " AND $filter";
	if($type != '') {
		$sql .= ' AND pnotes.title = ?';
		$binds[] = $type;
	}
	if($show_from != '' && $show_from != '~all~') {
		$sql .= ' AND pnotes.user = ?';
		$binds[] = $show_from;
	}
	if($show_to != '' && $show_to != '~all~') {
		$sql .= ' AND (pnotes.assigned_to = ?';
		$binds[] = $show_to;
		$query = 'SELECT users.username, msg_group_link.* FROM users '.
			'RIGHT JOIN msg_group_link ON (users.id = msg_group_link.user_id) '.
			'WHERE users.username=?';
		$grps = sqlStatement($query, array($show_to));
		while($grp = sqlFetchArray($grps)) {
			$sql .= ' OR pnotes.assigned_to = ?';
			$binds[] = 'GRP:' . $grp['group_id'];
		}
		// THIS HANDLES GROUPING THE RX SUB GROUPS UNDER THE MAIN RX GROUP
		if(substr($show_to,-7) == '_refill') {
			$query = 'SELECT * FROM list_options '.
				'WHERE list_id = "Messaging_Groups" AND option_id LIKE "rx_grp_%"';
			$grps = sqlStatement($query);
			while($grp = sqlFetchArray($grps)) {
				$sql .= ' OR pnotes.assigned_to = ?';
				$binds[] = 'GRP:' . $grp{'option_id'};
			}
		}
		$sql .= ') ';
	}
	if($start != '') {
		$sql .= ' AND pnotes.date >= ?';
		$binds[] = $start . ' 00:00:00';
	}
	if($end != '') {
		$sql .= ' AND pnotes.date <= ?';
		$binds[] = $end . ' 23:23:59';
	}
	$sql .= ' GROUP BY pnotes.id';
  if(!empty($sortby)) $sql .= ' ORDER BY '.add_escape_custom($sortby);
  if(!empty($sortorder))$sql .= ' '.add_escape_custom($sortorder);
	if(!empty($begin) || !empty($listnumber)) {
		$sql .= ' LIMIT ';
		if($begin) $sql .= add_escape_custom($begin);
		if($listnumber) {
			if($begin) $sql .= ', ';
			$sql .= add_escape_custom($listnumber);
		}
  }
  $result = sqlStatement($sql, $binds);

  // return the results
  if ($count) {
    if(sqlNumRows($result) != 0) {
        $total = sqlNumRows($result);
    } else {
        $total = 0;
    }
    return $total;
  } else {
    return $result;
  }
}

function GetNextGroupUser($grp='') {
	if($grp == '') return false;
	$ures = sqlStatement('SELECT msg_group_link.*, msg_status.status, '.
		'list_options.notes FROM '.
		'msg_group_link LEFT JOIN msg_status USING (user_id) lEFT JOIN '.
		'list_options ON (list_id="msg_status" AND option_id=msg_status.status) '.
		'WHERE group_id=? ORDER BY user_id', array($grp));
	$grp_users = array();
	while($urow = sqlFetchArray($ures)) {
		if(strpos(strtolower($urow{'notes'}),'no alert') !== false) {
			$grp_users[] = $urow{'user_id'};
		}
	}
	$res = sqlQuery('SELECT * FROM msg_round_robin WHERE group_id=?',array($grp));
	$last_user = $res{'user_id'};
	$this_user = false;
	if(isset($grp_users[0])) $this_user = $grp_users[0];
	$pos = false;
	if($last_user) $pos = array_search($last_user, $grp_users);
	if($pos === false) {
		$res = sqlQuery('SELECT * FROM users WHERE id=?', array($this_user));
		if(!$res{'username'}) return false;
		sqlInsert('REPLACE INTO msg_round_robin SET timestamp=NOW(), user_id=?, '.
				'group_id=?',array($this_user, $grp));
		return $res{'username'};
	} else {
		if(($pos + 1) < count($grp_users)) {
			$pos++;
		} else {
			$pos = 0;
		}
		$this_user = $grp_users[$pos];
		$res = sqlQuery('SELECT * FROM users WHERE id=?', array($this_user));
		if(!$res{'username'}) return false;
		sqlInsert('REPLACE INTO msg_round_robin SET timestamp=NOW(), user_id=?, '.
				'group_id=?',array($this_user, $grp));
		return $res{'username'};
	}
}

}

?>
