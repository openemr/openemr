<?php
require_once('../nusoap.php');

/*
 * EDIT THE BELOW URL TO MATCH YOUR SERVER.
 */
$soapclient = new soapclient('http://localhost/phpgacl/soap/server.php');

function acl_check($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $root_aro_group_id=NULL, $root_axo_group_id=NULL) {
        global $soapclient;

	$parameters = array($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $root_aro_group_id, $root_axo_group_id);

	return $soapclient->call('acl_check',$parameters);	
}

if ( acl_check('system','login','users','john_doe') ) {
        echo "John Doe has been granted access to login!<br>\n";
} else {
        echo "John Doe has been denied access to login!<br>\n";
}

?>

