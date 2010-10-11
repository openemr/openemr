<?php
include_once("../../globals.php");
include_once("$srcdir/forms.inc");

//function called to set the global session variable for encounter number
function setencounter($enc) {
	
	$return_val = 1;
	global $encounter;
	global $pid;
	
	if ($enc == "" ) {
		$enc = date("Ymd");
		if (getFormByEncounter($pid,$enc)) {
			//there is an encounter enterred for today
		} else {
			//addForm($enc, "New Patient Encounter", 0, $pid, 1);
			$return_val = 0;
		}
	}
	
	$_SESSION['encounter']=$enc;
	$encounter=$enc;
	
	
	//returns 1 on successful global set, or 0 if there was no
	//current encounter, signifying that the interface should load
	//the screen for a new encounter
	return $return_val;
}

?>

