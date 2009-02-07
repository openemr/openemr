<?php
/*
 *	PHPGACL Millennium Falcon ACL definingAccessControl.php
 *
 *	Defining access Control with phpGACL. (manual p.7-8)
 *
 *   The ACL tree for this example is:
 * Millennium Falcon Passengers Group
 * |-Crew Group
 * | |-Han ARO
 * | '-Chewie ARO
 * '-Passengers Group
 *   |-Obi-wan ARO
 *   |-Luke ARO
 *   |-R2D2 ARO
 *   '-C3PO ARO
 */

/*
 * Initialise the database - by clearing the database.
 */

// Let's get ready to RUMBLE!!!
$gacl_api = new gacl_api($gacl_options);

/*
 * Keep in mind, all of this can be done through the Administration Interface via your browser.
 */

/*
 * Create an Access Control Object (ACO) section. 
 * Sections serve no other purpose than to categorize ACOs.
 * 
 * add_object_section($name, $value=0, $order=0, $hidden=0, $object_type=NULL)
 */
 
$result = $gacl_api->add_object_section('Access', 'access', 10, 0, 'ACO'); //Must specifiy Object Type.
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created ACO section sucessfully. <br>\n";
	} else {
		echo "Error creating ACO section.<br>\n";
	}
}
unset($result);

/*
 * Now that we have our ACO Section created, lets put a Access Control Object (ACO) in it.
 * You can think of ACO's as "Actions".  
 * In this case the Action is the rooms the passengers have Access to.
 * The ACOs required for the Millennium Falcon are Access to:
 * 		- Lounge
 *		- Engines
 *		- Guns
 *		- Cockpit
 * 
 * add_object($section_value, $name, $value=0, $order=0, $hidden=0, $object_type=NULL)
 */
$result = $gacl_api->add_object('access', 'Lounge', 'lounge', 10, 0, 'ACO'); //Must specifiy Object Type.
if ($outputDebug == TRUE)
{
	if ($result !== FALSE) {
		echo "Created Lounge ACO sucessfully. <br>\n";
	} else {
		echo "Error creating Lounge ACO.<br>\n";
	}
}
unset($result);

// Add the Engines ACO
$result = $gacl_api->add_object('access', 'Engines', 'engines', 10, 0, 'ACO'); //Must specifiy Object Type.
if ($outputDebug == TRUE)
{
	if ($result !== FALSE) {
		echo "Created Engines ACO sucessfully. <br>\n";
	} else {
		echo "Error creating Engines ACO.<br>\n";
	}
}
unset($result);

// Add the Guns ACO
$result = $gacl_api->add_object('access', 'Guns', 'guns', 10, 0, 'ACO'); //Must specifiy Object Type.
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created Guns ACO sucessfully. <br>\n";
	} else {
		echo "Error creating Guns ACO.<br>\n";
	}
}
unset($result);

// Add the Cockpit ACO
$result = $gacl_api->add_object('access', 'Cockpit', 'cockpit', 10, 0, 'ACO'); //Must specifiy Object Type.
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created Cockpit ACO sucessfully. <br>\n";
	} else {
		echo "Error creating Cockpit ACO.<br>\n";
	}
}
unset($result);

/*
 * So we've created our ACOs that will be used to control who has access to where. 
 * Now we create Access Request Objects (ARO) Sections to assign to the passengers. 
 * The Sections are in this example are: 
 * 		- Crew
 *		- Passengers
 * 
 * This is an almost identical process as for the ACOs.
 * 
 * add_object_section($name, $value=0, $order=0, $hidden=0, $object_type=NULL)
 */
$result = $gacl_api->add_object_section('Crew', 'crew', 10, 0, 'ARO'); //Must specifiy Object Type, notice it is ARO now.
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created Crew ARO section sucessfully. <br>\n";
	} else {
		echo "Error creating Crew ARO section.<br>\n";
	}
}
unset($result);

// Add Passengers Section
$result = $gacl_api->add_object_section('Passengers', 'passengers', 11, 0, 'ARO'); //Must specifiy Object Type, notice it is ARO now.
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created Passengers ARO section sucessfully. <br>\n";
	} else {
		echo "Error creating Passengers ARO section.<br>\n";
	}
}
unset($result);

/* 
 * Now we have our sections, now we create Access Request Objects (ARO). 
 * The passengers of the Millenium Falcon: 
 * 
 *   -Han
 *   -Chewie
 *   -Obi-wan
 *   -Luke
 *   -R2D2
 *   -C3PO
 *
 *  So, we will create AROs for the Two Sections. 
 * add_object_section($name, $value=0, $order=0, $hidden=0, $object_type=NULL)
 */

// Add Han to the Crew 
$result = $gacl_api->add_object('crew', 'Han', 'han', 10, 0, 'ARO'); 
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'Han' ARO sucessfully. <br>\n";
	} else {
		echo "Error creating 'Han' ARO.<br>\n";
	}
}
unset($result);

// Add Chewie to the Crew 
$result = $gacl_api->add_object('crew', 'Chewie', 'chewie', 11, 0, 'ARO'); 
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'Chewie' ARO sucessfully. <br>\n";
	} else {
		echo "Error creating 'Chewie' ARO.<br>\n";
	}
}
unset($result);

// Add Obi-wan to the Passengers
$result = $gacl_api->add_object('passengers', 'Obi-wan', 'obi-wan', 10, 0, 'ARO'); 
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'Obi-wan' ARO sucessfully. <br>\n";
	} else {
		echo "Error creating 'Obi-wan' ARO.<br>\n";
	}
}
unset($result);

// Add Luke to the Passengers
$result = $gacl_api->add_object('passengers', 'Luke', 'luke', 11, 0, 'ARO'); 
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'Luke' ARO sucessfully. <br>\n";
	} else {
		echo "Error creating 'Luke' ARO.<br>\n";
	}
}
unset($result);

// Add R2D2 to the Passengers
$result = $gacl_api->add_object('passengers', 'R2D2', 'r2d2', 12, 0, 'ARO'); 
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'R2D2' ARO sucessfully. <br>\n";
	} else {
		echo "Error creating 'R2D2' ARO.<br>\n";
	}
}
unset($result);

// Add C3PO to the Passengers
$result = $gacl_api->add_object('passengers', 'C3PO', 'c3po', 13, 0, 'ARO'); 
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created 'C3PO' ARO sucessfully. <br>\n";
	} else {
		echo "Error creating 'C3PO' ARO.<br>\n";
	}
}
unset($result);

/*
 * The Millennium Falcon has now got all its passengers. 
 * Now we need to add the groups: 
 *
 * Millennium Falcon Passengers Group
 * |-Crew Group
 * '-Passengers Group
 *
 * add_group($value, $name, $parent_id, $group_type);
 */
 
 
/* 
 * So working from the Top lets add the Millennium Falcon Passengers Group
 */ 
$result = $gacl_api->add_group('millennium_falcon_passengers','Millennium Falcon Passengers', 0, 'aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created our Millennium Falcon Passengers ARO Group Successfully. <br>\n";
	} else {
		echo "Error Millennium Falcon Passengers ARO Group.<br>\n";
	}
}
$millenniumFalconPassengersGroupID = $result;
unset($result);
 
/* 
 * Next its the Crew Group
 */ 
$result = $gacl_api->add_group('crew','Crew', $millenniumFalconPassengersGroupID, 'aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created our Crew ARO Group Successfully. <br>\n";
	} else {
		echo "Error Crew ARO Group.<br>\n";
	}
}
$crewGroupID = $result;
unset($result);

/* 
 * Next its the Passengers Group
 */ 
$result = $gacl_api->add_group('passengers','Passengers', $millenniumFalconPassengersGroupID, 'aro');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created our Passengers ARO Group Successfully. <br>\n";
	} else {
		echo "Error Passengers ARO Group.<br>\n";
	}
}
$passengersGroupID = $result;
unset($result);

/*
 * The Millennium Falcon has now got all its passengers & groups. 
 * But we need to assign the passengers to the groups, like so: 
 *
 * Millennium Falcon Passengers Group
 * |-Crew Group
 * | |-Han ARO
 * | '-Chewie ARO
 * '-Passengers Group
 *   |-Obi-wan ARO
 *   |-Luke ARO
 *   |-R2D2 ARO
 *   '-C3PO ARO
 *
 * add_group_object($group_id, $object_section_value, $object_value, $group_type='ARO')
 */
 
/* 
 * Assign Han to the Crew Group.
 */ 
$result = $gacl_api->add_group_object($crewGroupID, 'crew', 'han', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Assigned 'Han' to the Crew ARO Group. <br>\n";
	} else {
		echo "Error assigning 'Han' to the Crew ARO Group.<br>\n";
	}
}
unset($result);

/* 
 * Assign Chewie to the Crew Group.
 */ 
$result = $gacl_api->add_group_object($crewGroupID, 'crew', 'chewie', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Assigned 'Chewie' to the Crew ARO Group. <br>\n";
	} else {
		echo "Error assigning 'Chewie' to the Crew ARO Group.<br>\n";
	}
}
unset($result);

/* 
 * Assign Obi-wan to the Passengers Group.
 */ 
$result = $gacl_api->add_group_object($passengersGroupID, 'passengers', 'obi-wan', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Assigned 'Obi-wan' to the Passengers ARO Group. <br>\n";
	} else {
		echo "Error assigning 'Obi-wan' to the Passengers ARO Group.<br>\n";
	}
}
unset($result);

/* 
 * Assign Luke to the Passengers Group.
 */ 
$result = $gacl_api->add_group_object($passengersGroupID, 'passengers', 'luke', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Assigned 'Luke' to the Passengers ARO Group. <br>\n";
	} else {
		echo "Error assigning 'Luke' to the Passengers ARO Group.<br>\n";
	}
}
unset($result);

/* 
 * Assign R2D2 to the Passengers Group.
 */ 
$result = $gacl_api->add_group_object($passengersGroupID, 'passengers', 'r2d2', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Assigned 'R2D2' to the Passengers ARO Group. <br>\n";
	} else {
		echo "Error assigning 'R2D2' to the Passengers ARO Group.<br>\n";
	}
}
unset($result);

/* 
 * Assign C3PO to the Passengers Group.
 */ 
$result = $gacl_api->add_group_object($passengersGroupID, 'passengers', 'c3po', 'ARO');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Assigned 'C3PO' to the Passengers ARO Group. <br>\n";
	} else {
		echo "Error assigning 'C3PO' to the Passengers ARO Group.<br>\n";
	}
}
unset($result);

/*
 * The Millennium Falcon has now got all its passengers & groups. 
 * The passengers are assigned to their groups, but as yet no one has permission to
 * to go anywhere - we need to create ACLs, shown in the tree by the ALLOW notation. 
 *
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
 * add_acl($aco_array, $aro_array, $aro_group_ids=NULL, $axo_array=NULL, $axo_group_ids=NULL, $allow=1, $enabled=1, $return_value=NULL, $note=NULL, $section_value=NULL )
 */

/*
 * First The Crew:
 */
//Associative array, with Object Section Value => array( Object Value ) pairs. 
$aco_array = array('access' => array('cockpit','engines','guns','lounge') );
$aro_array_GroupID =array($gacl_api->get_group_id('crew') );
$allow = TRUE;
$enabled = TRUE;
$return_value = NULL;
$note = "Allowing the Crew to have Access to: cockpit, engines, guns and lounge!";

//The NULL values are for the more advanced options such as groups, and AXOs. Refer to the manual for more info.
$result = $gacl_api->add_acl($aco_array, NULL, $aro_array_GroupID, NULL, NULL, $allow, $enabled, $return_value, $note, 'user');
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created our first ACL sucessfully!<br>\n";
	} else {
		echo "Error creating ACL.<br>\n";
	}
}
unset($result);

/*
 * Now The Passengers:
 */
//Associative array, with Object Section Value => array( Object Value ) pairs. 
$aco_array = array('access' => array('lounge') );
$aro_array_GroupID = array($gacl_api->get_group_id('Passengers'));
$allow = TRUE;
$enabled = TRUE;
$return_value = NULL;
$note = "Allowing the Passengers to have Access to the lounge!";

//The NULL values are for the more advanced options such as groups, and AXOs. Refer to the manual for more info.
$result = $gacl_api->add_acl($aco_array, NULL, $aro_array_GroupID, NULL, NULL, $allow, $enabled, $return_value, $note);
if ($outputDebug == TRUE){
	if ($result !== FALSE) {
		echo "Created our second ACL sucessfully! Click <a href='../../../admin/acl_test.php'>here</a> to see it in action!<br>\n";
	} else {
		echo "Error creating ACL.<br>\n";
	}
}
unset($result);
if ($outputDebug == TRUE){
	echo "<br>\n";
	echo "=================================================================================================<br>\n";
	echo "-- Good stuff thats it all done as of the top of Page 8!  - so lets test a couple of scenarios --<br>\n";
	echo "=================================================================================================<br>\n";
}
/*
 * Awesome, we've setup our ACL system just the way we want it. Now for the easy part,
 * the code to check ACLs.
 *
 * Keep in the mind the API class does not need to be included in scripts that just
 * check ACLs. This is for performance reasons of course.
 *
 * I'm including gacl.class.php again here just to give you the full picture of what you
 * need in each script to check ACLs.
 */
require_once(dirname(__FILE__).'/../../../gacl.class.php');
$gacl = new gacl($gacl_options); //Use the same options as above.

// Lets check Han has access to the cockpit
	if ( $gacl->acl_check('access', 'cockpit', 'crew', 'han') ) {
		if ($outputDebug == TRUE){
		echo "Han has been granted access to the cockpit!<br>\n";	
		}
	} else {
		if ($outputDebug == TRUE){
			echo "Han has been denied access to the cockpit!<br>\n";	
		}
	}

// Lets check Luke has access to the cockpit *should fail!
	if ( $gacl->acl_check('access', 'cockpit', 'crew', 'Luke') ) {
		if ($outputDebug == TRUE){
			echo "Luke has been granted access to the cockpit!<br>\n";	
		}
	} else {
		if ($outputDebug == TRUE){
			echo "Luke has been denied access to the cockpit! (good he's not allowed there!)<br>\n";	
		}
	}

if ($outputDebug == TRUE){
	echo "<br>\n<br>\nDone! Easy - lots of setting up done in this example - but next we'll extend this setup <br>\n";
	echo "Remember to check out the <a href='../../../admin/acl_list.php'>Administration Interface</a> which can do all of the above in a few simple clicks.<br>\n<br>\n";
}
?>
