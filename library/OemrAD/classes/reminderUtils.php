<?php

require_once(__DIR__ . "/../interface/globals.php");
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");

function sqlStatementNoLogExecute($statement, $binds = false){
  	try {
	  	// Below line is to avoid a nasty bug in windows.
	    if (empty($binds)) {
	        $binds = false;
	    }

	  	// Use adodb ExecuteNoLog with binding and return a recordset.
	    $recordset = $GLOBALS['adodb']['db']->ExecuteNoLog($statement, $binds);
	    if ($recordset === false) {
	    	throw new Exception("query failed: $statement Error: " . getSqlLastError());
	        //HelpfulDie("query failed: $statement", getSqlLastError());
	    }
	} catch(Exception $e) {
		throw new Exception($e->getMessage());
		return false;
	}

    return $recordset;
}

function logNotificationData($type, $log_id = '', $message = '', $isError = 0) {
	if(!empty($type)) {
		sqlInsert("INSERT INTO `vh_cron_log` (type, cron_id, value, error) VALUES (?, ?, ?, ?) ", array($type, $log_id, $message, $isError));
	}
}