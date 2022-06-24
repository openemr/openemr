<?php
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("ACL Administration")]);
    exit;
}

require_once("gacl_admin.inc.php");

function get_system_info() {
	global $gacl_api;

	//Grab system info
	$system_info = 'PHP Version: '.phpversion()."\n";
	$system_info .= 'Zend Version: '.zend_version()."\n";
	$system_info .= 'Web Server: '.$_SERVER['SERVER_SOFTWARE']."\n\n";
	$system_info .= 'phpGACL Settings: '."\n";
	$system_info .= '  phpGACL Version: '.$gacl_api->get_version()."\n";
	$system_info .= '  phpGACL Schema Version: '.$gacl_api->get_schema_version()."\n";

	if($gacl_api->_caching == TRUE) {
		$caching = 'True';
	} else {
		$caching = 'False';
	}
	$system_info .= '  Caching Enabled: '. $caching ."\n";

	if($gacl_api->_force_cache_expire == TRUE) {
		$force_cache_expire = 'True';
	} else {
		$force_cache_expire = 'False';
	}
	$system_info .= '  Force Cache Expire: '.$force_cache_expire."\n";

	$system_info .= '  Database Prefix: \''.$gacl_api->_db_table_prefix."'\n";
	$system_info .= '  Database Type: '.$gacl_api->_db_type."\n";

	$database_server_info = $gacl_api->db->ServerInfo();
	$system_info .= '  Database Version: '.$database_server_info['version']."\n";
	$system_info .= '  Database Description: '.$database_server_info['description']."\n\n";

	$system_info .= 'Server Name: '. $_SERVER["SERVER_NAME"] ."\n";
	$system_info .= ' OS: '. PHP_OS ."\n";
	$system_info .= ' IP Address: '. $_SERVER["REMOTE_ADDR"] ."\n";
	$system_info .= ' Browser: '. $_SERVER["HTTP_USER_AGENT"] ."\n\n";

	$system_info .= 'System Information: '. php_uname() ."\n";

	return trim($system_info);
}

$system_info = get_system_info();

//Read credits.
$smarty->assign("credits", implode('',file('../CREDITS')) );

$smarty->assign("system_info", $system_info);
$smarty->assign("system_info_md5", md5($system_info) );

$smarty->assign("return_page", $_SERVER['PHP_SELF'] );

$smarty->assign('current','about');
$smarty->assign('page_title', 'About phpGACL');

$smarty->assign("phpgacl_version", $gacl_api->get_version() );
$smarty->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->display('phpgacl/about.tpl');
?>
