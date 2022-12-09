<?php 

function getFamilyMembers($thisPid)
{
  $sql = "SELECT * FROM form_psy_fam WHERE pid=? ORDER BY pf_relationship";
	$all=array();
  $res = sqlStatement($sql,array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function getChildren($thisPid)
{
  $sql = "SELECT * FROM form_psy_child WHERE pid=? ORDER BY pc_relationship";
	$all=array();
  $res = sqlStatement($sql,array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

?>
