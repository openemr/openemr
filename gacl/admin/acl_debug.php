<?php
/*
Ready for smarty 3
Changes: Used smarty 3 data object
*/
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
            echo xlt('ACL Administration Not Authorized');
            exit;
}

require_once('gacl_admin.inc.php');

//make a smarty 3 data object
$data = new Smarty_Data;

switch ($_GET['action']) {
    case 'Submit':
        $gacl_api->debug_text('Submit!!');
		//$result = $gacl_api->acl_query('system', 'email_pw', 'users', '1', NULL, NULL, NULL, NULL, TRUE);
		$result = $gacl_api->acl_query(	$_GET['aco_section_value'],
										$_GET['aco_value'],
										$_GET['aro_section_value'],
										$_GET['aro_value'],
										$_GET['axo_section_value'],
										$_GET['axo_value'],
										$_GET['root_aro_group_id'],
										$_GET['root_axo_group_id'],
										TRUE);

		//Grab all relevant columns
		$result['query'] = str_replace(	'a.id,a.allow,a.return_value',
										'	a.id,
											a.allow,
											a.return_value,
											a.note,
											a.updated_date,
											ac.section_value as aco_section_value,
											ac.value as aco_value,
											ar.section_value as aro_section_value,
											ar.value as aro_value,
											ax.section_value as axo_section_value,
											ax.value as axo_value',
											$result['query']);
		$rs = $gacl_api->db->Execute($result['query']);

		if (is_object($rs)) {
			while ($row = $rs->FetchRow()) {
				list(
					$id,
					$allow,
					$return_value,
					$note,
					$updated_date,
					$aco_section_value,
					$aco_value,
					$aro_section_value,
					$aro_value,
					$axo_section_value,
					$axo_value
				) = $row;

				$acls[] = array(
					'id' => $id,
					'allow' => $allow,
					'return_value' => $return_value,
					'note' => $note,
					'updated_date' => date('d-M-y H:m:i',$updated_date),

					'aco_section_value' => $aco_section_value,
					'aco_value' => $aco_value,

					'aro_section_value' => $aro_section_value,
					'aro_value' => $aro_value,

					'axo_section_value' => $axo_section_value,
					'axo_value' => $axo_value,
				);
			}
		}

		//echo "<br /><br />$x ACL_CHECK()'s<br />\n";

		$data->assign('acls', $acls);

		$data->assign('aco_section_value', $_GET['aco_section_value']);
		$data->assign('aco_value', $_GET['aco_value']);
		$data->assign('aro_section_value', $_GET['aro_section_value']);
		$data->assign('aro_value', $_GET['aro_value']);
		$data->assign('axo_section_value', $_GET['axo_section_value']);
		$data->assign('axo_value', $_GET['axo_value']);
		$data->assign('root_aro_group_id', $_GET['root_aro_group_id']);
		$data->assign('root_axo_group_id', $_GET['root_axo_group_id']);
        break;
    default:
		break;
}

$data->assign('return_page', $_SERVER['PHP_SELF']);

$data->assign('current','acl_debug');
$data->assign('page_title', 'ACL Debug');

$data->assign('phpgacl_version', $gacl_api->get_version());
$data->assign('phpgacl_schema_version', $gacl_api->get_schema_version());

$smarty->display('phpgacl/acl_debug.tpl',$data);
?>
