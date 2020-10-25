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
if (!empty($_GET['object_type'])) {
	$object_type = $_GET['object_type'];
} else {
	$object_type = $_POST['object_type'];
}

switch(strtolower(trim($object_type))) {
    case 'aco':
        $object_type = 'aco';
	$object_table = $gacl_api->_db_table_prefix . 'aco';
		$object_sections_table = $gacl_api->_db_table_prefix . 'aco_sections';
        break;
    case 'aro':
        $object_type = 'aro';
	$object_table = $gacl_api->_db_table_prefix . 'aro';
		$object_sections_table = $gacl_api->_db_table_prefix . 'aro_sections';
        break;
    case 'axo':
        $object_type = 'axo';
	$object_table = $gacl_api->_db_table_prefix . 'axo';
		$object_sections_table = $gacl_api->_db_table_prefix . 'axo_sections';
        break;
    default:
        echo "ERROR: Must select an object type<br />\n";
        exit();
        break;
}

$postAction = $_POST['action'] ?? null;
switch ($postAction) {
    case 'Delete':

        if (count($_POST['delete_object']) > 0) {
            foreach($_POST['delete_object'] as $id) {
                $gacl_api->del_object($id, $object_type, TRUE);
            }
        }

        //Return page.
        $gacl_api->return_page($_POST['return_page']);

        break;
    case 'Submit':
        $gacl_api->debug_text("Submit!!");

        //Update objects
        if (!empty($_POST['objects'])) {
            foreach ($_POST['objects'] as $row) {
                list($id, $value, $order, $name) = $row;
                $gacl_api->edit_object($id, $_POST['section_value'], $name, $value, $order, 0, $object_type);
            }
        }
        unset($id);
        unset($section_value);
        unset($value);
        unset($order);
        unset($name);

        //Insert new sections
        foreach ($_POST['new_objects'] as $row) {
            list($value, $order, $name) = $row;

            if (!empty($value) AND !empty($name)) {
                $object_id= $gacl_api->add_object($_POST['section_value'], $name, $value, $order, 0, $object_type);
            }
        }
        $gacl_api->debug_text("return_page: ". $_POST['return_page']);
        $gacl_api->return_page($_POST['return_page']);

        break;
    default:
        //Grab section name
        $query = "select name from $object_sections_table where value = ". $db->qstr($_GET['section_value']);
        $section_name = $db->GetOne($query);

        $query = "select
                                    id,
                                    section_value,
                                    value,
                                    order_value,
                                    name
                        from    $object_table
                        where   section_value=". $db->qstr($_GET['section_value']) ."
                        order by order_value";
        $rs = $db->pageexecute($query, $gacl_api->_items_per_page, ($_GET['page'] ?? null));
        $rows = $rs->GetRows();

        foreach ($rows as $row) {
            list($id, $section_value, $value, $order_value, $name) = $row;

                $objects[] = array(
                                                'id' => $id,
                                                'section_value' => $section_value,
                                                'value' => $value,
                                                'order' => $order_value,
                                                'name' => $name
                                            );
        }

        for($i=0; $i < 5; $i++) {
                $new_objects[] = array(
                                                'id' => $i,
                                                'section_value' => NULL,
                                                'value' => NULL,
                                                'order' => NULL,
                                                'name' => NULL
                                            );
        }

        $smarty->assign('objects', ($objects ?? null));
        $smarty->assign('new_objects', $new_objects);

        $smarty->assign("paging_data", $gacl_api->get_paging_data($rs));

        break;
}

$smarty->assign('section_value', ($_GET['section_value'] ?? null));
$smarty->assign('section_value_escaped', attr($_GET['section_value'] ?? null));

$smarty->assign('section_name', ($section_name ?? null));

$smarty->assign('object_type', $object_type);
$smarty->assign('object_type_escaped', attr($object_type));

$smarty->assign('return_page', $_SERVER['REQUEST_URI']);

$smarty->assign('current','edit_'. $object_type .'s');
$smarty->assign('page_title', 'Edit '. strtoupper($object_type) .' Objects');

$smarty->assign("phpgacl_version", $gacl_api->get_version() );
$smarty->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

$smarty->display('phpgacl/edit_objects.tpl');
?>
