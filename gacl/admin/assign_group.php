<?php
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("ACL Administration")]);
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
		$table = $gacl_api->_db_table_prefix . 'axo';
		$group_table = $gacl_api->_db_table_prefix . 'axo_groups';
		$group_sections_table = $gacl_api->_db_table_prefix . 'axo_sections';
		$group_map_table = $gacl_api->_db_table_prefix . 'groups_axo_map';
		$object_type = 'Access eXtension Object';
		break;
	default:
		$group_type = 'aro';
		$table = $gacl_api->_db_table_prefix . 'aro';
		$group_table = $gacl_api->_db_table_prefix . 'aro_groups';
		$group_sections_table = $gacl_api->_db_table_prefix . 'aro_sections';
		$group_map_table = $gacl_api->_db_table_prefix . 'groups_aro_map';
		$object_type = 'Access Request Object';
		break;
}

$postAction = $_POST['action'] ?? null;
switch ($postAction) {
	case 'Remove':
		$gacl_api->debug_text('Delete!!');

		//Parse the form values
		//foreach ($_POST['delete_assigned_aro'] as $aro_value) {
        foreach ($_POST['delete_assigned_object'] as $object_value) {
			$split_object_value = explode('^', $object_value);
			$selected_object_array[$split_object_value[0]][] = $split_object_value[1];
		}

		//Insert Object -> GROUP mappings
        foreach ($selected_object_array as $object_section_value => $object_array) {
			$gacl_api->debug_text('Assign: Object ID: '. $object_section_value .' to Group: '. $_POST['group_id']);

			foreach ($object_array as $object_value) {
				$gacl_api->del_group_object($_POST['group_id'], $object_section_value, $object_value, $group_type);
			}
		}

		//Return page.
		$gacl_api->return_page($_SERVER['PHP_SELF'] .'?group_type='. urlencode($_POST['group_type']) .'&group_id='. urlencode($_POST['group_id']));

		break;
	case 'Submit':
		$gacl_api->debug_text('Submit!!');

		//showarray($_POST['selected_'.$_POST['group_type']]);
		//Parse the form values
		//foreach ($_POST['selected_aro'] as $aro_value) {
        foreach ($_POST['selected_'.$_POST['group_type']] as $object_value) {
			$split_object_value = explode('^', $object_value);
			$selected_object_array[$split_object_value[0]][] = $split_object_value[1];
		}

		//Insert ARO -> GROUP mappings
        foreach ($selected_object_array as $object_section_value => $object_array) {
			$gacl_api->debug_text('Assign: Object ID: '. $object_section_value .' to Group: '. $_POST['group_id']);

			foreach ($object_array as $object_value) {
				$gacl_api->add_group_object($_POST['group_id'], $object_section_value, $object_value, $group_type);
			}
		}

		$gacl_api->return_page($_SERVER['PHP_SELF'] .'?group_type='. urlencode($_POST['group_type']) .'&group_id='. urlencode($_POST['group_id']));

		break;
	default:
	//
	//Grab all sections for select box
	//
	$query = 'SELECT value,name FROM '. $group_sections_table .' ORDER BY order_value,name';
	$rs = $db->Execute($query);

	$options_sections = array();

	if (is_object($rs)) {
		while ($row = $rs->FetchRow()) {
			$options_sections[$row[0]] = $row[1];
		}
	}

	//showarray($options_sections);
	$smarty->assign('options_sections', $options_sections);
	$smarty->assign('section_value', reset($options_sections));

	//
	//Grab all objects for select box
	//
	$query = 'SELECT section_value,value,name FROM '. $table .' ORDER BY section_value,order_value,name';
	$rs = $db->SelectLimit($query, $gacl_api->_max_select_box_items);

	$js_array_name = 'options[\''. $group_type .'\']';
	//Init the main aro js array.
	$js_array = 'var options = new Array();' . "\n";
	$js_array .= $js_array_name .' = new Array();' . "\n";

	unset($tmp_section_value);

	if (is_object($rs)) {
		while ($row = $rs->FetchRow()) {
			//list($section_value, $value, $name) = $row;

			$section_value = addslashes($row[0]);
			$value = addslashes($row[1]);
			$name = addslashes($row[2]);

			//Prepare javascript code for dynamic select box.
			//Init the javascript sub-array.
			if (!isset($tmp_section_value) OR $section_value != $tmp_section_value) {
				$i = 0;
				$js_array .= $js_array_name .'[\''. $section_value .'\'] = new Array();' . "\n";
			}

			//Add each select option for the section
			$js_array .= $js_array_name .'[\''. $section_value .'\']['. $i .'] = new Array(\''. $value .'\', \''. $name ."');\n";

			$tmp_section_value = $section_value;
			$i++;
		}
	}

	$smarty->assign('js_array', $js_array);
	$smarty->assign('js_array_name', $group_type);

	//Grab list of assigned Objects
	$query = '
		SELECT	b.section_value,b.value,b.name AS b_name,c.name AS c_name
		FROM	'. $group_map_table .' a
		INNER JOIN	'. $table .' b ON b.id=a.'. $group_type .'_id
		INNER JOIN	'. $group_sections_table .' c ON c.value=b.section_value
		WHERE   a.group_id='. $db->qstr($_GET['group_id']) .'
		ORDER BY c.name, b.name';
	//$rs = $db->Execute($query);
	$rs = $db->PageExecute($query, $gacl_api->_items_per_page, ($_GET['page'] ?? null));

	$object_rows = array();

	if (is_object($rs)) {
		while ($row = $rs->FetchRow()) {
			list($section_value, $value, $name, $section) = $row;

			$object_rows[] = array(
				'section_value' => $row[0],
				'value' => $row[1],
				'name' => $row[2],
				'section' => $row[3]
			);
		}

		$smarty->assign('total_objects', $rs->_maxRecordCount);

		$smarty->assign('paging_data', $gacl_api->get_paging_data($rs));
	}
	//showarray($aros);

	$smarty->assign('rows', $object_rows);

	//Get group name.
	$group_data = $gacl_api->get_group_data($_GET['group_id'], $group_type);
	$smarty->assign('group_name', $group_data[2]);

	$smarty->assign('group_id', $_GET['group_id']);
    $smarty->assign('group_id_escaped', attr($_GET['group_id']));

	break;
}

$smarty->assign('group_type', $group_type);
$smarty->assign('group_type_escaped', attr($group_type));
$smarty->assign('object_type', $object_type);
$smarty->assign('return_page', $_SERVER['REQUEST_URI'] );

$smarty->assign('current','assign_group_'. $group_type);
$smarty->assign('page_title', 'Assign Group - '. strtoupper($group_type));

$smarty->assign('phpgacl_version', $gacl_api->get_version() );
$smarty->assign('phpgacl_schema_version', $gacl_api->get_schema_version() );

$smarty->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

$smarty->display('phpgacl/assign_group.tpl');
?>
