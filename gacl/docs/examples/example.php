<?php
require_once(dirname(__FILE__).'/gacl.class.php');
require_once(dirname(__FILE__).'/gacl_api.class.php');
require_once(dirname(__FILE__).'/admin/gacl_admin.inc.php');

/*
 * Create an array containing your preferred settings, including how to connect to your database.
 */
$gacl_options = array(
								'debug' => $gacl_options['debug'],
								'items_per_page' => 100,
								'max_select_box_items' => 100,
								'max_search_return_items' => 200,
								'db_type' => $gacl_options['db_type'],
								'db_host' => $gacl_options['db_host'],
								'db_user' => $gacl_options['db_user'],
								'db_password' => $gacl_options['db_password'],
								'db_name' => $gacl_options['db_name'],
								'db_table_prefix' => $gacl_options['db_table_prefix'],
								'caching' => FALSE,
								'force_cache_expire' => TRUE,
								'cache_dir' => '/tmp/phpgacl_cache',
								'cache_expire_time' => 600
							);

/*
 * Let's get ready to RUMBLE!!!
 */
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
 
$result = $gacl_api->add_object_section('System', 'system', 10, 0, 'ACO'); //Must specifiy Object Type.

if ($result !== FALSE) {
	echo "Created ACO section sucessfully. <br>\n";
} else {
	echo "Error creating ACO section.<br>\n";
}
unset($result);

/*
 * Now that we have our ACO Section created, lets put a Access Control Object (ACO) in it.
 * You can think of ACO's as "Actions".
 * 
 * add_object($section_value, $name, $value=0, $order=0, $hidden=0, $object_type=NULL)
 */
$result = $gacl_api->add_object('system', 'Enable - Login', 'login', 10, 0, 'ACO'); //Must specifiy Object Type.

if ($result !== FALSE) {
	echo "Created ACO sucessfully. <br>\n";
} else {
	echo "Error creating ACO.<br>\n";
}
unset($result);

/*
 * So we've created our ACO that will enable login access. Now we have create Access Request Objects (ARO)
 * that will eventually "request" access to login. This is an almost identical process.
 * 
 * add_object_section($name, $value=0, $order=0, $hidden=0, $object_type=NULL)
 * add_object($section_value, $name, $value=0, $order=0, $hidden=0, $object_type=NULL)
 */
$result = $gacl_api->add_object_section('Users', 'users', 10, 0, 'ARO'); //Must specifiy Object Type, notice it is ARO now.
if ($result !== FALSE) {
	echo "Created ARO section sucessfully. <br>\n";
} else {
	echo "Error creating ARO section.<br>\n";
}
unset($result);

//Notice the Object Type. In most cases you'll want to make the ARO value for users a unique User ID,
//or user name of some sort.
$result = $gacl_api->add_object('users', 'John Doe', 'john_doe', 10, 0, 'ARO'); 

if ($result !== FALSE) {
	echo "Created 'John Doe' ARO sucessfully. <br>\n";
} else {
	echo "Error creating 'John Doe' ARO.<br>\n";
}
unset($result);
 
//Lets create two users, just for fun.
$result = $gacl_api->add_object('users', 'Jane Doe', 'jane_doe', 11, 0, 'ARO'); 

if ($result !== FALSE) {
	echo "Created 'Jane Doe' ARO sucessfully. <br>\n";
} else {
	echo "Error creating 'Jane Doe' ARO.<br>\n";
}
unset($result);


/*
 * There, we now have the building blocks to start creating our ACL matrix from.
 * Lets give John Doe access to login.
 *
 * add_acl($aco_array, $aro_array, $aro_group_ids=NULL, $axo_array=NULL, $axo_group_ids=NULL, $allow=1, $enabled=1, $return_value=NULL, $note=NULL, $acl_id=FALSE )
 */
 
//Associative array, with Object Section Value => array( Object Value ) pairs. 
$aco_array = array('system' => array('login') );
$aro_array = array('users' => array('john_doe', 'jane_doe') );

$allow = TRUE;
$enabled = TRUE;
$return_value = NULL;
$note = "Allowing John and Jane Doe access to login!";

//The NULL values are for the more advanced options such as groups, and AXOs. Refer to the manual for more info.
$result = $gacl_api->add_acl($aco_array, $aro_array, NULL, NULL, NULL, $allow, $enabled, $return_value, $note);

if ($result !== FALSE) {
	echo "Created our first ACL sucessfully. Click <a href=admin/acl_test.php>here</a> to see it in action!<br>\n";
} else {
	echo "Error creating ACL.<br>\n";
}
unset($result);

echo "<br>\n<br>\n";
echo "-- Lets test our work --<br>\n";

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
require_once(dirname(__FILE__).'/gacl.class.php');
$gacl = new gacl($gacl_options); //Use the same options as above.

if ( $gacl->acl_check('system','login','users','john_doe') ) {
	echo "John Doe has been granted access to login!<br>\n";	
} else {
	echo "John Doe has been denied access to login!<br>\n";	
}

if ( $gacl->acl_check('system','login','users','jane_doe') ) {
	echo "Jane Doe has been granted access to login!<br>\n";	
} else {
	echo "Jane Doe has been denied access to login!<br>\n";	
}


echo "<br>\n<br>\nDone! Now how easy was that? <br>\n";
echo "Remember to check out the <a href=admin/acl_list.php>Administration Interface</a> which can do all of the above in a few simple clicks.<br>\n<br>\n";

echo "<b>If you run this script more then once, you may get some errors, as duplicate object entries can not be created.</b><br>\n";
?>
