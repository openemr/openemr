<?php

function getAllBilledCPT($thisPid)
{
  $sql = "SELECT * FROM billing WHERE pid=? AND billed=1 ORDER BY encounter";
	$all=array();
  $res = sqlStatement($sql, array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getAllUnbilledCPT($thisPid)
{
  $sql = "SELECT * FROM billing WHERE pid=? AND billed=0 ORDER BY encounter";
	$all=array();
  $res = sqlStatement($sql, array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getAllCPT($thisPid)
{
  $sql = "SELECT * FROM billing WHERE pid=? ORDER BY encounter";
	$all=array();
  $res = sqlStatement($sql, array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getAllCPTbyEncounter($thisPid, $enc=0)
{
  $sql = "SELECT * FROM billing WHERE pid=? AND encounter=?";
	$all=array();
  $res = sqlStatement($sql, array($thisPid, $enc));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getUnbilledCPTbyEncounter($thisPid, $enc=0)
{
  $sql = "SELECT * FROM billing WHERE pid=? AND encounter=? AND billed=0";
	$all=array();
  $res = sqlStatement($sql, array($thisPid, $enc));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

function getBilledCPTbyEncounter($thisPid, $enc=0)
{
  $sql = "SELECT * FROM billing WHERE pid=? AND encounter=? AND billed=1";
	$all=array();
  $res = sqlStatement($sql, array($thisPid, $enc));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
	$all[] = $row;
  return $all;
}

$clist= array();
$cres= sqlStatement("SELECT codes.*, prices.pr_price, list_options.title ".
		"AS category FROM codes LEFT JOIN prices ON codes.id = prices.pr_id ".
		"LEFT JOIN list_options ON (codes.superbill = list_options.option_id AND ".
		"list_options.list_id = 'superbill') ".
		"WHERE code_type=1 AND active=1 AND superbill != '' ".
		"ORDER BY category, code");
while($crow= sqlFetchArray($cres)) {
	$clist[]= $crow;
}

?>
