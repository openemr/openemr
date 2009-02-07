<?php
/*
 *	PHPGACL Millennium Falcon ACL fineGrainAccessControl.php
 *
 *	Fine-grain access Control. (manual p.8-9)
 *
 * The ACL tree for this example should start as:
 * Millennium Falcon Passengers Group
 * |-Crew Group			[ALLOW: ALL]
 * | |-Han ARO
 * | '-Chewie ARO
 * '-Passengers Group	[ALLOW: Lounge]
 *   |-Obi-wan ARO
 *   |-Luke ARO
 *   |-R2D2 ARO
 *   '-C3PO ARO
 *
 * The ACL tree at the end of this example will be:
 * Millennium Falcon Passengers Group
 * |-Crew Group			[ALLOW: ALL]
 * | |-Han ARO
 * | '-Chewie ARO		[DENY: Engines]
 * '-Passengers Group	[ALLOW: Lounge]
 *   |-Obi-wan ARO
 *   |-Luke ARO			[ALLOW: Guns]
 *   |-R2D2 ARO			[ALLOW: Engines]
 *   '-C3PO ARO
 */

/*
 * Initialise the database - by clearing and running the previous examples.
 */

// Let's get ready to RUMBLE!!!
$gacl_api = new gacl_api($gacl_options);

/*
 * Keep in mind, all of this can be done through the Administration Interface via your browser.
 */

/*
 * Deny Chewie Access to the Engines!
 */
//Associative array, with Object Section Value => array( Object Value ) pairs. 
$aco_array = array('access' => array('engines') );
$aro_array = array('crew' => array('chewie') );
$allow = FALSE;
$enabled = TRUE;
$return_value = NULL;
$note = "Denying Chewie access to the engines!";

//The NULL values are for the more advanced options such as groups, and AXOs. Refer to the manual for more info.
$result = $gacl_api->add_acl($aco_array, $aro_array, NULL, NULL, NULL, $allow, $enabled, $return_value, $note);
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Chewie has been denied access to the Engines!<br>\n";
	} else {
		echo "Error creating ACL.<br>\n";
	}
	echo "<br>\n";
	echo "=================================================================================================<br>\n";
	echo "-- Lets test the new ACL for Chewie! --<br>\n";
	echo "=================================================================================================<br>\n";
}
// Lets check if Chewie has access to the engines
if ( $gacl_api->acl_check('access', 'engines', 'crew', 'chewie') ) {
	if ($outputDebug == TRUE){
		echo "Chewie still has access to the engines!<br>\n";	
	}
} else {
	if ($outputDebug == TRUE){
		echo "Chewie has been denied access to the engines! (Han saves the hyperdrive from further distress!)<br>\n";	
	}
}
// Lets check if Chewie still has access to the cockpit
if ( $gacl_api->acl_check('access', 'cockpit', 'crew', 'chewie') ) {
	if ($outputDebug == TRUE){
		echo "And Chewie still has access to the cockpit! (Hans plan worked!)<br>\n";	
	}
} else {
	if ($outputDebug == TRUE){
		echo "Chewie has been denied access to the cockpit! (Not good - somethings not right!)<br>\n";	
	}
}

if ($outputDebug == TRUE){
	echo "<br>\n";
	echo "=================================================================================================<br>\n";
	echo "-- Under Attack - Allow Luke Access to the Guns and R2D2 to the Engines! --<br>\n";
	echo "=================================================================================================<br>\n";
}
/*
 * Allow Luke Access to the Guns!
 */
//Associative array, with Object Section Value => array( Object Value ) pairs. 
$aco_array = array('access' => array('guns') );
$aro_array = array('passengers' => array('luke') );
$allow = TRUE;
$enabled = TRUE;
$return_value = NULL;
$note = "Allowing Luke access to the guns!";

//The NULL values are for the more advanced options such as groups, and AXOs. Refer to the manual for more info.
$result = $gacl_api->add_acl($aco_array, $aro_array, NULL, NULL, NULL, $allow, $enabled, $return_value, $note);
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Luke has been granted access to the Guns!<br>\n";
	} else {
		echo "Error creating ACL - Luke can't get to the Guns.<br>\n";
	}
}

/*
 * Allow R2D2 Access to the Engines!
 */
//Associative array, with Object Section Value => array( Object Value ) pairs. 
$aco_array = array('access' => array('engines') );
$aro_array = array('passengers' => array('r2d2') );
$allow = TRUE;
$enabled = TRUE;
$return_value = NULL;
$note = "Allowing R2D2 access to the engines!";

//The NULL values are for the more advanced options such as groups, and AXOs. Refer to the manual for more info.
$result = $gacl_api->add_acl($aco_array, $aro_array, NULL, NULL, NULL, $allow, $enabled, $return_value, $note);
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "R2D2 has been granted access to the Engines!<br>\n";
	} else {
		echo "Error creating ACL - R2D2 can't get to the Engines! (we're doomed says C3PO!).<br>\n";
	}
}

if ($outputDebug == TRUE){
	echo "<br>\n<br>\nDone! Now how easy was that? <br>\n";
	echo "Remember to check out the <a href='../../../admin/acl_list.php'>Administration Interface</a> which can do all of the above in a few simple clicks.<br>\n<br>\n";
}
?>
