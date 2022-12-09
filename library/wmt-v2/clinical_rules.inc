<?php
function  meetClinicalTarget($pid, $data) {
	if(!$pid) ReportMissingPID();
	if(!isset($data['category'])) $data['category'] = '';
	if(!isset($data['item'])) $data['item'] = '';
	if(!isset($data['complete'])) $data['complete'] = 'YES';
	if(!$data['complete']) $data['complete'] = 'YES';
	if(!isset($data['result'])) $data['result'] = 'See Clinical Record';
	if(!$data['result']) $data['result'] = 'See Clinical Record';
	if(!isset($data['date'])) $data['date'] = date('Y-m-d  H:i:s');
	if(!$data['date']) $data['date'] = date('Y-m-d  H:i:s');
	if(!$data['item']) return false;
	// GET THE MOST RECENT RULE FOR THIS ACTION ITEM FROM THE PATIENT EVENTS
	$sql = "SELECT * FROM `rule_patient_data` WHERE `pid` = ? AND `item` = ? ";
	$binds = array($pid,$data['item']);
	if($data['category']) {
		$sql .= "AND `category` = ? ";
		$binds[] = $data['category'];
	}
	$sql .= "AND `complete` = ? ORDER BY `date` DESC LIMIT 1";
	$binds[] = $data['complete'];
	$rrow = sqlQuery($sql, $binds);
	if(!isset($rrow{'date'})) $rrow{'date'} = '';
	$recent_date = $rrow{'date'};

	// IF THE EXISTING EVENT DATE IS OLDER, INSERT THE NEW ONE
	if(substr($recent_date,0,10) < substr($data['date'],0,10)) {
		$sql = "SELECT * FROM rule_action_item WHERE item=? ".
					"AND custom_flag=?";
		$rres = sqlStatementNoLog($sql,array($data['item'],'1'));
		while($rrow = sqlFetchArray($rres)) {
			if(!$data['category'] ||($data['category'] == $rrow['category'])) {
				$sql = "INSERT INTO rule_patient_data (date, pid, category, item, ".
							"complete, result) VALUES (?, ?, ?, ?, ?, ?)";
				$binds = array($data['date'], $pid, $rrow{'category'}, 
						$rrow{'item'}, $data['complete'], $data['result']);
				$tst = sqlInsert($sql, $binds);
			}
		}
	} else {
		return -1;
	}
	return $tst;
}

?>
