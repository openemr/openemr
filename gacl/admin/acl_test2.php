<?php
/*
if (!empty($_GET['debug'])) {
	$debug = $_GET['debug'];
}
*/
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("ACL Administration")]);
    exit;
}

@set_time_limit(600);

require_once('../profiler.inc.php');
$profiler = new Profiler(true,true);

require_once("gacl_admin.inc.php");

$query = '
	SELECT		a.value AS a_value, a.name AS a_name,
				b.value AS b_value, b.name AS b_name,
				c.value AS c_value, c.name AS c_name,
				d.value AS d_value, d.name AS d_name
	FROM		'. $gacl_api->_db_table_prefix .'aco_sections a
	LEFT JOIN	'. $gacl_api->_db_table_prefix .'aco b ON a.value=b.section_value,
				'. $gacl_api->_db_table_prefix .'aro_sections c
	LEFT JOIN	'. $gacl_api->_db_table_prefix .'aro d ON c.value=d.section_value
	ORDER BY	a.value, b.value, c.value, d.value';

//$rs = $db->Execute($query);
$rs = $db->pageexecute($query, $gacl_api->_items_per_page, ($_GET['page'] ?? null));
$rows = $rs->GetRows();

/*
echo("<pre>");
print_r($rows);
echo("</pre>");
*/

$total_rows = count($rows);

$total_acl_check_time = 0;

foreach ($rows as $row) {
    [$aco_section_value, $aco_section_name, $aco_value, $aco_name, $aro_section_value, $aro_section_name, $aro_value, $aro_name] = $row;

	$acl_check_begin_time = $profiler->getMicroTime();
	$acl_result = $gacl->acl_query($aco_section_value, $aco_value, $aro_section_value, $aro_value);
	$acl_check_end_time = $profiler->getMicroTime();

	$access = &$acl_result['allow'];
	$return_value = &$acl_result['return_value'];

	$acl_check_time = ($acl_check_end_time - $acl_check_begin_time) * 1000;
	$total_acl_check_time += $acl_check_time;

	if (empty($tmp_aco_section_name) OR $aco_section_name != $tmp_aco_section_name OR $aco_name != $tmp_aco_name) {
		$display_aco_name = "$aco_section_name > $aco_name";
	} else {
		$display_aco_name = "";
	}

	$acls[] = array(
						'aco_section_value' => $aco_section_value,
						'aco_section_name' => $aco_section_name,
						'aco_value' => $aco_value,
						'aco_name' => $aco_name,

						'aro_section_value' => $aro_section_value,
						'aro_section_name' => $aro_section_name,
						'aro_value' => $aro_value,
						'aro_name' => $aro_name,

						'access' => $access,
						'return_value' => $return_value,
						'acl_check_time' => number_format($acl_check_time, 2),

						'display_aco_name' => $display_aco_name,
					);

	$tmp_aco_section_name = $aco_section_name;
	$tmp_aco_name = $aco_name;
}

//echo "<br /><br />$x ACL_CHECK()'s<br />\n";

$smarty->assign("acls", $acls);

$smarty->assign("total_acl_checks", $total_rows);
$smarty->assign("total_acl_check_time", $total_acl_check_time);

if ($total_rows > 0) {
	$avg_acl_check_time = $total_acl_check_time / $total_rows;
}
$smarty->assign("avg_acl_check_time", number_format( ($avg_acl_check_time + 0) ,2));

$smarty->assign("paging_data", $gacl_api->get_paging_data($rs));

$smarty->assign("return_page", $_SERVER['PHP_SELF'] );

$smarty->assign('current','acl_test');
$smarty->assign('page_title', '2-dim. ACL Test');

$smarty->assign("phpgacl_version", $gacl_api->get_version() );
$smarty->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->display('phpgacl/acl_test2.tpl');
?>
