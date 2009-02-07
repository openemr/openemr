<?php
/*
meinhard_jahn@web.de, 20041102: link to acl_test2.php and acl_test3.php
*/
/*
if (!empty($_GET['debug'])) {
	$debug = $_GET['debug'];
}
*/
//First make sure user has access
include_once("../../interface/globals.php");
include_once("$srcdir/acl.inc");
//ensure user has proper access
if (!acl_check('admin', 'acl')) {
            echo xl('ACL Administration Not Authorized');
            exit;
}
//ensure php is installed
if (!isset($phpgacl_location)) {
            echo xl('php-GACL access controls are turned off');
            exit;
}

@set_time_limit(600);

require_once('../profiler.inc');
$profiler = new Profiler(true,true);

require_once("gacl_admin.inc.php");

$smarty->assign("return_page", $_SERVER['PHP_SELF'] );

$smarty->assign('current','acl_test');
$smarty->assign('page_title', 'ACL Test');

$smarty->assign("phpgacl_version", $gacl_api->get_version() );
$smarty->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->display('phpgacl/acl_test.tpl');
?>
