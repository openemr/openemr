<?php

require_once("../../../globals.php");

$users = isset($_REQUEST['user']) ? $_REQUEST['user'] : array();
$uGroup = !empty($users) ? explode(":", $users) : array();

if(!empty($uGroup) && $uGroup[0] != "GRP") {
	echo json_encode(array(
		'status' => true,
		'isGroup' => false,
		'data' =>  ''
	));
	exit();
}

$userGroup = !empty($uGroup) && $uGroup[0] == "GRP" ? $uGroup[1] : "";
$responce = array();


if(!empty($userGroup)) {
	$sResult = sqlQuery("SELECT count(msg_group_link.id) as total_result FROM `msg_group_link` JOIN `users` ON users.id  = msg_group_link.user_id WHERE group_id = ?  LIMIT 1", array($userGroup));

	$responce = array(
		'status' => true,
		'isGroup' => true,
		'data' =>  isset($sResult['total_result']) ? $sResult['total_result'] : 0
	);

} else {
	$responce = array(
		'status' => false,
		'isGroup' => true,
		'data' =>  ''
	);
}
echo json_encode($responce);