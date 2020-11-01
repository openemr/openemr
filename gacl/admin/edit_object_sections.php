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

require_once("gacl_admin.inc.php");

//GET takes precedence.
if ( isset($_GET['object_type']) AND $_GET['object_type'] != '' ) {
	$object_type = $_GET['object_type'];
} else {
	$object_type = $_POST['object_type'];
}

switch(strtolower(trim($object_type))) {
    case 'aco':
        $object_type = 'aco';
		$object_sections_table = $gacl_api->_db_table_prefix . 'aco_sections';
        break;
    case 'aro':
        $object_type = 'aro';
		$object_sections_table = $gacl_api->_db_table_prefix . 'aro_sections';
        break;
    case 'axo':
        $object_type = 'axo';
		$object_sections_table = $gacl_api->_db_table_prefix . 'axo_sections';
        break;
    case 'acl':
        $object_type = 'acl';
		$object_sections_table = $gacl_api->_db_table_prefix . 'acl_sections';
        break;
    default:
        echo "ERROR: Must select an object type<br />\n";
        exit();
        break;
}

$postAction = $_POST['action'] ?? null;
switch ($postAction) {
    case 'Delete':

        if (count($_POST['delete_sections']) > 0) {
            foreach($_POST['delete_sections'] as $id) {
                $gacl_api->del_object_section($id, $object_type, TRUE);
            }
        }

        //Return page.
        $gacl_api->return_page($_POST['return_page']);

        break;
    case 'Submit':
        $gacl_api->debug_text("Submit!!");

        //Update sections
        foreach ($_POST['sections'] as $row) {
            list($id, $value, $order, $name) = $row;
            $gacl_api->edit_object_section($id, $name, $value, $order,0,$object_type );
        }
        unset($id);
        unset($value);
        unset($order);
        unset($name);

        //Insert new sections
        foreach ($_POST['new_sections'] as $row) {
            list($value, $order, $name) = $row;

            if (!empty($value) AND !empty($order) AND !empty($name)) {

                $object_section_id = $gacl_api->add_object_section($name, $value, $order, 0, $object_type);
                $gacl_api->debug_text("Section ID: $object_section_id");
            }
        }
        $gacl_api->debug_text("return_page: ". $_POST['return_page']);
        $gacl_api->return_page($_POST['return_page']);

        break;
    default:
        $query = "select id,value,order_value,name from $object_sections_table order by order_value";

        $rs = $db->pageexecute($query, $gacl_api->_items_per_page, ($_GET['page'] ?? null));
        $rows = $rs->GetRows();

        $sections = array();

        foreach ($rows as $row) {
            list($id, $value, $order_value, $name) = $row;

                $sections[] = array(
                                                'id' => $id,
                                                'value' => $value,
                                                'order' => $order_value,
                                                'name' => $name
                                            );
        }

        $new_sections = array();

        for($i=0; $i < 5; $i++) {
                $new_sections[] = array(
                                                'id' => $i,
                                                'value' => NULL,
                                                'order' => NULL,
                                                'name' => NULL
                                            );
        }

        $smarty->assign('sections', $sections);
        $smarty->assign('new_sections', $new_sections);

        $smarty->assign("paging_data", $gacl_api->get_paging_data($rs));

        break;
}

$smarty->assign('object_type', $object_type);
$smarty->assign('object_type_escaped', attr($object_type));

$smarty->assign('return_page', $_SERVER['REQUEST_URI']);

$smarty->assign('current','edit_'. $object_type .'_sections');
$smarty->assign('page_title', 'Edit '. strtoupper($object_type) .' Sections');

$smarty->assign("phpgacl_version", $gacl_api->get_version() );
$smarty->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

$smarty->display('phpgacl/edit_object_sections.tpl');
?>
