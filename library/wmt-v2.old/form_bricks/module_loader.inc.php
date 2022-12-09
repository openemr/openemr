<?php
if(!isset($form_mode)) $form_mode = 'print';
$sql = '';
unset($binds);
$binds = array();
unset($md);
$md = array();

if($frmdir == $this_module) {
	// THE MODULE WAS CALLED AS A STAND-ALONE IN THE PARENT FORM
	// THIS LOADING IS DONE IN THE form_process.inc.php OF THE MAIN FORM LOOP
	$dt[$this_module . '_id' ] = '';

} else {
	if($form_mode == 'new' && $first_pass) {
		// IS THERE ALREADY AN ENCOUNTER LEVEL FORM?
		$old = sqlQuery('SELECT id FROM ' . $this_table . ' WHERE link_id = ? '.
			'AND link_form = ? AND pid = ?',array($encounter, 'encounter', $pid));
		if(!isset($old{'id'})) $old{'id'} = '';
		// IS THE SYSTEM SET TO LOAD A HISTORICAL ENTRY?
		if(!$old{'id'} && !$nohist) {
			$old = sqlQuery('SELECT id FROM ' . $this_table . ' WHERE pid = ? '.
				'ORDER BY `date` DESC LIMIT 1',array($pid));
			if(!isset($old{'id'})) $old{'id'} = '';
		}
		if($old{'id'}) {
			$sql = 'SELECT * FROM ' . $this_table . ' WHERE id = ? AND pid = ?';
			$binds = array($old{'id'}, $pid);
		}
	} else {
		// GET ENCOUNTER LEVEL FORM
		$sql = 'SELECT * FROM ' . $this_table . ' WHERE link_id = ? AND ' . 
			'link_form = ? AND pid = ?';
		$binds = array($encounter, 'encounter', $pid);
	}

	if($sql) $md = sqlQuery($sql, $binds);
	if($md) {
		$dt[$this_module . '_id'] = $md['id'];
		$md = array_slice($md, 14);
		foreach($md as $key => $val) {
			if($key == 'form_dt') continue;
			$dt[$key] = $val;
		}
	} else {
		$md = sqlListFields($this_table);
		$md = array_slice($md, 14);
		foreach($md as $key) {
			if($key == 'form_dt') continue;
			$dt[$fld] = '';
		}
		$dt[$this_module . '_id' ] = '';
	}
}
?>
