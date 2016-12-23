<?php
/**
 * library/log_validation.php to validate audit logs tamper resistance.
 *
 * Copyright (C) 2016 Visolve <services@visolve.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Visolve <services@visolve.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../interface/globals.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");


	$valid  = true;
	$errors = array();
	catch_logs();
	$sql = sqlStatement("select * from log_validator");
	while($row = sqlFetchArray($sql)){
		$logEntry = sqlQuery("select * from log where id = ?",array($row['log_id']));
		if(empty($logEntry)){
			$valid = false;
			array_push($errors, xl("Following audit log entry number is missing") . ": " . $row['log_id']);
		}
		else if($row['log_checksum'] != $logEntry['checksum']){
			$valid = false;
			array_push($errors, xl("Audit log tampering evident at entry number") . " " . $row['log_id']);
		}
		if(!$valid) break;
	}
	if($valid){
		echo xl("Audit Log Validated Successfully");
	}
	else
	{
		echo xl("Audit Log Validation Failed") . "(ERROR:: $errors[0])";
	}

	function catch_logs(){
		$sql  = sqlStatement("select * from log where id not in(select log_id from log_validator) and checksum is NOT null and checksum != ''");
		while($row = sqlFetchArray($sql)){
			sqlInsert("INSERT into log_validator (log_id,log_checksum) VALUES(?,?)",array($row['id'],$row['checksum']));
		}
	}
?>
