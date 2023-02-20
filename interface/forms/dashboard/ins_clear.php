<?php
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');

// GRAB ALL THE PLANS
$sql = "SELECT id FROM insurance_companies WHERE 1";
$mres = sqlStatement($sql);

while($mrow = sqlFetchArray($mres)) {
	$id = $mrow{'id'};
	$cnt = sqlQuery("SELECT COUNT(*) FROM ar_session WHERE payer_id = ?",array($id));
	echo "Session Result: ";
	print_r($cnt);
	echo "<br>\n";
	if($cnt{'COUNT(*)'}) continue;

	$cnt = sqlQuery("SELECT COUNT(*) FROM billing WHERE payer_id = ?",array($id));
	echo "Billing Result: ";
	print_r($cnt);
	echo "<br>\n";
	if($cnt{'COUNT(*)'}) continue;

	$cnt = sqlQuery("SELECT COUNT(*) FROM claims WHERE payer_id = ?",array($id));
	echo "Claims Result: ";
	print_r($cnt);
	echo "<br>\n";
	if($cnt{'COUNT(*)'}) continue;

	$cnt = sqlQuery("SELECT COUNT(*) FROM insurance_data WHERE provider = ?",array($id));
	echo "Plan Result: ";
	print_r($cnt);
	echo "<br>\n";
	if($cnt{'COUNT(*)'}) continue;

	echo "Deleting This One ($id)<br>\n";
	sqlStatement("DELETE FROM insurance_companies WHERE id=?",array($id));
	sqlStatement("DELETE FROM addresses WHERE foreign_id=?",array($id));
	
}

?>
