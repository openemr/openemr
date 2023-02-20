<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc");

$sql = "SELECT * FROM form_rto WHERE rto_target_date IS NULL OR rto_target_date=''";
$mres = sqlStatement($sql);
while($mrow = sqlFetchArray($mres)) {
	$id= $mrow{'id'};
	$num= $mrow{'rto_num'};
	$frame= $mrow{'rto_frame'}; 
	$test_dt=substr($mrow{'rto_date'},0,10);
	$target= $test_dt;
	echo "Processing PID: ",$mrow{'pid'},"   Date: $test_dt  Old Target: ",
			$mrow{'rto_target_date'},"   Frame: $frame  Num: $num<br/>\n";
	$new_dt= new DateTime($test_dt);
	if(!$new_dt) {
		$new_dt= new DateTime(substr($mrow{'date'},0,10));
	}
	if(!$new_dt) {
		echo "** FAILURE ** Could NOT Create a New Date</br>\n";
	} else {
		if($frame && $num) {
			$mult= strtoupper($frame);
			$num= 'P'.$num.$mult;
			$new_dt->add(new DateInterval($num));
			$target= $new_dt->format('Y-m-d');
		}
	}
	echo "Updating Target Date to: $target</br>\n";
	$sql = "UPDATE form_rto SET rto_target_date='$target' WHERE id=$id";
	sqlStatement($sql);
}

?>
