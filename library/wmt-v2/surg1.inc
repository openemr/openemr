<?php

function CptGroupSel($thisCode, $type=0)
{
	$rlist = sqlStatement("SELECT code_text, code_text_short, code, code_type ".
		"FROM codes WHERE code_type=1 AND active=1 AND superbill=? ".
		"ORDER BY code, code_text", array($type));
  echo "<option value=''";
  if(!$thisCode) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow['code'] . "'";
    if($thisCode == $rrow['code']) echo " selected='selected'";
		if($rrow['code_text_short']) {
    	echo ">" . $rrow['code'] . ' - ' . $rrow['code_text_short'];
		} else {
    	echo ">" . $rrow['code'] . ' - ' . substr($rrow['code_text'],0,35);
		}
    echo "</option>";
  }
}

function CptGroupLook($thisCode, $type=0)
{
	if(!$thisCode) { return ''; }
	$rlist = sqlStatement("SELECT code_text, code_text_short, code, code_type ".
		"FROM codes WHERE code_type=1 AND active=1 AND code=? ", array($thisCode));
  $rrow= sqlFetchArray($rlist);
	if($rrow['code_text_short']) { 
		$thisCode .= ' - '.$rrow['code_text_short'];
	} else if($rrow['code_text']) {
   	$thisCode .= ' - '.substr($rrow['code_text'],0,35);
	}
	return $thisCode;
}

function SetupSurgAction($dt, $done, $sched_action = 'sa', $pend_action = 'ref_pend', $rcv_action = 'ref_rcv', $delay=7) {
		$stat = 'p';
		$action = $sched_action;
		if(strtolower($dt) == 'yyyy-mm-dd') { $dt = ''; }
		if($dt) { $action = $pend_action; }
		if(!$dt) {
			$dt = date('Y-m-d', mktime(0,0,0,date('m'),date('d')+$delay,date('Y')));
			$dt = fixDate($dt);
		}
		if($done) { 
			$stat = 'rr';
			$action = $rcv_action;
		}
	$ret = array('target_dt' => $dt, 'new_stat' => $stat, 'new_action' => $action);	
	return($ret);
}
?>
