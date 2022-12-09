<?php
include_once("../../globals.php");
include_once("{$GLOBALS['srcdir']}/sql.inc");
include_once("{$GLOBALS['srcdir']}/billing.inc");

function wmtFormSubmit ($tableName, $values, $id, $authorized = "0", $thisPid)
{
	$authorized = $_SESSION['userauthorized'];
	$binds = array($thisPid, $_SESSION['authProvider'], $_SESSION['authUser'],
			$_SESSION['userauthorized']);
	$sql = "INSERT INTO $tableName (pid, groupname, user, authorized, ".
		"activity";
	$parms = ") VALUES (?, ?, ?, ?, 1";
	foreach ($values as $key => $value) {
		if (strpos($key,"openemr_net_cpt") === 0) {
			//code to auto add cpt code
			if (!empty($value)) {
				$code_array = explode(" ",$value,2);
				addBilling(date("Ymd"), 'CPT4', $code_array[0], $code_array[1], 
						$thisPid, $authorized, $_SESSION['authUserID']);
			}
		}
		//case where key looks like "[a-zA-Z]*diagnosis[0-9]" which is special, it is used to auto add ICD codes
		
		elseif (strpos($key,"diagnosis") == (strlen($key) -10) && !(strpos($key,"diagnosis")=== false )) {
			//icd auto add ICD9-CM
			if (!empty($value)) {
				$code_array = explode(" ",$value,2);
				addBilling(date("Ymd"), 'ICD9-M', $code_array[0], $code_array[1],
						 $thisPid, $authorized, $_SESSION['authUserID']);
			}
		}
		else {
			$sql .= ", $key";
			$parms .= ", ?";
			$binds[] = $value;
		}
	}
	$sql = $sql . ", `date`" . $parms . ", NOW())";
	/***
	echo "Insert: $sql<br>\n";
	echo "Binds: ";
	print_r($binds);
	echo "<br>\n";
	***/
	return sqlInsert($sql, $binds);
}

function wmtFormFetch ($tableName, $thisId, $thisPid, $cols="*", $activity="1")
{
	$sql = "SELECT $cols FROM `$tableName` WHERE id=? AND ".
		"pid=? AND activity LIKE '$activity' ORDER BY date DESC LIMIT 0,1";
	$binds = array($thisId, $thisPid);
	return sqlQuery($sql, $binds);
}

?>
