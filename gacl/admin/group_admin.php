<?php
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


require_once('gacl_admin.inc.php');

//GET takes precedence.
if ($_GET['group_type'] != '') {
	$group_type = $_GET['group_type'];
} else {
	$group_type = $_POST['group_type'];
}

switch(strtolower(trim($group_type))) {
	case 'axo':
		$group_type = 'axo';
		$group_table = $gacl_api->_db_table_prefix . 'axo_groups';
		$group_map_table = $gacl_api->_db_table_prefix . 'groups_axo_map';
		$smarty->assign('current','axo_group');
		break;
	default:
		$group_type = 'aro';
		$group_table = $gacl_api->_db_table_prefix . 'aro_groups';
		$group_map_table = $gacl_api->_db_table_prefix . 'groups_aro_map';
		$smarty->assign('current','aro_group');
		break;
}

switch ($_POST['action']) {
	case 'Delete':
		//See edit_group.php
		break;
	default:
		$formatted_groups = $gacl_api->format_groups($gacl_api->sort_groups($group_type), HTML);
		
		$query = '
			SELECT		a.id, a.name, a.value, count(b.'. $group_type .'_id)
			FROM		'. $group_table .' a
			LEFT JOIN	'. $group_map_table .' b ON b.group_id=a.id
			GROUP BY	a.id,a.name,a.value';
		$rs = $db->Execute($query);
		
		$group_data = array();
		
		if(is_object($rs)) {
			while($row = $rs->FetchRow()) {
				$group_data[$row[0]] = array(
					'name' => $row[1],
					'value' => $row[2],
					'count' => $row[3]
				);
			}
		}
		
		$groups = array();
		
		foreach($formatted_groups as $id => $name) {
			$groups[] = array(
				'id' => $id,
				// 'parent_id' => $parent_id,
				// 'family_id' => $family_id,
				'name' => $name,
				'raw_name' => $group_data[$id]['name'],
				'value' => $group_data[$id]['value'],
				'object_count' => $group_data[$id]['count']
			);
		}
		
		$smarty->assign('groups', $groups);
		break;
}

$smarty->assign('group_type', $group_type);
$smarty->assign('return_page', $_SERVER['REQUEST_URI']);

$smarty->assign('current', $group_type .'_group');
$smarty->assign('page_title', strtoupper($group_type) .' Group Admin');

$smarty->assign('phpgacl_version', $gacl_api->get_version());
$smarty->assign('phpgacl_schema_version', $gacl_api->get_schema_version());

$smarty->display('phpgacl/group_admin.tpl');
?>
