Contrib Forms Checklist				
========================
To replace mysql_insert_id with the following changes. 

Whenever audit is enabled, the GLOBALS['lastidado'] set by the audit 
 can be used for determining lastid, because it stores the 
 correct mysql_insert_id before the audit call

Example:

	if($GLOBALS['lastidado'] > 0)
		$last_id = $GLOBALS['lastidado'];
	else
		$last_id = mysql_insert_id($GLOBALS['dbh']); 
