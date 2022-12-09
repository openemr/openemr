<?php

function GetEncounterConcepts($thisPid, $thisEnc, $order='', $cols='*') {
  $sql = "SELECT $cols FROM form_or_track1 WHERE pid=? AND or1_enc=?";
	$all = array();
  $res = sqlStatement($sql, array($thisPid, $thisEnc));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function GetEncounterConceptsByTime($thisPid, $thisEnc, $order='', $cols='*') {
  $sql = "SELECT $cols FROM form_or_track1 WHERE pid=? AND or1_enc=? ".
				"ORDER BY or1_time $order";
	$all = array();
  $res = sqlStatement($sql, array($thisPid, $thisEnc));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function UpdateConcept($id,$time,$concept,$tool='',$part='',$supply='',$desc='',$ben='') {
	if(!$id) return false;
	// Update the provided ID 
	$sql = "UPDATE form_or_track1 SET date=NOW(), user=?, or1_time=?, ".
		"or1_concept=?, or1_tool=?, or1_part=?, or1_supply=?, or1_description=?, ".
		"or1_benefit=? WHERE id=?";
	$parms= array($_SESSION['authUserID'], $time, $concept, $tool, $part,
			$supply, $desc, $ben, $id);
	// print_r($parms);
	$test = sqlStatement($sql, $parms);
}

?>
