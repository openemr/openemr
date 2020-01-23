<?php
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
    echo xlt('ACL Administration Not Authorized');
    exit;
}

require_once('gacl_admin.inc.php');

switch (strtolower($_GET['object_type'])) {
	case 'axo':
		$object_type = 'axo';
		break;
	default:
		$object_type = 'aro';
}

switch ($_GET['action']) {
	case 'Search':
		$gacl_api->debug_text('Submit!!');

		//Function to pass array_walk to trim all entries in an array.
		function array_walk_trim(&$array_field) {
			$array_field = $db->qstr(strtolower(trim($array_field)));
		}

		$value_search_str = trim($_GET['value_search_str']);
		$name_search_str = trim($_GET['name_search_str']);

		$exploded_value_search_str = explode("\n", $value_search_str);
		$exploded_name_search_str = explode("\n", $name_search_str);

		if (count($exploded_value_search_str) > 1 OR count($exploded_name_search_str) > 1) {
			//Given a list, lets try to match all lines in it.
			array_walk($exploded_value_search_str, 'array_walk_trim');
			array_walk($exploded_name_search_str, 'array_walk_trim');
		} else {
			if ($value_search_str != '') {
				$value_search_str .= '%';
			}

			if ($name_search_str != '') {
				$name_search_str .= '%';
			}
		}

		//Search
		$query = '
			SELECT	section_value,value,name
			FROM	'. $gacl_api->_db_table_prefix . $object_type .'
			WHERE	section_value='. $db->qstr($_GET['section_value']) .'
			AND		(';

		if (count($exploded_value_search_str) > 1) {
			$query .= 'lower(value) IN ('. implode(',', $exploded_value_search_str) .')';
		} else {
			$query .= 'lower(value) LIKE ' . $db->qstr($value_search_str);
		}

		$query .= ' OR ';

		if (count($exploded_name_search_str) > 1) {
			$query .= 'lower(name) IN ('. implode(',', $exploded_name_search_str) .')';
		} else {
			$query .= 'lower(name) LIKE ' . $db->qstr($name_search_str);
		}

		$query .= ')
			ORDER BY section_value,order_value,name';
		$rs = $db->SelectLimit($query, $gacl_api->_max_search_return_items);

		$options_objects = array();
		$total_rows = 0;

		if (is_object($rs)) {
			$total_rows = $rs->RecordCount();

			while ($row = $rs->FetchRow()) {
				list($section_value, $value, $name) = $row;
				$options_objects[attr($value)] = attr($name);
			}
		}

		$smarty->assign('options_objects', $options_objects);
		$smarty->assign('total_rows', $total_rows);

		$smarty->assign('value_search_str', $_GET['value_search_str']);
		$smarty->assign('name_search_str', $_GET['name_search_str']);

		//break;
	default:
		$smarty->assign('src_form', $_GET['src_form']);
		$smarty->assign('section_value', $_GET['section_value']);
		$smarty->assign('section_value_name', ucfirst($_GET['section_value']));
		$smarty->assign('object_type', $object_type);
		$smarty->assign('object_type_name', strtoupper($object_type));

		break;
}

$smarty->assign('current', $object_type .'_search');
$smarty->assign('page_title', strtoupper($object_type) .' Search');

$smarty->assign('phpgacl_version', $gacl_api->get_version());
$smarty->assign('phpgacl_schema_version', $gacl_api->get_schema_version());

$smarty->display('phpgacl/object_search.tpl');
?>