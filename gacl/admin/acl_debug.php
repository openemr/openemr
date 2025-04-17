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

require_once('gacl_admin.inc.php');

$getAction = $_GET['action'] ?? null;
switch ($getAction) {
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

		$smarty->assign('acls', ($acls ?? null));
		$smarty->assign('aco_section_value', $_GET['aco_section_value']);
		$smarty->assign('aco_value', $_GET['aco_value']);
		$smarty->assign('aro_section_value', $_GET['aro_section_value']);
		$smarty->assign('aro_value', $_GET['aro_value']);
		$smarty->assign('axo_section_value', $_GET['axo_section_value']);
		$smarty->assign('axo_value', $_GET['axo_value']);
		$smarty->assign('root_aro_group_id', $_GET['root_aro_group_id']);
		$smarty->assign('root_axo_group_id', $_GET['root_axo_group_id']);
        break;
    default:
        $smarty->assign('acls', null);
        $smarty->assign('aco_section_value', null);
        $smarty->assign('aco_value', null);
        $smarty->assign('aro_section_value', null);
        $smarty->assign('aro_value', null);
        $smarty->assign('axo_section_value', null);
        $smarty->assign('axo_value', null);
        $smarty->assign('root_aro_group_id', null);
        $smarty->assign('root_axo_group_id', null);
		break;
}

$smarty->assign('return_page', $_SERVER['PHP_SELF']);

$smarty->assign('current','acl_debug');
$smarty->assign('page_title', 'ACL Debug');

$smarty->assign('phpgacl_version', $gacl_api->get_version());
$smarty->assign('phpgacl_schema_version', $gacl_api->get_schema_version());

$smarty->display('phpgacl/acl_debug.tpl');
?>
