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
        echo "ERROR: Must select an object type<br>\n";
        exit();
        break;
}
   
switch ($_POST['action']) {
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
        while (list(,$row) = @each($_POST['sections'])) {
            list($id, $value, $order, $name) = $row;
            $gacl_api->edit_object_section($id, $name, $value, $order,0,$object_type );
        }
        unset($id);
        unset($value);
        unset($order);
        unset($name);

        //Insert new sections
        while (list(,$row) = @each($_POST['new_sections'])) {
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

        $rs = $db->pageexecute($query, $gacl_api->_items_per_page, $_GET['page']);
        $rows = $rs->GetRows();

        $sections = array();

        while (list(,$row) = @each($rows)) {
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
$smarty->assign('return_page', $_SERVER['REQUEST_URI']);

$smarty->assign('current','edit_'. $object_type .'_sections');
$smarty->assign('page_title', 'Edit '. strtoupper($object_type) .' Sections');

$smarty->assign("phpgacl_version", $gacl_api->get_version() );
$smarty->assign("phpgacl_schema_version", $gacl_api->get_schema_version() );

$smarty->display('phpgacl/edit_object_sections.tpl');
?>
