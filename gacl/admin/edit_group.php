<?php
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
            echo xlt('ACL Administration Not Authorized');
            exit;
}

require_once('gacl_admin.inc.php');

// GET takes precedence.
if (empty($_GET['group_type'])) {
	$group_type = $_POST['group_type'];
} else {
	$group_type = $_GET['group_type'];
}

if (empty($_GET['return_page'])) {
	$return_page = $_POST['return_page'];
} else {
	$return_page = $_GET['return_page'];
}

switch(strtolower(trim($group_type))) {
	case 'axo':
		$group_type = 'axo';
		$group_table = $gacl_api->_db_table_prefix . 'axo_groups';
		break;
	default:
		$group_type = 'aro';
		$group_table = $gacl_api->_db_table_prefix . 'aro_groups';
		break;
}

$postAction = $_POST['action'] ?? null;
switch ($postAction) {
	case 'Delete':
		$gacl_api->debug_text('Delete');

		if (count($_POST['delete_group']) > 0) {
			//Always reparent children when deleting a group.
			foreach ($_POST['delete_group'] as $group_id) {
				$gacl_api->debug_text('Deleting group_id: '. $group_id);

				$result = $gacl_api->del_group($group_id, TRUE, $group_type);
				if ($result == FALSE) {
					$retry[] = $group_id;
				}
			}

			if (count($retry) > 0) {
				foreach($retry as $group_id) {
					$gacl_api->del_group($group_id, TRUE, $group_type);
				}
			}

		}

		//Return page.
		$gacl_api->return_page($return_page);
		break;
	case 'Submit':
		$gacl_api->debug_text('Submit');

		if (empty($_POST['parent_id'])) {
			$parent_id = 0;
		} else {
			$parent_id = $_POST['parent_id'];
		}

		//Make sure we're not reparenting to ourself.
		if (!empty($_POST['group_id']) AND $parent_id == $_POST['group_id']) {
			echo "Sorry, can't reparent to self!<br />\n";
			exit;
		}

		//No parent, assume a "root" group, generate a new parent id.
		if (empty($_POST['group_id'])) {
			$gacl_api->debug_text('Insert');

			$insert_id = $gacl_api->add_group($_POST['value'], $_POST['name'], $parent_id, $group_type);
		} else {
			$gacl_api->debug_text('Update');

			$gacl_api->edit_group($_POST['group_id'], $_POST['value'], $_POST['name'], $parent_id, $group_type);
		}

		$gacl_api->return_page($return_page);
		break;
	default:
		//Grab specific group data
		if (!empty($_GET['group_id'])) {
			$query = '
				SELECT	id,parent_id,value,name
				FROM	'. $group_table .'
				WHERE	id='. (int)$_GET['group_id'];

			list($id, $parent_id, $value, $name) = $db->GetRow($query);
			//showarray($row);
		} else {
			$parent_id = $_GET['parent_id'] ?? null;
			$value = '';
			$name = '';
		}

		$smarty->assign('id', ($id ?? null));
		$smarty->assign('parent_id', $parent_id);
		$smarty->assign('value', $value);
		$smarty->assign('name', $name);

		$smarty->assign('options_groups', $gacl_api->format_groups($gacl_api->sort_groups($group_type)));
		break;
}

$smarty->assign('group_type', $group_type);
$smarty->assign('return_page', $return_page);

$smarty->assign('current','edit_'. $group_type .'_group');
$smarty->assign('page_title', 'Edit '. strtoupper($group_type) .' Group');

$smarty->assign('phpgacl_version', $gacl_api->get_version());
$smarty->assign('phpgacl_schema_version', $gacl_api->get_schema_version());

$smarty->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

$smarty->display('phpgacl/edit_group.tpl');
?>
