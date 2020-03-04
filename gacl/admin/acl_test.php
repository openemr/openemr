<?php
/*
Ready for smarty 3
Changes: Used smarty 3 data object
*/

/*
meinhard_jahn@web.de, 20041102: link to acl_test2.php and acl_test3.php
*/
/*
if (!empty($_GET['debug'])) {
	$debug = $_GET['debug'];
}
*/
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;

//make a smarty 3 data object
$data = new Smarty_Data;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
            echo xlt('ACL Administration Not Authorized');
            exit;
}

@set_time_limit(600);

require_once('../profiler.inc');
$profiler = new Profiler(true,true);

require_once("gacl_admin.inc.php");

$data->assign("return_page", $_SERVER['PHP_SELF'] );

$data->assign('current','acl_test');
$data->assign('page_title', 'ACL Test');

$data->assign("phpgacl_version", $gacl_api->get_version() );
$data->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->display('phpgacl/acl_test.tpl',$data);
?>
