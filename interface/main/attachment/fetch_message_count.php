<?php

include_once("../../globals.php");

$direction = isset($_REQUEST['direction']) ? $_REQUEST['direction'] : "";
$msg_from = isset($_REQUEST['msg_from']) ? $_REQUEST['msg_from'] : "";
$msg_to = isset($_REQUEST['msg_to']) ? $_REQUEST['msg_to'] : "";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";

$responce = array(
	'count' => 0
);

$msg_from = str_replace("'", "\\'", $msg_from);
$msg_to = str_replace("'", "\\'", $msg_to);


if($action == "similer_records") {
	$sql = "SELECT COUNT(mlp.`id`) as count ".
 			"FROM message_log mlp WHERE (mlp.`pid` IS NULL OR mlp.`pid` = '') ";
 	if($direction == "in") {
 		$sql .= "AND (( mlp.`msg_from` = '".$msg_from."' OR mlp.`msg_to` = '".$msg_from."')) ";
 	} else if($direction == "out") {
 		$sql .= "AND (( mlp.`msg_from` ='".$msg_to."' OR mlp.`msg_to` = '".$msg_to."')) ";
 	}
 	$sql .= "AND NOT mlp.`id` = '".$id."' ";
	$resData = sqlQuery($sql);
	
	if(!empty($resData)) {
		$responce = $resData;
	}
} else if($action == "similer_active_records") {
	$sql = "SELECT COUNT(mlp.`id`) as count ".
 			"FROM message_log mlp WHERE mlp.`activity` = '1' ";

 	if($direction == "in") {
 		$sql .= "AND (mlp.`msg_from` = '".$msg_from."' ) ";
 	} else if($direction == "out") {
 		$sql .= "AND (mlp.`msg_from` = '".$msg_to."') ";
 	}
 	$sql .= "AND NOT mlp.`id` = ".$id." ";
	$resData = sqlQuery($sql);
	
	if(!empty($resData)) {
		$responce = $resData;
	}
}


echo json_encode($responce);
?>