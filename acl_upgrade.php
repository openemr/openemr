<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This script will update the phpGACL database, which include
// Access Control Objects(ACO), Groups(ARO), and Access Control
// Lists(ACL) to the most recent version.
// It will display whether each update already exist
// or if it was updated succesfully.
//
// Updates included:
//   2.8.2
//     Section "sensitivities" (Sensitivities):
//       ADD  normal   Normal              (Administrators, Physicians, Clinicians(addonly))
//       ADD  high     High                (Administrators, Physicians)
//     Section "admin"         (Administration):
//       ADD  super    Superuser           (Adminstrators)
//   2.8.4
//     Section "admin"         (Administration):
//       ADD  drugs    Pharmacy Dispensary (Administrators, Physicians, Clinicians(write))
//       ADD  acl      ACL Administration (Administrators)
//     Section "sensitivities" (Sensitivities):
//       EDIT high     High               (ensure the order variable is '20')
//     Section "acct"          (Accounting):
//       ADD  disc     Price Discounting (Administrators, Physicians, Accounting(write))


//Ensure that phpGACL has been installed
include_once('library/acl.inc');
if (isset ($phpgacl_location)) {
	include_once("$phpgacl_location/gacl_api.class.php");
	$gacl = new gacl_api();
}
else {
	die("You must first set up library/acl.inc to use phpGACL!");
}


//Collect the ACL ID numbers.
echo "<B>Checking to ensure all the proper ACL(access control list) are present:</B></BR>";
//Get Administrator ACL ID number
$admin_write = getAclIdNumber('Administrators', 'write');
//Get Doctor ACL ID Number
$doc_write = getAclIdNumber('Physicians', 'write');
//Get Clinician ACL with write access ID number
$clin_write = getAclIdNumber('Clinicians', 'write');
//Get Clinician ACL with addonly access ID number
$clin_addonly = getAclIdNumber('Clinicians', 'addonly');
//Get Receptionist ACL ID number
$front_write = getAclIdNumber('Front Office', 'write');
//Get Accountant ACL ID number
$back_write = getAclIdNumber('Accounting', 'write');


//Add new object Sections
echo "<BR/><B>Adding new object sections</B><BR/>";
//Add 'Sensitivities' object section (added in 2.8.2)
addObjectSectionAcl('sensitivities', 'Sensitivities');


//Add new Objects
echo "<BR/><B>Adding new objects</B><BR/>";
//Add 'Normal' sensitivity object, order variable is default 10 (added in 2.8.2)
addObjectAcl('sensitivities', 'Sensitivities', 'normal', 'Normal');
//Add 'High' sensitivity object, order variable is set to 20 (added in 2.8.2)
addObjectAclWithOrder('sensitivities', 'Sensitivities', 'high', 'High', 20);
//Add 'Pharmacy Dispensary' object (added in 2.8.4)
addObjectAcl('admin', 'Administration', 'drugs', 'Pharmacy Dispensary');
//Add 'ACL Administration' object (added in 2.8.4)
addObjectAcl('admin', 'Administration', 'acl', 'ACL Administration');
//Add 'Price Discounting' object (added in 2.8.4)
addObjectAcl('acct', 'Accounting', 'disc', 'Price Discounting');


//Update already existing Objects
echo "<BR/><B>Upgrading objects</B><BR/>";
//Ensure that 'High' sensitivity object order variable is set to 20
editObjectAcl('sensitivities', 'Sensitivities', 'high', 'High', 20);


//Add new User Defined Groups (ARO) here
//(placemarker, since no new user defined groups since 2.8.1 have been added)


//Update the ACLs
echo "<BR/><B>Updating the ACLs(Access Control Lists)</B><BR/>";
//Insert the 'super' object from the 'admin' section into the Administrators group write ACL (added in 2.8.2)
updateAcl($admin_write, 'Administrators', 'admin', 'Administration', 'super', 'Superuser', 'write');
//Insert the 'high' object from the 'sensitivities' section into the Administrators group write ACL (added in 2.8.2)
updateAcl($admin_write, 'Administrators', 'sensitivities', 'Sensitivities', 'high', 'High', 'write');
//Insert the 'normal' object from the 'sensitivities' section into the Administrators group write ACL (added in 2.8.2)
updateAcl($admin_write, 'Administrators', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'write');
//Insert the 'high' object from the 'sensitivities' section into the Physicians group write ACL (added in 2.8.2)
updateAcl($doc_write, 'Physicians', 'sensitivities', 'Sensitivities', 'high', 'High', 'write');
//Insert the 'normal' object from the 'sensitivities' section into the Physicians group write ACL (added in 2.8.2)
updateAcl($doc_write, 'Physicians', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'write');
//Insert the 'normal' object from the 'sensitivities' section into the Clinicians group  addonly ACL (added in 2.8.2)
updateAcl($clin_addonly, 'Clinicians', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'addonly');
//Insert the 'drugs' object from the 'admin' section into the Administrators group write ACL (added in 2.8.4)
updateAcl($admin_write, 'Administrators', 'admin', 'Administration', 'drugs', 'Pharmacy Dispensary', 'write');
//Insert the 'drugs' object from the 'admin' section into the Physicians group write ACL (added in 2.8.4)
updateAcl($doc_write, 'Physicians', 'admin', 'Administration', 'drugs', 'Pharmacy Dispensary', 'write');
//Insert the 'drugs' object from the 'admin' section into the Clinicians group write ACL (added in 2.8.4)
updateAcl($clin_write, 'Clinicians', 'admin', 'Administration', 'drugs', 'Pharmacy Dispensary', 'write');
//Insert the 'acl' object from the 'admin' section into the Administrators group write ACL (added in 2.8.4)
updateAcl($admin_write, 'Administrators', 'admin', 'Administration', 'acl', 'ACL Administration', 'write');
//Insert the 'disc' object from the 'acct' section into the Administrators group write ACL (added in 2.8.4)
updateAcl($admin_write, 'Administrators', 'acct', 'Accounting', 'disc', 'Price Discounting', 'write');
//Insert the 'disc' object from the 'acct' section into the Accounting group write ACL (added in 2.8.4)
updateAcl($back_write, 'Accounting', 'acct', 'Accounting', 'disc', 'Price Discounting', 'write');
//Insert the 'disc' object from the 'acct' section into the Physicians group write ACL (added in 2.8.4)
updateAcl($doc_write, 'Physicians', 'acct', 'Accounting', 'disc', 'Price Discounting', 'write');


//Function will return an array that contains the ACL ID number.
//It will also check to ensure the ACL exist and is not duplicated.
//  $title = Title(string) of group.
//  $return_value = What the acl returns (string), usually 'write' or 'addonly'
function getAclIdNumber($title, $return_value) {
	global $gacl;
	$temp_acl_id_array  = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $title, FALSE, FALSE, FALSE, $return_value);
	switch (count($temp_acl_id_array)) {
		case 0:
			echo "<B>ERROR</B>, '$title' group '$return_value' ACL does not exist.</BR>";
			break;
		case 1:
			echo "'$title' group '$return_value' ACL is present.</BR>";
			break;
		default:
			echo "<B>ERROR</B>, Multiple '$title' group '$return_value' ACLs are present.</BR>";
			break;
	}
	return $temp_acl_id_array;
}


//Function to add a group. 
//This is just a placeholder function at this point, since have not added another group yet.
//  $name = Identifier(string) of group
//  $title = Title(string) of group
function addGroupAcl($name, $title) {
	global $gacl;
//if add a group, then will need to add logic here
	return;
}


//Function to add an object section.
//It will check to ensure the object section doesn't already exist.
//  $name = Identifier(string) of section
//  $title = Title(string) of object
function addObjectSectionAcl($name, $title) {
	global $gacl;
	if ($gacl->get_object_section_section_id($title, $name, 'ACO')) {
		echo "The '$title' object section already exist.</BR>";
	}
	else {
		$tmp_boolean = $gacl->add_object_section($title , $name, 10, 0, 'ACO');
		if ($tmp_boolean) {
			echo "The '$title' object section has been successfully added.</BR>";
		}
		else {
			echo "<B>ERROR</B>,unable to create the '$title' object section.</BR>";
		}
	}
	return;
}


//Function to add an object.
//It will check to ensure the object doesn't already exist.
//  $section_name = Identifier(string) of section
//  $section_title = Title(string) of section
//  $object_name = Identifier(string) of object
//  $object_title = Title(string) of object
function addObjectAcl($section_name, $section_title, $object_name, $object_title) {
	global $gacl;
	if ($gacl->get_object_id($section_name, $object_name, 'ACO')) {
		echo "The '$object_title' object in the '$section_title' section already exist.</BR>";
	}
	else {
		$tmp_boolean = $gacl->add_object($section_name, $object_title, $object_name, 10, 0, 'ACO');
		if ($tmp_boolean) {
			echo "The '$object_title' object in the '$section_title' section has been successfully added.</BR>";
		}
		else {
			echo "<B>ERROR</B>,unable to create the '$object_title' object in the '$section_title' section.</BR>";
		}
	}
	return;
}


//Function to add an object and set the 'order' variable.
//It will check to ensure the object doesn't already exist.
//  $section_name = Identifier(string) of section
//  $section_title = Title(string) of section
//  $object_name = Identifier(string) of object
//  $object_title = Title(string) of object
//  $order_number = number to determine order in list. used in sensitivities to order the choices
//                  in openemr
function addObjectAclWithOrder($section_name, $section_title, $object_name, $object_title, $order_number) {
	global $gacl;
	if ($gacl->get_object_id($section_name, $object_name, 'ACO')) {
		echo "The '$object_title' object in the '$section_title' section already exist.</BR>";
	}
	else {
		$tmp_boolean = $gacl->add_object($section_name, $object_title, $object_name, $order_number, 0, 'ACO');
		if ($tmp_boolean) {
			echo "The '$object_title' object in the '$section_title' section has been successfully added.</BR>";
		}
		else {
			echo "<B>ERROR</B>,unable to create the '$object_title' object in the '$section_title' section.</BR>";
		}
	}
	return;
}


//Function to edit an object and set the 'order' variable.
//It will check to ensure the object already exist, and hasn't been upgraded yet.
//  $section_name = Identifier(string) of section
//  $section_title = Title(string) of section
//  $object_name = Identifier(string) of object
//  $object_title = Title(string) of object
//  $order_number = number to determine order in list. used in sensitivities to order the choices
//                  in openemr
function editObjectAcl($section_name, $section_title, $object_name, $object_title, $order_number) {
	global $gacl;
	$tmp_objectID = $gacl->get_object_id($section_name, $object_name, 'ACO');
	if ($tmp_objectID) {
		$tmp_object = $gacl->get_object_data($tmp_objectID, 'ACO');
		if ($tmp_object[0][2] ==  $order_number && 
		    $tmp_object[0][0] ==  $section_name &&
		    $tmp_object[0][1] ==  $object_name &&
		    $tmp_object[0][3] ==  $object_title) {
			echo "The '$object_title' object in the '$section_title' section has already been updated.</BR>";
		}
		else {
			$tmp_boolean = $gacl->edit_object($tmp_objectID, $section_name, $object_title, $object_name, $order_number, 0, 'ACO');
			if ($tmp_boolean) {
				echo "The '$object_title' object in the '$section_title' section has been successfully updated.</BR>";
			}
			else {
				echo "<B>ERROR</B>,unable to update the '$object_title' object in the '$section_title' section.</BR>";
			}
		}
	}
	else {
		echo "<B>ERROR</B>, the '$object_title' object in the '$section_title' section does not exist.</BR>";
	}
	return;
}


//Update the ACL
//It will check to ensure the ACL hasn't already been updated.
//  $array_acl_id_number = array containing hopefully one element, which is an integer, and is identifier of acl to be updated.
//  $group_title = Title(string) of group.
//  $object_section_name = Identifier(string) of section
//  $object_section_title = Title(string) of section
//  $object_name = Identifier(string) of object
//  $object_title = Title(string) of object
//  $acl_return_value = What the acl returns (string), usually 'write' or 'addonly'
function updateAcl($array_acl_id_number, $group_title, $section_name, $section_title, $object_name, $object_title, $return_value) {
	global $gacl;
	$tmp_array = $gacl->search_acl($section_name, $object_name, FALSE, FALSE, $group_title, FALSE, FALSE, FALSE, $return_value);
	switch (count($tmp_array)) {
		case 0:
			$tmp_boolean = @$gacl->append_acl($array_acl_id_number[0], NULL, NULL, NULL, NULL, array($section_name=>array($object_name)));
			if ($tmp_boolean){
				echo "Successfully placed the '$object_title' object of the '$section_title' section into the '$group_title' group '$return_value' ACL.</BR>";
			}
			else {
				echo "<B>ERROR</B>,unable to place the '$object_title' object of the '$section_title' section into the '$group_title' group '$return_value' ACL.</BR>";
			}
			break;
		case 1:
			echo "The '$object_title' object of the '$section_title' section is already found in the '$group_title' group '$return_value' ACL.</BR>";
			break;
		default:
			echo "<B>ERROR</B>, Multiple '$group_title' group '$return_value' ACLs with the '$object_title' object of the '$section_title' section are present.</BR>";
			break;
	}
	return;
}

//All done
echo "</BR><B>ALL DONE</B>";

?>
