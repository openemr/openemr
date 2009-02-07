<?php
/*
 *  MAKE SURE phpGACL's DEBUG IS DISABLED... Otherwise the SOAP server will break.
 *
 *
 *  Currently, only the acl_check() function is exported to the SOAP server.
 *
 */

/*
 *  A small speed improvement can be made if you copy the $gacl_options array from gacl_admin.inc.php
 *  into this file, and only include the ../gacl.class.php file. 
 */
require_once('../admin/gacl_admin.inc.php');
require_once('nusoap.php');

$s = new soap_server;

$s->register('acl_check');
$s->register('test');

function acl_check($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $root_aro_group_id=NULL, $root_axo_group_id=NULL) {
	global $gacl;

	return $gacl->acl_check($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $root_aro_group_id, $root_axo_group_id);
}

function test($text) {
	return $text;
}

$s->service($HTTP_RAW_POST_DATA);
?>
