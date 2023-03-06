<?php
global $GLOBALS_NTF;

$GLOBALS_NTF = array();

/*
Environment Mode
Production = production
Test = test
*/
$GLOBALS_NTF['mode'] = "test";

/*Set test email id*/
$GLOBALS_NTF['email'] = "developer1.logilite@gmail.com";

/*Set test phone*/
$GLOBALS_NTF['phone'] = "5136598460";

/*Set Fax Data*/
$GLOBALS_NTF['fax_number'] = "112233";

/*Set postal address*/
$GLOBALS_NTF['postal_address'] = array(
			    'street' => "1310 Scenic Knl",
			    'street1' => "nr.",
			    'city' => "San Antonio",
			    'state' => "Texas",
			    'postal_code' => "78258",
			    'country' => "United States",
			);

?>
