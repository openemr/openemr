<?php

include_once("../interface/globals.php");
include_once("$srcdir/log.inc");
include_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");


	$valid  = true;
	$errors = array(); 
	catch_logs();
	$sql = sqlStatementNoLog("select *from log_validator");
	while($row = sqlFetchArray($sql)){
		$logEntry = sqlQuery("select * from log where id = ".$row['log_id']);
		if(empty($logEntry)){
			$valid = false;
			array_push($errors, "Audit log entry #".$row['log_id']." is missing");
		}
		else if($row['log_checksum'] != $logEntry['checksum']){
			$valid = false;
			array_push($errors,"Audit log tampering evident at entry #".$row['log_id']);
		}
		if(!$valid) break;
	}
	if($valid){
		echo "Audit Log Validated Successfully";
	}
	else 
	{
		echo "Audit Log validation failed(ERROR:: $errors[0])";
	}
	
	function catch_logs(){
		$sql  = sqlStatementNoLog("select *from log where id not in(select log_id from log_validator) and checksum is NOT null and checksum != ''");
		while($row = sqlFetchArray($sql)){
			$insert = "INSERT into log_validator(log_id,log_checksum) VALUES(".$row['id'].",'".$row['checksum']."')";
			sqlInsertClean_audit($insert);
		}
	}
?>
