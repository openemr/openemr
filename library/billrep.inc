<?php
	require_once("{$GLOBALS['srcdir']}/sql.inc");

	//date must be in nice format (e.g. 2002-07-11)

//	function getBillsBy($date, $qtype = "day", $auth="%", $cols = "date,pid,code_type,code,user,authorized")
//	{
//		if ($qtype == "all") {
//			$res = sqlStatement("select $cols from billing order by date ASC");
//		}
//		elseif ($qtype == "day") {
//			$res = sqlStatement("select $cols from billing where date like '$date' order by date ASC");
//		}
//		elseif ($qtype == "month") {
//			$m = substr($date,5,2);
//			$y = substr($date,0,4);
//			$res = sqlStatement("select $cols from billing where MONTH(date) = '$m' and YEAR(date) = '$y' ORDER by date ASC");
//		}
//		elseif ($qtype == "year") {
//			$y = substr($date,0,4);
//			$res = sqlStatement("select $cols from billing where YEAR(date) = '$y' ORDER by date ASC");
//		}
//
//		for($iter=0; $row=sqlFetchArray($res); $iter++)
//		{
//			$all[$iter] = $row;
//		}
//
//		return $all;
//	}

	function getBillsBetween($date, $date2, $auth="%", $unbilled, $code_type,
		$cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id")
	{
		$billstring = "billing.billed = '0'";
		if ($unbilled == "0") {
			//3 is an error condition
			$billstring = "billing.billed = '0' or (billing.billed = '1' and billing.bill_process = '3')";
		}
		else {
			$billstring = "billing.billed = '1'";
		}

//	$sql = "select * from billing where date >= '$date' and " .
//		"date <= '$date2 23:59:59' and authorized like '$auth' and " .
//		"($billstring) and code_type like '$code_type' and activity = 1 " .
//		"order by encounter, pid, code_type, code ASC";

		// Selecting by the date in the billing table is wrong, because that is
		// just the data entry date; instead we want to go by the encounter date
		// which is the date in the form_encounter table.
		//
		/****
		$sql = "select form_encounter.date as enc_date, billing.* " .
			"from form_encounter, billing where " .
			"form_encounter.date >= '$date' and " .
			"form_encounter.date <= '$date2 23:59:59' and " .
			"billing.encounter = form_encounter.encounter and " .
			"billing.authorized like '$auth' and " .
			"($billstring) and " .
			"billing.code_type like '$code_type' and " .
			"billing.activity = 1 " .
			"order by billing.encounter, billing.pid, billing.code_type, billing.code ASC";
		****/

		$sql = "SELECT fe.date AS enc_date, fe.pid AS enc_pid, " .
			"fe.encounter AS enc_encounter, billing.* " .
			"FROM form_encounter AS fe " .
			"LEFT OUTER JOIN billing ON " .
			"billing.encounter = fe.encounter AND " .
			"billing.pid = fe.pid AND " .
			"billing.authorized like '$auth' AND " .
			"( $billstring ) AND " .
			"billing.code_type LIKE '$code_type' AND " .
			"billing.activity = 1 " .
			"WHERE " .
			"fe.date >= '$date' AND " .
			"fe.date <= '$date2 23:59:59' " .
			"ORDER BY fe.encounter, fe.pid, billing.code_type, billing.code ASC";

		$res = sqlStatement($sql);

		for($iter=0; $row=sqlFetchArray($res); $iter++)
		{
			$all[$iter] = $row;
		}

		return $all;
	}

	function getBillsListBetween($date, $date2, $auth="%", $unbilled, $code_type,
		$cols = "billing.id, form_encounter.date, billing.pid, billing.code_type, billing.code, billing.user")
	{
		$billstring = "billed = '0'";
		if ($unbilled == "0") {
			//3 is an error condition
			$billstring = "billed = '0' or (billed = '1' and bill_process = '3')";
		}
		else {
			$billstring = "billed = '1'";
		}

//	$sql = "select $cols from billing where date >= '$date' and date <= '$date2 23:59:59' and authorized like '$auth' and ($billstring) and code_type like '$code_type' and activity = 1 order by pid,date ASC";

		// See above comment in getBillsBetween().
		//
		$sql = "select $cols " .
			"from form_encounter, billing where " .
			"form_encounter.date >= '$date' and " .
			"form_encounter.date <= '$date2 23:59:59' and " .
			"billing.encounter = form_encounter.encounter and " .
			"billing.authorized like '$auth' and " .
			"($billstring) and " .
			"billing.code_type like '$code_type' and " .
			"billing.activity = 1 " .
			"order by billing.pid, billing.date ASC";

		$res = sqlStatement($sql);
		//print "select $cols from billing where date >= '$date' and date <= '$date2 23:59:59' and authorized like '$auth' and billed like '$unbilled' and code_type like '$code_type' order by pid,date ASC";
		$string = "( ";
		for($iter=0; $row=sqlFetchArray($res); $iter++)
		{
			$string .= $row{"id"}.",";
		}
		$string = substr($string,0,strlen($string)-1);
		$string .= ")";
		return $string;
	}

// function billCodesBetween($date, $date2, $auth="%", $code_type)
// {
// 	sqlStatement("update billing set billed=1 where date >= '$date' and date <= '$date2 23:59:59' and authorized like '$auth' and code_type like '$code_type'");
// }

	function billCodesList($list,$skip = "()") {
		if ($list == "()")
			return;

		if ($skip == "()")
			sqlStatement("update billing set billed=1 where id in $list");
		else
			sqlStatement("update billing set billed=1 where id in $list and id not in $skip");

		return;
	}

//	function getCodeTypes () {
//		$res = sqlStatement("select distinct code_type from billing order by code_type");
//
//		for($iter=0; $row=sqlFetchArray($res); $iter++)
//		{
//			$all[$iter] = $row;
//		}
//
//		return $all;
//	}

?>
