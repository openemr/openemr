<?php
/*
 *	PHPGACL Millennium Falcon ACL Multi-levelGroups.php
 *
 *	Multi-level Groups. (manual p.9)
 *
 * The ACL tree for this example should start as:
 * Millennium Falcon Passengers Group
 * |-Crew Group			[ALLOW: ALL]
 * | |-Han ARO
 * | '-Chewie ARO		[DENY: Engines]
 * '-Passengers Group	[ALLOW: Lounge]
 *   |-Obi-wan ARO
 *   |-Luke ARO			[ALLOW: Guns]
 *   |-R2D2 ARO			[ALLOW: Engines]
 *   '-C3PO ARO
 *
 * The ACL tree at the end of this example will be:
 * Millennium Falcon Passengers Group
 * |-Crew Group			[ALLOW: ALL]
 * | |-Han ARO
 * | '-Chewie ARO		[DENY: Engines]
 * '-Passengers Group	[ALLOW: Lounge]
 *   |- Jedi			[ALLOW: Cockpit]
 	 |	|-Obi-wan ARO
 *   |	'-Luke ARO		[ALLOW: Guns]
 *   |-R2D2 ARO			[ALLOW: Engines]
 *   '-C3PO ARO
 *
 */

// Get the phpGACL option settings
require_once('millenniumFalcon.inc');

/*
 * Initialise the database - by clearing and running the previous examples.
 */

// Let's get ready to RUMBLE!!!
$gacl_api = new gacl_api($gacl_options);

/*
 * Keep in mind, all of this can be done through the Administration Interface via your browser.
 */

/*
 * To keep things clear for this stage the process is as follows:
 *
 * 1) Add a Jedi ARO Section.
 * 2) Add the ARO Group for Jedi, with the parent group being Passengers
 * 3) Edit Obi-wan and Lukes Object to assign the Section to Jedi.
 * 4) Assign ACL to Jedi ARO Group.
 * 5) Test!
 */

/*
 * Add an ARO Section for Jedi - so we can assign Passengers to this section.
 */
$result = $gacl_api->add_object_section('Jedi', 'jedi', 12, 0, 'ARO'); //Must specifiy Object Type, notice it is ARO now.
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created Jedi ARO section sucessfully. <br>\n";
	} else {
		echo "Error creating Jedi ARO section.<br>\n";
	}
}
unset($result);

/*
 * Add Jedi Group to Passengers
 *
 * First get the Passengers Groupid.
 */
$result = $gacl_api->get_group_id('passengers');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Got the groupid for Passengers!<br>\n";
	} else {
		echo "Error failed getting the groupid for passengers.<br>\n";
	}
}
$passengersGroupID = $result;
unset($result);

/* 
 * We add the Jedi Group and use the Passengers groupid for the parent.
 */ 
$result = $gacl_api->add_group('jedi','Jedi', $passengersGroupID, 'aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "The Jedi ARO Group has been added to the Passengers Successfully. <br>\n";
	} else {
		echo "Error creating the Jedi ARO Group.<br>\n";
	}
}
// Get the Jedi Group Id that has been returned from: add_group.
$jediGroupID = $result;
unset($result);

/*
 * The tree now looks like: 
 *
 * Millennium Falcon Passengers Group
 * |-Crew Group			[ALLOW: ALL]
 * | |-Han ARO
 * | '-Chewie ARO		[DENY: Engines]
 * '-Passengers Group	[ALLOW: Lounge]
 *	 | '- Jedi
 *   |-Obi-wan ARO
 *   |-Luke ARO			[ALLOW: Guns]
 *   |-R2D2 ARO			[ALLOW: Engines]
 *   '-C3PO ARO
 *
 * So we need to reassign Obi-wan and Luke from the Passengers Group, to the
 * Jedi group.
 *
 * So we do this by editing Obi-wans and Lukes object which links them to the Passengers Section.
 * edit_object($object_id, $section_value, $name, $value=0, $order=0, $hidden=0, $object_type) 
 */ 
 
/*
 * First we need the object_id's!
 * get_object_id($section_value, $value, $object_type)
 */ 
$result = $gacl_api->get_object_id('passengers','obi-wan','aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Got 'Passengers > Obi-wan' objectid!<br>\n";
	} else {
		echo "Error getting 'Passengers > Obi-wan' objectid.<br>\n";
	}
}
$obiWanObjectId = $result;
unset($result);

$result = $gacl_api->get_object_id('passengers','luke','aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Got 'Passengers > Luke' objectid!<br>\n";
	} else {
		echo "Error getting 'Passengers > Luke' objectid.<br>\n";
	}
}
$lukeObjectId = $result;
unset($result);

/*
 * Excellent, we are nearly there!  
 * Next we need to remove Obi-wans and Lukes assigned AROs to the Passengers Group
 * and add the AROs to the Jedi ARO Group.
 *
 * get_group_id($value, $name, $group_type)
 * del_group_object($group_id, $object_section_value, $object_value, $group_type)
 * add_group_object($group_id, $object_section_value, $object_value, $group_type)
 */

/*
 * First lets get the Passengers ARO GroupID
 */
$result = $gacl_api->get_group_id('passengers', 'Passengers', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Got 'Passengers' ARO GroupID!<br>\n";
	} else {
		echo "Error getting 'Passengers' ARO GroupID!.<br>\n";
	}
}
$passengersGroupId = $result;
unset($result);
$gacl_api->_DEBUG = true;
/*
 * Now lets delete Obi-Wans and Lukes ARO GROUP ARO connections.
 */
$result = $gacl_api->del_group_object($passengersGroupId, 'passengers', 'obi-wan', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Deleted 'Passengers > Obi-Wan' ARO Group > ARO Connection!<br>\n";
	} else {
		echo "Error deleting 'Passengers > Obi-Wan' ARO Group > ARO Connection!<br>\n";
	}
}
unset($result);

$result = $gacl_api->del_group_object($passengersGroupId, 'passengers', 'luke', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Deleted 'Passengers > Luke' ARO Group > ARO Connection!<br>\n";
	} else {
		echo "Error deleting 'Passengers > Luke' ARO Group > ARO Connection!<br>\n";
	}
}
unset($result);

/*
 * OK - we now have the id's to edit - lets change the Section_Value to 'jedi'
 * before we add the new AROs to the ARO Group.
 */
$result = $gacl_api->edit_object($obiWanObjectId, 'jedi', 'Obi-wan', 'obi-wan', 10, 0, 'aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Edited 'Passengers > Obi-wan' Successfully - now its: 'Jedi > Obi-wan'!<br>\n";
	} else {
		echo "Error editing 'Passengers > Obi-wan' Object.<br>\n";
	}
}
unset($result);
$result = $gacl_api->edit_object($lukeObjectId, 'jedi', 'Luke', 'luke', 11, 0, 'aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Edited 'Passengers > Luke' Successfully - now its: 'Jedi > Luke'!<br>\n";
	} else {
		echo "Error editing 'Passengers > Luke' Object.<br>\n";
	}
}
unset($result);

/*
 * Ok lets get the Jedi ARO GroupID
 */
$result = $gacl_api->get_group_id('jedi', 'Jedi', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Got 'Jedi' ARO GroupID!<br>\n";
	} else {
		echo "Error getting 'Jedi' ARO GroupID!.<br>\n";
	}
}
$jediGroupId = $result;
unset($result);
/*
 * Now lets add Obi-Wans and Lukes ARO GROUP ARO connections to Jedi.
 */
$result = $gacl_api->add_group_object($jediGroupId, 'jedi', 'obi-wan', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'Jedi > Obi-Wan' ARO Group > ARO Connection!<br>\n";
	} else {
		echo "Error creating 'Jedi > Obi-Wan' ARO Group > ARO Connection!<br>\n";
	}
}
unset($result);

$result = $gacl_api->add_group_object($jediGroupId, 'jedi', 'luke', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'Jedi > Luke' ARO Group > ARO Connection!<br>\n";
	} else {
		echo "Error creating 'Jedi > Luke' ARO Group > ARO Connection!<br>\n";
	}
}
unset($result);

/*
 * Allow the Jedi ARO Group Access to the Cockpit:
 */
//Associative array, with Object Section Value => array( Object Value ) pairs. 
$aco_array = array('access' => array('cockpit') );
$aro_array_GroupID =array($gacl_api->get_group_id('jedi') );
$allow = TRUE;
$enabled = TRUE;
$return_value = NULL;
$note = "Allowing the Jedi to have Access to the cockpit!";

//The NULL values are for the more advanced options such as groups, and AXOs. Refer to the manual for more info.
$result = $gacl_api->add_acl($aco_array, NULL, $aro_array_GroupID, NULL, NULL, $allow, $enabled, $return_value, $note, 'user');
if ($outputDebug == TRUE){
if ($result !== FALSE) {
	echo "Created our Jedi Cockpit Access ACL sucessfully!<br>\n";
} else {
	echo "Error creating ACL.<br>\n";
}
}
unset($result);

if ($outputDebug == TRUE){ 
echo "<br>\n";
echo "=================================================================================================<br>\n";
echo "-- Lets test the Jedi ACL for Obi-wan! --<br>\n";
echo "=================================================================================================<br>\n";
}
// Lets check if Obi-wan has access to the Lounge
if ( $gacl_api->acl_check('access', 'lounge', 'jedi', 'obi-wan') ) {
	if ($outputDebug == TRUE){
		echo "Obi-Wan still has access to the lounge!<br>\n";	
	}
} else {
	if ($outputDebug == TRUE){
		echo "Obi-Wan no longer has access to the lounge! (Not good!)<br>\n";	
	}
}
// Lets check if Obi-wan has access to the Cockpit
if ( $gacl_api->acl_check('access', 'cockpit', 'jedi', 'obi-wan') ) {
	if ($outputDebug == TRUE){
		echo "Obi-Wan has access to the Cockpit! (the Jedi ACL Worked!)<br>\n";	
	}
} else {
	if ($outputDebug == TRUE){
		echo "Obi-Wan can't access to the Cockpit! (Something went wrong!)<br>\n";	
	}
}
// Lets check if Obi-wan has access to the Engines - should fail!
if ( $gacl_api->acl_check('access', 'engines', 'jedi', 'obi-wan') ) {
	if ($outputDebug == TRUE){
		echo "Obi-Wan has access to the Engines! (How'd he get in there?)<br>\n";	
	}
} else {
	if ($outputDebug == TRUE){
		echo "Obi-Wan can't access to the Engines! (Good hes not allowed there!)<br>\n";	
	}
}

if ($outputDebug == TRUE){
echo "<br>\n<br>\nDone! Not difficult really! <br>\n";
echo "Remember to check out the <a href='../../../admin/acl_list.php'>Administration Interface</a> which can do all of the above in a few simple clicks.<br>\n<br>\n";
}
?>
